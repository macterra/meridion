<?php
//File last updated and altered on April 1st/2002 - Jeff Lewis
/*****************************************************************************/
/* Upgrade.php                                                               */
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
?>
<html>
<head>
	<title>YaBB SE SQL Upgrade Utility</title>
<style>
<!--
body {
	font-family : Verdana;
	font-size : 10pt;
}
td {
	font-size : 10pt;
}
-->
</style>
</head>
<body bgcolor="#FFFFFF">
<center><table border=0 cellspacing=1 cellpadding=4 bgcolor="#000000" width=90%>
<tr>
	<th bgcolor="#34699E"><font color="#FFFFFF">YaBB SE SQL Upgrade Utility</font></th>
</tr>
<tr>
 <td bgcolor="#FFFFFF">
<?
/* ### Version Info ### */

include_once ("Settings.php");
$error=0;
$dbcon = mysql_connect($db_server, $db_user, $db_passwd);
mysql_select_db($db_name);

/*############# BEGIN TABLE ALTERATIONS - CHANGES #############*/

//Alter boards - ID_BOARD
$result1 = mysql_query("ALTER TABLE {$db_prefix}boards CHANGE ID_BOARD ID_BOARD INT NOT NULL AUTO_INCREMENT");
if(!$result1){
    echo "<font color=red>Error altering boards table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Boards table altered! ID_BOARD changed from TINYINT to INT.</font><BR>";

//Alter log_boards - ID_BOARD
$result2 = mysql_query("ALTER TABLE {$db_prefix}log_boards CHANGE ID_BOARD ID_BOARD INT NOT NULL");
if(!$result2){
    echo "<font color=red>Error altering log_boards table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>log_boards table altered! ID_BOARD changed from TINYINT to INT.</font><BR>";

//Alter log_mark_read - ID_BOARD
$result3 = mysql_query("ALTER TABLE {$db_prefix}log_mark_read CHANGE ID_BOARD ID_BOARD INT NOT NULL");
if(!$result3){
    echo "<font color=red>Error altering log_mark_read table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>log_mark_read table altered! ID_BOARD changed from TINYINT to INT.</font><BR>";

//Alter topics - ID_BOARD
$result4 = mysql_query("ALTER TABLE {$db_prefix}topics CHANGE ID_BOARD ID_BOARD INT NOT NULL");
if(!$result4){
    echo "<font color=red>Error altering topics table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>topics table altered! ID_BOARD changed from TINYINT to INT.</font><BR>";

//Alter members - memberName
$result5 = mysql_query("ALTER TABLE {$db_prefix}members CHANGE memberName memberName VARCHAR(80) NOT NULL DEFAULT ''");
if(!$result5){
    echo "<font color=red>Error altering members table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>members table altered! memberName changed from tinytext to varchar(80).</font><BR>";

//Alter messages - attachmentSize
$result6 = mysql_query("ALTER TABLE {$db_prefix}messages CHANGE attachmentSize attachmentSize MEDIUMINT NOT NULL DEFAULT '0'");
if(!$result6){
    echo "<font color=red>Error altering messages table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>messages table altered! attachmentSize changed from INT to MEDIUMINT.</font><BR>";

/*############# END TABLE ALTERATIONS - CHANGES #############*/

/*############# BEGIN TABLE ALTERATIONS - ADDS #############*/

//Alter log_topics - notificationSent
$result7 = mysql_query("ALTER TABLE {$db_prefix}log_topics ADD notificationSent TINYINT(4) DEFAULT '0' NOT NULL");
if(!$result7){
    echo "<font color=red>Error altering log_topics table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>log_topics table altered! notificationSent field added.</font><BR>";

//Alter boards - isAnnouncement
$result8 = mysql_query("ALTER TABLE {$db_prefix}boards ADD isAnnouncement TINYINT(4) DEFAULT '0' NOT NULL");
if(!$result8){
    echo "<font color=red>Error altering boards table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>boards table altered! isAnnouncement field added.</font><BR>";

//Alter boards - notifyAnnouncements
$result9 = mysql_query("ALTER TABLE {$db_prefix}boards ADD notifyAnnouncements TINYINT(4) DEFAULT '0' NOT NULL");
if(!$result9){
    echo "<font color=red>Error altering boards table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Boards table altered! notifyAnnouncements field added.</font><BR>";

//Alter boards - count
$result10 = mysql_query("ALTER TABLE {$db_prefix}boards ADD count TINYINT(4) DEFAULT '0' NOT NULL");
if(!$result10){
    echo "<font color=red>Error altering boards table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Boards table altered! count field added.</font><BR>";

//Alter members - usertitle
$result11 = mysql_query("ALTER TABLE {$db_prefix}members ADD usertitle TINYTEXT");
if(!$result11){
    echo "<font color=red>Error altering members table. SQL Error: ".mysql_error()."</font><P>";
    $error++;}
else
 echo "<font color=green>Members table altered! usertitle field added.</font><P>";

//Alter members - lngfile
$result12 = mysql_query("ALTER TABLE {$db_prefix}members ADD lngfile TINYTEXT");
if(!$result12){
    echo "<font color=red>Error altering members table. SQL Error: ".mysql_error()."</font><P>";
    $error++;}
else
 echo "<font color=green>Members table altered! lngfile field added.</font><P>"; 

//Alter members - notifyAnnouncements
$result13 = mysql_query("ALTER TABLE {$db_prefix}members ADD notifyAnnouncements TINYINT(4) DEFAULT '0' NOT NULL");
if(!$result13){
    echo "<font color=red>Error altering members table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Members table altered! notifyAnnouncements field added.</font><BR>";

//Alter members - notifyOnce
$result14 = mysql_query("ALTER TABLE {$db_prefix}members ADD notifyOnce TINYINT(4) DEFAULT '0' NOT NULL");
if(!$result14){
    echo "<font color=red>Error altering members table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Members table altered! notifyOnce field added.</font><BR>";

//Alter members - MSN
$result15 = mysql_query("ALTER TABLE {$db_prefix}members ADD MSN TINYTEXT");
if(!$result15){
    echo "<font color=red>Error altering members table. SQL Error: ".mysql_error()."</font><P>";
    $error++;}
else
 echo "<font color=green>Members table altered! MSN field added.</font><P>";	

//Alter members - memberIP 
$result16 = mysql_query("ALTER TABLE {$db_prefix}members ADD memberIP TINYTEXT");
if(!$result16){
    echo "<font color=red>Error altering members table. SQL Error: ".mysql_error()."</font><P>";
    $error++;}
else
 echo "<font color=green>Members table altered! memberIP field added.</font><P>";

//Alter members - secretQuestion
$result17 = mysql_query("ALTER TABLE {$db_prefix}members ADD secretQuestion TINYTEXT NOT NULL");
if(!$result17){
    echo "<font color=red>Error altering members table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Members table altered! secretQuestion field added.</font><BR>";

//Alter members - secretAnswer
$result18 = mysql_query("ALTER TABLE {$db_prefix}members ADD secretAnswer TINYTEXT NOT NULL");
if(!$result18){
    echo "<font color=red>Error altering members table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Members table altered! secretAnswer field added.</font><BR>";

/*############# END TABLE ALTERATIONS - ADDS #############*/

/*############# BEGIN SETTINGS ADDITIONS #############*/

//Check for titlesEnable setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='titlesEnable'");
if (mysql_num_rows($modCheck)==0){
$result19 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('titlesEnable', '1')");
if(!$result19){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - titlesEnable added.</font><BR>";
}
else
 echo "<font color=green>Settings titlesEnable already exists!</font><BR>";

//Check for topicSummaryPosts setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='topicSummaryPosts'");
if (mysql_num_rows($modCheck)==0){
$result20 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('topicSummaryPosts', '15')");
if(!$result20){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - topicSummaryPosts added!</font><BR>";
}
else
 echo "<font color=green>Settings topicSummaryPosts already exists!</font><BR>";

//Check for enableUserTopicLocking setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='enableUserTopicLocking'");
if (mysql_num_rows($modCheck)==0){
$result21 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('enableUserTopicLocking', '1')");
if(!$result21){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - enableUserTopicLocking added!</font><BR>";
}
else
 echo "<font color=green>Settings enableUserTopicLocking already exists!</font><BR>";

//Check for enableReportToMod setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='enableReportToMod'");
if (mysql_num_rows($modCheck)==0){
$result22 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('enableReportToMod', '1')");
if(!$result22){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - enableReportToMod added!</font><BR>";
}
else
 echo "<font color=green>Settings enableReportToMod already exists!</font><BR>";

//Check for enableErrorLogging setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='enableErrorLogging'");
if (mysql_num_rows($modCheck)==0){
$result23 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('enableErrorLogging', '1')");
if(!$result23){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - enableErrorLogging added!</font><BR>";
}
else
 echo "<font color=green>Settings enableErrorLogging already exists!</font><BR>";

//Check for viewNewestFirst setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='viewNewestFirst'");
if (mysql_num_rows($modCheck)==0){
$result24 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('viewNewestFirst', '0')");
if(!$result24){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - viewNewestFirst added!</font><BR>";
}
else
 echo "<font color=green>Settings viewNewestFirst already exists!</font><BR>";

//Check for trackStats setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='trackStats'");
if (mysql_num_rows($modCheck)==0){
$result25 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('trackStats', '1')");
if(!$result25){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - trackStats added!</font><BR>";
}
else
 echo "<font color=green>Settings trackStats already exists!</font><BR>";

//Check for hitStats setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='hitStats'");
if (mysql_num_rows($modCheck)==0){
$result26 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('hitStats', '0')");
if(!$result26){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - hitStats added!</font><BR>";
}
else
 echo "<font color=green>Settings hitStats already exists!</font><BR>";

//Check for userLanguage setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='userLanguage'");
if (mysql_num_rows($modCheck)==0){
$result27 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('userLanguage', '0')");
if(!$result27){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - userLanguage added!</font><BR>";
}
else
 echo "<font color=green>Settings userLanguage already exists!</font><BR>";

//Check for mostOnline setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='mostOnline'");
if (mysql_num_rows($modCheck)==0){
$result28 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('mostOnline', '0')");
if(!$result28){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - mostOnline added!</font><BR>";
}
else
 echo "<font color=green>Settings mostOnline already exists!</font><BR>";

//Check for mostDate setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='mostDate'");
if (mysql_num_rows($modCheck)==0){
$result29 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('mostDate', '0')");
if(!$result29){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - mostDate added!</font><BR>";
}
else
 echo "<font color=green>Settings mostDate already exists!</font><BR>";

//Check for notifyAnncmnts_UserDisable setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='notifyAnncmnts_UserDisable'");
if (mysql_num_rows($modCheck)==0){
$result30 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('notifyAnncmnts_UserDisable', '1')");
if(!$result30){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - notifyAnncmnts_UserDisable added!</font><BR>";
}
else
 echo "<font color=green>Settings notifyAnncmnts_UserDisable already exists!</font><BR>";

//Check for viewNewestFirst setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='viewNewestFirst'");
if (mysql_num_rows($modCheck)==0){
$result31 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('viewNewestFirst', '0')");
if(!$result31){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - viewNewestFirst added!</font><BR>";
}
else
 echo "<font color=green>Settings viewNewestFirst already exists!</font><BR>";

//Check for maxwidth setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='maxwidth'");
if (mysql_num_rows($modCheck)==0){
$result32 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('maxwidth', '0')");
if(!$result32){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - maxwidth added!</font><BR>";
}
else
 echo "<font color=green>Settings maxwidth already exists!</font><BR>";

//Check for maxheight setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='maxheight'");
if (mysql_num_rows($modCheck)==0){
$result33 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('maxheight', '0')");
if(!$result33){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - maxheight added!</font><BR>";
}
else
 echo "<font color=green>Settings maxheight already exists!</font><BR>";

//Check for onlineEnable setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='onlineEnable'");
if (mysql_num_rows($modCheck)==0){
$result34 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('onlineEnable', '0')");
if(!$result34){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - onlineEnable added!</font><BR>";
}
else
 echo "<font color=green>Settings onlineEnable already exists!</font><BR>";
 
//Check for censorWholeWord setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='censorWholeWord'");
if (mysql_num_rows($modCheck)==0){
$result35 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('censorWholeWord', '0')");
if(!$result35){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended -censorWholeWord added!</font><BR>";
}
else
 echo "<font color=green>Settings censorWholeWord already exists!</font><BR>";

//Check for compactTopicPagesContiguous setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='compactTopicPagesContiguous'");
if (mysql_num_rows($modCheck)==0){
$result36 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('compactTopicPagesContiguous', '0')");
if(!$result36){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - compactTopicPagesContiguous added!</font><BR>";
}
else
 echo "<font color=green>Settings compactTopicPagesContiguous already exists!</font><BR>";

//Check for compactTopicPagesEnable setting
$modCheck = mysql_query("SELECT * FROM {$db_prefix}settings WHERE variable='compactTopicPagesEnable'");
if (mysql_num_rows($modCheck)==0){
$result37 = mysql_query("INSERT INTO {$db_prefix}settings VALUES ('compactTopicPagesEnable', '0')");
if(!$result37){
    echo "<font color=red>Error adding record to settings table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>Settings table appended - compactTopicPagesEnable added!</font><BR>";
}
else
 echo "<font color=green>Settings compactTopicPagesEnable already exists!</font><BR>";

/*############# END SETTINGS ADDITIONS #############*/

/*############# BEGIN TABLE CREATION #############*/

$result38 = mysql_query("CREATE TABLE {$db_prefix}log_errors (ID_ERROR bigint(20) NOT NULL auto_increment, logTime bigint(20) NOT NULL default '0', memberName tinytext NOT NULL, IP tinytext NOT NULL, url text NOT NULL, message text NOT NULL, PRIMARY KEY (ID_ERROR)) TYPE=MyISAM");
if(!$result38){
    echo "<font color=red>Error creating log_errors table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>log_errors table created!</font><BR>";
 
$result39 = mysql_query("CREATE TABLE {$db_prefix}log_activity (month tinyint(4) NOT NULL default '0', day tinyint(4) NOT NULL default '0', year mediumint(9) NOT NULL default '0', hits int(11) NOT NULL default '0', topics int(11) NOT NULL default '0', posts int(11) NOT NULL default '0', registers int(11) NOT NULL default '0', mostOn int(11) NOT NULL default '0') TYPE=MyISAM");
if(!$result39){
    echo "<font color=red>Error creating log_activity table. SQL Error: ".mysql_error()."</font><BR>";
    $error++;}
else
 echo "<font color=green>log_errors table created!</font><BR>";

/*############# END TABLE CREATION #############*/

/*############# BEGIN FILE ALTERATION #############*/
$delfile = 0;
if (file_exists('Settings_bak.php'))
   $delfile = 1;

if ($delfile == 1)
 {
   @unlink("Settings.bak");
   echo "Settings.bak deleted.  Please check to verify.<BR>";
 }
else
 {
   @rename("Settings.bak", "Settings_bak.php");
   echo "Settings.bak renamed to Settings_bak.php.  Please check to verify.<BR>";
 }

@chmod ("Settings_bak.php", 0666);
echo "Settings_bak.php has been chmoded to 666.  Please check to verify.<BR>";

@chmod("$directory/Packages/installed.list", 0777);
@chmod("$directory/Packages/server.list", 0777);


/*############# END FILE ALTERATION #############*/

echo "</td></tr></table>";
if($error==0)
 echo "<P>Upgrade of SQL is ok.";
elseif ($error==1)
 echo "<P>There was <B>1</B> error when upgrading your SQL. An error message indicating a duplicate table is ok.";
elseif ($error>1)
 echo "<P>There were <B>$error</B> errors when upgrading your SQL. An error message indicating a duplicate table is ok.";
?>
