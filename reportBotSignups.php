#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

$sql = "SELECT realName, emailAddress, FROM_UNIXTIME(dateRegistered) FROM `cov_members` where lastLogin is NULL and memberName=realName and memberName!=emailAddress order by id_member";

$request = mysql_query($sql);

$i = 0;

while ($row = mysql_fetch_row($request)) {
  $i++;
  $name = $row[0];
  $addr = $row[1];
  $date = $row[2];

  echo "$i $date $name $addr\n";
}

?>
