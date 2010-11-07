#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

$sql = "delete FROM `cov_members` where lastLogin is NULL and memberName=realName and memberName!=emailAddress";

$request = mysql_query($sql);

print "$request\n";

?>
