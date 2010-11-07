<?php
/*****************************************************************************/
/* RemoveOldThreads.php                                                      */
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

$removeoldthreadsplver="YaBB SE 1.3.0";

function RemoveOldThreads (){
	global $settings,$txt,$db_prefix,$modSettings,$maxdays;
	if($settings[7] != "Administrator") { fatal_error($txt[1]); }

	$yytitle = "$txt[120] $maxdays";
	$threshold = time()-(24*60*60*$maxdays);
	template_header();
	$request = mysql_query("SELECT t.ID_TOPIC,t.ID_BOARD,t.ID_POLL FROM {$db_prefix}topics as t, {$db_prefix}messages as m WHERE (m.ID_MSG = t.ID_LAST_MSG AND m.posterTime < $threshold)");
	while ($row = mysql_fetch_row($request))
	{
		$msgs = 0;
		print "$txt[50] - $row[0]<br>\n";
		print "&nbsp;&nbsp;$txt[49]<br>\n";
	//Lines 48-56 all there to delete attachments on thread deletion - Jeff
		$result = mysql_query("SELECT attachmentFilename FROM {$db_prefix}messages WHERE ID_TOPIC=$row[0] AND attachmentFilename!=NULL");

	    if (mysql_num_rows($result)>0){
			while($attachRow = mysql_fetch_array($result)){
				unlink($modSettings['attachmentUploadDir'] . "/" . $attachRow['attachmentFilename']);
			}
		}
		$result = mysql_query ("DELETE FROM {$db_prefix}messages WHERE ID_TOPIC='$row[0]'");
		$msgs = mysql_affected_rows();
		$result = mysql_query("DELETE FROM {$db_prefix}polls WHERE ID_POLL='$row[2]' LIMIT 1");
		$result = mysql_query ("DELETE FROM {$db_prefix}topics WHERE ID_TOPIC='$row[0]'");
		$result = mysql_query ("UPDATE {$db_prefix}boards SET numTopics=numTopics-1,numPosts=numPosts-$msgs WHERE ID_BOARD=$row[1]");
	}
	print " <br>$txt[51]<br> <br>";
	footer();
	obExit();
}
?>
