<?php
/*****************************************************************************/
/* LockThread.php                                                            */
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

$lockthreadplver="YaBB SE 1.3.0";

function LockThread (){
	global $ID_MEMBER,$username,$moderators,$settings,$txt,$yyThreadLine,$start,$yySetLocation,$cgi,$modSettings;
	global $threadid,$db_prefix,$doLimitOne;
	$request = mysql_query("SELECT ID_MEMBER_STARTED,locked FROM {$db_prefix}topics WHERE ID_TOPIC=$threadid");
	$ThreadInfo = mysql_fetch_assoc($request);
	$locked = $ThreadInfo['locked'];
	if ($locked=='0')
	{
		if (in_array($username,$moderators) || $settings[7]=='Administrator' || $settings[7]=='Global Moderator')
			$locked = '1';
		else
			$locked = '2';
	}
	else if ($locked=='1')
	{
		if (in_array($username,$moderators) || $settings[7]=='Administrator' || $settings[7]=='Global Moderator')
			$locked = '0';
		else
			fatal_error($txt['yse31']);
	}
	else if ($locked=='2')
	{
		if ( in_array($username,$moderators) || $settings[7] == 'Administrator' || $settings[7] == 'Global Moderator' || ($ID_MEMBER == $ThreadInfo["ID_MEMBER_STARTED"] && $ID_MEMBER != '-1' && $ThreadInfo["ID_MEMBER_STARTED"] != '-1' && $modSettings['enableUserTopicLocking'] == 1) )
			$locked = '0';
	}
	mysql_free_result($request);
	if ( in_array($username,$moderators) || $settings[7] == 'Administrator' || $settings[7] == 'Global Moderator' || ($ID_MEMBER == $ThreadInfo["ID_MEMBER_STARTED"] && $ID_MEMBER != '-1' && $ThreadInfo["ID_MEMBER_STARTED"] != '-1') ){
	$request = mysql_query("UPDATE {$db_prefix}topics SET locked=$locked WHERE ID_TOPIC=$threadid $doLimitOne");
	$start = ($start != '') ? $start : 0;
	$yySetLocation = "$cgi;action=display&threadid=$threadid";
	redirectexit();
	} else { fatal_error($txt[93]); }
}

?>