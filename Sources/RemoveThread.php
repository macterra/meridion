<?php
/*****************************************************************************/
/* RemoveThread.php                                                          */
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

$removethreadplver="YaBB SE 1.3.0";

function RemoveThread2 () {
	global $username,$moderators,$settings,$modSettings,$txt,$cgi,$yySetLocation,$currentboard,$cgi,$threadid,$db_prefix;
	if (!(in_array($username,$moderators) || $settings[7]=='Administrator' || $settings[7]=='Global Moderator'))
		fatal_error("$txt[73]");
  
    //Lines 38-45 all there to delete attachments on thread deletion - Jeff
    $request = mysql_query("SELECT attachmentFilename FROM {$db_prefix}messages WHERE (ID_TOPIC=$threadid AND attachmentFilename<>NULL)");

    if (mysql_num_rows($request)>0){
	    while($row = mysql_fetch_array($request)){
		  unlink($modSettings['attachmentUploadDir'] . "/" . $row['attachmentFilename']);
	   }
	}

	$request = mysql_query("SELECT ID_BOARD,numReplies,ID_POLL FROM {$db_prefix}topics WHERE ID_TOPIC=$threadid");
	$row = mysql_fetch_array($request);
	$request = mysql_query("DELETE FROM {$db_prefix}polls WHERE ID_POLL='$row[ID_POLL]' LIMIT 1");
	$row['numReplies']++;
	$request = mysql_query("UPDATE {$db_prefix}boards SET numTopics=numTopics-1,numPosts=numPosts-$row[numReplies] WHERE ID_BOARD=$row[ID_BOARD]");
	$request = mysql_query("DELETE FROM {$db_prefix}topics WHERE ID_TOPIC=$threadid");
	$request = mysql_query("DELETE FROM {$db_prefix}messages WHERE ID_TOPIC=$threadid");
	
	$request = mysql_query("DELETE FROM {$db_prefix}log_topics WHERE ID_TOPIC=$threadid");

	$yySetLocation = "$cgi";
	redirectexit();
}

?>
