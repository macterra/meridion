<?php
/*****************************************************************************/
/* ManageBoards.php                                                          */
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

$manageboardsplver="YaBB SE 1.3.0";
is_admin();

function ManageBoards (){
	global $yytitle,$txt,$color,$cgi,$img,$imagesdir,$db_prefix;
	$yytitle = "$txt[41]";
    $selectcat="";
	$allcats = mysql_query("SELECT name,ID_CAT FROM {$db_prefix}categories WHERE 1 ORDER BY catOrder");
	while ($allcat = mysql_fetch_assoc($allcats))
	{
		if ($selectcat == '')
			$addboardselect = "<option value=$allcat[ID_CAT] selected>$allcat[name]</option>";
		$selectcat=$selectcat."<option value=$allcat[ID_CAT]>$allcat[name]</option>";
	}
	template_header();
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
    <a href="javascript:reqWin('help.php?help=1')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font class="text1" color="$color[titletext]" size="1"><b>$txt[41]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" colspan="3"><BR><font size="1">$txt[677]</font><BR><BR></td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg]" class="windowbg" width="100%" valign=top colspan="3">
    <form action="$cgi;action=addboard" method="POST">
    <table border="0" cellspacing="0" cellpadding="5">
      <tr>
        <td nobreak>
			<img src="$imagesdir/board.gif" alt="" border="0"> <font size="2"><b>$txt[44]: </b><input type=text name="boardname" size="30"></font>
		</td>
        <td valign="bottom" align="right" nobreak><font size="2">
			<b>$txt[yse84]: </b><select name="ID_CAT">$addboardselect$selectcat</select>
		</td>
      </tr>
  	  <tr>
		<td rowspan=2><textarea name="descr" cols=50 rows=3></textarea><br><input type=checkbox name='isAnnouncement'> $txt[announcement2]<br><input type=checkbox name='count'> $txt[count]</td>
		<td align="right"><b>$txt[299]: </b><input type=text name=moderator value="" size="25"></font></td></tr>
  	  <tr><td align="right"><input type=submit value="$txt[45]"></td></tr>
    </table>
    </form>
    </td>
  </tr>

	<tr>
    <td class="titlebg" bgcolor="$color[titlebg]" width="*"><font class="text1" color="$color[titletext]" size="1"><b>$txt[20]</b></font></td>
    <td class="titlebg" bgcolor="$color[titlebg]" width="20%"><font class="text1" color="$color[titletext]" size="1"><b>$txt[12]</b></font></td>
    <td class="titlebg" bgcolor="$color[titlebg]" width="20%"><font class="text1" color="$color[titletext]" size="1"><b>$txt[42]</b></font></td>
  </tr>
EOT;
$request = mysql_query("SELECT name,ID_CAT FROM {$db_prefix}categories WHERE 1 ORDER BY catOrder");
while ($curcat = mysql_fetch_assoc($request))
	{
		print <<<EOT
  <tr>
    <td colspan="3" bgcolor="$color[catbg]" class="catbg" height="25"><font size="2"><b>$curcat[name]</b> <a href="$cgi;action=reorderboards;ID_CAT=$curcat[ID_CAT]">($txt[643])</a></font></td>
  </tr>
EOT;
$request2 = mysql_query("SELECT name,description,moderators,ID_BOARD,isAnnouncement,notifyAnnouncements,count FROM {$db_prefix}boards WHERE ID_CAT=$curcat[ID_CAT]");
		while ($curboard = mysql_fetch_assoc($request2)) {
			$isAnnouncement = $curboard['isAnnouncement']?' checked':'';
			$count = $curboard['count']?' checked':'';
			if ($isAnnouncement != '') {
				$notifyAnnouncements = $curboard['notifyAnnouncements']?' checked':'';
				$notifyAnncmntsChk = "<br><input type=checkbox name=\"notifyAnnouncements\"$notifyAnnouncements> $txt[notifyXAnn5]";
			}
			else
			  $notifyAnncmntsChk = '';

				print <<<EOT
  <tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2" width="100%" valign="top" colspan="3">
    <form action="$cgi;action=modifyboard" method="POST" name="board$curboard[ID_BOARD]">
    <table border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td width="10" valign="top"><img src="$imagesdir/board.gif" alt="" border="0"></td>
        <td><font size="2">
        <input type="hidden" name="ID_BOARD" value="$curboard[ID_BOARD]"><input type="hidden" name="ID_CAT" value="$curcat[ID_CAT]">
        <input type="text" name="boardname" value="$curboard[name]" size="30"><br>
        <textarea name="descr" cols="50" rows="3">$curboard[description]</textarea><br><input type=checkbox name="isAnnouncement"$isAnnouncement> $txt[announcement2]$notifyAnncmntsChk<br><input type=checkbox name="count"$count> $txt[count]<br>$txt[yse84]: <select name="changecat"><option value="nochange">$curcat[name]</option>$selectcat</select></font>
        </td>
        <td valign="middle" width="20%"><input type="text" name="moderator" value="$curboard[moderators]" size="25"></td>
        <td valign="middle" width="20%">
	<input type="hidden" name="moda" value="1">
	<input type=button value="$txt[17]" onclick="board$curboard[ID_BOARD].moda.value='1'; submit(); "> 
	<input type=button value="$txt[31]" onclick="if(confirm('$txt[boardConfirm]')) { board$curboard[ID_BOARD].moda.value='-1'; submit(); }">

	</td>
      </tr>
    </table>
    </form>
    </td>
  </tr>
EOT;
		}
	}
	print "</table>";
	footer();
	obExit();
}

function ReorderBoards (){
	global $yytitle,$txt,$ID_CAT,$cgi,$color,$imagesdir,$db_prefix;
	$yytitle = $txt[46];
	template_header();
	print <<<EOT
<table border=0 width="400" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    <img src="$imagesdir/board.gif">
    <font size=2 class="text1" color="$color[titletext]"><b>$txt[46]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" valign=top align="center"><font size=2>
   <form action="$cgi;action=reorderboards2" method="POST">
	<table border=0 align=center>
EOT;
	$request = mysql_query("SELECT name,ID_BOARD,boardOrder FROM {$db_prefix}boards WHERE ID_CAT=$ID_CAT ORDER BY boardOrder");
while ($row = mysql_fetch_assoc($request))
	print "<tr><td><font size=2>$row[name]</font></td><td>&nbsp;&nbsp;&nbsp;#<input type=text size=5 name=\"boardOrder$row[ID_BOARD]\" value=\"$row[boardOrder]\"></td></tr>\n";
print<<<EOT
	</table>
    <input type=hidden name="ID_CAT" value="$ID_CAT">
    <input type=submit value="$txt[46]">
    </form>
    </font></td>
  </tr>
</table>
EOT;
	footer();
	obExit();
}

function ReorderBoards2 (){
	global $HTTP_POST_VARS,$ID_CAT,$cgi,$yySetLocation,$db_prefix;
	$boardOrders = array();
	foreach($HTTP_POST_VARS as $key => $value) {
		if (substr($key,0,10)=='boardOrder')
			$request = mysql_query("UPDATE {$db_prefix}boards SET boardOrder=$value WHERE ID_BOARD=".substr($key,10));
	}
	$yySetLocation = "$cgi;action=reorderboards;ID_CAT=$ID_CAT";
	redirectexit();
}


function ModifyBoard (){
	global $descr,$boardname,$moderator,$ID_BOARD,$yySetLocation,$moda,$txt,$cgi,$db_prefix,$changecat,$isAnnouncement,$notifyAnnouncements,$count;
	if($moda != '-1') {

        if ($changecat!="nochange"){

            $result = mysql_query("UPDATE {$db_prefix}boards SET ID_CAT='$changecat' WHERE ID_BOARD=$ID_BOARD");
        }
        if (get_magic_quotes_gpc()==0) {
        $boardname = mysql_escape_string($boardname);
        $descr = mysql_escape_string($descr);
        }
		$isAnnouncement = ($isAnnouncement == 'on')?'1':'0';
		$notifyAnnouncements = ($notifyAnnouncements == 'on' && $isAnnouncement == '1')?'1':'0';
		$count = ($count == 'on')?'1':'0';
		$request = mysql_query("UPDATE {$db_prefix}boards SET name='$boardname',description='$descr',moderators='$moderator',isAnnouncement='$isAnnouncement',notifyAnnouncements='$notifyAnnouncements',count='$count' WHERE ID_BOARD=$ID_BOARD");
		$yySetLocation = "$cgi;action=manageboards";
		redirectexit();
	}
	else
	{
		template_header();
		print "$txt[48] $ID_BOARD<br>";

        //Lines 212-220 all there to delete attachments on board deletion - Jeff
        $request = mysql_query("SELECT m.attachmentFilename FROM {$db_prefix}messages as m, {$db_prefix}topics as t WHERE m.ID_TOPIC=t.ID_TOPIC AND t.ID_BOARD=$ID_BOARD AND attachmentFilename<>NULL");

        if (mysql_numrows($request)>0){
        while($row = mysql_fetch_array($request)){
        unlink($modSettings['attachmentUploadDir'] . "/" . $row['attachmentFilename']);

        }
       }
		$request2 = mysql_query("DELETE FROM {$db_prefix}boards WHERE ID_BOARD=$ID_BOARD");
		$request2 = mysql_query("SELECT ID_TOPIC FROM {$db_prefix}topics WHERE ID_BOARD=$ID_BOARD");
		while ($row2 = mysql_fetch_row($request2))
		{
			print "&nbsp;&nbsp;$txt[50] $row2[0]<br>";
			$request3 = mysql_query("DELETE FROM {$db_prefix}topics WHERE ID_TOPIC=$row2[0]");
			print "&nbsp;&nbsp;&nbsp;&nbsp;$txt[49]<br>";
			$request3 = mysql_query("DELETE FROM {$db_prefix}messages WHERE ID_TOPIC=$row2[0]");
			print "&nbsp;&nbsp;$txt[51]<br>";
		}
		print "$txt[51]<br> <br>";
		print "<font size=2><a href=\"$cgi;action=manageboards\">$txt[4]</a><br><a href=\"$cgi;action=admin\">$txt[208]</a></font><br>";
		footer();
	}
	obExit();
}

function CreateBoard (){
	global $boardname,$descr,$moderator,$yySetLocation,$cgi,$ID_CAT,$db_prefix,$isAnnouncement,$count;

	$isAnnouncement = isset($isAnnouncement)?1:0;
	$count = isset($count)?1:0;

   if (get_magic_quotes_gpc()==0) {
        $boardname = mysql_escape_string($boardname);
        $descr = mysql_escape_string($descr);
   }
	$request = mysql_query("INSERT INTO {$db_prefix}boards (name,description,moderators,ID_CAT,isAnnouncement,count) VALUES ('$boardname','$descr','$moderator',$ID_CAT,$isAnnouncement,$count)");
	$yySetLocation = "$cgi;action=manageboards";
	redirectexit();
}

?>
