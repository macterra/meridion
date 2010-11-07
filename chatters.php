#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

function TopChatters()
{
  $sql = "SELECT lcase(left(source, instr(source, '!')-1)) as nick, count(*) as c, left(max(logged), 16) as last FROM `irclog` group by nick order by c desc LIMIT 50";
  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $nick = $row[0];
    $count = $row[1];
    $last = $row[2];
    
    $n++;
    
    if ($n % 2 == 1) {
      $chatters .= "<tr class=windowbg>";
    }
    else {
      $chatters .= "<tr class=windowbg2>";
    }
    
    $chatters .= "<td align=right>$n</td><td>$nick</td><td align=right>$count&nbsp;&nbsp;</td><td>$last</td></tr>\n";
  }

  return $chatters;
}

$chatters = TopChatters();

echo "<?\n";
echo '$chatters=<<<END';
echo "\n$chatters";
echo "END;\n?>\n";
?>
