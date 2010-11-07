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

$postplver="YaBB SE 1.3.1";

include_once ("$sourcedir/Export.php");

function Post (){
	global $username,$password,$enable_guestposting,$txt,$img,$title,$start,$ID_MEMBER,$sourcedir;
	global $board,$quote,$enable_notification,$yytitle,$ubbcjspath,$scripturl;
	global $enable_ubbc,$showyabbcbutt,$realname,$realemail,$imagesdir,$mbname;
	global $cgi,$color,$threadid,$settings,$moderators,$db_prefix,$modSettings;
	if($username == 'Guest' && $enable_guestposting == 0) { fatal_error($txt[165]); }
	if( $board == '' ) { fatal_error($txt[1]); }
	$quotemsg = $quote;

	$threadinfo = array('locked' => 0, 'ID_MEMBER_STARTED'=>'-1');
	$mstate = 0;
	if ($threadid != ''){
		$request = mysql_query("SELECT * FROM {$db_prefix}topics WHERE ID_TOPIC=$threadid");
		$threadinfo = mysql_fetch_array($request);
		$mstate = $threadinfo['locked'];
	} else if ($title != $txt[464])
		fatal_error ($txt[472]);

	if( $threadinfo['locked'] != 0 ) { fatal_error($txt[90]); }	// don't allow a post if it's locked

	# Limit posts per day by reputation
	# dbm 2006.12.30
	include_once("$sourcedir/Reputation.php");
	$reputation = UserReputation($ID_MEMBER);
	$maxposts = (int)(1+$reputation);
	$request = mysql_query("SELECT count(*) FROM `cov_messages` where (UNIX_TIMESTAMP()-posterTime) < (24*60*60) and id_member=$ID_MEMBER");
	if ($row = mysql_fetch_row($request)) {
	  $posts24 = $row[0];
	}
	else {
	  $posts24 = 0;
	}

	if ($posts24 >= $maxposts)
	  fatal_error("You have reached your maximum number of posts ($maxposts) for the last 24 hours");

	# Determine what category we are in.
	$request = mysql_query("SELECT b.ID_BOARD as bid,b.name as bname,c.ID_CAT as cid,c.memberGroups,c.name as cname,b.isAnnouncement FROM {$db_prefix}boards as b,{$db_prefix}categories as c WHERE (b.ID_BOARD=$board AND b.ID_CAT=c.ID_CAT)");

	if (mysql_num_rows($request) == 0)
		fatal_error($txt['yse232']	);

	$bcinfo = mysql_fetch_array($request);
	$curcat = $bcinfo['cid'];
	$cat = $bcinfo['cname'];
	$currentboard = $bcinfo['bid'];
	$boardname = $bcinfo['bname'];

	if ($bcinfo['isAnnouncement'] && $threadid=='' && $settings[7]!='Administrator' && $settings[7]!='Global Moderator')
		fatal_error($txt['announcement1']);

	$memgroups = explode(",",$bcinfo['memberGroups']);
	if (!(in_array($settings[7],$memgroups) || $memgroups[0]==null || $settings[7]=='Administrator' || $settings[7]=='Global Moderator'))
		fatal_error($txt[1]);

	$notification = (!$enable_notification || $username == 'Guest') ? '' : <<<EOT
<tr>
	<td align="right"><font size=2><b>$txt[131]:</b></font></td>
	<td><font size=2><input type=checkbox name="notify"></font> <font size=1> $txt[yse14]</font></td>
</tr>
EOT;
	$lockthread = ($settings[7]!='Administrator' && $settings[7]!='Global Moderator' && !in_array($username,$moderators)) ? '' : <<<EOT
<tr>
	<td align="right"><font size=2><b>$txt[yse13]:</b></font></td>
	<td><font size=2><input type=checkbox name="lock"></font> <font size=1> $txt[yse15]</font></td>
</tr>
EOT;


	if ($title==$txt[464]) {$title=$txt[33]; }
	else if ($title==$txt[116]) {$title=$txt[25]; }
	$title = str_replace("\\","",$title);
	$yytitle=$title;
	template_header();

	$name_field = $realname == '' ? "<tr><td align=\"right\"><font size=2><b>$txt[68]:</b></font></td><td><font size=2><input type=text name=\"name\" size=25></font></td></tr>" : '';

	$email_field = $realemail == '' ? "<tr><td align=\"right\"><font size=2><b>$txt[69]:</b></font></td><td><font size=\"2\"><input type=\"text\" name=\"email\" size=\"25\"></font>" : '';

	$msubject=$mname=$memail=$mdate=$musername=$micon=$mip=$mmessage=$mns=$mid='';
	$form_message = '';
	$form_subject = '';
	if ($threadid != '' && $quotemsg != '')
	{
		$request = mysql_query("SELECT subject,posterName,posterEmail,posterTime,icon,posterIP,body,smiliesEnabled,ID_MEMBER FROM {$db_prefix}messages WHERE ID_MSG=$quotemsg");
		list($msubject,$mname,$memail,$mdate,$micon,$mip,$mmessage,$mns,$mi) = mysql_fetch_row($request);
		if ($mi != '-1')
		{
			$request = mysql_query("SELECT realName FROM {$db_prefix}members WHERE ID_MEMBER='$mi' LIMIT 1");
			if (mysql_num_rows($request) != 0)
				list($mname) = mysql_fetch_row($request);
		}
		$form_message = str_replace("<br>","\n",$mmessage);
		$form_message = "[quote author=$mname link=board=$currentboard;threadid=$threadid;start=$start#$quotemsg date=$mdate]\n$form_message\n[/quote]";
		$form_subject = "$msubject";
		if (!stristr(substr($msubject,0,3),"re:"))
			$form_subject = "Re:$form_subject";
	}
	else if ($threadid != '' && $quotemsg == '')
	{
		$request = mysql_query("SELECT subject,posterName,posterEmail,posterTime,icon,posterIP,body,smiliesEnabled,ID_MEMBER FROM {$db_prefix}messages WHERE ID_TOPIC=$threadid ORDER BY ID_MSG LIMIT 1");
		list($msubject,$mname,$memail,$mdate,$micon,$mip,$mmessage,$mns,$mi) = mysql_fetch_row($request);

		$form_subject = "$msubject";
		if (!stristr(substr($msubject,0,3),"re:"))
			$form_subject = "Re:$form_subject";
	}

	if(!$form_subject) { $sub = "<i>$txt[33]</i>"; }
	else { $sub = $form_subject; }
# Build the link tree
	$displayLinkTree = $modSettings['enableInlineLinks'] ? "<font size=\"1\" class=\"nav\"><B><a href=\"$scripturl\" class=\"nav\">$mbname</a> </b>&nbsp;|&nbsp;<b> " : "<font size=\"2\" class=\"nav\"><B><img src=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$scripturl\" class=\"nav\">$mbname</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<a href=\"$scripturl#$curcat\" class=\"nav\">$cat</a> </b>&nbsp;|&nbsp;<b> " : "<img src=\"$imagesdir/tline.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$scripturl#$curcat\" class=\"nav\">$cat</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<a href=\"$cgi\" class=\"nav\">$boardname</a> </b>&nbsp;|&nbsp;<b> " : "<img src=\"$imagesdir/tline2.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$cgi\" class=\"nav\">$boardname</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "$title ( $sub )</b></font>" : "<img SRC=\"$imagesdir/tline3.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;$title ( $sub )</b></font>" ;

	print <<<EOT

<script language="JavaScript1.2" type="text/javascript">
<!--
function showimage()
{
	document.images.icons.src="$imagesdir/"+document.postmodify.icon.options[document.postmodify.icon.selectedIndex].value+".gif";
}
//-->
</script>
<form action="$cgi;action=post2" method="post" name="postmodify" onSubmit="submitonce(this);" enctype="multipart/form-data">
<table  width="75%" align="center" cellpadding="0" cellspacing="0">
  <tr>
     <td valign=bottom colspan="2">$displayLinkTree</td>
  </tr>
</table>
<table border="0"  width="75%" align="center" cellspacing="1" cellpadding="3" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$yytitle</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <input type="hidden" name="threadid" value="$threadid">
    <table border=0 cellpadding="3" width="100%">
      $name_field
	  $email_field
      <tr>
<b>new:</b> You have posted $posts24 out of your limit of $maxposts messages in the last 24 hours.<p>
        <td align="right"><font size=2><b>$txt[70]:</b></font></td>
        <td><font size=2><input type=text name="subject" value="$form_subject" size="40" maxlength="80"></font></td>
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
EOT;
printPostBox($form_message);
# Attachment field added by Meriadoc
$attachmentFields = "";
if($modSettings['attachmentEnable']==1) {
$attachmentFields = "
<tr>
	<td align=\"right\" valign=top><font size=2><b>$txt[yse119]:</b></font><BR><BR></td>
	<td><input type=\"file\" size=48 name=\"attachment\"><br><font size=1>$txt[yse120]: $modSettings[attachmentExtensions]<br>$txt[yse121]: $modSettings[attachmentSizeLimit] KB</font><BR><BR></td>
</tr>";
}
if($username == 'Guest' && $modSettings['attachmentEnableGuest']==0) {
	$attachmentFields = "";
}
if($modSettings['attachmentMemberGroups']!="") {
	//Fix by Omar Bazavilvazo -- Administrator & Global Moderator can't upload attachments when description was changed
	if(!(in_array($settings[7],explode(",",trim($modSettings['attachmentMemberGroups']))) || $settings[7]=='Administrator' || $settings[7]=='Global Moderator')) {
		$attachmentFields = "";
	}
}
# end of this part...
print <<<EOT
$lockthread
$notification
<tr>
	<td align="right"><font size=2><b>$txt[276]:</b></font><BR><BR></td>
	<td><input type=checkbox name="ns" value="NS"> <font size=1> $txt[277]</font><BR><BR></td>
</tr>

$attachmentFields

<tr>
	<td align="center" colspan="2">
    <font size="1" class="text1" color="#000000"><font style="font-weight:normal" size="1">$txt[yse16]</font></font><BR>
	<input type="hidden" name="waction" value="post">
	<input type="submit" name="post" value="$txt[105]" onClick="WhichClicked('post');" accesskey="s">
	<input type="submit" name="preview" value="$txt[507]" onClick="WhichClicked('preview');" accesskey="p">
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
	doshowthread();
	footer();
	obExit();
}

function Preview (){
	global $name,$email,$subject,$message,$icon,$ns,$threadid,$notify,$enable_ubbc,$txt,$waction,$db_prefix;
	global $censored,$yytitle,$imagesdir,$cgi,$to,$from,$board,$username,$ubbcjspath,$postid,$color,$modSettings;
	global $sourcedir,$realname,$lock,$enable_notification,$settings,$realemail,$moderators,$start,$postid;
	if (strlen($subject) > 80) { $subject = substr($subject,0,80); }
	$subject = stripcslashes($subject);
	$message = stripcslashes($message);
	$subject = htmlspecialchars($subject);
//	$subject = str_replace("|","&#124;",$subject);
	$message2 = $message;
	$message2 = htmlspecialchars($message2);
	$message2 = str_replace("\t","&nbsp;&nbsp;&nbsp;",$message2);
	$message2 = str_replace("\r","",$message2);
	$message2 = str_replace("\n","<br>",$message2);
//	$message2 = str_replace("|","&#124;",$message2);
//	$message =~ s/\cM//g;
//	$message =~ s~\[([^\]]{0,30})\n([^\]]{0,30})\]~\[$1$2\]~g;
//	$message =~ s~\[/([^\]]{0,30})\n([^\]]{0,30})\]~\[/$1$2\]~g;
//	$message =~ s~(\w+://[^<>\s\n\"\]\[]+)\n([^<>\s\n\"\]\[]+)~$1$2~g;

//	&CheckIcon;

	// create the options at the bottom
	$notify = ($notify!='')?' checked':'';
	$ns = isset($ns)?' checked':'';
	$lock = ($lock!='')?' checked':'';

	$name_field = $realname == '' ? "<tr><td align=\"right\"><font size=2><b>$txt[68]:</b></font></td><td><font size=2><input type=text name=name size=25 value=\"$name\"></font></td></tr>" : '';

	$email_field = $realemail == '' ? "</tr><tr><td align=\"right\"><font size=2><b>$txt[69]:</b></font></td><td><font size=2><input type=text name=email size=25 value=\"$email\">" : '';

	if ($username == 'Guest') {
		// we are doing a check to make sure the person posting, isn't using a name
		// that a member has already taken.
		$request = mysql_query("SELECT ID_MEMBER FROM {$db_prefix}members WHERE (memberName='$name' OR realName='$name') LIMIT 1");
		if (mysql_num_rows($request) > 0)
			fatal_error($txt[100]);

		// now make sure they aren't trying to use a reserved name
		$request = mysql_query("SELECT * FROM {$db_prefix}reserved_names ORDER BY setting");
		$matchword = $matchcase = $matchuser = $matchname = '';
		for ($i = 0; $i < 4; $i++)
		{
			$tmp = mysql_fetch_row($request);
			${$tmp[0]}=$tmp[1];
		}
		$namecheck = $matchcase ? $name : strtolower ($name);

		while ($tmp = mysql_fetch_row($request)) {
			if ($tmp[0] == 'word')
			{
				$reserved = $tmp[1];
				$reservecheck = $matchcase ? $reserved : strtolower ($reserved);
				if ($matchname) {
					if ($matchword)
						if ($namecheck == $reservecheck) { fatal_error("$txt[244] $reserved"); }
					else
						if (strstr($namecheck,$reservecheck)) { fatal_error("$txt[244] $reserved"); }
				}
			}
		}
	}
	if($enable_ubbc)
   {
      $message2 = preparsecode($message2, $realname, $username);
	  $es = ($ns==' checked')?0:1;
      $message2 = DoUBBC($message2,$es);
   }
	$destination = $submittxt = '';
	if( $waction == 'previewmodify' ) {
		$destination = "modify2;start=$start;postid=$postid";
		$submittxt = $txt[10];
	}
	else if( $waction == 'previewim' ) {
		$destination = 'imsend2';
		$submittxt = $txt[148];
	}
	else {
		$destination = 'post2';
		$submittxt = $txt[105];
	}

	$csubject = $subject;

	CensorTxt($csubject);
	CensorTxt($message2);

	$yytitle = "$txt[507] - $csubject";

	template_header();
	print<<<EOT
<script language="JavaScript1.2" src="$ubbcjspath" type="text/javascript"></script>
<table border=0 width="75%" cellspacing=1 cellpadding="3" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]">$csubject</font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]"><font size=2>$message2</font></td>
EOT;

print "</table><br>";
if ($waction!='previewim') {
print <<<EOT
<script language="JavaScript1.2" type="text/javascript">
<!--
function showimage()
{
	document.images.icons.src="$imagesdir/"+document.postmodify.icon.options[document.postmodify.icon.selectedIndex].value+".gif";
}
//-->
</script>
<table border="0"  width="75%" align="center" cellspacing="1" cellpadding="3" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$yytitle</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <form action="$cgi;action=$destination" method="post" name="postmodify" onSubmit="submitonce(this);" enctype="multipart/form-data">
    <input type="hidden" name="threadid" value="$threadid">
	<table border=0 cellpadding="3">
      $name_field
      $email_field
	  <tr>
        <td align="right"><font size=2><b>$txt[70]:</b></font></td>
        <td><font size=2><input type=text name="subject" value="$subject" size="40" maxlength="80"></font></td>
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
EOT;
} else {
print <<<EOT
	<table border=0 width="75%" align="center" cellpadding="3" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor">
   <tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <form action="$cgi;action=imsend2" method="post" name="postmodify" onSubmit="submitonce(this);">
    <table border=0 cellpadding=3>
      <tr>
        <td align="right"><font size=2><b>$txt[150]:</b></font></td>
        <td><font size=2><input type=text name="to" value="$to" size="20" maxlength="80">
        <font size=1">$txt[748]</font></font></td>
      </tr><tr>
        <td align="right"><font size=2><b>$txt[70]:</b></font></td>
        <td><font size=2><input type=text name="subject" value="$subject" size="40" maxlength="80"></font></td>
      </tr>
EOT;

}
printPostBox($message);
if ($waction != 'previewim') {
# Attachment field added by Meriadoc
$attachmentFields = "";
if($modSettings['attachmentEnable']==1) {
$attachmentFields = "
<tr>
	<td align=\"right\" valign=top><font size=2><b>$txt[yse119]:</b></font><BR><BR></td>
	<td><input type=\"file\" size=48 name=\"attachment\"><br><font size=1>$txt[yse120]: $modSettings[attachmentExtensions]<br>$txt[yse121]: $modSettings[attachmentSizeLimit] KB</font><BR><BR></td>
</tr>";
}
if($username == 'Guest' && $modSettings['attachmentEnableGuest']==0) {
	$attachmentFields = "";
}
if($modSettings['attachmentMemberGroups']!="") {
	if(!in_array($settings[7],explode(",",trim($modSettings['attachmentMemberGroups'])))) {
		$attachmentFields = "";
	}
}

	$notification = (!$enable_notification || $username == 'Guest') ? '' : <<<EOT
<tr>
	<td align="right"><font size=2><b>$txt[131]:</b></font></td>
	<td><font size=2><input type=checkbox name="notify" $notify></font> <font size=1> $txt[yse14]</font></td>
</tr>
EOT;
	$lockthread = ($settings[7]!='Administrator' && !in_array($username,$moderators)) ? '' : <<<EOT
<tr>
	<td align="right"><font size=2><b>$txt[yse13]:</b></font></td>
	<td><font size=2><input type=checkbox name="lock" $lock></font> <font size=1> $txt[yse15]</font></td>
</tr>
EOT;
print <<<EOT
$lockthread
$notification
<tr>
	<td align="right"><font size=2><b>$txt[276]:</b></font><BR><BR></td>
	<td><input type=checkbox name="ns" value="NS" $ns> <font size=1> $txt[277]</font><BR><BR></td>
</tr>

$attachmentFields

<tr>
	<td align="center" colspan="2">
    <font size="1" class="text1" color="#000000"><font style="font-weight:normal" size="1">$txt[yse16]</font></font><BR>
	<input type="hidden" name="waction" value="post">
	<input type="submit" name="post" value="$submittxt" onClick="WhichClicked('post');" accesskey="s">
	<input type="submit" name="$waction" value="$txt[507]" onClick="WhichClicked('$waction');" accesskey="p">
	</td>
</tr>
EOT;
} else {
print <<<EOT
	<tr>
      <td align=center colspan=2>
       <input type="hidden" name="waction" value="imsend">
       <input type="submit" value="$submittxt" onClick="WhichClicked('imsend');" accesskey="s">
       <input type="submit" name="preview" value="$txt[507]" onClick="WhichClicked('previewim');">
      </td>
     </tr>
EOT;
}
print <<<EOT
<tr>
<td colspan=2></td>
</tr>
</table></form>
</td>
</tr>
</table>
EOT;

footer();
	obExit();
}

function Post2 (){
	global $name,$email,$username,$subject,$message,$icon,$ns,$threadid,$txt,$notify,$MaxMessLen,$db_prefix;
	global $settings,$board,$mreplies,$maxmessagedisplay,$enable_guestposting,$waction,$ID_MEMBER,$ubbcjspath ;
	global $REMOTE_ADDR,$cgi,$yySetLocation,$color,$sourcedir,$realname,$lock,$settings,$moderators,$doLimitOne;
	global $attachment,$attachment_size,$attachment_name,$HTTP_POST_FILES,$modSettings,$isAnnouncement;
	if($username == 'Guest' && $enable_guestposting == 0) {	fatal_error($txt[165]); }

Export1();

	if ($threadid != ''){
		$request = mysql_query("SELECT locked FROM {$db_prefix}topics WHERE ID_TOPIC=$threadid");
		list($tmplocked) = mysql_fetch_array($request);
		if( $tmplocked != 0 ) { fatal_error($txt[90]); }	// don't allow a post if it's locked
	}

	# If poster is a Guest then evaluate the legality of name and email
	if ($username == 'Guest') {
		$name = trim($name);
		if ($name == '' || $name == '_' || $name == ' ')
			fatal_error($txt[75]);
		if (strlen($name) > 25)
			fatal_error($txt[568]);
		if ($email == '')
			fatal_error($txt[76]);
		if (!preg_match("/^[0-9A-Za-z@\._\-]+$/",$email))
			fatal_error($txt[243]);
	}

	if( !empty($threadid) && !is_numeric($threadid)) { fatal_error($txt[337]); }

	// did they toggle lock topic after post?
	$locked = (($settings[7]=='Administrator' || $settings[7]=='Global Moderator' || in_array($username,$moderators)) && $lock=='on')?1:0;

	$name = htmlspecialchars($name);
	$email = htmlspecialchars($email);
//	$name = str_replace("|","&#124;",$name);
//	$email = str_replace("|","",$email);

	//		$tempname = $name;
	//		$name =~ s/\_/ /g;

	if (trim($subject)=='')
		fatal_error($txt[77]);
	if (trim($message)=='')
		fatal_error($txt[78]);

	if (strlen($message)>$MaxMessLen) { fatal_error($txt[499]); }
	if( $waction == 'preview' ) { Preview(); }
	spam_protection();


	if (strlen($subject) > 80) { $subject = substr($subject,0,80); }
	$message = htmlspecialchars($message);
	$subject = htmlspecialchars($subject);

	if ($username != 'Guest')		# If not guest, get name and email.
	{
		$name = $username;
		$email = $settings[2];
	}

   // Preparse code (zef)
   $message = preparsecode($message, $realname, $name);

   $message = str_replace("\r","",$message);
   $message = str_replace("\n","<br>",$message);


//	$subject = str_replace("|","&#124;",$subject);
//	$message = str_replace("|","&#124;",$message);
	$message = str_replace("\t","&nbsp;&nbsp;&nbsp;",$message);



	if ($username == 'Guest')
	{
		# If user is Guest, then make sure the chosen name
		# is not reserved or used by a member.
		#
        if (get_magic_quotes_gpc()==0) {
        $name = mysql_escape_string($name);
        }

		$request = mysql_query("SELECT ID_MEMBER FROM {$db_prefix}members WHERE (memberName='$name' || realName='$name')");
		if (mysql_num_rows($request) != 0)
			fatal_error($txt[473]);

		// now make sure they arn't trying to use a reserved name
		$request = mysql_query("SELECT * FROM {$db_prefix}reserved_names ORDER BY setting");
		$matchword = $matchcase = $matchuser = $matchname = '';
		for ($i = 0; $i < 4; $i++)
		{
			$tmp = mysql_fetch_row($request);
			${$tmp[0]}=$tmp[1];
		}
		$namecheck = $matchcase ? $name : strtolower ($name);

		while ($tmp = mysql_fetch_row($request)) {
			if ($tmp[0] == 'word')
			{
				$reserved = $tmp[1];
				$reservecheck = $matchcase ? $reserved : strtolower ($reserved);
				if ($matchname) {
					if ($matchword)
						if ($namecheck == $reservecheck) { fatal_error("$txt[244] $reserved"); }
					else
						if (strstr($namecheck,$reservecheck)) { fatal_error("$txt[244] $reserved"); }
				}
			}
		}
	}

	# Validate the attachment if there is one
	# By Meriadoc 12/11-14/2001

	// change all ' ' to '_'
	$HTTP_POST_FILES['attachment']['name'] = str_replace(' ','_',$HTTP_POST_FILES['attachment']['name']);
	// remove all special chars
	$HTTP_POST_FILES['attachment']['name'] = preg_replace("/[^\w_.-]/","",$HTTP_POST_FILES['attachment']['name']);

	if($HTTP_POST_FILES['attachment']['name'] != "") {
		if($HTTP_POST_FILES['attachment']['size'] > $modSettings['attachmentSizeLimit'] * 1024) { fatal_error("$txt[yse122] $modSettings[attachmentSizeLimit]."); }
		if($modSettings['attachmentCheckExtensions'] == "1") {
			if (!in_array(strtolower(substr(strrchr($HTTP_POST_FILES['attachment']['name'], '.'), 1)),explode(",",strtolower($modSettings['attachmentExtensions'])))) {
				fatal_error("$HTTP_POST_FILES[attachment][name].<br>$txt[yse123] $modSettings[attachmentExtensions].");
			}
		}
		// make sure they aren't trying to upload a nasty file
		$disabledFiles = array('CON','COM1','COM2','COM3','COM4','PRN','AUX','LPT1');
		if (in_array(strtoupper(substr(strrchr($HTTP_POST_FILES['attachment']['name'], '.'), 1)),$disabledFiles)) {
				fatal_error("$HTTP_POST_FILES[attachment][name].<br>$txt[yse130b].");
		}
		if(file_exists($modSettings['attachmentUploadDir'] . "/" . $HTTP_POST_FILES['attachment']['name'])) { fatal_error($txt[yse125]); }
		$dirSize = "0";
		$dir=opendir($modSettings['attachmentUploadDir']);
		while($file=readdir($dir)) {
			$dirSize = $dirSize + filesize($modSettings['attachmentUploadDir'] . "/" .$file);
		}
		if($HTTP_POST_FILES['attachment']['size'] + $dirSize > $modSettings['attachmentDirSizeLimit'] * 1024) { fatal_error($txt[yse126]); }

		$parts = isset($HTTP_POST_FILES['attachment']) ? preg_split("~(\\|/)~",$HTTP_POST_FILES['attachment']['name']) : array();
		$destName = array_pop($parts);

		if(!move_uploaded_file($HTTP_POST_FILES['attachment']['tmp_name'], $modSettings['attachmentUploadDir'] . "/" . $destName)) { fatal_error("$txt[yse124]"); }
		chmod ("$modSettings[attachmentUploadDir]/$destName",0644) || $chmod_failed = 1;
	} else {
		$HTTP_POST_FILES['attachment']['name'] = "NULL";
	}

	# If no thread specified, this is a new thread.
	# Find a valid random ID for it.
	$newtopic = ($threadid == '')?true:false;
	$time = time();
	$se = $ns?0:1;

    if (get_magic_quotes_gpc()==0) {
        $subject = mysql_escape_string($subject);
        $message = mysql_escape_string($message);
        }

	if($newtopic)	# This is a new topic. Save it.
	{
		if ($isAnnouncement && $settings[7]!='Administrator' && $settings[7]!='Global Moderator')
			fatal_error($txt['announcement1']);
		$tmpname = ($HTTP_POST_FILES['attachment']['name']=="NULL")?"NULL":"'".mysql_escape_string($HTTP_POST_FILES['attachment']['name'])."'";
		$request = mysql_query("INSERT INTO {$db_prefix}messages (ID_MEMBER,subject,posterName,posterEmail,posterTime,posterIP,smiliesEnabled,body,icon,attachmentSize,attachmentFilename) VALUES ($ID_MEMBER,'$subject','$name','$email',$time,'$REMOTE_ADDR',$se,'$message','$icon','$attachment_size',$tmpname)");
		$ID_MSG = mysql_insert_id();
		if ($ID_MSG > 0)
		{
			$request = mysql_query("INSERT INTO {$db_prefix}topics (ID_BOARD,ID_MEMBER_STARTED,ID_MEMBER_UPDATED,ID_FIRST_MSG,ID_LAST_MSG,locked,numViews) VALUES ($board,$ID_MEMBER,$ID_MEMBER,$ID_MSG,$ID_MSG,$locked,0)");
			if (mysql_insert_id() > 0)
			{
				$threadid = mysql_insert_id();
				$request = mysql_query("UPDATE {$db_prefix}messages SET ID_TOPIC=$threadid WHERE (ID_MSG=$ID_MSG)");
				$request = mysql_query("UPDATE {$db_prefix}boards SET numPosts=numPosts+1,numTopics=numTopics+1 WHERE (ID_BOARD=$board)");
				$mreplies = 0;
                if ($modSettings['trackStats']==1){
                  $date = getdate(time());
                  $statsquery = mysql_query("UPDATE {$db_prefix}log_activity SET topics = topics + 1, posts = posts + 1 WHERE month = $date[mon] AND day = $date[mday] AND year = $date[year]");
                  if(mysql_affected_rows() == 0)
                    $statsquery = mysql_query("INSERT INTO {$db_prefix}log_activity (month, day, year, topics, posts) VALUES ($date[mon], $date[mday], $date[year], 1, 1)");
                }

				if ($isAnnouncement) {
	            $reqAnn = mysql_query("SELECT b.notifyAnnouncements FROM {$db_prefix}boards as b,{$db_prefix}categories as c WHERE (b.ID_BOARD=$board AND b.ID_CAT=c.ID_CAT)");
	            $rowAnn = mysql_fetch_array($reqAnn);

	            if ($rowAnn['notifyAnnouncements'])
	            	NotifyUsersNewAnnouncement();
            }
			}
		}

	}
	else			# This is an old thread. Save it.
	{
		$tmpname = ($HTTP_POST_FILES['attachment']['name']=="NULL")?"NULL":"'".mysql_escape_string($HTTP_POST_FILES['attachment']['name'])."'";
		$request = mysql_query("INSERT INTO {$db_prefix}messages (ID_TOPIC,ID_MEMBER,subject,posterName,posterEmail,posterTime,posterIP,smiliesEnabled,body,icon,attachmentSize,attachmentFilename) VALUES ($threadid,$ID_MEMBER,'$subject','$name','$email',$time,'$REMOTE_ADDR',$se,'$message','$icon','$attachment_size',$tmpname)");
		$ID_MSG = mysql_insert_id();
		if ($ID_MSG > 0)
		{
			$request = mysql_query("UPDATE {$db_prefix}topics SET ID_MEMBER_UPDATED=$ID_MEMBER,ID_LAST_MSG=$ID_MSG,numReplies=numReplies+1,locked=$locked WHERE (ID_TOPIC=$threadid)");
			$request = mysql_query("UPDATE {$db_prefix}boards SET numPosts=numPosts+1 WHERE (ID_BOARD=$board)");
			$mreplies ++;
            if ($modSettings['trackStats']==1){
                  $date = getdate(time());
                  $statsquery = mysql_query("UPDATE {$db_prefix}log_activity SET posts = posts + 1 WHERE month = $date[mon] AND day = $date[mday] AND year = $date[year]");
                  if(mysql_affected_rows() == 0)
                    $statsquery = mysql_query("INSERT INTO {$db_prefix}log_activity (month, day, year, posts) VALUES ($date[mon], $date[mday], $date[year], 1)");
                }
		}
	}

	// clear all the logs of people who read this thread
	//$request = mysql_query("DELETE FROM {$db_prefix}log_topics WHERE ID_TOPIC='$threadid'");
	//$request = mysql_query("DELETE FROM {$db_prefix}log_boards WHERE ID_BOARD='$board'");

	if($username != 'Guest') {

		$request = mysql_query("SELECT * FROM {$db_prefix}boards WHERE ID_BOARD='$board'");
		$pcount = mysql_fetch_array($request);
		$pcounter = $pcount['count'];
		if ($pcounter != 1) {
		++$settings[6];
		$request = mysql_query("UPDATE {$db_prefix}members SET posts=posts+1 WHERE ID_MEMBER=$ID_MEMBER $doLimitOne");
		}

		# Mark thread as read for the member.
		$request = mysql_query("UPDATE {$db_prefix}log_topics SET logTime=".time()." WHERE (memberName='$username' AND ID_TOPIC=$threadid)");
		if (mysql_affected_rows() == 0)
			$request = mysql_query("INSERT INTO {$db_prefix}log_topics (logTime,memberName,ID_TOPIC) VALUES (".time().",'$username',$threadid)");
	}

	# The thread ID, regardless of whether it's a new thread or not.
	$thread = $threadid;

	# Notify any members who have notification turned on for this thread.
	NotifyUsers();

	// turn notification on
	if ($notify != '')
	{
		include_once("$sourcedir/Notify.php");
		Notify2();
	}

Export2();

	# Let's figure out what page number to show
	$start = (floor ($mreplies / $maxmessagedisplay))*$maxmessagedisplay;

//	$yySetLocation = "$cgi;board=$board;action=display;threadid=$thread";
	$yySetLocation = "$cgi";
	redirectexit();
}

function NotifyUsersNewAnnouncement () {
	global $subject,$threadid,$txt,$cgi,$db_prefix,$modSettings;

	$Cond = ($modSettings['notifyAnncmnts_UserDisable']=='1'?" WHERE notifyAnnouncements=1":"");
	$request = mysql_query("SELECT emailAddress FROM {$db_prefix}members $Cond");
	while ($row = mysql_fetch_array($request)) {
	   $send_subject="$txt[notifyXAnn2]: $subject";
      $send_body="$txt[notifyXAnn3] $cgi;action=display;threadid=$threadid;start=new\n\n".$txt[130];
		sendmail($row['emailAddress'], $send_subject, $send_body);
	}
}

function NotifyUsers (){
	global $subject,$threadid,$txt,$cgi,$db_prefix,$realemail,$doLimitOne,$username;
	$request = mysql_query("SELECT notifies FROM {$db_prefix}topics WHERE (ID_TOPIC=$threadid && notifies!='') LIMIT 1");
	if (mysql_num_rows($request) != 0)
	{
		$row = mysql_fetch_row($request);

		$members = mysql_query("SELECT emailAddress, notifyOnce, memberName FROM {$db_prefix}members WHERE ID_MEMBER IN ($row[0]) && emailAddress!='' && memberName!='$username'");

		while ($rowmember = mysql_fetch_array($members)) {
			$send_subject=$txt[127].": ".$subject;

			if ($rowmember['notifyOnce'] == 1) {
				$request = mysql_query("SELECT notificationSent FROM {$db_prefix}log_topics WHERE memberName='$rowmember[memberName]' && ID_TOPIC=$threadid $doLimitOne");
				if (mysql_num_rows($request) == 0)
					$notificationSent = 0;
				else
					list($notificationSent) = mysql_fetch_row($request);

				if ($notificationSent == 0) {
					$send_body=$txt[128].", ".$txt[129]." $cgi;action=display;threadid=$threadid;start=new\n\n$txt[notifyXOnce2]\n\n".$txt[130];
					sendmail($rowmember['emailAddress'], $send_subject, $send_body);

					if (mysql_num_rows($request) == 0)
						$request = mysql_query("INSERT INTO {$db_prefix}log_topics (logTime,memberName,ID_TOPIC,notificationSent) VALUES (0,'$rowmember[memberName]',$threadid,1)");
					else
						$request = mysql_query("UPDATE {$db_prefix}log_topics SET notificationSent=1 WHERE (memberName='$rowmember[memberName]' AND ID_TOPIC=$threadid)");
				}
			}
			else {
				$send_body=$txt[128].", ".$txt[129]." $cgi;action=display;threadid=$threadid\n\n".$txt[130];
				sendmail($rowmember['emailAddress'], $send_subject, $send_body);
			}
		}
	}
}

function doshowthread (){
	global $censored,$threadid,$enable_ubbc,$txt,$color,$db_prefix,$realNames,$modSettings;
	// howmany messages to show
	$limitString = ($modSettings['topicSummaryPosts'] < 0)?'':" LIMIT $modSettings[topicSummaryPosts]";

	$request = mysql_query("SELECT posterName,posterTime,body,smiliesEnabled FROM {$db_prefix}messages WHERE ID_TOPIC='$threadid' ORDER BY ID_MSG DESC$limitString");
	if (mysql_num_rows($request)!=0) {
		print <<<EOT
	<BR><BR>
	<table cellspacing=1 cellpadding=0 width="75%" align="center" bgcolor="$color[bordercolor]" class="bordercolor">
	<tr><td>
	<table class="windowbg" cellspacing="1" cellpadding="2" width="100%" align="center" bgcolor="$color[windowbg]">
	<tr><td class="titlebg" bgcolor="$color[titlebg]" colspan=2><font size=2 class="text1" color="$color[titletext]">
	<b>$txt[468]</b></font>
	</td></tr>
EOT;
		while (list ($tempname,$tempdate,$message,$es) = mysql_fetch_row($request))
		{

            $tempname = addslashes($tempname);
			LoadRealName($tempname);
            $realNames[$tempname] = stripslashes($realNames[$tempname]);

			$tempdate = timeformat($tempdate);

			CensorTxt($message);

			if($enable_ubbc) { $message = DoUBBC($message,$es); }
			print <<<EOT

<tr><td align=left class="catbg">
<font size=1>$txt[279]: $realNames[$tempname]</font></td>
<td class="catbg" align=right>
<font size=1>$txt[280]: $tempdate</font></td>
</tr>
<tr><td class="windowbg2" colspan=2 bgcolor="$color[windowbg2]">
<font size=1>$message</font>
</td></tr>
EOT;
		}
		print "</table></td></tr></table>\n";
	}
	else { print "<!--no summary-->"; }
}

function printPostBox($msg)
{
	global $txt,$enable_ubbc,$showyabbcbutt,$imagesdir,$ubbcjspath;
print <<<EOT
		<script language="JavaScript1.2" src="$ubbcjspath" type="text/javascript"></script>
EOT;
if( $enable_ubbc && $showyabbcbutt) {
	print <<<EOT
<tr>
<td align="right">
<font size=2><b>$txt[252]:</b></font></td>
<td valign=middle>
<a href="javascript:bold()"><img src="$imagesdir/bold.gif" align=bottom width=23 height=22 alt="$txt[253]" border=0></a><a href="javascript:italicize()"><img src="$imagesdir/italicize.gif" align=bottom width=23 height=22 alt="$txt[254]" border="0"></a><a href="javascript:underline()"><img src="$imagesdir/underline.gif" align=bottom width=23 height=22 alt="$txt[255]" border="0"></a><a href="javascript:strike()"><img src="$imagesdir/strike.gif" align=bottom width=23 height=22 alt="$txt[441]" border="0"></a><a href="javascript:glow()"><img src="$imagesdir/glow.gif" align=bottom width=23 height=22 alt="$txt[442]" border="0"></a><a href="javascript:shadow()"><img src="$imagesdir/shadow.gif" align=bottom width=23 height=22 alt="$txt[443]" border="0"></a><a href="javascript:move()"><img src="$imagesdir/move.gif" align=bottom width=23 height=22 alt="$txt[439]" border="0"></a><a href="javascript:pre()"><img src="$imagesdir/pre.gif" align=bottom width=23 height=22 alt="$txt[444]" border="0"></a><a href="javascript:left()"><img src="$imagesdir/left.gif" align=bottom width=23 height=22 alt="$txt[445]" border="0"></a><a href="javascript:center()"><img src="$imagesdir/center.gif" align=bottom width=23 height=22 alt="$txt[256]" border="0"></a><a href="javascript:right()"><img src="$imagesdir/right.gif" align=bottom width=23 height=22 alt="$txt[446]" border="0"></a><a href="javascript:hr()"><img src="$imagesdir/hr.gif" align=bottom width=23 height=22 alt="$txt[531]" border="0"></a><a href="javascript:size()"><img src="$imagesdir/size.gif" align=bottom width=23 height=22 alt="$txt[532]" border="0"></a><a href="javascript:font()"><img src="$imagesdir/face.gif" align=bottom width=23 height=22 alt="$txt[533]" border="0"></a><br>
<a href="javascript:flash()"><img src="$imagesdir/flash.gif" align=bottom width=23 height=22 alt="$txt[433]" border="0"></a><a href="javascript:hyperlink()"><img src="$imagesdir/url.gif" align=bottom width=23 height=22 alt="$txt[257]" border="0"></a><a href="javascript:ftp()"><img src="$imagesdir/ftp.gif" align=bottom width=23 height=22 alt="$txt[434]" border="0"></a><a href="javascript:image()"><img src="$imagesdir/img.gif" align=bottom width=23 height=22 alt="$txt[435]" border="0"></a><a href="javascript:emai1()"><img src="$imagesdir/email2.gif" align=bottom width=23 height=22 alt="$txt[258]" border="0"></a><a href="javascript:table()"><img src="$imagesdir/table.gif" align=bottom width=23 height=22 alt="$txt[436]" border="0"></a><a href="javascript:trow()"><img src="$imagesdir/tr.gif" align=bottom width=23 height=22 alt="$txt[437]" border="0"></a><a href="javascript:tcol()"><img src="$imagesdir/td.gif" align=bottom width=23 height=22 alt="$txt[449]" border="0"></a><a href="javascript:superscript()"><img src="$imagesdir/sup.gif" align=bottom width=23 height=22 alt="$txt[447]" border="0"></a><a href="javascript:subscript()"><img src="$imagesdir/sub.gif" align=bottom width=23 height=22 alt="$txt[448]" border="0"></a><a href="javascript:teletype()"><img src="$imagesdir/tele.gif" align=bottom width=23 height=22 alt="$txt[440]" border="0"></a><a href="javascript:showcode()"><img src="$imagesdir/code.gif" align=bottom width=23 height=22 alt="$txt[259]" border="0"></a><a href="javascript:quote()"><img src="$imagesdir/quote2.gif" align=bottom width=23 height=22 alt="$txt[260]" border="0"></a><a href="javascript:list()"><img src="$imagesdir/list.gif" align=bottom width=23 height=22 alt="$txt[261]" border="0"></a><select name="txtcolor" onChange="showcolor(this.options[this.selectedIndex].value)">
	<option value="Black" selected>$txt[262]</option>
	<option value="Red">$txt[263]</option>
	<option value="Yellow">$txt[264]</option>
	<option value="Pink">$txt[265]</option>
	<option value="Green">$txt[266]</option>
	<option value="Orange">$txt[267]</option>
	<option value="Purple">$txt[268]</option>
	<option value="Blue">$txt[269]</option>
	<option value="Beige">$txt[270]</option>
	<option value="Brown">$txt[271]</option>
	<option value="Teal">$txt[272]</option>
	<option value="Navy">$txt[273]</option>
	<option value="Maroon">$txt[274]</option>
	<option value="LimeGreen">$txt[275]</option>
</select>
</td>
</tr>
EOT;
}
print <<<EOT
<tr>
<td align="right"><font size=2><b>$txt[297]:</b></font></td>
<td valign=middle>
<a href="javascript:smiley()"><img src="$imagesdir/smiley.gif" align=bottom alt="$txt[287]" border="0"></a> <a href="javascript:wink()"><img src="$imagesdir/wink.gif" align=bottom alt="$txt[292]" border="0"></a> <a href="javascript:cheesy()"><img src="$imagesdir/cheesy.gif" align=bottom alt="$txt[289]" border="0"></a> <a href="javascript:grin()"><img src="$imagesdir/grin.gif" align=bottom alt="$txt[293]" border="0"></a> <a href="javascript:angry()"><img src="$imagesdir/angry.gif" align=bottom alt="$txt[288]" border="0"></a> <a href="javascript:sad()"><img src="$imagesdir/sad.gif" align=bottom alt="$txt[291]" border="0"></a> <a href="javascript:shocked()"><img src="$imagesdir/shocked.gif" align=bottom alt="$txt[294]" border="0"></a> <a href="javascript:cool()"><img src="$imagesdir/cool.gif" align=bottom alt="$txt[295]" border="0"></a> <a href="javascript:huh()"><img src="$imagesdir/huh.gif" align=bottom alt="$txt[296]" border="0"></a> <a href="javascript:rolleyes()"><img src="$imagesdir/rolleyes.gif" align=bottom alt="$txt[450]" border="0"></a> <a href="javascript:tongue()"><img src="$imagesdir/tongue.gif" align=bottom alt="$txt[451]" border="0"></a> <a href="javascript:embarassed()"><img src="$imagesdir/embarassed.gif" align=bottom alt="$txt[526]" border="0"></a> <a href="javascript:lipsrsealed()"><img src="$imagesdir/lipsrsealed.gif" align=bottom alt="$txt[527]" border="0"></a> <a href="javascript:undecided()"><img src="$imagesdir/undecided.gif" align=bottom alt="$txt[528]" border="0"></a> <a href="javascript:kiss()"><img src="$imagesdir/kiss.gif" align=bottom alt="$txt[529]" border="0"></a> <a href="javascript:cry()"><img src="$imagesdir/cry.gif" align=bottom alt="$txt[530]" border="0"></a>
More&nbsp;<a href="smilies" target="_blank">smilies</a>:&nbsp;<select name="smilies" onChange="showsmiley(this.value,'$imagesdir')">
	<option value=""></option>
EOT;

if ($handle = opendir($boarddir.'YaBBImages/smilies')) {
    while (false !== ($file = readdir($handle))) { 
        if (strstr($file, '.gif')) {
            $name = str_replace('.gif', '', $file);
            echo "<option value=\"$name\">$name</option>\n";
        }
    }
    closedir($handle); 
}

print <<<EOT
</select>
<img src="$imagesdir/smiley.gif" name="smiley">
</td>
</tr>
<tr>
	<td valign=top align="right"><font size=2><b>$txt[72]:</b></font></td>
	<td><textarea class=editor name=message rows=24 cols=60 ONSELECT="javascript:storeCaret(this);" ONCLICK="javascript:storeCaret(this);" ONKEYUP="javascript:storeCaret(this);" ONCHANGE="javascript:storeCaret(this);">$msg</textarea></td>
</tr>
EOT;

}
?>
