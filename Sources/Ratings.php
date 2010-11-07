<?php

$notifyplver="YaBB SE 1.3.1";

include_once "$sourcedir/Matrix.php";
include_once "$sourcedir/Meridion.php";

class Subject
{
  var $id;
  var $title;
  var $category;
  var $url;
  var $reputation;

  function Subject($id) {
    global $db_prefix;

    $this->id = $id;

    if ($id == 0) {
      return;
    }

    $request = mysql_query("SELECT * from {$db_prefix}rating_subjects WHERE id_subject=$id");
    $row = mysql_fetch_assoc($request);

    if ($row) {
      $this->category = $row['category'];
      $this->title = $row['title'];
      $this->url = $row['url'];
      $this->sponsor = $row['id_member'];
      $this->reputation = $row['reputation'];
    }
  }

  function Save($minorEdit) {
    global $db_prefix;

    if (!$this->title) {
      return "No title";
    }

    if (!$this->url) {
      return "No URL";
    }

    if (!$this->category) {
      return "No category";
    }

    $title = mysql_escape_string($this->title);
    $url = mysql_escape_string($this->url);

    if ($this->id == 0) {
      $sql = "INSERT INTO {$db_prefix}rating_subjects (id_member,title,url,category) ";
      $sql .= "VALUES ({$this->sponsor},'$title','$url','{$this->category}')";
      print "$sql<br>\n";
      mysql_query($sql);
      $this->id = mysql_insert_id();
    }
    else {
      $sql = "UPDATE {$db_prefix}rating_subjects SET ";
      $sql .= "title='$title', ";
      $sql .= "url='$url', ";

      if (!$minorEdit) {
	$sql .= "reputation=5, ";
      }

      $sql .= "category='{$this->category}' ";
      $sql .= "WHERE id_subject={$this->id}";
      print "$sql<br>\n";
      mysql_query($sql);

      if (!$minorEdit) {
	$sql = "DELETE FROM {$db_prefix}rating_ballots WHERE id_subject={$this->id}";
	print "$sql<br>\n";
	mysql_query($sql);
      }
    }
  }

  function Delete() {
    global $db_prefix;

    mysql_query("DELETE FROM {$db_prefix}rating_subjects WHERE id_subject={$this->id}");
    mysql_query("DELETE FROM {$db_prefix}rating_ballots WHERE id_subject={$this->id}");
  }

}

function AllSubjects()
{
  global $db_prefix;

  $sql = "SELECT id_subject FROM {$db_prefix}rating_subjects";
  $request = mysql_query($sql);

  while ($row = mysql_fetch_row($request)) {
    $subjects[] = $row[0];
  }

  return $subjects;
}

function AllCategories()
{
  global $db_prefix;

  $sql = "SELECT DISTINCT category FROM {$db_prefix}rating_subjects ORDER BY category";
  $request = mysql_query($sql);

  while ($row = mysql_fetch_row($request)) {
    $cats[] = $row[0];
  }

  return $cats;
}

function AllRatings($init)
{
  global $db_prefix;
  
  if ($init) {
    $subjects = AllSubjects();
    $users = AllUsers();

    foreach($subjects as $subject) {
      foreach($users as $user) {
	$ratings[$subject][$user] = 5;
      }
    }
  }

  $request = mysql_query("SELECT * FROM {$db_prefix}rating_ballots");

  while ($row = mysql_fetch_row($request)) {
    $member = $row[0];
    $subject = $row[1];
    $rating = $row[2];
    $ratings[$subject][$member] = $rating;
  }

  return $ratings;
}

function RecalculateReps()
{
  global $db_prefix;
  
  $subjects = AllSubjects();
  $ratings = AllRatings(true);
  $equity = AllEquity();

  $foo = MatrixMultiply($equity, $ratings);
  $reps = VectorDivide($foo, 100);

  foreach($subjects as $subject) {
    $newrep = $reps[$subject];
    mysql_query("UPDATE {$db_prefix}rating_subjects SET reputation=$newrep WHERE id_subject=$subject");
  }
}

function Timestamp2Date($timestamp)
{
    $year = substr($timestamp, 0, 4);
    $month = substr($timestamp, 4, 2);
    $day = substr($timestamp, 6, 2);
    $date = date('M d, Y', mktime(0, 0, 0, $month, $day, $year));

    return $date;
}

function AllByCategory($cat, $sort, $order)
{
  global $db_prefix, $cgi, $ID_MEMBER;

  $sql = "select s.*,  count(b.rating) as ratings, avg(b.rating) as average, m.realName as sponsor, m.memberName from cov_rating_subjects s left outer join cov_rating_ballots b using (id_subject), cov_members m where m.id_member=s.id_member AND s.category='$cat' group by id_subject ORDER BY $sort $order";

  $request = mysql_query($sql);
  $n = 0;
  
  if ($order == "desc") {
    $order = "asc";
  }
  else {
    $order = "desc";
  }

  $orderLink = "$cgi;action=rateIndex;category=$cat;order=$order;sort=";
  $repLink = $orderLink . "reputation";
  $titleLink = $orderLink . "title";
  $sponsorLink = $orderLink . "sponsor";
  $updateLink = $orderLink . "updated";

  $all = "<tr class=windowbg>";
  $all .= "<td align=left><b><u><a href=$repLink>Reputation</a></u></b></td>";
  $all .= "<td align=left>&nbsp;</td>";
  $all .= "<td align=left><b><u><a href=$titleLink>Title</a></u></b></td>";
  $all .= "<td align=left><b><u><a href=$sponsorLink>Sponsor</a></u></b></td>";
  $all .= "<td align=left><b><u><a href=$updateLink>Updated</a></u></b></td>";
  $all .= "<td align=left><b><u><a href={$orderLink}ratings>Ratings</a></u></b></td>";
  $all .= "<td align=left><b><u><a href={$orderLink}average>Average</a></u></b></td>";
  $all .= "</tr>\n";

  while ($row = mysql_fetch_assoc($request)) {
    $n++;

    if ($n % 2 == 1) {
      $all .= "<tr class=windowbg>";
    }
    else {
      $all .= "<tr class=windowbg2>";
    }
    
    $id = $row['id_subject'];
    $title = $row['title'];
    $url = $row['url'];
    $titlelink = "<a href=$url target=_blank>$title</a>&nbsp;&nbsp;";
    $rep = sprintf("%1.2f", $row['reputation']);
    $category = $row['category'];
    $memberName = $row['memberName'];
    $sponsor = $row['sponsor'];
    $updated = Timestamp2Date($row['updated']);
    $ratings = $row['ratings'];
    $average = $row['average'];
    $profile = "$cgi;action=viewprofile;user=$memberName";
    $namelink = "<a href=$profile>$sponsor</a>";

    if ($ID_MEMBER == $row['id_member'] || $ID_MEMBER == 1) {
      $editlink = "<a href=$cgi;action=rateEdit;id=$id>edit</a>";
    }
    else {
      $editlink = "";
    }

    $all .= "<td>$rep</td>";
    $all .= "<td>$editlink</td>";
    $all .= "<td>$titlelink</td>";
    $all .= "<td>$namelink</td>";
    $all .= "<td>$updated</td>";
    $all .= "<td>$ratings</td>";
    $all .= "<td>$average</td>";
    $all .= "</tr>\n";
  }

  return $all;
}

function AllChoices($cat)
{
  global $db_prefix, $ID_MEMBER, $joinedRep, $settings, $cgi;

  $ratings = AllRatings(false);

  $sql = "SELECT id_subject, title, url, category FROM {$db_prefix}rating_subjects WHERE category='$cat' ORDER BY title";
  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $title = $row[1];
    $url = $row[2];
    $category = $row[3];

    $n++;
    
    if ($n % 10 == 1) {
      $all .= "<tr align=center>";
      $all .= "<td>&nbsp;</td><td>&nbsp;</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td>";
      $all .= "</tr>\n";
    }

    if ($n % 2 == 1) {
      $all .= "<tr class=windowbg>";
    }
    else {
      $all .= "<tr class=windowbg2>";
    }
    
    if ($ID_MEMBER == $id) {
      $joinedRep = 1;
    }

    $namelink = "<a href=$url target=_blank>$title</a>&nbsp;&nbsp;";

    if ($ratings[$id][$ID_MEMBER]) {
      $all .= "<td align=right>$n</td><td>$namelink</td>";
    }
    else {
      $all .= "<td align=right><font color=yellow>new! $n</font></td><td>$namelink</td>";
    }
    
    for ($i = 1; $i < 10; $i++) {
      if ($ratings[$id][$ID_MEMBER] == $i) {
	$all .= "<td><input type=radio name=$id value=$i checked></td>";
      }
      else {
	$all .= "<td><input type=radio name=$id value=$i></td>";
      }
    }

    $all .= "<td>{$ratings[$id][$ID_MEMBER]}</td>";
    $all .= "</tr>\n";
  }

  return $all;
}

function Index()
{
  global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
  global $category, $sort, $order;

  $yytitle = "Meridion Ratings";
  template_header();

  $userRep = UserReputation($ID_MEMBER);
  $cats = AllCategories();

  foreach($cats as $cat) {
    $selected = ($cat == $category) ? "selected" : "";
    $catopts .= "<option $selected>$cat</option>";
  }

  if (!$category) {
    $category = $cats[0];
  }

  if (!$sort) {
    $sort = "reputation";
  }

  if (!$order) {
    $order = "desc";
  }

  if ($userRep > 6) {
    $addlink = "$cgi;action=rateEdit;id=0;category=$category";
    $addForm = "<form action=$addlink method=post><input type=submit value=\"Add $category\">";
    $addForm .= " Your reputation is high enough to add a new $category to rate. ";
    $addForm .= " Hint: click on Title header and make sure your addition is unique.</form>";
  }

  $all = AllByCategory($category, $sort, $order);

  print <<<EOT
<p>
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="50%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]">viewing category: <b>$category</b></font></td>
      </tr>
      <tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
    <form action="$cgi;action=rateEnter" method="post">
    <input type="submit" value="Enter Ratings"> You must join Meridion for your ratings to count.
    <input type="hidden" name="category" value="$category">
    <input type="hidden" name="sort" value="$sort">
    </form>
    $addForm
<p>
<table align=center width="80%">

<tr>
  <form action="$cgi;action=rateIndex" method="post">										  
  <td colspan=4>Category: <select name=category>$catopts</select> <input type="submit" value="Display"></td>
  </form>
</tr>

$all
</table>
        </td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2" valign=middle>
<br>
<table align=center width=50%>
<tr>
<td valign=top>Reputation:</td>
<td>The average rating weighted by each <a href="http://virus.lucifer.com/bbs/index.php?board=;action=repIndex">member's influence</a>.<p></td>
</tr>

</table>

    </td>
  </tr>												       
  </tr>
</table>
EOT;

  footer();
  obExit();
}

function EnterRatings()
{
  global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
  global $category;

  if($username == "Guest") { fatal_error("$txt[138]"); }
  $yytitle = "Reputation";
  template_header();

  $all = AllChoices($category);

  print <<<EOT
<form action="$cgi;action=rateSubmit" method="post">
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="80%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Active Users</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<table align=center>
<tr>
  <td>&nbsp;</td><td><b><u>title</u></b></td><td align=left colspan=2>&lt;-bad</td><td align=center colspan=5><b><u>rating</u></b></td><td align=right colspan=2>good-&gt;</td>
</tr>
$all
</table>
        </td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=middle>
    <input type="submit" value="Submit Ratings">
    <input type="hidden" name="category" value="$category">
    </td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2" valign=middle>
<p>
<br>
<center><u>Meanings of Ratings</u><br></center>
<ol>
<li>The worst of the worst
<li>Little or no redeeming value
<li>Strongly negative opinion
<li>Slight negative opinion
<li>Neutral or no opinion
<li>Slight positive opinion
<li>Strong positive opinion
<li>Among best in class
<li>The best of the best
</ol>
    </td>
  </tr>												       
</table>
</form>
EOT;

  footer();
  obExit();
}

function SubmitRatings()
{
  global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
  global $yySetLocation, $cgi;
  global $r1, $r2, $r3;
  global $category;

  if($username == "Guest") { fatal_error("$txt[138]"); }
  $yytitle = "Reputation";
  template_header();

  foreach($_POST as $id => $rating) {
    print "$ID_MEMBER gave $id a $rating<br>\n";
    mysql_query("DELETE FROM {$db_prefix}rating_ballots WHERE id_member=$ID_MEMBER AND id_subject=$id");
    mysql_query("INSERT INTO {$db_prefix}rating_ballots (id_member,id_subject,rating) VALUES ($ID_MEMBER,$id,$rating)");
    //mysql_query("REPLACE INTO {$db_prefix}rating_ballots (id_member,id_subject,rating) VALUES ($ID_MEMBER,$id,$rating)");
  }


  RecalculateReps();

  $yySetLocation = "$cgi;action=rateIndex;category=$category";
  redirectexit();
}

function EditSubject()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $id, $category, $url, $title, $ID_MEMBER;

  $userRep = UserReputation($ID_MEMBER);
  if ($userRep < 6) { 
    fatal_error("Sorry you need a reputation of at least 6 for this feature");
  }

  $yytitle = "Edit Subject";
  template_header();

  $subject = new Subject($id);

  $cats = array("person", "book", "movie", "music", "org", "site", "game", "software", "character", "periodical");

  if ($id == 0) {
    $subject->category = $category;
    $subject->title = $title;
    $subject->url = $url;
  }

  foreach($cats as $cat) {
    $selected = ($cat == $subject->category) ? "selected" : "";
    $catopts .= "<option $selected>$cat</option>";
  }

  if ($id == 0) {
    $header = "Add a new subject";
  }
  else {
    if ($subject->sponsor != $ID_MEMBER && $ID_MEMBER != 1) {
      fatal_error("Sorry, only the sponsor can edit");
    }

    $header = "Edit subject";
    //$warning = "Warning: submitting form will erase all existing ratings!";
    $minorEdit = "minor edit: <input type=checkbox name=minorEdit>";
    $onclick = "onClick=\"return confirmSubmit()\"";
    $delLink = "$cgi;action=rateDelete;id=$id";
    $delForm = "<form action=$delLink method=post><input type=submit value=Delete $onclick></form>";
  }

print <<<EOT
<script LANGUAGE="JavaScript">
<!--
  // Nannette Thacker http://www.shiningstar.net
  function confirmSubmit() 
{
  var agree=confirm("Are you sure you wish to continue?");
  if (agree)
  return true ;
else
  return false ;
}
// -->
</script>
<table border=0  align="center" cellspacing=1 cellpadding="3" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>$header</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<form action="$cgi;action=rateEditSubmit" method="post">
<input type=hidden name=id value=$id>
<table align=center cellpadding=5 cellspacing=0 border=0>
<tr><td colspan=2 align=left>$text</td><tr>
<tr>
  <td>Category</td><td><select name=category>$catopts</select></td>
</tr>
<tr>
  <td>Title</td>
  <td><input name=title size=40 maxlength=80 value="{$subject->title}"></td>
</tr>
<tr>
  <td>URL</td>
  <td><input name=url size=40 maxlength=100 value="{$subject->url}"></td>
</tr>
<tr>
  <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=middle colspan=2>
  <font color=red>
    $warning
  </font>
  </td>
</tr>
<tr>
  <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=middle colspan=2>
  <input type="submit" value="Submit">
  $minorEdit
  </td>
</tr>
</table>
</form>
        </td>
      </tr>
<tr>
 <td>
$delForm
 </td>
</tr>
    </table>
    </td>
  </tr>
      <tr>
       <td width=100% class="windowbg2">

<table border=0>
    <tr><td><b>category</b></td> <td><b>URL Guidelines</b></td></tr>
    <tr><td>book</td> <td>http://www.amazon.com/exec/obidos/tg/detail/-/{book id}</td></tr>
    <tr><td>movie</td> <td>http://www.imdb.com/title/{movie id}</td></tr>
    <tr><td>music</td> <td>http://www.amazon.com/exec/obidos/tg/detail/-/{album id}<br>
    solo artists should be categorized under person, bands under org
    </td></tr>
    <tr><td>org</td> <td>use the organization's home home page or Wikipedia</td></tr>
    <tr><td>site</td> <td>use the top level website URL</td></tr>
    <tr><td>person</td> <td>use wikipedia or their home page</td></tr>
    <tr><td>game</td> <td>use the official home page</td></tr>
    <tr><td>software</td> <td>use the official home page</td></tr>
</table>

       </td>
      </tr>
      <tr>
       <td width=100% class="windowbg">
Bookmark, add to favourites, or drag this link to your toolbar to easily add new items for rating>>
<blockquote><a href="javascript:void(location.href='http://virus.lucifer.com/bbs/index.php?board=;action=rateEdit;id=0;url='+location.href+';title='+document.title)"  onmouseover="window.status='';return true" onclick="return false">Add to Meridion!</a></blockquote>
It will automatically set the title and URL fields with the page where you came from.<br>
Don't forget to set the category and edit the URL according the guidelines above.
<p>
<a href="$cgi;action=rateIndex;category=$category">Back</a>
       </td>
      </tr>
</table>
EOT;

  footer();
  obExit();
}

function SubmitSubject()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $ID_MEMBER, $yySetLocation;
  global $id, $title, $category, $url, $minorEdit;

  $userRep = UserReputation($ID_MEMBER);
  if ($userRep < 6) { 
    fatal_error("Sorry you need a reputation of at least 6 for this feature");
  }

  $yytitle = "Edit Subject";
  template_header();

  $subject = new Subject(0);

  $subject->id = $id;
  $subject->sponsor = $ID_MEMBER;
  $subject->title = $title;
  $subject->category = $category;
  $subject->url = $url;

  $err = $subject->Save($minorEdit);

  if ($err) {
    fatal_error($err);
  }

  //footer();
  //obExit();

  $yySetLocation = "$cgi;action=rateIndex;category=$category";
  redirectexit();
}

function DeleteSubject()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $ID_MEMBER, $yySetLocation;
  global $id;

  $yytitle = "Delete Subject";
  template_header();

  $subject = new Subject($id);

  if ($subject->sponsor != $ID_MEMBER) {
    fatal_error("Sorry, only the sponsor can delete a subject");
  }

  $category = $subject->category;
  $err = $subject->Delete();

  if ($err) {
    fatal_error($err);
  }

  //footer();
  //obExit();

  $yySetLocation = "$cgi;action=rateIndex;category=$category";
  redirectexit();
}

?>
