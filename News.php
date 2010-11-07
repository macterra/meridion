<?
/*****************************************************************************/
/* News.php                                                                   */
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

$newsphpver = "YaBB SE 1.1.3";

global $scripturl,$db_prefix,$txt,$db_name,$db_server,$db_user,$db_passwd,$cgi,$imagesdir,$board,$limit,$template,$ext,$locale,$timeformatstring;

$dbcon = mysql_connect($db_server,$db_user,$db_passwd);
mysql_select_db ($db_name);

setlocale ("LC_TIME", $locale);

if ($limit == null) {
	$limit = 5;
} 

if ($board == null) {
	print("No Board Specified!");
	exit;
}

$request = mysql_query("SELECT t.*,b.*,c.* FROM {$db_prefix}topics as t,{$db_prefix}boards as b,{$db_prefix}categories as c WHERE (b.ID_CAT=c.ID_CAT && c.memberGroups='' && b.ID_BOARD=$board)");
if (mysql_fetch_array($request) == null) {
	print("You can not display a board that is in a category with MemberGroups or the board ID does not exist. Please check the board number before trying again.");
	exit;
}

$request = mysql_query("SELECT t.*,m.* FROM {$db_prefix}topics as t,{$db_prefix}messages as m WHERE (t.ID_BOARD=$board && m.ID_MSG=t.ID_FIRST_MSG) ORDER BY m.posterTime DESC LIMIT $limit");

while ($row = mysql_fetch_array($request))
{
	$news_icon = "<img src=\"$imagesdir/$row[icon].gif\" align=\"absmiddle\" />";
	$news_title = "$row[subject]";
	$news_date = strftime($timeformatstring,$row['posterTime']);
	$news_tmp = "$row[posterName]";
	$request2 = mysql_query("SELECT realName FROM {$db_prefix}members WHERE (memberName='$news_tmp') LIMIT 1");
	while ($tmp = mysql_fetch_array($request2))
	{
	$news_poster = "<a href=\"$scripturl?action=viewprofile;user=$row[posterName]\">$tmp[realName]</a>";
	}
	$news_body = DoUBBC($row['body']);
	$news_checkcomment = ($row['numReplies']==1) ? "$txt[yse_news_1]" : "$txt[yse_news_2]";
	$news_comments = "<a href=\"$cgi;action=display;threadid=$row[ID_TOPIC];start=0\">$row[numReplies] $news_checkcomment</a>";
	$news_newcomment = "<a href=\"$cgi;action=post;threadid=$row[ID_TOPIC];title=Post+reply;start=$row[numReplies]\">$txt[yse_news_3]</a>";
	if ($template == null) {
		include("news_template.php");
	} else {
		if ($ext == null) {
			include($template.".php");
		} else {
			include($template.".".$ext);
		}
	}
}
	exit;
?>