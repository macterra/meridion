<?php
/*****************************************************************************/
/* Chat.php                                                             */
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

$sendtopicplver="YaBB SE";

function Chat ()
{
	global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;

	$yytitle = "Join a chat";
	template_header();

	$nick = str_replace(" ", "_", $settings[1]);
	$email = $settings[2];
	
	if (!$nick) {
	    $nick = "Guest???";
	}

	if (!$email) {
	  $email = "anon@nowhere.com";
	}

print <<<EOT
<applet archive="jirc_nss.zip" code=Chat.class width=100% height=400 codebase="/bbs/jpilot/">

<param name="CABBASE" value="jirc_mss.cab">


<param name="ServerPort" value="6667">

<param name="ServerName1" value="lucifer.com">

<param name="Channel1" value="virus">
<param name="Channel2" value="scitech">
<param name="Channel4" value="hottub">
<param name="Channel5" value="noobie"> 

<param name="AllowURL" value="true">
<param name="AllowIdentd" value="true">

<param name="WelcomeMessage" value="Welcome to Java IRC chat!">
<param name="RealName" value="$email">
<param name="NickName" value="$nick">
<param name="UserName" value="$email">
<param name="isLimitedServers" value="true">
<param name="isLimitedChannels" value="true">

<param name="MessageCol" value="80">

<param name="BackgroundColor" value="99,132,181">

<param name="TextColor" value="black">
<param name="TextScreenColor" value="white">
<param name="ListTextColor" value="black">

<param name="TextFontName" value="Arial">
<param name="TextFontSize" value="12">

<param name="ConfigNickOnly" value="true">
<param name="NickNChannelOnly" value="true"> 
<param name="LogoBgColor" value="white">
<param name="BorderVsp" value="3">
<param name="DirectStart" value="false">

<param name="FGColor" value="black">

<param name="TitleBackgroundColor" value="black">
<param name="TitleForegroundColor" value="white">


<param name="InputTextColor" value="black">
<param name="InputScreenColor" value="white">
<param name="IgnoreLevel" value="3">

<param name="DisplayConfigRealName" value="false">
<param name="DisplayConfigServer" value="false">
<param name="DisplayConfigPort" value="false">
<param name="DisplayConfigMisc" value="false">


</applet>
EOT;

footer();
obExit();
}
?>
