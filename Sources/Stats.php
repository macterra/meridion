<?php
/*****************************************************************************/
/* Stats.php                                                                */
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

$statsphpver ="YaBB SE 1.3.1";

function display_stats() {
	global $txt, $color,$yytitle,$boardurl,$yyheaderdone,$settings,$months,$db_prefix,$username,$REMOTE_ADDR,$REQUEST_URI,$modSettings,$imagesdir,$mbname,$sourcedir,$totalt,$totalm,$messnum, $scripturl;
	$yytitle = "$mbname - $txt[yse_stats_1]";
	$request = mysql_query("SELECT memberName,realName FROM {$db_prefix}members ORDER BY dateRegistered DESC LIMIT 1");
	$temp = mysql_fetch_array($request);
	$name = (!isset($temp['realName']) || $temp['realName']=='')?$temp['memberName']:$temp['realName'];

	$euser=urlencode($temp['memberName']);
	$thelatestmember = "<a href=\"$scripturl?action=viewprofile;user=$euser\">$name</a>";
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
    $result = mysql_query("SELECT value FROM {$db_prefix}settings WHERE variable='mostOnline';");
	$temp = mysql_fetch_row($result);
    $mostonline = $temp[0];
    $result = mysql_query("SELECT value FROM {$db_prefix}settings WHERE variable='mostDate';");
	$temp = mysql_fetch_row($result);
    $mostdate = timeformat($temp[0]);
	# Build the link tree
	$displayLinkTree = $modSettings['enableInlineLinks'] ? "<font size=\"1\" class=\"nav\"><B><a href=\"$scripturl\" class=\"nav\">$mbname</a> </b>&nbsp;|&nbsp;<b> " : "<font size=\"2\" class=\"nav\"><B><img src=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$scripturl\" class=\"nav\">$mbname</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<a href=\"$cgi?action=stats\" class=\"nav\">$txt[yse_stats_1]</a> </b>&nbsp;|&nbsp;<b> " : "<img src=\"$imagesdir/tline.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$cgi?action=stats\" class=\"nav\">$txt[yse_stats_1]</a><br>" ;
	if (!$yyheaderdone)
		template_header();
	print <<<EOT
<table width="100%" align="center"><tr><td valign="bottom">$displayLinkTree</td></tr></table>
EOT;
print <<<EOT
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="bordercolor"><tr><td>
<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor">
  <tr>
    <td class="titlebg" align="center" colspan="4">
    <b>$mbname - $txt[yse_stats_1]</b></td>
  </tr>
  <tr>
    <td class="catbg" colspan="4"><b>$txt[yse_stats_2]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/stats_info.gif" border="0" width="20" height="20" alt=""></td>
    <td class="windowbg2" width="100%" colspan="3">
	<table border="0" cellpadding="1" cellspacing="0" width="100%">
	  <tr>
            <td><font size="2">$txt[488]</font></td>
            <td align="right"><font size="2"><a href="$scripturl?action=mlall">$memcount</a></font></td>
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
            <td><font size="2">$txt[656]</font></td>
            <td align="right"><font size="2">$thelatestmember</font></td>
          </tr><tr>
            <td><font size="2">$txt[888]</font></td>
            <td align="right"><font size="2">$mostonline - $mostdate</font></td>
          </tr>
        </table>
EOT;
print <<<EOT
</td>
  </tr>
  <tr>
    <td class="catbg" colspan="2" width="50%"><b>$txt[yse_stats_3]</b></td><td class="catbg" colspan="2" width="50%"><b>$txt[yse_stats_4]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/stats_posters.gif" border="0" width="20" height="20" alt=""></td><td class="windowbg2" width="50%"><table border="0" cellpadding="1" cellspacing="0" width="100%">
EOT;
// Poster top 10
$members_result = mysql_query("SELECT * FROM {$db_prefix}members ORDER BY posts DESC LIMIT 10");
while($row_members = mysql_fetch_array($members_result))
{
  $euser=urlencode($row_members['memberName']);
  echo "<tr><td><font size=\"2\"><a href=\"$cgi?action=viewprofile;user=$euser\">$row_members[realName]</a></font></td><td align=\"right\"><font size=\"2\">$row_members[posts]</font></td></tr>";
}
print <<<EOT
</table></td><td class="windowbg" width="20" valign="middle" align="center" nowrap="nowrap"><img src="$imagesdir/stats_board.gif" width="20" height="20" border="0" alt=""></td><td class="windowbg2" width="50%"><table border="0" cellpadding="1" cellspacing="0" width="100%">
EOT;
// Board top 10
$boards_result = mysql_query("SELECT * FROM {$db_prefix}boards ORDER BY numPosts DESC LIMIT 10");
while($row_board = mysql_fetch_array($boards_result))
{
  echo "<tr><td><font size=\"2\"><a href=\"$cgi?board=$row_board[ID_BOARD]\">$row_board[name]</a></td><td align=\"right\"><font size=\"2\">$row_board[numPosts]</font></td></tr>";
}
print <<<EOT
</table>
  </td>
  </tr>
 <tr><td class="catbg" colspan="2" width="50%"><b>$txt[yse_stats_11]</b></td><td class="catbg" colspan="2" width="50%"><b>$txt[yse_stats_12]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/stats_replies.gif" border="0" width="20" height="20" alt=""></td><td class="windowbg2" width="50%"><table border="0" cellpadding="1" cellspacing="0" width="100%">
EOT;
// Topic replies top 10
$topic_reply_result = mysql_query("SELECT {$db_prefix}topics.*,m.* FROM {$db_prefix}topics,{$db_prefix}messages as m, {$db_prefix}messages as mes WHERE (m.ID_MSG={$db_prefix}topics.ID_FIRST_MSG && mes.ID_MSG={$db_prefix}topics.ID_LAST_MSG) ORDER BY {$db_prefix}topics.numReplies DESC LIMIT 10");
while ($row_topic_reply = mysql_fetch_array($topic_reply_result))
{
  echo "<tr><td><font size=\"2\"><a href=\"$cgi?board=$row_topic_reply[ID_BOARD];action=display;threadid=$row_topic_reply[ID_TOPIC];start=0\">$row_topic_reply[subject]</a></font></td><td align=\"right\"><font size=\"2\">$row_topic_reply[numReplies]</font></td></tr>";
}
print <<<EOT
</table></td><td class="windowbg" width="20" valign="middle" align="center" nowrap="nowrap"><img src="$imagesdir/stats_views.gif" width="20" height="20" border="0" alt=""></td><td class="windowbg2" width="50%"><table border="0" cellpadding="1" cellspacing="0" width="100%">
EOT;
// Topic views top 10
$topic_view_result = mysql_query("SELECT {$db_prefix}topics.*,m.* FROM {$db_prefix}topics,{$db_prefix}messages as m, {$db_prefix}messages as mes WHERE (m.ID_MSG={$db_prefix}topics.ID_FIRST_MSG && mes.ID_MSG={$db_prefix}topics.ID_LAST_MSG) ORDER BY {$db_prefix}topics.numViews DESC LIMIT 10");
while($row_topic_view = mysql_fetch_array($topic_view_result))
{
  echo "<tr><td><font size=\"2\"><a href=\"$cgi?board=$row_topic_view[ID_BOARD];action=display;threadid=$row_topic_view[ID_TOPIC];start=0\">$row_topic_view[subject]</a></td><td align=\"right\"><font size=\"2\">$row_topic_view[numViews]</font></td></tr>";
}

$track_hits1 = $modSettings['hitStats'] ? "<td class=\"titlebg\" valign=\"middle\" align=\"center\">$txt[yse_stats_10]</td>" : "" ;
print <<<EOT
</table></td></tr><tr><td class="catbg" colspan="4"><b>$txt[yse_stats_5]</b></td>
  </tr><tr>
    <td class="windowbg" width="20" valign="middle" align="center"><img src="$imagesdir/stats_history.gif" border="0" width="20" height="20" alt=""></td><td class="windowbg2" colspan="4"><table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor"><tr><td class="titlebg" valign="middle" align="center">$txt[yse_stats_6]</td><td class="titlebg" valign="middle" align="center">$txt[yse_stats_7]</td><td class="titlebg" valign="middle" align="center">$txt[yse_stats_8]</td><td class="titlebg" valign="middle" align="center">$txt[yse_stats_9]</td><td class="titlebg" valign="middle" align="center">$txt[yse_stats_14]</td>$track_hits1</tr>
EOT;
// Days
$days_result = mysql_query("SELECT * FROM {$db_prefix}log_activity ORDER BY year DESC, month DESC, day DESC LIMIT 30");
while($row_days = mysql_fetch_array($days_result))
{
$track_hits2 = $modSettings['hitStats'] ? "<td>$row_days[hits]</td>" : "" ;
  echo "<tr class=\"windowbg2\" valign=\"middle\" align=\"center\"><td>$row_days[day]/$row_days[month]/$row_days[year]</td><td>$row_days[topics]</td><td>$row_days[posts]</td><td>$row_days[registers]</td><td>$row_days[mostOn]</td>$track_hits2</tr>";
}
print "</table><P>";
print <<<EOT
<table border="0" width="100%" cellspacing="1" cellpadding="4" class="bordercolor"><tr><td class="titlebg" valign="middle" align="center">$txt[yse_stats_13]</td><td class="titlebg" valign="middle" align="center">$txt[yse_stats_7]</td><td class="titlebg" valign="middle" align="center">$txt[yse_stats_8]</td><td class="titlebg" valign="middle" align="center">$txt[yse_stats_9]</td><td class="titlebg" valign="middle" align="center">$txt[yse_stats_14]</td>$track_hits1</tr>
EOT;
// Days
$days_result = mysql_query("SELECT month, year, SUM(hits) as shit, SUM(registers) as sreg, SUM(topics) as stop, SUM(posts) as spos, MAX(mostOn) as mOn FROM {$db_prefix}log_activity GROUP BY year, month ORDER BY year DESC, month DESC LIMIT 30");
while($row_days = mysql_fetch_array($days_result))
{
$tempval=$row_days[month]-1;
$temp_month=$months[$tempval];
$track_hits2 = $modSettings['hitStats'] ? "<td>$row_days[shit]</td>" : "" ;
  echo "<tr class=\"windowbg2\" valign=\"middle\" align=\"center\"><td>$temp_month $row_days[year]</td><td>$row_days[stop]</td><td>$row_days[spos]</td><td>$row_days[sreg]</td><td>$row_days[mOn]</td>$track_hits2</tr>";
}
print "</table></td></tr></table></td></tr></table>";
footer();
exit();
}
?>
