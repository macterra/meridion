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

function Chatters ()
{
	global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;

	$yytitle = "Top Chatters";
	template_header();

	$nick = str_replace(" ", "_", $settings[1]);
	$chatters = TopChatters();
	
print <<<EOT

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

EOT;

footer();
	obExit();
}

?>
