<?php
/*****************************************************************************/
/* Subs.php                                                                  */
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

$subsplver="YaBB SE 1.3.1";

### Set the Cookie Exp. Date and the Current Date###
$Cookie_Exp_Date = "Sun, 17-Jan-2038 00:00:00 GMT"; # default just in case
#&SetCookieExp;
#&get_date;

$currentboard = $board;
if (($board != "") && (!preg_match("/\A[\s0-9A-Za-z#%+,-\.:=?@^_]+\Z/",$board))){ fatal_error($txt[399]); }

$pwseed = 'ys';

$printurl = $boardurl."/Printpage.php";
$reminderurl = $boardurl."/Reminder.php";
$scripturl = $boardurl."/index.php";
$cgi = $scripturl."?board=".$board;

$yyheaderdone = 0;	// this is used to make the fatal error thing work better

$endComment = '<font size=1 color=silver>';
list($start_time,$waste) = explode(" ",microtime());

function totalTime($comment) {
	global $start_time,$waste,$endComment;
	list($now,$waste) = explode(" ",microtime());
	$endComment .= "$comment: ".($now-$start_time)."<br>";
	$start_time = $now;
	return $endComment."</font>";
}

function footer() {
	global $yyboardname,$yytitle,$yyuname,$yyim,$yytime,$yymenu,$yymain;
	global $yytemplate,$yycopyin,$yytemplatemain,$yycopyright,$settings,$yyVBStyleLogin,$yynews;
//	if ($settings[7]=='Administrator')
//		print totalTime("Seconds to process this directive: ");
	for($i = $yytemplatemain; $i < sizeof($yytemplate); $i++) {
		$curline = $yytemplate[$i];
		if( !$yycopyin && strstr($curline,"<yabb copyright>")) { $yycopyin = 1; }
		$tags = array();
		while (preg_match ("/<yabb\s+(\w+)>/",$curline,$tags))
		{
			$temp = "yy$tags[1]";
			if (function_exists($temp))
			{
				ob_start();
				$temp();
				$str = ob_get_contents();
				$curline = preg_replace("/<yabb\s+$tags[1]>/",$str,$curline);
				ob_end_clean();
			}
			else
			{
				$curline = preg_replace("/<yabb\s+$tags[1]>/",$$temp,$curline);
			}
		}

		print "$curline";
	}
	# Do not remove hard-coded text - it's in here so users cannot change the text easily (as if it were in .lng)
	if($yycopyin == 0) {
		print "<center><font size=5><B>Sorry, the copyright tag <yabb copyright> must be in the template.<BR>Please notify this forum's administrator that this site is using an ILLEGAL copy of YaBB!</B></font></center>";
	}
}

function template_header() {
    global $yytemplate,$yyboardname,$yytitle,$yyuname,$yyim,$yytime,$yymenu,$yymain,$yycopyright;
	global $yynews,$menusep,$enable_notification,$enable_news,$username,$db_prefix,$locale, $boarddir;
	global $maintenance,$txt,$mbname,$date,$settings,$cgi,$img,$scripturl,$helpfile,$realname,$yyheaderdone;
	global $yycopyin,$yytemplatemain,$yytitle,$yyVBStyleLogin,$timeformatstring,$timeoffset,$modSettings;

	// print stuff to prevent cacheing of pages
	header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	if ($modSettings['disableCaching']==1)
	{
		header ("Cache-Control: no-cache, must-revalidate");
		header ("Pragma: no-cache");
	}

	$yymenu = "<a href=\"$scripturl\">$img[home]</a>$menusep<a href=\"$helpfile\" target=_blank>$img[help]</a>$menusep<a href=\"$cgi;action=search\">$img[search]</a>";
	if($settings[7] == 'Administrator') { $yymenu = $yymenu.$menusep."<a href=\"$cgi;action=admin\">$img[admin]</a>"; }
	if($username == "Guest") { $yymenu .= $menusep."<a href=\"$cgi;action=login\">$img[login]</a>$menusep<a href=\"$cgi;action=register\">$img[register]</a>";
	} else {
		$euser = urlencode($username);
		$yymenu .= "$menusep<a href=\"$cgi;action=profile;user=$euser\">$img[profile]</a>";
		if($enable_notification) { $yymenu .= "$menusep<a href=\"$cgi;action=shownotify\">$img[notification]</a>"; }
		$yymenu .= "$menusep<a href=\"$cgi;action=chat\"><img src=\"YaBBImages/chat.gif\" alt=\"Chat\" border=\"0\"></a>";
//		$yymenu .= "$menusep<a href=\"$cgi;action=repIndex2\"><img src=\"YaBBImages/reputation.gif\" alt=\"Reputation\" border=\"0\"></a>";
		$yymenu .= "$menusep<a href=\"$cgi;action=voteMeridion\"><img src=\"YaBBImages/vote.gif\" alt=\"Vote\" border=\"0\"></a>";
		$yymenu .= "$menusep<a href=\"$cgi;action=logout\">$img[logout]</a>";
	}

	if($enable_news=='1') {
		$request = mysql_query("SELECT value FROM {$db_prefix}settings WHERE variable='news'");
		$temp = mysql_fetch_row($request);
		$news = $temp[0];
		$newsmessages = explode("\n",str_replace("\r","",trim($news)));
		// if we've disabled the fader....
		srand(time());
		// then some fuck-nut decided we should display a random news item
		$newstring = '';
		if (sizeof($newsmessages) == 1)
			$newstring = $newsmessages[0];
		elseif (sizeof($newsmessages) > 1)
			$newstring = $newsmessages[floor(rand(0,(sizeof($newsmessages)-1)))];
		$yynews = "<b>$txt[102]:</b> ".DoUBBC($newstring);
	}
	if($username != "Guest") {
		$request = mysql_query("SELECT COUNT(*) FROM {$db_prefix}instant_messages WHERE (toName='$username' && deletedBy!=1)");
		$temp = mysql_fetch_row($request);
		$mnum = $temp[0];
		if($mnum == "1") { $yyim = "$txt[152] <a href=\"$cgi;action=im\">$mnum $txt[471]</a>."; }
		else { $yyim = "$txt[152] <a href=\"$cgi;action=im\">$mnum $txt[153]</a>."; }
		if($maintenance) { $yyim .= "<BR><B>$txt[616]</B>"; }
	}

	ob_start();
   $templateFile = $boarddir."/template.php";
   if (!file_exists($templateFile))
      $templateFile = $boarddir."/template.html";
   include ($templateFile);
   $yytemplate = explode("\n",ob_get_contents());
   ob_end_clean();
   
	if (!sizeof($yytemplate)){ die ("$txt[23]: $templateFile"); }

	$yyboardname = $mbname;
	$time = isset($settings[18])?$settings[18]:0;

	$yytime = lang_strftime(time()+(($timeoffset+$time)*3600));
	// display their username if they haven't set their real name yet.
	$tmp = ($realname=='')?$username:$realname;
	$yyuname = ($username == 'Guest') ? "$txt[248] <b>$txt[28]</b>. $txt[249] <a href=\"$cgi;action=login\">$txt[34]</a> $txt[377] <a href=\"$cgi;action=register\">$txt[97]</a>." : "$txt[247] <b>$tmp</b>, ";
	$yycopyin = 0;

	$yyVBStyleLogin = '<br>';
	if ($modSettings['enableVBStyleLogin']=='1' && $username=='Guest')
	{
		$yyVBStyleLogin =<<<EOT
<form action="$cgi;action=login2" method="post"><br><input type=text name="user" size="7"> <input type=password name="passwrd" size="7"> <select name="cookielength"><option value="60">$txt[yse53]</option><option value="1440">$txt[yse47]</option><option value="10080">$txt[yse48]</option><option value="302400">$txt[yse49]</option><option value="$txt[yse50]" selected>$txt[yse50]</option></select> <input type="submit" value="$txt[34]"><br>$txt[yse52]</form>
EOT;
	}

	for( $yytemplatemain = 0; $yytemplatemain < sizeof($yytemplate); $yytemplatemain++ ) {
		$curline = $yytemplate[$yytemplatemain];
		if(strstr($curline,"<yabb main>")) { $yytemplatemain++; break; }
		if( !$yycopyin && strstr($curline,"<yabb copyright>")) { $yycopyin = 1; }
		$tags = array();
		while (preg_match ("/<yabb\s+(\w+)>/",$curline,$tags))
		{
			$temp = "yy$tags[1]";
			if (function_exists($temp))
			{
				ob_start();
				$temp();
				$str = ob_get_contents();
				$curline = preg_replace("/<yabb\s+$tags[1]>/",$str,$curline);
				ob_end_clean();
			}
			else
			{
				$curline = preg_replace("/<yabb\s+$tags[1]>/",$$temp,$curline);
			}
		}

		print "$curline";
	}
	$yyheaderdone = 1;
}

function jeffsdatediff($now, $old)
{

  $DIS = $now - $old;	// Diff In Secs
  $secs = $DIS % 60; // modulo
  $DIS -= $secs;
  $days = floor($DIS / (24*60*60));
  $DIS -= $days * (24*60*60);
  $hours = floor($DIS / (60*60));
  $DIS -= $hours * (60*60);
  $mins = floor($DIS / 60);
  $DIS -= $mins * 60;
  //$diffstr= "$days Days, $hours Hours, $mins Minutes, $secs Seconds";
  return $days;
}

function datediff($now, $old, $format='seconds')
{
  $datearray1 = getdate($now);
  $datearray2 = getdate($old);
  $seconds = $now - $old;	// Diff In Secs
  $minutes = (int) round ($seconds / 60);
  $hours = (int) round ($seconds / 3600);
  $days = (int) round ($seconds / 86400);
  $years = $datearray1['year']-$datearray2['year']-(($datearray1['yday']>$datearray2['yday'])?0:1);

  return $$format;
}

//Fix by Omar Bazavilvazo
function lang_strftime($currtime) {
	global $locale, $days, $months, $days_short, $months_short, $username, $settings, $timeformatstring;

	if ($username == 'Guest' || $settings[17]=='')
		$str = stripslashes($timeformatstring);
	else
		$str = stripslashes($settings[17]);

	if (setlocale(LC_TIME, $locale)) {
	   $str = ereg_replace('%a', ucwords(strftime('%a', $currtime)), $str);
	   $str = ereg_replace('%A', ucwords(strftime('%A', $currtime)), $str);
	   $str = ereg_replace('%b', ucwords(strftime('%b', $currtime)), $str);
	   $str = ereg_replace('%B', ucwords(strftime('%B', $currtime)), $str);
   }
   else {
		$str = ereg_replace('%a', $days_short[(int)strftime('%w',$currtime)], $str);
		$str = ereg_replace('%A', $days[(int)strftime('%w',$currtime)], $str);
		$str = ereg_replace('%b', $months_short[(int)strftime('%m',$currtime)-1], $str);
		$str = ereg_replace('%B', $months[(int)strftime('%m',$currtime)-1], $str);
		$str = ereg_replace('%p', (int)strftime('%H',$currtime)<12?"am":"pm", $str);
   }

   return strftime($str, $currtime);
}

function timeformat ($logTime){
	global $timeformatstring,$username,$settings,$timeoffset,$txt,$db_prefix,$locale;
	if (!isset($GLOBALS['todayMod']))
	{
		// pre-load variable so we don't query too much
		$req = mysql_query("SELECT value FROM {$db_prefix}settings WHERE variable='todayMod'");
		list($GLOBALS['todayMod'])=mysql_fetch_row($req);
	}
	$time = isset($settings[18])?$settings[18]:0;
	$time = ($timeoffset+$time)*3600;
	$nowtime = $time+time();
	$time += $logTime;
	if ($GLOBALS['todayMod']=='1')
	{
		$t1 = getdate($time);
		$t2 = getdate($nowtime);
		$strtfmt = (($username == 'Guest' || $settings[17]=='')?$timeformatstring:$settings[17]);
		if ($t1['yday']==$t2['yday'] && $t1['year']==$t2['year'])
			return $txt['yse10'].date(substr_count($strtfmt, "%H") == 0?" h:i:sa":" H:i:s", $time);	//Bugfix by Omar Bazavilvazo
	}
	return lang_strftime ($time);
}

function jumpto (){
	global $board,$txt,$settings,$scripturl,$db_prefix;
	$request = mysql_query("SELECT name,ID_CAT FROM {$db_prefix}categories WHERE (FIND_IN_SET('$settings[7]',memberGroups)!=0 OR memberGroups='' OR '$settings[7]'='Administrator' OR '$settings[7]'='Global Moderator')  ORDER BY catOrder");

	$selecthtml = "<select name=\"values\" onChange=\"if(this.options[this.selectedIndex].value) window.location.href='$scripturl' + this.options[this.selectedIndex].value;\">";

	$selecthtml .= "<option value=\"\">$txt[251]:</option>\n";
	while ($row = mysql_fetch_row($request))
	{
		$selecthtml .= "<option value=\"\">-----------------------------</option>\n";
		$selecthtml .= "<option value=\"#$row[1]\">$row[0]</option>\n";
		$selecthtml .= "<option value=\"\">-----------------------------</option>\n";
		$request2 = mysql_query("SELECT name,ID_BOARD FROM {$db_prefix}boards WHERE ID_CAT=$row[1] ORDER BY boardOrder");
		while ($row2 = mysql_fetch_row($request2))
		{
			if ($board == $row2[1])
				$selecthtml .= "<option value=\"?board=$row2[1]\" selected> =>$row2[0]</option>\n";
			else
				$selecthtml .= "<option value=\"?board=$row2[1]\"> =>$row2[0]</option>\n";
		}
	}
	$selecthtml .= "</select>";
	return $selecthtml;
}

function sendmail($to,$subject,$message,$from = null) {

    global $mailtype,$webmaster_email;
	if ($from==null){ $from=$webmaster_email; }
    $subject = stripslashes($subject);
    //Messy crap - perhaps someone can alter this later
            $subject = str_replace ("&quot;", "\"", $subject);
            $subject = str_replace ("&#039;", "'", $subject);
            $subject = str_replace ("&amp;", "&", $subject);
            $subject = str_replace ("&lt;", "<", $subject);
            $subject = str_replace ("&gt;", ">", $subject);
    $message = stripslashes($message);
	$mail_result = mail ($to,$subject,$message,
		"From: $from\r\n".
		"Return-Path: $webmaster_email");

    return $mail_result;
}

function spam_protection (){
	global $timeout,$REMOTE_ADDR,$txt,$db_prefix;
//	if (!$timeout){ return (false); }

	$time = time();
	$ip = $REMOTE_ADDR;

	$request = mysql_query("DELETE FROM {$db_prefix}log_floodcontrol WHERE ($time-logTime > $timeout)");
	$request = mysql_query("SELECT ip FROM {$db_prefix}log_floodcontrol WHERE ip='$REMOTE_ADDR' LIMIT 1");
	if (mysql_num_rows($request) == 0)
	{
		$request = mysql_query("INSERT INTO {$db_prefix}log_floodcontrol (ip,logTime) VALUES ('$REMOTE_ADDR',$time)");
		return (false);
	}
	else
	{
		fatal_error("$txt[409] $timeout $txt[410]");
		return (true);
	}
}

function SetCookieExp () {
	global $Cookie_Length,$Cookie_Exp_Date;
	# set to default if missing
	if ($Cookie_Length == "") { $Cookie_Length = "120"; }
	$expires = ($Cookie_Length*60);
	$Cookie_Exp_Date = gmdate ("D, d-M-Y H:i:s GMT",(time()+$expires));
}

/**
 * Preparses a message, puts [url] tags around urls etc.
 * @param $message the message to parse the code in
 * @return the preparsed code
 */

function preparsecode($message, $realname, $username) {

	global $settings, $ext;


	$message = fixTags($message);

	if(strstr($realname, "[") || strstr($realname, "]") || strstr($realname, "'") || strstr($realname, "\"")) {
		$realname = $username;
	}


	//		'/(^|[ \n\r\t])((http(s?):\/\/)(www\.)?([a-z0-9_-]+(\.[a-z0-9_-]+)+)(:[0-9]+)?(\/[^\/ \)\(\n\r]*)*)/is',

	$codes = array(
		'/(\/me) (.*)([\r\n]?)/i'
	);

	// 		'\1[url=\2]\2[/url]',
	$codesto = array(
		"[me=$realname]\\2[/me]\\3"
	);

	$message = preg_replace($codes, $codesto, $message);
	$message = str_replace("\r","", $message);

	// Check if all quotes are closed
	preg_match_all("/(\[quote author=(.+?) link=(.+?) date=(.+?)\])|(\[quote\])/", $message, $regs);
	$quoteopen =  count($regs[0]);
	preg_match_all("/(\[\/quote\])/", $message, $regs);
	$quoteclose =  count($regs[0]);

	if($quoteopen > $quoteclose) {
		$toclose = $quoteopen - $quoteclose;
		for($i = 0 ; $i < $toclose ; $i++) {
			$message .= "[/quote]";
		}
	} elseif($quoteclose > $quoteopen) {
		$toopen = $quoteclose - $quoteopen;
		for($i = 0 ; $i < $toopen ; $i++) {
			$message = "[quote]$message";
		}
	}

    // Check if all code tags are closed
	preg_match_all("/(\[code\])/", $message, $regs);
	$codeopen =  count($regs[0]);
	preg_match_all("/(\[\/code\])/", $message, $regs);
	$codeclose =  count($regs[0]);

	if($codeopen > $codeclose) {
		$toclose = $codeopen - $codeclose;
		for($i = 0 ; $i < $toclose ; $i++) {
			$message .= "[/code]";
		}
	} elseif($codeclose > $codeopen) {
		$toopen = $codeclose - $codeopen;
		for($i = 0 ; $i < $toopen ; $i++) {
			$message = "[code]$message";
		}
	}


	return $message;

}

function fixTags ($message) {

	// First, we fix the [img] Tags

	while (preg_match("/\[img\](.+?)\[\/img\]/i", $message, $matches)) {

		$searchfor = $matches[1];
		$replace = $searchfor;
		$replace = trim($replace);	// remove all leading and trailing whitespaces
		if (!stristr($replace,"http://")) {
			if (!stristr($replace,"https://")) {
				$replace = "http://".$replace;
			} else {
				$replace = stristr($replace,"https://");
			}
		} else {
			$replace = stristr($replace,"http://");
		}
  $message = str_replace("[img]".$searchfor."[/img]","{img}".$replace."{/img}",$message);
  $message = str_replace("[IMG]".$searchfor."[/IMG]","{IMG}".$replace."{/IMG}",$message);

	}

	$message = str_replace("{img}","[img]",$message);
	$message = str_replace("{/img}","[/img]",$message);
    $message = str_replace("{IMG}","[IMG]",$message);
	$message = str_replace("{/IMG}","[/IMG]",$message);

	// Now the [url]'s

	while (preg_match("/\[url\](.+?)\[\/url\]/si", $message, $matches)) {

		$searchfor = $matches[1];
		$replace = $searchfor;
		$replace = trim($replace);	// remove all leading and trailing whitespaces
		if (!stristr($replace,"http://")) {
			if (!stristr($replace,"https://")) {
				$replace = "http://".$replace;
			} else {
				$replace = stristr($replace,"https://");
			}
		} else {
			$replace = stristr($replace,"http://");
		}
		$message = str_replace("[url]".$searchfor."[/url]","{url}".$replace."{/url}",$message);
        $message = str_replace("[URL]".$searchfor."[/URL]","{URL}".$replace."{/URL}",$message);

 }

	$message = str_replace("{url}","[url]",$message);
	$message = str_replace("{/url}","[/url]",$message);
    $message = str_replace("{URL}","[URL]",$message);
	$message = str_replace("{/URL}","[/URL]",$message);

	while (preg_match("/\[url=(.+?)\](.+?)\[\/url\]/si", $message, $matches)) {

		$searchfor = $matches[1];
		$replace = $searchfor;
		$replace = trim($replace);	// remove all leading and trailing whitespaces
		if (!stristr($replace,"http://")) {
			if (!stristr($replace,"https://")) {
				$replace = "http://".$replace;
			} else {
				$replace = stristr($replace,"https://");
			}
		} else {
			$replace = stristr($replace,"http://");
		}
		$message = str_replace("[url=".$searchfor."]".$matches[2]."[/url]","{url=".$replace."]".$matches[2]."{/url}",$message);
        $message = str_replace("[URL=".$searchfor."]".$matches[2]."[/URL]","{URL=".$replace."]".$matches[2]."{/URL}",$message);

	}

	$message = str_replace("{url=","[url=",$message);
	$message = str_replace("{/url}","[/url]",$message);
    $message = str_replace("{URL=","[URL=",$message);
	$message = str_replace("{/URL}","[/URL]",$message);

	// Now the [iurl]'s

	while (preg_match("/\[iurl\](.+?)\[\/iurl\]/si", $message, $matches)) {

		$searchfor = $matches[1];
		$replace = $searchfor;
		$replace = trim($replace);	// remove all leading and trailing whitespaces
		if (!stristr($replace,"http://")) {
			if (!stristr($replace,"https://")) {
				$replace = "http://".$replace;
			} else {
				$replace = stristr($replace,"https://");
			}
		} else {
			$replace = stristr($replace,"http://");
		}
		$message = str_replace("[iurl]".$searchfor."[/iurl]","{iurl}".$replace."{/iurl}",$message);
         $message = str_replace("[IURL]".$searchfor."[/IURL]","{IURL}".$replace."{/IURL}",$message);

	}

	$message = str_replace("{iurl}","[iurl]",$message);
	$message = str_replace("{/iurl}","[/iurl]",$message);
    $message = str_replace("{IURL}","[IURL]",$message);
	$message = str_replace("{/IURL}","[/IURL]",$message);

	while (preg_match("/\[iurl=(.+?)\](.+?)\[\/iurl\]/si", $message, $matches)) {

		$searchfor = $matches[1];
		$replace = $searchfor;
		$replace = trim($replace);	// remove all leading and trailing whitespaces
		if (!stristr($replace,"http://")) {
			if (!stristr($replace,"https://")) {
				$replace = "http://".$replace;
			} else {
				$replace = stristr($replace,"https://");
			}
		} else {
			$replace = stristr($replace,"http://");
		}
		$message = str_replace("[iurl=".$searchfor."]".$matches[2]."[/iurl]","{iurl=".$replace."]".$matches[2]."{/iurl}",$message);
        $message = str_replace("[IURL=".$searchfor."]".$matches[2]."[/IURL]","{IURL=".$replace."]".$matches[2]."{/IURL}",$message);

	}

	$message = str_replace("{iurl=","[iurl=",$message);
	$message = str_replace("{/iurl}","[/iurl]",$message);
    $message = str_replace("{IURL=","[IURL=",$message);
	$message = str_replace("{/IURL}","[/IURL]",$message);

	// Now the [ftp]'s

	while (preg_match("/\[ftp\](.+?)\[\/ftp\]/si", $message, $matches)) {

		$searchfor = $matches[1];
		$replace = $searchfor;
		$replace = trim($replace);	// remove all leading and trailing whitespaces
		if (!stristr($replace,"ftp://")) {
			$replace = "ftp://".$replace;
		} else {
			$replace = stristr($replace,"ftp://");
		}
		$message = str_replace("[ftp]".$searchfor."[/ftp]","{ftp}".$replace."{/ftp}",$message);
        $message = str_replace("[FTP]".$searchfor."[/FTP]","{FTP}".$replace."{/FTP}",$message);

	}

	$message = str_replace("{ftp}","[ftp]",$message);
	$message = str_replace("{/ftp}","[/ftp]",$message);
    $message = str_replace("{FTP}","[FTP]",$message);
	$message = str_replace("{/FTP}","[/FTP]",$message);

	while (preg_match("/\[ftp=(.+?)\](.+?)\[\/ftp\]/si", $message, $matches)) {

		$searchfor = $matches[1];
		$replace = $searchfor;
		$replace = trim($replace);	// remove all leading and trailing whitespaces
		if (!stristr($replace,"ftp://")) {
			$replace = "ftp://".$replace;
		} else {
			$replace = stristr($replace,"ftp://");
		}
		$message = str_replace("[ftp=".$searchfor."]".$matches[2]."[/ftp]","{ftp=".$replace."]".$matches[2]."{/ftp}",$message);
        $message = str_replace("[FTP=".$searchfor."]".$matches[2]."[/FTP]","{FTP=".$replace."]".$matches[2]."{/FTP}",$message);

	}

	$message = str_replace("{ftp=","[ftp=",$message);
	$message = str_replace("{/ftp}","[/ftp]",$message);
    $message = str_replace("{FTP=","[FTP=",$message);
	$message = str_replace("{/FTP}","[/FTP]",$message);
	return $message;

}

/**
 * Parses both smilies and kB code as required
 * @param $message the message to parse the code in
 * @param $boarddata the data array of the current board
 * @param $code whether or not to parse kB codes
 * @param $smilies same for smilies :-)
 */

function doUBBC($message,$enableSmilies = 1) {

	global $settings, $text;

	$message = str_replace('$','&#36;',$message);
	$message = str_replace('[[','{<{',$message);
	$message = str_replace(']]','}>}',$message);

	$message = " $message";
	$message = preg_replace ("/([\n >\(])([\w\-_]+?):\/\/([\w\-_]+)((\.[\w\-_]+)+(:[\d]+)?((\/[\w\-_%]+(\.[\w\-_%]+)*)|(\/[\w\-_%]*))*(\/?(\?[&;=\w\+%]+)*)?(#[\w\-_]*)?)/", "\\1[url=\\2://\\3\\4]\\2://\\3\\4[/url]", $message);
	$message = preg_replace ("/([\n >\(])www((\.[\w\-_]+)+(:[\d]+)?((\/[\w\-_%]+(\.[\w\-_%]+)*)|(\/[\w\-_%]*))*(\/?(\?[&;=\w\+%]+)*)?(#[\w\-_]*)?)/", "\\1[url=http://www\\2]www\\2[/url]", $message);

	$message = substr($message,1);

	$parts = split('\[\/?code\]', $message);

	for($i = 0 ; $i < count($parts) ; $i++)
	{
		if($i%2==0)
		{
			$parts[$i] = doparsecodesmilies($parts[$i], $enableSmilies);
			if($i > 0)
				$parts[$i] = "</font></td></tr></table>".$parts[$i];
		}
		elseif($i <= count($parts)-1)
			$parts[$i] = "<font size=1><b>Code:</b></font><table border=0 cellspacing=1 cellpadding=2 width=\"100%\"><tr><td class=quote><font face=\"Courier new\">".$parts[$i];
	}


	/*$patterns = array(
		'/(^[^(\[\/code\])(\[\/php\])]+|\[\/code\]|\[\/php\])(.*)(\[code\]|\[php\]|$)/Uise',
		'/(^\[\/code\]|\[code\]$)/i',
		'/\[code\][\n\r]*(.+?)[\n\r]*\[\/code\]/is',
		'/\[php\][\n\r]*(.+?)[\n\r]*\[\/php\]/esi'
	);

	$replace = array(
		"'\\1'.doparsecodesmilies('\\2',$enableSmilies).'\\3'",
		"",
		"<font size=1><b>Code:</b></font><table border=0 cellspacing=1 cellpadding=2 width=\"100%\"><tr><td class=quote><font face=\"Courier new\">\\1</font></td></tr></table>",
		"phphighlight('\\1')"
	);*/

//	$message = stripslashes(preg_replace($patterns, $replace, addslashes($message)));
	$message = implode("", $parts);
	//echo "<hr>\n$message\n<hr>";
	$message = str_replace('{<{','[',$message);
	$message = str_replace('}>}',']',$message);
	// $message = stripslashes($message); // Maybe you'll have to uncomment this
	$message = str_replace("  ","&nbsp; ",$message);
	$message = str_replace("\t","&nbsp; &nbsp; ",$message);
	$message = str_replace("\n\r","<br>",$message);
	$message = str_replace("\r","<br>",$message);
	$message = str_replace("\n","<br>",$message);
	return $message;

}

/**
 * This function is being called by the above function (in a regexp) and will parse
 * smilies and tags as desired outside [code] and [php] tags.
 * @param $message the message to parse the code in
 * @param $boarddata the data array of the current board
 */

function doparsecodesmilies($message,$enableSmilies = 1) {

	global $codefromcache, $codetocache, $smileyfromcache, $smileytocache, $text, $scripturl,$imagesdir;

        $WikiNameRegexp = "(?<![[:alnum:]])(?:[[:upper:]][[:lower:]]+){2,}(?![[:alnum:]])";

	if(gettype($codefromcache) != "array" || gettype($smileyfromcache) != "array") {

		$codefromcache = array(
			'/([a-z_-][a-z0-9\._-]*@[a-z0-9_-]+(\.[a-z0-9_-]+)+)/is',
			'/\[url\](.+?)\[\/url\]/is',
			'/\[url=(.+?)\](.+?)\[\/url\]/is',
			'/\[iurl\](.+?)\[\/iurl\]/is',
			'/\[iurl=(.+?)\](.+?)\[\/iurl\]/is',
			'/\[b\](.+?)\[\/b\]/is',
			'/\[i\](.+?)\[\/i\]/is',
			'/\[u\](.+?)\[\/u\]/is',
			'/\[s\](.+?)\[\/s\]/is',
			'/\[move\](.+?)\[\/move\]/is',
			'/\n?\[quote author=(.+?) link=(.+?) date=(.+?)\]\n*/ei',
			'/\[\/quote\]/i',
			'/\n?\[quote\]\n*/i',
			'/\[me=([^\]]+)\](.+?)\[\/me\]/is',
			'/\[img\](.+?)\[\/img\]/i',
			'/\[img width=([0-9]+) height=([0-9]+)\s*\](.+?)\[\/img\]/i',
			'/\[img height=([0-9]+) width=([0-9]+)\s*\](.+?)\[\/img\]/i',
			'/\[color=([\w#]+)\](.*?)\[\/color\]/is',
			'/\[black\](.+?)\[\/black\]/is',
			'/\[white\](.+?)\[\/white\]/is',
			'/\[red\](.+?)\[\/red\]/is',
			'/\[green\](.+?)\[\/green\]/is',
			'/\[blue\](.+?)\[\/blue\]/is',
			'/\[font=(.+?)\](.+?)\[\/font\]/is',
			'/\[size=(.+?)\](.+?)\[\/size\]/is',
			'/\[pre\](.+?)\[\/pre\]/is',
			'/\[left\](.+?)\[\/left\]/is',
			'/\[right\](.+?)\[\/right\]/is',
			'/\[center\](.+?)\[\/center\]/is',
			'/\[sub\](.+?)\[\/sub\]/is',
			'/\[sup\](.+?)\[\/sup\]/is',
			'/\[tt\](.+?)\[\/tt\]/is',
			'/\[table\](.+?)\[\/table\]/is',
			'/\[tr\](.*?)\[\/tr\]/is',
			'/\[td\](.*?)\[\/td\]/is',
			'/\[ftp\](.+?)\[\/ftp\]/is',
			'/\[ftp=(.+?)\](.+?)\[\/ftp\]/is',
			'/\[glow=(.+?),(.+?),(.+?)\](.+?)\[\/glow\]/eis',
			'/\[shadow=(.+?),(.+?),(.+?)\](.+?)\[\/shadow\]/is',
			'/\[email\](.+?)\[\/email\]/is',
			'/\[hr\]/i',
			'/\[flash=(\S+?),(\S+?)\](\S+?)\[\/flash\]/is',
			'/\[youtube](\S+?)\[\/youtube\]/is',
			'/\[list\]/',
			'/\[\/list\]/',
			'/(<\/?table>|<\/?tr>|<\/td>)<br>/',
                        '/%%(.+?)%%/',
		);

		$codetocache = array(
			'[url=mailto:\1]\1[/url]',
			"<a href=\"\\1\" target=_blank>\\1</a>",
			"<a href=\"\\1\" target=_blank>\\2</a>",
			"<a href=\"\\1\">\\1</a>",
			"<a href=\"\\1\">\\2</a>",
			"<b>\\1</b>",
			"<i>\\1</i>",
			"<u>\\1</u>",
			"<s>\\1</s>",
			"<marquee>\\1</marquee>",
			"'<br><font size=1><b><a href=\"$scripturl?action=display;\\2\">Quote from: \\1 on '.timeformat('\\3').'</a>	</b></font><table border=0 cellspacing=1 cellpadding=2 width=\"100%\"><tr><td class=quote>'",
			"</td></tr></table>",
			"<br><font size=1><b>Quote:</b></font><table border=0 cellspacing=1 cellpadding=2 width=\"100%\"><tr><td class=quote>",
			"<font class=meaction>* \\1 \\2</font>",
			"<img src=\"\\1\" alt=\"\" border=\"0\">",
			"<img src=\"\\3\" alt=\"\" border=\"0\" width=\"\\1\" height=\"\\2\">",
			"<img src=\"\\3\" alt=\"\" border=\"0\" width=\"\\2\" height=\"\\1\">",
			"<font color=\"\\1\">\\2</font>",
			"<font color=\"#000000\">\\1</font>",
			"<font color=\"#FFFFFF\">\\1</font>",
			"<font color=\"#FF0000\">\\1</font>",
			"<font color=\"#00FF00\">\\1</font>",
			"<font color=\"#0000FF\">\\1</font>",
			"<font face=\"\\1\">\\2</font>",
			"<font size=\\1>\\2</font>",
			"<pre>\\1</pre>",
			"<div align=\"left\">\\1</div>",
			"<div align=\"right\">\\1</div>",
			"<div align=\"center\">\\1</div>",
			"<sub>\\1</sub>",
			"<sup>\\1</sup>",
			"<tt>\\1</tt>",
			"<table>\\1</table>",
			"<tr>\\1</tr>",
			"<td>\\1</td>",
			"<a href=\"\\1\" target=_blank>\\1</a>",
			"<a href=\"\\1\" target=_blank>\\2</a>",
			"'<table style=\"Filter: Glow(Color=\\1, Strength='.(('\\2'<400)?'\\2':400).')\" width='.(('\\3'<400)?'\\3':400).'\>\\4</table>'",
			"<span style=\"Filter: Shadow(Color=\\1, Direction=\\2); width: \\3 px; \">\\4</span>",
			"\\1",
			"<hr>",
			"<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\\1 height=\\2><param name=movie value=\\3><param name=play value=true><param name=loop value=true><param name=quality value=high><embed src=\\3 width=\\1 height=\\2 play=true loop=true quality=high></embed></object>",
			"<object width=425 height=350><param name=movie value=http://www.youtube.com/v/\\1></param><param name=wmode value=transparent></param><embed src=http://www.youtube.com/v/\\1 type=application/x-shockwave-flash wmode=transparent width=425 height=350></embed></object>",
			"<ul>",
			"</ul>",
			"\\1",
                        "<a href=\"http://virus.lucifer.com/wiki/\\1\" target=_blank>\\1</a>",
		);

		// Instead we use:
		$smiliesfrom = array("::)", ">:(", ">:D", ":)", ";)", ':D', ';D', ':(', ':o', '8)', ':P', '???', ':-[', ':-X', ':-*', ":\'(",':-\\',"^-^","O0");
		//  :-/ :-* :'(
		$smiliesto = array('rolleyes', 'angry', "evil", "smiley", "wink", 'cheesy', 'grin', 'sad', 'shocked', 'cool', 'tongue', 'huh', 'embarassed', 'lipsrsealed', 'kiss', 'cry','undecided','azn','afro'); // You'll get the idea

		// This smiley regex makes sure it doesn't parse smilies within code tags (so [url=mailto:David@bla.com] doesn't parse the :D smiley)
		for($i=0 ; $i < count($smiliesfrom); $i++) {
			//$smileyfromcache[] ='/(((\][^\[]*)|(^[^\[]*))([^\w]|^))('.str_replace('|','\|', quotemeta(str_replace("<", "&lt;", str_replace(">", "&gt;", str_replace("\/", "\\\/", $smiliesfrom[$i]))))).')/s';
			//$smileytocache[] = "\\1[img]$imagesdir/$smiliesto[$i].gif[/img]";
			$smileyfromcache[] ='/(((>[^<]*)|(^[^<]*))([^\w]|^))('.str_replace('|','\|', quotemeta(str_replace("<", "&lt;", str_replace(">", "&gt;", str_replace("\/", "\\\/", $smiliesfrom[$i]))))).')/s';
			$smileytocache[] = "\\1<img src=\"$imagesdir/$smiliesto[$i].gif\" alt=\"\">";
		}

	}

	if ($enableSmilies) {
		$message = parsesmilies($message);
		$message = preg_replace("/\[:-\](.+?)\[\/:-\]/", "<img src=\"$imagesdir/smilies/\\1.gif\" alt=\"\">", $message);
	}
	$message = parsecode($message);

	// List items
	$itemcode = array(
		'*' => '',
		'@' => ' type="disc"',
		'+' => ' type="square"',
		'x' => ' type="square"',
		'#' => ' type="square"',
		'o' => ' type="circle"',
		'0' => ' type="circle"'
	);

	$message = preg_replace("!\\[([*@+x#o0])\\]!Uie", '\'<li\'.$itemcode[\'\\1\'].\'>\'', $message);

	return $message;

}

/**
 * Parses the special KeyBulletin code (also being used in UBB, YaBB, iB and other boards)
 * and returns it parsed (duh).
 * @param $message the message to parse the code in
 * @return the parsed code
 */

function parsecode($message) {
	global $codefromcache, $codetocache, $modSettings;

	if(!strstr($message, '[') && !strstr($message, '://') && !strstr($message, '@') && !strstr($message, '/me')) {
		return $message;
	}

	if(gettype($codefromcache) != "array") {
		return $message;
	}

	$message = preg_replace($codefromcache, $codetocache, $message);

    //start resize/restrict posted images mod by Mostmaster
	$maxwidth = $modSettings['maxwidth'];
	$maxheight = $modSettings['maxheight'];
	if(!($maxwidth=="0" && $maxheight=="0")){
		preg_match_all('/<img src="(http:\/\/.+?)" alt="" border="0">/is', $message, $imgmatches, PREG_PATTERN_ORDER);
		for($i=0; $i<count($imgmatches[1]); $i++){
			$imagesize = @getimagesize($imgmatches[1][$i]);
			$width = $imagesize[0];
			$height = $imagesize[1];
			if($width>$maxwidth || $height>$maxheight){
				if($width>$maxwidth && $maxwidth!="0"){
					$height = floor($maxwidth/$width*$height);
					$width = $maxwidth;
					if($height>$maxheight && $maxheight!="0"){
						$width = floor($maxheight/$height*$width);
						$height = $maxheight;
					}
				}else{
					if($height>$maxheight && $maxheight!="0"){
						$width = floor($maxheight/$height*$width);
						$height = $maxheight;
					}
				}
			}
			$imgnew[$i] = "<img src=\"" . $imgmatches[1][$i] . "\" width=\"$width\" height=\"$height\" alt=\"\" border=\"0\">";
		}
		$message = str_replace($imgmatches[0], $imgnew, $message);
	}
	//end resize/restrict posted images mod by Mostmaster

	return $message;

}

/**
 * Parses smilies in the specified $message
 * @param $message the message to parse
 * @return the parsed code
 */

function parsesmilies($message) {

	global $smileyfromcache, $smileytocache;

	$oldmessage = "";
	while($oldmessage != $message) {
		$oldmessage = $message;
		$message = preg_replace($smileyfromcache, $smileytocache, $message);
	}

	return $message;

}

function KickGuest (){
	global $yytitle, $txt, $reminderurl, $color, $cgi, $Cookie_Length;
	$yytitle = $txt[34];
	template_header();
	print <<<EOT
<table border=0 cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[633]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]"><font size=2><BR>
    $txt[634]<BR>
    $txt[635] <a href="$cgi;action=register">$txt[636]</a> $txt[637]
    <BR><BR></font></td>
</tr><tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[34]</b></font></td>
</tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]"><font size=2><form action="$cgi;action=login2" method="POST">
    <table border=0 align="left">
      <tr>
        <td align="right"><font size=2><b>$txt[35]:</b></font></td>
        <td><font size=2><input type=text name="user" size=20></font></td>
      </tr><tr>
        <td align="right"><font size=2><b>$txt[36]:</b></font></td>
        <td><font size=2><input type=password name="passwrd" size=20></font></td>
      </tr><tr>
        <td align="right"><font size=2><b>$txt[497]:</b></font></td>
        <td><font size=2><input type=text name="cookielength" size=4 maxlength="4" value="$Cookie_Length"></font></td>
      </tr><tr>
        <td align="right"><font size=2><b>$txt[508]:</b></font></td>
        <td><font size=2><input type=checkbox name="cookieneverexp"></font></td>
      </tr><tr>
        <td align=center colspan=2><BR><input type=submit value="$txt[34]"></td>
      </tr><tr>
        <td align=center colspan=2><small><small><a href="$reminderurl?action=input_user">$txt[315]</small></small></a><BR><BR></td>
      </tr>
    </table>
    </td>
  </tr>
</table></form>
EOT;
	footer();
	obExit();
}

/**
 * Highlights passed $code (only works in PHP4+)
 * @param $code code to be parsed
 * @return the php code parsed code
 */
function phphighlight($code)
{
   if (floor(phpversion())<4)
      $buffer=$code;
   else
   {
      $code = stripslashes($code);
      $code = stripslashes($code);
      $code = str_replace("&gt;", ">", $code);
      $code = str_replace("&lt;", "<", $code);
      $code = str_replace("&#36;", "\$", $code);
      $code = str_replace("&quot;", "\"", $code);
      if (!strstr($code,'<?')) {
         $code="<?php\n".trim($code)."\n?>";
         $addedtags=1;
      }
      ob_start();
      $oldlevel=error_reporting(0);
      highlight_string($code);
      error_reporting($oldlevel);
      $buffer = ob_get_contents();
      ob_end_clean();
      $buffer = str_replace("&quot", "\"", $buffer);
     }

   return "<font size=1><b>PHP:</b></font><table border=0 cellspacing=1 cellpadding=2 width=\"100%\"><tr><td class=quote>".addslashes(addslashes($buffer))."</td></tr></table>";
}


function WriteLog (){
	global $REMOTE_ADDR,$username,$db_prefix;
	$logTime = time();
	$identity = $username;
	if($username == 'Guest') { $identity = $REMOTE_ADDR; }
	$request = mysql_query ("UPDATE {$db_prefix}members SET lastLogin=$logTime WHERE memberName='$identity'");
	$request = mysql_query ("DELETE FROM {$db_prefix}log_online WHERE (logTime < ".($logTime - 900)." || identity='$identity')");
	$request = mysql_query ("INSERT INTO {$db_prefix}log_online (identity,logTime) VALUES ('$identity',$logTime)");
}

function ClickLog (){
	global $HTTP_REFERER,$HTTP_USER_AGENT,$REMOTE_ADDR,$REQUEST_URI,$ClickLogTime,$db_prefix;
	$logTime = time();
	$threshold = time() - ($ClickLogTime*60);
	$request = mysql_query ("INSERT INTO {$db_prefix}log_clicks (ip,logTime,agent,fromUrl,toUrl) VALUES ('$REMOTE_ADDR',$logTime,'$HTTP_USER_AGENT','$HTTP_REFERER','$REQUEST_URI')");
	$request = mysql_query ("DELETE FROM {$db_prefix}log_clicks WHERE logTime<$threshold");
}

function redirectinternal () {
	global $currentboard,$sourcedir;
	if( $currentboard != '') {
		if( isset($num) ) {
			include_once("$sourcedir/Display.php");
			Display();
		}
		else {
			include_once ("$sourcedir/MessageIndex.php");
			MessageIndex();
		}
	}
	else {
		include_once ("$sourcedir/BoardIndex.php");
		BoardIndex();
	}
	obExit();
}

function redirectexit (){
	global $yySetLocation;
	header("Location: ".str_replace(" ","%20",$yySetLocation));
	obExit();
}

function Sticky ()
{
	global $threadid,$sticky,$moderators,$settings,$username,$txt,$cgi,$yySetLocation,$db_prefix,$doLimitOne;
	if (!in_array($username,$moderators) && $settings[7] != 'Administrator' && $settings[7] != 'Global Moderator') { fatal_error("$txt[67]"); }
	if ($threadid == '') { fatal_error("No thread specified!"); }
	$newsticky = ($sticky == '1' ) ? 0 : 1;
	$request = mysql_query("UPDATE {$db_prefix}topics SET isSticky=$newsticky WHERE ID_TOPIC=$threadid$doLimitOne");
	$yySetLocation = "$cgi;action=display;threadid=$threadid";
	redirectexit();
}

function ReportToModerator() {
	global $txt,$color,$cgi,$thread,$board,$id,$subject,$poster,$username;
	template_header();
    $rname=LoadRealName($username);
    print <<<EOT
<table border=0 width="80%" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor" align="center" cellpadding="4">
  <tr>
    <td  class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[rtm1]</b></font></td>
  </tr><tr>
    <td  class="windowbg" bgcolor="$color[windowbg]"><BR>
    	<form action="$cgi;action=reporttm2" method="POST">
    	<center>
    	<font size=2>$txt[rtm2]

        <input type=text name="comment" value="" size="50">
        <input type="hidden" name="reporter" value="$rname ($username)">
        <input type="hidden" name="thread" value="$thread">
        <input type="hidden" name="board" value="$board">
        <input type="hidden" name="id" value="$id">
        <input type="hidden" name="subject" value="$subject">
        <input type="hidden" name="poster" value="$poster">
        <input type=submit value="$txt[rtm10]">

        </form>
        </font>
        </center>
    </td>
  </tr>
</table>
EOT;

footer();
exit;
}

function ReportToModerator2() {

	global $txt,$cgi,$db_prefix,$thread,$board,$id,$subject,$poster,$yySetLocation,$comment,$reporter;

	// first, we create the message...

	$mailtext = "$txt[rtm6]: $comment\n$txt[rtm5] $username\n$txt[rtm7]: $reporter\n$txt[rtm4]: $poster\n$txt[rtm9]: $id\n $cgi&action=display&threadid=$thread&start=$id\n\n";
	$mailsub = "$txt[rtm3]\:  $subject $txt[rtm4] $poster";

	// lets get some mods...

	$themoderators = array();

	$result = mysql_query("SELECT moderators FROM {$db_prefix}boards WHERE (ID_BOARD=$board)");
	if (mysql_num_rows($result) > 0){
		$mod_row = mysql_fetch_array($result);
		if (strlen($mod_row['moderators']) > 0)
			$themoderators = explode(",",$mod_row['moderators']);	    
	}

	// loop through admins

	$results = mysql_query("SELECT memberName FROM {$db_prefix}members WHERE (memberGroup='Administrator') OR (memberGroup='Global Moderator')");

	while ($row = mysql_fetch_array($results)) {
		if (!in_array ($row['memberName'], $themoderators)) {
			$themoderators[] = $row['memberName'];
		}
	}

	// OK, send mails...

	if (sizeof($themoderators)>0) {
		while (list ($key, $val) = each ($themoderators)) {
			$result = mysql_query("SELECT emailAddress FROM {$db_prefix}members WHERE memberName='$val'");
			if ($result){
				$mod_row = mysql_fetch_array($result);
				sendmail($mod_row['emailAddress'],$mailsub,$mailtext);
//				echo "$mod_row[emailAddress]<br><br>$mailsub<br><br><pre>$mailtext</pre><br><hr>";
			}
		}
	} else {
		fatal_error("$txt[rtm11]");
	}

	//$yySetLocation = "$cgi;action=display;threadid=$thread";
	//redirectexit();

}

function obExit()
{
	global $HTTP_ACCEPT_ENCODING,$modSettings;
	if (!$modSettings['enableCompressedOutput'])
	{
		ob_end_flush();
		exit;
	}
 	$html = ob_get_contents();
	ob_end_clean();
	$level = 1;

	// Check if zlib is installed
	if(function_exists("crc32") && function_exists("gzcompress"))
	{
	$encoding = '';
		// if so check what kind of gzipping can be used
		if(strpos(" ".$HTTP_ACCEPT_ENCODING, "x-gzip"))
			$encoding = "x-gzip";
		elseif(strpos(" ".$HTTP_ACCEPT_ENCODING,"gzip"))
			$encoding = "gzip";

		if ($encoding) // ok, encoding is accepted
		{
			header("Content-Encoding: $encoding");

			$size = strlen($html);
			$crc = crc32($html);

			$output = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
			$output .= substr(gzcompress($html, $level), 0, -4);
			$output .= pack("V", $crc);
			$output .= pack("V", $size);
		}
		else
		$output = $html;
	}
	else
	$output = $html;

	echo $output;
	exit;
}

function yymsn()
{
 print "<object classid=\"clsid:F3A614DC-ABE0-11d2-A441-00C04F795683\" codebase=\"#Version=2,0,0,83\" codetype=application/x-oleobject id=MsgrObj width=0 height=0></object>";
 print "<OBJECT classid=\"clsid:FB7199AB-79BF-11d2-8D94-0000F875C541\" codeType=application/x-oleobject id=MsgrApp width=0 height=0></OBJECT>";

}

function yytop()
{
	
}

function yybottom()
{
	
}

function OnlineStatus($user){

global $db_prefix;
global $txt;

$onlineatatus = 0;
$onlinequery = mysql_query("SELECT identity FROM {$db_prefix}log_online WHERE 1 ORDER BY logTime DESC");
while ($onlinetmp = mysql_fetch_array($onlinequery)) {
$identity = $onlinetmp[0];
if ($identity == $user) {
$onlinestatus ++;
 }
}
return $onlinestatus;
}
?>
