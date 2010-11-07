#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

function AllChannels()
{
  $sql = "SELECT target FROM irclog GROUP BY target";
  
  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $channel = $row[0];
    
    if ($channel[0] == '#') {
      $channels .= "<option>$row[0]\n";
    }
  }

  return $channels;
}

$channels = AllChannels();

echo "<?\n";
echo '$channels=<<<END';
echo "\n$channels";
echo "END;\n?>\n";
?>
