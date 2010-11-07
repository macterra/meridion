#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

function ActiveChannels()
{
  $sql = "SELECT target, count(*) as c from irclog where UNIX_TIMESTAMP() - UNIX_TIMESTAMP(logged) < 600 group by target order by c desc";
  
  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $channel = $row[0];
    $count = $row[1];
    
    $n++;
    
    if ($n % 2 == 1) {
      $channels .= "<tr class=windowbg>";
    }
    else {
      $channels .= "<tr class=windowbg2>";
    }
    
    $channels .= "<td align=right>$n</td><td>$channel</td><td align=right>$count</td></tr>\n";
  }

  return $channels;
}

$channels = ActiveChannels();

echo "<?\n";
echo '$channels=<<<END';
echo "\n$channels";
echo "END;\n?>\n";
?>
