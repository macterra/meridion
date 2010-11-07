#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

$query = "SELECT count(*) as count, ID_MEMBER FROM {$db_prefix}messages WHERE 1 group by ID_MEMBER";
$result = mysql_query($query);

while ($row = mysql_fetch_array($result)) {
  extract($row);
  $update = "UPDATE {$db_prefix}members SET posts=$count WHERE ID_MEMBER=$ID_MEMBER";
  $res = mysql_query($update);
  echo ($res) ? "SUCCESS" : "FAIL";
  echo ": Update $ID_MEMBER with $count posts\n";
}

echo "Done.\n";
?>
