#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

$DEBUG = true;

if ($argc != 3) {
  echo "usage: $argv[0] mainAddr otherAddr\n";
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
$oldMember = $members[$oldAddr];

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

if (!$oldMember) {
  echo "Can't find member for <$oldAddr>\n";
  exit();
}
else {
  $old = $oldMember->dump();
  $oldID = $oldMember->id;
}

echo "$new will claim all posts from $old.\n";

$update = "UPDATE {$db_prefix}messages SET ID_MEMBER=$newID,posterName='$newName',posterEmail='$newMail' WHERE (ID_MEMBER=$oldID)";
$result = mysql_query($update);
if ($DEBUG) {
	echo ($result ? "SUCCEEDED" : "FAILED") . ": $update\n";
}

$update = "UPDATE {$db_prefix}instant_messages SET ID_MEMBER_FROM=$newID,fromName='$newName' WHERE (ID_MEMBER_FROM=$oldID)";
$result = mysql_query($update);
if ($DEBUG) {
	echo ($result ? "SUCCEEDED" : "FAILED") . ": $update\n";
}

$update = "UPDATE {$db_prefix}instant_messages SET ID_MEMBER_TO=$newID,toName='$newName' WHERE (ID_MEMBER_TO=$oldID)";
$result = mysql_query($update);
if ($DEBUG) {
	echo ($result ? "SUCCEEDED" : "FAILED") . ": $update\n";
}

$newMember->posts += $oldMember->posts;
if ($oldMember->dateRegistered < $newMember->dateRegistered) {
  $newMember->dateRegistered = $oldMember->dateRegistered;
}

$update = "UPDATE {$db_prefix}members SET posts=$newMember->posts,dateRegistered=$newMember->dateRegistered WHERE (ID_MEMBER=$newID)";
$result = mysql_query($update);
if ($DEBUG) {
	echo ($result ? "SUCCEEDED" : "FAILED") . ": $update\n";
}

$update = "UPDATE {$db_prefix}members SET posts=0,websiteURL='$newMember->emailAddress',realName='$oldMember->emailAddress' WHERE (ID_MEMBER=$oldID)";
$result = mysql_query($update);
if ($DEBUG) {
	echo ($result ? "SUCCEEDED" : "FAILED") . ": $update\n";
}

echo "Done.\n";
?>
