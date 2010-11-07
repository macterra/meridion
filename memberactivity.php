#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

function TimeAgo($stamp) {
  $secs = time()-$stamp;

  if ($secs < 60) {
    return sprintf("%ds", $secs);
  }

  $mins = $secs/60;

  if ($mins < 60) {
    return sprintf("%dm", $mins);
  }

  $hours = $mins/60;

  if ($hours < 24) {
    return sprintf("%dh", $hours);
  }

  $days = $hours/24;

  if ($days < 365.25) {
    return sprintf("%dd", $days);
  }

  return sprintf("%dy %dd", $days/365.25, $days%365.25);
}

$DEBUG = true;

$members = getMembers();

foreach($members as $member) {
  $regdate = strftime("%d %b %Y", $member->dateRegistered);
  $diff = time()-$member->dateRegistered;
  $ago = TimeAgo($member->dateRegistered);
  echo "$member->realName $regdate $diff $ago ago\n";
}

echo "Done.\n";
?>
