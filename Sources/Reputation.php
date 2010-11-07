<?php

$notifyplver="YaBB SE 1.3.1";

function AllUsers()
{
  global $db_prefix;

  // inner join with members table to automatically weed out deleted members
  $sql = "SELECT r.id_member, realName FROM cov_ratings AS r, cov_members AS m WHERE r.id_member=m.id_member GROUP BY id_member";
  $request = mysql_query($sql);

  while ($row = mysql_fetch_row($request)) {
    $users[] = $row[0];
  }

  return $users;
}

function UserCount()
{
  return count(AllUsers());
}

function AllRatings()
{
  global $db_prefix;
  
  $users = AllUsers();

  foreach($users as $user) {
    foreach ($users as $other) {
      $ratings[$other][$user] = 5;
    }	
  }

  $request = mysql_query("SELECT * FROM {$db_prefix}ratings");

  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $other = $row[1];
    $rating = $row[2];
    $ratings[$other][$id] = $rating;
  }

  return $ratings;
}

function Recalculate()
{
  global $db_prefix;

  $users = AllUsers();
  $ratings = AllRatings();

  mysql_query("DELETE FROM {$db_prefix}reputation");

  foreach($users as $user) {
    $sum = 0;
    $n = 0;
    foreach($users as $other) {
      $sum += $ratings[$other][$user];
      $n++;
    }
    $rep = $sum/$n;
    mysql_query("INSERT INTO {$db_prefix}reputation (id_member,reputation) VALUES ($user,$rep)");
    print "user $user got a reputation of $rep<br>\n";
  }
}

function Weights($reps)
{
  $min = 1;
  $max = 9;
  $count = count($reps);
  $mag = 2*log($count)/log(10);

  if ($mag>0) {
    $scale = ($max-$min)/$mag;
  }
  else {
    $scale = 1;
  }

  foreach($reps as $user => $rep) {
    $weights[$user] = pow(10, ($reps[$user]-$min)/$scale);
  }

  return VectorDivide($weights, $count);
}

function DotProduct($v1, $v2) 
{
  $dp = 0;

  foreach($v1 as $id => $val) {
    $dp += $val * $v2[$id];
  }

  return $dp;
}

function VectorSum($v) 
{
  $sum = 0;

  foreach(array_values($v) as $val) {
    $sum += $val;
  }

  return $sum;
}

function VectorDivide($v, $div)
{
  foreach($v as $id => $val) {
    $new[$id] = 1.0*$val/$div;
  }

  return $new;
}

function VectorDiff($v1, $v2)
{
  foreach($v1 as $id => $val) {
    $diff[$id] = $val - $v2[$id];
  }

  return $diff;
}

function VectorNorm($v)
{
  $sum = 0;

  foreach(array_values($v) as $val) {
    $sum += $val * $val;
  }

  return sqrt($sum);
}

function MatrixMultiply($vec, $matrix) 
{
  foreach($matrix as $id => $vec2) {
    $res[$id] = DotProduct($vec, $vec2);
  }

  return $res;
}

function PrintVector($v, $name)
{
  foreach($v as $key=>$val) {
    print "$name";
    print "[$key] = $val<br>\n";
  }
}

function RecalculateReps()
{
  global $db_prefix;

  $users = AllUsers();
  $ratings = AllRatings();

  foreach($users as $user) {
    $reps[$user] = 5;
  }

  for($i=0; $i<10; $i++) {
    $weights = Weights($reps);
    $foo = MatrixMultiply($weights, $ratings);
    $bar = VectorDivide($foo, VectorSum($weights));
    $diff = VectorDiff($reps, $bar);
    $norm = VectorNorm($diff);

    /*
    PrintVector($reps, "reps");
    PrintVector($weights, "weights");
    PrintVector($foo, "foo");
    PrintVector($bar, "bar");
    */

    print "$i norm = $norm<br>\n";

    if ($norm < 0.001) {
      break;
    }

    $reps = $bar;
  }

  mysql_query("DELETE FROM {$db_prefix}reputation");

  foreach($users as $user) {
    mysql_query("INSERT INTO {$db_prefix}reputation (id_member,reputation) VALUES ($user,$reps[$user])");
    print "user $user got a reputation of $reps[$user]<br>\n";
  }
}

function UserReputation($id_member)
{
  global $db_prefix;
  
  $request = mysql_query("SELECT reputation FROM {$db_prefix}reputation WHERE id_member=$id_member");

  if ($row = mysql_fetch_row($request)) {
    return $row[0];
  }
  else {
    return 0;
  }
}

function AllReputation()
{
  global $db_prefix;
  
  $request = mysql_query("SELECT * FROM {$db_prefix}reputation");
  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $rep = $row[1];
    $reps[$id] = $rep;
  }

  return $reps;
}

function AllInfluence()
{
  $reps = AllReputation();

  return Weights($reps);
}

function AllEquity()
{
  $weights = AllInfluence();
  $equity = VectorDivide($weights, VectorSum($weights)/100);

  return $equity;
}

function AllByRep()
{
  global $db_prefix, $cgi;
  global $sortby;

  if (!$sortby) {
    $sortby = "realName";
  }

  $sql = "SELECT m.id_member, m.realName, m.memberName, r.reputation FROM {$db_prefix}reputation as r, {$db_prefix}members as m WHERE r.id_member=m.id_member ORDER BY $sortby";

  $sql = "SELECT m.id_member, m.realName, m.memberName, rep.reputation, count(rating) as ratings, avg(rating) as average, rep.reputation-avg(rating) as diff FROM cov_reputation rep left outer join cov_ratings rat on (rep.id_member=rat.id_other), cov_members m where (rep.id_member=m.id_member) group by rat.id_other order by diff desc";

  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];

    $ids[] = $id;
    $realNames[$id] = $row[1];
    $memberNames[$id] = $row[2];
    $reps[$id] = $row[3];
    $rating_count[$id] = $row[4];
    $rating_avg[$id] = $row[5];
  }

  $weights = Weights($reps);
  $equity = VectorDivide($weights, VectorSum($weights)/100);
  $count = count($weights);

  foreach($ids as $id)  {
    $n++;
    
    if ($n % 2 == 1) {
      $all .= "<tr class=windowbg>";
    }
    else {
      $all .= "<tr class=windowbg2>";
    }
    
    $memberName = str_replace(" ", "%20", $memberNames[$id]);
    $profile = "$cgi;action=viewprofile;user=$memberName";
    $namelink = "<a href=$profile>$realNames[$id]</a>";
    $rep = sprintf("%1.4f", $reps[$id]);
    $replink = "<a href=$cgi;action=repProfile;userID=$id>$rep</a>";
    $inf = sprintf("%1.2f", $weights[$id]);
    $inflink = "<a href=http://www.google.com/search?q=e^(2*ln($count)*($rep-5)/(9-1))>$inf</a>";
    $equ = sprintf("%1.2f", $equity[$id]);
    $count = $rating_count[$id];
    $average = $rating_avg[$id];

    $all .= "<td align=right>$n</td><td>$namelink</td>";
    $all .= "<td align=right>$replink&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$inflink&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$equ&nbsp;&nbsp;</td></tr>\n";
    $all .= "<td align=right>$count&nbsp;&nbsp;</td></tr>\n";
    $all .= "<td align=right>$average&nbsp;&nbsp;</td></tr>\n";
  }

  if ($n == 0) {
    $all = "<tr class=windowbg><td>No one has joined yet</td></tr>";
  }
  else {
    $sumRep = VectorSum($reps);
    $avgRep = $sumRep/$count;
    $avgRep = sprintf("%.4f", $avgRep);
    $sumRep = sprintf("%.4f", $sumRep);

    $sumInf = VectorSum($weights);
    $avgInf = $sumInf/$count;
    $avgInf = sprintf("%.2f", $avgInf);
    $sumInf = sprintf("%.2f", $sumInf);

    $sumEqu = VectorSum($equity);
    $avgEqu = $sumEqu/$count;
    $avgEqu = sprintf("%.2f", $avgEqu);
    $sumEqu = sprintf("%.2f", $sumEqu);

    $all .= "<tr><td colspan=5><hr></td></tr>\n";

    $all .= "<tr><td></td><td align=left>mean</td>";
    $all .= "<td align=right>$avgRep&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$avgInf&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$avgEqu&nbsp;&nbsp;</td></tr>\n";

    $all .= "<tr><td></td><td align=left>total</td>";
    $all .= "<td align=right>&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$sumInf&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$sumEqu&nbsp;&nbsp;</td></tr>\n";
  }

  return $all;
}

function AllChoices()
{
  global $db_prefix, $ID_MEMBER, $joinedRep, $settings, $cgi;

  $request = mysql_query("SELECT * FROM {$db_prefix}ratings WHERE id_member=$ID_MEMBER");

  while ($row = mysql_fetch_row($request)) {
    $other = $row[1];
    $rating = $row[2];
    $ratings[$other] = $rating;
  }

  $sql = "SELECT m.id_member, m.memberName, m.realName, r.reputation FROM {$db_prefix}reputation as r, {$db_prefix}members as m WHERE r.id_member=m.id_member ORDER BY m.realName";
  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $member = $row[1];
    $name = $row[2];
    $rep = $row[3];
    
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

    $namelink = "<a href=$cgi;action=viewprofile;user=$member target=_blank>$name</a>";

    if ($ratings[$id]) {
      $all .= "<td align=right>$n</td><td>$namelink</td>";
    }
    else {
      $all .= "<td align=right><font color=yellow>new! $n</font></td><td>$namelink</td>";
    }
    
    for ($i = 1; $i < 10; $i++) {
      if ($ratings[$id] == $i) {
	$all .= "<td><input type=radio name=$id value=$i checked></td>";
      }
      else {
	$all .= "<td><input type=radio name=$id value=$i></td>";
      }
    }

    $all .= "</tr>\n";
  }

  if (!$joinedRep) {
    $n++;
    $name = $settings[1];
    $id = $ID_MEMBER;

    $all .= "<td align=right>$n</td><td>$name</td><td align=right>";
    
    for ($i = 1; $i < 8; $i++) {
      $all .= "<td><input type=radio name=$id value=$i></td>";
    }

    $all .= "</td></tr>\n";
  }

  if ($n == 0) {
    $all = "<tr class=windowbg><td>No one has joined yet</td></tr>";
  }

  return $all;
}

function Reputation()
{
  global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
  global $joinedRep;

  $yytitle = "Reputation";
  template_header();

  $all = AllByRep();

  print <<<EOT
<form action="$cgi;action=repRate" method="post">
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="80%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Active Users</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<p>
<table align=center>
<tr><td>&nbsp;</td><td><b><u>member</u></b></td><td align=center><b><u>reputation</u></b></td><td align=center><b><u>influence</u></b></td><td align=center><b><u>equity</u></b></td></tr>
$all
</table>
        </td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=middle>
    <input type="submit" value="Enter Ratings">
    </td>
  <tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2" valign=middle>
<br>

<table align=center width=50%>
<tr>
<td valign=top>Reputation</td>
<td>The average rating weighted by each member's influence.<p></td>
</tr>

<tr>
<td valign=top>Influence</td>
<td>How much weight the member's opinion carries.
This is calculated from the reputation such that in a community of N members
a level-1 will have 1 vote, a level-5 will have N votes and a level-9 will have N*N votes.
<p>
</td>
</tr>

<tr>
<td valign=top>Equity</td>
<td>The percentage of the total influence wielded by this member.<p></td>
</tr>
</table>

    </td>
  </tr>												       
  </tr>
</table>
</form>
EOT;

  footer();
  obExit();
}

function ProfileHistogram($field, $id)
{
  global $db_prefix, $imagesdir;

  $userCount = UserCount();

  $sql = "SELECT rating FROM {$db_prefix}ratings WHERE $field=$id";
  $request = mysql_query($sql);
  $count = mysql_num_rows($request);
  while ($row = mysql_fetch_row($request)) {
    $rating = $row[0];
    $ratings[$rating]++;
  }

  for ($i = 1; $i < 10; $i++) {
    $n = $ratings[$i];
    if ($count > 0) {
      $barWide = 100 * $n/$count;
    }
    else {
      $barWide = 0;
    }
    $percent = sprintf("%.2f", $barWide);
    $barLine = "<img src=\"$imagesdir/poll_middle.gif\" width=\"$barWide\" height=\"12\" alt=\"\">";
    $profile .= "<tr><td align=right>$i</td><td align=right>$n</td><td>$barLine ($percent%)</td></tr>";
  }
  
  $barWide = 100 * $count/$userCount;
  $percent = sprintf("%.2f", $barWide);
  $barLine = "<img src=\"$imagesdir/poll_middle.gif\" width=\"$barWide\" height=\"12\" alt=\"\">";
  $profile .= "<tr><td colspan=3><hr></td></tr>";
  $profile .= "<tr><td>total</td><td align=right>$count</td><td>$barLine ($percent%)</td></tr>";

  return $profile;
}

function Profile()
{
  global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
  global $userID;

  $yytitle = "Reputation Profile";
  template_header();

  $sql = "SELECT memberName, realName FROM {$db_prefix}members WHERE id_member=$userID";
  $request = mysql_query($sql);
  if ($row = mysql_fetch_row($request)) {
    $memberName = $row[0];
    $realName = $row[1];
  }

  $namelink = "<a href=$cgi;action=viewprofile;user=$memberName>{$realName}'s BBS profile</a>";

  $header1 = "How $realName rated others";
  $header2 = "How others rated $realName";

  $profile1 = ProfileHistogram("id_member", $userID);
  $profile2 = ProfileHistogram("id_other", $userID);

  print <<<EOT
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="80%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>$header1</b></font></td>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>$header2</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<table align=center>
$profile1
</table>
        </td>
        <td bgcolor="$color[windowbg]" class="windowbg">
<table align=center>
$profile2
</table>
        </td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg]" class="windowbg" align=center valign=middle>
<br>
View {$namelink} (access recent posts)    
<p>
Return to <a href=$cgi;action=repIndex>Reputation Index</a>
<p>
    </td>
</table>
EOT;

  footer();
  obExit();
}

function EnterRatings()
{
  global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
  global $joinedRep;

  if($username == "Guest") { fatal_error("$txt[138]"); }
  $yytitle = "Reputation";
  template_header();

  $all = AllChoices();

  print <<<EOT
<form action="$cgi;action=repSubmit" method="post">
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
  <td>&nbsp;</td><td><b><u>member</u></b></td><td align=left colspan=2>&lt;-bad</td><td align=center colspan=5><b><u>rating</u></b></td><td align=right colspan=2>good-&gt;</td>
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
    </td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2" valign=middle>
<p>
<br>
<center><u>Meanings of Ratings</u><br></center>
<ol>
<li>This person should be banned
<li>Little or no redeeming value
<li>Strongly negative opinion
<li>Slight negative opinion
<li>Neutral or no opinion
<li>Slight positive opinion
<li>Strong positive opinion
<li>A credit to the the community
<li>A pillar of the community
</ol>
    </td>
  </tr>												       
</table>
EOT;

  footer();
  obExit();
}

function SubmitRatings()
{
  global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
  global $yySetLocation, $cgi;
  global $r1, $r2, $r3;

  if($username == "Guest") { fatal_error("$txt[138]"); }
  $yytitle = "Reputation";
  template_header();

  mysql_query("DELETE from {$db_prefix}ratings WHERE id_member=$ID_MEMBER");
  foreach($_POST as $id => $rating) {
    print "$ID_MEMBER gave $id a $rating<br>\n";
    mysql_query("INSERT INTO {$db_prefix}ratings (id_member,id_other,rating) VALUES ($ID_MEMBER,$id,$rating)");
  }


  RecalculateReps();

  $yySetLocation = "$cgi;action=repIndex";
  redirectexit();
}

?>
