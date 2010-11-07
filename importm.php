#!/usr/bin/php -q
<?php

include_once("Console/Getopt.php");
include_once("importf.php"); // import functions
include_once("importc.php"); // configuration

$DEBUG = true;

$board = $savedBoard;
$timezone = $savedTZ;

$opts = new Console_Getopt();
$arr = $opts->getopt($argv, "-b: -t:");

foreach($arr[0] as $option) {
  switch ($option[0]) {
  case "b":
    $board = $option[1];
    break;

  case "t":
    $timezone = $option[1];
    break;
  }
}

importArchive("php://stdin");

?>
