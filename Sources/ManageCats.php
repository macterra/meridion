<?php
/*****************************************************************************/
/* ManageCats.php                                                            */
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

$managecatsplver="YaBB SE 1.3.0";

// verify the user is an admin
is_admin();
function ManageCats (){
	global $yytitle,$cgi,$txt,$img,$color,$imagesdir,$db_prefix;
	$categories = array();

	$request = mysql_query("SELECT membergroup FROM {$db_prefix}membergroups WHERE (ID_GROUP=1 OR ID_GROUP > 7)");
	$membergrps = '';
	while ($row = mysql_fetch_row($request))
		$membergrps .= "<option>".trim($row[0])."</option>";

	template_header();
	print <<<EOT
<table border="0" cellspacing="1" cellpadding="4" bgcolor="$color[bordercolor]" class="bordercolor" align="center" width="100%">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]" colspan="2">
	<script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		function memberGroups(theForm)
		{
		if (window.popup)
			popup.close();
		popup = window.open("","membergroups","toolbar=no,location=no,status=no,menubar=no,scrollbars=no,width=280,height=300,resizable=no");
		popup.document.write("<html><head><title>$txt[57]</title>\\n");
		popup.document.write("<script language='javascript' TYPE='text/javascript'>\\n");
		popup.document.write("function changeSelectedGroups(listObject) {\\n");
		popup.document.write("var newMemberGroups = '';\\n");
		popup.document.write("for (var i = 0; i < listObject.length; i++){\\n");
		popup.document.write("if (listObject.options[i].selected){\\n");
		popup.document.write("newMemberGroups += listObject.options[i].text +',';\\n");
		popup.document.write("}\\n}\\n");
		popup.document.write("if (newMemberGroups.length > 0){\\n");
		popup.document.write("newMemberGroups=newMemberGroups.substr(0,newMemberGroups.length-1)");
		popup.document.write("}\\nwindow.opener."+theForm+".catgroups.value=newMemberGroups;\\n}");
		popup.document.write("\\n</script>\\n</head>\\n<body>\\n");
		popup.document.write("<font face='verdana,arial' size=2><b>$txt[57]</b><br>$txt[yse55]\\n");
		popup.document.write("<p align=center><form name='form1'><select size=6 name='myOptions' multiple onchange='changeSelectedGroups(this)'>\\n");
		popup.document.write("$membergrps\\n");
		popup.document.write("</select><br> <br> <input type=button onclick=\"window.close()\" value=\"$txt[17]\"></form></p></font></body></html>");
		}
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=0')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size=2 class="text1" color="$color[titletext]"><b>$txt[52]</b></font></td>
  </tr><tr>
    <td colspan="2" bgcolor="$color[catbg]" class="catbg" height="25">
	<font size="2">
    <b>$txt[56]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" valign="top">
	<!-- Begin Create Cat Code -->
    <form action="$cgi;action=createcat" method="POST"><table width="100%"><tr><td><font size=2>
    <B>$txt[44]:</B></font><BR><font size="1">$txt[672]</font></td>
    <td class="windowbg" bgcolor="$color[windowbg]" valign="top"><font size=2>
	<input type="text" size="40" name="catname"></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" valign="top" width="50%"><font size=2>
    <B>$txt[57]:</B></font><BR><font size="1">$txt[673]</font></td>
    <td class="windowbg" bgcolor="$color[windowbg]" valign="top" width="50%"><font size=2>
    <input type="text" size="40" name="memgroup"><BR></font><font size="1">($txt[58])</font><br><BR>
    <input type="submit" value="$txt[59]">
	</td></tr></table></form>
	<!-- Stop Create Cat Code -->
    </td>
  </tr>
</table>

EOT;
	$request = mysql_query("SELECT name,ID_CAT,memberGroups,catOrder FROM {$db_prefix}categories WHERE 1 ORDER BY catOrder");
	while ($curcat = mysql_fetch_assoc($request)) {
		print <<<EOT
	<!-- Begin Modify Cat Code -->
	<form name="cat$curcat[ID_CAT]" action="$cgi;action=modifycat" method="POST">
   <table border="0" cellspacing="1" cellpadding="4" bgcolor="$color[bordercolor]" class="bordercolor" align="center" width="100%">
	<tr>
    <td colspan="2" valign="top" bgcolor="$color[catbg]" class="catbg" height="25">

		<font size="2"><b>$txt[44]: <input type="text" value="$curcat[name]" size="25" name="catname"><input type=hidden name="ID_CAT" value="$curcat[ID_CAT]"></B></font></td>
		</tr><tr>
		<td bgcolor="$color[windowbg2]" class="windowbg2" colspan="2">
			<table border="0" cellpadding="1" cellspacing="0" width="100%">
			<tr>
			<td valign="middle"><font size="2">
			$txt[43]:<BR> <input type="text" value="$curcat[catOrder]" size="5" name="catorder"></font></td>
			<td valign="middle"><font size="2">
			$txt[57]:<BR> <input type="text" value="$curcat[memberGroups]" size="40" name="catgroups">  <a href="javascript:memberGroups('cat$curcat[ID_CAT]');"><img src="$imagesdir/assist.gif" border="0" alt=""></a></font></td>
			<td valign="middle">
			<input type="hidden" name="id" value="$curcat[ID_CAT]">
<input type="hidden" name="moda" value="1">
	<input type=button value="$txt[17]" onclick="cat$curcat[ID_CAT].moda.value='1'; submit();"> 
	<input type=button value="$txt[31]" onclick="if(confirm('$txt[catConfirm]')) { cat$curcat[ID_CAT].moda.value='-1'; submit(); }">
			</td>
			</tr>
			</table>
    </td>
  </tr></table></form>
EOT;
	}
print "\n";
	footer();
	obExit();
}

function CreateCat (){
	global $catname,$memgroup,$yySetLocation,$cgi,$db_prefix;
    if (get_magic_quotes_gpc()==0) {
        $catname = mysql_escape_string($catname);
        }
	$request = mysql_query("INSERT INTO {$db_prefix}categories (name,memberGroups,catOrder) VALUES ('$catname','$memgroup',0)");
	$yySetLocation = "$cgi;action=managecats";
	redirectexit();
}

function ModifyCat (){
	global $moda,$txt,$catname,$catgroups,$catorder,$ID_CAT,$yySetLocation,$cgi,$db_prefix;
	if($moda != '-1') {
        if (get_magic_quotes_gpc()==0) {
        $catname = mysql_escape_string($catname);
        }
		$request = mysql_query("UPDATE {$db_prefix}categories SET name='$catname',memberGroups='$catgroups',catOrder='$catorder' WHERE ID_CAT=$ID_CAT");
		$yySetLocation = "$cgi;action=managecats";
		redirectexit();
	}
	else {
		template_header();
		print "$txt[60] $ID_CAT<br>";
  
        //Lines 174-182 all there to delete attachments on category deletion - Jeff
        $request = mysql_query("SELECT m.attachmentFilename FROM {$db_prefix}messages as m, {$db_prefix}topics as t, {$db_prefix}boards as b WHERE (m.ID_TOPIC=t.ID_TOPIC AND t.ID_BOARD=b.ID_BOARD AND b.ID_CAT=$ID_CAT AND m.attachmentFilename<>NULL)");

        if (mysql_numrows($request)>0){
	        while($row = mysql_fetch_array($request)){
			    unlink($modSettings['attachmentUploadDir'] . "/" . $row['attachmentFilename']);
			}
		}
		$request = mysql_query("DELETE FROM {$db_prefix}categories WHERE ID_CAT=$ID_CAT");
		$request = mysql_query("SELECT ID_BOARD FROM {$db_prefix}boards WHERE ID_CAT=$ID_CAT");

		while($row = mysql_fetch_row($request))
		{
			print "&nbsp;&nbsp;$txt[48] $row[0]<br>";
			$request2 = mysql_query("DELETE FROM {$db_prefix}boards WHERE ID_BOARD=$row[0]");
			$request2 = mysql_query("SELECT ID_TOPIC FROM {$db_prefix}topics WHERE ID_BOARD=$row[0]");
			while ($row2 = mysql_fetch_row($request2))
			{
				print "&nbsp;&nbsp;&nbsp;&nbsp;$txt[50] $row2[0]<br>";
				$request3 = mysql_query("DELETE FROM {$db_prefix}topics WHERE ID_TOPIC=$row2[0]");
				print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$txt[49]<br>";
				$request3 = mysql_query("DELETE FROM {$db_prefix}messages WHERE ID_TOPIC=$row2[0]");
				print "&nbsp;&nbsp;&nbsp;&nbsp;$txt[51]<br>";
			}
			print "&nbsp;&nbsp;$txt[51]<br>";
		}
		print "$txt[51]<br> <br>";
		print "<font size=2><a href=\"$cgi;action=managecats\">$txt[3]</a><br><a href=\"$cgi;action=admin\">$txt[208]</a></font><br>";
		footer();
	obExit();
	}
}
?>
