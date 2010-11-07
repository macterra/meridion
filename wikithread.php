<?php
ob_start();

/* ### Version Info ### */
$YaBBversion = 'YaBB SE 1.3.0';
$YaBBplver = 'YaBB SE 1.3.0';

error_reporting (E_ALL ^ E_NOTICE);

include_once ("QueryString.php");
include_once ("Settings.php");
include_once ("$sourcedir/Subs.php");
include_once ("$sourcedir/Errors.php");
include_once ("$sourcedir/Load.php");
include_once ("$sourcedir/Security.php");

set_time_limit(300);

$dbcon = mysql_connect($db_server, $db_user, $db_passwd);
mysql_select_db($db_name);

//$request = mysql_query("SELECT VERSION()");
//$row = mysql_fetch_row($request);  // version will be something like '3.23.13-log'
//global $doLimitOne;
//$doLimitOne = (substr($row[0],0,4) >= 3.23)?' LIMIT 1':'';
$doLimitOne = '';

//print $QUERY_STRING;
$page = urldecode($QUERY_STRING);

$sql = "SELECT t.ID_TOPIC FROM `cov_messages` as m, cov_topics as t where subject='$page' and m.ID_TOPIC=t.ID_TOPIC and t.ID_BOARD=59 and t.ID_FIRST_MSG=m.ID_MSG";

$request = mysql_query($sql);
$row = mysql_fetch_row($request);

if ($row) {
  $thread = $row[0];
  $threadurl = "http://www.churchofvirus.org/bbs/index.php?board=59;action=display;threadid=$thread";
}
else {
  $threadurl = "http://www.churchofvirus.org/bbs/index.php?board=59";
}

header("Location: $threadurl");

?>

<html>
<?= $sql ?>
<p>
<?= $threadurl ?>
</html>
