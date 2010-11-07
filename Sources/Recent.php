<?php
/*****************************************************************************/
/* Recent.php                                                                */
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

$recentplver="YaBB SE 1.3.1";

function LastPost (){
	global $settings,$scripturl,$txt,$censored,$recentsender,$db_prefix;

	if (!isset($recentsender)) {$recentsender='';}

	$request = mysql_query("SELECT m.posterTime,m.subject,m.ID_TOPIC,t.ID_BOARD,m.posterName,t.numReplies,t.ID_FIRST_MSG FROM {$db_prefix}messages as m,{$db_prefix}topics as t,{$db_prefix}boards as b,{$db_prefix}categories as c WHERE (m.ID_TOPIC=t.ID_TOPIC && t.ID_BOARD=b.ID_BOARD && b.ID_CAT=c.ID_CAT && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || c.memberGroups='' || '$settings[7]' LIKE 'Administrator' || '$settings[7]' LIKE 'Global Moderator')) ORDER BY m.posterTime DESC LIMIT 1");
	if (mysql_num_rows($request)==0)
		return;
	$row = mysql_fetch_array($request);
	$request = mysql_query ("SELECT subject FROM {$db_prefix}messages WHERE ID_MSG=$row[ID_FIRST_MSG] LIMIT 1");
	$row2 = mysql_fetch_array($request);
    //Messy crap - perhaps someone can alter this later
    $row2['subject'] = str_replace ("&quot;", "\"", $row2['subject']);
    $row2['subject'] = str_replace ("&#039;", "'", $row2['subject']);
    $row2['subject'] = str_replace ("&amp;", "&", $row2['subject']);
    $row2['subject'] = str_replace ("&lt;", "<", $row2['subject']);
    $row2['subject'] = str_replace ("&gt;", ">", $row2['subject']);
	if($recentsender == 'admin') {
		$row2['subject'] = (strlen($row2['subject'])>25)?(substr($row2['subject'],0,22)."..."):$row2['subject'];
		$post = "\"<a href=\"$scripturl?board=$row[ID_BOARD];action=display;threadid=$row[ID_TOPIC];start=$row[numReplies]\">$row2[subject]</a>\" (".timeformat($row['posterTime']).")\n";
	} else {
		$post = "$txt[234] \"<a href=\"$scripturl?board=$row[ID_BOARD];action=display;threadid=$row[ID_TOPIC];start=$row[numReplies]\">$row2[subject]</a>\" $txt[235] (".timeformat($row['posterTime']).")<br></font></td>\n";
	}

	CensorTxt($post);

	print $post;
}

function RecentPosts (){
	global $settings,$txt,$yytitle,$censored,$scripturl,$enable_ubbc,$enable_notification,$menusep,$db_prefix;
	global $cgi,$img,$color,$imagesdir,$realNames;
	global $sourcedir;

	$display = 10;

	$request = mysql_query("SELECT m.smiliesEnabled,m.posterTime,m.ID_MEMBER,m.ID_MSG,m.subject,m.body,m.ID_TOPIC,t.ID_BOARD,b.name as bname,c.name as cname,m.posterName,t.numReplies,r.reputation FROM {$db_prefix}messages as m,{$db_prefix}topics as t,{$db_prefix}boards as b,{$db_prefix}categories as c, {$db_prefix}reputation as r WHERE (m.ID_MEMBER = r.id_member AND r.reputation>3 AND m.ID_TOPIC = t.ID_TOPIC AND t.ID_BOARD = b.ID_BOARD AND b.ID_CAT=c.ID_CAT AND (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 OR c.memberGroups='' OR '$settings[7]'='Administrator' OR '$settings[7]'='Global Moderator')) ORDER BY m.posterTime DESC LIMIT $display");

	$yytitle = $txt[214];
	template_header();

	$counter = 0;

	while ($row = mysql_fetch_array($request)) {
		$counter ++;
		$request2 = mysql_query("SELECT m.posterName,m.ID_MEMBER FROM {$db_prefix}messages as m,{$db_prefix}topics as t WHERE (m.ID_MSG=t.ID_FIRST_MSG && t.ID_TOPIC=$row[ID_TOPIC])");
		$row2 = mysql_fetch_array($request2);

        $row['posterName'] = addslashes($row['posterName']);
        $row2['posterName'] = addslashes($row2['posterName']);

	$reputation = $row['reputation'];

		LoadRealName($row['posterName']);
		LoadRealName($row2['posterName']);
		if ($row['ID_MEMBER'] != '-1') {
			$euser=urlencode($row['posterName']);
			$row['posterName'] = "<a href=\"$scripturl?action=viewprofile;user=$euser\">{$realNames[$row['posterName']]}</a>";
		}
		if ($row2['ID_MEMBER'] != '-1') {
			$euser=urlencode($row2[posterName]);
			$row2['posterName'] = "<a href=\"$scripturl?action=viewprofile;user=$euser\">{$realNames[$row2['posterName']]}</a>";
		}

        if ($row['ID_MEMBER'] == '-1')
			$row['posterName'] = stripslashes($row['posterName']);
		if ($row2['ID_MEMBER'] == '-1')
            $row2['posterName'] = stripslashes($row2['posterName']);

		$message = $row['body'];

		CensorTxt($message);
		CensorTxt($row['subject']);

		if($enable_ubbc) { $message = DoUBBC($message,$row['smiliesEnabled']); }

		if($enable_notification)
			$notify = "$menusep<a href=\"$scripturl?board=$row[ID_BOARD];action=notify;threadid=$row[ID_TOPIC];start=$row[numReplies]\">$img[notify_sm]</a>";
		$row['posterTime'] = timeformat($row['posterTime']);


		print <<<EOT
<table border="0" width="100%" cellspacing="1" bgcolor="$color[bordercolor]">
<tr>
	<td align=left bgcolor="$color[titlebg]"><font class="text1" color="$color[titletext]" size=2>&nbsp;$counter&nbsp;</font></td>
	<td width="75%" bgcolor="$color[titlebg]"><font class="text1" color="$color[titletext]" size="2"><b>&nbsp;$row[cname] / $row[bname] / <a href="$scripturl?board=$row[ID_BOARD];action=display;threadid=$row[ID_TOPIC];start=$row[numReplies]">$row[subject]</a></b></font></td>
	<td align=right bgcolor="$color[titlebg]" nowrap>&nbsp;<font class="text1" color="$color[titletext]" size="2">$txt[30]: $row[posterTime]&nbsp;</font></td>
</tr>
<tr>
	<td colspan=3 bgcolor="$color[catbg]" class="catbg"><font class="catbg" size=2>$txt[109] $row2[posterName] | $txt[22] $txt[525] $row[posterName]</font></td>
</tr>
<tr>
	<td colspan=3 bgcolor="$color[windowbg2]" valign=top height=40><font size=2>$message</font></td>
</tr>
<tr>
	<td colspan=3 bgcolor="$color[catbg]"><font size=2>
		&nbsp;<a href="$scripturl?board=$row[ID_BOARD];action=post;threadid=$row[ID_TOPIC];start=$row[numReplies];title=Post+reply">$img[reply_sm]</a>$menusep<a href="$scripturl?board=$row[ID_BOARD];action=post;threadid=$row[ID_TOPIC];quote=$row[ID_MSG];title=Post+reply">$img[replyquote]</a>$notify
	</font></td>
</tr>
</table><br>
EOT;
}

	print <<<EOT
<font size="1"><a href="$cgi">$txt[236]</a>
$txt[237]<br></font>
EOT;
	footer();
	obExit();
}


function LastPostings (){

   global $settings,$scripturl,$txt,$censored,$recentsender,$db_prefix, $post, $dummy;

   $showlatestcount = 5;

   if (!isset($recentsender)) {$recentsender='';}

   $request = mysql_query("SELECT m.posterTime,m.subject,m.ID_TOPIC,t.ID_BOARD,m.posterName,m.ID_MEMBER,t.numReplies,t.ID_FIRST_MSG FROM {$db_prefix}messages as m,{$db_prefix}topics as t,{$db_prefix}boards as b,{$db_prefix}categories as c WHERE (m.ID_TOPIC=t.ID_TOPIC && t.ID_BOARD=b.ID_BOARD && b.ID_CAT=c.ID_CAT && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || c.memberGroups='' || '$settings[7]' LIKE 'Administrator' || '$settings[7]' LIKE 'Global Moderator')) ORDER BY m.posterTime DESC LIMIT 0,$showlatestcount");

   if( mysql_num_rows($request) > 0 ) {

      $post = "<table width=\"100%\" border=\"0\">";

      while ($row = mysql_fetch_array($request)) {

         $post .= "<tr>";

         $request3 = mysql_query ("SELECT name FROM {$db_prefix}boards WHERE (ID_BOARD=$row[ID_BOARD]) LIMIT 1");
         $temp = mysql_fetch_row($request3);
         $bname = $temp[0];

         if ($row['ID_MEMBER'] != -1) {
            $request4 = mysql_query ("SELECT realName FROM {$db_prefix}members WHERE ID_MEMBER=$row[ID_MEMBER] LIMIT 1");
            $temp2 = mysql_fetch_row($request4);
            $euser=urlencode($row['posterName']);
            $dummy = "<a href=\"$scripturl?action=viewprofile;user=$euser\">$temp2[0]</a>";
         } else {
            $dummy = $row['posterName'];
         }

         $post .= "<td align=\"right\" valign=\"top\" nowrap>[<a href=\"$scripturl?board=$row[ID_BOARD]\">$bname</a>]</td>";

         $request2 = mysql_query ("SELECT subject FROM {$db_prefix}messages WHERE ID_MSG=$row[ID_FIRST_MSG] LIMIT 1");
         $row2 = mysql_fetch_array($request2);

         $post .= "<td valign=\"top\"><a href=\"$scripturl?board=$row[ID_BOARD];action=display;threadid=$row[ID_TOPIC];start=$row[numReplies]\">$row2[subject]</a> $txt[525] $dummy</td><td align=\"right\" nowrap>".timeformat($row['posterTime'])."</td></tr>\n";

      }

      $post .= "</table>";

   } else {
      $post .= "---";
   }

   $post .= "</td>";

   CensorTxt($post);

   print $post;
}

function ListNewPosts()
{
	global $settings,$username,$realNames,$cgi,$color;
	if ($username=='Guest')
		fatal_error($txt[223]);

   if (!isset($recentsender)) {$recentsender='';}

   $request = mysql_query("SELECT m.posterTime,m.subject,m.ID_TOPIC,t.ID_BOARD,m.posterName,m.ID_MEMBER,t.numReplies,t.ID_FIRST_MSG FROM {$db_prefix}messages as m,{$db_prefix}topics as t,{$db_prefix}boards as b,{$db_prefix}categories as c WHERE ((m.ID_TOPIC=t.ID_TOPIC && t.ID_BOARD=b.ID_BOARD && b.ID_CAT=c.ID_CAT && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || c.memberGroups='' || '$settings[7]' LIKE 'Administrator' || '$settings[7]' LIKE 'Global Moderator')) && NOT EXISTS (SELECT top.ID_TOPIC FROM {$db_prefix}log_topics as top WHERE top.memberName='$username' && top.ID_TOPIC=m.ID_TOPIC)) ORDER BY m.posterTime DESC");

	template_header();
print "SELECT m.posterTime,m.subject,m.ID_TOPIC,t.ID_BOARD,m.posterName,m.ID_MEMBER,t.numReplies,t.ID_FIRST_MSG FROM {$db_prefix}messages as m,{$db_prefix}topics as t,{$db_prefix}boards as b,{$db_prefix}categories as c WHERE ((m.ID_TOPIC=t.ID_TOPIC && t.ID_BOARD=b.ID_BOARD && b.ID_CAT=c.ID_CAT && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || c.memberGroups='' || '$settings[7]' LIKE 'Administrator' || '$settings[7]' LIKE 'Global Moderator')) && NOT EXISTS (SELECT top.ID_TOPIC FROM {$db_prefix}log_topics as top WHERE top.memberName='$username' && top.ID_TOPIC=m.ID_TOPIC)) ORDER BY m.posterTime DESC";
	while ($row = mysql_fetch_array($request))
	{
		print "$row[subject] by $row[posterName]<br>";
	}

	footer();

	obExit();
}
?>
