#!/usr/bin/php -q
<?php

$email[1] = "david";
$email[2] = "tracy";
$actlevel[1][30] = 666;
$actlevel[2][0] = 123;

foreach($email as $id => $addr) {
  echo "$addr --> ";
  for ($i = 0; $i < 100; $i += 30) {
    echo $actlevel[$id][$i], ", ";
  }
  echo "\n";
}

?>
