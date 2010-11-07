#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

$query = "SELECT * FROM irclog WHERE target='#vpsummit'";
$result = mysql_query($query);

while ($row = mysql_fetch_array($result)) {
  extract($row);
  $event = strtoupper($event);
  if ($event == 'ACTION') {
    echo "$logged :$source CTCP $target :ACTION $text\n";
  }
  else {
    echo "$logged :$source $event $target :$text\n";
  }
}

?>
