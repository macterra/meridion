#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

function AllActivity()
{
  global $db_prefix;
  
  $request = mysql_query("SELECT * FROM {$db_prefix}mdn_activity");

  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $level = $row[1];
    $msgActivity[$id] = $level;
    $ids[$id] = $id;
    echo "msg $id $level\n";
  }

  $sql = "SELECT id_member, sum(lcount) as irclines FROM cov_mdn_irc_activity a, cov_irc_nicks n where a.nick=n.nick group by id_member";
  $request = mysql_query($sql);

  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $lines = $row[1];
    $ircActivity[$id] = $lines;
    $ids[$id] = $id;
    echo "irc $id $lines\n";
  }

  foreach($ids as $id) {
    $act = log(1 + $msgActivity[$id])/log(2);
    $activity[$id] = $act;
    echo "act $id $act\n";
  }

  return $activity;
}


$a = AllActivity();

?>
