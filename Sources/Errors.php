<?php
/*****************************************************************************/
/* Errors.php                                                                */
/*****************************************************************************/
/* YaBB: Yet another Bulletin Board                                          */
/* Open-Source Project started by Zef Hemel (zef@zefnet.com)                 */
/* Software Version: YaBB SE                                                 */
/* ========================================================================= */
/* Software Distributed by:    http://www.yabb.info                          */
/* Support, News, Updates at:  http://www.yabb.info/community                */
/*                             http://yabb.xnull.com/community               */
/* ========================================================================= */
/* Copyright (c) 2001-2002 Lewis Media - All Rights Reserved                 */
/* Software by: The YaBB Development Team                                    */
/*****************************************************************************/
/* This program is free software; you can redistribute it and/or modify it   */
/* under the terms of the GNU General Public License as published by the     */
/* Free Software Foundation; either version 2 of the License, or (at your    */
/* option) any later version.                                                */
/*                                                                           */
/* This program is distributed in the hope that it will be useful, but       */
/* WITHOUT ANY WARRANTY; without even the implied warranty of                */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General */
/* Public License for more details.                                          */
/*                                                                           */
/* The GNU GPL can be found in gpl.txt in this directory                     */
/*****************************************************************************/

$errorsphpver ="YaBB SE 1.3.0";

function fatal_error($e, $t = null) {
	global $txt, $color,$yytitle,$yyheaderdone,$db_prefix,$username,$REMOTE_ADDR,$REQUEST_URI,$modSettings;
	if ($t == null)
		$yytitle = $txt['106'];
	else
		$yytitle = $t;
	if (!$yyheaderdone)
		template_header();
	print <<<EOT
<table border=0 width="80%" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor" align="center" cellpadding="4">
  <tr>
<td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$yytitle</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]"><BR><font size=2>$e</font><BR><BR></td>
  </tr>
</table>
<center><BR><a href="javascript:history.go(-1)">
 $txt[250]</a></center>
EOT;

	// now log the error
	if ($t == null && $modSettings['enableErrorLogging'] == 1)
	{
		$request = mysql_query("INSERT INTO {$db_prefix}log_errors (logTime,memberName,IP,url,message) VALUES ('".time()."','$username','$REMOTE_ADDR','$REQUEST_URI','".mysql_escape_string($e)."')");
	}
	footer();
	obExit();
}

function ViewErrorLog()
{
	global $db_prefix,$cgi,$txt,$imagesdir,$color,$yytitle;
	// verify the user is an administrator
	is_admin();
	$yytitle = $txt[errlog1];
	template_header();

	$request = mysql_query("SELECT ID_ERROR,IP,url,memberName,logTime,message FROM {$db_prefix}log_errors ORDER BY logTime");
	print<<<EOT
<form action="$cgi;action=deleteErrors" method=POST>
<table border="0" cellspacing="1" cellpadding="5" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" colspan=2>
    <img src="$imagesdir/xx.gif" alt="" border="0">&nbsp;
    <font size="2" color="$color[titletext]"><b>$txt[errlog1]</b></font></td>
  </tr><tr>
    <td colspan = 2 class="quote">
    <font class="quote">$txt[errlog2]</font></td>
  </tr>
EOT;
	while ($row = mysql_fetch_array($request))
	{
		$errorDate = timeformat($row['logTime']);
		print <<<EOT
			<tr><td width=15 align=center class="windowbg2"><input type="checkbox" name="delete_$row[ID_ERROR]"></td><td class="windowbg2" width="100%">
			<b>$row[memberName]</b> : <b>$row[IP]</b> : <b>$errorDate</b><br><a href="$row[url]">$row[url]</a><br>$row[message]
			</td></tr>
EOT;
	}
	print "</table><br><input type=submit value=\"$txt[31]\">&nbsp;&nbsp;<input type=submit name=\"delall\" value=\"$txt[yse219]\"></form>";

	footer();
	obExit();
}

function DeleteErrors()
{
	global $HTTP_POST_VARS,$db_prefix,$delall;
	// verify the user is an administrator
	is_admin();

    if ($delall){
        $request = mysql_query("DELETE FROM {$db_prefix}log_errors");
        ViewErrorLog();
    }

	foreach ($HTTP_POST_VARS as $postVar=>$postVarValue)
	{
		if (strcmp(substr($postVar,0,7),"delete_")==0)
		{
			$id = substr($postVar,7);
			$request = mysql_query("DELETE FROM {$db_prefix}log_errors WHERE ID_ERROR='$id'");
		}
	}
	ViewErrorLog();
}

?>
