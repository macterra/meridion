<?php
/*****************************************************************************/
/* Karma.php                                                                 */
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

$karmaphpver="YaBB SE 1.3.0";

function ModifyKarma ()
{
	global $modSettings,$username,$settings,$db_prefix,$karmaAction,$uid,$ID_MEMBER,$HTTP_REFERER,$txt;
	global $doLimitOne;

	/* if the mod is disabled, error */
	if ($modSettings['karmaMode']=='0')
		fatal_error($txt['yse63']);

	/* if we've defined any member groups to restrict it to, and if you're
	   not part of one of said membergroups, kick you're ass to the curb */
	if ($modSettings['karmaMemberGroups'][0]!='' && sizeof($modSettings['karmaMemberGroups'])==1 && !in_array($settings[7],$modSettings['karmaMemberGroups']))
		fatal_error($txt[1]);

	/* if you're a guest, blow you off */
	if ($username == 'Guest')
		fatal_error($txt[1]);

	/* if you don't have enough posts, tough luck */
	if ($settings[6] < $modSettings['karmaMinPosts'])
		fatal_error("$txt[yse60]$modSettings[karmaMinPosts].");

	/* and you can't modify you're own punk! */
	if ($uid == $ID_MEMBER)
		fatal_error("$txt[yse61]");

	$dir = strcasecmp("applaud","$karmaAction")?'-':'+';
	$nowtime = time();
	$request = mysql_query("DELETE FROM {$db_prefix}log_karma WHERE ($nowtime-logTime)>($modSettings[karmaWaitTime]*3600)");

	$change = 0;

	if ($modSettings['karmaTimeRestrictAdmins'] || $settings[7] != 'Administrator')
	{
		$request = mysql_query("SELECT action FROM {$db_prefix}log_karma WHERE (ID_TARGET='$uid' && ID_EXECUTOR='$ID_MEMBER') LIMIT 1");
		if (mysql_num_rows($request) == 0) // there are no entries for you and that user logged
		{
			$request = mysql_query("INSERT INTO {$db_prefix}log_karma (action,ID_TARGET,ID_EXECUTOR,logTime) VALUES ('$dir','$uid','$ID_MEMBER','$nowtime')");
			$change = 1;
		}
		else
		{
			$row = mysql_fetch_row($request);
			if ($row[0]==$dir)	// if you're trying to repeat
				fatal_error("$txt[yse62] $modSettings[karmaWaitTime] $txt[578].");
			else
			{
				$request = mysql_query ("UPDATE {$db_prefix}log_karma SET action='$dir', logTime='$nowtime' WHERE (ID_TARGET='$uid' && ID_EXECUTOR='$ID_MEMBER') $doLimitOne");
				$change = 2;
			}
		}

	}
	else
	{
		$request = mysql_query("UPDATE {$db_prefix}log_karma SET action='$dir',logTime='$nowtime' WHERE (ID_TARGET='$uid' && ID_EXECUTOR='$ID_MEMBER') $doLimitOne");
		if (mysql_affected_rows() == 0)
			$request = mysql_query("INSERT INTO {$db_prefix}log_karma (action,ID_TARGET,ID_EXECUTOR,logTime) VALUES ('$dir','$uid','$ID_MEMBER','$nowtime')");
		$change = 1;
	}

	if ($change != 0)
	{
		$field = ($dir == '+')?'karmaGood':'karmaBad';
		$request = mysql_query("UPDATE {$db_prefix}members SET $field=$field+$change WHERE ID_MEMBER='$uid' $doLimitOne");
	}

	print ("<script>history.go(-1)</script>");
	obExit();
}

?>