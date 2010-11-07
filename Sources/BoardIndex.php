<?php
/*****************************************************************************/
/* BoardIndex.php                                                            */
/*****************************************************************************/
/* YaBB: Yet another Bulletin Board                                          */
/* Open-Source Project started by Zef Hemel (zef@zefnet.com)                 */
/* Software Version: YaBB SE                                                 */
/* ========================================================================= */
/* Floating Category Hack by Tywick:   http://tywick.com                     */
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

$boardindexplver="YaBB SE 1.3.1";

function BoardIndex (){
	global $imagesdir,$txt,$scripturl,$mbname,$curposlinks,$showlatestmember,$censored,$maxmessagedisplay;
	global $color,$imagesdir,$enable_ubbc,$shownewsfader,$faderpath,$settings,$fadertime,$recentsender;
	global $username,$curboard,$showmarkread,$img,$Show_RecentBar,$Show_MemberBar,$realNames;
	global $sourcedir,$reminderurl,$Cookie_Length,$cgi,$yytitle,$ID_MEMBER,$modSettings,$db_prefix;
    $result = mysql_query("SELECT memberName,realName FROM {$db_prefix}members ORDER BY dateRegistered DESC LIMIT 1");
	$temp = mysql_fetch_row($result);
	$latestmember = $temp[0];
	$latestRealName = $temp[1];
    if (!$result) { echo "latestmember SQL failed";}
	$result = mysql_query("SELECT COUNT(*) as memcount FROM {$db_prefix}members;");
	$temp = mysql_fetch_row($result);
    $memcount = $temp[0];
    if (!$result) { echo "memcount SQL failed";}
	$result = mysql_query("SELECT COUNT(*) as totalm FROM {$db_prefix}messages as m,{$db_prefix}boards as b,{$db_prefix}topics as t,{$db_prefix}categories as c WHERE (m.ID_TOPIC=t.ID_TOPIC && t.ID_BOARD=b.ID_BOARD && b.ID_CAT=c.ID_CAT && ('$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || c.memberGroups=''))");

	$temp = mysql_fetch_row($result);
    $totalm = $temp[0];
    if (!$result) { echo "totalm SQL failed";}
	$result = mysql_query("SELECT COUNT(*) as totalt FROM {$db_prefix}topics;");
	$temp = mysql_fetch_row($result);
    $totalt = $temp[0];
    if (!$result) { echo "totalt SQL failed";}
   $euser=urlencode($latestmember);
 	$thelatestmember = "$txt[201] <a href=\"$scripturl?action=viewprofile;user=$euser\"><b>$latestRealName</b></a>$txt[581]";
	$thelatestmember2 = "$txt[656] <b><a href=\"$scripturl?action=viewprofile;user=$euser\">$latestRealName</a></b>";
	$yytitle = "$txt[18]";
	template_header();
	$curforumurl = $curposlinks ? "<a href=\"$scripturl\" class=\"nav\">$mbname</a>" : $mbname;
// Build the link tree
	$displayLinkTree = 	$modSettings['enableInlineLinks']? "<font class=\"nav\"><b>$curforumurl</b></font>"  :  "<font class=\"nav\"><IMG SRC=\"$imagesdir/open.gif\" BORDER=\"0\" alt=\"\"> <b>$curforumurl</b></font>" ;

	print <<<EOT

<!--Start Tywick Cat Float part one / the Navigation -->
<!-- <table width="100%" align="center" class="bordercolor" cellspacing="0" cellpadding="1" border="0"><tr><td> -->

<table width="100%" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="bottom" >$displayLinkTree</td>
    <td align="right" >
EOT;
	if ($modSettings['enableSP1Info'] != 1)
		print "<font size=\"2\">$txt[19]: $memcount &nbsp;&bull;&nbsp; $txt[95] $totalm &nbsp;&bull;&nbsp; $txt[64] $totalt </font>";
	if ($showlatestmember == 1 && $modSettings['enableSP1Info'] != 1)
		print "	<br><font size=\"2\">".$thelatestmember."</font>\n";
	print <<<EOT
    </td>
  </tr>
</table>

<!--</td></tr></table>-->
<!--End Tywick Cat Float Part one-->

EOT;
	if($shownewsfader == 1) {
		if(!isset($fadertime)) { $fadertime = 5000; }

		print <<<EOT
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor"><tr><td>
<table border="0" width="100%" cellspacing="1" cellpadding="5" class="bordercolor">
  <tr>
    <td class="titlebg" align="center">
    <b>$txt[102]</b></td>
  </tr><tr>
    <td class="windowbg2" valign="middle" align="center" height="60">
    <SCRIPT LANGUAGE="JavaScript1.2" TYPE="text/javascript">
    <!--
	var delay = $fadertime
	var bcolor = "$color[windowbg2]"
	var tcolor = "$color[fadertext2]"
	var fcontent = new Array()
	begintag = '<font size="2"><B>'

EOT;

	$request = mysql_query("SELECT value FROM {$db_prefix}settings WHERE variable='news'");
	$temp = mysql_fetch_row($request);
	$newslines = str_replace("\r","",trim($temp[0]));
	$newslines = explode("\n",$temp[0]);
	for($i = 0; $i < sizeof($newslines); $i++){
		if ($enable_ubbc==1)
			$newslines[$i] = DoUBBC($newslines[$i]);
		$newslines[$i] = str_replace("\"","'",trim($newslines[$i]));
		$newslines[$i] = str_replace("/","\/",$newslines[$i]);
		print "fcontent[$i] = \"$newslines[$i]\"\n";
	}
	print <<<EOT
	closetag = '<\/b><\/font>'
	 // -->
    </SCRIPT>
    <SCRIPT LANGUAGE="JavaScript1.2" src="$faderpath" TYPE="text/javascript"></SCRIPT>
<script language="JavaScript1.2" type="text/javascript">
<!--
if (navigator.appVersion.substring(0,1) < 5 && navigator.appName == "Netscape") {
   var fwidth = screen.availWidth / 2;
   var bwidth = screen.availWidth / 4;
   document.write('<ilayer id="fscrollerns" width='+fwidth+' height=35 left='+bwidth+' top=0><layer id="fscrollerns_sub" width='+fwidth+' height=35 left=0 top=0><\/layer><\/ilayer>');
}
else if (navigator.userAgent.search(/Opera/) != -1 || (navigator.platform != "Win32" && navigator.userAgent.indexOf('Gecko') == -1)) {
   document.open();
   for(i=0;i<fcontent.length;++i) {
      document.write(begintag+fcontent[i]+closetag+"<br>");
   }
   document.close();
}
else {
   document.write('<div id="fscroller" style="width:90% height:15px; padding:2px"><\/div>');
}
window.onload = fade;
// -->
</script>
    </td>
  </tr>
</table>
</td></tr></table>
EOT;
		}
	print <<<EOT
<table border="0" width="100%" cellspacing="0" cellpadding="0"><tr><td>
<table border="0" width="100%" cellspacing="0" cellpadding="0">

EOT;

	$result = mysql_query("SELECT name,ID_CAT FROM {$db_prefix}categories WHERE ('$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || FIND_IN_SET('$settings[7]',memberGroups)!=0 || memberGroups='') ORDER BY catOrder");

	while ($row_cat = mysql_fetch_array($result))
	{


		print <<<EOT

<!--Start Tywick cat floater 1.0 part two / the Categories-->
<tr><td height="27">


</td></tr>
<tr><td width="100%" colspan="5">

<table width="100%" cellpadding="0" cellspacing="1" class="bordercolor" border="0">
<tr><td><table width="100%" cellpadding="4" cellspacing="0" border="0">

  <tr>
    <td colspan="5" class="catbg" height="18"><a name="$row_cat[ID_CAT]"><b>$row_cat[name]</b></a></td>
  </tr>

  <tr>
    <td class="titlebg" colspan="2" border="1" ><b>$txt[20]</b></td>
    <td class="titlebg" width="6%" align="center" border="1" ><b>$txt[330]</b></td>
    <td class="titlebg" width="6%" align="center" border="1" ><b>$txt[21]</b></td>
    <td class="titlebg" width="22%" align="center" border="1" ><b>$txt[22]</b></td>
  </tr>

</table></td></tr>
</table></td></tr>

<!--end Tywick Cat Float-->

EOT;
		
		$result2 = mysql_query("SELECT ID_BOARD,name,description,moderators,numPosts,numTopics FROM {$db_prefix}boards WHERE (ID_CAT=$row_cat[ID_CAT]) ORDER BY boardOrder");
		while ($row_board = mysql_fetch_array($result2))
		{
			$result3 = mysql_query("SELECT m.posterName,m.ID_MEMBER,m.posterTime,m.modifiedTime,m.subject,t.ID_TOPIC,t.numReplies,r.reputation FROM {$db_prefix}messages as m, {$db_prefix}topics as t, {$db_prefix}reputation r WHERE (m.ID_MSG=t.ID_LAST_MSG && t.ID_BOARD=$row_board[ID_BOARD] && m.ID_MEMBER=r.ID_MEMBER) ORDER BY m.posterTime DESC LIMIT 1");
			$latestPostName = $txt[470];
			$latestPostTime = $txt[470];
			$latestPostID = '-1';
			$latestModTime = $subject = $topicID = '';
			$numReplies = 0;
			if (mysql_num_rows($result3) > 0)
				list ($latestPostName,$latestPostID,$latestPostTime,$latestModTime,$subject,$topicID,$numReplies) = mysql_fetch_row($result3);
			$latestEditTime = $latestModTime = $latestPostTime;

			if ($latestPostID != '-1') {
				$result3 = mysql_query("SELECT memberName,realName FROM {$db_prefix}members WHERE ID_MEMBER=$latestPostID");
				$rowmem = mysql_fetch_row($result3);
				$latestPostRealName = isset($rowmem[1])?$rowmem[1]:$rowmem[0];
			}


			$themoderators = explode(",",$row_board['moderators']);
			for ($i = 0; $i < sizeof($themoderators); $i++){
				$themoderators[$i] = trim($themoderators[$i]);
				if ($themoderators[$i]!=''){
				LoadRealName($themoderators[$i]);
				$euser=urlencode($themoderators[$i]);
				$themoderators[$i] = "<a href=\"$scripturl?action=viewprofile;user=$euser\"><acronym			title=\"$txt[62]\">{$realNames[$themoderators[$i]]}</acronym></a>";
				}
			}

			$showmods = implode (", ",$themoderators);
			if ($showmods!='')
			{
                 if (sizeof($themoderators)>1)
                  $showmods = "<BR><font size=\"1\"><i>$txt[299]: $showmods</i></font>";
                 else
                  $showmods = "<BR><font size=\"1\"><i>$txt[298]: $showmods</i></font>";
			}else{
				$showmods = "";}

			$new = "<img src=\"$imagesdir/off.gif\" alt=\"$txt[334]\">";

			if ($username!= 'Guest')
			{
				$result3 = mysql_query("SELECT logTime FROM {$db_prefix}log_boards WHERE (ID_BOARD=$row_board[ID_BOARD] && memberName='$username' && logTime >= $latestEditTime) LIMIT 1");
				$result3b = mysql_query("SELECT logTime FROM {$db_prefix}log_mark_read WHERE (ID_BOARD=$row_board[ID_BOARD] && memberName='$username' && logTime >= $latestEditTime) LIMIT 1");

				if (($latestPostTime != $txt[470]) && ((mysql_num_rows($result3) + mysql_num_rows($result3b)) == 0))
					$new = "<img src=\"$imagesdir/on.gif\" alt=\"$txt[333]\">";
			}

			if ($latestPostName != $txt[470] && $latestPostID != '-1') {
				$euser=urlencode($latestPostName);
				$latestPostName = "<a href=\"$scripturl?action=viewprofile;user=$euser\">$latestPostRealName</a>";
			}
			if ($latestPostTime != $txt[470])
				$latestPostTime = timeformat($latestPostTime);

				CensorTxt($subject);

            //Messy crap - perhaps someone can alter this later
            $subject = str_replace ("&quot;", "\"", $subject);
            $subject = str_replace ("&#039;", "'", $subject);
            $subject = str_replace ("&amp;", "&", $subject);
            $subject = str_replace ("&lt;", "<", $subject);
            $subject = str_replace ("&gt;", ">", $subject);
			$subject = (strlen($subject) > 20)?substr($subject,0,20) . '...':$subject;
			$startPage = (floor(($numReplies)/$maxmessagedisplay)*$maxmessagedisplay);

			print <<<EOT

<!-- Start Tywick Float part three / the Boards(forums) -->
<tr><td width="100%" colspan="5">
<table width="100%" class="bordercolor" cellspacing="1" cellpadding="2" border="0">


  <tr>
    <td class="windowbg" width="6%" align="center" valign="top">$new</td>
    <td class="windowbg2" align="left" width="60%">
    <a name="$curboard"></a>
    <font size="2"><b><a href="$scripturl?board=$row_board[ID_BOARD]">$row_board[name]</a></b>
    <br>$row_board[description]</font>$showmods</td>
    <td class="windowbg" valign="middle" align="center" width="6%">$row_board[numTopics]</td>
    <td class="windowbg" valign="middle" align="center" width="6%">$row_board[numPosts]</td>
    <td class="windowbg2" valign="middle" width="22%"><font size="1">$latestPostTime<br />$txt[yse88] <a href="$scripturl?board=$row_board[ID_BOARD];action=display;threadid=$topicID;start=$startPage;boardseen=1">$subject</a><br> $txt[525] $latestPostName</font></td>
  </tr>

</table></td></tr>
<!--end Tywick Float-->

EOT;
		}
	}
	// load the number of users online right now
	$guests = 0;
	$tmpusers = array();
	$request3 = mysql_query("SELECT identity FROM {$db_prefix}log_online WHERE 1 ORDER BY logTime DESC");
	while ($tmp = mysql_fetch_array($request3))
	{
		$identity = $tmp[0];
		$euser=urlencode($identity);
		$request4 = mysql_query("SELECT realName, memberGroup FROM {$db_prefix}members WHERE (memberName='$identity') LIMIT 1");
		if (mysql_num_rows($request4) > 0){
			$tmp = mysql_fetch_row($request4);
            if ($tmp[1]=="Administrator")
              $tmpusers[] = "<a href=\"$scripturl?action=viewprofile;user=$euser\"><font color=\"red\">$tmp[0]</font></a>";
            elseif ($tmp[1]=="Global Moderator")
              $tmpusers[] = "<a href=\"$scripturl?action=viewprofile;user=$euser\"><font color=\"blue\">$tmp[0]</font></a>";
            elseif ($tmp[1]=="YaBB SE Developer")
              $tmpusers[] = "<a href=\"$scripturl?action=viewprofile;user=$euser\"><font color=\"green\">$tmp[0]</font></a>";
            elseif ($tmp[1]=="Mod Team")
              $tmpusers[] = "<a href=\"$scripturl?action=viewprofile;user=$euser\"><font color=\"orange\">$tmp[0]</font></a>";
            else
			  $tmpusers[] = "<a href=\"$scripturl?action=viewprofile;user=$euser\">$tmp[0]</a>";
		}
		else
			$guests ++;
	}
    //change here
	$users = "<font size=1>".implode(", ",$tmpusers)."</font>";
	$numusersonline = sizeof($tmpusers);

    //Determines most user online - both all time and per day
    $total_users=$guests+$numusersonline;
    $tot_date=time();
    if ($total_users>$modSettings['mostOnline']){
     $result=mysql_query("UPDATE {$db_prefix}settings SET value='$total_users' WHERE variable='mostOnline'");
     $result=mysql_query("UPDATE {$db_prefix}settings SET value='$tot_date' WHERE variable='mostDate'");
    }
    $mdate = getdate(time());
    $monthquery = mysql_query("SELECT MAX(mostOn) as mostOn FROM {$db_prefix}log_activity WHERE month = $mdate[mon] AND day = $mdate[mday] AND year = $mdate[year]");
    echo mysql_error();
    $temp = mysql_fetch_row($monthquery);
	$oldMost = $temp[0];
    if ($total_users>$oldMost){
     $statsquery = mysql_query("UPDATE {$db_prefix}log_activity SET mostOn = $total_users WHERE month = $mdate[mon] AND day = $mdate[mday] AND year = $mdate[year]");
                  if(mysql_affected_rows() == 0)
                    $statsquery = mysql_query("INSERT INTO {$db_prefix}log_activity (month, day, year, mostOn) VALUES ($mdate[mon], $mdate[mday], $mdate[year], $total_users)");
                }

    //End most users online

	if( $username != 'Guest' ) {
		// deal with instant messages
		$request3 = mysql_query("SELECT COUNT(*) FROM {$db_prefix}instant_messages WHERE (ID_MEMBER_TO=$ID_MEMBER && deletedBy != 1)");
		 $temp = mysql_fetch_row($request3);
		 $messnum = $temp[0];

		print <<<EOT

<!-- Start Tywick Float part four / the Legend -->
<tr><td height="20"></td></tr>
<tr><td colspan="6"><table width="100%" cellspacing="1" cellpadding="1" class="bordercolor">


  <tr>
    <td class="titlebg" colspan="6" align="center">
    <table cellpadding="0" border="0" cellspacing="0" width="100%">
      <tr>
        <td align="left">
        <img src="$imagesdir/new_some.gif" border="0" alt="$txt[333]">&nbsp;&nbsp;
        <img src="$imagesdir/new_none.gif" border="0" alt="$txt[334]"></td>
        <td align="right"><font size="1">&nbsp;
EOT;
		if($showmarkread==1)
			print " <a href=\"$scripturl?action=markallasread\">$img[markallread]</a>";
	print <<<EOT
        </font></td>
      </tr>
    </table>


</td></tr></table>
<!-- end tywick float -->
    </td>
  </tr>
EOT;
		}
print "</table></td></tr></table><br><BR>";
if( $username == 'Guest' )
		print "<form action=\"$cgi;action=login2\" method=\"POST\">\n";
print <<<EOT
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor"><tr><td>
<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
  <tr>
    <td class="titlebg" align="center" colspan="2">
    <b>$txt[685]</b></td>
  </tr>
EOT;

	if($Show_RecentBar == 1) {
		print <<<EOT

  <tr>
    <td class="catbg" colspan="2"><b>$txt[214]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/xx.gif" border="0" alt=""></td>
    <td class="windowbg2"><b><A href="$scripturl?action=recent">$txt[214]</A></b><br>
    <font size="1">
EOT;
	include_once ("$sourcedir/Recent.php");
	LastPost();
	print <<<EOT
    </tr>
EOT;
}
else if ($Show_RecentBar == 2)
{
      print <<<EOT

  <tr>
    <td class="catbg" colspan="2"><b>$txt[214]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/xx.gif" border="0" alt=""></td>
    <td class="windowbg2">
    <font size="1">
EOT;
   include_once ("$sourcedir/Recent.php");
   LastPostings();
   print <<<EOT
    </tr>
EOT;
}

	if($Show_MemberBar == 1) {
		print <<<EOT
  <tr>
    <td class="catbg" colspan="2"><b>$txt[331]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/guest.gif" border="0" width="20" alt=""></td>
    <td class="windowbg2" width="100%"><b><A href="$scripturl?action=mlall">$txt[332]</A></b><br><font size="1">$txt[200]</font></td>
  </tr>
EOT;
	}
	if ($modSettings['enableSP1Info'] == 1)
	{
print <<<EOT
  <tr>
    <td class="catbg" colspan="2"><b>$txt[645]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/info.gif" border="0" alt=""></td>
    <td class="windowbg2" width="100%">
	<table border="0" width="80%"><tr>
	<td><font size=1>$txt[94] $txt[64] <b>$totalt</b> &nbsp;&nbsp;&nbsp;&nbsp; $txt[94] $txt[95] <b>$totalm</b><br>
	$txt[659]
EOT;
include_once("$sourcedir/Recent.php");
$recentsender = "admin";
LastPost();
if ($modSettings['trackStats']==1)
$stats="<br><a href=\"$scripturl?board=;action=stats\">$txt[yse223]</a>";
else
$stats="";
print <<<EOT
	<br><a href="$scripturl?action=recent">$txt[234]</a>$stats</font></td>
	<td><font size=1>
$txt[94] $txt[19] <b><a href="$scripturl?action=mlall">$memcount</a></b><br>
$thelatestmember2<br>
EOT;
if( $username != 'Guest' )
	print "$txt[yse199]: <b><a href=\"$scripturl?board=;action=im\">$messnum</a></b>";
print <<<EOT
</font></td>
	</tr></table>
</td>
 </tr>
EOT;
	}
    if ($modSettings['trackStats']==1 && $modSettings['enableSP1Info'] != 1)
     $stats="<br><font size=1><a href=\"$scripturl?board=;action=stats\">$txt[yse223]</a></font>";
     else
     $stats="";
	print <<<EOT
  <tr>
    <td class="catbg" colspan="2"><b>$txt[158]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/online.gif" border="0" alt=""></td>
    <td class="windowbg2" width="100%">$guests $txt[141], $numusersonline $txt[142]<br>$users $stats</td>
  </tr>
EOT;
	if( $username != 'Guest' && $modSettings['enableSP1Info'] != 1) {
		print <<<EOT
  <tr>
    <td class="catbg" colspan="2"><b>$txt[159]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/message_sm.gif" border="0" alt=""></td>
    <td class="windowbg2" valign="top"><b><a href="$scripturl?board=;action=im">$txt[159]</a></b><br><font size="1">$txt[660] $messnum
EOT;
		if($messnum == 1) { print " ".$txt[471]; }
		else { print " ".$txt[153]; }
		print <<<EOT
    .... $txt[661] <a href="$scripturl?board=;action=im">$txt[662]</a> $txt[663]</font></td>
  </tr>
EOT;
}
	if($modSettings['enableVBStyleLogin']!='1' && $username=='Guest') {
		print <<<EOT
  <tr>
    <td class="catbg" colspan="2"><b>$txt[34]</b>
    <a href="$reminderurl?action=input_user"><small>($txt[315])</small></a></td>
  </tr><tr>
    <td class="windowbg" width="20" align="center"><img src="$imagesdir/login_bindex.gif" border="0" alt=""></td>
    <td class="windowbg" valign="middle">
    <table border="0" cellpadding="2" cellspacing="0" align="center" width="100%">
      <tr>
        <td valign="middle" align="left"><b>$txt[35]:</b><br><input type="text" name="user" size="15"></td>
        <td valign="middle" align="left"><b>$txt[36]:</b><br><input type="password" name="passwrd" size="15"></td>
        <td valign="middle" align="left"><b>$txt[497]:</b><br><input type="text" name="cookielength" size="4" maxlength="4" value="$Cookie_Length"></td>
        <td valign="middle" align="left"><b>$txt[508]:</b><br><input type="checkbox" name="cookieneverexp" checked></td>
        <td valign="middle" align="left"><input type="submit" value="$txt[34]"></td>
      </tr>
    </table>
	</td>
  </tr>
EOT;
	}

	print "</table></td></tr></table>";
if( $username == 'Guest' )
	print "</form>";

	footer();
	obExit();
}

function msnCenter (){
 global $imagesdir, $yytitle;
 $yytitle = "MSN Center";
 template_header();
 print <<<EOT
<span id=appload style="display:none"></span>

<SCRIPT language=JScript><!--
var hotLog=false;
var isDrawn_=false;
function track(){
if(email.innerText!="Inbox")
mctrack.src="http://go.msn.com/P/6/";
}
//--></SCRIPT>

<SCRIPT language=VBScript>
Dim noncgi
noncgi="$imagesdir/"
</SCRIPT>

<SCRIPT language=VBScript id=mcvbs src="mc.vbs">
</SCRIPT>

<sCRIPT language=VBScript>
SUB SubmitHM(frm)
If(window.event.keyCode)=13 Then
If valHotmail Then
document.HotmailForm.Submit()
End If
End If
END SUB
</SCRIPT>

<object classid="clsid:F3A614DC-ABE0-11d2-A441-00C04F795683" codebase="#Version=2,0,0,83" codetype=application/x-oleobject id=MsgrObj width=0 height=0>
<span style=display:none;>&nbsp;</span>
</object>

<script for=mcvbs event=onReadyStateChange language=VBScript>
If mcvbs.readyState="complete" And Not isDrawn_ Then
isDrawn_=True
DrawInitialState
End If
</script>
<script event="onload" for="window" language="VBScript">
// FDRcountLoads
If Not isDrawn Then
isDrawn = True
DrawInitialState
END if
</script>


<table border="0" cellpadding="4" cellspacing="1" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" colspan="2">
        <img src="$imagesdir/msn.gif" alt="" border="0">&nbsp;
        <font size=2 class="text1"><B>Msn Message Center</b></font>
    </td>
  </tr><tr id=emailhdr>
     <font face=arial,sans-serif size=2><b><span id=welcome></span></b></font><img id=mctrack alt="" width=1 height=1>
      <td class="windowbg"><font face=verdana,sans-serif size=1><b>E-mail</b></font></td>
  </tr>
    <tr>
      <td class="windowbg"> <span id=loginbox style="display:none"> <label for=login><font face=verdana,sans-serif size=1>Hotmail
        <u>M</u>ember Name:</font></label><br>
        <form style="margin:0;" name=HotmailForm ACTION="https://lc1.law13.hotmail.passport.com/cgi-bin/dologin/" target="_blank" method=post onkeydown="SubmitHM(this.event)" onsubmit="return valHotmail();">
          <input type="text" name="login" id=login size="15" maxlength="64" accesskey="m">
          <br>
          <input type=hidden name=curmbox value=ACTIVE>
          <input type=hidden name=js value=yes>
          <input type=hidden name="6c6f7264" value="www.msn.com">
          <font face=verdana,sans-serif size=1><u>P</u>assword:</font><br>
          <input type="password" name="passwd" size="15" maxlength="64" accesskey="p">
          <INPUT TYPE=IMAGE height=17 width=17 ID=m BORDER=0 SRC=$imagesdir/go_m.gif class=srchBtn ALT="Sign in to Hotmail">
          <br>
          <a href="http://go.msn.com/P/1/" class=mclink target=_blank><font face=verdana,sans-serif size=1>Sign
          up for free e-mail</font></a>
        </form>
        </span> <span id=goinbox style="display:none"> <a href="http://go.msn.com/P/2/" onclick=track() class=mclink target=_blank><img src="$imagesdir/newmail1.gif" align=absbottom WIDTH=16 HEIGHT=12 border=0 ALT="Go to Inbox"></a>
        <a href="http://go.msn.com/P/2/" onclick=track() class=mclink target=_blank><font face=verdana,sans-serif size=1><span id=email></span></font></a><br>
        </span> <span id=loginform style="display:none"></span> </td>
    </tr>
    <tr>
      <td class="windowbg"><font face=verdana,sans-serif size=1><b>Online Contacts</b></font></td>
    </tr>
    <tr>
      <td class="windowbg"> <span id=getmsgr style="display:none"> To activate, <a href="http://go.msn.com/P/0/" class=mclink target=_blank><font face=verdana,sans-serif size=1>download
        the new MSN Messenger Service.</font></a> </span> <span id=msgrlogon style="display:none">
        <font face=verdana,sans-serif size=1><span id=status></span></font> </span>
        <div id=noneol style="display:none"> </div>
        <span id=mUser style="display:none"> </span> <span id=cmore style="display:none">
        <a href="vbscript:op(-2)" name=mlink class=mclink><font face=verdana,sans-serif size=1><b><i>Open MSN Messenger main window</i></b></font></a> </span> </td>
     </tr>
</table>
EOT;
 footer();
 obExit();
}


function MarkAllRead (){
	global $settings,$username,$db_prefix;
	$result = mysql_query("SELECT c.ID_CAT,b.ID_BOARD FROM {$db_prefix}categories as c,{$db_prefix}boards as b WHERE ('$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || c.memberGroups='')");

	while (list($ID_CAT,$ID_BOARD) = mysql_fetch_row($result)){
		$request = mysql_query("SELECT logTime FROM {$db_prefix}log_mark_read WHERE (memberName='$username' AND ID_BOARD=$ID_BOARD) LIMIT 1");

		if (mysql_num_rows($request)==0)
			$request = mysql_query("INSERT INTO {$db_prefix}log_mark_read (logTime,memberName,ID_BOARD) VALUES (".time().",'$username',$ID_BOARD)");
		else
			$request = mysql_query("UPDATE {$db_prefix}log_mark_read SET logTime=".time()." WHERE (memberName='$username' AND ID_BOARD=$ID_BOARD)");

	}
	BoardIndex();
	obExit();
}
?>
