<?php
/*****************************************************************************/
/* SendTopic.php                                                             */
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

$sendtopicplver="YaBB SE 1.3.0";

function SendTopic ()
{
	global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix;
	if ($board=="_" || $board=="" || $board==" "){ fatal_error($txt[709]); }
	if ($threadid=="_" || $threadid=="" || $threadid==" "){ fatal_error($txt[710]); }

	$request = mysql_query("SELECT * FROM {$db_prefix}messages WHERE ID_TOPIC=$threadid ORDER BY ID_MSG LIMIT 1");
	$row = mysql_fetch_array($request);

	$yytitle = "$txt[707]&nbsp; &#171; $row[subject] &#187; &nbsp;$txt[708]";
	template_header();

print <<<EOT
<form action="$cgi;action=sendtopic2" method="post">
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]" colspan="2">
        <img src="$imagesdir/email_sm.gif" alt="" border="0">
        <font size=2 class="text1" color="$color[titletext]"><b>$yytitle</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>$txt[715]</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <input type="text" name="y_name" size="20" maxlength="40" value="$settings[1]">
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>$txt[716]</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <input type="text" name="y_email" size="20" maxlength="40" value="$settings[2]">
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=center valign=top colspan="2">
        <hr width="100%" size="1" class="windowbg3">
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align="right" valign="top">
        <font size=2><B>$txt[717]</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align="left" valign="middle">
        <input type="text" name="r_name" size="20" maxlength="40">
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>$txt[718]</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <input type="text" name="r_email" size="20" maxlength="40">
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=center valign=middle colspan=2>
	<INPUT TYPE="hidden" NAME="board" VALUE="$board">
	<INPUT TYPE="hidden" NAME="threadid" VALUE="$threadid">
        <input type="submit" name="Send" value="$txt[339]">
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</form>
EOT;

footer();
	obExit();
}

function SendTopic2 (){
	global $threadid,$board,$y_name,$r_name,$y_email,$r_email,$yySetLocation,$cgi,$txt,$mbname,$db_prefix;
	if ($board=="_" || $board=="" || $board==" "){ fatal_error($txt[709]); }
	if ($threadid=="_" || $threadid=="" || $threadid==" "){ fatal_error($txt[710]); }

	$y_name = trim($y_name);
	$r_name = trim($r_name);

	if ($y_name=="_" || $y_name=="" || $y_name==" "){ fatal_error($txt[75]); }
	if ($y_email==""){ fatal_error($txt[76]); }
	if (!preg_match("/^[0-9A-Za-z@\._\-]+$/",$y_email)) { fatal_error($txt[243]); }
	
	if ($r_name=="_" || $r_name=="" || $r_name==" "){ fatal_error($txt[710]); }
	if ($r_email==""){ fatal_error($txt[76]); }
	if (!preg_match("/^[0-9A-Za-z@\._\-]+$/",$r_email)) { fatal_error($txt[243]); }

	$request = mysql_query("SELECT * FROM {$db_prefix}messages WHERE ID_TOPIC=$threadid ORDER BY ID_MSG LIMIT 1");
	$row = mysql_fetch_array($request);

	sendmail($r_email,"$txt[118]:  $row[subject] ($txt[318] $y_name)","$txt[711] $r_name,\n\n$txt[712]: $row[subject], on $mbname. $txt[713]:\n\n$cgi&action=display&threadid=$threadid\n\n\n$txt[714],\n$y_name",$y_email);

	$yySetLocation ="$cgi&action=display&threadid=$threadid";
	redirectexit();
}
?>