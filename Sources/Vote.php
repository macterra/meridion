<?php

include_once "$sourcedir/Meridion.php";
include_once "$sourcedir/Matrix.php";

$sendtopicplver="YaBB SE";

class Vote
{
  var $id;
  var $title;
  var $text;
  var $category;
  var $type;
  var $sponsor;
  var $closed;
  var $options;
  var $ballots;
  var $voters;
  var $repr;
  var $total;
  var $quorumLevel;
  var $decisiveness;
  var $contribution;
  var $memberBallots;

  function Vote($idvote) {
    global $db_prefix;

    $this->id = $idvote;
    $this->options = array();
    $this->ballots = array();
    $this->voters = array();
    $this->repr = array();
    $this->memberBallots = array();

    if ($idvote == 0) {
      return;
    }

    $request = mysql_query("SELECT * from {$db_prefix}votes WHERE id_vote=$idvote");
    $row = mysql_fetch_assoc($request);

    if ($row) {
      $this->title = $row['title'];
      $this->text = $row['text'];
      $this->sponsor = $row['id_member'];
      $this->category = $row['category'];
      $this->type = $row['type'];
      $this->closed = $row['closed'];
    }

    if ($this->type == 3) {
      $this->options = array(0 => "No", 1 => "Yes");
      return;
    }
 
    if ($this->type == 4) {
      $this->options = array(0 => "Disagree", 1 => "Agree");
      return;
    }

    $request = mysql_query("SELECT * from {$db_prefix}vote_options WHERE id_vote=$idvote ORDER BY sort");
    while ($row = mysql_fetch_assoc($request)) {
      $id_option = $row['id_option'];
      $this->options[$id_option] = $row['text'];
    }
  }

  function Save() {
    global $db_prefix;

    if (!$this->title) {
      return "No title";
    }

    if (!$this->text) {
      return "No question";
    }

    if (!$this->category) {
      return "No category";
    }

    if (count($this->options) == 0) {
      return "No options";
    }

    $title = mysql_escape_string($this->title);
    $text = mysql_escape_string($this->text);

    if ($this->id == 0) {
      $sql = "INSERT INTO {$db_prefix}votes (id_member,title,text,type,closed,category) ";
      $sql .= "VALUES ({$this->sponsor},'$title','$text',{$this->type},{$this->closed},'{$this->category}')";
      print "$sql<br>\n";
      mysql_query($sql);
      $this->id = mysql_insert_id();
    }
    else {
      $sql = "UPDATE {$db_prefix}votes SET ";
      $sql .= "title='$title',";
      $sql .= "text='$text',";
      $sql .= "type={$this->type},";
      $sql .= "closed={$this->closed},";
      $sql .= "category='{$this->category}' ";
      $sql .= "WHERE id_vote={$this->id}";
      print "$sql<br>\n";
      mysql_query($sql);

      $sql = "DELETE FROM {$db_prefix}vote_options WHERE id_vote={$this->id}";
      print "$sql<br>\n";
      mysql_query($sql);

      $sql = "DELETE FROM {$db_prefix}vote_ballots WHERE id_vote={$this->id}";
      print "$sql<br>\n";
      mysql_query($sql);

      $sql = "DELETE FROM {$db_prefix}vote_cast WHERE id_vote={$this->id}";
      print "$sql<br>\n";
      mysql_query($sql);
    }

    $n = 0;
    foreach($this->options as $opt) {
      $n++;
      if ($opt) {
	$opt = mysql_escape_string($opt);
	$sql = "INSERT INTO {$db_prefix}vote_options (id_vote,text,sort) VALUES ({$this->id},'$opt',$n)";
	print "$sql<br>\n";
	mysql_query($sql);
      }	
    }	
  }

  function Delete() {
    global $db_prefix;

    mysql_query("DELETE FROM {$db_prefix}votes WHERE id_vote={$this->id}");
    mysql_query("DELETE FROM {$db_prefix}vote_option WHERE id_vote={$this->id}");
    mysql_query("DELETE FROM {$db_prefix}vote_ballots WHERE id_vote={$this->id}");
    mysql_query("DELETE FROM {$db_prefix}vote_cast WHERE id_vote={$this->id}");
  }

  function IsPoll() {
    return $this->category == 'poll';
  }

  function GetResults() {
    if ($this->IsPoll()) {
      $this->GetPollResults();
    }
    else {
      $this->GetVoteResults();
    }

    $this->GetVoteComments();
  }

  function GetVoteResults() {
    global $db_prefix, $sourcedir, $ID_MEMBER;

    $equity = AllEquity();
    $this->contribution = $equity[$ID_MEMBER];

    $sql = "SELECT b.id_member, b.id_option, m.realName FROM {$db_prefix}vote_ballots as b, {$db_prefix}members as m WHERE id_vote={$this->id} AND b.id_member=m.id_member";
    $request = mysql_query($sql);
    while ($row = mysql_fetch_assoc($request)) {
      $idmem = $row['id_member'];
      $idopt = $row['id_option'];
      $realName = $row['realName'];

      if ($idopt > -1) {
	$this->ballots[$idopt] += $equity[$idmem];
      }
      $this->memberBallots[$idmem][] = $idopt;
      $this->repr[$idmem] = $equity[$idmem];
      $this->voters[$idmem] = $realName;
    }

    $foo = array_values($this->ballots);
    rsort($foo);
    $this->decisiveness = $foo[0]-$foo[1];

    $this->total = VectorSum($this->ballots);
    $this->quorumLevel = VectorSum($this->repr);
  }

  function GetPollResults() {
    global $db_prefix, $sourcedir, $ID_MEMBER;

    $equity = AllEquity();
    $this->contribution = 1;

    $sql = "SELECT b.id_member, b.id_option, m.realName FROM {$db_prefix}vote_ballots as b, {$db_prefix}members as m WHERE id_vote={$this->id} AND b.id_member=m.id_member";
    $request = mysql_query($sql);
    while ($row = mysql_fetch_assoc($request)) {
      $idmem = $row['id_member'];
      $idopt = $row['id_option'];
      $realName = $row['realName'];

      $this->ballots[$idopt]++;
      $this->memberBallots[$idmem][] = $idopt;
      $this->repr[$idmem] = 1;
      $this->voters[$idmem] = $realName;
    }

    $this->total = VectorSum($this->repr);
    $this->totalVoters = count($equity);
    $this->quorumLevel = 100 * $this->total/$this->totalVoters;
  }

  function GetVoteComments() {
    global $db_prefix;

    $this->comments = "<tr><td><b><u>voter</u></b></td><td><b><u>voted for</u></b></td><td width=300><b><u>comment</u></b></td></tr>\n";

    $sql = "SELECT * FROM {$db_prefix}vote_cast WHERE id_vote={$this->id} ORDER BY time";
    $request = mysql_query($sql);
    $n = 1;
    while ($row = mysql_fetch_assoc($request)) {
      $idmem = $row['id_member'];
      $time = $row['time'];
      $pubvote = $row['public_vote'];
      $pubcomment = $row['public_comment'];
      $comment = $row['comment'];

      $voter = $this->voters[$idmem];
      $votedfor = "";

      if ($pubvote) {
	$foo = $this->memberBallots[$idmem];
	if ($foo) {
	  sort($foo);

	  foreach($foo as $idopt) {
	    if($idopt == -1) {
	      $votedfor = "abstained";
	    }
	    else {
	      $votedfor .= "{$this->options[$idopt]}<br>";
	    }
	  }
	}
      }
      else {
	$votedfor = "private";
      }

      if (!$pubcomment) $comment = "no comment";

      $n++;

      if ($n % 2 == 1) {
	$this->comments .= "<tr class=windowbg>";
      }
      else {
	$this->comments .= "<tr class=windowbg2>";
      }
    
      $this->comments .= "<td>$voter</td><td>$votedfor</td><td>$comment</td></tr>\n";
    }
  }

  function Dump() {
    print "id = {$this->id}<br>\n";
    print "title = {$this->title}<br>\n";
    print "text = {$this->text}<br>\n";
    print "category = {$this->category}<br>\n";
    print "type = {$this->type}<br>\n";
    print "closed = {$this->closed}<br>\n";
    print "sponsor = {$this->sponsor}<br>\n";
    foreach($this->options as $opt) {
      print "option = $opt<br>\n";
    }
  }

  function OneChoice($control, $text, $value, $options) {
    $checked = in_array($value, $options) ? "checked" : "";
    return "<td><input type=$control name=ballot[] value=$value $checked>$text</input></td>";
  }

  function AllOptions($id_member) {
    global $db_prefix, $cgi;

    $id = $this->id;
    $type = $this->type;

    $sql = "SELECT * FROM {$db_prefix}vote_cast WHERE id_member=$id_member AND id_vote=$id";
    $request = mysql_query($sql);
    if ($row = mysql_fetch_assoc($request)) {
      $pubvote = $row['public_vote'] ? "checked" : "";
      $pubcomment = $row['public_comment'] ? "checked" : "";
      $comment = $row['comment'];
    }

    $sql = "SELECT * FROM {$db_prefix}vote_ballots WHERE id_member=$id_member AND id_vote=$id";
    $request = mysql_query($sql);
    $options = array();
    while ($row = mysql_fetch_assoc($request)) {
      $options[] = $row['id_option'];
    }
  
    if ($type == 3) {
      $all = "<tr>";
      $all .= $this->OneChoice("radio", "Yes", 1, $options);
      $all .= "</tr><tr>\n";
      $all .= $this->OneChoice("radio", "No", 0, $options);
      $all .= "</tr>\n";
      return $all;
    }

    if ($type == 4) {
      $all = "<tr>";
      $all .= $this->OneChoice("radio", "Agree", 1, $options);
      $all .= "</tr><tr>\n";
      $all .= $this->OneChoice("radio", "Disagree", 0, $options);
      $all .= "</tr>\n";
      return $all;
    }

    $sql = "SELECT * FROM {$db_prefix}vote_options WHERE id_vote=$id ORDER BY sort";
    $request = mysql_query($sql);
    $n = 0;
  
    $control = "radio";
    if ($type == 2) {
      $control = "checkbox";
    }

    while ($row = mysql_fetch_assoc($request)) {
      $n++;

      if ($n % 2 == 1) {
	$all .= "<tr class=windowbg>";
      }
      else {
	$all .= "<tr class=windowbg2>";
      }
    
      $text = $row['text'];
      $option = $row['id_option'];

      $all .= "<td align=right>$n</td>";
      $all .= $this->OneChoice($control, $text, $option, $options);
      $all .= "</tr>\n";
    }

    return array($all, $pubvote, $pubcomment, $comment);
  }

  function Cast($id_member, $ballot, $abstain, $pubvote, $pubcomment, $comment) {
    global $db_prefix;

    $idvote = $this->id;
    $time = time();
    $pubvote = $pubvote ? 1:0;
    $pubcomment = $pubcomment ? 1:0;
    $comment = mysql_escape_string($comment);

    print "Cast $comment<br>";

    mysql_query("DELETE from {$db_prefix}vote_cast WHERE id_member=$id_member and id_vote=$idvote");
    $sql = "INSERT INTO {$db_prefix}vote_cast (id_member,id_vote,time,public_vote,public_comment,comment) VALUES ($id_member,$idvote,$time,$pubvote,$pubcomment,'$comment')";
    print "sql = $sql<br>";
    mysql_query($sql);

    mysql_query("DELETE from {$db_prefix}vote_ballots WHERE id_member=$id_member and id_vote=$idvote");

    if ($abstain) {
      mysql_query("INSERT INTO {$db_prefix}vote_ballots (id_member,id_vote,id_option) VALUES ($id_member,$idvote,-1)");
    }
    else if ($ballot) {
      foreach($ballot as $option) {
	print "$ID_MEMBER $idvote $option<br>";
	mysql_query("INSERT INTO {$db_prefix}vote_ballots (id_member,id_vote,id_option) VALUES ($id_member,$idvote,$option)");
      }
    }
  }
}

function AllVotes()
{
  global $db_prefix, $cgi, $ID_MEMBER;

  $sql = "SELECT id_vote FROM {$db_prefix}vote_ballots WHERE id_member=$ID_MEMBER GROUP BY id_vote";
  $request = mysql_query($sql);
  while ($row = mysql_fetch_row($request)) {
    $voted[] = $row[0];
  }

  $sql = "SELECT v.id_vote, v.title, v.category, v.closed, m.memberName, m.realName FROM {$db_prefix}votes as v, {$db_prefix}members as m WHERE v.id_member=m.id_member ORDER BY category";
  $request = mysql_query($sql);
  $n = 0;
  
  $all = "<tr class=windowbg>";
  $all .= "<td>&nbsp;</td>";
  $all .= "<td align=center><b><u>Category</u></b></td>";
  $all .= "<td align=center><b><u>Title</u></b></td>";
  $all .= "<td align=center><b><u>Sponsor</u></b></td>";
  $all .= "<td align=center><b><u>Status</u></b></td>";
  $all .= "</tr>\n";

  while ($row = mysql_fetch_assoc($request)) {
    $n++;

    if ($n % 2 == 1) {
      $all .= "<tr class=windowbg>";
    }
    else {
      $all .= "<tr class=windowbg2>";
    }
    
    $id = $row['id_vote'];
    $title = $row['title'];
    $category = $row['category'];
    $votelink = "<a href=$cgi;action=voteResults;idvote=$id>$title</a>";
    $closed = $row['closed'];
    $memberName = $row['memberName'];
    $realName = $row['realName'];
    $profile = "$cgi;action=viewprofile;user=$memberName";
    $namelink = "<a href=$profile>$realName</a>";
    $status = $closed ? "<font color=red>closed</font>" : "<font color=limegreen>open</font>";

    if (!$closed) {
      if ($voted && in_array($id, $voted)) {
	$cmd = "revote";
      }
      else {
	$cmd = "vote now";
      }

      $status .= " (<a href=$cgi;action=voteCast;id=$id>$cmd</a>)";
    }

    if ($current != $category) {
      $current = $category;
      $cat = $category;
    }
    else {
      $cat = "&nbsp;";
    }

    $all .= "<td align=right>$n</td>";
    $all .= "<td>$cat</td>";
    $all .= "<td>$votelink</td>";
    $all .= "<td>$namelink</td>";
    $all .= "<td>$status</td>";
    $all .= "</tr>\n";
  }

  return $all;
}

function VoteIndex()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $ID_MEMBER;

  $yytitle = "Voting Index";
  template_header();

  $allvotes = AllVotes();

  $userRep = UserReputation($ID_MEMBER);
  if ($userRep > 6) {
    $addVote = "<tr><td colspan=3><a href=$cgi;action=voteEdit;idvote=0>Add new issue</a></td></tr>";
  }

print <<<EOT
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="80%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Votes</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<p>
<table align=center cellpadding=5 cellspacing=0 border=0>
$allvotes
$addVote
</table>
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
EOT;

  footer();
  obExit();
}

function VoteCast()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle,$id;
  global $ID_MEMBER;

  if($username == "Guest") { fatal_error("$txt[138]"); }

  $yytitle = "Voting Station $id";
  template_header();

  $vote = new Vote($id);
  list($choices,$pubvote,$pubcomment,$comment) = $vote->AllOptions($ID_MEMBER);

  $comment2 = htmlentities($comment);
  $comment3 = htmlspecialchars($comment);
  
print <<<EOT
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="80%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>{$vote->title}</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<p>
<form action="$cgi;action=voteSubmit" method="post">
<input type=hidden name=idvote value=$id>
<table align=center cellpadding=5 cellspacing=0 border=0>
<tr><td colspan=2 align=left>{$vote->text}</td><tr>
$choices
<tr><td colspan=2><hr></td></tr>
<tr>
  <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle colspan=2>
  <input type="checkbox" name="pubvote" $pubvote> Make my ballot public
  </td>
</tr>
<tr>
  <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle colspan=2>
  <input type="checkbox" name="pubcomment" $pubcomment> Make this comment public
  </td>
</tr>
<tr>
  <td bgcolor="$color[windowbg]" class="windowbg" align=left valign=middle colspan=2>
  <input type="text" name="comment" maxlength="255" size="80" value="$comment2"><br>
  (comment limited to 255 characters)
  </td>
</tr>
<tr>
  <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=middle colspan=2>
  <input type="submit" name=vote value="Vote">
  <input type="submit" name=vote value="Abstain">
  </td>
</tr>
</table>
</form>
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
EOT;

  footer();
  obExit();
}

function VoteSubmit()
{
  global $ID_MEMBER, $ballot, $idvote, $vote, $pubvote, $pubcomment, $comment;

  $vt = new Vote($idvote);
  $vt->Cast($ID_MEMBER, $ballot, $vote=='Abstain', $pubvote, $pubcomment, $comment);

  VoteResults();
}

function BarLine($barWide)
{
  global $imagesdir;

  $barLine = <<<EOT
<img src="$imagesdir/poll_left.gif" alt=""><img src="$imagesdir/poll_middle.gif" width="$barWide" height="12" alt=""><img src="$imagesdir/poll_right.gif" alt="">
EOT;

  return $barLine;
}

function BarRow($label, $count, $percent)
{
  global $imagesdir;

  //  $barLine = BarLine($percent);

  $barLine = "<img src=\"$imagesdir/poll_middle.gif\" width=\"$percent\" height=\"12\" alt=\"\">";
  $percent = sprintf("%.2f", $percent);

  return "<tr><td align=right>$label</td><td align=right>$count</td><td>$barLine ($percent%)</td></tr>\n";
}

function VoteResults()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $ID_MEMBER, $ballot, $idvote, $vote;

  $yytitle = "Vote Results";
  template_header();

  $vote = new Vote($idvote);
  $vote->GetResults();

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
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="80%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>{$vote->title}</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<p>
<table align=center cellpadding=5 cellspacing=0 border=0>
<tr><td colspan=3 align=left>{$vote->text}</td><tr>
<tr><td colspan=3 align=left><hr></td><tr>
EOT;

  foreach($vote->options as $opt => $text) {
    $ballot = $vote->ballots[$opt];
    $ballotp = $vote->total>0 ? 100 * $ballot/$vote->total : 0;

    if ($vote->IsPoll()) {
      $weight = $ballot ? $ballot:0;
    }
    else {
      $weight = sprintf("%.2f", $ballot);
    }

    print BarRow($text, $weight, $ballotp);
  }

  print "<tr><td colspan=3 align=left><hr></td><tr>\n";

  if ($vote->IsPoll()) {
    print BarRow("not voted", $vote->totalVoters - $vote->total, 100 - $vote->quorumLevel);
    print BarRow("voted", $vote->total, $vote->quorumLevel);
  }
  else {
    //    print BarRow("abstained", "", $vote->???);
    print BarRow("decisiveness", "", $vote->decisiveness);
    print BarRow("unvoted equity", "", 100 - $vote->quorumLevel);
    print BarRow("voted equity", "", $vote->quorumLevel);
  }

  print "<tr><td colspan=3 align=left><hr></td><tr>\n";

  $nv = count($vote->voters);
  print "<tr><td colspan=3 align=left>$nv Voters: ";
  foreach ($vote->voters as $voter) {
    print "$voter, ";
  }
  print "</td><tr>\n";
  print $vote->comments;

print <<<EOT
</table>
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
<p>
EOT;

 if (!$vote->contribution) {
   print "<center>Your vote will count for something when you join the <a href=$cgi;action=foo>reputation system</a></center><br>";
 }
 if ($ID_MEMBER==$vote->sponsor) {
   print "<center><a href=$cgi;action=voteEdit;idvote=$idvote>Edit this vote</a></center><br>";
   print "<center><a onclick=\"return confirmSubmit()\" href=$cgi;action=voteDelete;idvote=$idvote>Delete this vote</a></center><br>";
   if ($vote->closed==1) {
     print "<center><a href=$cgi;action=voteStatus;idvote=$idvote;closed=0>Open this vote</a></center><br>";
   }
   else {
     print "<center><a href=$cgi;action=voteStatus;idvote=$idvote;closed=1>Close this vote</a></center><br>";
   }
 }
 if (!$vote->closed) {
   print "<center><a href=$cgi;action=voteCast;id=$idvote>Vote on this issue</a></center><br>";
 }
 print "<center><a href=$cgi;action=voteIndex>Return to vote index</a></center>";

 footer();
 obExit();
}

function EditVote()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $idvote;

  $yytitle = "Edit Issue";
  template_header();

  $vote = new Vote($idvote);

  $cats = array("poll", "doctrine", "policy", "test");

  foreach($cats as $cat) {
    $selected = ($cat == $vote->category) ? "selected" : "";
    $catopts .= "<option $selected>$cat</option>";
  }

  if ($vote->type == 2) {
    $multiple = "checked";
  }
  else {
    $single = "checked";
  }

  foreach($vote->options as $opt) {
    $n++;
    $options .= "<tr><td>Option $n</td><td><input name=option[] size=40 value=\"$opt\"></td></tr>\n";
  }

  $n++;
  $options .= "<tr><td>Option $n</td><td><input name=option[] size=40></td></tr>\n";
  $n++;
  $options .= "<tr><td>Option $n</td><td><input name=option[] size=40></td></tr>\n";
  $n++;
  $options .= "<tr><td>Option $n</td><td><input name=option[] size=40></td></tr>\n";
  $n++;
  $options .= "<tr><td>Option $n</td><td><input name=option[] size=40></td></tr>\n";
  
  if ($idvote == 0) {
    $header = "Add a new issue";
  }
  else {
    $header = "Edit issue";
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
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>$header</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<form action="$cgi;action=voteEdit2" method="post">
<input type=hidden name=idvote value=$idvote>
<table align=center cellpadding=5 cellspacing=0 border=0>
<tr><td colspan=2 align=left>$text</td><tr>
<tr>
  <td>Category</td><td><select name=category>$catopts</select></td>
</tr>
<tr>
  <td>Title</td><td>
<input name=title size=40 maxlength=40 value="{$vote->title}"></td>
</tr>
<tr>
  <td>Question</td><td><textarea name=question rows=4 cols=40>{$vote->text}</textarea></td>
</tr>
<tr>
  <td>Selection</td>
  <td>
    <input type=radio name=type value=1 $single>single</input>
    <input type=radio name=type value=2 $multiple>multiple</input>
  </td>
</tr>
$options
<tr>
  <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=middle colspan=2>
  <font color=red>
  Warning: submitting form will erase all existing ballots!
  </font>
  </td>
</tr>
<tr>
  <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=middle colspan=2>
  <input type="submit" value="Submit" onClick="return confirmSubmit()">
  </td>
</tr>
</table>
</form>
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
EOT;

  footer();
  obExit();
}

function EditVote2()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $ID_MEMBER, $yySetLocation;
  global $idvote, $title, $question, $option, $type, $category;

  $yytitle = "New Issue";
  template_header();

  $vote = new Vote(0);

  $vote->sponsor = $ID_MEMBER;
  $vote->id = $idvote;
  $vote->title = $title;
  $vote->text = $question;
  $vote->category = $category;
  $vote->type = $type;
  $vote->closed = 1;

  foreach($option as $opt) {
    $vote->options[] = $opt;
  }

  $err = $vote->Save();

  if ($err) {
    fatal_error($err);
  }

  //  footer();
  //  obExit();
  $yySetLocation = "$cgi;action=voteResults;idvote={$vote->id}";
  redirectexit();
}

function VoteStatus()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $ID_MEMBER, $idvote, $closed, $yySetLocation;

  if($username == "Guest") { fatal_error("$txt[138]"); }

  $yytitle = "Vote Status";
  template_header();

  if ($ID_MEMBER && $idvote) {
    mysql_query("UPDATE {$db_prefix}votes SET closed=$closed WHERE id_vote=$idvote AND id_member=$ID_MEMBER");
  }
  else {
    fatal_error("incorrect parameters");
  }

  $yySetLocation = "$cgi;action=voteResults;idvote=$idvote";
  redirectexit();
}

function DeleteVote()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $ID_MEMBER, $idvote, $yySetLocation;

  if($username == "Guest") { fatal_error("$txt[138]"); }
  if(!$ID_MEMBER) { fatal_error("incorrect parameters"); }
  if(!$idvote) { fatal_error("incorrect parameters"); }

  $vote = new Vote($idvote);

  if ($vote->sponsor != $ID_MEMBER) { fatal_error("incorrect parameters"); }

  $vote->Delete();

  $yySetLocation = "$cgi;action=voteIndex";
  redirectexit();
}

function TopRankers($max)
{
  $sql = "SELECT m.realName, count(*) AS count FROM cov_ratings b, cov_members m WHERE b.id_member = m.id_member GROUP BY b.id_member ORDER BY count DESC limit $max";

  $request = mysql_query($sql);
  $n = 0;
  
  $rows = "<tr><td>&nbsp;</td><td><b><u>member</u></b></td><td align=center><b><u>ratings</u></b></td></tr>";

  while ($row = mysql_fetch_row($request)) {
    $nick = $row[0];
    $count = $row[1];
    
    $n++;
    
    if ($n % 2 == 1) {
      $rows .= "<tr class=windowbg>";
    }
    else {
      $rows .= "<tr class=windowbg2>";
    }
    
    $rows .= "<td align=right>$n</td><td>$nick</td><td>$count</td></tr>\n";
  }

  return $rows;
}

function TopRaters($max)
{
  $sql = "SELECT m.realName, count(*) AS count FROM cov_rating_ballots b, cov_members m WHERE b.id_member = m.id_member GROUP BY b.id_member ORDER BY count DESC limit $max";

  $request = mysql_query($sql);
  $n = 0;
  
  $rows = "<tr><td>&nbsp;</td><td><b><u>member</u></b></td><td align=center><b><u>ratings</u></b></td></tr>";

  while ($row = mysql_fetch_row($request)) {
    $nick = $row[0];
    $count = $row[1];
    
    $n++;
    
    if ($n % 2 == 1) {
      $rows .= "<tr class=windowbg>";
    }
    else {
      $rows .= "<tr class=windowbg2>";
    }
    
    $rows .= "<td align=right>$n</td><td>$nick</td><td>$count</td></tr>\n";
  }

  while ($n++ < $max) {
    $rows .= "<td align=right>$n</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
  }

  return $rows;
}

function TopSponsors($max)
{
  $sql = "SELECT m.realName, count(*) AS count FROM cov_rating_subjects s, cov_members m WHERE s.id_member = m.id_member GROUP BY s.id_member ORDER BY count DESC limit $max";

  $request = mysql_query($sql);
  $n = 0;
  
  $rows = "<tr><td>&nbsp;</td><td><b><u>member</u></b></td><td align=center><b><u>subjects</u></b></td></tr>";

  while ($row = mysql_fetch_row($request)) {
    $nick = $row[0];
    $count = $row[1];
    
    $n++;
    
    if ($n % 2 == 1) {
      $rows .= "<tr class=windowbg>";
    }
    else {
      $rows .= "<tr class=windowbg2>";
    }
    
    $rows .= "<td align=right>$n</td><td>$nick</td><td>$count</td></tr>\n";
  }

  while ($n++ < $max) {
    $rows .= "<td align=right>$n</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
  }

  return $rows;
}

function TopVoters($max)
{
  // only count open votes here
  $sql = "SELECT m.realName, count(*) as count FROM cov_vote_ballots b, cov_members m, cov_votes v where b.id_member=m.id_member AND b.id_vote=v.id_vote AND v.closed=0 group by b.id_member order by count desc limit $max"; 

  $request = mysql_query($sql);
  $n = 0;
  
  $rows = "<tr><td>&nbsp;</td><td><b><u>member</u></b></td><td align=center><b><u>votes</u></b></td></tr>";

  while ($row = mysql_fetch_row($request)) {
    $nick = $row[0];
    $count = $row[1];
    
    $n++;
    
    if ($n % 2 == 1) {
      $rows .= "<tr class=windowbg>";
    }
    else {
      $rows .= "<tr class=windowbg2>";
    }
    
    $rows .= "<td align=right>$n</td><td>$nick</td><td>$count</td></tr>\n";
  }

  return $rows;
}

function Meridion()
{
    global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;

    $yytitle = "Meridion";
    template_header();

    $nick = str_replace(" ", "_", $settings[1]);

    $max = 20;
    $topRankers = TopRankers($max);
    $topRaters = TopRaters($max);
    $topVoters = TopVoters($max);
    $topSponsors = TopSponsors($max);

print <<<EOT

<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor" width=100%>
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Meridion</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<p>
<a href="http://virus.lucifer.com/wiki/Meridion">Meridion</a> is the CoV reputation system. We rate each other to determine a quantitative influence measure
which is then used to weight our votes and ratings.
<ul>
<li> View and vote on <a href="$cgi;action=repIndex2">member reputations</a>.
<li> View and vote on <a href="$cgi;action=voteIndex">policy and doctrine</a>.
<li> View and rate <a href="$cgi;action=rateIndex">books, web sites, people, organizations and more</a>.
</ul>
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>

<p>

<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td bgcolor="$color[windowbg]" class="windowbg">

    <table border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Top $max Raters (rep)</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<table>
$topRankers
</table>
        </td>
      </tr>
    </table>
    </td>

    <td>
    <table border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Top $max Voters</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<table>
$topVoters
</table>
        </td>
      </tr>
    </table>
    </td>

    <td>
    <table border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Top $max Raters</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<table>
$topRaters
</table>
        </td>
      </tr>
    </table>
    </td>

    <td>
    <table border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Top $max Sponsors</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<table>
$topSponsors
</table>
        </td>
      </tr>
    </table>
    </td>

  </tr>
</table>

EOT;

footer();
obExit();
}

?>
