#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

$DEBUG = true;

function FixTopic($topic, $idFirst, $idLast, $numReplies)
{
# print "Fixing topic $topic\n";

  $query = "SELECT * FROM cov_messages WHERE ID_TOPIC=$topic ORDER BY posterTime";
  $result = mysql_query($query);
  $count = mysql_num_rows($result);

  if ($count != ($numReplies+1))
    print "$topic $count ($numReplies) messages in topic $topic\n";

  while ($row = mysql_fetch_assoc($result)) {
    extract($row);
#    print "$ID_MSG $posterName $posterTime\n";
    $ids[] = $ID_MSG;
  }

  $id1 = $ids[0];
  $id2 = $ids[count($ids)-1];

  if ($id1 != $idFirst) {
    print "$topic first id = $id1 $idFirst\n";
    $update = "UPDATE cov_topics SET ID_FIRST_MSG=$id1 WHERE (ID_TOPIC=$topic)";
    $result = mysql_query($update);
  }

  if ($id2 != $idLast) {
    print "fixing $topic last  id = $id2 $idLast\n";
    $update = "UPDATE cov_topics SET ID_LAST_MSG=$id2 WHERE (ID_TOPIC=$topic)";
    $result = mysql_query($update);
    if ($DEBUG) {
      echo ($result ? "SUCCEEDED" : "FAILED") . ": $update\n";
    }
  }
}

function FixBoard($board)
{
  print "Fixing board $board\n";

  $query = "SELECT * FROM cov_topics WHERE ID_BOARD=$board";
  $result = mysql_query($query);
  $count = mysql_num_rows($result);

  print "$count topics in board $board\n";

  while ($row = mysql_fetch_assoc($result)) {
    extract($row);
    FixTopic($ID_TOPIC, $ID_FIRST_MSG, $ID_LAST_MSG, $numReplies);
  }
}

function FixAll()
{
  $query = "SELECT * FROM cov_boards";
  $result = mysql_query($query);
  $count = mysql_num_rows($result);

  print "$count boards in BBS\n";

  while ($row = mysql_fetch_assoc($result)) {
    extract($row);
    FixBoard($ID_BOARD);
  }
}

FixAll();

echo "Done.\n";
?>
