<?php
ob_start();
/*****************************************************************************/
/* Printpage.php                                                             */
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

include_once ("QueryString.php");
include_once ("Settings.php");
include_once ("$language");
include_once ("$sourcedir/Subs.php");
include_once ("$sourcedir/Errors.php");
include_once ("$sourcedir/Load.php");
include_once ("$sourcedir/Security.php");
$dbcon = mysql_connect($db_server, $db_user, $db_passwd);
mysql_select_db($db_name);

/* Load the mysql version, and set a variable for 3.22 compliancy :P */
$request = mysql_query("SELECT VERSION()");
$row = mysql_fetch_row($request);  // version will be something like '3.23.13-log'
global $doLimitOne;
$doLimitOne = (substr($row[0],0,4) >= 3.23)?' LIMIT 1':'';

/* ### Log this click ### */
ClickLog();

$printpageplver="YaBB SE";

/* ### Load the user's cookie (or set to guest) ### */
LoadCookie();

/* ### Load user settings ### */
LoadUserSettings();

/* #### Choose what to do based on the form action #### */
if ($maintenance == 1 && $settings[7] != 'Administrator') { include_once "$sourcedir/Maintenance.php"; InMaintenance(); }

/* ### Load board information ### */
LoadBoard();

/* ### Banning ### */
banning();

/* ### Write log ### */
WriteLog();

/* ### Determine what category we are in. ### */
$request = mysql_query ("SELECT ID_CAT,name FROM {$db_prefix}boards WHERE ID_BOARD=$currentboard");
$row = mysql_fetch_array($request);
$boardname = $row['name'];

$request = mysql_query("SELECT name FROM {$db_prefix}categories WHERE ID_CAT=$row[ID_CAT]");
$row = mysql_fetch_array($request);
$cat = $row['name'];

$request = mysql_query("SELECT m.posterTime,m.posterName FROM {$db_prefix}messages as m WHERE (m.ID_TOPIC=$threadid) ORDER BY ID_MSG LIMIT 1");
$row = mysql_fetch_array($request);
LoadRealName($row['posterName']);
$startedby = $realNames[$row['posterName']];
$startedon = timeformat($row['posterTime']);

### Lets output all that info. ###
print <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<style type="text/css">
<!--
BODY          {font-family: Verdana, arial, helvetica, serif; font-size:12px;}
TABLE       {empty-cells: show }
TD            {font-family: Verdana, arial, helvetica, serif; color: #000000; font-size:12px;}
-->
</style>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">

<title>$txt[668]</title>
</head>

<body bgcolor="#FFFFFF" text="#000000">


<font size="3" face="arial">
 <b>$mbname</B></font>
	<pre>
    $cat => $boardname => $txt[195]: $startedby $txt[176] $startedon
	</pre>
	<table border=0 width="90%"><tr><td>
EOT;

### Split the threads up so we can print them.
$request = mysql_query("SELECT subject,posterName,posterTime,body FROM {$db_prefix}messages WHERE ID_TOPIC=$threadid ORDER BY ID_MSG");
while ($row = mysql_fetch_array($request)) {
	$threadtitle = $row['subject'];
	$threadposter = $row ['posterName'];
	$threaddate = timeformat($row ['posterTime']);
	$threadpost = $row ['body'];

## Do YaBBC Stuff ###
	$threadpost = str_replace ("<br>","\n",$threadpost);
	$threadpost = str_replace ("[code]","<BR><B>Code:</B><br><table bgcolor=#000000 cellspacing=1><tr><td><table cellpadding=2 cellspacing=0 bgcolor=#FFFFFF><tr><td><font face=Courier size=1 color=#000000>", $threadpost);
	$threadpost = str_replace ("[/code]", "</font></td></tr></table></td></tr></table>", $threadpost);

	$threadpost = str_replace ("[b]", "<b>", $threadpost);
	$threadpost = str_replace ("[i]", "<i>", $threadpost);
	$threadpost = str_replace ("[u]", "<u>", $threadpost);
	$threadpost = str_replace ("[s]", "<s>", $threadpost);
	$threadpost = str_replace ("[/b]", "</b>", $threadpost);
	$threadpost = str_replace ("[/i]", "</i>", $threadpost);
	$threadpost = str_replace ("[/u]", "</u>", $threadpost);
	$threadpost = str_replace ("[/s]", "</s>", $threadpost);

	$threadpost = str_replace ("[move]", "", $threadpost);
	$threadpost = str_replace ("[/move]", "", $threadpost);

	$threadpost = preg_replace ("/\[glow(.*)\](.*)\[\/glow\]/","$2",$threadpost);
	$threadpost = preg_replace ("/\[shadow(.*)\](.*)\[\/shadow\]/", "$2", $threadpost);

	$threadpost = preg_replace ("/\[color=(.*)\](.*)\[\/color\]/","$2",$threadpost);

	$threadpost = str_replace("[black]","",$threadpost);
	$threadpost = str_replace("[white]","",$threadpost);
	$threadpost = str_replace("[red]","",$threadpost);
	$threadpost = str_replace("[green]","",$threadpost);
	$threadpost = str_replace("[blue]","",$threadpost);
	$threadpost = str_replace("[/black]","",$threadpost);
	$threadpost = str_replace("[/white]","",$threadpost);
	$threadpost = str_replace("[/red]","",$threadpost);
	$threadpost = str_replace("[/green]","",$threadpost);
	$threadpost = str_replace("[/blue]","",$threadpost);

	$threadpost = preg_replace ("/\[font=(.*)\](.*)\[\/font\]/", "<font face=\"$1\">$2</font>", $threadpost);
	$threadpost = preg_replace ("/\[size=(.*)\](.*)\[\/size\]/", "<font size=\"$1\">$2</size>", $threadpost);

	$threadpost = str_replace("[img(.*)]","",$threadpost);
	$threadpost = str_replace("[/img]","",$threadpost);

	$threadpost = str_replace ("[tt]", "<tt>", $threadpost);
	$threadpost = str_replace ("[/tt]", "</tt>", $threadpost);
	$threadpost = str_replace ("[left]", "<p align=left>", $threadpost);
	$threadpost = str_replace ("[/left]", "</p>", $threadpost);
	$threadpost = str_replace ("[center]", "<center>", $threadpost);
	$threadpost = str_replace ("[/center]", "</center>", $threadpost);
	$threadpost = str_replace ("[right]", "<p align=right>", $threadpost);
	$threadpost = str_replace ("[/right]", "</p>", $threadpost);
	$threadpost = str_replace ("[sub]", "<sub>", $threadpost);
	$threadpost = str_replace ("[/sub]", "</sub>", $threadpost);
	$threadpost = str_replace ("[sup]", "<sup>", $threadpost);
	$threadpost = str_replace ("[/sup]", "</sup>", $threadpost);
	$threadpost = str_replace ("[fixed]", "<font face=\"Courier New\">", $threadpost);
	$threadpost = str_replace ("[/fixed]", "</font>", $threadpost);

	$threadpost = str_replace ("[[","{{",$threadpost);
	$threadpost = str_replace ("]]","}}",$threadpost);

	$threadpost = str_replace ("|","&#124;",$threadpost);

	$threadpost = str_replace ("[hr]","<hr width=40% align=left size=1>",$threadpost);
	$threadpost = str_replace ("[br]","\n",$threadpost);

	$threadpost = preg_replace("/\[url=(.+)\](.+)\s*\[\/url\]/","$2 ($1)",$threadpost);
	$threadpost = preg_replace("/\[url\](.+)\[\/url\]/","$1",$threadpost);

	$threadpost = preg_replace("/\[email\](.*)\[\/email\]/","$1",$threadpost);
	$threadpost = preg_replace("/\[email=(.*)\](.*)\[\/email\]/","$2 ($1)",$threadpost);

	$threadpost = str_replace("[news]","",$threadpost);
	$threadpost = str_replace("[/news]","",$threadpost);
	$threadpost = str_replace("[gopher]","",$threadpost);
	$threadpost = str_replace("[/gopher]","",$threadpost);
	$threadpost = str_replace("[ftp]","",$threadpost);
	$threadpost = str_replace("[/ftp]","",$threadpost);

	$threadpost = preg_replace("/\[quote\s+author=(.*) link=(.*) date=(.*)\](.*)\[\/quote\]/","<BR><i>on $3, <a href=$scripturl?action=display&$2>$1 wrote</a>:</i><table bgcolor=#000000 cellspacing=1 width=90%><tr><td width=100%><table cellpadding=2 cellspacing=0 width=100% bgcolor=#FFFFFF><tr><td width=100%><font size=1 color=#000000>$4</font></td></tr></table></td></tr></table>",$threadpost);
	$threadpost = preg_replace("/\[quote\](.*)\[\/quote\]/","<BR><i>Quote:</i><table bgcolor=#000000 cellspacing=1 width=90%><tr><td width=100%><table cellpadding=2 cellspacing=0 width=100% bgcolor=#FFFFFF><tr><td width=\"100%\"><font face=\"Arial,Helvetica\" size=\"1\" color=#000000>$1</font></td></tr></table></td></tr></table>",$threadpost);

	$threadpost = str_replace("[list]","<ul>",$threadpost);
	$threadpost = str_replace("[*]","<li>",$threadpost);
	$threadpost = str_replace("[/list]","</li>",$threadpost);

	$threadpost = str_replace("[pre]","<pre>",$threadpost);
	$threadpost = str_replace("[/pre]","</pre>",$threadpost);

	$threadpost = preg_replace("/\[flash=(.*)\](.*)\[\/flash\]/","$2",$threadpost);

	$threadpost = str_replace ("{{","[",$threadpost);
	$threadpost = str_replace ("}}","]",$threadpost);

	$threadpost = preg_replace("/\[table(.*?)\]/","<table$1>",$threadpost);
	$threadpost = str_replace("[\/table]","</table>",$threadpost);
	$threadpost = str_replace("[tr]","<tr>",$threadpost);
	$threadpost = str_replace("[/tr]","</tr>",$threadpost);
	$threadpost = str_replace("[td]","<td>",$threadpost);
	$threadpost = str_replace("[/td]","</td>",$threadpost);

	$threadpost = str_replace("\n","<br>",$threadpost);

	### Censor it ###
	CensorTxt($threadtitle);
	CensorTxt($threadpost);

print <<<EOT
<hr size=2 width="100%">
<font size=2>$txt[196]: <b>$threadtitle</b>
<BR>$txt[197]: <b>$threadposter</b> $txt[176] <b>$threaddate</b></font>
<hr>
<table border="0" width="95%" align=center><tr><td><font size="2">$threadpost</font></td></tr></table><p>
EOT;

}

print <<<EOT
<BR><BR>
<center><font size="1">$yycopyright</font></center>
</td></tr></table>
</body></html>
EOT;

function donoopen ()
{
global $txt;
print <<<EOT
<html><head><title>$txt[199]</title></head>
<body bgcolor=#ffffff>
<center>$txt[199]</center>
</body></html>

EOT;
}

?>
