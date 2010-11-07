<?php
/*****************************************************************************/
/* MessageIndex.php                                                          */
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

$messageindexplver="YaBB SE 1.3.1";


function MessageIndex (){
	global $view,$maxdisplay,$showmarkread,$img,$maxmessagedisplay,$menusep,$currentboard,$realNames;
	global $imagesdir,$censored,$username,$user,$pageindex,$mbname,$txt,$ShowBDescrip,$color,$scripturl;
	global $curposlinks,$cgi,$yytitle,$settings,$start,$modSettings,$viewResults,$ID_MEMBER,$db_prefix;

	// Get the board and category information
	$result = mysql_query("SELECT b.name,b.description,b.moderators,c.name,b.ID_CAT,c.memberGroups,b.isAnnouncement FROM {$db_prefix}boards as b,{$db_prefix}categories as c WHERE(b.ID_BOARD=$currentboard && b.ID_CAT=c.ID_CAT)");

	if (mysql_num_rows($result) == 0)
		fatal_error($txt['yse232']	);

	list ($boardname,$bdescrip,$bdmods,$cat,$currcat,$temp2,$isAnnouncement) = mysql_fetch_row($result);

	$memgroups = explode(",",$temp2);
	if (!(in_array($settings[7],$memgroups) || $memgroups[0]==null || $settings[7]=='Administrator' || $settings[7]=='Global Moderator'))
		fatal_error($txt[1]);

	// how many topics are there in total?
	// (used to figure out how many pages)
	$result = mysql_query("SELECT COUNT(*) as topiccount FROM {$db_prefix}topics WHERE (ID_BOARD=$currentboard)");
	$temp = mysql_fetch_row($result);
    $topiccount = $temp[0];
	// are we views all the topics, or just a few?
	$maxindex = $view == 'all' ? $topiccount : $maxdisplay;

	// Make sure the starting place makes sense.
	if (!isset($start)) { $start = 0; }
	if( $start > $topiccount ) { $start =( $topiccount % $maxindex ) * $maxindex; }
	elseif( $start < 0 ) { $start = 0; }
	$start = (int)($start / $maxindex) * $maxindex;

	// Construct the page links for this board.
	if ($modSettings['compactTopicPagesEnable'] == '0') {
		$tmpa = $start - $maxindex;
		$pageindex .= ($start == 0 ) ? " " : "<a href=\"$cgi;action=messageindex;start=$tmpa\">&#171;</a> ";
		$tmpa = 1;
		for( $counter = 0; $counter < $topiccount; $counter += $maxindex ) {
			$pageindex .= ($start == $counter) ? "<B>$tmpa</B> " : "<a href=\"$cgi;action=messageindex;start=$counter\">$tmpa</a> ";
			++$tmpa;
		}
		$tmpa = $start + $maxindex;
		$tmpa = ($tmpa > $topiccount) ? $topiccount : $tmpa;
		if($start != $counter-$topiccount) {
			$pageindex .= $tmpa > $counter-$maxindex ? " " : "<a href=\"$cgi;action=messageindex;start=$tmpa\">&#187;</a> ";
		}
	}
	else {
		if (($modSettings['compactTopicPagesContiguous'] % 2) == 1)	//1,3,5,...
			$PageContiguous = (int)(($modSettings['compactTopicPagesContiguous'] - 1) / 2);
		else
			$PageContiguous = (int)($modSettings['compactTopicPagesContiguous'] / 2);	//invalid value, but let's deal with it

		if ($start > $maxindex * $PageContiguous)	//	first
			$pageindex.= "<a class=\"navPages\" href=\"$cgi;action=messageindex\">1</a> ";

		if ($start > $maxindex * ($PageContiguous + 1))	// ...
			$pageindex.= "<B> ... </B>";

		for ($nCont=$PageContiguous; $nCont >= 1; $nCont--)	// 1 & 2 before
			if ($start >= $maxindex * $nCont) {
				$tmpStart = $start - $maxindex * $nCont;
				$tmpPage = $tmpStart / $maxindex + 1;
				$pageindex.= "<a class=\"navPages\" href=\"$cgi;action=messageindex;start=$tmpStart\">$tmpPage</a> ";
			}

		$tmpPage = $start / $maxindex + 1;	// page to show
		$pageindex.= " [<B>$tmpPage</B>] ";

		$tmpMaxPages = (int)(($topiccount - 1) / $maxindex) * $maxindex;	// 1 & 2 after
		for ($nCont=1; $nCont <= $PageContiguous; $nCont++)
			if ($start + $maxindex * $nCont <= $tmpMaxPages) {
				$tmpStart = $start + $maxindex * $nCont;
				$tmpPage = $tmpStart / $maxindex + 1;
				$pageindex.= "<a class=\"navPages\" href=\"$cgi;action=messageindex;start=$tmpStart\">$tmpPage</a> ";
			}

		if ($start + $maxindex * ($PageContiguous + 1) < $tmpMaxPages)	// ...
			$pageindex.= "<B> ... </B>";

		if ($start + $maxindex * $PageContiguous < $tmpMaxPages)	{ //	last
			$tmpPage = $tmpMaxPages / $maxindex + 1;
			$pageindex.= "<a class=\"navPages\" href=\"$cgi;action=messageindex;start=$tmpMaxPages\">$tmpPage</a> ";
		}
	}

	// Add the "new poll" icon if allowed
	$newPollIcon = ($modSettings['pollMode']=='1' && (($modSettings['pollPostingRestrictions']=='1' && $settings[7]=='Administrator') || $modSettings['pollPostingRestrictions']=='0'))?"$menusep<a href=\"$cgi;action=postpoll\">$img[newpoll]</a>":'';


	// mark current board as seen
	$request = mysql_query("SELECT logTime FROM {$db_prefix}log_boards WHERE (memberName='$username' AND ID_BOARD=$currentboard) LIMIT 1");
	if (mysql_num_rows($request)==0)
		$request = mysql_query("INSERT INTO {$db_prefix}log_boards (logTime,memberName,ID_BOARD) VALUES (".time().",'$username',$currentboard)");
	else
		$request = mysql_query("UPDATE {$db_prefix}log_boards SET logTime=".time()." WHERE (memberName='$username' AND ID_BOARD=$currentboard)");

	// Build a list of the board's moderators.
	$moderators = explode(",", $bdmods);
	if (sizeof($moderators) > 0 && $moderators[0]!=NULL)
	{
		if (sizeof($moderators) == 1)
			$showmods = "($txt[298]: ";
		else
			$showmods = "($txt[299]: ";
		$tmp = array();
		for ($i = 0; $i < sizeof($moderators); $i++){
			$euser=urlencode($moderators[$i]);
			$tmp[$i] = "<a href=\"$scripturl?action=viewprofile;user=$euser\"><acronym			title=\"$txt[62]\">{$realNames[$moderators[$i]]}</acronym></a>";
		}
		$showmods .= implode(", ",$tmp).")";
	}

	// Print the header and board info.
	$yytitle = $boardname;
	template_header();
	$curboardurl = $curposlinks ? "<a href=\"$cgi\" class=\"nav\">$boardname</a>" : $boardname;
	# Build the link tree
	$displayLinkTree = $modSettings['enableInlineLinks'] ? "<font size=\"1\" class=\"nav\"><B><a href=\"$scripturl\" class=\"nav\">$mbname</a> </b>&nbsp;|&nbsp;<b> " : "<font size=\"2\" class=\"nav\"><B><img src=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$scripturl\" class=\"nav\">$mbname</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<a href=\"$scripturl#$currcat\" class=\"nav\">$cat</a> </b>&nbsp;|&nbsp;<b> " : "<img src=\"$imagesdir/tline.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$scripturl#$currcat\" class=\"nav\">$cat</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "$curboardurl</b> $showmods</font>" : "<img src=\"$imagesdir/tline2.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;$curboardurl</b> $showmods</font>" ;
	print <<< EOT
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td>$displayLinkTree</td>
  </tr>
</table>
EOT;
if($ShowBDescrip) {
print <<<EOT
<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor"><tr><td>
<table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td align="left" class="catbg" bgcolor="$color[catbg]" width="100%"  height="30">
    <table cellpadding="3" cellspacing="0" width="100%">
      <tr>
        <td width="100%"><font size="1">$bdescrip</font></td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</td></tr></table>
EOT;

}
print <<<EOT
<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor"><tr><td>
<table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td align="left" class="catbg" bgcolor="$color[catbg]" width="100%"  height="30">
    <table cellpadding="3" cellspacing="0" width="100%">
      <tr>
        <td><font size="2"><b>$txt[139]:</b> $pageindex</font></td>
	<td align="right" nowrap><font size="-1"><b>
EOT;
if($username != 'Guest') {
if($showmarkread) {
	print "	<a href=\"$cgi;action=markasread\">$img[markboardread]</a>";
}
}
if (!$isAnnouncement || $settings[7]=='Administrator' || $settings[7]=='Global Moderator')
	print "$menusep<a href=\"$cgi;action=post;title=$txt[464]\">$img[newthread]</a>$newPollIcon&nbsp;";
	print <<<EOT
	</b></font></td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</td></tr></table>
<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor"><tr><td>
<table border="0" width="100%" cellspacing="1" cellpadding="4" bgcolor="$color[bordercolor]" class="bordercolor">
<tr>
	<td class="titlebg" bgcolor="$color[titlebg]" width="10%" colspan="2"><font size="2">&nbsp;</font></td>
	<td class="titlebg" bgcolor="$color[titlebg]" width="48%"><font size="2" class="text1" color="$color[titletext]"><b>$txt[70]</b></font></td>
	<td class="titlebg" bgcolor="$color[titlebg]" width="14%"><font size="2" class="text1" color="$color[titletext]"><b>$txt[109]</b></font></td>
	<td class="titlebg" bgcolor="$color[titlebg]" width="4%" align="center"><font size="2" class="text1" color="$color[titletext]"><b>$txt[110]</b></font></td>
	<td class="titlebg" bgcolor="$color[titlebg]" width="4%" align="center"><font size="2" class="text1" color="$color[titletext]"><b>$txt[301]</b></font></td>
	<td class="titlebg" bgcolor="$color[titlebg]" width="27%"><font size="2" class="text1" color="$color[titletext]"><b>$txt[111]</b></font></td>
EOT;

	// Grab the appropriate topic information
	if ($modSettings['enableStickyTopics'] == '1')
		$result = mysql_query("SELECT t.ID_LAST_MSG,t.ID_TOPIC,t.numReplies,t.locked,m.posterName,m.ID_MEMBER,t.numViews,m.posterTime,m.modifiedTime,t.ID_FIRST_MSG,t.isSticky,t.ID_POLL,mes.posterName as mname,mes.ID_MEMBER as mid,mes.subject as msub,mes.icon as micon FROM {$db_prefix}topics as t, {$db_prefix}messages as m, {$db_prefix}messages as mes WHERE (t.ID_BOARD=$currentboard AND m.ID_MSG=t.ID_LAST_MSG AND mes.ID_MSG=t.ID_FIRST_MSG) ORDER BY t.isSticky DESC, m.posterTime DESC LIMIT $start,$maxindex");
	else
		$result = mysql_query("SELECT t.ID_LAST_MSG,t.ID_TOPIC,t.numReplies,t.locked,m.posterName,m.ID_MEMBER,t.numViews,m.posterTime,m.modifiedTime,t.ID_FIRST_MSG,t.isSticky,t.ID_POLL,mes.posterName as mname,mes.ID_MEMBER as mid,mes.subject as msub,mes.icon as micon FROM {$db_prefix}topics as t, {$db_prefix}messages as m,{$db_prefix}messages as mes WHERE (t.ID_BOARD=$currentboard AND m.ID_MSG=t.ID_LAST_MSG AND mes.ID_MSG=t.ID_FIRST_MSG) ORDER BY m.posterTime DESC LIMIT $start,$maxindex");


	// Begin printing the message index for current board.
	while ($row = mysql_fetch_array($result))
	{
		if ($row['ID_POLL'] != '-1' && $modSettings['pollMode']==0)
			continue;
		$lastposter = $row['posterName'];
		$lastPosterID = $row['ID_MEMBER'];
		$mdate = $row['posterTime'];
		$mname = $row['mname'];
		$mid = $row['mid'];
		$msub = $row['msub'];
		$micon=$row['micon'];
		$mnum = $row['ID_TOPIC'];
		$mreplies = $row['numReplies'];
		$mstate = $row['locked'];
		$views = $row['numViews'];
		$isSticky = $row['isSticky'];
		$pollID = $row['ID_POLL'];
		$topicEditedTime = $row['posterTime'];
		if ($lastPosterID != '-1') {
			LoadRealName ($lastposter);
			$name1 = $realNames[$lastposter];
		}
		if ($mid != '-1') {
			LoadRealName ($mname);
			$name2 = $realNames[$mname];
		}

		// Set thread class depending on locked status and number of replies.
		if( $mstate == 1 || $mstate==2 ) { $threadclass = 'locked'; }
		elseif( $mreplies > 24 ) { $threadclass = 'veryhotthread'; }
		elseif( $mreplies > 14 ) { $threadclass = 'hotthread'; }
		elseif( $mstate == 0) { $threadclass = 'thread'; }
		if ($modSettings['enableStickyTopics'] == '1' && $isSticky == '1') {$threadclass = 'sticky'; }
		if (($mstate == 1 || $mstate==2 )&&($modSettings['enableStickyTopics'] == '1' && $isSticky == '1')) {$threadclass='lockedsticky';}
		if ($modSettings['pollMode']=='1' && $pollID != '-1') { $threadclass = 'poll'; }
		if ($modSettings['pollMode']=='1' && $pollID != '-1' && ( $mstate == 1 || $mstate==2 )) {$threadclass = 'locked_poll'; }

		// Decide if thread should have the "NEW" indicator next to it.
		// Do this by reading the user's log for last read time on thread,
		// and compare to the last post time on the thread.
		$new = true;
		$request = mysql_query("SELECT logTime FROM {$db_prefix}log_topics WHERE (ID_TOPIC=$mnum && memberName='$username' && logTime>=$topicEditedTime) LIMIT 1");
		if (mysql_num_rows($request) == 0)
		{
			$request = mysql_query("SELECT logTime FROM {$db_prefix}log_mark_read WHERE (ID_BOARD=$currentboard && memberName='$username' && logTime>=$topicEditedTime)");
			if (mysql_num_rows($request) != 0)
				$new = false;
		}
		else
			$new = false;

		if (!$new || $username=='Guest')
			$new = '';
		else
			$new = "<img src=\"$imagesdir/new.gif\" alt=$txt[302]\">";

		if ($mid != -1) {
			$euser=urlencode($mname);
			$mname = "<a href=\"$scripturl?action=viewprofile;user=$euser\"><acronym title=\"$txt[92] $name2\">$name2</acronym></a>";
		}

		// Censor the subject of the thread.
		CensorTxt($msub);

		// Decide how many pages the thread should have.
		$threadlength = $mreplies + 1;
		$pages = '';
		if( $threadlength > $maxmessagedisplay ) {
			$tmppages = array();
			$tmpa = 1;
			for( $tmpb = 0; $tmpb < $threadlength; $tmpb += $maxmessagedisplay ) {
				$tmppages[] = "<a href=\"$cgi;action=display;threadid=$mnum;start=$tmpb\">$tmpa</a>";
				++$tmpa;
			}
			if (sizeof($tmppages) <= 5 ) {	// should we show links to ALL the pages?
				$pages = implode(" ",$tmppages);
				$pages = "<font size=\"1\">&#171; $pages &#187;</font>";
			} else {						// or should we skip some?
				$s1 = sizeof($tmppages)-1;
				$s2 = sizeof($tmppages)-2;
				$pages = "<font size=\"1\">&#171; $tmppages[0] $tmppages[1] ... $tmppages[$s2] $tmppages[$s1] &#187;</font>";
			}
		}

		if ($lastPosterID != -1) {
			$euser=urlencode($lastposter);
			$lastposter = "<a href=\"$scripturl?action=viewprofile;user=$euser\">$name1</a>";
		}

		# Print the thread info.
		$mydate = timeformat($mdate);
		print <<<EOT
<tr>
	<td class="windowbg2" valign="middle" align="center" width="6%" bgcolor="$color[windowbg2]"><img src="$imagesdir/$threadclass.gif" alt=""></td>
	<td class="windowbg2" valign="middle" align="center" width="4%" bgcolor="$color[windowbg2]"><img src="$imagesdir/$micon.gif" alt="" border="0" align="middle"></td>
	<td class="windowbg" valign="middle" width="48%" bgcolor="$color[windowbg]"><font size="2"><a href="$cgi;action=display;threadid=$mnum">$msub</a> $new $pages</font></td>
	<td class="windowbg2" valign="middle" width="14%" bgcolor="$color[windowbg2]"><font size="2">$mname</font></td>
	<td class="windowbg" valign="middle" width="4%" align="center" bgcolor="$color[windowbg]"><font size="2">$mreplies</font></td>
	<td class="windowbg" valign="middle" width="4%" align="center" bgcolor="$color[windowbg]"><font size="2">$views</font></td>
	<td class="windowbg2" valign="middle" width="27%" bgcolor="$color[windowbg2]"><font size="1">$mydate<br>$txt[525] $lastposter</font></td>
</tr>
EOT;
	}
	print <<<EOT
</table>
</td></tr></table>
<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor"><tr><td>
<table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td align="left" class="catbg" bgcolor="$color[catbg]" width="100%"  height="30">
    <table cellpadding="3" cellspacing="0" width="100%">
      <tr>
        <td><font size="2"><b>$txt[139]:</b> $pageindex</font></td>
	<td class="catbg" bgcolor="$color[catbg]" align="right"><font size="-1">
EOT;
if($username != 'Guest') {
if($showmarkread) {
	print "	<a href=\"$cgi;action=markasread\">$img[markboardread]</a>";
}
}
	$selecthtml = jumpto();
	$polllegend = ($modSettings['pollMode']=='1')?"<br><img src=\"$imagesdir/poll.gif\" alt=\"\"> <font size=1>$txt[yse43]</font>":'';
	$stickylegend = ($modSettings['enableStickyTopics'] == '1')?"<br><img src=\"$imagesdir/sticky.gif\" alt=\"\"> <font size=1>$txt[yse96]</font>":'';
	$lockedstickylegend = ($modSettings['enableStickyTopics'] == '1')?"<br><img src=\"$imagesdir/lockedsticky.gif\" alt=\"\"> <font size=1>$txt[yse97]</font>":'';
	$lockedpolllegend = ($modSettings['pollMode']=='1')?"<br><img src=\"$imagesdir/locked_poll.gif\" alt=\"\"> <font size=1>$txt[yse98]</font>":'';

if (!$isAnnouncement || $settings[7]=='Administrator' || $settings[7]=='Global Moderator')
	print "$menusep<a href=\"$cgi;action=post;title=$txt[464]\">$img[newthread]</a>$newPollIcon&nbsp;";
	print <<<EOT
	</font></td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</td></tr></table>
<table cellpadding="0" cellspacing="0" width="100%">
EOT;
  if ($modSettings['enableInlineLinks'])
    echo "<tr>\n 	<td colspan=\"3\" valign=\"bottom\">$displayLinkTree<br><br></td>\n </tr>";
print <<<EOT
  <tr>
    <td align="left" valign="middle">
    <img src="$imagesdir/hotthread.gif" alt=""> <font size="1">$txt[454]</font>
    <BR><img src="$imagesdir/veryhotthread.gif" alt=""> <font size="1">$txt[455]</font>$stickylegend$polllegend</td>
    <td align="left" valign="middle">
    <img src="$imagesdir/locked.gif" alt=""> <font size="1">$txt[456]</font>
    <BR><img src="$imagesdir/thread.gif" alt=""> <font size="1">$txt[457]</font>$lockedstickylegend$lockedpolllegend</td>
    <td align="right" valign="middle"><form action="$scripturl" method="GET">
    <font size="1">$txt[160]:</font>$selecthtml</form></td>
  </tr>
</table>
EOT;
	footer();
	obExit();
}

function MarkRead() {
	# Mark all threads in this board as read.
	global $currentboard,$username,$db_prefix;
	$request = mysql_query("SELECT logTime FROM {$db_prefix}log_mark_read WHERE (ID_BOARD=$currentboard && memberName='$username')");
	if (mysql_num_rows($request) == 0)
		$request = mysql_query("INSERT INTO {$db_prefix}log_mark_read (logTime,ID_BOARD,memberName) VALUES (".time().",$currentboard,'$username')");
	else
		$request = mysql_query("UPDATE {$db_prefix}log_mark_read SET logTime=".time()." WHERE (ID_BOARD=$currentboard && memberName='$username')");

	MessageIndex();
	obExit();
}
?>
