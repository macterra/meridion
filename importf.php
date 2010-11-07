<?php

class Member 
{
  var $id;
  var $memberName;
  var $realName;
  var $emailAddress;
  var $forwardingAddress;
  var $dateRegistered;
  var $posts;
  var $updated;

  function Member($id, $memberName, $realName, $emailAddress, $dateRegistered, $posts, $url) {
    $this->id = $id;
    $this->memberName = $memberName;
    $this->realName = $realName;
    $this->emailAddress = $emailAddress;
    $this->dateRegistered = $dateRegistered;
    $this->posts = $posts;
    $this->updated = false;

    if (ereg("(http://)?(.*@.*)", $url, $regs)) {
      $this->forwardingAddress = $regs[2];
      //      logit("forwarding address $id $emailAddress -> $regs[2]");
    }
  }

  function update($msg) {
    if ($msg->arrived < $this->dateRegistered) {
      $this->dateRegistered = $msg->arrived;
    }

    $this->posts += 1;
    $this->updated = true;

    //    echo "updated ". $this->dump();
  }

  function dump() {
    return "[$this->id $this->realName $this->emailAddress $this->posts $this->updated]\n";
  }
}

class Message
{
  var $arrived;
  var $headers;
  var $body;

  function Message($arrived, $headers, $body) {
    $this->arrived = $arrived;
    $this->headers = $headers;
    $this->body = $body;
  }

  function dump() {
    echo "Arrived on ", date("H:m:s d-M-Y", $this->arrived), "\n";
    foreach($this->headers as $key => $value) {
      $foo = htmlentities($value);
      echo "$key: $foo\n";
    }
    echo "$this->body";
  }

  function asString() {
    $str = "Arrived on ".date("H:m:s d-M-Y", $this->arrived)."\n";
    foreach($this->headers as $key => $value) {
      $str .= "$key: $value\n";
    }
    $str .= $this->body;

    return $str;
  }
}

/* ### Version Info ### */
$YaBBversion = 'YaBB SE 1.0.0';
$YaBBplver = 'YaBB SE';

include_once ("QueryString.php");
include_once ("Settings.php");
include_once ($language);
include_once ("$sourcedir/Subs.php");
include_once ("$sourcedir/Load.php");
include_once ("$sourcedir/Security.php");

$dbcon = mysql_connect($db_server, $db_user, $db_passwd);
mysql_select_db($db_name);

/* Load the mysql version, and set a variable for 3.22 compliancy :P */
$request = mysql_query("SELECT VERSION()");
$row = mysql_fetch_row($request);  // version will be something like '3.23.13-log'
global $doLimitOne;
$doLimitOne = (substr($row[0],0,4) >= 3.23)?' LIMIT 1':'';

set_time_limit(6000);
//set_error_handler("yabb_error_handler");

global $members;
global $boards;
global $topics;
global $messages;
global $db_prefix;

function &getMembers()
{
  global $members;
  global $db_prefix;

  if (!$members) {
    $request = mysql_query ("SELECT * FROM ${db_prefix}members");

    while($row = mysql_fetch_assoc($request)) {
      $id = $row["ID_MEMBER"];
      $addr = $row["emailAddress"];
      $memberName = $row["memberName"];
      $realName = $row["realName"];
      $date = $row["dateRegistered"];
      $posts = $row["posts"];
      $url = $row["websiteUrl"];

      $member = new Member($id, $memberName, $realName, $addr, $date, $posts, $url);
      $members[$addr] = $member;
    }
  }

  return $members;
}

function &getBoards() {
  global $boards;
  global $db_prefix;

  if (!$boards) {
    $request = mysql_query ("SELECT * FROM ${db_prefix}boards");

    while ($row = mysql_fetch_assoc($request)) {
      $id = $row["ID_BOARD"];
      $boards[$id] = $row["name"];
    }
  }

  return $boards;
}

function &getTopics() {
  global $topics;
  global $db_prefix;

  $trans = get_html_translation_table(HTML_ENTITIES);
  $trans = array_flip($trans);

  if (!$topics) {
    $request = mysql_query ("SELECT t.id_topic, m.subject FROM ${db_prefix}topics as t, ${db_prefix}messages as m WHERE (t.id_first_msg=m.id_msg)");

    while($row = mysql_fetch_assoc($request)) {
      $id = $row["id_topic"];
      $subj = strtr($row["subject"], $trans);
      $topics[compressSubject($subj)] = $id;
    }
  }

  return $topics;
}

function &getMessagesOBSOLETE() 
{
  global $messages;
  global $db_prefix;

  if (!$messages) {
    $request = mysql_query ("SELECT id_msg, id_member, posterTime FROM ${db_prefix}messages");

    while($row = mysql_fetch_assoc($request)) {
      $key = $row["id_member"]."-".$row["posterTime"];
      $messages[$key] =  $row["id_msg"];

      //  echo "message $key = $messages[$key]\n";
    }
  }

  return $messages;
}

function cleanUp() 
{
  global $db_prefix;

  mysql_query("DELETE FROM ${db_prefix}members WHERE ID_MEMBER > 1");
  mysql_query("DELETE FROM ${db_prefix}topics WHERE ID_TOPIC > 1");
  mysql_query("DELETE FROM ${db_prefix}messages WHERE ID_MSG > 1");
}

function parseAddress($from)
{
  $foo = htmlentities($from);
  //echo "parseAddress($foo)\n";

  $EMAIL = "[_a-z0-9-]+[\.[_a-z0-9-]+]*@[a-z0-9-]+[\.[a-z0-9-]+]*";
  $NAME = "[[:alnum:][:space:]]+";	

  if (eregi("($EMAIL) +\(($NAME)\)", $from, $regs)) {
    return "$regs[1]:$regs[2]";
  }

  if (eregi("($NAME) +<($EMAIL)>", $from, $regs)) {
    return "$regs[2]:$regs[1]";
  }

  if (eregi("($EMAIL)", $from, $regs)) {
    return "$regs[1]:";
  }

  return ":";
}

function prepareStr($str)
{
  $str = htmlentities($str);
  $str = mysql_escape_string($str);

  return $str;
}

function compressSubject($subject)
{
  $subj = strtolower($subject);

  $subj = str_replace("virus:", "", $subj); 
  $subj = str_replace("re:", "", $subj); 
  $subj = str_replace("fwd:", "", $subj); 
  $subj = str_replace("fw:", "", $subj); 
  $subj = eregi_replace("\[.?\]", "", $subj); 
  $subj = eregi_replace("[[:space:]]", "", $subj);
  $subj = eregi_replace("[[:punct:]]", "", $subj);
  $subj = substr($subj, 0, 25);

  //logit("compressSubject [$subject]->[$subj]\n");

  return $subj;
}

function msgExists($id, $arrived)
{
  global $db_prefix;

  $request = mysql_query("SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_MEMBER='$id' && posterTime='$arrived'");

  return (mysql_num_rows($request) > 0) ? true : false;
}

function saveMsg($message, &$member) 
{
  global $db_prefix;
  global $board;

  $refTopics =& getTopics();

  $key = $member->id."-".$message->arrived;

  if (msgExists($member->id, $message->arrived)) {
    $id = htmlentities($message->headers["message-id"]);
    logit("Discarded $id (duplicate message key: $key).");
    return;
  }

  $subject = $message->headers["subject"];
  $topicKey = compressSubject($subject);
  $topicID = $refTopics[$topicKey];
  $subject = prepareStr($subject);
  $body = prepareStr($message->body);
  $body = preparsecode($body, $member->memberName, $member->realName);
  
  $cols = "ID_MEMBER,subject,posterName,posterEmail,posterTime,posterIP,body,icon";
  $values = "$member->id,'$subject','$member->memberName','$member->emailAddress',$message->arrived,'127.0.0.1', '$body','xx'";
  $query = "INSERT INTO {$db_prefix}messages ($cols) VALUES ($values)";
  $request = mysql_query($query);
  $msgID = mysql_insert_id();

  if ($msgID > 0) {
    //    echo "message successfully inserted at $msgID\n";

    if (!$topicID) {
      $request = mysql_query("INSERT INTO {$db_prefix}topics (ID_BOARD,ID_MEMBER_STARTED,ID_MEMBER_UPDATED,ID_FIRST_MSG,ID_LAST_MSG,locked,numViews,numReplies) VALUES ($board,$member->id,$member->id,$msgID,$msgID,0,0,-1)");
      $topicID = mysql_insert_id();
      $refTopics[$topicKey] = $topicID;
      logit("new topic: $topicKey");
    }

    if ($topicID) {
      $request = mysql_query("UPDATE {$db_prefix}messages SET ID_TOPIC=$topicID WHERE (ID_MSG=$msgID)");
      $request = mysql_query("UPDATE {$db_prefix}topics SET ID_MEMBER_UPDATED=$member->id,ID_LAST_MSG=$msgID,numReplies=numReplies+1 WHERE (ID_TOPIC=$topicID)");
    }

    $member->update($message);
  }
  else {
    $foo = htmlentities($message->headers["message-id"]);
    $foo = $message->asString();
    logit("msg insert failed for:\n$foo\n");
    logit($query);
    logit("strlen(body) = ". strlen($body));
    logit("strlen(message->body) = ". strlen($message->body));
  }
}

$log = fopen("import.log", "a");

function logit($msg) 
{
  global $log, $DEBUG;

  $time = date("D M j Y G:i:s T");

  fputs($log, "$time $msg\n");

  if ($DEBUG) {
    echo "$time $msg<br>\n";
  }
}

function &getMember($addr, $name, $requesttime) 
{
  global $db_prefix;
  global $txt;

  $addr = strtolower($addr);

  $refMembers =& getMembers();
  $member =& $refMembers[$addr];

  if ($member) {
    if ($member->forwardingAddress) {
      return getMember($member->forwardingAddress, $name, $requesttime);
    }
  }
  else{
    
    //logit("Have to add $addr\n");

    srand(time());
    $pass = crypt(mt_rand(-100000,100000));
    $queryPasswdPart = crypt($pass,substr($pass,0,2));

    if (!$name) {
      $name = $addr;
    }

    $request = mysql_query("INSERT INTO {$db_prefix}members (memberName,realName,passwd,emailAddress,posts,personalText,avatar,dateRegistered,hideEmail) VALUES ('$addr','$name','$queryPasswdPart','$addr',0,'$txt[209]','blank.gif','$requesttime','1')");

    $member = new Member(mysql_insert_id(), $addr, $name, $addr, $requesttime, 0, "");
    $refMembers[$addr] = $member;
    logit("new member: $name $addr");
  }

  return $member;
}

function newMessage($arrived, $headers, $body) 
{
  global $msgids;

  $msgid = $headers["message-id"];
  $from = $headers["from"];
  $subj = $headers["subject"];
  $mailer = $headers["x-mailer"];
  
  if (eregi("yabb", $mailer)) {
    logit("Discarded $msgid (mailed by YaBB)");
    return;
  }
  
  if (!$subj) {
    $foo = htmlentities($msgid);
    logit("Discarded $msgid (no subject line)");
    return;
  }

  if ($msgids[$msgid]) {
    $id = htmlentities($msgid);
    logit("Discarded $id (duplicate message-id)");
    return;
  }

  $msgids[$msgid] = 1;

  $message = new Message($arrived, $headers, $body);
  $pair = parseAddress($from);
  list($addr, $name) = explode(":", $pair);
  $name = trim($name);
  $name = str_replace("\"", "", $name);

  if ($addr) {
    $member =& getMember($addr, $name, $arrived);
    saveMsg($message, $member);
  }
  else {
    $foo = htmlentities($from);
    logit("Discarded (couldn't parse email address: $foo)");
  }
}

function saveMembers()
{
  global $db_prefix;
  
  $refMembers =& getMembers();

  foreach($refMembers as $member) {
    if ($member->updated) {
      $request = mysql_query("UPDATE {$db_prefix}members SET posts=$member->posts, dateRegistered=$member->dateRegistered WHERE (ID_MEMBER=$member->id)");
      //logit("Member $member->id ($member->memberName) wrote $member->posts messages.");
    }
  }	
}


function rowCount($table)
{
  global $db_prefix;

  $request = mysql_query("SELECT count(*) as count from {$db_prefix}$table");

  if ($row = mysql_fetch_row($request)) {
    return $row[0];
  }
  else {
    return 0;
  }
}

function importArchive($file) 
{
  global $board, $timezone;

  $EMAIL = "[_a-z0-9-]+[\.[_a-z0-9-]+]*@?[a-z0-9-]+[\.[a-z0-9-]+]*";

  $memberCount = rowCount("members");
  $topicCount = rowCount("topics");
  $messageCount = rowCount("messages");

  $state = 0;
  $body = 0;
  $headers = array();

  logit("Beginning import of $file into board $board using tz offset $timezone...");

  $f = fopen($file, "r");

  while (!feof($f)) {
    $line = fgets($f, 4096);

    //    echo "$state $line";

    switch($state) {
    case 0:
      if (ereg("^From $EMAIL +([[:print:]]+)", $line, $regs)) {
	if ($body && $headers && $arrived) {
	  newMessage($arrived, $headers, $body);
	}

	$state = 1;

	$headers = array();
	$body = "";

	list($day, $month, $date, $time, $year) = split(" +", $regs[1]);
	$arrived = strtotime("$time$timezone $date-$month-$year");  
      }
      else {
	$body .= $line;
      }
      break;

    case 1:
      if (eregi("^\n$", $line)) {
	$state = 0;
      }
      else {
	if (eregi("^([[:alnum:]|-]+): ([[:print:]]+)", $line, $regs)) {
	  $headers[strtolower($regs[1])] = $regs[2];
	}
      }
      break;
    }
  }

  fclose($f);

  if ($body && $headers && $arrived) {
    newMessage($arrived, $headers, $body);
  }

  saveMembers();
  
  $newMemberCount = rowCount("members");
  $newTopicCount = rowCount("topics");
  $newMessageCount = rowCount("messages");

  $newMembers = $newMemberCount - $memberCount;
  $newTopics = $newTopicCount - $topicCount;
  $newMessages = $newMessageCount - $messageCount;

  logit("Import of $file complete.");
  logit("Added $newMembers members. ($memberCount - $newMemberCount)");
  logit("Added $newTopics topics. ($topicCount - $newTopicCount)");
  logit("Added $newMessages messages. ($messageCount - $newMessageCount)");
}

function updateMembers()
{
  global $db_prefix;

  $refMembers =& getMembers();

  foreach ($refMembers as $member) {

    $result = mysql_query("SELECT posterTime FROM ${db_prefix}messages WHERE id_member=$member->id ORDER BY posterTime ");

    logit("fetched messages for $member->id\n");

    $count = mysql_num_rows($result);
    $temp = mysql_fetch_row($result);
    $earliest = $temp[0];

    $request = mysql_query("UPDATE ${db_prefix}refMembers SET posts=$count, dateRegistered=$earliest WHERE id_member=$member->id");

    logit("$member->id $member->emailAddress ($member->realName) has posted $count messages.");
  }
}

?>
