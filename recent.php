#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

include("feedcreator.class.php");

$rss = new UniversalFeedCreator();
$rss->useCached();
$rss->title = "CoV BBS";
$rss->description = "recent posts to the Church of Virus forum";
$rss->link = "http://www.churchofvirus.com/bbs";
$rss->syndicationURL = "http://www.churchofvirus.com/".$PHP_SELF;

$sql = "SELECT m.*, t.numReplies, t.ID_BOARD, x.realName FROM cov_messages as m, cov_topics as t, cov_members as x, cov_reputation as r where (m.ID_MEMBER=x.ID_MEMBER AND m.ID_MEMBER=r.ID_MEMBER AND r.reputation>3 AND m.ID_TOPIC=t.ID_TOPIC) AND (UNIX_TIMESTAMP()-posterTime) < (24*60*60) order by posterTime desc";

$request = mysql_query($sql);

while ($row = mysql_fetch_assoc($request)) {
  $idMsg = $row['ID_MSG'];
  $idTopic = $row['ID_TOPIC'];
  $idBoard = $row['ID_BOARD'];
  $idMember = $row['ID_MEMBER'];
  $subject = $row['subject'];
  $body = $row['body'];
  $numReplies = $row['numReplies'];
  $posterEmail = $row['posterEmail'];
#  $posterTime = timeformat($row['posterTime']);
  $posterTime = 0 + $row['posterTime'];
  $realName = $row['realName'];

  $message = $row['body'];

  CensorTxt($message);
  CensorTxt($subject);

  $message = DoUBBC($message,$row['smiliesEnabled']); 

  $fd = new FeedDate($posterTime);
  $foo = $fd->iso8601();
  $bar = is_integer(0 + $posterTime);
  $unix = $fd->unix();

  echo "$idMsg $idTopic $subject $idMember $realName $posterEmail $posterTime $foo $unix $bar\n";
  echo $message;

  $link = "$scripturl?board=$idBoard;action=display;threadid=$idTopic;start=$numReplies;msg=$idMsg";

  $item = new FeedItem();
  $item->title = $subject;
  $item->link = $link;
  $item->description = $message;
  $item->date = $posterTime;
  $item->source = "http://www.churchofvirus.org/bbs";
  $item->author = "$posterEmail ($realName)";
  $item->descriptionTruncSize = 500;
  $item->descriptionHtmlSyndicated = true;
    
  $rss->addItem($item);
}

$rss->saveFeed("RSS2.0", "feed.xml");
?>

	

