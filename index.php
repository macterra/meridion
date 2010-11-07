<?php
ob_start();
/* last edit by joseph nov 30 13:30 est */
/*****************************************************************************/
/* index.php                                                                 */
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

/* ### Version Info ### */
$YaBBversion = 'YaBB SE 1.3.0';
$YaBBplver = 'YaBB SE 1.3.0';

error_reporting (E_ALL ^ E_NOTICE);

include_once ("QueryString.php");
include_once ("Settings.php");
include_once ("$sourcedir/Subs.php");
include_once ("$sourcedir/Errors.php");
include_once ("$sourcedir/Load.php");
include_once ("$sourcedir/Security.php");

set_time_limit(300);

$dbcon = mysql_connect($db_server, $db_user, $db_passwd);
mysql_select_db($db_name);

/* Load the mysql version, and set a variable for 3.22 compliancy :P */
//$request = mysql_query("SELECT VERSION()");
//$row = mysql_fetch_row($request);  // version will be something like '3.23.13-log'
//global $doLimitOne;
//$doLimitOne = (substr($row[0],0,4) >= 3.23)?' LIMIT 1':'';
$doLimitOne = '';


/* ### Log this click ### */
ClickLog();

/* ### Load the user's cookie (or set to guest) ### */
LoadCookie();

/* ### Load user settings ### */
LoadUserSettings();

$usrlng_result = mysql_query("SELECT value FROM {$db_prefix}settings WHERE variable='userLanguage'");
$temp = mysql_fetch_array($usrlng_result);
$chkusrlng = $temp[0];
$lngfile_result = mysql_query("SELECT lngfile FROM {$db_prefix}members WHERE memberName='$username'");
$temp = mysql_fetch_array($lngfile_result);
$chklngfile = $temp[0];
$chklngfile2 = $temp[0];

	if ($chkusrlng == 1) {
		if ($chklngfile == Null) {
			include_once ($language);
		} else {
			include_once ($chklngfile2);
		}
	} else {
			include_once ($language);
	}
 
 //include_once("english.lng");

/* ### Banning ### */
flooding();
banning();

/* ### Write log ### */
WriteLog();

/* ### Load board information ### */
LoadBoard();

set_error_handler("yabb_error_handler");
yymain();
function  yymain() {
	global $maintenance,$action,$sourcedir,$settings,$username,$guestaccess,$modSettings,$currentboard, $db_prefix;

/* #BEGIN SUB YYMAIN */

/* #### Choose what to do based on the form action #### */
if ($maintenance == 1 && $action == 'login2') { include_once("$sourcedir/LogInOut.php"); Login2(); }
if ($maintenance == 1 && $settings[7] != 'Administrator') { include_once ("$sourcedir/Maintenance.php"); InMaintenance(); }
/* ### Guest can do the very few following actions. */
if($username == 'Guest' && $guestaccess == 0) {
            if (!(($action == 'login') || ($action == 'login2') || ($action == 'register') || ($action == 'register2')))
            { KickGuest(); }
}

if (($modSettings['trackStats']==1) && ($modSettings['hitStats']==1)){
  $date = getdate(time());
  $statsquery = mysql_query("UPDATE {$db_prefix}log_activity SET hits = hits + 1 WHERE month = $date[mon] AND day = $date[mday] AND year = $date[year]");
  if(mysql_affected_rows() == 0)
    $statsquery = mysql_query("INSERT INTO {$db_prefix}log_activity (month, day, year, hits) VALUES ($date[mon], $date[mday], $date[year], 1)");
}


$fastfind = substr($action,0,1);
/* #BEGIN FASTFIND IF STATEMENT */
if( $fastfind == 'l' ) {
	if ($action == 'login') { include_once ("$sourcedir/LogInOut.php"); Login(); }
	else if ($action == 'login2') { include_once ("$sourcedir/LogInOut.php"); Login2(); }
	else if ($action == 'logout') { include_once ("$sourcedir/LogInOut.php"); Logout(); }
	else if ($action == 'lock') { include_once ("$sourcedir/LockThread.php"); LockThread(); }
	else if ($action == 'lockVoting') { include_once ("$sourcedir/Poll.php"); LockVoting(); }
/* END FASTFIND L* */
}
if( $fastfind == 'd' ) {
	if ($action == 'display') { include_once ("$sourcedir/Display.php"); Display(); }
	else if ($action == 'displaynew') { include_once ("$sourcedir/DisplayNew.php"); DisplayNew(); }
	else if ($action == 'detailedversion') { include_once ("$sourcedir/Admin.php"); ver_detail(); }
	else if ($action == 'deletemultimembers') { include_once ("$sourcedir/Admin.php"); DeleteMultiMembers(); }
	else if ($action == 'do_clean_log') { include_once ("$sourcedir/Admin.php"); do_clean_log(); }
	else if ($action == 'deleteErrors') { include_once ("$sourcedir/Errors.php"); DeleteErrors(); }
/* #END FASTFIND D* */
}
else if( $fastfind == 'm' ) {
	$fastfind = substr($action,1,1);
	if( $fastfind == 'o' ) {
		if ($action == 'modify') { include_once ("$sourcedir/ModifyMessage.php"); ModifyMessage(); }
		else if ($action == 'modify2') { include_once ("$sourcedir/ModifyMessage.php"); ModifyMessage2(); }
		else if ($action == 'modtemp') { include_once ("$sourcedir/Admin.php"); ModifyTemplate(); }
		else if ($action == 'modtemp2') { include_once ("$sourcedir/Admin.php"); ModifyTemplate2(); }
		else if ($action == 'modsettings') { include_once ("$sourcedir/Admin.php"); ModifySettings(); }
		else if ($action == 'modsettings2') { include_once ("$sourcedir/Admin.php"); ModifySettings2(); }
		else if ($action == 'modmemgr') { include_once ("$sourcedir/Admin.php"); EditMemberGroups(); }
		else if ($action == 'modmemgr2') { include_once ("$sourcedir/Admin.php"); EditMemberGroups2(); }
		else if ($action == 'movethread') { include_once ("$sourcedir/MoveThread.php"); MoveThread(); }
		else if ($action == 'movethread2') { include_once ("$sourcedir/MoveThread.php"); MoveThread2(); }
		else if ($action == 'modifycatorder') { include_once ("$sourcedir/ManageCats.php"); ReorderCats(); }
		else if ($action == 'modifycat') { include_once ("$sourcedir/ManageCats.php"); ModifyCat(); }
		else if ($action == 'modifyboard') { include_once ("$sourcedir/ManageBoards.php"); ModifyBoard(); }
		else if ($action == 'modifyModSettings') { include_once ("$sourcedir/ModSettings.php"); ModifyModSettings(); }
		else if ($action == 'modifyModSettings2') { include_once ("$sourcedir/ModSettings.php"); ModifyModSettings2(); }
		else if ($action == 'modifykarma') { include_once ("$sourcedir/Karma.php"); ModifyKarma(); }
#END FASTFIND MO*
	}
	else {
		if ($action == 'markasread') { include_once "$sourcedir/MessageIndex.php"; MarkRead(); }
		else if ($action == 'markallasread') { include_once "$sourcedir/BoardIndex.php"; MarkAllRead(); }
		else if ($action == 'managecats') { include_once "$sourcedir/ManageCats.php"; ManageCats(); }
		else if ($action == 'mailing') { include_once "$sourcedir/Admin.php"; MailingList(); }
		else if ($action == 'membershiprecount') { include_once "$sourcedir/Admin.php"; AdminMembershipRecount(); }
		else if ($action == 'mlall') { include_once "$sourcedir/Memberlist.php"; MLAll(); }
		else if ($action == 'mlletter') { include_once "$sourcedir/Memberlist.php"; MLByLetter(); }
		else if ($action == 'mltop') { include_once "$sourcedir/Memberlist.php"; MLTop(); }
		else if ($action == 'manageboards') { include_once "$sourcedir/ManageBoards.php"; ManageBoards(); }
		else if ($action == 'manageattachments') { include_once "$sourcedir/ManageAttachments.php"; ManageAttachments(); }
		else if ($action == 'ml') { include_once "$sourcedir/Admin.php"; ml(); }
        else if ($action == 'msn') { include_once ("$sourcedir/BoardIndex.php"); msnCenter(); }
#END FASTFIND M*
	}
}
else if( $fastfind == 'p' ) {
	if ($action == 'post') { include_once "$sourcedir/Post.php"; Post(); }
	else if ($action == 'post2') { include_once "$sourcedir/Post.php"; Post2(); }
	else if ($action == 'profile') { include_once "$sourcedir/Profile.php"; ModifyProfile(); }
	else if ($action == 'profile2') { include_once "$sourcedir/Profile.php"; ModifyProfile2(); }
	else if ($action == 'postpoll') { include_once "$sourcedir/Poll.php"; PostPoll(); }
	else if ($action == 'postpoll2') { include_once "$sourcedir/Poll.php"; PostPoll2(); }
    else if ($action == 'packages') { include_once "$sourcedir/Packages.php"; Packages(); }
    else if ($action == 'packagecreate') { include_once "$sourcedir/Packages.php"; PackageCreate(); }
    else if ($action == 'packagecreate2') { include_once "$sourcedir/Packages.php"; PackageCreate2(); }
    else if ($action == 'packagepremod') { include_once "$sourcedir/Packages.php"; PackagePremod(); }
    else if ($action == 'packagemod') { include_once "$sourcedir/Packages.php"; PackageMod(); }
    else if ($action == 'packagemod2') { include_once "$sourcedir/Packages.php"; PackageMod2(); }
    else if ($action == 'packagemod3') { include_once "$sourcedir/Packages.php"; PackageMod3(); }
    else if ($action == 'packagemod4') { include_once "$sourcedir/Packages.php"; PackageMod4(); }
    else if ($action == 'packageavatars') { include_once "$sourcedir/Packages.php"; PackageAvatars(); }
    else if ($action == 'packagelanguage') { include_once "$sourcedir/Packages.php"; PackageLanguage(); }
    else if ($action == 'packageremove') { include_once "$sourcedir/Packages.php"; PackageRemove(); }
    else if ($action == 'packagelist') { include_once "$sourcedir/Packages.php"; PackageList(); }
    else if ($action == 'packageget') { include_once "$sourcedir/PackageGet.php"; PackageGet(); }
    else if ($action == 'pgadd') { include_once "$sourcedir/PackageGet.php"; PackageServerAdd(); }
    else if ($action == 'pgremove') { include_once "$sourcedir/PackageGet.php"; PackageServerRemove(); }
    else if ($action == 'pgbrowse') { include_once "$sourcedir/PackageGet.php"; PackageBrowse(); }
    else if ($action == 'pgdownload') { include_once "$sourcedir/PackageGet.php"; PackageDownload(); }
    else if ($action == 'packageuninstall') { include_once "$sourcedir/Packages.php"; PackageUninstall(); }
    else if ($action == 'pkinstalllist') { include_once "$sourcedir/Packages.php"; InstalledList(); }
    else if ($action == 'pkflushlist') { include_once "$sourcedir/Packages.php"; FlushInstall(); }
    else if ($action == 'packagemodSQL') { include_once "$sourcedir/Packages.php"; PackageModSQL(); }
    else if ($action == 'packagebrowse') { include_once "$sourcedir/Packages.php"; PackageBrowse(); }
    else if ($action == 'packshow') { include_once "$sourcedir/PackageGet.php"; PackShow(); }
#END FASTFIND P*
}
else if( $fastfind == 'r' ) {
	if ($action == 'register') { include_once "$sourcedir/Register.php"; Register(); }
	else if ($action == 'register2') { include_once "$sourcedir/Register.php"; Register2(); }
	else if ($action == 'removethread2') { include_once "$sourcedir/RemoveThread.php"; RemoveThread2(); }
	else if ($action == 'recent') { include_once "$sourcedir/Recent.php"; RecentPosts(); }
	else if ($action == 'removeoldthreads') { include_once "$sourcedir/RemoveOldThreads.php"; RemoveOldThreads(); }
	else if ($action == 'reorderboards') { include_once "$sourcedir/ManageBoards.php"; ReorderBoards(); }
	else if ($action == 'reorderboards2') { include_once "$sourcedir/ManageBoards.php"; ReorderBoards2(); }
	else if ($action == 'repairboards') { include_once "$sourcedir/RepairBoards.php"; RepairBoards(); }
	else if ($action == 'rebuildmemlist') { include_once "$sourcedir/Admin.php"; RebuildMemList(); }
    else if ($action == 'reporttm') { include_once "$sourcedir/Subs.php"; ReportToModerator(); }
	else if ($action == 'reporttm2') { include_once "$sourcedir/Subs.php"; ReportToModerator2(); }
	else if ($action == 'removeAttachmentsByAge') { include_once "$sourcedir/ManageAttachments.php"; RemoveAttachmentByAge(); }
	else if ($action == 'removeAttachmentsBySize') { include_once "$sourcedir/ManageAttachments.php"; RemoveAttachmentBySize(); }
	else if ($action == 'removeattachment') { include_once "$sourcedir/ManageAttachments.php"; RemoveAttachment(); }
	else if ($action == 'removeallattachments') { include_once "$sourcedir/ManageAttachments.php"; RemoveAllAttachments(); }
	else if ($action == 'repIndex') { include_once "$sourcedir/Reputation2.php"; Reputation(); }
	else if ($action == 'repProfile') { include_once "$sourcedir/Reputation2.php"; Profile(); }
	else if ($action == 'repRate') { include_once "$sourcedir/Reputation2.php"; EnterRatings(); }
	else if ($action == 'repSubmit') { include_once "$sourcedir/Reputation2.php"; SubmitRatings(); }
	else if ($action == 'repIndex2') { include_once "$sourcedir/Reputation2.php"; Reputation(); }
	else if ($action == 'repProfile2') { include_once "$sourcedir/Reputation2.php"; Profile(); }
	else if ($action == 'repDetails2') { include_once "$sourcedir/Reputation2.php"; RatingDetails(); }
	else if ($action == 'repRate2') { include_once "$sourcedir/Reputation2.php"; EnterRatings(); }
	else if ($action == 'repSubmit2') { include_once "$sourcedir/Reputation2.php"; SubmitRatings(); }
	else if ($action == 'repSubmitSingle') { include_once "$sourcedir/Reputation2.php"; SubmitRating(); }

	else if ($action == 'rateIndex') { include_once "$sourcedir/Ratings.php"; Index(); }
	else if ($action == 'rateEnter') { include_once "$sourcedir/Ratings.php"; EnterRatings(); }
	else if ($action == 'rateSubmit') { include_once "$sourcedir/Ratings.php"; SubmitRatings(); }
	else if ($action == 'rateEdit') { include_once "$sourcedir/Ratings.php"; EditSubject(); }
	else if ($action == 'rateEditSubmit') { include_once "$sourcedir/Ratings.php"; SubmitSubject(); }
	else if ($action == 'rateDelete') { include_once "$sourcedir/Ratings.php"; DeleteSubject(); }
#END FASTFIND R*
}
else if( $fastfind == 'i' ) {
	if ($action == 'im') { include_once "$sourcedir/InstantMessage.php"; IMIndex(); }
	else if ($action == 'imprefs') { include_once "$sourcedir/InstantMessage.php"; IMPreferences(); }
	else if ($action == 'imprefs2') { include_once "$sourcedir/InstantMessage.php"; IMPreferences2(); }
	else if ($action == 'imoutbox') { include_once "$sourcedir/InstantMessage.php"; IMOutbox(); }
	else if ($action == 'imremove') { include_once "$sourcedir/InstantMessage.php"; IMRemove(); }
	else if ($action == 'imsend') { include_once "$sourcedir/InstantMessage.php"; IMPost(); }
	else if ($action == 'imsend2') { include_once "$sourcedir/InstantMessage.php"; IMPost2(); }
	else if ($action == 'imremoveall') { include_once "$sourcedir/InstantMessage.php"; KillAllQuery(); }
	else if ($action == 'imremoveall2') { include_once "$sourcedir/InstantMessage.php"; KillAll(); }
	else if ($action == 'icqpager') { include_once "$sourcedir/ICQPager.php"; IcqPager(); }
	else if ($action == 'ipban') { include_once "$sourcedir/Admin.php"; ipban(); }
	else if ($action == 'ipban2') { include_once "$sourcedir/Admin.php"; ipban2(); }
#END FASTFIND I*
}
else if( $fastfind == 'c' ) {
	if ($action == 'changemode') { setmode; }
	else if ($action == 'changemv') { setmv; }
	else if ($action == 'createcat') { include_once "$sourcedir/ManageCats.php"; CreateCat(); }
	else if ($action == 'clean_log') { include_once "$sourcedir/Admin.php"; clean_log(); }
	else if ($action == 'chat') { include_once "$sourcedir/Chat.php"; Chat(); }
	else if ($action == 'chat2') { include_once "$sourcedir/Chat.php"; Chat2(); }
	else if ($action == 'chatlog') { include_once "$sourcedir/ChatLog.php"; ChatLog(); }
	else if ($action == 'chatlog2') { include_once "$sourcedir/ChatLog.php"; ChatLog2(); }
	else if ($action == 'chatlog3') { include_once "$sourcedir/ChatLog.php"; ChatLog3(); }
	else if ($action == 'chatters') { include_once "$sourcedir/Chatters.php"; Chatters(); }
#END FASTFIND C*
}
else if( $fastfind == 'n' ) {
	if ($action == 'notify') { include_once "$sourcedir/Notify.php"; Notify(); }
	else if ($action == 'notify2') { include_once "$sourcedir/Notify.php"; Notify2(); }
	else if ($action == 'notify3') { include_once "$sourcedir/Notify.php"; Notify3(); }
	else if ($action == 'notify4') { include_once "$sourcedir/Notify.php"; Notify4(); }
	else if ($action == 'news') { include_once "News.php"; }
	else if ($action == 'notifyXSettings') { include_once "$sourcedir/Notify.php"; NotifyXSettings(); }
#END FASTFIND N*
}
else if( $fastfind == 's' ) {
	if ($action == 'setsmp') { setsmp; }
	else if ($action == 'sendICQpage') { include_once "$sourcedir/ICQPager.php"; send_icqpage(); }
	else if ($action == 'sendtopic') { include_once "$sourcedir/SendTopic.php"; SendTopic(); }
	else if ($action == 'sendtopic2') { include_once "$sourcedir/SendTopic.php"; SendTopic2(); }
	else if ($action == 'setcensor') { include_once "$sourcedir/Admin.php"; SetCensor(); }
	else if ($action == 'setcensor2') { include_once "$sourcedir/Admin.php"; SetCensor2(); }
	else if ($action == 'search') { include_once "$sourcedir/Search.php"; plushSearch1(); }
	else if ($action == 'search2') { include_once "$sourcedir/Search.php"; plushSearch2(); }
	else if ($action == 'setreserve') { include_once "$sourcedir/Admin.php"; SetReserve(); }
	else if ($action == 'setreserve2') { include_once "$sourcedir/Admin.php"; SetReserve2(); }
	else if ($action == 'showclicks') { include_once "$sourcedir/Admin.php"; ShowClickLog(); }
	else if ($action == 'shownotify') { include_once "$sourcedir/Notify.php"; ShowNotifications(); }
	else if ($action == 'sticky') { include_once "$sourcedir/Subs.php"; Sticky(); }
    else if ($action == 'stats') { include_once "$sourcedir/Stats.php"; display_stats(); }
	else if ($action == 'ssi') { include_once "SSI.php"; }
#END FASTFIND S*
}
else {
	if ($action == 'viewprofile') { include_once "$sourcedir/Profile.php"; ViewProfile(); }
	else if ($action == 'addboard') { include_once "$sourcedir/ManageBoards.php"; CreateBoard(); }
	else if ($action == 'admin') { include_once "$sourcedir/Admin.php"; Admin(); }
	else if ($action == 'viewmembers') { include_once "$sourcedir/Admin.php"; ViewMembers(); }
	else if ($action == 'viewinactive') { include_once "$sourcedir/Admin.php"; ViewInactiveMembers(); }
	else if ($action == 'editnews') { include_once "$sourcedir/Admin.php"; EditNews(); }
	else if ($action == 'boardrecount') { include_once "$sourcedir/Admin.php"; AdminBoardRecount(); }
	else if ($action == 'editnews2') { include_once "$sourcedir/Admin.php"; EditNews2(); }
	else if ($action == 'editagreement') { include_once "$sourcedir/Admin.php"; EditAgreement(); }
	else if ($action == 'editagreement2') { include_once "$sourcedir/Admin.php"; EditAgreement2(); }
	else if ($action == 'usersrecentposts') { include_once "$sourcedir/Profile.php"; usersrecentposts(); }
	else if ($action == 'vote') { include_once "$sourcedir/Poll.php"; Vote(); }
	else if ($action == 'editpoll') { include_once "$sourcedir/Poll.php"; EditPoll(); }
	else if ($action == 'editpoll2') { include_once "$sourcedir/Poll.php"; EditPoll2(); }
	else if ($action == 'viewErrorLog') { include_once "$sourcedir/Poll.php"; ViewErrorLog(); }
	else if ($action == 'listNewPosts') { include_once "$sourcedir/Recent.php"; ListNewPosts(); }
	else if ($action == 'test') { include_once "$sourcedir/UserLanguage.php"; LoadLanguage(); }
	else if ($action == 'voteIndex') { include_once "$sourcedir/Vote.php"; VoteIndex(); }
	else if ($action == 'voteCast') { include_once "$sourcedir/Vote.php"; VoteCast(); }
	else if ($action == 'voteSubmit') { include_once "$sourcedir/Vote.php"; VoteSubmit(); }
	else if ($action == 'voteResults') { include_once "$sourcedir/Vote.php"; VoteResults(); }
	else if ($action == 'voteEdit') { include_once "$sourcedir/Vote.php"; EditVote(); }
	else if ($action == 'voteEdit2') { include_once "$sourcedir/Vote.php"; EditVote2(); }
	else if ($action == 'voteDelete') { include_once "$sourcedir/Vote.php"; DeleteVote(); }
	else if ($action == 'voteStatus') { include_once "$sourcedir/Vote.php"; VoteStatus(); }
	else if ($action == 'voteMeridion') { include_once "$sourcedir/Vote.php"; Meridion(); }
	else if ($action == 'wiki') { include_once "$sourcedir/Wiki.php"; Wiki(); }
#END FASTFIND *
}
#END FASTFIND IF STATEMENT

# No board? Show Board Index

if ($currentboard == "") { 
    include_once "$sourcedir/BoardIndex.php"; 
    BoardIndex(); 
//print "status: action=$action, user=$username, board=$currentboard";
    obExit();
}
# No action? Show Message Index
include_once "$sourcedir/MessageIndex.php";
MessageIndex();

obExit();
}

function yabb_error_handler($errno, $errstr, $errfile, $errline) {
	if (!($errno & error_reporting())) return;
	fatal_error ("$errno: $errstr\n<br>($errfile ln $errline)");
}
#END SUB YYMAIN
?>
