<?php
/*****************************************************************************/
/* Admin.php                                                                 */
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

global $adminplver;
$adminplver="YaBB SE 1.3.0";


// verify the user is an administrator
is_admin();

function Admin (){
	global $yytitle,$YaBBversion,$txt,$img,$scripturl,$color,$imagesdir,$settings,$username;
	global $realName,$cgi,$sourcedir,$recentsender,$db_prefix;

	// get the latest member stuff
	$request = mysql_query("SELECT memberName,realName FROM {$db_prefix}members ORDER BY dateRegistered DESC LIMIT 1");
	$temp = mysql_fetch_array($request);
	$name = (!isset($temp['realName']) || $temp['realName']=='')?$temp['memberName']:$temp['realName'];

	$euser=urlencode($temp['memberName']);
	$thelatestmember = "<font size=2><B>$txt[656]</B></font> <font size=1><a href=\"$scripturl?action=viewprofile;user=$euser\">$name</a></font>";

	// get the number of members
	$request = mysql_query("SELECT COUNT(*) as memcount FROM {$db_prefix}members");
	$temp = mysql_fetch_array($request);
	$memcount = $temp[0];

	// Load data for the 'remove old messages' feature, get totals, and get moderators
	$request = mysql_query("SELECT value FROM {$db_prefix}settings WHERE variable='maxdays'");
	$temp = mysql_fetch_array($request);
	$maxdays = $temp[0];

	$result = mysql_query("SELECT COUNT(*) as memcount FROM {$db_prefix}members;");
	$temp = mysql_fetch_row($result);
    $memcount = $temp[0];
	$result = mysql_query("SELECT COUNT(*) as totalm FROM {$db_prefix}messages;");
	$temp = mysql_fetch_row($result);
    $totalm = $temp[0];
	$result = mysql_query("SELECT COUNT(*) as totalt FROM {$db_prefix}topics;");
	$temp = mysql_fetch_row($result);
    $totalt = $temp[0];
	$result = mysql_query("SELECT COUNT(*) as totalb FROM {$db_prefix}boards;");
	$temp = mysql_fetch_row($result);
    $numboards = $temp[0];
	$result = mysql_query("SELECT COUNT(*) as totalc FROM {$db_prefix}categories;");
	$temp = mysql_fetch_row($result);
    $numcats = $temp[0];
	$result = mysql_query("SELECT COUNT(*) as click_total FROM {$db_prefix}log_clicks;");
	$temp = mysql_fetch_row($result);
    $yyclicks = $temp[0];

//	$avgt = $totalt / $memcount;
//	$avgm = $totalm / $memcount;  why the 'heck' (here's to StarSaber) are these needed?

	// load the administrators
	$request = mysql_query("SELECT memberName,realName FROM {$db_prefix}members WHERE memberGroup='Administrator'");
	$admins = array();
	while ($row = mysql_fetch_array($request)) {
		$euser=urlencode($row['memberName']);
		$admins[] = "<a href=\"$scripturl?action=viewprofile;user=$euser\">$row[realName]</a>";
	}
	$administrators = implode(",&nbsp;",$admins);

	$yytitle = "$txt[208]";
	template_header();
	print <<<EOT
		<script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
<table border="0" cellpadding="0" cellspacing="6" align="center" width="100%">
  <tr>
    <td colspan=2 width="100%">
    <table border="0" cellpadding="5" cellspacing="1" align="center" bgcolor="$color[bordercolor]" class="bordercolor" width="100%">
      <tr>
        <td bgcolor="$color[titlebg]" class="titlebg" height="23" align="center" colspan="2">
        <font size="4" color="$color[titletext]">$txt[208]</font></td>
      </tr><tr>
        <td class="windowbg" bgcolor="$color[windowbg]" valign="top" align="center" width="50%">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr><td colspan=2><a href="javascript:reqWin('help.php?help=13')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a> <b>$txt[yse217]</td></tr>
		<tr><td colspan="2"><img src="$imagesdir/blank.gif" height="6" width="4" alt=""></td></tr>
EOT;
	$yabbinfo = array(); //getYaBBinfo();
	if (!$yabbinfo)
	{
		print "<tr><td colspan=2>$txt[lfyi]</td></tr>";
	}
	foreach($yabbinfo as $info){
		print "<tr><td colspan=\"2\"><font size=\"1\"><a href=\"$info[url]\">$info[subject]</a> | $info[author] | ".timeformat($info['logTime'])."</font></td></tr>\n";
		print "<tr><td><font size=\"1\">&nbsp;&nbsp;&nbsp;&nbsp;</font></td><td><font size=\"1\">$info[details]</font></td></tr>\n";
		print "<tr><td colspan=\"2\"><img src=\"$imagesdir/blank.gif\" height=\"6\" width=\"4\" alt=\"\"></td></tr>\n";
	}
print<<<EOT
		</table>
        </td>
        <td class="windowbg" bgcolor="$color[windowbg]" width="50%" valign=top>
        <font size="2"><B>$txt[248] $settings[1] ($username)!</B></font>
        <font size="1"><BR>$txt[644]</font></td>
      </tr>
    </table>
    </td>
  </tr><tr>
    <td valign="top" width="50%">
    <table border="0" cellpadding="4" cellspacing="1" align="center" bgcolor="$color[bordercolor]" class="bordercolor" width="100%">
      <tr>
        <td class="catbg" bgcolor="$color[catbg]"><font size="4" class="catbg">$txt[424]</font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" height="21" class="windowbg">
        <img src="$imagesdir/board.gif" alt="" border="0"> <font size="3"><b>$txt[427]</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg2]" height="21" class="windowbg2"><font size="2">
        <a href="$cgi;action=editnews">$txt[7]</a><br>
        <a href="$cgi;action=editagreement">$txt[yse11]</a><br>
        <a href="$cgi;action=managecats">$txt[3]</a><br>
        <a href="$cgi;action=manageboards">$txt[4]</a><br>
        <a href="$cgi;action=packages">$txt[package1]</a><br>
		<a href="$cgi;action=manageattachments">$txt[yse201]</a><br><br></font>
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" height="21" class="windowbg">
        <img src="$imagesdir/board.gif" alt="" border="0"> <font size="3"><b>$txt[426]</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg2]" height="21" class="windowbg2"><font size="2">
        <a href="$cgi;action=viewmembers">$txt[5]</a><br>
        <a href="$cgi;action=modmemgr">$txt[8]</a><br>
        <a href="$cgi;action=mailing">$txt[6]</a><br>
        <a href="$cgi;action=ipban">$txt[206]</a><br>
        <a href="$cgi;action=setreserve">$txt[207]</a><br><br>
        <form action="$cgi;action=viewinactive" method="POST">
        <font size="2">$txt[yse71] <input type=text name="mindays" size="2" maxlength="3" value="30"> $txt[yse72]
        <input type="submit" value="$txt[305]"></font></form></font>
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" height="21" class="windowbg">
        <img src="$imagesdir/board.gif" alt="" border="0"> <font size="3"><b>$txt[428]</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg2]" height="21" class="windowbg2"><font size="2">
        <a href="$cgi;action=modtemp">$txt[216]</a><br>
        <a href="$cgi;action=modsettings">$txt[222]</a><br>
        <a href="$cgi;action=modifyModSettings">$txt[yse2]</a><br>
        <a href="$cgi;action=setcensor">$txt[135]</a><br><br></font>
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" height="21" class="windowbg">
        <img src="$imagesdir/board.gif" alt="" border="0"> <font size="3"><b>$txt[501]</b></font></td>
      </tr><tr>
        <td width="340" bgcolor="$color[windowbg2]" height="21" class="windowbg2"><font size="2">
        <a href="$cgi;action=repairboards">$txt[610]</a><BR>
        <a href="$cgi;action=boardrecount">$txt[502]</a><br>
        <a href="$cgi;action=clean_log">$txt[202]</a><br>
		<a href="$cgi;action=viewErrorLog">$txt[errlog1]</a><br>
        <form action="$cgi;action=removeoldthreads" method="POST">
        <font size="2">$txt[124] <input type=text name="maxdays" size="2" value="$maxdays"> $txt[579]
        <input type="submit" value="$txt[31]"></font></form>
        </td>
      </tr>
    </table>
    </td>
    <td valign="top" width="100%">
    <table border="0" cellpadding="4" cellspacing="1" align="center" bgcolor="$color[bordercolor]" class="bordercolor" width="100%">
      <tr>
        <td class="catbg" bgcolor="$color[catbg]"><font size="4" class="catbg">$txt[645]</font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" height="21">
        <img src="$imagesdir/cat.gif" alt="" border="0"> <font size="3"><B>$txt[94]</B></font><BR></td>
      </tr><tr>
	<td bgcolor="$color[windowbg2]" class="windowbg2">
	<table border="0" cellpadding="1" cellspacing="0" width="100%">
	  <tr>
            <td><font size="2">$txt[488]</font></td>
            <td align="right"><font size="2">$memcount</font></td>
          </tr><tr>
            <td><font size="2">$txt[489]</font></td>
            <td align="right"><font size="2">$totalm</font></td>
          </tr><tr>
            <td><font size="2">$txt[490]</font></td>
            <td align="right"><font size="2">$totalt</font></td>
          </tr><tr>
            <td><font size="2">$txt[658]</font></td>
            <td align="right"><font size="2">$numcats</font></td>
          </tr><tr>
            <td><font size="2">$txt[665]</font></td>
            <td align="right"><font size="2">$numboards</font></td>
          </tr><tr>
            <td><font size="2">$txt[691] <font size="1">($txt[692])</font>:</font></td>
            <td align="right"><font size="2">$yyclicks</font></td>
          </tr><tr>
            <td colspan="2"><font size="2"><a href="$scripturl?action=showclicks">$txt[693]</a></font></td>
          </tr>
        </table>
	</td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" height="21">
        <img src="$imagesdir/cat.gif" alt="" border="0"> <font size="3"><B>$txt[657]</B></font><BR></td>
      </tr><tr>
	<td bgcolor="$color[windowbg2]" class="windowbg2">
        $thelatestmember<BR>
        <font size="2"><B>$txt[659]</b></font><font size="1">
EOT;
        include_once("$sourcedir/Recent.php");
        $recentsender = "admin";
		LastPost();
	print <<<EOT
        </font><BR><BR>
	<font size="2"><B>$txt[684]:</B></font> <font size="1">$administrators</font><BR><BR>
        <font size="2"><b>$txt[425]:</b></font>
        <font size="1">$YaBBversion/<img src="http://www.yabb.info/images/yabbsever.gif"></font><BR>
        <center><font size="2"><a href="$cgi;action=detailedversion">$txt[429]</a></font></center><BR></td>
      </tr>
    </table>
		<br>
<table border="0" cellpadding="5" cellspacing="1" align="center" bgcolor="$color[bordercolor]" class="bordercolor" width="100%">
      <tr>
	<td class="catbg" bgcolor="$color[catbg]">
	<img src="$imagesdir/xx.gif" alt="" border="0">
	<font size="4" class="catbg">$txt[571]</font></td>
      </tr><tr>
        <td class="windowbg" bgcolor="$color[windowbg]">
        <font size="1"><BR><i><B>YaBB SE Dev Team:</B> </i>Joseph Fung, Jeff Lewis, Dave Baughman, Christian Land, Peter Crouch, Zef Hemel, Ted Suzman, Tim Ceuppens, Philip Renich, Alan Cramer and the rest who helped lay the foundation with YaBB 1 Final and YaBB 1 Gold.<br> <br>
		<i><B>YaBB SE Beta Testers:</B> </i>Edwin Weij, Dave Smulders, Michael Prager, John R, Tom Smid, Shaun, Shoeb Omar, Patty Breen, Chris Boston, Mohammed Alimul, Richard Bongiovanni, Andrea Hubacher, Ian Fette, Brian McClure, Darren Hedlund and Daniel Diehl<br> <br>
		<i><b>Misc:</b></i> And thanks to Alex Rolko for doing a tonne of stuff - both on and off the record.</font>
        <BR><BR></td>
      </tr>
</table>
    </td>
  </tr>
</table>
EOT;
	footer();
	obExit();
}

/****************************************/
/* FORUM CONTROLS                       */
/****************************************/
function EditNews (){
	global $yytitle,$txt,$cgi,$color,$imagesdir,$db_prefix;
	$request = mysql_query("SELECT value FROM {$db_prefix}settings WHERE variable='news'");
	$temp = mysql_fetch_row($request);
	$news = $temp[0];
	$yytitle = $txt[7];
	template_header();
	print <<<EOT
<form action="$cgi;action=editnews2" method="POST">
<table border="0" width="70%" cellspacing="1" cellpadding="3" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    	<script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=2')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[7]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]"><BR><font size="1">$txt[670]</font><BR><BR></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" align="center"><BR>
    <font size="2">
    <textarea cols="70" rows="8" name="news">$news</textarea><br><input type="submit" value="$txt[10]"></font><BR></td>
  </tr>
</table>
</form>
EOT;
	footer();
	obExit();
}

function EditNews2 (){
	global $news,$cgi,$yySetLocation,$db_prefix;
	$request = mysql_query("UPDATE {$db_prefix}settings SET value='$news' WHERE variable='news'");
	$yySetLocation = "$cgi;action=admin";
	redirectexit();
}

function EditAgreement (){
	global $yytitle,$txt,$cgi,$color,$imagesdir,$db_prefix;
	$request = mysql_query("SELECT value FROM {$db_prefix}settings WHERE variable='agreement'");
	$temp = mysql_fetch_row($request);
	$agreement = $temp[0];
	$yytitle = $txt['yse11'];
	template_header();
	print <<<EOT
<form action="$cgi;action=editagreement2" method="POST">
<table border="0" width="70%" cellspacing="1" cellpadding="3" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    	<script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=3')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[yse11]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]"><BR><font size="1">$txt[yse12]</font><BR><BR></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" align="center"><BR>
    <font size="2">
    <textarea cols="70" rows="20" name="agreement">$agreement</textarea><br><input type="submit" value="$txt[10]"></font><BR></td>
  </tr>
</table>
</form>
EOT;
	footer();
	obExit();
}

function EditAgreement2 (){
	global $agreement,$cgi,$yySetLocation,$db_prefix;
	$request = mysql_query("UPDATE {$db_prefix}settings SET value='$agreement' WHERE variable='agreement'");
	$yySetLocation = "$cgi;action=admin";
	redirectexit();
}

/****************************************/
/* MEMBER CONTROLS                      */
/****************************************/
function ViewMembers (){
	global $yytitle,$txt,$color,$cgi,$imagesdir,$db_prefix;
	$yytitle = $txt[9];
	template_header();
	print <<<EOT
<table border="0" width="300" cellspacing="1" cellpadding="2" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    <script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=4')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[9]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" align="left" width="95%">
    <form action="$cgi;action=deletemultimembers" method="POST">
    <table border="0" cellspacing="4" cellpadding="0" align="center" width="95%">
EOT;
	$request = mysql_query("SELECT memberName,realName,ID_MEMBER FROM {$db_prefix}members WHERE 1 ORDER BY memberName");
	while ($curmem = mysql_fetch_array($request)) {
		$rname = isset($curmem['realName'])?"($curmem[realName])":'';
		$euser=urlencode($curmem['memberName']);
		print "      <tr>\n        <td><font size=\"2\"><a href=\"$cgi;action=viewprofile;user=$euser\">$curmem[memberName] $rname</a></font></td><td> &nbsp;&nbsp;<input type=\"checkbox\" name=\"ID_MEMBER$curmem[ID_MEMBER]\"></td>\n      </tr>\n";
	}
	print <<<EOT
	<tr>
	<td class="windowbg" bgcolor="$color[windowbg]" align="center"><input type="submit" value="$txt[608]"></td>
  </tr>
</table>
</form>
</td>
  </tr>
    </table>

EOT;
	footer();
	obExit();
}

function ViewInactiveMembers (){
	global $yytitle,$txt,$color,$cgi,$imagesdir,$db_prefix,$mindays;
	$yytitle = $txt[9];
	template_header();
    $daytime = strtotime("-$mindays days");

	print <<<EOT
<table border="0" width="300" cellspacing="1" cellpadding="2" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    <script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=4')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[yse99]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" align="left" width="95%">
    <form action="$cgi;action=deletemultimembers" method="POST">
    <table border="0" cellspacing="4" cellpadding="0" align="center" width="95%">
EOT;
	//Fixed by Omar Bazavilvazo - Incorrect calculation
	$sQuery = "SELECT {$db_prefix}members.memberName, {$db_prefix}members.realName, {$db_prefix}members.ID_MEMBER, IFNULL({$db_prefix}members.lastLogin, 0) as lastLogin, IFNULL(Max({$db_prefix}log_boards.logTime), 0) as log_boards, IFNULL(Max({$db_prefix}log_topics.logTime), 0) as log_topics, IFNULL(Max({$db_prefix}log_errors.logTime), 0) as log_errors, IFNULL(Max({$db_prefix}log_mark_read.logTime), 0) as log_mark_read ";
	$sQuery .= "FROM ((({$db_prefix}members LEFT JOIN {$db_prefix}log_boards ON {$db_prefix}members.memberName = {$db_prefix}log_boards.memberName) LEFT JOIN {$db_prefix}log_topics ON {$db_prefix}members.memberName = {$db_prefix}log_topics.memberName) LEFT JOIN {$db_prefix}log_errors ON {$db_prefix}members.memberName = {$db_prefix}log_errors.memberName) LEFT JOIN {$db_prefix}log_mark_read ON {$db_prefix}members.memberName = {$db_prefix}log_mark_read.memberName ";
	$sQuery .= "GROUP BY {$db_prefix}members.memberName ";
	$sQuery .= "HAVING (((lastLogin) <= $daytime) AND ((log_boards) <= $daytime) AND ((log_topics) <= $daytime) AND ((log_errors) <= $daytime) AND ((log_mark_read) <= $daytime)) ";
	$sQuery .= "ORDER BY {$db_prefix}members.memberName;";

	$request = mysql_query($sQuery);
	while ($curmem = mysql_fetch_array($request)) {
		$lastLogin = max($curmem['lastLogin'], $curmem['logs_boards'], $curmem['log_topics'], $curmem['log_errors'], $curmem['log_mark_read']);
		$rname = isset($curmem['realName'])?"($curmem[realName])":'';
		$difference=jeffsdatediff(time(), $lastLogin);
		$euser=urlencode($curmem[memberName]);
		print "      <tr>\n        <td><font size=\"2\"><a href=\"$cgi;action=viewprofile;user=$euser\">$curmem[memberName] $rname</a></font></td><td>&nbsp; $difference &nbsp;</td><td> &nbsp;&nbsp;<input type=\"checkbox\" name=\"ID_MEMBER$curmem[ID_MEMBER]\"></td>\n      </tr>\n";
	}
	print <<<EOT
	<tr>
	<td class="windowbg" bgcolor="$color[windowbg]" align="center"><input type="submit" value="$txt[608]"></td>
  </tr>
</table>
</form>
</td>
  </tr>
    </table>

EOT;
	footer();
	obExit();
}

function DeleteMultiMembers (){
	global $HTTP_POST_VARS,$yySetLocation,$scripturl,$db_prefix;
	foreach($HTTP_POST_VARS as $key => $value)
		if (substr($key,0,9)=='ID_MEMBER' && $value=='on'){
			$request = mysql_query("SELECT memberName FROM {$db_prefix}members WHERE ID_MEMBER=".substr($key,9)." LIMIT 1");
			$row = mysql_fetch_row($request);
			$request = mysql_query("UPDATE {$db_prefix}messages SET ID_MEMBER='-1' WHERE ID_MEMBER='".substr($key,9)."'");
			$request = mysql_query("DELETE FROM {$db_prefix}members WHERE ID_MEMBER=".substr($key,9)." LIMIT 1");
			$request = mysql_query("DELETE FROM {$db_prefix}log_topics WHERE memberName='$row[0]'");
			$request = mysql_query("DELETE FROM {$db_prefix}log_boards WHERE memberName='$row[0]'");
			$request = mysql_query("DELETE FROM {$db_prefix}log_mark_read WHERE memberName='$row[0]'");
			$request = mysql_query("DELETE FROM {$db_prefix}instant_messages WHERE (ID_MEMBER_TO=".substr($key,9)." AND deletedBy=0)");
			$request = mysql_query("DELETE FROM {$db_prefix}instant_messages WHERE (ID_MEMBER_FROM=".substr($key,9)." AND deletedBy=1)");
			$request = mysql_query("UPDATE {$db_prefix}instant_messages SET deletedBy=1 WHERE ID_MEMBER_TO=".substr($key,9));
			$request = mysql_query("UPDATE {$db_prefix}instant_messages SET deletedBy=0 WHERE ID_MEMBER_FROM=".substr($key,9));
		}
	$yySetLocation = "$scripturl?action=admin";
	redirectexit();
}

function EditMemberGroups (){
	global $yytitle,$txt,$color,$imagesdir,$cgi,$db_prefix;
	$yytitle = $txt[8];
	template_header();
	$request = mysql_query("SELECT ID_GROUP,membergroup FROM {$db_prefix}membergroups WHERE 1 ORDER BY ID_GROUP");
	$groups = array();
	$additional  = '';
	while ($groups[] = mysql_fetch_array($request)){;}
	for ($i = 8; $i < sizeof($groups); $i ++)
		$additional .= "{$groups[$i]['membergroup']}\n";
	$additional = trim($additional);
	print <<<EOT
<table border="0" width="600" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor" align="center" cellpadding="4">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    <script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=5')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[8]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <form action="$cgi;action=modmemgr2" method="POST">
    <table border="0" cellpadding="1" cellspacing="0">
      <tr>
        <td align="right"><font size="2"><b>$txt[11]:</b></font></td>
        <td><input type="text" name="admin" size=30 value="{$groups[0]['membergroup']}"></td>
      </tr><tr>
        <td align="right"><font size="2"><b>Global Moderator:</b></font></td>
        <td><input type="text" name="globalmod" size="30" value="{$groups[7]['membergroup']}"></td>
      </tr><tr>
        <td align="right"><font size="2"><b>$txt[12]:</b></font></td>
        <td><input type="text" name="moderator" size="30" value="{$groups[1]['membergroup']}"></td>
      </tr><tr>
        <td align="right"><font size="2"><b>$txt[569]:</b></font></td>
        <td><input type="text" name="newbie" size="30" value="{$groups[2]['membergroup']}"></td>
      </tr><tr>
        <td align="right"><font size="2"><b>$txt[13]:</b></font></td>
        <td><input type="text" name="junior" size="30" value="{$groups[3]['membergroup']}"></td>
      </tr><tr>
        <td align="right"><font size="2"><b>$txt[14]:</b></font></td>
        <td><input type="text" name="full" size="30" value="{$groups[4]['membergroup']}"></td>
      </tr><tr>
        <td align="right"><font size="2"><b>$txt[15]:</b></font></td>
        <td><input type="text" name="senior" size="30" value="{$groups[5]['membergroup']}"></td>
      </tr><tr>
        <td align="right"><font size="2"><b>$txt[570]:</b></font></td>
        <td><input type="text" name="god" size="30" value="{$groups[6]['membergroup']}"></td>
      </tr><tr>
        <td align="right"><font size="2"><B>$txt[16]:</b></font></td>
        <td><textarea name="additional" cols="30" rows="5">$additional</textarea><BR>
        <center><input type="submit" value="$txt[10]"></center></td>
      </tr>
    </table>
    </form>
    </td>
  </tr>
</table>
EOT;
	footer();
	obExit();
}

function EditMemberGroups2 (){
	global $admin,$globalmod,$moderator,$newbie,$junior,$full,$senior,$god,$additional,$yySetLocation,$cgi,$db_prefix;
	$request = mysql_query("UPDATE {$db_prefix}membergroups SET membergroup='$admin',grouptype=0 WHERE ID_GROUP=1");
	$request = mysql_query("UPDATE {$db_prefix}membergroups SET membergroup='$globalmod',grouptype=0 WHERE ID_GROUP=8");
	$request = mysql_query("UPDATE {$db_prefix}membergroups SET membergroup='$moderator',grouptype=0 WHERE ID_GROUP=2");
	$request = mysql_query("UPDATE {$db_prefix}membergroups SET membergroup='$newbie',grouptype=0 WHERE ID_GROUP=3");
	$request = mysql_query("UPDATE {$db_prefix}membergroups SET membergroup='$junior',grouptype=0 WHERE ID_GROUP=4");
	$request = mysql_query("UPDATE {$db_prefix}membergroups SET membergroup='$full',grouptype=0 WHERE ID_GROUP=5");
	$request = mysql_query("UPDATE {$db_prefix}membergroups SET membergroup='$senior',grouptype=0 WHERE ID_GROUP=6");
	$request = mysql_query("UPDATE {$db_prefix}membergroups SET membergroup='$god',grouptype=0 WHERE ID_GROUP=7");
	$moregroups = explode("\n",$additional);
	$request = mysql_query("DELETE FROM {$db_prefix}membergroups WHERE grouptype=1");
	foreach($moregroups as $onemore)
		$request = mysql_query("INSERT INTO {$db_prefix}membergroups (membergroup,grouptype) VALUES ('".trim($onemore)."',1)");

	$yySetLocation = "$cgi;action=admin";
	redirectexit();
}

function MailingList (){
	global $yytitle,$txt,$color,$imagesdir,$cgi,$db_prefix;
	$yytitle = "$txt[6]";
	template_header();
	print <<<EOT
<form action="$cgi;action=ml" method="POST">
<table border="0" width="600" cellspacing="1" cellpadding="4" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    <script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=6')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[6]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <BR><font size=1>$txt[735]</font><BR><BR></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]">
    <textarea cols="70" rows="7" name="emails">
EOT;
	$request = mysql_query("SELECT emailAddress FROM {$db_prefix}members WHERE 1");
	while ($curmem = mysql_fetch_row($request))
		print "$curmem[0]; ";
	print <<<EOT
</textarea><BR><BR></td>
  </tr><tr>
    <td bgcolor="$color[titlebg]"><font size="2" color="$color[titletext]"><b>$txt[338]</b></font></td>
  </tr><tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2">
    <input type="text" name="subject" size="30" value="$txt[70]"><br><br>
    <textarea cols="70" rows="9" name="message">$txt[72]</textarea><br><br>
    <center><input type="submit" value="$txt[339]"></center></td>
  </tr>
</table>
</form>
EOT;
	footer();
	obExit();
}

function ml (){
	global $emails,$message,$yySetLocation,$cgi,$txt,$scripturl,$mbname,$subject,$db_prefix;
	$addressed = explode("; ",$emails);
	foreach ($addressed as $curmem) {
		$curmem = trim($curmem);
		if ($curmem != '')
			sendmail( $curmem, "$mbname: $subject", "$message\n\n$txt[130]\n\n$scripturl");
	}
	$yySetLocation = "$cgi;action=admin";
	redirectexit();
}

function ipban (){
 global $yytitle,$cgi,$txt,$imagesdir,$color,$db_prefix;
	$request = mysql_query("SELECT type,value FROM {$db_prefix}banned WHERE 1");
	$ipban = array();
	$emailban = array();
	$userban = array();
	while ($row = mysql_fetch_assoc($request))
	{
		if ($row['type']=='email')
			$emailban[]=$row['value'];
		elseif ($row['type']=='ip')
			$ipban[]=$row['value'];
        elseif ($row['type']=='username')
            $userban[]=$row['value'];
	}
	$ipbans = implode("\n",$ipban);
	$emailbans = implode("\n",$emailban);
    $userbans = implode("\n",$userban);
	$yytitle = "$txt[340]";
	template_header();
	print <<<EOT
<form action="$cgi;action=ipban2" method="POST">
<table border="0" cellspacing="1" cellpadding="4" align="center" width="550">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    <script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=7')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[340]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" align="center">
    <font size="2">
    <BR>$txt[724]<br>
    <textarea cols="60" rows="6" name="ban">$ipbans</textarea><br><br>
    $txt[725]<br>
    <textarea cols="60" rows="6" name="ban_email">$emailbans</textarea><br><BR>
    $txt[7252]<br>
    <textarea cols="60" rows="6" name="ban_user">$userbans</textarea><br><BR>
    <input type="submit" value="$txt[10]">
    </font></td>
  </tr>
</table>
</form>
EOT;
	footer();
	obExit();
}

function ipban2 (){
	global $ban,$ban_email,$ban_user,$yySetLocation,$cgi,$db_prefix;
	$ipban = explode("\n",$ban);
	$emailban = explode("\n",$ban_email);
    $userban = explode("\n",$ban_user);
	$request = mysql_query("DELETE FROM {$db_prefix}banned WHERE 1");
	foreach($ipban as $curban)
		$request = mysql_query("INSERT INTO {$db_prefix}banned (type,value) VALUES ('ip','".trim($curban)."')");
	foreach($emailban as $curban)
		$request = mysql_query("INSERT INTO {$db_prefix}banned (type,value) VALUES ('email','".trim($curban)."')");
    foreach($userban as $curban)
		$request = mysql_query("INSERT INTO {$db_prefix}banned (type,value) VALUES ('username','".trim($curban)."')");

	$yySetLocation = "$cgi;action=admin";
	redirectexit();
}

function SetReserve (){
	global $yytitle,$color,$txt,$cgi,$imagesdir,$db_prefix;
	$request = mysql_query("SELECT setting,value FROM {$db_prefix}reserved_names WHERE 1");
	$reserved = array();
	$matchword = $matchcase = $matchuser = $matchname = 0;
	while ($row = mysql_fetch_array($request))
	{
		if ($row['setting']=='word')
			$reserved[]=$row['value'];
		else
			${$row['setting']}=$row['value']?' checked':'';
	}
	$reswords = implode("\n",$reserved);

	$yytitle = "$txt[341]";

	template_header();
	print <<<EOT
<form action="$cgi;action=setreserve2" method="POST">
<table border="0" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor" align="center" cellpadding="4" width="580">
  <tr>
    <td bgcolor="$color[titlebg]" class="titlebg">
    <script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=8')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size=2 color="$color[titletext]"><b>$txt[341]</b></font></td>
  </tr><tr>
    <td bgcolor="$color[windowbg]" class="windowbg">
    <font size="1"><BR>$txt[699]<BR><BR></font></td>
  </tr><tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2"><div align="center"><font size="2">
    $txt[342]<br>
    <textarea cols="30" rows="6" name="reserved">$reswords</textarea></font></div><br>
	<font size="2"><input type="checkbox" name="matchword" $matchword></font>
	<font size="2">$txt[726]</font><br>
	<font size="2"><input type="checkbox" name="matchcase" $matchcase></font>
	<font size="2">$txt[727]</font><br>
	<font size="2"><input type="checkbox" name="matchuser" $matchuser></font>
	<font size="2">$txt[728]</font><br>
	<font size="2"><input type="checkbox" name="matchname" $matchname></font>
	<font size="2">$txt[729]</font><br>
	<div align="center"><input type="submit" value="$txt[10]"></div></td>
</tr>
</table>
</form>
EOT;
	footer();
	obExit();
}

function SetReserve2 (){
	global $reserved,$matchword,$matchcase,$matchuser,$matchname,$yySetLocation,$cgi,$db_prefix;
	$matchword = isset($matchword) ? 1 : 0;
	$matchcase = isset($matchcase) ? 1 : 0;
	$matchuser = isset($matchuser) ? 1 : 0;
	$matchname = isset($matchname) ? 1 : 0;
	$request = mysql_query("UPDATE {$db_prefix}reserved_names SET value=$matchword WHERE setting='matchword'");
	$request = mysql_query("UPDATE {$db_prefix}reserved_names SET value=$matchcase WHERE setting='matchcase'");
	$request = mysql_query("UPDATE {$db_prefix}reserved_names SET value=$matchuser WHERE setting='matchuser'");
	$request = mysql_query("UPDATE {$db_prefix}reserved_names SET value=$matchname WHERE setting='matchname'");
	$resnames = explode("\n",$reserved);
	$request = mysql_query("DELETE FROM {$db_prefix}reserved_names WHERE setting='word'");
	foreach($resnames as $curname)
		$request = mysql_query("INSERT INTO {$db_prefix}reserved_names (setting,value) VALUES ('word','".trim($curname)."')");
	$yySetLocation = "$cgi;action=admin";
	redirectexit();
}


function ModifyTemplate (){
	global $yytitle,$boarddir,$txt,$imagesdir,$color,$cgi,$db_prefix;

/*** Added Dave by Smulders - .html & .php template support ***/
	$templateFile = "$boarddir/template.php";
	if (!file_exists($templateFile))
		$templateFile = "$boarddir/template.html";

 /*** Matt Siegman's Admin Fix ***/
	$file = fopen($templateFile,"r");
	$fulltemplate = fread($file,filesize($templateFile));
	$fulltemplate = eregi_replace("</textarea>","&lt;/textarea&gt;",$fulltemplate);  //fix by Omar Bazavilvazo
	fclose($file);
	/*** Matt Siegman's Admin Fix ***/
	$yytitle = $txt[216];
	template_header();
	print <<<EOT
<form action="$cgi;action=modtemp2" method="POST">
<table border="0" width="100%" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor" cellpadding="4">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    <script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=9')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[216]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <BR><font size="1">$txt[682]</font><BR><BR></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" align="center"><font size="2">
    <form action="$cgi;action=modtemp2" method="POST"><BR>
    <textarea rows="30" cols="95" name="template" style="width:98%">$fulltemplate</textarea>
    <br><BR><input type="submit" value="$txt[10]"></font></td>
  </tr>
</table>
</form>
EOT;
	footer();
	obExit();
}

function ModifyTemplate2 (){
	global $template,$boarddir,$yySetLocation,$cgi,$db_prefix;
	$template = stripslashes($template);

 /*** Added by Dave Smulders - .html & .php template support ***/
	$templateFile = "$boarddir/template.php";
	if (!file_exists($templateFile))
		$templateFile = "$boarddir/template.html";

	$fh = fopen($templateFile,'w');
	fputs ($fh,$template);
	fclose($fh);
 /*** End Added by Dave Smulders ***/
	$yySetLocation = "$cgi;action=admin";
	redirectexit();
}

function ModifySettings (){
    global $maintenance,$guestaccess,$yyForceIIS,$yyblankpageIIS,$RegAgree,$emailpassword,$emailnewpass;
	global $emailwelcome,$MenuType,$enable_ubbc,$autolinkurls,$curposlinks,$profilebutton,$enable_news;
	global $enable_guestposting,$enable_notification,$allow_hide_email,$showlatestmember,$Show_RecentBar;
	global $Show_MemberBar,$showmarkread,$showmodify,$ShowBDescrip,$showuserpic,$showusertext,$color;
	global $showgenderimage,$shownewsfader,$showyabbcbutt,$allowpics,$use_flock,$mailtype,$timeformatstring;
	global $cgi,$color,$db_name,$db_server,$db_user,$db_passwd,$txt,$usetempfile,$faketruncation,$db_prefix;
	global $imagesdir,$boarddir,$mbname,$boardurl,$Cookie_Length,$cookieusername,$cookiepassword;
	global $mailprog,$smtp_server,$webmaster_email,$sourcedir,$facesdir,$facesurl,$faderpath,$ubbcjspath;
	global $helpfile,$timeoffset,$language,$TopAmmount,$MembersPerPage,$maxdisplay,$MaxMessLen,$maxmessagedisplay;
	global $MaxSigLen,$ClickLogTime,$max_log_days_old,$fadertime,$timeout,$JrPostNum,$FullPostNum,$SrPostNum;
	global $GodPostNum,$userpic_width,$userpic_height,$userpic_limits,$yytitle,$mtxt,$mtitle,$mmessage;

	if (!@copy("Settings.php", "Settings_bak.php"))
		$warning=$txt['yse1'];

	$mainchecked=$guestaccchecked=$forcechecked=$blankchecked=$agreechecked=$mailpasschecked=$newpasschecked=$welchecked=$menuchecked=$ubbcchecked=$aluchecked=$cpchecked=$pbchecked=$insertchecked=$newschecked=$gpchecked=$notifchecked=$ahmchecked=$slmchecked=$srbarchecked=$smbarchecked=$smreadchecked=$smodchecked=$supicchecked=$sutextchecked=$sgichecked=$snfchecked=$mst1=$mts2=$bdescripchecked=$syabbcchecked=$allowpicschecked=$srb0=$srb1=$srb2='';

	$yytitle = $txt[222];
	template_header();
    #Warning if couldn't backup Settings.php
    if ($warning)
	 echo "<center><B>".$warning."</B></center>";
	# figure out what to print
	if ($maintenance) { $mainchecked = ' checked'; }
	if ($guestaccess == 0) { $guestaccchecked = ' checked'; }
	if ($yyForceIIS) { $forcechecked = ' checked'; }
	if ($yyblankpageIIS) { $blankchecked = ' checked'; }
	if($RegAgree) { $agreechecked = " checked"; }
	if($emailpassword) { $mailpasschecked = " checked"; }
	if($emailnewpass) { $newpasschecked = " checked"; }
	if($emailwelcome) { $welchecked = " checked"; }
	if ($MenuType) { $menuchecked = ' checked'; }
	if ($enable_ubbc) { $ubbcchecked = ' checked'; }
	if ($autolinkurls) { $aluchecked = ' checked'; }
	if ($curposlinks) { $cpchecked = ' checked'; }
	if ($profilebutton) { $pbchecked = ' checked'; }
	if ($enable_news) { $newschecked = "checked"; }
	if ($enable_guestposting) { $gpchecked = "checked"; }
	if ($enable_notification) { $notifchecked = "checked"; }
	if ($allow_hide_email) { $ahmchecked = "checked"; }
	if ($showlatestmember) { $slmchecked = "checked"; }
	if ($Show_MemberBar) { $smbarchecked = "checked"; }
	if ($showmarkread) { $smreadchecked = "checked"; }
	if ($showmodify) { $smodchecked = "checked"; }
	if ($ShowBDescrip) { $bdescripchecked = "checked"; }
	if ($showuserpic) { $supicchecked = "checked"; }
	if ($showusertext) { $sutextchecked = "checked"; }
	if ($showgenderimage) { $sgichecked = "checked"; }
	if ($shownewsfader) { $snfchecked = "checked"; }
	if ($showyabbcbutt) { $syabbcchecked = "checked"; }
	if ($allowpics) { $allowpicschecked = "checked"; }
	if ($mailtype == 0) { $mts1 = ' selected'; } else if ($mailtype == 1) { $mts2 = ' selected'; }
	if ($Show_RecentBar == 1){$srb1=' selected';} else if ($Show_RecentBar == 2){$srb2 = ' selected'; } else {$srb0=' selected';}

	print <<<EOT
<script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=200,resizable=no");
        }
		// -->
	</script>
		<form action="$cgi;action=modsettings2" method="POST">
<table width="75%" border="0" cellspacing="1" cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor" align="center"><tr><td>
  <table border="0" cellspacing="0" cellpadding="4" align="center" width="100%">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]" colspan="2">
    <img src="$imagesdir/settings.gif" alt="" border="0">
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[222]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" colspan="2"><BR><font size="1">$txt[347]</font><BR><BR></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" width="400"><font size="2">$txt[yse5]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="db_server" value="$db_server"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" width="400"><font size="2">$txt[yse6]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="db_user" value="$db_user"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" width="400"><font size="2">$txt[yse7]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="db_passwd" value="$db_passwd"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" width="400"><font size="2">$txt[yse8]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="db_name" value="$db_name"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" width="400"><font size="2">$txt[yse54]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="db_prefix" value="$db_prefix"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" width="400"><font size="2">$txt[348]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="maintenance"$mainchecked></td>
  </tr><tr>
  <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$mtxt[1]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="mtitle" size="15" value="$mtitle"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$mtxt[2]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="mmessage" size="30" value="$mmessage"></td>
  </tr><tr>
    <td colspan=2 class="windowbg2" bgcolor="$color[windowbg2]">
    <HR size=1 width="100%" color="$color[windowbg3]" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[632]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="guestaccess"$guestaccchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[666]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="yyforceiis"$forcechecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[667]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="yyblankpageiis"$blankchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[349]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><select name="language">
EOT;
$dir = dir($boarddir);
while ($entry = $dir->read()){
	$n = substr($entry,0,(strlen($entry)-4));
	$e = substr($entry,(strlen($entry)-4),4);
	if ($e == '.lng'){
		$selected = "";
		if ($entry == $language) { $selected = " selected"; }
		print "    <option value=\"$entry\"$selected>$n</option>\n";
	}
}

print <<<EOT
</select></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[350]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="mbname" size="30" value="$mbname"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[351]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="boardurl" size="35" value="$boardurl"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[432]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="Cookie_Length" size="5" value="$Cookie_Length"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[352]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="cookieusername" size="20" value="$cookieusername"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[353]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="cookiepassword" size="20" value="$cookiepassword"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[584]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="RegAgree"$agreechecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[702]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="emailpassword"$mailpasschecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[639]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="emailnewpass"$newpasschecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[619]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="emailwelcome"$welchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[354]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="mailprog" size="20" value="$mailprog"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[407]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="smtp_server" size="20" value="$smtp_server"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[355]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="webmaster_email" size="20" value="$webmaster_email"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[404]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]">
    <select name="mailtype" size=1>
    <option value="0"$mts1>$txt[405]</option>
    <option value="1"$mts2>$txt[406]</option>
    </select></td>
  </tr><tr>
    <td colspan="2" class="windowbg2" bgcolor="$color[windowbg2]">
    <HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[356]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="boarddir" size="30" value="$boarddir"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[360]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="sourcedir" size="30" value="$sourcedir"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[362]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="facesdir" size="30" value="$facesdir"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[423]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="facesurl" size="35" value="$facesurl"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[363]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="imagesdir" size="35" value="$imagesdir"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[390]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="faderpath" size="35" value="$faderpath"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[506]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="ubbcjspath" size="35" value="$ubbcjspath"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[364]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="helpfile" size="35" value="$helpfile"></td>
  </tr><tr>
    <td colspan=2 class="windowbg2" bgcolor="$color[windowbg2]">
    <HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[365]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="titlebg" size="10" value="$color[titlebg]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[366]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="titletext" size="10" value="$color[titletext]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[367]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="windowbg" size="10" value="$color[windowbg]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[368]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="windowbg2" size="10" value="$color[windowbg2]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[640]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="windowbg3" size="10" value="$color[windowbg3]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[369]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="catbg" size="10" value="$color[catbg]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[370]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="bordercolor" size="10" value="$color[bordercolor]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[388]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="fadertext" size="10" value="$color[fadertext]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[389]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="fadertext2" size="10" value="$color[fadertext2]"></td>
  </tr><tr>
    <td colspan=2 class="windowbg2" bgcolor="$color[windowbg2]">
    <HR size=1 width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[521]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="menutype"$menuchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[522]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="curposlinks"$cpchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[523]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="profilebutton"$pbchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[587]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]" nobreak><input type="text" name="timeformatstring" value="$timeformatstring"> <a href="javascript:reqWin('help.php?help=12')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[723]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="allow_hide_email" $ahmchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[382]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="showlatestmember" $slmchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[387]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="shownewsfader" $snfchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[509]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><select name="showrecentbar"><option value=0$srb0>$txt[yse93]</option><option value=1$srb1>$txt[yse94]</option><option value=2$srb2>$txt[yse95]</option></select></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[510]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="showmemberbar" $smbarchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[618]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="showmarkread" $smreadchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[732]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="showbdescrip" $bdescripchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[383]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="showmodify" $smodchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[384]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="showuserpic" $supicchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[385]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="showusertext" $sutextchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[386]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="showgenderimage" $sgichecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[740]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="showyabbcbutt" $syabbcchecked></td>
  </tr><tr>
    <td colspan=2 class="windowbg2" bgcolor="$color[windowbg2]">
    <HR size=1 width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[378]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="enable_ubbc"$ubbcchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[379]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="enable_news" $newschecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[746]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="allowpics" $allowpicschecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[380]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="enable_guestposting" $gpchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[381]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="enable_notification" $notifchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[524]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="checkbox" name="autolinkurls"$aluchecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[371]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="timeoffset" size="5" value="$timeoffset"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[372]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="TopAmmount" size="5" value="$TopAmmount"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[373]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="MembersPerPage" size="5" value="$MembersPerPage"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[374]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="maxdisplay" size="5" value="$maxdisplay"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[375]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="maxmessagedisplay" size="5" value="$maxmessagedisplay"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[498]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="maxmesslen" size="5" value="$MaxMessLen"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[689]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="maxsiglen" size="5" value="$MaxSigLen"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[690]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="clicklogtime" size="5" value="$ClickLogTime"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[376]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="max_log_days_old" size="5" value="$max_log_days_old"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[739]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="fadertime" size="5" value="$fadertime"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[408]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="timeout" size="5" value="$timeout"></td>
  </tr><tr>
    <td colspan=2 class="windowbg2" bgcolor="$color[windowbg2]">
    <HR size=1 width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[588]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="jrmem" size="5" value="$JrPostNum"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[589]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="fullmem" size="5" value="$FullPostNum"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[590]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="srmem" size="5" value="$SrPostNum"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[591]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="godmem" size="5" value="$GodPostNum"></td>
  </tr><tr>
    <td colspan=2 class="windowbg2" bgcolor="$color[windowbg2]">
    <HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[476]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="userpic_width" size="5" value="$userpic_width"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[477]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="userpic_height" size="5" value="$userpic_height"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[478]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type="text" name="userpic_limits" size="35" value="$userpic_limits"></td>
 </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan="2" align="center" valign="middle">
    <HR size="1" width="100%" class="windowbg3"><input type="submit" value="$txt[10]">
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

function ModifySettings2 (){
	global $maintenance,$guestaccess,$yyForceIIS,$yyblankpageIIS,$RegAgree,$emailpassword,$emailnewpass;
	global $emailwelcome,$MenuType,$enable_ubbc,$autolinkurls,$curposlinks,$profilebutton,$enable_news;
	global $enable_guestposting,$enable_notification,$allow_hide_email,$showlatestmember,$Show_RecentBar;
	global $Show_MemberBar,$showmarkread,$showmodify,$ShowBDescrip,$showuserpic,$showusertext,$color;
	global $showgenderimage,$shownewsfader,$showyabbcbutt,$allowpics,$mailtype,$timeformatstring;
	global $cgi,$color,$db_name,$db_server,$db_user,$db_passwd,$txt,$db_prefix;
	global $imagesdir,$boarddir,$mbname,$boardurl,$Cookie_Length,$cookieusername,$cookiepassword;
	global $mailprog,$smtp_server,$webmaster_email,$sourcedir,$facesdir,$facesurl,$faderpath,$ubbcjspath;
	global $helpfile,$timeoffset,$language,$TopAmmount,$MembersPerPage,$maxdisplay,$MaxMessLen,$maxmessagedisplay;
	global $MaxSigLen,$ClickLogTime,$max_log_days_old,$fadertime,$timeout,$JrPostNum,$FullPostNum,$SrPostNum;
	global $GodPostNum,$userpic_width,$userpic_height,$userpic_limits,$showrecentbar ;
	global $settings,$pwseed,$Cookie_Exp_Date,$yySetCookies,$username,$password;

	global $HTTP_POST_VARS;
	foreach($HTTP_POST_VARS as $key=>$val)
		$HTTP_POST_VARS[$key] = stripslashes($val);

	$onoff = array('allowpics', 'showyabbcbutt', 'showbdescrip', 'maintenance', 'guestaccess', 'insert_original', 'enable_ubbc', 'enable_news', 'enable_guestposting', 'enable_notification', 'showlatestmember', 'showmemberbar', 'showmarkread', 'showmodify', 'showuserpic', 'showusertext', 'showgenderimage', 'shownewsfader', 'menutype', 'curposlinks', 'profilebutton', 'autolinkurls', 'emailpassword', 'RegAgree', 'emailwelcome', 'allow_hide_email', 'usetempfile', 'emailnewpass', 'yyForceIIS', 'yyblankpageIIS');
	foreach ($onoff as $key)
	{
		if (isset($HTTP_POST_VARS[$key]))
			$$key = 1;
		else
			$$key = 0;
	}

	$guestaccess = $guestaccess ? 0 : 1;

	# If empty fields are submitted, set them to default-values to save yabb from crashing

	$db_prefix = isset($HTTP_POST_VARS['db_prefix']) ? $HTTP_POST_VARS['db_prefix'] : "";

	$timeout = isset($HTTP_POST_VARS['timeout']) ? $HTTP_POST_VARS['timeout'] : 0;
	$fadertime = isset($HTTP_POST_VARS['fadertime']) ? $HTTP_POST_VARS['fadertime'] : 5;
	$timeoffset = isset($HTTP_POST_VARS['timeoffset']) ? $HTTP_POST_VARS['timeoffset'] : 0;
	$TopAmmount = isset($HTTP_POST_VARS['TopAmmount']) ? $HTTP_POST_VARS['TopAmmount'] : 25;
	$MembersPerPage = isset($HTTP_POST_VARS['MembersPerPage']) ? $HTTP_POST_VARS['MembersPerPage'] : 20;
	$maxdisplay = isset($HTTP_POST_VARS['maxdisplay']) ? $HTTP_POST_VARS['maxdisplay'] : 20;
	$maxmessagedisplay = isset($HTTP_POST_VARS['maxmessagedisplay']) ? $HTTP_POST_VARS['maxmessagedisplay'] : 20;
	$max_log_days_old = isset($HTTP_POST_VARS['max_log_days_old']) ? $HTTP_POST_VARS['max_log_days_old'] : 21;
	$clicklogtime = isset($HTTP_POST_VARS['clicklogtime']) ? $HTTP_POST_VARS['clicklogtime'] : 600;
	$Cookie_Length = isset($HTTP_POST_VARS['Cookie_Length']) ? $HTTP_POST_VARS['Cookie_Length'] : 60;
	$cookieusername = isset($HTTP_POST_VARS['cookieusername']) ? $HTTP_POST_VARS['cookieusername'] : 'yabbseusername';
	$cookiepassword = isset($HTTP_POST_VARS['cookiepassword']) ? $HTTP_POST_VARS['cookiepassword'] : 'yabbsepassword'	;
	$timeformatstring = isset($HTTP_POST_VARS['timeformatstring']) ? $HTTP_POST_VARS['timeformatstring'] : 'F jS, Y, h:i:s a';
	$maxmesslen = isset($HTTP_POST_VARS['maxmesslen']) ? $HTTP_POST_VARS['maxmesslen'] : 5000;
	$maxsiglen = isset($HTTP_POST_VARS['maxsiglen']) ? $HTTP_POST_VARS['maxsiglen'] : 200;
	$jrmem = isset($HTTP_POST_VARS['jrmem']) ? $HTTP_POST_VARS['jrmem'] : 50;
	$fullmem = isset($HTTP_POST_VARS['fullmem']) ? $HTTP_POST_VARS['fullmem'] : 100;
	$srmem = isset($HTTP_POST_VARS['srmem']) ? $HTTP_POST_VARS['srmem'] : 250;
	$godmem = isset($HTTP_POST_VARS['godmem']) ? $HTTP_POST_VARS['godmem'] : 500;
	$language = isset($HTTP_POST_VARS['language']) ? $HTTP_POST_VARS['language'] : 'english.lng';
	$mbname = isset($HTTP_POST_VARS['mbname']) ? $HTTP_POST_VARS['mbname'] : 'My YaBB SE';
	$boardurl = isset($HTTP_POST_VARS['boardurl']) ? $HTTP_POST_VARS['boardurl'] : GetBoardURL();
	$boarddir = isset($HTTP_POST_VARS['boarddir']) ? $HTTP_POST_VARS['boarddir'] : GetDirPath();
	$sourcedir = isset($HTTP_POST_VARS['sourcedir']) ? $HTTP_POST_VARS['sourcedir'] : "$boarddir/Sources";
	$facesdir = isset($HTTP_POST_VARS['facesdir']) ? $HTTP_POST_VARS['facesdir'] : "$boarddir/YaBBImages/avatars";
	$facesurl = isset($HTTP_POST_VARS['facesurl']) ? $HTTP_POST_VARS['facesurl'] : "$boardurl/YaBBImages/avatars";
	$imagesdir = isset($HTTP_POST_VARS['imagesdir']) ? $HTTP_POST_VARS['imagesdir'] : "$boardurl/YaBBImages";
	$helpfile = isset($HTTP_POST_VARS['helpfile']) ? $HTTP_POST_VARS['helpfile'] : "$boardurl/YaBBHelp/index.html";

	$mailprog = isset($HTTP_POST_VARS['mailprog']) ? $HTTP_POST_VARS['mailprog'] : '/usr/sbin/sendmail';
	$smtp_server = isset($HTTP_POST_VARS['smtp_server']) ? $HTTP_POST_VARS['smtp_server'] : '127.0.0.1';
	$webmaster_email = isset($HTTP_POST_VARS['webmaster_email']) ? $HTTP_POST_VARS['webmaster_email'] : 'webmaster@mysite.com';
	$mailtype = isset($HTTP_POST_VARS['mailtype']) ? $HTTP_POST_VARS['mailtype'] : 0;

	$color['titlebg'] = isset($HTTP_POST_VARS['titlebg']) ? $HTTP_POST_VARS['titlebg'] : '#6E94B7';
    $mtitle = isset($HTTP_POST_VARS['mtitle']) ? $HTTP_POST_VARS['mtitle'] : '$mtxt[04]';
    $mmessage = isset($HTTP_POST_VARS['mmessage']) ? $HTTP_POST_VARS['mmessage'] : '$mtxt[05]';

	$color['titletext'] = isset($HTTP_POST_VARS['titletext']) ? $HTTP_POST_VARS['titletext'] : '#FFFFFF';
	$color['windowbg'] = isset($HTTP_POST_VARS['windowbg']) ? $HTTP_POST_VARS['windowbg'] : '#AFC6DB';
	$color['windowbg2'] = isset($HTTP_POST_VARS['windowbg2']) ? $HTTP_POST_VARS['windowbg2'] : '#F8F8F8';
	$color['windowbg3'] = isset($HTTP_POST_VARS['windowbg3']) ? $HTTP_POST_VARS['windowbg3'] : '#6394BD';
	$color['catbg'] = isset($HTTP_POST_VARS['catbg']) ? $HTTP_POST_VARS['catbg'] : '#DEE7EF';
	$color['bordercolor'] = isset($HTTP_POST_VARS['bordercolor']) ? $HTTP_POST_VARS['bordercolor'] : '#6394BD';
	$color['fadertext'] = isset($HTTP_POST_VARS['fadertext']) ? $HTTP_POST_VARS['fadertext'] : '#000000';
	$color['fadertext2'] = isset($HTTP_POST_VARS['fadertext2']) ? $HTTP_POST_VARS['fadertext2'] : '#000000';

	$faderpath = isset($HTTP_POST_VARS['faderpath']) ? $HTTP_POST_VARS['faderpath'] : "$boardurl/fader.js";
	$ubbcjspath = isset($HTTP_POST_VARS['ubbcjspath']) ? $HTTP_POST_VARS['ubbcjspath'] : "$boardurl/ubbc.js";;
	$userpic_width = isset($HTTP_POST_VARS['userpic_width']) ? $HTTP_POST_VARS['userpic_width'] : 65;
	$userpic_height = isset($HTTP_POST_VARS['userpic_height']) ? $HTTP_POST_VARS['userpic_height'] : 65;
	$userpic_limits = isset($HTTP_POST_VARS['userpic_limits']) ? stripslashes($HTTP_POST_VARS['userpic_limits']) : 'Please note that your image has to be <b>gif</b> or <b>jpg</b> and that it will be resized!';

	$filler = "                                                                               ";
	$setfile = <<<EOT
<?php
/*****************************************************************************/
/* Settings.php                                                              */
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

########## Board Info ##########
# Note: these settings must be properly changed for YaBB to work

\$maintenance = $maintenance;				# Set to 1 to enable Maintenance mode
\$mtitle = "$mtitle";                       # Subject for display
\$mmessage = "$mmessage";                   # Message Description for display

\$guestaccess = $guestaccess;				# Set to 0 to disallow guests from doing anything but login or register

\$yyForceIIS = $yyForceIIS;				# Set to 1 if you encounter errors while running on an MS IIS server
\$yyblankpageIIS = $yyblankpageIIS;			# Set to 1 if you encounter blank pages after posting (usually on MS IIS servers)

\$language = "$language";				# Change to language pack you want to use
\$mbname = "$mbname";					# The name of your YaBB forum
\$boardurl = "$boardurl";				# URL of your board's folder (without trailing '/')

\$Cookie_Length = $Cookie_Length;			# Cookies will expire after XX minutes of person logging in (they will be logged out after)
\$cookieusername = "$cookieusername";			# Name of the username cookie
\$cookiepassword = "$cookiepassword";			# Name of the password cookie

\$RegAgree = $RegAgree;					# Set to 1 to display the registration agreement when registering
\$emailpassword = $emailpassword;			# 0 - instant registration. 1 - password emailed to new members
\$emailnewpass = $emailnewpass;				# Set to 1 to email a new password to members if they change their email address
\$emailwelcome = $emailwelcome;				# Set to 1 to email a welcome message to users even when you have mail password turned off

\$mailprog = "$mailprog";				# Location of your sendmail program
\$smtp_server = "$smtp_server";				# SMTP-Server
\$webmaster_email = "$webmaster_email";		# Your e-mail address.
\$mailtype = $mailtype;					# 0 - sendmail, 1 - SMTP

########## Database Info ##########
\$db_name = "$db_name";
\$db_user = "$db_user";
\$db_passwd = "$db_passwd";
\$db_server = "$db_server";
\$db_prefix = "$db_prefix";

########## Directories/Files ##########
# Note: directories other than \$imagesdir do not have to be changed unless you move things

\$boarddir = "$boarddir"; 				# The absolute path to the board's folder (usually can be left as '.')
\$sourcedir = "$sourcedir";        			# Directory with YaBB source files
\$facesdir = "$facesdir";				# Absolute Path to your avatars folder
\$facesurl = "$facesurl";				# URL to your avatars folder
\$imagesdir = "$imagesdir";				# URL to your images directory
\$ubbcjspath = "$ubbcjspath";	                        # Web path to your 'ubbc.js' REQUIRED for post/modify to work properly!
\$faderpath = "$faderpath";				# Web path to your 'fader.js'
\$helpfile = "$helpfile";				# Location of your help file


########## Colors ##########
# Note: equivalent to colors in CSS tag of template.html, so set to same colors preferrably
# for browsers without CSS compatibility and for some items that don't use the CSS tag

\$color['titlebg'] = "$color[titlebg]";		# Background color of the 'title-bar'
\$color['titletext'] = "$color[titletext]";		# Color of text in the 'title-bar' (above each 'window')
\$color['windowbg'] = "$color[windowbg]";		# Background color for messages/forms etc.
\$color['windowbg2'] = "$color[windowbg2]";		# Background color for messages/forms etc.
\$color['windowbg3'] = "$color[windowbg3]";		# Color of horizontal rules in posts
\$color['catbg'] = "$color[catbg]";			# Background color for category (at Board Index)
\$color['bordercolor'] = "$color[bordercolor]";	# Table Border color for some tables
\$color['fadertext']  = "$color[fadertext]";		# Color of text in the NewsFader ("The Latest News" color)
\$color['fadertext2']  = "$color[fadertext2]";	# Color of text in the NewsFader (news color)

########## Layout ##########

\$MenuType = $menutype;					# 1 for text menu or anything else for images menu
\$curposlinks = $curposlinks;				# 1 for links in navigation on current page, or 0 for text without link
\$profilebutton = $profilebutton;			# 1 to show view profile button under post, or 0 for blank
\$timeformatstring = "$timeformatstring";				# Select your preferred output Format of Time and Date
\$allow_hide_email = $allow_hide_email;			# Allow users to hide their email from public. Set 0 to disable
\$showlatestmember = $showlatestmember;			# Set to 1 to display "Welcome Newest Member" on the Board Index
\$shownewsfader = $shownewsfader;			# 1 to allow or 0 to disallow NewsFader javascript on the Board Index
							# If 0, you'll have no news at all unless you put <yabb news> tag
							# back into template.html!!!
\$Show_RecentBar = $showrecentbar;			# Set to 1 to display the Recent Posts bar on Board Index
\$Show_MemberBar = $showmemberbar;			# Set to 1 to display the Members List table row on Board Index
\$showmarkread = $showmarkread;				# Set to 1 to display and enable the mark as read buttons
\$showmodify = $showmodify;				# Set to 1 to display "Last modified: Realname - Date" under each message
\$ShowBDescrip = $showbdescrip;				# Set to 1 to display board descriptions on the topic (message) index for each board
\$showuserpic = $showuserpic;				# Set to 1 to display each member's picture in the message view (by the ICQ.. etc.)
\$showusertext = $showusertext;				# Set to 1 to display each member's personal text in the message view (by the ICQ.. etc.)
\$showgenderimage = $showgenderimage;			# Set to 1 to display each member's gender in the message view (by the ICQ.. etc.)
\$showyabbcbutt = $showyabbcbutt;                       # Set to 1 to display the yabbc buttons on Posting and IM Send Pages

########## Feature Settings ##########

\$enable_ubbc = $enable_ubbc;				# Set to 1 if you want to enable UBBC (Uniform Bulletin Board Code)
\$enable_news = $enable_news;				# Set to 1 to turn news on, or 0 to set news off
\$allowpics = $allowpics;				# set to 1 to allow members to choose avatars in their profile
\$enable_guestposting = $enable_guestposting;		# Set to 0 if do not allow 1 is allow.
\$enable_notification = $enable_notification;		# Allow e-mail notification
\$autolinkurls = $autolinkurls;				# Set to 1 to turn URLs into links, or 0 for no auto-linking.

\$timeoffset = $timeoffset;				# Time Offset (so if your server is EST, this would be set to -1 for CST)
\$TopAmmount = $TopAmmount;				# No. of top posters to display on the top members list
\$MembersPerPage = $MembersPerPage;			# No. of members to display per page of Members List - All
\$maxdisplay = $maxdisplay;				# Maximum of topics to display
\$maxmessagedisplay = $maxmessagedisplay;		# Maximum of messages to display
\$MaxMessLen = $maxmesslen;  				# Maximum Allowed Characters in a Posts
\$MaxSigLen = $maxsiglen;				# Maximum Allowed Characters in Signatures
\$ClickLogTime = $clicklogtime;				# Time in minutes to log every click to your forum (longer time means larger log file size)
\$max_log_days_old = $max_log_days_old;			# If an entry in the user's log is older than ... days remove it
							# Set to 0 if you want it disabled
\$fadertime = $fadertime;				# Length in seconds to display each item in the news fader
\$timeout = $timeout;					# Minimum time between 2 postings from the same IP


########## Membergroups ##########

\$JrPostNum = $jrmem;					# Number of Posts required to show person as 'junior' membergroup
\$FullPostNum = $fullmem;				# Number of Posts required to show person as 'full' membergroup
\$SrPostNum = $srmem;					# Number of Posts required to show person as 'senior' membergroup
\$GodPostNum = $godmem;					# Number of Posts required to show person as 'god' membergroup


########## MemberPic Settings ##########

\$userpic_width = $userpic_width;			# Set pixel size to which the selfselected userpics are resized, 0 disables this limit
\$userpic_height = $userpic_height;			# Set pixel size to which the selfselected userpics are resized, 0 disables this limit
\$userpic_limits = "$userpic_limits";			# Text To Describe The Limits
?>
EOT;

//	$setfile = preg_replace("/(.+\;)\s+(\#.+$)/e", $1 . substr( $filler, 0, (70-(length $1)) ) . $2,$setfile);
//	$setfile = preg_replace("/(.{64,}\;)\s+(\#.+$)/e",$1 . "\n   " . $2,$setfile);
	# $setfile =~ s~(.+\;)(\s+)(\#.{40,}?)\s+(.{10,}$)~$1 . $2 . $3 . "\n   # " . $4~gem;
	# $setfile =~ s~^\s\s\s+(\#.{40,}?)\s+(.{10,}$)~"   " . $1 . "\n   # " . $2~gem;
//	$setfile = preg_replaec("/^\s\s\s+(\#.+$)/e",substr( $filler, 0, 70 ) . $1 ,$setfile);

	$fh = fopen ("$boarddir/Settings.php",'w');
	fputs($fh,$setfile);
	fclose($fh);

	$password = crypt($settings[0],$pwseed);
	$Cookie_Exp_Date = 'Sun, 17-Jan-2038 00:00:00 GMT';

	$yySetCookies = "Set-Cookie: $cookieusername=$username; path=/; expires=$Cookie_Exp_Date;\n";
	$yySetCookies .= "Set-Cookie: $cookiepassword=$password; path=/; expires=$Cookie_Exp_Date;\n";

	LoadUserSettings();
	WriteLog();
	Admin();
}

function GetBoardURL () {
	global $HTTP_HOST,$SERVER_NAME,$SCRIPT_NAME,$SERVER_POST;
	$url = 'http://' . (isset($HTTP_HOST) ? $HTTP_HOST : $SERVER_NAME ).
	($SERVER_PORT != 80 ? '' :$SERVER_PORT) .
	$SCRIPT_NAME;
	return $url;
}

# Gets our current absolute path. Needed for error messages.
function GetDirPath () {
	global $SCRIPT_FILENAME;
	return $SCRIPT_FILENAME;
}

function SetCensor (){
	global $yytitle,$txt,$color,$cgi,$imagesdir,$db_prefix;
	$request = mysql_query("SELECT vulgar,proper FROM {$db_prefix}censor WHERE 1");
	$censored = array();
	while ($row = mysql_fetch_row($request))
		$censored[] = "$row[0]=$row[1]";
	$censortext = implode("\n",$censored);
	$yytitle = $txt[135];
	template_header();
	print <<<EOT
<form action="$cgi;action=setcensor2" method="POST">
<table border="0" width="300" cellspacing="1" cellpadding="4" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
	<script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=11')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[135]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" align="center"><font size="2">
        $txt[136]<br>
    <textarea cols="50" rows="6" name="censortext">$censortext</textarea><br><BR>
    <input type="submit" value="$txt[10]"></font></td>
  </tr>
</table>
</form>
EOT;
	footer();
	obExit();
}

function SetCensor2 (){
	global $censortext,$yySetLocation,$cgi,$db_prefix;
	$censored = explode("\n",$censortext);
	$request = mysql_query("DELETE FROM {$db_prefix}censor WHERE 1");
	foreach ($censored as $row)
	{
		if (trim($row) != '')
		{
			$items = explode("=",trim($row));
			$request = mysql_query("INSERT INTO {$db_prefix}censor (vulgar,proper) VALUES ('$items[0]','$items[1]')");
		}
	}
	$yySetLocation = "$cgi;action=admin";
	redirectexit();
}


function clean_log () {
	global $yytitle,$txt,$scripturl,$cgi,$color,$db_prefix;
	$yytitle = $txt[202];
	template_header();
	print <<<EOT
<table border="0" width="90%" cellspacing=1 cellpadding="3" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[202]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]"><font size="2">$txt[203]
    <a href="$cgi;action=do_clean_log">$txt[163]</a>&nbsp;&nbsp;<a href="$scripturl?action=admin">$txt[164]</a><br>
    </font></td>
  </tr>
</table>
EOT;
	footer();
	obExit();
}

function do_clean_log (){
	global $db_prefix;
	$request = mysql_query("DELETE FROM {$db_prefix}log_online WHERE 1");
	Admin();
}

function AdminBoardRecount () {
	global $txt,$yytitle,$db_prefix;
	$yytitle = $txt[502];
	$board = array();
	$request = mysql_query("SELECT ID_BOARD FROM {$db_prefix}boards WHERE 1");
	while ($row = mysql_fetch_row($request)){
		$result = mysql_query("SELECT ID_TOPIC FROM {$db_prefix}topics WHERE ID_BOARD=$row[0]");
		$topics = mysql_num_rows($result);
		while ($row2 = mysql_fetch_row($result))
		{
			$result2 = mysql_query("SELECT COUNT(*) as count FROM {$db_prefix}messages WHERE ID_TOPIC=$row2[0]");
			$row3 = mysql_fetch_row($result2);
			$result2 = mysql_query("UPDATE {$db_prefix}topics SET numReplies=$row3[0]-1 WHERE ID_TOPIC=$row2[0]");
		}
		$result = mysql_query("SELECT COUNT(*) FROM {$db_prefix}messages as m,{$db_prefix}topics as t WHERE (m.ID_TOPIC=t.ID_TOPIC AND t.ID_BOARD=$row[0])");
		$tmp = mysql_fetch_row($result);
		$posts = $tmp[0];
		$result = mysql_query("UPDATE {$db_prefix}boards SET numTopics=$topics,numPosts=$posts WHERE ID_BOARD=$row[0]");
	}

	template_header();
	print " <br><b>$txt[503]</b> <br> <br>";
	footer();
	obExit();
}

function ShowClickLog (){
	global $yytitle,$txt,$color,$imagesdir,$img,$boardurl,$db_prefix;
	$yytitle = $txt[693];
	template_header();

	$ip = array();
	$to = array();
	$from = array();
	$info = array();
	$request = mysql_query("SELECT ip,agent,toUrl,fromUrl FROM {$db_prefix}log_clicks WHERE 1");
	while ($row = mysql_fetch_assoc($request)){
		$ip[] = $row['ip'];
		$to[] = $row['toUrl'];
		$from[] = $row['fromUrl'];
		$info[] = $row['agent'];
	}


	$i = 0;
	$os = array();
	$browser = array();
	foreach ($info as $curentry) {
		if (!preg_match("/\s\(Win/",$curentry) || !preg_match("/\s\(mac/",$curentry))
			$curentry = preg_replace("/\s\((compatible;\s)*/"," - ",$curentry);
		else
			$curentry = preg_replace("/(\S)*\(/",";",$curentry);
		if (preg_match("/\s-\sWin/",$curentry))
			$curentry = preg_replace("/\s-\sWin/","; win",$curentry);
		if (preg_match("/\s-\sMac/",$curentry))
			$curentry = preg_replace("/\s-\sMac/","; mac",$curentry);
		if (strstr($curentry,"; "))
			list ($browser[$i],$os[$i]) = explode(";", $curentry);
		else{
			$browser[$i] = $curentry;
			$os[$i] = $txt[470];
		}
		$browser[$i] = isset($browser[$i])?$browser[$i]:'';
		$os[$i] = isset($os[$i])?$os[$i]:'';
		if (preg_match("/\) \//",$os[$i])) { list ($os[$i],$browser[$i]) = explode(") /", $os[$i]); }
		$os[$i] = str_replace(")","",$os[$i]);
		$i++;
	}

	print <<<EOT
<table border="0" cellspacing="1" cellpadding="5" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td bgcolor="$color[titlebg]" class="titlebg">
    <img src="$imagesdir/xx.gif" alt="" border="0">&nbsp;
    <font size="2" color="$color[titletext]"><b>$txt[693]</b></font></td>
  </tr><tr>
    <td class="quote">
    <BR>$txt[697]<BR><BR></td>
  </tr><tr>
    <td class="catbg">
    <font size="2"><center><B>$txt[694]</B></center></font>
    </td>
  </tr><tr>
    <td class="windowbg2"><font size="2">
EOT;
$iplist = array();
for($i = 0; $i < sizeof($ip); $i++) {
	$iplist[$ip[$i]] = isset($iplist[$ip[$i]])?$iplist[$ip[$i]]+1:1;
}

$totalclick = sizeof($ip);
$totalip = sizeof($iplist);

print "<i>$txt[742]: $totalclick</i><BR>";
print "<i>$txt[743]: $totalip</i><BR><BR>";
foreach($iplist as $key=>$value)
	print "$key &nbsp;(<i>$value</i>)<BR>\n";

print <<<EOT
    </font></td>
  </tr><tr>
    <td bgcolor="$color[catbg]" class="catbg">
    <font size="2"><center><B>$txt[695]</B>
    </td>
  </tr><tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2"><font size="2">
EOT;
$newbrowser = array();
for($i = 0; $i < sizeof($browser); $i++)
	$newbrowser[$browser[$i]] = isset($newbrowser[$browser[$i]])?$newbrowser[$browser[$i]]+1:1;

$totalbrow = sizeof($newbrowser);
print "<i>$txt[744]: $totalbrow</i><BR><BR>";

foreach ($newbrowser as $key => $value)
	print "$key &nbsp;(<i>$value</i>)<BR>\n";

print <<<EOT
    </font></td>
  </tr><tr>
    <td bgcolor="$color[catbg]" class="catbg">
    <font size="2"><center><B>$txt[696]</B>
    </td>
  </tr><tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2"><font size="2">
EOT;
$newos = array();
for($i = 0; $i < sizeof($os); $i++)
	$newos[$os[$i]] = isset($newos[$os[$i]])?$newos[$os[$i]]+1:1;

$totalos = sizeof($newos);
print "<i>$txt[745]: $totalos</i><BR><BR>";

foreach ($newos as $key => $value)
	print "$key &nbsp;(<i>$value</i>)<BR>\n";

print <<<EOT
    </font></td>
  </tr><tr>
    <td bgcolor="$color[catbg]" class="catbg">
    <font size="2"><center><B>Pages Visited</B>
    </td>
  </tr><tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2"><font size="2">
EOT;
$newto = array();
for($i = 0; $i < sizeof($to); $i++)
	$newto[$to[$i]] = isset($newto[$to[$i]])?$newto[$to[$i]]+1:1;

foreach($newto as $key => $value)
	print "<a href=\"$key\" target=\"_blank\">$key</a> 	&nbsp;(<i>$value</i>)<BR>\n";

print <<<EOT
    </font></td>
  </tr><tr>
    <td bgcolor="$color[catbg]" class="catbg">
    <font size="2"><center><B>Referring Pages</B>
    </td>
  </tr><tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2"><font size="2">
EOT;
$newfrom = array();
for($i = 0; $i < sizeof($from); $i++)
	$newfrom[$from[$i]] = isset($newfrom[$from[$i]])?$newfrom[$from[$i]]+1:1;

foreach($newfrom as $key => $value){
	if ($key == '')
	$key = $txt[470];
	if (strcasecmp(substr($key,0,strlen($boardurl)),$boardurl) != 0)
		print "<a href=\"$key\" target=_blank>".wordwrap($key,80,'...<br>&nbsp;&nbsp;',1)."</a> &nbsp;(<i>$value</i>)<BR>\n";
}

print <<<EOT
    </font></td>
  </tr>
</table>
EOT;
	footer();
	obExit();
}

function ver_detail (){
	global $yytitle,$txt,$color,$YaBBversion,$language,$YaBBplver,$englishlngver,$adminplver;
	global $boardindexplver,$displayplver,$icqpagerplver,$instantmessageplver,$loadplver;
	global $lockthreadplver,$loginoutplver,$maintenanceplver,$manageboardsplver,$managecatsplver;
	global $memberlistplver,$modifymessageplver,$movethreadplver,$notifyplver,$postplver;
	global $profileplver,$recentplver,$registerplver,$removeoldthreadsplver,$removethreadplver;
	global $searchplver,$sendtopicplver,$securityplver,$subsplver,$messageindexplver,$pollplver;
	global $scripturl,$sourcedir,$db_prefix,$karmaphpver,$modsettingsphpver,$repairboardsphpver;
	loadfiles();
	$yytitle = $txt[429];
	template_header();

	print <<<EOT
<table border="0" width="70%" cellspacing="1" bgcolor="$color[titlebg]" cellpadding=3 align="center">
  <tr>
    <td class="titlebg"><font size="2" color="$color[titletext]"><b>$txt[429]</b></font></td>
</tr><tr>
	<td class ="quote">$txt[dvc1]</td>
	</tr><tr>
    <td class="windowbg" align="center">
	<form action="http://www.yabb.info/versionchecker.php" method=POST target="_blank">
    <table border="0" bgcolor="$color[windowbg]" class="windowbg" width="80%">
      <tr>
        <td width="30%"><B>$txt[495]</B></td>
        <td width="30%"><B>$txt[494]</B></td>
      </tr><tr>
        <td width="30%">$txt[496]</td><td><i>$YaBBversion</i><input type=hidden name="YaBBversion" value="$YaBBversion"></td>
      </tr><tr>
        <td width="30%">index.php</td><td><i>$YaBBplver</i><input type=hidden name="index" value="$YaBBplver"></td>
      </tr><tr>
        <td width="30%">$language</td><td><i>$englishlngver</i><input type=hidden name="language" value="$englishlngver"></td>
      </tr><tr>
        <td width="30%">Admin.php</td><td><i>$adminplver</i><input type=hidden name="Admin" value="$adminplver"></td>
      </tr><tr>
        <td width="30%">BoardIndex.php</td><td><i>$boardindexplver</i><input type=hidden name="BoardIndex" value="$boardindexplver"></td>
      </tr><tr>
        <td width="30%">Display.php</td><td><i>$displayplver</i><input type=hidden name="Display" value="$displayplver"></td>
      </tr><tr>
        <td width="30%">ICQPager.php</td><td><i>$icqpagerplver</i><input type=hidden name="ICQPager" value="$icqpagerplver"></td>
      </tr><tr>
        <td width="30%">InstantMessage.php</td><td><i>$instantmessageplver</i><input type=hidden name="InstantMessage" value="$instantmessageplver"></td>
      </tr><tr>
        <td width="30%">Karma.php</td><td><i>$karmaphpver</i><input type=hidden name="Karma" value="$karmaphpver"></td>
      </tr><tr>
        <td width="30%">Load.php</td><td><i>$loadplver</i><input type=hidden name="Load" value="$loadplver"></td>
      </tr><tr>
        <td width="30%">LockThread.php</td><td><i>$lockthreadplver</i><input type=hidden name="LockThread" value="$lockthreadplver"></td>
      </tr><tr>
        <td width="30%">LogInOut.php</td><td><i>$loginoutplver</i><input type=hidden name="LogInOut" value="$loginoutplver"></td>
      </tr><tr>
        <td width="30%">Maintenance.php</td><td><i>$maintenanceplver</i><input type=hidden name="Maintenance" value="$maintenanceplver"></td>
      </tr><tr>
        <td width="30%">ManageBoards.php</td><td><i>$manageboardsplver</i><input type=hidden name="ManageBoards" value="$manageboardsplver"></td>
      </tr><tr>
        <td width="30%">ManageCats.php</td><td><i>$managecatsplver</i><input type=hidden name="ManageCats" value="$managecatsplver"></td>
      </tr><tr>
        <td width="30%">Memberlist.php</td><td><i>$memberlistplver</i><input type=hidden name="Memberlist" value="$memberlistplver"></td>
      </tr><tr>
        <td width="30%">MessageIndex.php</td><td><i>$messageindexplver</i><input type=hidden name="MessageIndex" value="$messageindexplver"></td>
      </tr><tr>
        <td width="30%">ModifyMessage.php</td><td><i>$modifymessageplver</i><input type=hidden name="ModifyMessage" value="$modifymessageplver"></td>
      </tr><tr>
        <td width="30%">ModSettings.php</td><td><i>$modsettingsphpver</i><input type=hidden name="ModSettings" value="$modsettingsphpver"></td>
      </tr><tr>
        <td width="30%">MoveThread.php</td><td><i>$movethreadplver</i><input type=hidden name="MoveThread" value="$movethreadplver"></td>
      </tr><tr>
        <td width="30%">Notify.php</td><td><i>$notifyplver</i><input type=hidden name="Notify" value="$notifyplver"></td>
      </tr><tr>
        <td width="30%">Poll.php</td><td><i>$pollplver</i><input type=hidden name="Poll" value="$pollplver"></td>
      </tr><tr>
        <td width="30%">Post.php</td><td><i>$postplver</i><input type=hidden name="Post" value="$postplver"></td>
      </tr><tr>
        <td width="30%">Profile.php</td><td><i>$profileplver</i><input type=hidden name="Profile" value="$profileplver"></td>
      </tr><tr>
        <td width="30%">Recent.php</td><td><i>$recentplver</i><input type=hidden name="Recent" value="$recentplver"></td>
      </tr><tr>
        <td width="30%">Register.php</td><td><i>$registerplver</i><input type=hidden name="Register" value="$registerplver"></td>
      </tr><tr>
        <td width="30%">RemoveOldThreads.php</td><td><i>$removeoldthreadsplver</i><input type=hidden name="RemoveOldThreads" value="$removeoldthreadsplver"></td>
      </tr><tr>
        <td width="30%">RemoveThread.php</td><td><i>$removethreadplver</i><input type=hidden name="RemoveThread" value="$removethreadplver"></td>
      </tr><tr>
        <td width="30%">RepairBoards.php</td><td><i>$repairboardsphpver</i><input type=hidden name="RepairBoards" value="$repairboardsphpver"></td>
      </tr><tr>
        <td width="30%">Search.php</td><td><i>$searchplver</i><input type=hidden name="Search" value="$searchplver"></td>
      </tr><tr>
        <td width="30%">SendTopic.php</td><td><i>$sendtopicplver</i><input type=hidden name="SendTopic" value="$sendtopicplver"></td>
      </tr><tr>
        <td width="30%">Security.php</td><td><i>$securityplver</i><input type=hidden name="Security" value="$securityplver"></td>
      </tr><tr>
        <td width="30%">Subs.php</td><td><i>$subsplver</i><input type=hidden name="Subs" value="$subsplver"></td>
      </tr><tr>
		<td colspan=2><br><input type=submit value="$txt[dvc2]"></td>
	  </tr>
    </table>
	</form>
    </td>
  </tr>
</table>
EOT;
	footer();
	obExit();
}

function getYaBBinfo()
{
	global $boarddir,$db_prefix;
	$request = mysql_query("SELECT value FROM {$db_prefix}settings WHERE variable='yabbinfo'");
	$row = mysql_fetch_row($request);
	if ($row[0] < time()-0){
		$er = error_reporting(0);
		$contents = @file ("http://www.yabb.info/yabbinfo.xml");
		if (!$contents)
			return false;
		error_reporting($er);

		$completecontent = implode('',$contents);
		$fh = @fopen("$boarddir/yabbinfo.xml","w");
		if ($fh)
		{
			@fputs($fh,$completecontent);
			$request = mysql_query("UPDATE {$db_prefix}settings SET value=".time()." WHERE variable='yabbinfo'");	    
		}
		@fclose ($fh);
	}
	else
	{
		$contents = file ("$boarddir/yabbinfo.xml");
		$completecontent = implode('',$contents);
	}

	$items = explode('<infoitem>',$completecontent);

	$infoitems = array();
	for ($i = 1; $i < sizeof($items); $i++)
	{
		$tempitem = array();
		preg_match("/<subject>(.*)<\/subject>/",$items[$i],$matches);
		$tempitem['subject'] = $matches[1];
		preg_match("/<url>(.*)<\/url>/",$items[$i],$matches);
		$tempitem['url'] = $matches[1];
		preg_match("/<timestamp>(.*)<\/timestamp>/",$items[$i],$matches);
		$tempitem['logTime'] = $matches[1];
		preg_match("/<author>(.*)<\/author>/",$items[$i],$matches);
		$tempitem['author'] = $matches[1];
		preg_match("/<details>(.*)<\/details>/",$items[$i],$matches);
		$tempitem['details'] = $matches[1];
		$infoitems[] = $tempitem;
	}
	return ($infoitems);
}
?>
