<?php
/*****************************************************************************/
/* Security.php                                                              */
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

$securityplver="YaBB SE 1.3.0";

function is_admin (){
	global $settings,$txt;
	if($settings[7] != 'Administrator') { fatal_error($txt[1]); }
}

function is_admin2 (){
	global $settings,$txt;
	if($settings[7] != 'Administrator') { fatal_error($txt[134]); }
}

function flooding()
{
  global $txt,$settings,$username,$REMOTE_ADDR,$db_prefix;

  $sql = "SELECT count(*) as c from {$db_prefix}log_clicks WHERE UNIX_TIMESTAMP() - logTime < 60 AND ip='$REMOTE_ADDR' AND toUrl='/bbs/index.php'";
  
  $request = mysql_query($sql);

  while ($row = mysql_fetch_row($request)) {
    $count = $row[0];

    if ($count > 10) {
      $delete = "DELETE FROM {$db_prefix}banned WHERE value='$REMOTE_ADDR'";
      $insert = "INSERT INTO {$db_prefix}banned (type, value) VALUES ('ip', '$REMOTE_ADDR')";
      mysql_query($delete);
      mysql_query($insert);
    }
  }
}

function banning (){
	global $txt,$settings,$username,$REMOTE_ADDR,$db_prefix;
	// # IP BANNING
	$remote_ip = $REMOTE_ADDR;
	$ipparts = explode(".",$REMOTE_ADDR);
	$request = mysql_query("SELECT value FROM {$db_prefix}banned WHERE (type='ip' AND (value='$remote_ip' OR value='$ipparts[0].$ipparts[1].$ipparts[2].*' OR value='$ipparts[0].$ipparts[1].*.*'))");
	if (mysql_num_rows($request) != 0) {
		$request = mysql_query("INSERT INTO {$db_prefix}log_banned (ip,logTime) VALUES ('$remote_ip',".time().")");
		$username = "Guest";
		//fatal_error("$txt[678]$txt[430]!");
		fatal_error("Error, please email admin this IP: $remote_ip");
		return;
	}

	// # EMAIL BANNING
	if ($username != 'Guest') {
		$remote_ip = $REMOTE_ADDR;
		$request = mysql_query("SELECT value FROM {$db_prefix}banned WHERE (type='email' && value='$settings[2]')");
		if (mysql_num_rows($request) != 0) {
			$request = mysql_query("INSERT INTO {$db_prefix}log_banned (ip,email,logTime) VALUES ('$remote_ip','$settings[2]',".time().")");
			$username = "Guest";
			fatal_error("$txt[678]$txt[430]! $username");
		return;
		}
	}

    // # USERNAME BANNING
	if ($username != 'Guest') {
		$remote_ip = $REMOTE_ADDR;
		$request = mysql_query("SELECT value FROM {$db_prefix}banned WHERE (type='username' && value='$username')");
		if (mysql_num_rows($request) != 0) {
			$request = mysql_query("INSERT INTO {$db_prefix}log_banned (ip,email,logTime) VALUES ('$remote_ip','$settings[2]',".time().")");
			$username = "Guest";
			fatal_error("$txt[678]$txt[430]! 3");
		return;
		}
	}
}

function CheckIcon (){
	global $icon;
	$icons = array("xx","thumbup","thumbdown","exclamation","question","lamp","smiley","angry","cheesy","laugh","sad","wink");
	if (!in_array($icon,$icons))
		$icon = "xx";
}

?>
