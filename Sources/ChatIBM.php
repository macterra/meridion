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

function Chat()
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
<center>
<applet code=room.IRC.class archive=irc.jar width=400 height=200 codebase="/bbs/irc">
<!codebase="http"//venus.bocaraton.ibm.com/irc1_50/">
        <!frameName: the frame you want to display URLs>
        <param name=frameName value="URLs">
        <!server is your IRC server>
        <param name=server value="lucifer.com">
        <!port is your IRC server's port>
        <param name=port value="6667">
        <!allows you to connect to server outside a socks firewall with a relay server>
        <!param name=externalIRCServer value="yes">
        <!relayhost is always your web server where users download this applet>
        <!param name=relayhost value="venus.bocaraton.ibm.com">
        <!relayport defaults to 9999>
        <!use relayports if relay server runs on multiple ports>
        <!param name=relayport value="9999">
        <param name=nickname value="$nick">
        <param name=realname value="$settings[1]">
        <param name=email value="$email">
        <!param name=adURL value="ads">
        <!param name=adPollURL value="ads">
        <!adPollFrequency is counted in minutes>
        <!param name=adPollFrequency value=30>
        <param name=shortcutActionURL value=gif/shortcut">
        <param name=startButton value="Join Chat">
        <param name=bgcolor value="140,165,198">
        <!hostMessageColor set the text color for all messages from hosts>
        <param name=hostMessageColor value="0,0,0">
        <param name=activityLog value="yes">
        <!param name=startFrame value="yes">
        <param name=room0 value="#virus#pass#P#">
        <!tickerMode: valid values are: "Scroll", "Stop" and "Hide", case sensitive>
        <param name=tickerMode value="Hide">
        <!param name=initAd0 value="IBM Home Page\\http://www.ibm.com">
        <!serverPassword: if server requires authentication before connection>
        <!param name=serverPassword value="password">
        <param name=admin value="admin@lucifer.com">
</applet>
</center>
EOT;

footer();
obExit();
}
?>
