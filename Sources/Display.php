<?php
/*****************************************************************************/
/* Display.php                                                               */
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

$displayplver="YaBB SE 1.3.1";

function Display() {
   global $currentboard,$maxmessagedisplay,$censored,$enable_notification,$img;
	global $cgi,$yytitle,$imagesdir,$scripturl,$mbname,$showmodify,$settings;
	global $yyUDLoaded,$userprofile,$memberstar,$memberinfo,$icqad,$yimon,$ID_MEMBER;
	global $txt,$menusep,$start,$curposlinks,$color,$printurl,$MenuType,$username;
	global $enable_ubbc,$profilebutton,$membergroups,$allow_hide_email,$threadid;
	global $moderators,$modSettings,$viewResults,$realNames,$db_prefix,$yySetLocation,$boardseen;
	global $sourcedir;

	include_once("$sourcedir/Reputation.php");
	$allReps = AllReputation();

	$viewnum = $threadid;
	if( $currentboard == "" ) { fatal_error($txt[1]); }

	$result = mysql_query("SELECT b.ID_CAT,b.name,c.name,c.memberGroups FROM {$db_prefix}boards as b,{$db_prefix}categories as c WHERE (b.ID_BOARD=$currentboard && b.ID_CAT=c.ID_CAT)");
	list($curcat,$boardname,$cat,$temp2) = mysql_fetch_row($result);
	$memgroups = explode(",",$temp2);

	if (!(in_array($settings[7],$memgroups) || $memgroups[0]==null || $settings[7]=='Administrator' || $settings[7]=='Global Moderator'))
		fatal_error($txt[1]);

	//Redirect to page & post with new messages -- Omar Bazavilvazo
	if ($start == 'new') {

		//Check if a log exists, so we can go to next unreaded message page
		$request = mysql_query("SELECT logTime FROM {$db_prefix}log_topics WHERE (memberName='$username' AND ID_TOPIC=$viewnum) LIMIT 1");
		if (mysql_num_rows($request) > 0) {
			$temp = mysql_fetch_row($request);
    		$ltLastRead = $temp[0];

			$request = mysql_query("SELECT COUNT(*) FROM {$db_prefix}messages WHERE ID_TOPIC=$viewnum && posterTime<=$ltLastRead ORDER BY ID_MSG".($modSettings['viewNewestFirst'] == '1'?" DESC":""));
			$temp = mysql_fetch_row($request);
			$Page2Show = (int)($temp[0] / $maxmessagedisplay) * $maxmessagedisplay;

			$request = mysql_query("SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_TOPIC=$viewnum && posterTime>$ltLastRead ORDER BY ID_MSG".($modSettings['viewNewestFirst'] == '1'?" DESC":"")." LIMIT 1");
			if (mysql_num_rows($request) > 0) {
				$temp = mysql_fetch_row($request);
				$newMsgID = "#msg$temp[0]";
			}
			else
				$newMsgID = "#lastPost";

			// mark board as seen if we came using notification
			$request = mysql_query("SELECT logTime FROM {$db_prefix}log_boards WHERE (memberName='$username' AND ID_BOARD=$currentboard) LIMIT 1");
			if (mysql_num_rows($request)==0)
				$request = mysql_query("INSERT INTO {$db_prefix}log_boards (logTime,memberName,ID_BOARD) VALUES (".time().",'$username',$currentboard)");
			else
				$request = mysql_query("UPDATE {$db_prefix}log_boards SET logTime=".time()." WHERE (memberName='$username' AND ID_BOARD=$currentboard)");

			$yySetLocation = "$cgi;action=display;threadid=$viewnum;start=$Page2Show$newMsgID";
			redirectexit();
		}
	}

	// do the previous next stuff
	$previousNext = '';
	if ($modSettings['enablePreviousNext'] == '1')  // modSettings is defined in loadBoard
	{
		// Grab the number which this topic is
		if ($modSettings['enableStickyTopics'] == '1')
			$result = mysql_query("SELECT t.ID_TOPIC FROM {$db_prefix}topics as t,{$db_prefix}topics as top,{$db_prefix}messages as m,{$db_prefix}messages as mes WHERE (m.ID_MSG=t.ID_LAST_MSG AND mes.ID_MSG=top.ID_LAST_MSG AND t.ID_BOARD=$currentboard AND top.ID_TOPIC=$threadid AND ((m.posterTime > mes.posterTime AND t.isSticky=top.isSticky) OR (t.isSticky>top.isSticky ))) ORDER BY t.isSticky DESC, t.ID_LAST_MSG DESC");
		else
			$result = mysql_query("SELECT t.ID_TOPIC FROM {$db_prefix}topics as t,{$db_prefix}topics as top{$db_prefix}messages as m,{$db_prefix}messages as mes WHERE (m.ID_MSG=t.ID_LAST_MSG AND mes.ID_MSG=top.ID_LAST_MSG AND t.ID_BOARD=$currentboard AND top.ID_TOPIC=$threadid AND m.posterTime > mes.posterTime) ORDER BY t.ID_LAST_MSG DESC");
        if (!$result)
         fatal_error("$txt[472] - \"$threadid\"");

		$prevTopic = mysql_num_rows($result)-1;
		$nextTopic = mysql_num_rows($result)+1;

		// get previous
		if ($modSettings['enableStickyTopics'] == '1')
		  $query = "SELECT t.ID_TOPIC FROM {$db_prefix}topics as t, {$db_prefix}messages as m  WHERE (t.ID_BOARD=$currentboard AND m.ID_MSG=t.ID_LAST_MSG) ORDER BY t.isSticky DESC, m.posterTime DESC"; // LIMIT $prevTopic,1";
		else
		  $query = "SELECT t.ID_TOPIC FROM {$db_prefix}topics as t, {$db_prefix}messages as m  WHERE (t.ID_BOARD=$currentboard AND m.ID_MSG=t.ID_LAST_MSG) ORDER BY m.posterTime DESC"; // LIMIT $prevTopic,1";

		//print $query;

		$result = mysql_query($query);

		if (!$result)
		  fatal_error("xxx $query");

		list ($prevTopic) = mysql_num_rows($result) > 0 ? mysql_fetch_row($result) : array('');

		if ($prevTopic != '')
			$prevTopic = "<a href=\"$cgi;action=display;threadid=$prevTopic\">$modSettings[PreviousNext_back]</a>";

		// get next
		if ($modSettings['enableStickyTopics'] == '1')
			$result = mysql_query("SELECT t.ID_TOPIC FROM {$db_prefix}topics as t, {$db_prefix}messages as m WHERE (t.ID_BOARD=$currentboard AND m.ID_MSG=t.ID_LAST_MSG) ORDER BY t.isSticky DESC, m.posterTime DESC LIMIT $nextTopic,1");
		else
			$result = mysql_query("SELECT t.ID_TOPIC FROM {$db_prefix}topics as t, {$db_prefix}messages as m WHERE (t.ID_BOARD=$currentboard AND m.ID_MSG=t.ID_LAST_MSG) ORDER BY m.posterTime DESC LIMIT $nextTopic,1");
		list ($nextTopic) = mysql_num_rows($result) > 0 ? mysql_fetch_row($result) : array('');

		if ($nextTopic != '')
			$nextTopic = "<a href=\"$cgi;action=display;threadid=$nextTopic\">$modSettings[PreviousNext_forward]</a>";

		$previousNext = "$prevTopic $nextTopic";
	}

   $request = mysql_query("SELECT membergroup FROM {$db_prefix}membergroups WHERE 1 ORDER BY ID_GROUP");
	$membergroups = array();

	while ($row = mysql_fetch_row($request))
		$membergroups[] = $row[0];

	// get all the topic info
	$request = mysql_query("SELECT t.numReplies,t.numViews,t.locked,ms.subject,t.isSticky,ms.posterName,ms.ID_MEMBER,t.ID_POLL,t.ID_MEMBER_STARTED FROM {$db_prefix}topics as t,{$db_prefix}messages as ms WHERE (t.ID_BOARD=$currentboard && t.ID_TOPIC=$viewnum AND ms.ID_MSG=t.ID_FIRST_MSG)");
	if (mysql_num_rows($request)==0)
		fatal_error($txt[472]);
	$topicinfo = mysql_fetch_array($request);

	// mark the topic as read :)
	$request = mysql_query("SELECT logTime FROM {$db_prefix}log_topics WHERE (memberName='$username' AND ID_TOPIC=$viewnum) LIMIT 1");
	if (mysql_num_rows($request)==0)
		$request = mysql_query("INSERT INTO {$db_prefix}log_topics (logTime,memberName,ID_TOPIC,notificationSent) VALUES (".time().",'$username',$viewnum,0)");
	else
		$request = mysql_query("UPDATE {$db_prefix}log_topics SET logTime=".time().", notificationSent=0 WHERE (memberName='$username' AND ID_TOPIC=$viewnum)");

	// mark board as seen if we came using last post link from BoardIndex
	if (isset($boardseen)) {
		$request = mysql_query("SELECT logTime FROM {$db_prefix}log_boards WHERE (memberName='$username' AND ID_BOARD=$currentboard) LIMIT 1");
		if (mysql_num_rows($request)==0)
			$request = mysql_query("INSERT INTO {$db_prefix}log_boards (logTime,memberName,ID_BOARD) VALUES (".time().",'$username',$currentboard)");
		else
			$request = mysql_query("UPDATE {$db_prefix}log_boards SET logTime=".time()." WHERE (memberName='$username' AND ID_BOARD=$currentboard)");
	}

	# Add 1 to the number of views of this thread.
	$request = mysql_query("UPDATE {$db_prefix}topics SET numViews=numViews+1 WHERE ID_TOPIC=$viewnum");

	/*# Check to make sure this thread isn't locked. */
	$noposting = $topicinfo['locked'];
	$mreplies = $topicinfo['numReplies'];
	$mstate = $topicinfo['locked'];
	$msubthread = $topicinfo['subject'];
	$yytitle = str_replace("\$","&#36;",$topicinfo['subject']);

	# Get the class of this thread, based on lock status and number of replies.
	$threadclass = '';
	if( $mstate == 1 || $mstate==2) { $threadclass = 'locked'; }
	elseif( $mreplies > 24 ) { $threadclass = 'veryhotthread'; }
	elseif( $mreplies > 14 ) { $threadclass = 'hotthread'; }
	elseif( $mstate == 0 ) { $threadclass = 'thread'; }
	if ($modSettings['enableStickyTopics'] == '1' && $topicinfo['isSticky'] == '1') {$threadclass = 'sticky'; }
	if (($mstate == 1 || $mstate==2 )&&($modSettings['enableStickyTopics'] == '1' && $topicinfo['isSticky'] == '1')) {$threadclass='lockedsticky';}

	CensorTxt($msubthread);
   CensorTxt($yytitle);

	// Build a list of this board's moderators.
	$showmods = '';		// create an empty string
	$tmp = array();		// used to temporarily store the list
   $moderatorusernames = array();
	if( sizeof($moderators) > 0 && $moderators[0]!=NULL) {	// moderators is defined near the start of the function
		if( sizeof($moderators) == 1 )		// if only one mod - use a different string
			$showmods = "($txt[298]: ";
		else
			$showmods = "($txt[299]: ";
		for ($i = 0; $i < sizeof($moderators); $i++){
			$euser=urlencode($moderators[$i]);
			$tmp[$i] = "<a href=\"$scripturl?action=viewprofile;user=$euser\"><acronym			title=\"$txt[62]\">{$realNames[$moderators[$i]]}</acronym></a>";
         $moderatorusernames[trim($moderators[$i])] = true;
		}
		$showmods .= implode(", ",$tmp).")";	// stitch the list together
	}

	if( $enable_notification ) { // $enable_notification is defined in settings
		$notify = "$menusep<a href=\"$cgi;action=notify;threadid=$viewnum;start=$start\">$img[notify]</a>";
	}
	$selecthtml	= jumpto();

	# Build the page links list.
	$max = $mreplies + 1;
	$start = ($start > $mreplies) ? $mreplies : $start;
	$start = ( floor( $start / $maxmessagedisplay ) ) * $maxmessagedisplay;

	if ($modSettings['compactTopicPagesEnable'] == '0') {
		$tmpa = $start - $maxmessagedisplay;
		$pageindex = ($start == 0) ? '' : "<a href=\"$cgi;action=display;threadid=$viewnum;start=$tmpa\">&#171;</a>";
		$tmpa = 1;
		for( $counter = 0; $counter < $max; $counter += $maxmessagedisplay ) {
			$pageindex .= ($start == $counter) ? " <b>$tmpa</b>" : " <a href=\"$cgi;action=display;threadid=$viewnum;start=$counter\">$tmpa</a>";
			$tmpa++;
		}
		$tmpa = $start + $maxmessagedisplay;
		$tmpa = $tmpa > $mreplies ? $mreplies : $tmpa;
		if($start != $counter-$maxmessagedisplay) {
			$pageindex .= ($tmpa > $counter-$maxmessagedisplay) ? ' ' : " <a href=\"$cgi;action=display;threadid=$viewnum;start=$tmpa\">&#187;</a> ";
		}
	}
	else {
		if (($modSettings['compactTopicPagesContiguous'] % 2) == 1)	//1,3,5,...
			$PageContiguous = (int)(($modSettings['compactTopicPagesContiguous'] - 1) / 2);
		else
			$PageContiguous = (int)($modSettings['compactTopicPagesContiguous'] / 2);	//invalid value, but let's deal with it

		if ($start > $maxmessagedisplay * $PageContiguous)	//	first
			$pageindex.= "<a class=\"navPages\" href=\"$cgi;action=display;threadid=$viewnum\">1</a> ";

		if ($start > $maxmessagedisplay * ($PageContiguous + 1))	// ...
			$pageindex.= "<B> ... </B>";

		for ($nCont=$PageContiguous; $nCont >= 1; $nCont--)	// 1 & 2 before
			if ($start >= $maxmessagedisplay * $nCont) {
				$tmpStart = $start - $maxmessagedisplay * $nCont;
				$tmpPage = $tmpStart / $maxmessagedisplay + 1;
				$pageindex.= "<a class=\"navPages\" href=\"$cgi;action=display;threadid=$viewnum;start=$tmpStart\">$tmpPage</a> ";
			}

		$tmpPage = $start / $maxmessagedisplay + 1;	// page to show
		$pageindex.= " [<B>$tmpPage</B>] ";

		$tmpMaxPages = (int)(($max - 1) / $maxmessagedisplay) * $maxmessagedisplay;	// 1 & 2 after
		for ($nCont=1; $nCont <= $PageContiguous; $nCont++)
			if ($start + $maxmessagedisplay * $nCont <= $tmpMaxPages) {
				$tmpStart = $start + $maxmessagedisplay * $nCont;
				$tmpPage = $tmpStart / $maxmessagedisplay + 1;
				$pageindex.= "<a class=\"navPages\" href=\"$cgi;action=display;threadid=$viewnum;start=$tmpStart\">$tmpPage</a> ";
			}

		if ($start + $maxmessagedisplay * ($PageContiguous + 1) < $tmpMaxPages)	// ...
			$pageindex.= "<B> ... </B>";

		if ($start + $maxmessagedisplay * $PageContiguous < $tmpMaxPages)	{ //	last
			$tmpPage = $tmpMaxPages / $maxmessagedisplay + 1;
			$pageindex.= "<a class=\"navPages\" href=\"$cgi;action=display;threadid=$viewnum;start=$tmpMaxPages\">$tmpPage</a> ";
		}
	}

	$curthreadurl = $curposlinks ? "<a href=\"$cgi;action=display;threadid=$viewnum\" class=\"nav\">$msubthread</a>" : $msubthread;

# Build the link tree
	$displayLinkTree = $modSettings['enableInlineLinks'] ? "<font size=\"1\" class=\"nav\"><B><a href=\"$scripturl\" class=\"nav\">$mbname</a> </b>&nbsp;|&nbsp;<b> " : "<font size=\"2\" class=\"nav\"><B><img src=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$scripturl\" class=\"nav\">$mbname</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<a href=\"$scripturl#$curcat\" class=\"nav\">$cat</a> </b>&nbsp;|&nbsp;<b> " : "<img src=\"$imagesdir/tline.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$scripturl#$curcat\" class=\"nav\">$cat</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<a href=\"$cgi\" class=\"nav\">$boardname</a></b> $showmods<b> </b>&nbsp;|&nbsp;<b> " : "<img src=\"$imagesdir/tline2.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$cgi\" class=\"nav\">$boardname</a></b> $showmods<b><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "$txt[118]: $curthreadurl</b></font>" : "<img SRC=\"$imagesdir/tline3.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;$curthreadurl</b></font>" ;

	// only show reply if not locked
	$reply = '';
	if (!$mstate)
		$reply="<a href=\"$cgi;action=post;threadid=$viewnum;title=$txt[116];start=$start\">$img[reply]</a>";
	// Create the poll info if it exists
	$pollHtml = '';
	if ($topicinfo['ID_POLL'] != '-1' && $modSettings['pollMode'] == '1')
	{
		$request = mysql_query("SELECT question,votingLocked,votedMemberIDs,option1,option2,option3	,option4,option5,option6,option7,option8,votes1,votes2,votes3,votes4,votes5,votes6,votes7,votes8 FROM {$db_prefix}polls WHERE ID_POLL='$topicinfo[ID_POLL]' LIMIT 1");
		$pollinfo = mysql_fetch_assoc($request);
		$pollimage=($pollinfo['votingLocked'] != '0' )?'locked_poll':'poll';
		$pollHtml =<<<EOT
<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td>
    <table cellpadding="3" cellspacing="0" width="100%">
      <tr>
        <td>
         <font size="2" class="text1" color="$color[titletext]">&nbsp;<img src="$imagesdir/$pollimage.gif" alt=""> <b>$txt[yse43]</b></font>
        </td>
      </tr>
    </table>
    <table cellpadding="3" cellspacing="1" border="0" width="100%">
      <tr>
		<td valign="middle" align="left" bgcolor="$color[windowbg]" class="windowbg">
EOT;
$pollHtml.= "<table border=0><tr><td valign=\"top\"><b>$txt[yse21]:</b></td><td>".stripslashes(DoUBBC($pollinfo['question']));
		$lockVoting = '';
		$pollEdit = '';
		if ($ID_MEMBER==$topicinfo['ID_MEMBER_STARTED'] || in_array($username,$moderators) || $settings[7]=='Administrator')
			$lockVoting="<br><a href=\"$cgi;action=lockVoting;start=$start;threadid=$threadid\">".($pollinfo['votingLocked']=='0'?$txt['yse30']:$txt['yse30b'])."</a>";
		if (($ID_MEMBER==$topicinfo['ID_MEMBER_STARTED'] && $modSettings['pollEditMode']=='2') || (in_array($username,$moderators) && in_array($modSettings['pollEditMode'],array('2','1'))) || $settings[7]=='Administrator')
			$pollEdit="<a href=\"$cgi;action=editpoll;start=$start;threadid=$threadid\">$txt[yse39]</a>";

		if ($ID_MEMBER == '-1' || in_array("$ID_MEMBER", explode(",",$pollinfo['votedMemberIDs'])) || $pollinfo['votingLocked'] != '0' || $viewResults == '1')
		{
			$totalvotes =$pollinfo['votes1']+$pollinfo['votes2']+$pollinfo['votes3']+$pollinfo['votes4']+$pollinfo['votes5']+$pollinfo['votes6']+$pollinfo['votes7']+$pollinfo['votes8'];
			$divisor = ($totalvotes == 0)?1:$totalvotes;
			$pollHtml .= "<table><tr><td>";
			$pollHtml .= "<br><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
			for ($i = 1; $i <= 8; $i++)
				if ($pollinfo["option$i"] != '')
			{
				$bar = floor(($pollinfo["votes$i"] / $divisor) * 100);
				$barWide = ($bar==0)?1:floor(($bar*5)/3);
				$barLine = "<img src=\"$imagesdir/poll_left.gif\" alt=\"\"><img src=\"$imagesdir/poll_middle.gif\" width=\"$barWide\" height=\"12\" alt=\"\"><img src=\"$imagesdir/poll_right.gif\" alt=\"\">";
				$pollHtml .= "<tr><td>".stripslashes(DoUBBC($pollinfo["option$i"]))."</td><td width=\"7\">&nbsp;</td><td nowrap>$barLine ".$pollinfo["votes$i"]." ($bar%)</td></tr>\n";
			}
			$pollHtml.="</table>\n</td><td width=\"15\">&nbsp;</td><td valign=\"bottom\">$lockVoting&nbsp;&nbsp;$pollEdit</td></tr><tr><td><b>$txt[yse24]: $totalvotes</b></td><td>&nbsp;</td></tr></table><br>";
		}
		else
		{
			$pollHtml .= "<form action=\"$cgi;action=vote;threadid=$threadid;start=$start;poll=$topicinfo[ID_POLL]\" method=\"post\">";
			$pollHtml .= "<table><td>";
			for ($i = 1; $i <= 8; $i++)
				if ($pollinfo["option$i"] != '')
					$pollHtml .= "<input type=\"radio\" name=\"option\" value=\"$i\"> ".stripslashes(DoUBBC($pollinfo["option$i"]))."<br>\n";
			$pollHtml .= "</td><td width=\"15\">&nbsp;</td><td valign=\"bottom\"><a href=\"$cgi;action=display;start=$start;threadid=$threadid;viewResults=1\">$txt[yse29]</a>$lockVoting&nbsp;&nbsp;$pollEdit</td></tr><tr><td><input type=\"submit\" value=\"$txt[yse23]\"></td><td>&nbsp;</td></tr></table></form>";
		}

		$pollHtml .=<<<EOT
			</td></tr></table></td>
      </tr>
    </table>
    </td>
  </tr>
</table>
EOT;
	}

	template_header();
	print <<<EOT
<script language="JavaScript1.2" type="text/javascript"><!--
	function DoConfirm(message, url) {
		if(confirm(message)) location.href = url;
	}
	function displayDiv(checkbox, divID) {
	    var div = document.getElementById(divID);
	    if (div) {
	        div.style.display = checkbox.checked ? "inline" : "none";   
	    }
	}
//--></script>
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="bottom">$displayLinkTree</td>
	<td valign="bottom" align="right">
		<font size="2" class="nav">$previousNext</font>
	</td>
  </tr>
</table>
<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor"><tr><td>
<table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td align="left" class="catbg" bgcolor="$color[catbg]" width="100%"  height="35">
    <table cellpadding="3" cellspacing="0" width="100%">
      <tr>
        <td>
        <font size="2"><b>$txt[139]:</b> $pageindex</font>
        </td>
	<td class="catbg" bgcolor="$color[catbg]" align="right" width="350"><font size="-1">
        $reply$notify$menusep<a href="$cgi;action=sendtopic;threadid=$viewnum">$img[sendtopic]</a>$menusep<a href="$printurl?board=$currentboard;threadid=$viewnum" target="_blank">$img[printt]</a>&nbsp;
	</font></td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</td></tr></table>
$pollHtml
<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td>
    <table cellpadding="3" cellspacing="1" border="0" width="100%">
      <tr>
        <td valign="middle" align="left" width="15%" bgcolor="$color[titlebg]" class="titlebg">
        <font size="2" class="text1" color="$color[titletext]">&nbsp;<img src="$imagesdir/$threadclass.gif" alt="">
        &nbsp;<b>$txt[29]</b></font>
        </td>
        <td valign="middle" align="left" bgcolor="$color[titlebg]" class="titlebg" width="85%">
        <font size="2" class="text1" color="$color[titletext]"><b>&nbsp;$txt[118]: $msubthread</b> &nbsp;($txt[641] $topicinfo[numViews] $txt[642])</font></td>
      </tr>
    </table>
    </td>
  </tr>
</table>
EOT;

	# Load background color list.
	$bgcolors = array( $color['windowbg'], $color['windowbg2'] );
	$bgcolornum = sizeof($bgcolors);
	$cssvalues = array( "windowbg","windowbg2" );
	$cssnum = sizeof($bgcolors);

	if($MenuType == 0) { $sm = 1; }
	$counter = $start;

	# For each post in this thread
	if ($modSettings['viewNewestFirst'] == '1'){
	$request = mysql_query("SELECT ID_MSG,subject,posterName,posterEmail,posterTime,ID_MEMBER,icon,posterIP,body,smiliesEnabled,modifiedTime,modifiedName,attachmentFilename,attachmentSize FROM {$db_prefix}messages WHERE ID_TOPIC=$viewnum ORDER BY ID_MSG DESC LIMIT $start,$maxmessagedisplay");}
    else{
    $request = mysql_query("SELECT ID_MSG,subject,posterName,posterEmail,posterTime,ID_MEMBER,icon,posterIP,body,smiliesEnabled,modifiedTime,modifiedName,attachmentFilename,attachmentSize FROM {$db_prefix}messages WHERE ID_TOPIC=$viewnum ORDER BY ID_MSG LIMIT $start,$maxmessagedisplay");}

print "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\" bgcolor=\"$color[bordercolor]\" class=\"bordercolor\" align=\"center\">";

	while ($message = mysql_fetch_array($request)) {
		# Set up the attachment info - Meriadoc 12/14/2001
		$attached="";
		if ($message['attachmentSize']>0 && $modSettings['attachmentEnable'] != '0') {
			$attached = "<a href=\"$modSettings[attachmentUrl]/$message[attachmentFilename]\" target=_blank><img src=\"$imagesdir/clip.gif\" align=absmiddle border=0>&nbsp;<font size=1>$message[attachmentFilename]</font></a><BR>";
		}
		// Set up the image attachment info
		$attachImage = '';
		if ($modSettings['attachmentShowImages']=='1' && $modSettings['attachmentEnable'] != '0')
		{
			$imageTypes = array('jpg','jpeg','gif','png');
			if (in_array(strtolower(substr(strrchr($message[attachmentFilename], '.'), 1)),$imageTypes)){

				//start resize/restrict posted images mod by Mostmaster
				$maxwidth = $modSettings['maxwidth'];
				$maxheight = $modSettings['maxheight'];
				$imagesize = @getimagesize("$modSettings[attachmentUrl]/$message[attachmentFilename]");
				$width = $imagesize[0];
				$height = $imagesize[1];
				if(!($maxwidth=="0" && $maxheight=="0") && ($width>$maxwidth || $height>$maxheight)){
					if($width>$maxwidth && $maxwidth!="0"){
						$height = floor($maxwidth/$width*$height);
						$width = $maxwidth;
						if($height>$maxheight && $maxheight!="0"){
							$width = floor($maxheight/$height*$width);
							$height = $maxheight;
						}
					}else{
						if($height>$maxheight && $maxheight!="0"){
							$width = floor($maxheight/$height*$width);
							$height = $maxheight;
						}
					}
					$attachImage = "<hr color=gray size=1><img src=\"$modSettings[attachmentUrl]/$message[attachmentFilename]\" width=\"$width\" height=\"$height\">";
				}else{
					$attachImage = "<hr color=gray size=1><img src=\"$modSettings[attachmentUrl]/$message[attachmentFilename]\">";
				}
				//end resize/restrict posted images mod by Mostmaster

			}
   }

		list ($mid,$msub, $mname, $memail, $mdate, $muserID, $micon, $mip, $postmessage, $es, $mlm, $mlmb) = $message;

		$windowbg = $bgcolors[($counter % $bgcolornum)];
		$css = $cssvalues[($counter % $cssnum)];

		# Should we show "last modified by?"
		$lastmodified = '';
		if( $mlm && $showmodify && !empty($mlm) && !empty($mlmb)) {
			$messed = timeformat($mlm);
			$lastmodified = "&#171; <i>$txt[211]: $messed $txt[525] $mlmb</i> &#187;";
		}
		$msub = isset($msub)?$msub:$txt[24];
		$messdate = timeformat($mdate);
		$mip = $settings[7] == 'Administrator' ? $mip : $txt[511];
		$sendm = '';

		# If the user isn't a guest, load his/her info.
		$set = false;
		if( $muserID != -1 && !isset($yyUDLoaded[$mname])) {
			# If user is not in memory, s/he must be loaded.
			$set = LoadUserDisplay($mname);
		}

		$online = '';
		$title = '';
		$star = '';
		$icq = '';
		$yim = '';
		$memberinf ='';
		$postinfo = '';
		$usernamelink = '';
		if( isset($yyUDLoaded[$mname]) && $muserID != '-1') {
			$star = $memberstar[$mname];
            $addfunction = "";
			$rlnm = $userprofile[$mname]['realName'];

                        if (strlen($rlnm) > 18) {
                            $rlnm = substr($rlnm, 0, 15)."...";
                        }

			$psts = $userprofile[$mname]['posts'];
			if ($modSettings['titlesEnable'] != '0' && $userprofile[$mname]['usertitle'] != '')
				$title = "{$userprofile[$mname]['usertitle']}<br>";
			if ($modSettings['onlineEnable'] != '0') {
				if (OnlineStatus($mname) > 0) { $online = "$txt[online2]<br><br>\n"; } else $online = "$txt[online3]<br><br>\n";
			} else $online = '';
			//$memberinf = $memberinf.$memberinfo[$mname];

$reputation = sprintf("%.2f", $allReps[$muserID]);
if ($reputation > 8) { $memberinf .= "Archon"; }
else if ($reputation > 7) { $memberinf .= "Adept"; }
else if ($reputation > 6) { $memberinf .= "Magister"; }
else if ($reputation > 5) { $memberinf .= "Initiate"; }
else if ($reputation > 4) { $memberinf .= "Acolyte"; }
else if ($reputation > 3) { $memberinf .= "Anarch"; }
else if ($reputation > 0) { $memberinf .= "Heretic"; }
else { $memberinf = "Neophyte"; }

                        //$memberinf = "test";
			$icq = $icqad[$mname];
			$yim = $yimon[$mname];
                        $msn = $userprofile[$mname]['MSN'];
			# Allow instant message sending if current user is a member.
			if( $username != 'Guest' )
				if (OnlineStatus($mname) > 0) { $sendIM = $img[message_sm_on]; } else $sendIM = $img[message_sm_off];
				$sendm = "$menusep<a href=\"$cgi;action=imsend;to=$mname\">$sendIM</a>";
			$euser=urlencode($mname);
			$usernamelink = "<a href=\"$scripturl?board=$currentboard;action=viewprofile;user=$euser\"><font size=\"2\"><b><acronym title=\"$txt[92] $rlnm\">$rlnm</acronym></b></font></a>";
			$postinfo = "$txt[26]: $psts<br>";
			$postinfo .= "Reputation: $reputation<br>";
			if ($reputation > 0)
				$postinfo .= "<a href=$cgi;action=repIndex;userID=$muserID>Rate $rlnm</a><br>";
			$memail = $userprofile[$mname]['emailAddress'];
		}
		else {
			$musername = "Guest";
			$memberinf = "$txt[28]";
			$usernamelink = "<font size=\"2\"><b>$mname</b></font>";
		}

		CensorTxt($postmessage);
		CensorTxt($msub);

		# Run UBBC interpreter on the message.
		$message = $postmessage; # put the message back into the proper variable to do ubbc on it
		if($enable_ubbc) { $message = DoUBBC($message,$es); }
		$euser=urlencode($mname);
		$profbutton = ($profilebutton && $muserID != '-1') ? "<a href=\"$scripturl?action=viewprofile;user=$euser\">$img[viewprofile_sm]</a>$menusep": '';
		$counterwords = "";
		if($counter != 0) { $counterwords = "$txt[146] #$counter"; }
		# Print the post and user info for the poster.
		print <<<EOT

  <tr>
    <td>
    <a name="msg$mid"></a>
    <table cellpadding="3" cellspacing="1" border="0" width="100%">
      <tr>
        <td bgcolor="$windowbg" class="$css">
        <table width="100%" cellpadding="4" cellspacing="1" class="$css" bgcolor="$windowbg">
          <tr>
            <td class="$css" bgcolor="$windowbg" valign="top" width="15%" rowspan="2">
            $usernamelink<br><font size="1">$title
            $memberinf<br>
EOT;
if($muserID != "-1") {
    //Messy crap - perhaps someone can alter this later
            $ptext=$userprofile[$mname]['personalText'];
            $ptext = str_replace ("&quot;", "\"", $ptext);
            $ptext = str_replace ("&#039;", "'", $ptext);
            $ptext = str_replace ("&amp;", "&", $ptext);
            $ptext = str_replace ("&lt;", "<", $ptext);
            $ptext = str_replace ("&gt;", ">", $ptext);
	print "            $star<br><BR>\n$online            ".$userprofile[$mname]['gender'];
	print "\n            $postinfo\n           ".$userprofile[$mname]['avatar'].$ptext;
	print "\n            {$userprofile[$mname]['ICQ']} $icq $msn $yim {$userprofile[$mname]['AIM']}<BR>\n";
}
if($muserID == '-1') {
	print "            <BR><a href=\"mailto:$memail\">$img[email_sm]</a>\n";
}else if ($userprofile[$mname]['hideEmail'] != "1" || $settings[7] == "Administrator" || $allow_hide_email != '1') {
	print "            $profbutton".$userprofile[$mname]['websiteUrl']." <a href=\"mailto:$memail\">$img[email_sm]</a>$sendm\n";
} else {
	print"    $profbutton{$userprofile[$mname]['websiteUrl']}$sendm \n";
}
print <<< EOT
			</font>
			</td>
            <td class="$css" bgcolor="$windowbg" valign="top" width="85%" height="100%">
            <table width="100%" border="0">
              <tr>
                <td align="left" valign="middle"><img src="$imagesdir/$micon.gif" alt=""></td>
                <td align="left" valign="middle">
                <font size="2"><B>$msub</b></font><BR>
                <font size="1">&#171; <B>$counterwords $txt[30]:</B> $messdate &#187;</font></td>
                <td align="right" valign="bottom" nowrap height="20">
EOT;
if (!$mstate)
{
	print <<<EOT
                <font size="-1">$menusep<a href="$cgi;action=post;threadid=$viewnum;quote=$mid;title=$txt[116];start=$start">$img[replyquote]</a>
EOT;
		if(in_array($username,$moderators) || $settings[7] == 'Administrator' || $settings[7] == 'Global Moderator'|| ($ID_MEMBER==$muserID && $ID_MEMBER != -1))
                print "$menusep<a href=\"$cgi;action=modify;msg=$mid;threadid=$viewnum;start=$start\">$img[modify]</a>$menusep<a href=\"javascript:DoConfirm('$txt[154]?','$cgi;action=modify2;threadid=$viewnum;msg=$mid;d=1;start=$start');\">$img[delete]</a>";
		print "</font>";
}

$minRep = 3;

if ($muserID>0 && $reputation<$minRep) {
    if ($ID_MEMBER == $muserID) {
        $message = "[[ warning: your <a href=index.php?action=repIndex2>reputation</a> ($reputation) is beneath threshold ($minRep) so your post will be filtered ]]<p>$message";
    }
    else {
        $divID = "msg{$counter}";
        $message = "[[ author reputation ($reputation) beneath threshold ($minRep)... <input type=checkbox onclick=\"displayDiv(this, '$divID')\">display message ]]<p><div id=$divID style=display:none>$message</div>";
        $attachImage = "";
    }
}

print <<<EOT
                </td>
              </tr>
            </table>
            <hr width="100%" size="1" class="windowbg3">
            $message
            </td>
          </tr><tr>
            <td class="$css" bgcolor="$windowbg" valign="bottom">
            <table width="100%" border="0">
              <tr>
                <td align="left"><font size="1">$attached$lastmodified</font></td>
                <td align="right"><font size="1">
EOT;
if ($modSettings['enableReportToMod'] == '1')
    print "<font size=1><a href=\"$cgi;action=reporttm;thread=$viewnum;id=$counter;subject=$msub;poster=$mname\">".$txt['rtm1']."</a></font>&nbsp;&nbsp;";
if ($mip==$txt[511]){
    print "<img src=\"$imagesdir/ip.gif\" alt=\"\" border=\"0\"> $mip</font></td></tr></table>";
}
else{
 print "<a href=\"http://www.nic.com/cgi-bin/whois.cgi?query=$mip\" target=\"_blank\"><img src=\"$imagesdir/ip.gif\" alt=\"\" border=\"0\"> $mip</a></font></td></tr></table>";
}
if($muserID != '-1')
	print "{$userprofile[$mname]['signature']}";
print <<<EOT
			$attachImage
			</td>
          </tr>
        </table>
        </td>
      </tr>
    </table>
    </td>
  </tr>
EOT;
		$counter++;
	}

	print <<<EOT
</table>
<a name="lastPost"></a>
<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor"><tr><td>
<table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor">
EOT;
print <<<EOT
  <tr>
    <td align="left" class="catbg" bgcolor="$color[catbg]" width="100%" height="30">
    <table cellpadding="3" cellspacing="0" width="100%">
      <tr>
        <td>
        <font size="2"><b>$txt[139]:</b> $pageindex</font>
        </td>
	<td class="catbg" bgcolor="$color[catbg]" align="right" width="350"><font size="-1">
        $reply$notify$menusep<a href="$cgi;action=sendtopic;threadid=$viewnum">$img[sendtopic]</a>$menusep<a href="$printurl?board=$currentboard;threadid=$viewnum" target="_blank">$img[printt]</a>&nbsp;
	</font></td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</td></tr></table>
<table border="0" width="100%" cellpadding="0" cellspacing="0">

EOT;
  if ($modSettings['enableInlineLinks'])
    echo "<tr>\n 	<td valign=\"bottom\">$displayLinkTree<br><br></td>\n </tr>";

  print <<<EOT
  <tr>
    <td align="left" colspan="2">
    <font size="2">
EOT;
	if(in_array($username,$moderators) || $settings[7] == 'Administrator' || $settings[7] == 'Global Moderator') {
		print <<<EOT
	<a href="$cgi;action=movethread;threadid=$viewnum"><img src="$imagesdir/admin_move.gif" alt="$txt[132]" border="0"></a>
	<a href="javascript:DoConfirm('$txt[162]','$cgi;action=removethread2;threadid=$viewnum');"><img src="$imagesdir/admin_rem.gif" alt="$txt[63]" border="0"></a>
EOT;
	}
	if(in_array($username,$moderators) || $settings[7] == 'Administrator' || $settings[7] == 'Global Moderator' || ($username == "$topicinfo[posterName]" && $username != 'Guest' && $modSettings['enableUserTopicLocking'] == 1) ) {
		print (" <a href=\"$cgi;action=lock;threadid=$viewnum\"><img src=\"$imagesdir/admin_lock.gif\" alt=\"$txt[104]\" border=\"0\"></a>");
	}

	if ($modSettings['enableStickyTopics'] == '1' && (in_array($username,$moderators) || $settings[7] == 'Administrator' || $settings[7] == 'Global Moderator')) {
		print (" <a href=\"$cgi;action=sticky;threadid=$viewnum;sticky=$topicinfo[isSticky]\"><img src=\"$imagesdir/admin_sticky.gif\" border=\"0\"></a>");
		}
	print <<<EOT
    </font></td>
    <td align="right"><form action="$scripturl" method="GET">
    <font size="1">$txt[160]:</font>$selecthtml</form></td>
  </tr>
</table>
EOT;
	footer();
	obExit();
}
?>
