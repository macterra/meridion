<?php
ob_start();
/* last updated by Jeff 11:18 Nov 27 */
/*****************************************************************************/
/* Reminder.php                                                              */
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
include_once ("$sourcedir/Load.php");
include_once ("$sourcedir/Subs.php");
include_once ("$sourcedir/Errors.php");
include_once ("$sourcedir/Security.php");
$dbcon = mysql_connect($db_server, $db_user, $db_passwd);
mysql_select_db($db_name);

/* ### Log this click ### */
ClickLog();

$reminderplver = "1 Gold - PHP/MySQL Flavour";

$username = 'Guest';

/* ### Banning ### */
banning();

/* ### Write log ### */
WriteLog();

/* ############################################################################### */

$yytitle="$mbname $txt[669]";
template_header();
if (stristr ($QUERY_STRING, "input_user")) {
	input();
	footer();
	obExit();
}

$searchField = ($searchtype=='usearch') ? 'memberName' : 'emailAddress';

$user = urldecode($user);	// deal with special chars in the name

if ($user == '')
	no_user_error();

$request = mysql_query ("SELECT realName,memberName,emailAddress,memberGroup,secretQuestion,secretAnswer FROM {$db_prefix}members WHERE $searchField='$user' LIMIT 1");

$row = mysql_fetch_array($request);
$name = trim($row['realName']);
$email = trim($row['emailAddress']);
$memberName = trim($row['memberName']);

if ($email == '')
	mailprog_error();
$status = trim($row['memberGroup']);

// deal with the secret question stuff if applicable
if (isset($useSecret))		// if the checkbox was set
{
	// Now verify they have an secret question established - error out if they don't
	if (trim($row['secretQuestion']) == '' || $row['secretAnswer'] == '')
		fatal_error($txt['pswd5']);

	// ask for the secret answer input
	secretAnswerInput();

	// draw the footer and exit;
	footer();
	obExit();
}

$password = "";
if (isset($secretAnswer))
{
	$passwrd1 = stripslashes(urldecode($passwrd1));
	$passwrd2 = stripslashes(urldecode($passwrd2));
	$secretAnswer = stripslashes(urldecode($secretAnswer));
	$user = stripslashes(urldecode($user));

	// check if the secret answer is correct
	if ($secretAnswer != $row['secretAnswer'])
		fatal_error($txt['pswd7']);
	// it's ok - continue

	// check if the passwords are the same
	if ($passwrd1 != $passwrd2)
		fatal_error($txt[213]);

	$password = $passwrd1;
}
else
{
	// randomly generate a new password
	srand(time());
	$password = crypt(mt_rand(-100000,100000));

	// remove all non alpha-numeric characters
	$password = preg_replace("/\W/","",$password);

	// limit it to 10 characters max
	$password = substr($password,0,10);
}

$cryptpassword = crypt($password,substr($password,0,2));

$searchField = ($searchtype=="usearch") ? 'memberName' : 'emailAddress';

$request = mysql_query ("UPDATE {$db_prefix}members SET passwd='$cryptpassword' WHERE $searchField='$user'");

$messagetext = '';
if (!isset($secretAnswer))
{
	$subject = "$txt[36] $mbname : $name";
	sendmail($email, $subject, "$txt[711] $name,\n\n$mbname ==>\n\n$txt[35]: $memberName\n$txt[36]: $password\n$txt[87]: $status\n\n$txt[130]",$webmaster_email);
	$messagetext = "<b>$txt[192]: $user</b>";
}
else
{
	$messagetext = "$txt[pswd8]";
}

print <<<EOT
<BR><BR><table border=0 width=400 cellspacing=1 bgcolor="$color[bgcolor]" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    <font size=2 class="text1" color="$color[titletext]"><b>$mbname $txt[36] $txt[194]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <table border=0 align="center">
      <tr>
        <td align="center"><font size="2">$messagetext</font></td>
      </tr>
    </table>
    </td>
  </tr>
</table>
<br><center><a href="javascript:history.back(-2)">$txt[193]</a></center><br>
EOT;
footer();
	obExit();

/* ############################################################################### */

function input () {
global $color,$reminderurl,$txt,$mbname;
print <<<EOT
<BR><BR><table border=0 width=400 cellspacing=1 bgcolor="$color[bordercolor]" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    <font size=2 class="text1" color="$color[titletext]"><b>$mbname $txt[36] $txt[194]</b></b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <form action="$reminderurl">
    <table border=0 align="center">
      <tr>
        <td><font size="2">$txt[yse100]: <input type="text" name="user">
        <input type="submit" value="$txt[339]"></font></td>
      </tr>
      <tr>
        <td align=center><font size="2">$txt[yse101] <input type="radio" value="usearch" checked name="searchtype"> $txt[yse102] <input type="radio" name="searchtype" value="esearch">
        </font></td>
      </tr>
	  <tr>
	    <td align=center>$txt[pswd3]: <input type=checkbox name=useSecret></font></td>
	  </tr>
	  <tr>
	    <td align=center><font size=1>$txt[pswd4]</font></td>
	  </tr>
    </table>
    </form>
    </td>
  </tr>
</table>
EOT;
}

function secretAnswerInput()
{
	global $user, $row, $searchtype, $txt, $reminderurl, $color, $mbname;
print <<<EOT
<BR><BR><table border=0 width=400 cellspacing=1 bgcolor="$color[bordercolor]" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]">
    <font size=2 class="text1" color="$color[titletext]"><b>$mbname $txt[36] $txt[194]</b></b></font></td>
  </tr><tr>
 	<tr><td class=quote colspan=2><font class=quote>$txt[pswd6]</font></td></tr>
   <td class="windowbg" bgcolor="$color[windowbg]">
    <form action="$reminderurl" method=post>
    <table border=0 align="center">
	  <tr><td width="45%"><font size=2><b>$txt[pswd1]:</b> </td><td>$row[secretQuestion]</font></td></tr>
	  <tr>
        <td width="45%"><font size="2"><b>$txt[pswd2]:</b> </td><td><input type="text" name="secretAnswer"></td>
	  </tr>
	<tr>
	<td width="45%"><font size=2><b>$txt[81]: </b></font><BR>
	<font size=1>$txt[596]</font></td>
	<td><input type="password" name="passwrd1" size="20"></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[82]: </b></font></td>
	<td><input type="password" name="passwrd2" size="20"></td>
      </tr><tr>
	<tr><td colspan=2>
        <input type="submit"></font><input type=hidden name='searchtype' value='$searchtype'><input type=hidden name='user' value='$user'></td>
      </tr>
    </table>
    </form>
    </td>
  </tr>
</table>
EOT;
}

function mailprog_error ()
{
	global $txt,$webmaster_email;
	print "<br><center><b>$txt[394]<br>$txt[395] <a href=\"mailto:$webmaster_email\">Webmaster</a> $txt[396].</b></center>\n";
	print "<br><a href=\"javascript:history.back(-1)\">Back</a><br>\n";
	footer();
	obExit();

}

function no_user_error ()
{
	global $txt;
	print "<br><center><b>$txt[40]</b></center>\n";
	print "<br><a href=\"javascript:history.back(-1)\">$txt[193]</a><br>\n";
	footer();
	obExit();
}
?>
