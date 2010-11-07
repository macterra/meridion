<?php
/*****************************************************************************/
/* ICQPager.php                                                              */
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

$icqpagerplver="YaBB SE 1.3.0";

function IcqPager()
{
	global $UIN,$yytitle,$color,$txt,$mbname,$cgi,$realname,$realemail;
	$uin = $UIN;

	$name = isset($realname)?"<input type=hidden name=\"from\" value=\"$realname\">$realname":"<input type=text name=\"from\" size=20 maxlength=40>";
	$email = isset($realemail)?"<input type=hidden name=\"fromemail\" value=\"$realemail\">$realemail":"<input type=text name=\"fromemail\" size=20 maxlength=40>";

	$yytitle = "$txt[513] $txt[514]";
	template_header();

print <<<EOT
<form action="$cgi&action=sendICQpage" method="post">
<table border=0  width="600" align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]" colspan="2">
        <font size=2 class="text1" color="$color[titletext]"><b>$yytitle</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>$txt[324]:</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <font size="2">$uin</font> <img src="http://wwp.icq.com/scripts/online.dll?icq=$uin&img=5" alt="$uin" border="0">
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>$txt[335]:</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        $name
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align="right" valign="top">
        <font size=2><B>$txt[336]:</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align="left" valign="middle">
        $email
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>$txt[72]:</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <textarea name="body" rows="8" cols="45" wrap="Virtual"></textarea>
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=center valign=middle colspan=2>
        <input type="hidden" name="subject" value="$mbname">
	<input type="hidden" name="to" value="$UIN">
        <input type="submit" name="Send" value="$txt[339]">
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</form>
EOT;

	footer();
	obExit();
}

function send_icqpage ()
{
	global $from,$fromemail,$to,$body,$username,$yytitle,$txt;
	//mail("$to@pager.icq.com",'',"$body");
    $to=$to."@pager.icq.com";
    $from=$fromemail;
	$status= sendmail ($to,"ICQ Page from SE",$body,$from);

	$yytitle = "$txt[513] $txt[514]";
	template_header();
    print "Sent to ".$to." from ".$from." and a status of ".$status."<BR>";
	print "<br><a href=\"javascript:window.close();\">$txt[51]</a><br>";
	footer();
	obExit();
}
?>
