<?php
ob_start();

include_once ("$sourcedir/Subs.php");
include_once ("$sourcedir/Errors.php");
include_once ("$sourcedir/Load.php");
include_once ("$sourcedir/Security.php");
include_once ("$sourcedir/Reputation.php");

$dbcon = mysql_connect($db_server, $db_user, $db_passwd);
mysql_select_db($db_name);

/* Load the mysql version, and set a variable for 3.22 compliancy :P */
$request = mysql_query("SELECT VERSION()");
$row = mysql_fetch_row($request);  // version will be something like '3.23.13-log'
global $doLimitOne;
$doLimitOne = (substr($row[0],0,4) >= 3.23)?' LIMIT 1':'';

/* ### Log this click ### */
ClickLog();

$printpageplver="YaBB SE";

/* ### Load the user's cookie (or set to guest) ### */
LoadCookie();

/* ### Load user settings ### */
LoadUserSettings();

/* ### Load board information ### */
LoadBoard();

/* ### Banning ### */
banning();


function ToWikiName($name) 
{
  $WikiNameRegexp = "(?<![[:alnum:]])(?:[[:upper:]][[:lower:]]+){2,}(?![[:alnum:]])";

  if (!preg_match("/$WikiNameRegexp/", $name))  {
      $name = str_replace(".", " ", $name);
      $name = str_replace("_", " ", $name);
      $name = str_replace("@", " at ", $name);
      $name = ucwords(strtolower($name));
      $name = str_replace(" ", "", $name);
  }
  
  if (!preg_match("/$WikiNameRegexp/", $name))  {
    $name = "Vector" . $name;
  }
  
  return $name;
}


global $authUser, $authWikiName, $authLevel;
global $rep;

$rep = UserReputation($ID_MEMBER);
$authUser = $settings[21] ? $settings[21] : "guest";

if ($settings[1]) {
  $authWikiName = ToWikiName($settings[1]);
} else {
  $authWikiName = "AnonymousUser";
}

if ($settings[1]) {
  if ($settings[7] == "Administrator") {
    $authLevel = 10;
  } else if ($rep > 7) {
    $authLevel = 10;
  } else if ($rep > 6) {
    $authLevel = 2;
  } else {
    $authLevel = 0;
  }
}
