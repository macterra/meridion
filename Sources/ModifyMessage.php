<?php
/*****************************************************************************/
/* ModifyMessage.php                                                         */
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

$modifymessageplver="YaBB SE 1.3.0";

function ModifyMessage (){
	global $username,$ID_MEMBER,$currentboard,$yytitle,$txt,$color,$cgi,$imagesdir,$img,$threadid,$msg,$modSettings;
	global $moderators,$settings,$ubbcjspath,$color,$enable_ubbc,$showyabbcbutt,$start,$sourcedir,$db_prefix;
	if($username == 'Guest') { fatal_error($txt[223]); }
	if( $currentboard == '' ) { fatal_error($txt[1]); }
	
	$request = mysql_query("SELECT m.*,t.locked FROM {$db_prefix}messages as m, {$db_prefix}topics as t WHERE (m.ID_MSG=$msg AND m.ID_TOPIC=t.ID_TOPIC) LIMIT 1");
	$row = mysql_fetch_assoc($request);

	if( $row['locked'] == 1 )
		fatal_error($txt[90]);

	$yytitle = $txt[66];

	if($row['ID_MEMBER'] != $ID_MEMBER && !in_array($username,$moderators) && $settings[7] != 'Administrator' && $settings[7] != 'Global Moderator' )
		fatal_error($txt[67]);

	$lastmodification = isset($row['modifiedTime'])? timeformat($row['modifiedTime']) : '-';
	$nosmiley = $row['smiliesEnabled'] ? '' : ' checked';

	$row['body'] = str_replace("<br>","\n",$row['body']);

	# Attachment stuff added by Meriadoc
if ($modSettings['attachmentEnable'] == 0)
{
	$attachmentFields = "";			// if attachments are disabled, don't show anything
}
elseif ($modSettings['attachmentEnable'] == 1)
{
	if($row['attachmentSize'] > 0)	// if there is an attachment show only delete
	{
$attachmentFields = "
	<tr>
      <td align=\"right\"><font size=2><b>$txt[yse119]:</b></font><BR><BR></td>
      <td><input type=hidden name=\"attachOld\" value=\"$row[attachmentFilename]\"><input type=checkbox name=\"delAttach\"><font size=1>$txt[yse130]</font><BR><BR></td>
</tr>";
	}
	else
	{
$attachmentFields = "
<tr>
      <td align=\"right\"><font size=2><b>$txt[yse119]:</b></font><BR><BR></td>
      <td><input type=file name=\"attachment\" size=48>
      <br><font size=1>$txt[yse120]: $modSettings[attachmentExtensions]<br>$txt[yse121]: $modSettings[attachmentSizeLimit] KB<Br>$txt[yse129]</font><BR><BR></td>
</tr>";
	}
}
elseif ($modSettings['attachmentEnable'] == 2)
{
	if($row['attachmentSize'] > 0)	// if there is an attachment show only delete
	{
$attachmentFields = "
	<tr>
      <td align=\"right\"><font size=2><b>$txt[yse119]:</b></font><BR><BR></td>
      <td><input type=hidden name=\"attachOld\" value=\"$row[attachmentFilename]\"><input type=checkbox name=\"delAttach\"><font size=1>$txt[yse130]</font><BR><BR></td>
</tr>";
	}
}

if($modSettings['attachmentMemberGroups']!="") {
	if(!in_array($settings[7],explode(",",trim($modSettings['attachmentMemberGroups'])))) {
		$attachmentFields = "";
	}
}

	template_header();
	print <<<EOT
<script language="JavaScript1.2" src="$ubbcjspath" type="text/javascript"></script>

<script language="JavaScript1.2" type="text/javascript">
<!--
function showimage()
{
	document.images.icons.src="$imagesdir/"+document.postmodify.icon.options[document.postmodify.icon.selectedIndex].value+".gif";
}
//-->
</script>
<form action="$cgi;action=modify2;start=$start" method=post name="postmodify" onSubmit="submitonce(this);" enctype="multipart/form-data">
<table border=0  width="75%" align="center" cellpadding="3" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor">
<tr>
	<td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[66]</b></font></td>
</tr>
<tr>
	<td class="windowbg" bgcolor="$color[windowbg]">
<input type=hidden name="postid" value="$msg">
<input type=hidden name="threadid" value="$threadid">
<table border="0" cellpadding="3">
<tr>
	<td align="right"><font size=2><b>$txt[68]:</b></font></td>
	<td><font size=2>$row[posterName]</font></td>
</tr>
<tr>
	<td align="right"><font size=2><b>$txt[69]:</b></font></td>
	<td><font size=2>$row[posterEmail]</font></td>
</tr>
<tr>
	<td align="right"><font size=2><b>$txt[70]:</b></font></td>
	<td><font size=2><input type=text name="subject" value="$row[subject]" size="40" maxlength="80"></font></td>
</tr>
<tr>
	<td align="right"><font size=2><b>$txt[71]:</b></font></td>
	<td>
<select name="icon" onChange="showimage()">
	<option value="$row[icon]">$txt[112]
	<option value="$row[icon]">------------
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
	<img src="$imagesdir/$row[icon].gif" name="icons" border=0 hspace=15 alt=""></td>
</tr>
EOT;
include_once("$sourcedir/Post.php");
printPostBox ($row['body']);

print <<<EOT
<tr>
	<td valign=top align="right"><font size=2><b>$txt[211]:</b></font></td>
	<td><font size=2>$lastmodification</font></td>
</tr>
<tr>
      <td align="right"><font size=2><b>$txt[276]:</b></font><BR><BR></td>
      <td><font size=2><input type=checkbox name="ns" value="NS"$nosmiley></font>
      <font size=1>$txt[277]</font><BR><BR></td>
</tr>
$attachmentFields
<tr>
	<td align=center colspan=2>
	<input type="hidden" name="waction" value="postmodify">
	<input type="hidden" name="posterName" value="$row[posterName]">
	<input type="submit" name="postmodify" value="$txt[10]" onClick="WhichClicked('postmodify');"  accesskey="s">
	<input type="submit" name="previewmodify" value="$txt[507]" onClick="WhichClicked('previewmodify');">
	<input type="reset" value="$txt[278]">
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

function ModifyMessage2 (){
	global $username,$txt,$ID_MEMBER,$cgi,$yySetLocation,$waction,$d,$postid,$threadid,$db_prefix;
	global $moderators,$name,$email,$subject,$icon,$message,$ns,$MaxMessLen,$maxmessagedisplay;
	global $currentboard,$settings,$msg,$realname,$start,$posterName,$sourcedir,$doLimitOne,$attachOld;
	global $attachment,$attachment_size,$attachment_name,$HTTP_POST_FILES,$delAttach,$modSettings;
	# Validate the attachment if there is one
	# By Meriadoc 12/11-14/2001

// change all ' ' to '_'
$HTTP_POST_FILES['attachment']['name'] = str_replace(' ','_',$HTTP_POST_FILES['attachment']['name']);
// remove all special chars
$HTTP_POST_FILES['attachment']['name'] = preg_replace("/[^\w_.-]/","",$HTTP_POST_FILES['attachment']['name']);

if($delAttach=='on') {
	unlink($modSettings['attachmentUploadDir'] . "/" . $attachOld);
}
elseif($HTTP_POST_FILES['attachment']['name'] != '') {
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
		if(file_exists($modSettings['attachmentUploadDir'] . "/" . $HTTP_POST_FILES['attachment']['name'])) { $attachCancel=1; }

		if(!isset($attachCancel)) {
			$dirSize = "0";
			$dir=opendir($modSettings['attachmentUploadDir']);
			while($file=readdir($dir)) {
				$dirSize = $dirSize + filesize($modSettings['attachmentUploadDir'] . "/" .$file);
			}
			if($HTTP_POST_FILES['attachment']['size'] + $dirSize > $modSettings['attachmentDirSizeLimit'] * 1024) { fatal_error($txt['yse126']); }

			$parts = isset($HTTP_POST_FILES['attachment']) ? preg_split("~(\\|/)~",$HTTP_POST_FILES['attachment']['name']) : array();
			$destName = array_pop($parts);

			if(!move_uploaded_file($HTTP_POST_FILES['attachment']['tmp_name'], $modSettings['attachmentUploadDir'] . "/" . $destName)) { fatal_error("$txt[yse124]"); }
			chmod ("$modSettings[attachmentUploadDir]/$destName",0644) || $chmod_failed = 1;
		}
}



	$smilies = ($ns!='')?0:1;
	if($username == 'Guest') { fatal_error($txt[223]); }

	if (trim($subject=='') && $d != '1')
		fatal_error($txt[77]);
	if (trim($message=='') && $d != '1')
		fatal_error($txt[78]);

	if($waction == 'previewmodify' ) {
		include_once("$sourcedir/Post.php");
		Preview();
	}

    $message = preparsecode($message, $realname, $username);

	$deletepost = (isset($d) || $waction == 'deletemodify');

	if( $deletepost ) {
		if( $waction == 'deletemodify' ) {
			$msg = $postid;
		}
		$postid = $msg;
	}

	$yySetLocation = "$cgi&action=display&threadid=$threadid&start=$start";

	$request = mysql_query("SELECT m.*,t.locked,t.ID_FIRST_MSG,t.ID_LAST_MSG,t.ID_TOPIC,t.ID_BOARD,t.ID_POLL,t.numReplies FROM {$db_prefix}messages as m, {$db_prefix}topics as t WHERE (m.ID_MSG=$postid AND m.ID_TOPIC=t.ID_TOPIC) LIMIT 1");
	$row = mysql_fetch_assoc($request);
	if(($settings[7] != 'Administrator') && ($settings[7] != 'Global Moderator') && !in_array($username,$moderators) && $row['ID_MEMBER'] != $ID_MEMBER) {
		fatal_error($txt[67]);
	}
	if( $row['locked'] == 1 )
		fatal_error($txt[90]);

	# Make sure the user is allowed to edit this post.
	if($row['ID_MEMBER'] != $ID_MEMBER && !in_array($username,$moderators) && $settings[7] != 'Administrator' && $settings[7] != 'Global Moderator' )
		fatal_error($txt[67]);

	if( $row['locked'] == 1 )
		fatal_error($txt[90]);

	if( $deletepost )
	{
		if ($row['ID_FIRST_MSG']==$row['ID_MSG'])	// this is the first message
		{
			if ($row['numReplies'] != 0)
				fatal_error("$txt[delFirstPost]");

			$request = mysql_query("DELETE FROM {$db_prefix}topics WHERE ID_TOPIC=$row[ID_TOPIC] LIMIT 1");
			$request = mysql_query("DELETE FROM {$db_prefix}polls WHERE ID_POLL='$row[ID_POLL]' LIMIT 1");
			$request = mysql_query("UPDATE {$db_prefix}boards SET numPosts=numPosts-1,numTopics=numTopics-1 WHERE ID_BOARD=$row[ID_BOARD] $doLimitOne");
			$yySetLocation = "$cgi";
		}
		else if ($row['ID_FIRST_MSG']==$row['ID_LAST_MSG'])	// this is the only post
		{
			$request = mysql_query("DELETE FROM {$db_prefix}topics WHERE ID_TOPIC=$row[ID_TOPIC] LIMIT 1");
			$request = mysql_query("DELETE FROM {$db_prefix}polls WHERE ID_POLL='$row[ID_POLL]' LIMIT 1");
			$request = mysql_query("UPDATE {$db_prefix}boards SET numPosts=numPosts-1,numTopics=numTopics-1 WHERE ID_BOARD=$row[ID_BOARD] $doLimitOne");
			$yySetLocation = "$cgi";
		}
		else if ($row['ID_LAST_MSG']==$row['ID_MSG'])	// this is the last message
		{
			$request = mysql_query("SELECT ID_MSG FROM {$db_prefix}messages WHERE (ID_TOPIC=$row[ID_TOPIC] AND ID_MSG!=$row[ID_MSG]) ORDER BY ID_MSG DESC LIMIT 1");
			$row2 = mysql_fetch_assoc($request);
			$request = mysql_query("UPDATE {$db_prefix}topics SET ID_LAST_MSG=$row2[ID_MSG],numReplies=numReplies-1 WHERE ID_TOPIC=$row[ID_TOPIC] $doLimitOne");
			$request = mysql_query("UPDATE {$db_prefix}boards SET numPosts=numPosts-1 WHERE ID_BOARD=$row[ID_BOARD] $doLimitOne");
		}
		else // this is just "some" message
		{
			$request = mysql_query("UPDATE {$db_prefix}topics SET numReplies=numReplies-1 WHERE ID_TOPIC=$row[ID_TOPIC] $doLimitOne");
			$request = mysql_query("UPDATE {$db_prefix}boards SET numPosts=numPosts-1 WHERE ID_BOARD=$row[ID_BOARD] $doLimitOne");
		}
		if( $row['ID_MEMBER'] != '-1')
			$request = mysql_query("UPDATE {$db_prefix}members SET posts=posts-1 WHERE (ID_MEMBER=$row[ID_MEMBER] && posts > 0) $doLimitOne");

		$request = mysql_query("DELETE FROM {$db_prefix}messages WHERE ID_MSG=$postid LIMIT 1");
		#delete attachment, by Meriadoc
		if ($row['attachmentSize'] > 0)
			unlink($modSettings['attachmentUploadDir'] . "/" . $row['attachmentFilename']);
	}
	else {
		# If the post is to be modified...

		if (!isset($message)){ fatal_error($txt[78]); }
		if (!isset($subject)){ fatal_error($txt[77]); }
		if (strlen($message)>$MaxMessLen) { fatal_error($txt[499]); }

		$subject = htmlspecialchars($subject);
		$subject = str_replace("|","&#124;",$subject);

		$message = htmlspecialchars($message);
		$message = str_replace("|","&#124;",$message);
		$message = str_replace("\r\n","<br>",$message);
		$message = str_replace("\c","<br>",$message);
		$message = str_replace("\t","&nbsp;&nbsp;&nbsp;",$message);

        if (get_magic_quotes_gpc()==0) {
        $subject = mysql_escape_string($subject);
        $message = mysql_escape_string($message);
        }
		$request = mysql_query("UPDATE {$db_prefix}messages SET subject='$subject',icon='$icon',body='$message',modifiedTime=".time().",modifiedName='$realname',smiliesEnabled=$smilies WHERE ID_MSG=$postid $doLimitOne");

		# update for the attachments
		if($delAttach=='on') {
			$request = mysql_query("UPDATE {$db_prefix}messages SET attachmentSize='0',attachmentFilename=NULL WHERE ID_MSG=$postid $doLimitOne");
		}
		elseif($attachment_size!='') {
			$request = mysql_query("UPDATE {$db_prefix}messages SET attachmentSize='$attachment_size',attachmentFilename='".mysql_escape_string($HTTP_POST_FILES['attachment']['name'])."' WHERE ID_MSG=$postid $doLimitOne");
		}

	}
	// markt the topic as read :)
	$request = mysql_query("SELECT logTime FROM {$db_prefix}log_topics WHERE (memberName='$username' AND ID_TOPIC=$threadid) LIMIT 1");
	if (mysql_num_rows($request)==0)
		$request = mysql_query("INSERT INTO {$db_prefix}log_topics (logTime,memberName,ID_TOPIC) VALUES (".time().",'$username',$threadid)");
	else
		$request = mysql_query("UPDATE {$db_prefix}log_topics SET logTime=".time()." WHERE (memberName='$username' AND ID_TOPIC=$threadid)");

	redirectexit();
}

?>
