<?php
/*****************************************************************************/
/* Reminder.php                                                              */
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

$notifyplver="YaBB SE 1.3.1";

function Notify (){
	global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
	if( $board == '' ) { fatal_error($txt[1]); }
	if($username == "Guest") { fatal_error("$txt[138]"); }
	$yytitle = "$txt[125]";
	template_header();

	$request = mysql_query("SELECT ID_TOPIC FROM {$db_prefix}topics WHERE (ID_TOPIC=$threadid && FIND_IN_SET('$ID_MEMBER',notifies) != 0) LIMIT 1");

	if (mysql_num_rows($request) != 0){

	print <<<EOT
<table border="0" width="100%" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[125]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]"><font size=2>
    $txt[212]<br>
    <b><a href="$cgi;action=notify3;threadid=$threadid;start=$start">$txt[163]</a> - <a href="$cgi;action=display;threadid=$threadid;start=$start">$txt[164]</a></b>
    </font></td>
  </tr>
</table>
EOT;

	}
	else
	{

	print <<<EOT
<table border="0" width="100%" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[125]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]"><font size=2>
    $txt[126]<br>
    <b><a href="$cgi;action=notify2;threadid=$threadid;start=$start">$txt[163]</a> - <a href="$cgi;action=display;threadid=$threadid;start=$start">$txt[164]</a></b>
    </font></td>
  </tr>
</table>
EOT;
	}

	footer();
	obExit();
}

function Notify2 (){
	global $board,$username,$txt,$start,$cgi,$ID_MEMBER,$yySetLocation,$threadid,$db_prefix,$doLimitOne;
	if ($board == '') { fatal_error($txt[1]); }
	if ($username == 'Guest') { fatal_error($txt[138]); }

	$request = mysql_query("SELECT notifies FROM {$db_prefix}topics WHERE (ID_TOPIC=$threadid) LIMIT 1");
	list ($notification) = mysql_fetch_row($request);
	$notifies = explode(",",$notification);
	$notifications2 = array();
	foreach ($notifies as $note) {
		if ($note!= $ID_MEMBER && $note!='')
			$notifications2[]=$note;
	}
	if (!in_array($ID_MEMBER,$notifications2)) {
		$notifications2[] = $ID_MEMBER;
	}
	$notification = implode(",",$notifications2);
	$request = mysql_query("UPDATE {$db_prefix}topics SET notifies='$notification' WHERE ID_TOPIC=$threadid $doLimitOne");
//	$yySetLocation = "$cgi;action=display;threadid=$threadid;start=$start";
	$yySetLocation = "$cgi";
	redirectexit();
}

function Notify3 (){
	global $board,$txt,$yySetLocation,$cgi,$ID_MEMBER,$username,$threadid,$db_prefix,$doLimitOne;
	if( $board == '' ) { fatal_error($txt[1]); }
	if($username == "Guest") { fatal_error($txt[138]); }

	$request = mysql_query("SELECT notifies FROM {$db_prefix}topics WHERE (ID_TOPIC=$threadid) LIMIT 1");
	list ($notification) = mysql_fetch_row($request);
	$notifies = explode(",",$notification);
	$notifications2 = array();
	foreach ($notifies as $note) {
		if ($note!= $ID_MEMBER && $note!='')
			$notifications2[]=$note;
	}
	$notification = implode(",",$notifications2);
	$request = mysql_query("UPDATE {$db_prefix}topics SET notifies='$notification' WHERE ID_TOPIC=$threadid $doLimitOne");
	$yySetLocation = "$cgi;action=display;threadid=$threadid;start=$start";
	redirectexit();
}

function Notify4 (){
	global $username,$txt,$HTTP_POST_VARS,$ID_MEMBER,$db_prefix,$doLimitOne;
	if($username == "Guest") { fatal_error($txt[138]); }

	foreach ($HTTP_POST_VARS as $key=>$value)
	{
		if (substr($key,0,6)=='topic-' && $value)
		{
			$topic = substr($key,6);
			$request = mysql_query("SELECT notifies FROM {$db_prefix}topics WHERE (ID_TOPIC=$topic) LIMIT 1");
			list ($notification) = mysql_fetch_row($request);
			$notifies = explode(",",$notification);
			$notifications2 = array();
			foreach ($notifies as $note) {
				if ($note!= $ID_MEMBER)
				$notifications2[]=$note;
			}
			$notification = implode(",",$notifications2);
			$request = mysql_query("UPDATE {$db_prefix}topics SET notifies='$notification' WHERE ID_TOPIC=$topic $doLimitOne");
		}
	}
	Shownotifications();
}

function NotifyXSettings() {
	global $username,$txt,$ID_MEMBER,$db_prefix,$doLimitOne,$notifyAnnouncements,$notifyOnce,$cgi,$yySetLocation;
	if($username == "Guest") { fatal_error($txt[138]); }

	$notifyAnnouncements = ($notifyAnnouncements=='on'?1:0);
	$notifyOnce = ($notifyOnce=='on'?1:0);

	$request = mysql_query("UPDATE {$db_prefix}members SET notifyAnnouncements=$notifyAnnouncements, notifyOnce=$notifyOnce WHERE ID_MEMBER=$ID_MEMBER $doLimitOne");

	$yySetLocation = "$cgi;action=shownotify";
	redirectexit();
}

function Shownotifications (){
	global $username,$txt,$ID_MEMBER,$censored,$yytitle,$board,$color,$cgi,$scripturl,$db_prefix,$modSettings,$mbname,$imagesdir;
	if($username == "Guest") { fatal_error($txt[138]); }
	$request = mysql_query("SELECT t.ID_TOPIC,t.ID_BOARD,m.subject,m.posterName,m.ID_MEMBER FROM {$db_prefix}topics as t, {$db_prefix}messages as m WHERE (FIND_IN_SET($ID_MEMBER,t.notifies) != 0 && m.ID_MSG=t.ID_FIRST_MSG)");

	# Build the link tree
	$displayLinkTree = $modSettings['enableInlineLinks'] ? "<font size=\"1\" class=\"nav\"><B><a href=\"$cgi\" class=\"nav\">$mbname</a> </b>&nbsp;|&nbsp;<b> " : "<font size=\"2\" class=\"nav\"><B><img src=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$cgi\" class=\"nav\">$mbname</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<a href=\"$cgi?action=shownotify\" class=\"nav\">$txt[417]</a> </b>&nbsp;|&nbsp;<b> " : "<img src=\"$imagesdir/tline.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$cgi?action=shownotify\" class=\"nav\">$txt[417]</a><br>" ;
	$yytitle = "$mbname - $txt[417]";
	template_header();

	print <<<EOT
<table width="100%" align="center"><tr><td valign="bottom">$displayLinkTree</td></tr></table>
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor"><tr><td>
<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
  <tr>
    <td class="titlebg" align="center" colspan="2">
    <b>$mbname - $txt[418]</b></td>
  </tr>
EOT;
	//Start - Code by Omar Bazavilvazo

	$reqNotifySettings = mysql_query("SELECT notifyAnnouncements, notifyOnce FROM {$db_prefix}members WHERE ID_MEMBER=$ID_MEMBER");
	$rowNotifySettings = mysql_fetch_array($reqNotifySettings);
	$NotAnnChecked = ($rowNotifySettings['notifyAnnouncements'] ==1?' checked':'');
	$NotOnceChecked = ($rowNotifySettings['notifyOnce'] ==1?' checked':'');

	if ($modSettings['notifyAnncmnts_UserDisable']=='1') {
		$notifyAnnTxt = "
<tr><td><font size=2><input type=checkbox name=\"notifyAnnouncements\" $NotAnnChecked></font></td>
<td><font size=2>$txt[notifyXAnn4]</font></td></tr>";
	}
	else
		$notifyAnnTxt = "";

	print <<<EOT
<tr><td class="catbg" colspan="2"><b>$txt[notifyX]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/boardmod_main.gif" border="0" width="20" height="20" alt=""></td><td class="windowbg2" width="100%">
<form action="$cgi;action=notifyXSettings" method="post">
<table>
$notifyAnnTxt
<tr><td><font size=2><input type=checkbox name="notifyOnce" $NotOnceChecked></font></td>
<td><font size=2>$txt[notifyXOnce1]</font></td></tr>
<tr><td>&nbsp;</td><td><input type="submit" value="$txt[notifyX1]"></td></tr>
</table>
</form></td></tr><tr><td class="catbg" colspan="2"><b>$txt[417]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/notify_icon.gif" border="0" width="20" height="20" alt=""></td><td class="windowbg2" width="100%">
EOT;
	//End - Code by Omar Bazavilvazo

	if (mysql_num_rows($request)==0)
		print "<font size=\"2\">$txt[414]<br><br>&nbsp;</font>";
	else
	{
		print "<form action=\"$cgi;action=notify4\" method=\"post\">";
		print "<table>\n";
		print "<tr><td colspan=2><font size=2>$txt[415]:</font><br>&nbsp;</td></tr>";

		while ($row = mysql_fetch_assoc($request))
		{
			CensorTxt($row['subject']);

			print "<tr><td><font size=2>";
			print "<input type=checkbox name=\"topic-$row[ID_TOPIC]\" value=\"1\"></font></td>";
			if ($row['ID_MEMBER'] != -1)
			{
				$request2 = mysql_query("SELECT realName,memberName FROM {$db_prefix}members WHERE ID_MEMBER=$row[ID_MEMBER] LIMIT 1");
				$row2 = mysql_fetch_array($request2);
				$euser=urlencode($row2['memberName']);
				print "<td><font size=2><b><i><a href=\"$scripturl?board=$row[ID_BOARD];action=display;threadid=$row[ID_TOPIC]\">$row[subject]</a></i></b> $txt[525] <a href=\"$scripturl?board=$board;action=viewprofile;user=$euser\">$row2[realName]</a></font></td></tr>\n";
			}
			else
			{
				print "<td><font size=2><b><i><a href=\"$scripturl?board=$row[ID_BOARD];action=display;threadid=$row[ID_TOPIC]\">$row[subject]</a></i></b> $txt[525] $row[posterName]</font></td></tr>\n";
			}
		}
		print "<tr><td colspan=2><br><font size=2>$txt[416]</font><br>&nbsp;</td></tr>\n";
		print "<tr><td>&nbsp;</td><td><input type=reset value=\"$txt[329]\">&nbsp;&nbsp;&nbsp;<input type=submit value=\"$txt[417]\"></td></tr>";
		print "</table></form><br>&nbsp;\n";
	}

	print <<<EOT
  </td>
 </tr>
</table>
  </td>
 </tr>
</table>
EOT;
	footer();
	obExit();

}

?>
