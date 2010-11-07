#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

$DEBUG = true;

if ($argc != 3) {
  echo "usage: $argv[0] newAddr oldAddr\n";
  exit();
}

$newAddr = $argv[1];
$oldAddr = $argv[2];

if ($newAddr == $oldAddr) {
  echo "Addresses are identical\n";
  exit();
}

$members = getMembers();
$newMember = $members[$newAddr];

if (!$newMember) {
  echo "Can't find member for <$newAddr>\n";
  exit();
}
else {
  $new = $newMember->dump();
  $newID = $newMember->id;
  $newName = $newMember->memberName;
  $newMail = $newMember->emailAddress;
}

echo "$newName will claim all posts from $oldAddr.\n";

$update = "UPDATE {$db_prefix}messages SET ID_MEMBER=$newID,posterName='$newName' WHERE posterEmail='$oldAddr'";
$result = mysql_query($update);
if ($DEBUG) {
	echo ($result ? "SUCCEEDED" : "FAILED") . ": $update\n";
}

$rows = mysql_affected_rows();
echo "$rows records were changed\n";

echo "Done.\n";
?>
