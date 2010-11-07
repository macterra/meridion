#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

$sql = "SELECT u.realName, count(*) as c FROM `cov_messages` m, cov_members u where m.id_member>0 and m.id_member=u.id_member and (UNIX_TIMESTAMP()-posterTime) < (365*24*60*60) group by posterName order by c desc";

$request = mysql_query($sql);

while ($row = mysql_fetch_row($request)) {
  $name = $row[0];
  $count = sprintf("%4d", $row[1]);

  echo "$count $name\n";
}

?>
