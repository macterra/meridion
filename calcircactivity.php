#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

function IrcWindow($month) {
  global $nicks, $actlevel;

  $min = $month*30;
  $max = ($month+1)*30;

  $sql = "SELECT count(*), lcase(left(source, instr(source, '!')-1)) as nick FROM irclog WHERE DATE_SUB(CURDATE(),INTERVAL $min DAY) > logged and DATE_SUB(CURDATE(),INTERVAL $max DAY) <= logged and target='#virus' and (event='pubmsg' OR event='action') group by nick";

  $request = mysql_query($sql);

  while ($row = mysql_fetch_row($request)) {
    $lines = $row[0];
    $nick = $row[1];

    $nicks[$nick] = $nick;
    $actlevel[$nick][$month] = $lines;
  }
}

$decay = 0.707;
$window = 24;

for ($i = 0; $i < $window; ++$i) {
  IrcWindow($i);
}

foreach($nicks as $nick) {
  $level = 0;
  for ($i = $window; $i > 0; --$i) {
    $lines = $actlevel[$nick][$i-1];
    if (!isset($lines)) $lines = 0;
    $level = $lines + ($decay * $level);
    $history[$nick][] = $lines;
  }

  $ircactivity[$nick] = $level+1;
}

$res = 0;

while (list($nick, $lines) = each($ircactivity)) {
  $his = serialize($history[$nick]);
  $sql = "REPLACE INTO cov_mdn_irc_activity (nick, lcount,history) VALUES ('$nick', $lines, '$his')";
  $res += mysql_query($sql);
}

echo "$res records updated in cov_mdn_irc_activity\n";
?>
