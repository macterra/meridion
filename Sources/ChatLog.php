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

function DateSelect($mon, $d, $y, $h, $min)
{
  $months = array( 
		  '01' => 'Jan', 
		  '02' => 'Feb',
		  '03' => 'Mar', 
		  '04' => 'Apr',
		  '05' => 'May', 
		  '06' => 'Jun',
		  '07' => 'Jul', 
		  '08' => 'Aug',
		  '09' => 'Sep', 
		  '10' => 'Oct',
		  '11' => 'Nov', 
		  '12' => 'Dec' 
		  );

  $html = "<select name=month>\n";
  foreach($months as $val => $month) {
    if ($mon == $val) {
      $html .= "<option value=$val selected>$month</option>\n";
    }
    else {
      $html .= "<option value=$val>$month</option>\n";
    }
  }
  $html .= "</select>\n";

  $html .= "<select name=day>\n";
  for($i = 1; $i < 32; $i++) {
    $day = sprintf("%02d", $i);
    if ($d == $day) {
      $html .= "<option value=$day selected>$day</option>\n";
    }
    else {
      $html .= "<option value=$day>$day</option>\n";
    }
  }
  $html .= "</select>\n";

  $html .= "<select name=year>\n";
  for($i = 2002; $i < 2011; $i++) {
    if ($i == $y) {
        $html .= "<option value=$i selected>$i</option>\n";
    }
    else {
        $html .= "<option value=$i>$i</option>\n";
    }
  }
  $html .= "</select>\n";

  $html .= "<select name=hour>\n";
  for($i = 0; $i < 25; $i++) {
    $hour = sprintf("%02d", $i);
    if ($h == $hour) {
      $html .= "<option value=$hour selected>$hour</option>\n";
    }
    else {
      $html .= "<option value=$hour>$hour</option>\n";
    }
  }
  $html .= "</select>\n";

  $html .= ":<select name=mins>\n";
  for($i = 0; $i < 60; $i += 15) {
    $mins = sprintf("%02d", $i);
    if ($min == $mins) {
      $html .= "<option value=$mins selected>$mins</option>\n";
    }
    else {
      $html .= "<option value=$mins>$mins</option>\n";
    }
  }
  $html .= "</select>\n";

  return $html;
}

function ChannelOptions()
{
  $sql = "SELECT target FROM irclog GROUP BY target";
  $request = mysql_query($sql);

  while ($row = mysql_fetch_row($request)) {
    $channel = $row[0];
    if ($channel[0] == '#') {
      $opts .= "<option>$row[0]\n";
    }
  }

  return $opts;
}

function ChatLog ()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;

  $yytitle = "Chat Log";
  template_header();

  $today = getdate();
  $month = sprintf("%02d", $today['mon']);
  $day = sprintf("%02d", $today['mday']);
  $year = $today['year'];
  $hour = $today['hours'];
  $mins = $today['minutes'];
  $mins = 15 * (int)($mins/15);
  
  $dateselect = DateSelect($month, $day, $year, $hour, $mins);
  $fdateselect = preg_replace('/name=/', 'name=f_', $dateselect);
  $tdateselect = preg_replace('/name=/', 'name=t_', $dateselect);
  //  $chanopts = ChannelOptions();

  include_once("$sourcedir/allchannels.inc");
  
print <<<EOT
<form action="$cgi;action=chatlog2" method="post">
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]" colspan="2">
        <img src="$imagesdir/email_sm.gif" alt="" border="0">
        <font size=2 class="text1" color="$color[titletext]"><b>View Chat Log</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align="right" valign="top">
        <font size=2><B>Channel</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align="left" valign="middle">
        <select name="channel">
	$channels
	</select>
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align="right" valign="top">
        <font size=2><B>From</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align="left" valign="middle">
        $dateselect
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>Msgs/Page</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <select name=max>
          <option>10</option>
          <option>20</option>
          <option selected>30</option>
          <option>40</option>
          <option>50</option>
          <option>60</option>
        </select>
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <input type="submit" value="Show Log">
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</form>
<form action="$cgi;action=chatlog3" method="post">
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]" colspan="2">
        <img src="$imagesdir/email_sm.gif" alt="" border="0">
        <font size=2 class="text1" color="$color[titletext]"><b>Search Chat Log</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align="right" valign="top">
        <font size=2><B>Channel</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align="left" valign="middle">
        <select name="channel">
	$channels
	</select>
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align="right" valign="top">
        <font size=2><B>From</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align="left" valign="middle">
        $fdateselect
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align="right" valign="top">
        <font size=2><B>To</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align="left" valign="middle">
        $tdateselect
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>Nick</font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <input name=nick size=15> [optional]
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>Msg Text</font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <input name=searchfor size=20> [optional]
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        <font size=2><B>Msgs/Page</B></font>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <input name=max value=30 size=4>
        </td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=top>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle>
        <input type="submit" value="Search Log">
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

function ChatLog2 ()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $channel, $max, $start, $day, $month, $year, $hour, $mins, $date, $time;
	
  $yytitle = "Chat Log";
  template_header();

  if (!$start) {
    $start = 0;
  }
  
  if (!$max) {
    $max = 30;
  }
  
  if ($date) {
    list($year, $month, $day) = split('-', $date);
  }
  
  if ($time) {
    list($hour, $mins) = split(':', $time);
  }
  
  $channel = preg_replace('/%23/', '#', $channel);
  $date = "$year-$month-$day $hour:$mins:00";
  $sql = "SELECT * FROM irclog WHERE target='$channel' AND logged>'$date' ORDER BY id LIMIT $start,$max";
  $request = mysql_query($sql);
  $beg = $start + 1;
  $end = $start + $max;
  $next = $start + $max;
  $prev = $start - $max;
  if ($prev < 0) $prev = 0;
  
  navigators($prev, $next);

  $url = htmlspecialchars("$cgi;action=chatlog2;channel=$channel;date=$year-$month-$day;time=$hour:$mins;start=$start;max=$max");
  $url = preg_replace('/#/', '%23', $url);
  
  print <<<EOT
<table width=100% border=0  align="center" cellspacing=1 cellpadding=2 bgcolor="$color[bordercolor]" class="bordercolor">
EOT;

  $lastnick = "";
  $class = "windowbg";

  $row = mysql_fetch_array($request);
  
  if ($row) {
      $logged = $row['logged'];

#<tr class=windowbg2><td>time</td><td>nick</td><td>message</td></tr>

    print <<<EOT
<tr class=windowbg>
  <th colspan=2>$logged</td>
  <th align=center>$channel from $date (showing messages $beg-$end) Bookmark the <a href="$url">permanent url</a>.</td>
</tr>
EOT;
    
    do {
    
      $logged = $row['logged'];
      $event = $row['event'];
      $source = $row['source'];
      $text = $row['text'];

      list($nick, $ident) = split("!", $source);
      list($date, $time) = split(" ", $logged);
    
      if ($nick != $lastnick) {
	if ($class == "windowbg") {
	  $class = "windowbg2";
	}	
	else {
	  $class = "windowbg";
	}
      }

      $lastnick = $nick;
    
      $text = htmlspecialchars($text);
      $text = preg_replace('/(http:\/\/[^ ]+)/', '<a href="$1">$1</a>', $text);
      $text = preg_replace('/([^ @]+)@([^ ]+)/', '$1@[death to spam].$2', $text);
    
      if ($event == "action") {
	$text = "<font color=yellow>* $text</font>";
      }
    
      if ($event == "join" || $event == "part") {
	$text = "<b>$text</b>";
      }

      if ($event == "topic") {
	$text = "<b>$text</b>";
      }
    
      if ($event == "nick") {
	$text = "<font color=red>$text</font>";
      }
    
      if ($event == "kick") {
	$text = "<b><font color=red>$text</font></b>";
      }
    
      if ($event == "mode") {
	$text = "<i>$text</i>";
      }
    
      if ($event != "action" && $event != "pubmsg") {
	$text = "<p align=right>$text</p>";
      }
      
      print "<tr class=$class><td>$time</td><td>$nick</td><td>$text</td></tr>\n";
    }  while ($row = mysql_fetch_array($request));
  }
  else {
    print "No messages in $channel";
  }
  
  
  navigators($prev, $next);
  footer();
  obExit();
}

function ChatLog3 ()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $channel, $nick, $searchfor, $max, $start, $date, $time, $sql0;
  global $f_day, $f_month, $f_year, $f_hour, $f_mins;
  global $t_day, $t_month, $t_year, $t_hour, $t_mins;
	
  $yytitle = "Chat Log";
  template_header();

  if (!$start) {
    $start = 0;
  }
  
  if (!$max) {
    $max = 30;
  }
  
  if ($date) {
    list($year, $month, $day) = split('-', $date);
  }
  
  if ($time) {
    list($hour, $mins) = split(':', $time);
  }
  
  $channel = preg_replace('/%23/', '#', $channel);
  $fdate = "$f_year-$f_month-$f_day $f_hour:$f_mins:00";
  $tdate = "$t_year-$t_month-$t_day $t_hour:$t_mins:00";
  
  if ($nick) {
    $nicksql = "AND source REGEXP '$nick'";
  }
  
  if ($searchfor) {
    $searchsql = "AND text REGEXP '$searchfor'";
  }
  
  if ($sql0) {
    $sql0 = str_replace('\\', '', $sql0);
  }
  else {
    $sql0 = "SELECT * FROM irclog WHERE target='$channel' AND logged>'$fdate' AND logged<'$tdate' $nicksql $searchsql ORDER BY id";
  }
  
  $sql = $sql0 . " LIMIT $start,$max";

  //  print $sql;

  $request = mysql_query($sql);
  $beg = $start + 1;
  $end = $start + $max;
  $next = $start + $max;
  $prev = $start - $max;
  if ($prev < 0) $prev = 0;
  
  search_navigators($sql0, $prev, $next);

  $url = htmlspecialchars("$cgi;action=chatlog2;channel=$channel;date=$year-$month-$day;time=$hour:$mins;start=$start;max=$max");
  $url = preg_replace('/#/', '%23', $url);
  
  print <<<EOT
<table width=100% border=0  align="center" cellspacing=1 cellpadding=2 bgcolor="$color[bordercolor]" class="bordercolor">
EOT;

  $lastnick = "";
  $class = "windowbg";

  $row = mysql_fetch_array($request);
  
  if ($row) {
      $logged = $row['logged'];

#<tr class=windowbg2><td>time</td><td>nick</td><td>message</td></tr>

    print <<<EOT
<tr class=windowbg>
  <th colspan=2>$logged</td>
  <th align=center>$channel from $date (showing messages $beg-$end).</td>
</tr>
EOT;
    
    do {
    
      $logged = $row['logged'];
      $event = $row['event'];
      $source = $row['source'];
      $text = $row['text'];

      list($nick, $ident) = split("!", $source);
      list($date, $time) = split(" ", $logged);
    
      $url = htmlspecialchars("$cgi;action=chatlog2;channel=$channel;date=$date;time=$time;start=$start;max=$max");
      $url = preg_replace('/#/', '%23', $url);

      if ($nick != $lastnick) {
	if ($class == "windowbg") {
	  $class = "windowbg2";
	}	
	else {
	  $class = "windowbg";
	}
      }

      $lastnick = $nick;
    
      $text = htmlspecialchars($text);
      $text = preg_replace('/(http:\/\/[^ ]+)/', '<a href="$1">$1</a>', $text);
    
      if ($event == "action") {
	$text = "<font color=yellow>* $text</font>";
      }
    
      if ($event == "join" || $event == "part") {
	$text = "<b>$text</b>";
      }

      if ($event == "topic") {
	$text = "<b>$text</b>";
      }
    
      if ($event == "nick") {
	$text = "<font color=red>$text</font>";
      }
    
      if ($event == "kick") {
	$text = "<b><font color=red>$text</font></b>";
      }
    
      if ($event == "mode") {
	$text = "<i>$text</i>";
      }
    
      if ($event != "action" && $event != "pubmsg") {
	$text = "<p align=right>$text</p>";
      }
      
      print "<tr class=$class><td><a href=$url>$time&nbsp;$date</a></td><td>$nick</td><td>$text</td></tr>\n";
    }  while ($row = mysql_fetch_array($request));
  }
  else {
    print "No messages in $channel";
  }
  
  
  search_navigators($sql0, $prev, $next);
  footer();
  obExit();
}

function search_navigators($sql0, $prev, $next)
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $channel, $max, $start, $day, $month, $year, $hour, $mins;

  print <<<EOT
<table>
<tr>
<td>
<form action="$cgi;action=chatlog3" method=post>
<input type=hidden name=sql0 value="$sql0">
<input type=hidden name=start value=0>
<input type=hidden name=max value=$max>
<input type=submit value="Start">
</form>
</td>
<td>
<form action="$cgi;action=chatlog3" method=post>
<input type=hidden name=sql0 value="$sql0">
<input type=hidden name=start value=$prev>
<input type=hidden name=max value=$max>
<input type=submit value="Prev">
</form>
</td>
<td>
<form action="$cgi;action=chatlog3" method=post>
<input type=hidden name=sql0 value="$sql0">
<input type=hidden name=start value=$next>
<input type=hidden name=max value=$max>
<input type=submit value="Next">
</form>
</td>
<td>
<form action="$cgi;action=chatlog" method=post>
<input type=submit value="New Search">
</form>
</td>
</tr>
</table>
EOT;
}

function navigators($prev, $next)
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $channel, $max, $start, $day, $month, $year, $hour, $mins;

  $dateselect = DateSelect($month, $day, $year, $hour, $mins);

  print <<<EOT
<table>
<tr>
<td>
<form action="$cgi;action=chatlog2" method=post>
<input type=hidden name=channel value=$channel>
<input type=hidden name=start value=0>
<input type=hidden name=day value=$day>
<input type=hidden name=month value=$month>
<input type=hidden name=year value=$year>
<input type=hidden name=hour value=$hour>
<input type=hidden name=max value=$max>
<input type=submit value="Start">
</form>
</td>
<td>
<form action="$cgi;action=chatlog2" method=post>
<input type=hidden name=channel value=$channel>
<input type=hidden name=start value=$prev>
<input type=hidden name=day value=$day>
<input type=hidden name=month value=$month>
<input type=hidden name=year value=$year>
<input type=hidden name=hour value=$hour>
<input type=hidden name=max value=$max>
<input type=submit value="Prev">
</form>
</td>
<td>
<form action="$cgi;action=chatlog2" method=post>
<input type=hidden name=channel value=$channel>
<input type=hidden name=start value=$next>
<input type=hidden name=day value=$day>
<input type=hidden name=month value=$month>
<input type=hidden name=year value=$year>
<input type=hidden name=hour value=$hour>
<input type=hidden name=max value=$max>
<input type=submit value="Next">
</form>
</td>
<td>
<form action="$cgi;action=chatlog" method=post>
<input type=submit value="Search">
</form>
</td>
<td>
<form action="$cgi;action=chatlog2" method=post>
Start From: $dateselect
Msgs/Page:
<select name=max>
  <option>10</option>
  <option>20</option>
  <option selected>30</option>
  <option>40</option>
  <option>50</option>
  <option>60</option>
</select>
<input type=hidden name=channel value=$channel>
<input type=submit value="Go">
</form>
</td>
</tr>
</table>
EOT;
}

?>






