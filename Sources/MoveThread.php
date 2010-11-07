<?php
/*****************************************************************************/
/* MoveThread.php                                                            */
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

$movethreadplver="YaBB SE 1.3.0";

function MoveThread(){
	global $username,$moderators,$settings,$txt,$currentboard,$color,$cgi,$threadid,$ubbcjspath,$db_prefix;
	if (!in_array($username,$moderators) && $settings[7] != 'Administrator' && $settings[7] != 'Global Moderator') { fatal_error($txt[134]); }
	$yytitle = "$txt[132]";
	$boardlist="";
	$request = mysql_query("SELECT ID_BOARD, name FROM {$db_prefix}boards WHERE ID_BOARD != $currentboard");
	while ($row = mysql_fetch_assoc($request)){	
		$boardlist .= "<option value=\"$row[ID_BOARD]\">$row[name]</option>\n";
	}
	mysql_free_result($request);
	template_header();
	print <<<EOT
<script language="JavaScript1.2" src="$ubbcjspath" type="text/javascript"></script>
<table border="0" width="400" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor" cellpadding="4" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[132]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" valgin="middle" align="center"><font size=2>
    <form action="$cgi&action=movethread2&threadid=$threadid" method="POST" onSubmit="submitonce(this);"><br>
	<table border=0><tr><td>
    <b>$txt[133]:</b><select name="toboard">$boardlist</select><br> <br>
	$txt[yse57]<br><textarea rows="3" cols="35" name="reason"></textarea><br> <br>
    <input type=submit value="$txt[132]">
    </td></tr></table>
	</form>
    </font></td>
  </tr>
</table>
EOT;
	footer();
	obExit();
}

function MoveThread2() {
	global $username,$moderators,$settings,$txt,$currentboard,$threadid,$scripturl,$toboard,$yySetLocation;
	global $db_prefix,$reason,$ID_MEMBER,$realemail,$REMOTE_ADDR,$board,$doLimitOne;
	if (!in_array($username,$moderators) && $settings[7] != 'Administrator' && $settings[7] != 'Global Moderator') { fatal_error($txt[134]); }

	if (!is_numeric($threadid)) { fatal_error($txt[337]); }
	
	$request = mysql_query("SELECT t.numReplies,m.subject FROM {$db_prefix}topics as t,{$db_prefix}messages as m WHERE (t.ID_TOPIC=$threadid && t.ID_FIRST_MSG=m.ID_MSG)");
	if (mysql_num_rows($request)==0) {
		fatal_error("This topic does not exist in the topics list");
	}
	$A_moved = mysql_fetch_assoc($request);
	mysql_free_result($request);
	$threadposts = $A_moved["numReplies"] + 1;
	
	$request = mysql_query("UPDATE {$db_prefix}topics SET ID_BOARD=$toboard WHERE ID_TOPIC=$threadid");

	$request = mysql_query("UPDATE {$db_prefix}boards SET numPosts=((numPosts-$threadposts)+1) WHERE ID_BOARD = $currentboard");

	$request = mysql_query("UPDATE {$db_prefix}boards SET numTopics=numTopics+1, numPosts=numPosts+$threadposts WHERE ID_BOARD = $toboard");

	// create a link to this in the old board
	$request = mysql_query("INSERT INTO {$db_prefix}topics (ID_BOARD,ID_MEMBER_STARTED,ID_MEMBER_UPDATED,locked,numReplies,numViews,notifies) VALUES ('$board','$ID_MEMBER','$ID_MEMBER',1,0,0,'')");
	$remnantTopic = mysql_insert_id();
	$remnantSubject = addcslashes("$txt[yse56]: $A_moved[subject]","\"'");
	$reason .= "\n\n[url]$scripturl?board=$toboard;action=display;threadid={$threadid}[/url]";
    if (get_magic_quotes_gpc()==0) {
        $reason = mysql_escape_string($reason);
        }
	$request = mysql_query("INSERT INTO {$db_prefix}messages (ID_MEMBER,ID_TOPIC,subject,body,posterName,posterTime,posterEmail,posterIP,icon) VALUES ('$ID_MEMBER','$remnantTopic','$remnantSubject','$reason','$username','".time()."','$realemail','$REMOTE_ADDR','moved')");
	$remnantMsg = mysql_insert_id();
	$request = mysql_query("UPDATE {$db_prefix}topics SET ID_LAST_MSG='$remnantMsg',ID_FIRST_MSG='$remnantMsg' WHERE ID_TOPIC='$remnantTopic' $doLimitOne");

	$yySetLocation = "$scripturl?board=$toboard;action=display;threadid=$threadid;start=0";
	redirectexit();
}

?>
