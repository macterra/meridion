<?php
/*****************************************************************************/
/* LogInOut.php                                                              */
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

$loginoutplver="YaBB SE 1.3.0";

function Login (){
	global $txt,$yytitle,$cgi,$action,$color,$imagesdir,$reminderurl,$Cookie_Length;
	global $reminderurl;
	$yytitle = "$txt[34]";
	template_header();
	print <<<EOT
<BR><BR>
<form name="frmLogin" action="$cgi;action=login2" method="POST">
<table border="0" width="400" cellspacing="1" cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="windowbg" bgcolor="$color[windowbg]" width="100%">
    <table width="100%" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]" colspan="2">
        <img src="$imagesdir/login_sm.gif" alt="">
        <font size=2 class="text1" color="$color[titletext]"><b>$txt[34]</b></font></td>
      </tr><tr>
        <td align="right" class="windowbg" bgcolor="$color[windowbg]"><font size=2><b>$txt[35]:</b></font></td>
        <td class="windowbg" bgcolor="$color[windowbg]"><font size=2><input type=text name="user" size=20></font></td>
      </tr><tr>
        <td align="right" class="windowbg" bgcolor="$color[windowbg]"><font size=2><b>$txt[36]:</b></font></td>
        <td class="windowbg" bgcolor="$color[windowbg]"><font size=2><input type=password name="passwrd" size=20></font></td>
      </tr><tr>
        <td align="right" class="windowbg" bgcolor="$color[windowbg]"><font size=2><b>$txt[497]:</b></font></td>
        <td class="windowbg" bgcolor="$color[windowbg]"><font size=2><input type=text name="cookielength" size=4 maxlength="4" value="$Cookie_Length"></font></td>
      </tr><tr>
        <td align="right" class="windowbg" bgcolor="$color[windowbg]"><font size=2><b>$txt[508]:</b></font></td>
        <td class="windowbg" bgcolor="$color[windowbg]"><font size=2><input type=checkbox name="cookieneverexp"></font></td>
      </tr><tr>
        <td align=center colspan=2 class="windowbg" bgcolor="$color[windowbg]"><BR><input type=submit value="$txt[34]"></td>
      </tr><tr>
        <td align=center colspan=2 class="windowbg" bgcolor="$color[windowbg]"><a href="$reminderurl?action=input_user"><small>$txt[315]</small></a><BR><BR></td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</form>

<script language="JavaScript"><!--
	document.frmLogin.user.focus();
//--></script>

EOT;
	footer();
	obExit();
}

function Login2() {
	global $txt,$settings,$username,$cookielength,$Cookie_Length,$user,$passwrd,$cookieneverexp,$db_prefix;
	global $pwseed,$cookieusername,$cookiepassword,$password,$yySetCookies,$REMOTE_ADDR;
	if($user == ""){ fatal_error("$txt[37] - $passwrd"); }
	if($passwrd == "") { fatal_error("$txt[38]"); }
	if(!preg_match("/^[\s0-9A-Za-z#%+,-\.:=?@^_הציטצüחודגבא‗ךלםמןנסעףפץרשתû‎ÿ]+$/",$user)) {fatal_error("$txt[240]"); }
    if ($cookielength==$txt['yse50']) { $cookielength = 1;$cookieneverexp='on'; }

	if (!is_numeric($cookielength)) {fatal_error(" $cookieLength $txt[337]"); }

	$request = mysql_query("SELECT passwd,realName,emailAddress,websiteTitle,websiteUrl,signature,posts,memberGroup,ICQ,AIM,YIM,gender,personalText,avatar,dateRegistered,location,birthdate,timeFormat,timeOffset,hideEmail FROM {$db_prefix}members WHERE memberName='$user'");
    $attempt = $passwrd;
	$passwrd = crypt($passwrd,substr($passwrd,0,2));
	if (mysql_num_rows($request) == 0)
		fatal_error("$txt[40] - $user: $attempt");
	else
	{
		$settings = mysql_fetch_row($request);
		if ($settings[0] != $passwrd)
			fatal_error("$txt[39] - $user: $attempt");
		else
			$username = $user;
	}

	if ($cookielength < 1 || $cookielength > 504000)
		$cookielength = $Cookie_Length;

	if(!isset($cookieneverexp) || $cookieneverexp == '')
		$Cookie_Length = $cookielength;
	else
		$Cookie_Length = 504000;	// about 1 year

	$password = crypt($passwrd,$pwseed);

	// no longer reversed ordered dbm
	setCookie ("expiretime",time()+(60*$Cookie_Length),time()+30240000,"/");
	setCookie ($cookiepassword,$password, time()+30240000,"/");
	setCookie ($cookieusername,$username, time()+30240000,"/");
    $lastLog =time();
    $memIP = $REMOTE_ADDR;
    $result =  mysql_query("UPDATE {$db_prefix}members SET lastLogin='$lastLog',memberIP='$memIP' WHERE memberName='$user'");

	LoadUserSettings();
    $request = mysql_query ("DELETE FROM {$db_prefix}log_online WHERE identity='$REMOTE_ADDR'");

	WriteLog();
	redirectinternal();
}

function Logout (){
	global $username,$password,$cookieusername,$cookiepassword,$settings,$realname,$realemail;
	global $HTTP_COOKIE_VARS,$yySetCookies,$maintenance,$db_prefix,$sourcedir;
	# Write log
	$request = mysql_query("DELETE FROM {$db_prefix}log_online WHERE identity='$username'");
	// no longer reverse ordered dbm
	setCookie($cookieusername,'',time()-3600,"/");
	setCookie($cookieusername);
	setCookie($cookiepassword,'',time()-3600,"/");
	setCookie($cookiepassword);
	setCookie('expiretime','-1');
	$username = 'Guest';
	$password = '';
	$settings = array(7=>'');
	$realname = '';
	$realemail = '';
	$HTTP_COOKIE_VARS = array();

	banning();
	if ($maintenance == 1 && $settings[7] != 'Administrator') { include_once ("$sourcedir/Maintenance.php"); InMaintenance(); }

	redirectinternal();
}
?>
