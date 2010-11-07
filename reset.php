#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

$query = "SELECT * FROM {$db_prefix}ratings";
$result = mysql_query($query);

while ($row = mysql_fetch_array($result)) {
  extract($row);
  //  print "$id_member $id_other $rating\n";
  //  $sql = "INSERT INTO {$db_prefix}ratings (id_member,id_other,rating) VALUES ($id_other,$id_other,4)";
  $sql = "INSERT INTO {$db_prefix}reputation (id_member,reputation) VALUES ($id_other,4)";
  $res = mysql_query($sql);
  print "$sql\n";
}

echo "Done.\n";
?>
