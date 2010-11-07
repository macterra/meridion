<?php


include_once("$sourcedir/Post.php");

function Export()
{
logit("entering Export");

	Export1();
	Post2();
	Export2();

logit("exiting Export");
}

function time2date($x)
{
  return date("Y-m-d \a\\t H:i:s", $x);
}

function StripTags($body)
{
  $tag = "/\[(.+?)\](.+?)\[\/(\\1)\]/si";
  $tag2 = "/\[(.+?)=.+?\](.+?)\[\/(\\1)\]/si";
  $url = "/\[url=(.+?)\](.+?)\[\/url\]/si";
  $quo = "/\[quote author=(.+?) .+? date=(.+?)\](.+?)\[\/quote\]/sie";
  
  $body = preg_replace($url, "$2 ($1)", $body);
  $body = preg_replace($tag, "$2", $body);
  $body = preg_replace($tag2, "$2", $body);
  $body = preg_replace($quo, "'[quote from: $1 on '.time2date($2).'] $3'", $body);
  
  return $body;
}

function PrepareMessage($msg, $x)
{
  if ($x < 1) {
    return $msg;
  }
  
  $foo = StripTags($msg);
  
  while ($foo != $msg) {
    $msg = $foo;
    $foo = StripTags($msg, $x-1);
  }
  
  $foo = preg_replace('/\\\"/', '"', $foo);
  $foo = preg_replace("/\\\'/", "'", $foo);
  
  return $foo;
}

function Export1() 
{
	global $subject,$message;
	global $origSubject, $origMessage;

	$origSubject = $subject;
	$origMessage = $message;
}

function Export2()
{
	global $username, $email, $txt, $threadid, $cgi, $mreplies, $maxmessagedisplay;
	global $mbname, $board, $realname, $db_prefix;
	global $origSubject, $origMessage;

	$request = mysql_query("SELECT name, description FROM {$db_prefix}boards WHERE ID_BOARD=$board LIMIT 1");
	if (mysql_num_rows($request) != 1) {
		return;
	}

	list($boardName, $boardDesc) = mysql_fetch_array($request);

	if (eregi("mailto:([^ ]+)", $boardDesc, $regs)) {
		$to = $regs[1];
	}

	if (!$to) {
		return;
	}

	$from = $email;

	$request = mysql_query("SELECT hideEmail FROM {$db_prefix}members WHERE memberName='$username' LIMIT 1");
	if (mysql_num_rows($request) == 1) {
		list($hide) = mysql_fetch_row($request);
		if ($hide) {
			$from = "hidden@lucifer.com";
		}
	}

	$hdrs = "From: \"$realname\" <$from>\r\n".
		"X-Mailer: YaBB\r\n";

	$newMessage = PrepareMessage($origMessage, 10);

	$footer = "----\nThis message was posted by $realname to the $boardName board on $mbname.";
        $link = "$cgi;action=display;threadid=$threadid";
	$body = "$newMessage\n\n$footer\n<$link>";
	$subject = PrepareMessage($origSubject, 10);

	mail($to, $subject, $body, $hdrs, "-f$email");
}

?>
