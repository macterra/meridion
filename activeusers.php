#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

function ActiveUsers()
{
  $sql = "SELECT lcase(left(source, instr(source, '!')-1)) as nick, target FROM `irclog` where UNIX_TIMESTAMP() - UNIX_TIMESTAMP(logged) < 3600 group by nick order by target";
  
  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $nick = $row[0];
    $channel = $row[1];
    
    $n++;
    
    if ($n % 2 == 1) {
      $users .= "<tr class=windowbg>";
    }
    else {
      $users .= "<tr class=windowbg2>";
    }
    
    $users .= "<td align=right>$n</td><td>$nick</td><td>$channel</td></tr>\n";
  }

  return $users;
}

$users = ActiveUsers();

echo "<?\n";
echo '$users=<<<END';
echo "\n$users";
echo "END;\n?>\n";
?>
