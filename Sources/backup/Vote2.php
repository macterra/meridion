<?php

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
  var $repr;
  var $total;
  var $quorumLevel;
  var $decisiveness;

  function Vote($idvote) {
    global $db_prefix;

    $this->id = $idvote;
    $this->options = array();
    $this->ballots = array();
    $this->repr = array();

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

    if ($this->id == 0) {
      $sql = "INSERT INTO {$db_prefix}votes (id_member,title,text,type,closed,category) ";
      $sql .= "VALUES ({$this->sponsor},'{$this->title}','{$this->text}',{$this->type},{$this->closed},'{$this->category}')";
      print "$sql<br>\n";
      mysql_query($sql);
      $this->id = mysql_insert_id();
    }
    else {
      $sql = "UPDATE {$db_prefix}votes SET ";
      $sql .= "title='{$this->title}',";
      $sql .= "text='{$this->text}',";
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
    }

    $n = 0;
    foreach($this->options as $opt) {
      $n++;
      if ($opt) {
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
  }

  function GetResults() {
    global $db_prefix, $sourcedir;

    include_once "$sourcedir/Reputation.php";
    $equity = AllEquity();

    $sql = "SELECT * FROM {$db_prefix}vote_ballots WHERE id_vote={$this->id}";
    $request = mysql_query($sql);
    while ($row = mysql_fetch_assoc($request)) {
      $idmem = $row['id_member'];
      $idopt = $row['id_option'];

      $this->ballots[$idopt] += $equity[$idmem];
      $this->repr[$idmem] = $equity[$idmem];
    }

    $foo = array_values($this->ballots);
    if (count($foo) > 1) {
      rsort($foo);
      $this->decisiveness = $foo[0]-$foo[1];
    }

    $this->total = VectorSum($this->ballots);
    $this->quorumLevel = VectorSum($this->repr);
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
      if (in_array($id, $voted)) {
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

function OneChoice($control, $text, $value, $options) 
{
  $checked = in_array($value, $options) ? "checked" : "";
  return "<td><input type=$control name=ballot[] value=$value $checked>$text</input></td>";
}

function AllOptions($id, $type)
{
  global $db_prefix, $cgi, $ID_MEMBER;

  $sql = "SELECT * FROM {$db_prefix}vote_ballots WHERE id_member=$ID_MEMBER AND id_vote=$id";
  $request = mysql_query($sql);
  $options = array();
  while ($row = mysql_fetch_assoc($request)) {
    $options[] = $row['id_option'];
  }
  
  if ($type == 3) {
    $all = "<tr>";
    $all .= OneChoice("radio", "Yes", 1, $options);
    $all .= "</tr><tr>\n";
    $all .= OneChoice("radio", "No", 0, $options);
    $all .= "</tr>\n";
    return $all;
  }

  if ($type == 4) {
    $all = "<tr>";
    $all .= OneChoice("radio", "Agree", 1, $options);
    $all .= "</tr><tr>\n";
    $all .= OneChoice("radio", "Disagree", 0, $options);
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
    $all .= OneChoice($control, $text, $option, $options);
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

  include_once "$sourcedir/Reputation.php";
  $userRep = UserReputation($ID_MEMBER);
  if ($userRep > 7) {
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

  if($username == "Guest") { fatal_error("$txt[138]"); }

  $yytitle = "Voting Station $id";
  template_header();

  $request = mysql_query("SELECT * from {$db_prefix}votes WHERE id_vote=$id");
  $row = mysql_fetch_assoc($request);
  $title = $row['title'];
  $text = $row['text'];
  $type = $row['type'];
  $choices = AllOptions($id, $type);

print <<<EOT
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="80%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>$title</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<p>
<form action="$cgi;action=voteSubmit" method="post">
<input type=hidden name=idvote value=$id>
<table align=center cellpadding=5 cellspacing=0 border=0>
<tr><td colspan=2 align=left>$text</td><tr>
$choices
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
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $ID_MEMBER, $ballot, $idvote, $vote;

  mysql_query("DELETE from {$db_prefix}vote_ballots WHERE id_member=$ID_MEMBER and id_vote=$idvote");

  if ($vote == 'Abstain') {
    mysql_query("INSERT INTO {$db_prefix}vote_ballots (id_member,id_vote,id_option) VALUES ($ID_MEMBER,$idvote,-1)");
  }
  else if ($ballot) {
    foreach($ballot as $option) {
      print "$ID_MEMBER $idvote $option<br>";
      mysql_query("INSERT INTO {$db_prefix}vote_ballots (id_member,id_vote,id_option) VALUES ($ID_MEMBER,$idvote,$option)");
    }
  }

  VoteResults();
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
    $ballotp = 100 * $ballot/$vote->total;
    $weight = sprintf("%.2f", $ballot);

    if ($vote->total == 0) {
      $percent = "0.00";
      $barWide = 0;
    }
    else {
      $percent = sprintf("%.2f", $ballotp);
      $barWide = $ballotp;
    }
    $barLine = "<img src=\"$imagesdir/poll_left.gif\" alt=\"\"><img src=\"$imagesdir/poll_middle.gif\" width=\"$barWide\" height=\"12\" alt=\"\"><img src=\"$imagesdir/poll_right.gif\" alt=\"\">";
    print "<tr>";
    print "<td>$text</td><td align=right>$weight</td>";
    print "<td>$barLine ($percent%)</td>";
    print "</tr>\n";
  }

  print "<tr><td colspan=3 align=left><hr></td><tr>\n";

  $percent = sprintf("%.2f", $vote->decisiveness);
  $barWide = $vote->decisiveness;
  $barLine = "<img src=\"$imagesdir/poll_left.gif\" alt=\"\"><img src=\"$imagesdir/poll_middle.gif\" width=\"$barWide\" height=\"12\" alt=\"\"><img src=\"$imagesdir/poll_right.gif\" alt=\"\">";
  print "<tr>";
  print "<td colspan=2>decisiveness</td>";
  print "<td>$barLine ($percent%)</td>";
  print "</tr>\n";

  $unvoted = 100 - $vote->quorumLevel;
  $percent = sprintf("%.2f", $unvoted);
  $barWide = $unvoted;
  $barLine = "<img src=\"$imagesdir/poll_left.gif\" alt=\"\"><img src=\"$imagesdir/poll_middle.gif\" width=\"$barWide\" height=\"12\" alt=\"\"><img src=\"$imagesdir/poll_right.gif\" alt=\"\">";
  print "<tr>";
  print "<td colspan=2>unvoted equity</td>";
  print "<td>$barLine ($percent%)</td>";
  print "</tr>\n";

  $percent = sprintf("%.2f", $vote->quorumLevel);
  $barWide = $vote->quorumLevel;
  $barLine = "<img src=\"$imagesdir/poll_left.gif\" alt=\"\"><img src=\"$imagesdir/poll_middle.gif\" width=\"$barWide\" height=\"12\" alt=\"\"><img src=\"$imagesdir/poll_right.gif\" alt=\"\">";
  print "<tr>";
  print "<td colspan=2>quorum level</td>";
  print "<td>$barLine ($percent%)</td>";
  print "</tr>\n";

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

 if (!$equity[$ID_MEMBER]) {
   print "<center>Your vote will count for something when you join the <a href=$cgi;action=foo>reputation system</a></center><br>";
 }
 if ($ID_MEMBER==$sponsor) {
   print "<center><a href=$cgi;action=voteEdit;idvote=$idvote>Edit this vote</a></center><br>";
   print "<center><a onclick=\"return confirmSubmit()\" href=$cgi;action=voteDelete;idvote=$idvote>Delete this vote</a></center><br>";
   if ($status==1) {
     print "<center><a href=$cgi;action=voteStatus;idvote=$idvote;closed=0>Open this vote</a></center><br>";
   }
   else {
     print "<center><a href=$cgi;action=voteStatus;idvote=$idvote;closed=1>Close this vote</a></center><br>";
   }
 }
 if ($status != 1) {
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
  <td>Category</td><td><input name=category size=20 value="{$vote->category}"></td>
</tr>
<tr>
  <td>Title</td><td><input name=title size=40 value="{$vote->title}"></td>
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

?>
