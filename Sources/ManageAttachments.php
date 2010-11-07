<?php
/*****************************************************************************/
/* ManageAttachments.php                                                     */
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

$manageattachmentsphpver = "YaBB SE 1.3.0";
is_admin();

function ManageAttachments (){
	global $yytitle,$txt,$color,$cgi,$img,$imagesdir,$db_prefix,$modSettings,$realNames,$scripturl;
	$yytitle = "$txt[yse201]";
	template_header();

	// now we need to collect the stats
	$request = mysql_query("SELECT attachmentSize FROM {$db_prefix}messages WHERE (attachmentFilename!=NULL || attachmentSize>0)");
	$attachmentNo = 0;
	$attachmentDirSize = 0;
	while ($row = mysql_fetch_row($request))
	{
		$attachmentDirSize += $row[0];
		$attachmentNo ++;
	}
	$attachmentSpace = ($modSettings['attachmentDirSizeLimit']=='')?$txt['yse215']:(round($modSettings['attachmentDirSizeLimit']-($attachmentDirSize/1024),2)." $txt[yse211]");
	$attachmentDirSize = round ($attachmentDirSize/1024,2)." $txt[yse211]";

	print <<<EOT
<table border="0" align="center" cellspacing="1" cellpadding="4" bgcolor="$color[bordercolor]" class="bordercolor" width="90%">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]" colspan="3">
		<script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
    <font class="text1" color="$color[titletext]" size="1"><b>$txt[yse201]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" colspan="3"><BR><font size="1">$txt[yse202]</font><BR><BR></td>
  </tr>

  <tr>
    <td bgcolor="$color[catbg]" class="catbg" height="25"><font size="2"><b>$txt[yse203]</b></font></td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2" width="100%" valign="top">
    <table border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td>$txt[yse204]:</td><td>$attachmentNo</td>
      </tr>
      <tr>
        <td>$txt[yse205]:</td><td>$attachmentDirSize</td>
      </tr>
      <tr>
        <td>$txt[yse206]:</td><td>$attachmentSpace</td>
      </tr>
    </table><br>
    </td>
  </tr>
  <tr>
    <td bgcolor="$color[catbg]" class="catbg" height="25"><font size="2"><b>$txt[yse207]</b></font></td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2" width="100%" valign="top">
	<form action="$cgi;action=removeAttachmentsByAge" method=POST>
	$txt[72]: <input type=text size=40 value="$txt[yse216]" name="notice"><br>
	$txt[yse209] <input type=text value=25 size=4 name="age">$txt[579] <input type=submit value="$txt[31]"></form>
	<form action="$cgi;action=removeAttachmentsBySize" method=POST>
	$txt[72]: <input type=text size=40 value="$txt[yse216]" name="notice"><br>
	$txt[yse210] <input type=text value=100 size=4 name="size">$txt[yse211] <input type=submit value="$txt[31]"></form><br>
    </td>
  </tr>
  <tr>
    <td bgcolor="$color[catbg]" class="catbg" height="25"><font size="2"><b>$txt[yse208]</b></font></td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2" width="100%" valign="top">
<table border="0" cellspacing="1" cellpadding="4" bgcolor="$color[bordercolor]" class="bordercolor" width="100%">
      <tr>
          <td bgcolor="$color[windowbg2]" class="windowbg2" nowrap><b>$txt[yse213]</b></td>
		  <td bgcolor="$color[windowbg2]" class="windowbg2" nowrap><b>$txt[yse214]</b></td>
		  <td bgcolor="$color[windowbg2]" class="windowbg2" nowrap><b>$txt[279]</b></td>
		  <td bgcolor="$color[windowbg2]" class="windowbg2" nowrap><b>$txt[317]</b></td>
		  <td bgcolor="$color[windowbg2]" class="windowbg2" nowrap><b>$txt[118]</b></td>
		  <td bgcolor="$color[windowbg2]" class="windowbg2" nowrap><b>$txt[yse138]</b></td>
      </tr>
EOT;

$request = mysql_query("SELECT m.ID_MSG,m.posterName,m.posterTime,m.ID_TOPIC,m.ID_MEMBER,m.attachmentFilename,m.attachmentSize,mem.subject,t.ID_BOARD FROM {$db_prefix}messages as m,{$db_prefix}messages as mem, {$db_prefix}topics as t WHERE ((m.attachmentFilename!=NULL || m.attachmentSize > 0) && m.ID_TOPIC=t.ID_TOPIC && t.ID_FIRST_MSG=mem.ID_MSG)");
while ($row = mysql_fetch_array($request))
{
LoadRealName($row['posterName']);
$name = $realNames[$row['posterName']];
$euser=urlencode($row['posterName']);
$name = ($row['ID_MEMBER']==-1)?$name:"<a href=\"$cgi;action=viewprofile;user=$euser\">$name</a>";
$date = timeformat($row['posterTime']);
$size = round($row[attachmentSize]/1024,2);
print <<<EOT
	<tr>
        <td bgcolor="$color[windowbg2]" class="windowbg2"><a href="$modSettings[attachmentUrl]/$row[attachmentFilename]" target="_blank">$row[attachmentFilename]</a></td>
	    <td bgcolor="$color[windowbg2]" class="windowbg2" align=right>$size$txt[yse211]</td>
	    <td bgcolor="$color[windowbg2]" class="windowbg2">$name</td>
	    <td bgcolor="$color[windowbg2]" class="windowbg2">$date</td>
	    <td bgcolor="$color[windowbg2]" class="windowbg2"><a href="$scripturl?board=$row[ID_BOARD];action=display;threadid=$row[ID_TOPIC]">$row[subject]</a></td>
 	    <td bgcolor="$color[windowbg2]" class="windowbg2"><a href="$cgi;action=removeattachment;msg=$row[ID_MSG]">$txt[yse138]</a></td>
      </tr>
EOT;
}
print <<<EOT
	</table>
	  <div align=right><a href="$cgi;action=removeallattachments">$txt[yse138] $txt[190]</a></div>
		<br>
	  </td>
  </tr>
</table>
EOT;
	footer();
	obExit();
}

function RemoveAttachmentByAge()
{
	global $txt,$color,$cgi,$img,$imagesdir,$db_prefix,$modSettings,$realNames,$scripturl,$age,$notice,$msg;

	$threshold = time()-(24*60*60*$age);
	$request = mysql_query("SELECT attachmentFilename,ID_MSG FROM {$db_prefix}messages WHERE (posterTime<$threshold && (attachmentFilename!=NULL || attachmentSize>0))");
	while ($row = mysql_fetch_array($request))
	{
		$row['body'].= "\n\n".mysql_escape_string($notice);
		$request2 = mysql_query("UPDATE {$db_prefix}messages SET attachmentFilename=NULL,attachmentSize=0,body='$row[body]' WHERE ID_MSG='$row[ID_MSG]'");
		unlink($modSettings['attachmentUploadDir'] . "/" . $row['attachmentFilename']);
	}
	ManageAttachments();
}

function RemoveAttachmentBySize()
{
	global $txt,$color,$cgi,$img,$imagesdir,$db_prefix,$modSettings,$realNames,$scripturl,$size,$notice,$msg;

	$threshold = 1024*$size;
	$request = mysql_query("SELECT attachmentFilename,ID_MSG,body FROM {$db_prefix}messages WHERE (attachmentSize>$threshold)");
	while ($row = mysql_fetch_array($request))
	{
		$row['body'].= "\n\n".mysql_escape_string($notice);
		$request2 = mysql_query("UPDATE {$db_prefix}messages SET attachmentFilename=NULL,attachmentSize=0,body='$row[body]' WHERE ID_MSG='$row[ID_MSG]'");
		unlink("$modSettings[attachmentUploadDir]/$row[attachmentFilename]");
	}
	ManageAttachments();
}

function RemoveAttachment()
{
	global $txt,$color,$cgi,$img,$imagesdir,$db_prefix,$modSettings,$realNames,$scripturl,$msg;

	$request = mysql_query("SELECT attachmentFilename,body,ID_MSG FROM {$db_prefix}messages WHERE ID_MSG='$msg'");
	$row = mysql_fetch_array($request);
	$row['body'].= "\n\n".mysql_escape_string($txt['yse216']);
	$request2 = mysql_query("UPDATE {$db_prefix}messages SET attachmentFilename=NULL,attachmentSize=0,body='$row[body]' WHERE ID_MSG='$msg'");
	unlink($modSettings['attachmentUploadDir'] . "/" . $row['attachmentFilename']);
	ManageAttachments();
}

function RemoveAllAttachments()
{
	global $txt,$color,$cgi,$img,$imagesdir,$db_prefix,$modSettings,$realNames,$scripturl;

	$request = mysql_query("SELECT attachmentFilename,body,ID_MSG FROM {$db_prefix}messages WHERE (attachmentSize>0 || attachmentFilename!=NULL)");
	while ($row = mysql_fetch_array($request))
	{
		$row['body'].= "\n\n".mysql_escape_string($notice);
		$request2 = mysql_query("UPDATE {$db_prefix}messages SET attachmentFilename=NULL,attachmentSize=0,body='$row[body]' WHERE ID_MSG='$row[ID_MSG]'");
		unlink($modSettings['attachmentUploadDir'] . "/" . $row['attachmentFilename']);
	}
	ManageAttachments();
}

?>
