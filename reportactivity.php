#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

$sql = "SELECT * FROM cov_mdn_activity WHERE id_member=1";
$request = mysql_query($sql);
$row = mysql_fetch_assoc($request);

$mcount = $row['mcount'];
$history = unserialize($row['history']);

echo "$mcount $history\n";

$sql = "SELECT a.* FROM cov_mdn_irc_activity a, cov_irc_nicks n where a.nick=n.nick and id_member=1";
//$sql = "SELECT * FROM cov_mdn_irc_activity WHERE nick='lucifer'";
$request = mysql_query($sql);

while ($row = mysql_fetch_assoc($request)) {
  $nick[] = $row['nick'];
  $lcount[] = $row['lcount'];
  $irchistory[] = unserialize($row['history']);
  echo "{$row['nick']}\n";
}

$discount = 1.0;
$msum = 0.0;
$lsum = 0.0;

$decay = 0.707;
$window = 24;

for ($i = $window; $i > 0; --$i)
{
  $msgs = $history[$i-1];
  $msgcon = $discount * $msgs;
  $msum += $msgcon;

  printf("%2d %4d %5.2f ", $window-$i+1, $msgs, $msgcon);

  $lines = 0;
  for($j = 0; $j < count($irchistory); $j++) {
    $lines += $irchistory[$j][$i-1];
  }

  $linecon = $discount * $lines;
  $lsum += $linecon;
  printf("%6d %5.2f \n", $lines, $linecon);

  $discount *= $decay;
}

$activity = log(1 + $msum + $lsum/100)/log(2);

printf("message level  = %5.2f\n", $msum);
printf("IRC level      = %5.2f\n", $lsum);
printf("activity level = %5.2f\n", $activity);

?>
