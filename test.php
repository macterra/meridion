<?php
ob_start();

include_once ("Settings.php");
include_once ("auth.php");

### Lets output all that info. ###
print <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<style type="text/css">
<!--
BODY          {font-family: Verdana, arial, helvetica, serif; font-size:12px;}
TABLE       {empty-cells: show }
TD            {font-family: Verdana, arial, helvetica, serif; color: #000000; font-size:12px;}
-->
</style>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">

<title>$txt[668]</title>
</head>

<body bgcolor="#FFFFFF" text="#000000">
EOT;

print "$authUser<br>\n";
print "$authWikiName<br>\n";
print "$authLevel<p>\n";

for ($i = 0; $i < 30; $i++) {
  print "$i $settings[$i]<br>\n";
}

print <<<EOT
</body></html>
EOT;

$sql = "SELECT count(*) as c from {$db_prefix}log_clicks WHERE UNIX_TIMESTAMP() - logTime < 60 AND ip='$REMOTE_ADDR'";
  
$request = mysql_query($sql);

print "remote addr = $REMOTE_ADDR<p>\n";
print "query: $sql<p>\n";
print "request: $request<p>\n";

while ($row = mysql_fetch_row($request)) {
  $count = $row[0];
  print "$count clicks in last 60s<br>\n";

  if ($count > 10) {
    $insert = "INSERT INTO {$db_prefix}banned (type, value) VALUES ('ip', '$REMOTE_ADDR')";
    print "BAN!! $insert<br>\n";
    $request = mysql_query($insert);
  }
}
?>
