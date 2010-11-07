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

function TopChatters()
{
  $sql = "SELECT lcase(left(source, instr(source, '!')-1)) as nick, count(*) as c, left(max(logged), 16) as last FROM `irclog` group by nick order by c desc LIMIT 20";
  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $nick = $row[0];
    $count = $row[1];
    $last = $row[2];
    
    $n++;
    
    if ($n % 2 == 1) {
      $chatters .= "<tr class=windowbg>";
    }
    else {
      $chatters .= "<tr class=windowbg2>";
    }
    
    $chatters .= "<td align=right>$n</td><td>$nick</td><td align=right>$count&nbsp;&nbsp;</td><td>$last</td></tr>\n";
  }

  return $chatters;
}

function ActiveChannels()
{
  $sql = "SELECT target, count(*) as c from irclog where UNIX_TIMESTAMP() - UNIX_TIMESTAMP(logged) < 600 group by target order by c desc";
  
  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $channel = $row[0];
    $count = $row[1];
    
    $n++;
    
    if ($n % 2 == 1) {
      $channels .= "<tr class=windowbg>";
    }
    else {
      $channels .= "<tr class=windowbg2>";
    }
    
    $channels .= "<td align=right>$n</td><td>$channel</td><td align=right>$count</td></tr>\n";
  }

  return $channels;
}

function Chat ()
{
	global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;

	$yytitle = "Join a chat";
	template_header();

	$nick = str_replace(" ", "_", $settings[1]);
	$chatters = TopChatters();
	$channels = ActiveChannels();
	
print <<<EOT
<form action="$cgi;action=chat2" method="post">
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]" colspan="2">
        <img src="$imagesdir/email_sm.gif" alt="" border="0">
        <font size=2 class="text1" color="$color[titletext]"><b>$yytitle</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>Your nickname</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <input type="text" name="y_name" size="20" maxlength="40" value="$nick">
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>$txt[716]</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <input type="text" name="y_email" size="20" maxlength="40" value="$settings[2]">
        </td>
      </tr></tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align="right" valign="top">
        <font size=2><B>Channel</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align="left" valign="middle">
        <select name="channel">
	<option>#virus
	<option>#operhelp
	</select>
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <input type="submit" value="Join Chat">
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</form>
<p>
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Chat Tips</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
If you already use an IRC client, point it at irc.lucifer.com port 6667.
<p>
Useful links:
<ul>
<li> View <a href="$cgi;action=chatlog">chat logs</a>.
<li> Hermit's <a href="irc.html">Acronyms</a> page.
<li> The IRC <a href="http://virus.lucifer.com/bbs/index.php?board=31;action=display;threadid=11574">FAQ Collection</a> thread on the BBS.
</li>
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>

<p>

<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Top 20 Chatters</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<table>
<tr><td>&nbsp;</td><td><b><u>nick</u></b></td><td align=center><b><u>lines</u></b></td><td align=center><b><u>last on</u></b></td></tr>
$chatters
</table>
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>

<p>

<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Active Channels</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<table>
<tr><td>&nbsp;</td><td><b><u>channel</u></b></td><td align=center><b><u>activity (last 10 mins)</u></b></td></tr>
$channels
</table>
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>

EOT;

footer();
	obExit();
}

function Chat2 (){
	global $threadid,$board,$y_name,$y_email,$channel,$yySetLocation,$cgi,$txt,$mbname,$db_prefix,$yytitle;

	$yytitle = "Chatting";
	template_header();

	if (!$y_name) {
	    $y_name = "Guest???";
	}

	if (!$y_email) {
	  $y_email = "anon@nowhere.com";
	}

print <<<EOT
  <applet code="EIRC" archive="EIRC.jar,EIRC-gfx.jar" width="100%" height="400" codebase="http://virus.lucifer.com/bbs/chat/">
   <!--param name="cabinets" value="EIRC.cab,EIRC-gfx.cab" /-->
   <param name="server" value="virus.lucifer.com" />
   <param name="port" value="6667" />
   <!--param name="mainbg" value="#424242" /-->
   <param name="mainbg" value="#C0C0C0" />
   <param name="mainfg" value="#000000" />
   <param name="textbg" value="#FFFFFF" />
   <param name="textfg" value="#000000" />
   <param name="selbg" value="#00007F" />
   <param name="selfg" value="#FFFFFF" />
   <param name="channel" value="$channel" />
   <param name="titleExtra" value=" - EIRC" />
   <param name="username" value="$y_email" />
   <param name="realname" value="$y_email" />
   <param name="nickname" value="$y_name" />
   <param name="password" value="intermix" />
   <!--param name="servPassword" value="intermix" /-->
   <!--param name="servEmail" value="" /-->
   <param name="login" value="1" />
   <!--param name="spawn_frame" value="1" /-->
   <!--param name="frame_width" value="600" /-->
   <!--param name="frame_height" value="400" /-->
   <!--param name="language" value="en" /-->
   <!--param name="country" value="US" /-->

   <h1>Eteria IRC Client</h1>
   <p>
    Sorry, but you need a Java 1.1.x enabled browser to use EIRC.</p>
  </applet>
EOT;

footer();
obExit();
}
?>
