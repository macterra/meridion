<?php
/*****************************************************************************/
/* Post.php                                                                  */
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

$pollplver="YaBB SE 1.3.0";

function PostPoll (){
	global $username,$password,$txt,$img,$modSettings,$db_prefix;
	global $board,$enable_notification,$yytitle,$ubbcjspath,$scripturl;
	global $enable_ubbc,$showyabbcbutt,$realname,$realemail,$imagesdir,$mbname;
	global $cgi,$color,$threadid,$settings,$moderators,$sourcedir,$isAnnouncement;

	if ($username == 'Guest') { fatal_error($txt['yse28']); }
	if ($board == '' ) { fatal_error($txt[1]); }
	if ($modSettings['pollPostingRestrictions']=='1' && $settings[7] != 'Administrator')
		fatal_error($txt[1]);

	if ($isAnnouncement && $settings[7]!='Administrator' && $settings[7]!='Global Moderator')
		fatal_error($txt['announcement1']);

	include_once ("$sourcedir/Post.php");

	# Determine what category we are in.
	$request = mysql_query("SELECT b.ID_BOARD as bid,b.name as bname,c.ID_CAT as cid,c.name as cname FROM {$db_prefix}boards as b,{$db_prefix}categories as c WHERE (b.ID_BOARD=$board AND b.ID_CAT=c.ID_CAT)");

	if (mysql_num_rows($request) == 0)
		fatal_error($txt['yse232']	);

	$bcinfo = mysql_fetch_array($request);
	$curcat = $bcinfo['cid'];
	$cat = $bcinfo['cname'];
	$currentboard = $bcinfo['bid'];
	$boardname = $bcinfo['bname'];

	$notification = (!$enable_notification) ? '' : <<<EOT
<tr>
	<td align="right"><font size=2><b>$txt[131]:</b></font></td>
	<td><font size=2><input type=checkbox name="notify"></font> <font size=1> $txt[yse14]</font></td>
</tr>
EOT;
	$lockthread = ($settings[7]!='Administrator' && !in_array($username,$moderators)) ? '' : <<<EOT
<tr>
	<td align="right"><font size=2><b>$txt[yse13]:</b></font></td>
	<td><font size=2><input type=checkbox name="lock"></font> <font size=1> $txt[yse15]</font></td>
</tr>
EOT;

	$yytitle=$txt['yse21'];
	template_header();

	$msubject=$mname=$memail=$mdate=$musername=$micon=$mip=$mmessage=$mns=$mid='';

	print <<<EOT

<script language="JavaScript1.2" type="text/javascript">
<!--
function showimage()
{
	document.images.icons.src="$imagesdir/"+document.postmodify.icon.options[document.postmodify.icon.selectedIndex].value+".gif";
}
//-->
</script>
<form action="$cgi;action=postpoll2" method="post" name="postmodify" onSubmit="submitonce(this);">
<table  width="75%" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td valign=bottom colspan="2">
    <font size=2 class="nav"><B><img src="$imagesdir/open.gif" border="0" alt="">&nbsp;&nbsp;
    <a href="$scripturl" class="nav">$mbname</a><br>
    <img src="$imagesdir/tline.gif" border="0" alt=""><IMG SRC="$imagesdir/open.gif" border="0" alt="">&nbsp;&nbsp;
    <a href="$scripturl#$curcat" class="nav">$cat</a><br>
    <img src="$imagesdir/tline2.gif"  border="0" alt=""><IMG SRC="$imagesdir/open.gif" border="0" alt="">&nbsp;&nbsp;
    <a href="$cgi" class="nav">$boardname</a><br>
    <img SRC="$imagesdir/tline3.gif"  border="0" alt=""><IMG SRC="$imagesdir/open.gif" border="0" alt="">&nbsp;&nbsp;
    $txt[yse20]</b></font></td>
  </tr>
</table>
<table border="0"  width="75%" align="center" cellspacing="1" cellpadding="3" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[yse21]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <input type="hidden" name="threadid" value="$threadid">
    <table border=0 cellpadding="3" width="100%">
      <tr>
        <td align="right"><font size=2><b>$txt[70]:</b></font></td>
        <td><font size=2><input type=text name="subject" size="40" maxlength="50"></font></td>
      </tr><tr>
	<td align="right"><font size=2><b>$txt[71]:</b></font></td>
	<td>
<select name="icon" onChange="showimage()">
	<option value="xx">$txt[281]
	<option value="thumbup">$txt[282]
	<option value="thumbdown">$txt[283]
	<option value="exclamation">$txt[284]
	<option value="question">$txt[285]
	<option value="lamp">$txt[286]
	<option value="smiley">$txt[287]
	<option value="angry">$txt[288]
	<option value="cheesy">$txt[289]
	<option value="laugh">$txt[290]
	<option value="sad">$txt[291]
	<option value="wink">$txt[292]
	</select>
	<img src="$imagesdir/xx.gif" name="icons" border="0" hspace="15" alt=""></td>
</tr>
	<tr><td align="right"><font size=2><b>$txt[yse21]:</b></font></td><td align="left"><input type="text" name="question" size="40"></td></tr>
<tr><td>&nbsp;</td><td><font size=2>
$txt[yse22] 1: <input type="text" name="option1" size="25"><br>
$txt[yse22] 2: <input type="text" name="option2" size="25"><br>
$txt[yse22] 3: <input type="text" name="option3" size="25"><br>
$txt[yse22] 4: <input type="text" name="option4" size="25"><br>
$txt[yse22] 5: <input type="text" name="option5" size="25"><br>
$txt[yse22] 6: <input type="text" name="option6" size="25"><br>
$txt[yse22] 7: <input type="text" name="option7" size="25"><br>
$txt[yse22] 8: <input type="text" name="option8" size="25">
</font></td></tr>
EOT;
printPostBox("");
print <<<EOT
$lockthread
$notification
<tr>
	<td align="right"><font size=2><b>$txt[276]:</b></font><BR><BR></td>
	<td><input type=checkbox name="ns" value="NS"> <font size=1> $txt[277]</font><BR><BR></td>
</tr>
<tr>
	<td align="center" colspan="2">
    <font size="1" class="text1" color="#000000"><font style="font-weight:normal" size="1">$txt[yse25]</font></font><BR>
	<input type="hidden" name="waction" value="post">
	<input type="submit" name="post" value="$txt[105]" onClick="WhichClicked('post');" accesskey="s">
	<input type="reset" value="$txt[278]" accesskey="r">
	</td>
</tr>
<tr>
<td colspan=2></td>
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

function PostPoll2 (){
	global$username,$subject,$message,$icon,$ns,$txt,$notify,$MaxMessLen,$db_prefix,$threadid,$doLimitOne;
	global $settings,$board,$mreplies,$maxmessagedisplay,$enable_guestposting,$waction,$ID_MEMBER,$ubbcjspath ;
	global $REMOTE_ADDR,$cgi,$yySetLocation,$color,$sourcedir,$realname,$lock,$settings,$moderators;
	global $question,$option1,$option2,$option3,$option4,$option5,$option6,$option7,$option8,$isAnnouncement;
	if($username == 'Guest' && $enable_guestposting == 0) {	fatal_error($txt[165]); }

	if($username == 'Guest') { fatal_error($txt['yse28']); }
	if( $board == '' ) { fatal_error($txt[1]); }

	// did they toggle lock topic after post?
	$locked = (($settings[7]=='Administrator' || in_array($username,$moderators)) && $lock=='on')?1:0;

	if ($isAnnouncement && $settings[7]!='Administrator' && $settings[7]!='Global Moderator')
		fatal_error($txt['announcement1']);

	if (trim($subject)=='')
		fatal_error($txt[77]);
	if (trim($message)=='')
		fatal_error($txt[78]);

	if (trim($question)=='')
		fatal_error("$txt[164] $txt[yse21]");

	// if all the options are blank characters (including tabs & spaces), error out
	if ((trim($option1) == '') && (trim($option2) == '') &&(trim($option3) == '') &&(trim($option4) == '') &&(trim($option5) == '') &&(trim($option6) == '') &&(trim($option7) == '') &&(trim($option8) == ''))
		fatal_error($txt['yse26']);

	if (strlen($message)>$MaxMessLen) { fatal_error($txt[499]); }
	if( $waction == 'preview' ) { PreviewPoll(); }
	spam_protection();

	if (strlen($subject) > 50) { $subject = substr($subject,0,50); }
	$message = htmlspecialchars($message);
	$subject = htmlspecialchars($subject);

	// Preparse code (zef)
	$message = preparsecode($message, $realname, $username);

	$message = str_replace("\r","",$message);
   $message = str_replace("\n","<br>",$message);

//	$subject = str_replace("|","&#124",$subject);
//	$message = str_replace("|","&#124",$message);
	$message = str_replace("\t","&nbsp;&nbsp;&nbsp;",$message);

	$time = time();
	$se = $ns?0:1;

    if (get_magic_quotes_gpc()==0) {
        $subject = mysql_escape_string($subject);
        $message = mysql_escape_string($message);
        $question = mysql_escape_string($question);
        $option1 = mysql_escape_string($option1);
        $option2 = mysql_escape_string($option2);
        $option3 = mysql_escape_string($option3);
        $option4 = mysql_escape_string($option4);
        $option5 = mysql_escape_string($option5);
        $option6 = mysql_escape_string($option6);
        $option7 = mysql_escape_string($option7);
        $option8 = mysql_escape_string($option8);
        }

	$request = mysql_query("INSERT INTO {$db_prefix}messages (ID_TOPIC,ID_MEMBER,subject,posterName,posterEmail,posterTime,posterIP,smiliesEnabled,body,icon) VALUES ('-1',$ID_MEMBER,'$subject','$username','$settings[2]',$time,'$REMOTE_ADDR',$se,'$message','$icon')");
	$ID_MSG = mysql_insert_id();
	$request = mysql_query("INSERT INTO {$db_prefix}polls (question,option1,option2,option3,option4,option5,option6,option7,option8,votedMemberIDs) VALUES ('$question','$option1','$option2','$option3','$option4','$option5','$option6','$option7','$option8','')");
	$ID_POLL = mysql_insert_id();
	if ($ID_MSG > 0 && $ID_POLL>0)
	{
		$request = mysql_query("INSERT INTO {$db_prefix}topics (ID_BOARD,ID_MEMBER_STARTED,ID_MEMBER_UPDATED,ID_FIRST_MSG,ID_LAST_MSG,ID_POLL,locked) VALUES ($board,$ID_MEMBER,$ID_MEMBER,$ID_MSG,$ID_MSG,$ID_POLL,$locked)");
		if (mysql_insert_id() > 0)
		{
			$threadid = mysql_insert_id();
			$request = mysql_query("UPDATE {$db_prefix}messages SET ID_TOPIC=$threadid WHERE (ID_MSG=$ID_MSG)");
			$request = mysql_query("UPDATE {$db_prefix}boards SET numPosts=numPosts+1,numTopics=numTopics+1 WHERE (ID_BOARD=$board)");
			$mreplies = 0;
		}

		if ($isAnnouncement) {
         $reqAnn = mysql_query("SELECT b.notifyAnnouncements FROM {$db_prefix}boards as b,{$db_prefix}categories as c WHERE (b.ID_BOARD=$board AND b.ID_CAT=c.ID_CAT)");
         $rowAnn = mysql_fetch_array($reqAnn);

         if ($rowAnn['notifyAnnouncements'])
				include_once ("$sourcedir/Post.php");
         	NotifyUsersNewAnnouncement();
      }
	}

	// clear all the logs of people who read this thread
	$request = mysql_query("DELETE FROM {$db_prefix}log_topics WHERE ID_TOPIC=$threadid");
	$request = mysql_query("DELETE FROM {$db_prefix}log_boards WHERE ID_BOARD=$board");

	++$settings[6];
	$request = mysql_query("UPDATE {$db_prefix}members SET posts=posts+1 WHERE ID_MEMBER=$ID_MEMBER $doLimitOne");

	# Mark thread as read for the member.
	$request = mysql_query("INSERT INTO {$db_prefix}log_topics (logTime,memberName,ID_TOPIC) VALUES (".time().",'$username',$threadid)");

	if( $notify ) {
		include_once("$sourcedir/Notify.php");
		Notify2();
	}

	$yySetLocation = "$cgi";
	redirectinternal();
}

function Vote() {
	global$username,$subject,$message,$icon,$ns,$txt,$notify,$MaxMessLen,$db_prefix,$doLimitOne;
	global $settings,$board,$mreplies,$maxmessagedisplay,$enable_guestposting,$waction,$ID_MEMBER,$ubbcjspath ;
	global $cgi,$yySetLocation,$color,$sourcedir,$realname,$lock,$settings,$moderators;
	global $option,$poll,$start,$threadid;
	if($username == 'Guest') { fatal_error($txt['yse28']); } // Guests can't vote

	$request = mysql_query("SELECT votedMemberIDs,votingLocked FROM {$db_prefix}polls WHERE (ID_POLL='$poll' AND FIND_IN_SET('$ID_MEMBER',votedMemberIDs)=0) LIMIT 1");
	if (mysql_num_rows($request) == 0)
		fatal_error($txt['yse27']);

	if ($option == '')
		fatal_error($txt['yse26']);

	$tmp = mysql_fetch_row($request);
	if ($tmp[1] != '0')
		fatal_error($txt['yse27']);

	$votedMemberIDs = explode(",",$tmp[0]);

	$votedMemberIDs[] = $ID_MEMBER;
	$newIDs = implode(",",$votedMemberIDs);
	if (substr($newIDs,0,1)==',') {$newIDs = substr($newIDs,1); }

	$selectedoption = "votes$option";

	$request = mysql_query("UPDATE {$db_prefix}polls SET $selectedoption=$selectedoption+1,votedMemberIDs='$newIDs' WHERE ID_POLL='$poll' $doLimitOne");

	$yySetLocation = "$cgi;action=display;threadid=$threadid;start=$start";
	redirectexit();
}

function LockVoting()
{
	global $threadid,$start,$board,$settings,$cgi,$moderators,$username,$ID_MEMBER,$db_prefix,$doLimitOne;
	$request = mysql_query("SELECT t.ID_MEMBER_STARTED,t.ID_POLL,p.votingLocked FROM {$db_prefix}topics as t, {$db_prefix}polls as p WHERE (t.ID_TOPIC='$threadid' AND p.ID_POLL=t.ID_POLL) LIMIT 1");
	list ($memberID,$pollID,$votingLocked) = mysql_fetch_row($request);

	if ($settings[7] == 'Administrator' || in_array($username,$moderators) || $ID_MEMBER==$memberID)
	{
	if ($votingLocked == '1') { $votingLocked = '0'; }
	else if($votingLocked == '2' && $settings[7]=='Administrator') { $votingLocked = '0'; }
	else if($votingLocked == '2' && $settings[7]!='Administrator') { $fatal_error($txt['yse31']); }
	else if($votingLocked == '0' && $settings[7]=='Administrator') { $votingLocked = '2'; }
	else { $votingLocked = '1'; }

	$request = mysql_query("UPDATE {$db_prefix}polls SET votingLocked='$votingLocked' WHERE ID_POLL='$pollID' $doLimitOne");
	}
	$yySetLocation = "$cgi;action=display;threadid=$threadid;start=$start";
	redirectinternal();
}

function EditPoll (){
	global $username,$password,$txt,$img,$modSettings,$ID_MEMBER,$start,$db_prefix;
	global $board,$enable_notification,$yytitle,$ubbcjspath,$scripturl;
	global $enable_ubbc,$showyabbcbutt,$realname,$realemail,$imagesdir,$mbname;
	global $cgi,$color,$threadid,$settings,$moderators,$sourcedir;

	if ($username == 'Guest') { fatal_error($txt['yse28']); }
	if ($board == '' ) { fatal_error($txt[1]); }

	# Determine what category we are in.
	$request = mysql_query("SELECT b.ID_BOARD as bid,b.name as bname,c.ID_CAT as cid,c.name as cname,t.ID_MEMBER_STARTED FROM {$db_prefix}boards as b,{$db_prefix}categories as c,{$db_prefix}topics as t WHERE (b.ID_BOARD=t.ID_BOARD AND b.ID_CAT=c.ID_CAT AND t.ID_TOPIC='$threadid')");
	$bcinfo = mysql_fetch_array($request);
	$curcat = $bcinfo['cid'];
	$cat = $bcinfo['cname'];
	$currentboard = $bcinfo['bid'];
	$boardname = $bcinfo['bname'];

	if (($ID_MEMBER==$bcinfo['ID_MEMBER_STARTED'] && $modSettings['pollEditMode']=='2') || (in_array($username,$moderators) && in_array($modSettings['pollEditMode'],array('2','1'))) || $settings[7]=='Administrator')
		;// we're ok
	else
		fatal_error($txt[1]);

	$request = mysql_query("SELECT p.ID_POLL,p.question,p.option1,p.option2,p.option3,p.option4,p.option5,p.option6,p.option7,p.option8,p.votes1,p.votes2,p.votes3,p.votes4,p.votes5,p.votes6,p.votes7,p.votes8 FROM {$db_prefix}polls as p,{$db_prefix}topics as t WHERE (p.ID_POLL=t.ID_POLL && t.ID_TOPIC='$threadid') LIMIT 1");
	$pollinfo = mysql_fetch_array($request);

	$yytitle=$txt['yse39'];
	template_header();

	$msubject=$mname=$memail=$mdate=$musername=$micon=$mip=$mmessage=$mns=$mid='';

	$pollinfo["question"] = htmlspecialchars($pollinfo["question"]);
	for ($i = 1; $i <=8; $i++)
		$pollinfo["option$i"] = htmlspecialchars($pollinfo["option$i"]);

	print <<<EOT

<script language="JavaScript1.2" src="$ubbcjspath" type="text/javascript"></script>
<form action="$cgi;action=editpoll2;start=$start" method="post" onSubmit="submitonce(this);" name="postmodify">
<table  width="75%" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td valign=bottom colspan="2">
    <font size=2 class="nav"><B><img src="$imagesdir/open.gif" border="0" alt="">&nbsp;&nbsp;
    <a href="$scripturl" class="nav">$mbname</a><br>
    <img src="$imagesdir/tline.gif" border="0" alt=""><IMG SRC="$imagesdir/open.gif" border="0" alt="">&nbsp;&nbsp;
    <a href="$scripturl#$curcat" class="nav">$cat</a><br>
    <img src="$imagesdir/tline2.gif"  border="0" alt=""><IMG SRC="$imagesdir/open.gif" border="0" alt="">&nbsp;&nbsp;
    <a href="$cgi" class="nav">$boardname</a><br>
    <img SRC="$imagesdir/tline3.gif"  border="0" alt=""><IMG SRC="$imagesdir/open.gif" border="0" alt="">&nbsp;&nbsp;
    $txt[yse39]</b></font></td>
  </tr>
</table>
<table border="0"  width="75%" align="center" cellspacing="1" cellpadding="3" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[yse39]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <input type="hidden" name="threadid" value="$threadid">
    <input type="hidden" name="poll" value="$pollinfo[ID_POLL]">
    <table border=0 cellpadding="3" width="100%">
	<tr><td align="right"><font size=2><b>$txt[yse21]:</b></font></td><td align="left"><input type="text" name="question" size="40" value="$pollinfo[question]"></td></tr>
<tr><td>&nbsp;</td><td><font size=2>
$txt[yse22] 1: <input type="text" name="option1" size="25" value="$pollinfo[option1]"> ($pollinfo[votes1] $txt[yse42])<br>
$txt[yse22] 2: <input type="text" name="option2" size="25" value="$pollinfo[option2]"> ($pollinfo[votes2] $txt[yse42])<br>
$txt[yse22] 3: <input type="text" name="option3" size="25" value="$pollinfo[option3]"> ($pollinfo[votes3] $txt[yse42])<br>
$txt[yse22] 4: <input type="text" name="option4" size="25" value="$pollinfo[option4]"> ($pollinfo[votes4] $txt[yse42])<br>
$txt[yse22] 5: <input type="text" name="option5" size="25" value="$pollinfo[option5]"> ($pollinfo[votes5] $txt[yse42])<br>
$txt[yse22] 6: <input type="text" name="option6" size="25" value="$pollinfo[option6]"> ($pollinfo[votes6] $txt[yse42])<br>
$txt[yse22] 7: <input type="text" name="option7" size="25" value="$pollinfo[option7]"> ($pollinfo[votes7] $txt[yse42])<br>
$txt[yse22] 8: <input type="text" name="option8" size="25" value="$pollinfo[option8]"> ($pollinfo[votes8] $txt[yse42])
</font></td></tr>
<tr>
	<td align="right"><font size=2><b>$txt[yse40]:</b></font></td>
	<td><font size=2><input type=checkbox name="resetVoteCount"></font> <font size=1> $txt[yse41]</font></td>
</tr>
<tr>
	<td align="center" colspan="2">
    <font size="1" class="text1" color="#000000"><font style="font-weight:normal" size="1">$txt[yse25]</font></font><BR>
	<input type="hidden" name="waction" value="post">
	<input type="submit" name="post" value="$txt[105]" onClick="WhichClicked('post');" accesskey="s">
	<input type="reset" value="$txt[278]" accesskey="r">
	</td>
</tr>
<tr>
<td colspan=2></td>
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

function EditPoll2 ()
{
	global $username,$txt,$poll,$start,$threadid,$settings,$board,$ID_MEMBER,$db_prefix,$doLimitOne;
	global $cgi,$yySetLocation,$color,$sourcedir,$realname,$moderators,$modSettings;
	global $question,$option1,$option2,$option3,$option4,$option5,$option6,$option7,$option8,$resetVoteCount;

	if ($username == 'Guest') { fatal_error($txt['yse28']); }
	if ($board == '' ) { fatal_error($txt[1]); }

	# Determine what category we are in.
	$request = mysql_query("SELECT b.ID_BOARD as bid,b.name as bname,c.ID_CAT as cid,c.name as cname,t.ID_MEMBER_STARTED FROM {$db_prefix}boards as b,{$db_prefix}categories as c,{$db_prefix}topics as t WHERE (b.ID_BOARD=t.ID_BOARD AND b.ID_CAT=c.ID_CAT AND t.ID_TOPIC='$threadid')");
	$bcinfo = mysql_fetch_array($request);
	$curcat = $bcinfo['cid'];
	$cat = $bcinfo['cname'];
	$currentboard = $bcinfo['bid'];
	$boardname = $bcinfo['bname'];

	if (($ID_MEMBER==$bcinfo['ID_MEMBER_STARTED'] && $modSettings['pollEditMode']=='2') || (in_array($username,$moderators) && in_array($modSettings['pollEditMode'],array('2','1'))) || $settings[7]=='Administrator')
		;// we're ok
	else
		fatal_error($txt[1]);

	for ($i = 1; $i<=8;$i++)
		${"option$i"} = str_replace(array("&","\"","<",">"),array('&amp','&quot','&lt','&gt'),${"option$i"});
	$question = str_replace(array("&","\"","<",">"),array('&amp','&quot','&lt','&gt'),$question);

	if ($resetVoteCount=='on')
	$request = mysql_query("UPDATE {$db_prefix}polls SET votedMemberIDs='',votes1=0,votes2=0,votes3=0,votes4=0,votes5=0,votes6=0,votes7=0,votes8=0 WHERE ID_POLL='$poll' $doLimitOne");
    if (get_magic_quotes_gpc()==0) {
        $subject = mysql_escape_string($subject);
        $message = mysql_escape_string($message);
        $question = mysql_escape_string($question);
        $option1 = mysql_escape_string($option1);
        $option2 = mysql_escape_string($option2);
        $option3 = mysql_escape_string($option3);
        $option4 = mysql_escape_string($option4);
        $option5 = mysql_escape_string($option5);
        $option6 = mysql_escape_string($option6);
        $option7 = mysql_escape_string($option7);
        $option8 = mysql_escape_string($option8);
        }
	$request = mysql_query("UPDATE {$db_prefix}polls SET option1='$option1',option2='$option2',option3='$option3',option4='$option4',option5='$option5',option6='$option6',option7='$option7',option8='$option8',question='$question' WHERE ID_POLL='$poll' $doLimitOne");

	$yySetLocation = "$cgi;action=display;threadid=$threadid;start=$start";
	redirectexit();
}
?>
