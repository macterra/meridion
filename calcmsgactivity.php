#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

function MessageWindow($month) {
  global $email, $actlevel;

  $min = $month*30;
  $max = ($month+1)*30;

  $sql = "SELECT id_member, posterEmail, count(*) as activity FROM cov_messages where (UNIX_TIMESTAMP()-posterTime) < ($max*24*60*60) and (UNIX_TIMESTAMP()-posterTime) > ($min*24*60*60) and id_member>=1 group by id_member order by activity DESC";

  $request = mysql_query($sql);

  while ($row = mysql_fetch_assoc($request)) {
    $idMember = $row['id_member'];
    $posterEmail = $row['posterEmail'];
    $activity = $row['activity'];

    $email[$idMember] = $posterEmail;
    $actlevel[$idMember][$month] = $activity;
    //  echo "$idMember $posterEmail $activity\n";
  }
}

$decay = 0.707;
$window = 24;

for ($i = 0; $i < $window; ++$i) {
  MessageWindow($i);
}

foreach($email as $id => $addr) {
  $level = 0;
  $msgarr = "";
  for ($i = $window; $i > 0; --$i) {
    $messages = $actlevel[$id][$i-1];
    if (!isset($messages)) $messages = 0;
    $level = $messages + ($decay * $level);
    $history[$id][] = $messages;
  }
  $msgactivity[$id] = $level;
}

$res = 0;

while (list($id, $level) = each($msgactivity)) {
  $his = serialize($history[$id]);
  $sql = "REPLACE INTO cov_mdn_activity (id_member, mcount, history) VALUES ($id, $level, '$his')";
  $res += mysql_query($sql);
}

echo "$res records updated\n";

?>
