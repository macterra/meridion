<?
include("feedcreator.class.php");

$rss = new UniversalFeedCreator();
$rss->useCached();
$rss->title = "CoV BBS";
$rss->description = "recent posts to the Church of Virus forum";
$rss->link = "http://www.churchofvirus.com/bbs";
$rss->syndicationURL = "http://www.churchofvirus.com/".$PHP_SELF;

$image = new FeedImage();
$image->title = "dailyphp.net logo";
$image->url = "http://www.dailyphp.net/images/logo.gif";
$image->link = "http://www.dailyphp.net";
$image->description = "Feed provided by dailyphp.net. Click to visit.";
$rss->image = $image;

// get your news items from somewhere, e.g. your database:
/*
mysql_select_db($dbHost, $dbUser, $dbPass);
$res = mysql_query("SELECT * FROM news ORDER BY newsdate DESC");
while ($data = mysql_fetch_object($res)) {
    $item = new FeedItem();
    $item->title = $data->title;
    $item->link = $data->url;
    $item->description = $data->short;
    $item->date = $data->newsdate;
    $item->source = "http://www.dailyphp.net";
    $item->author = "John Doe";
    
    $rss->addItem($item);
}
*/

$rss->saveFeed("RSS1.0", "feed.xml");
?> 
