<?php

$notifyplver="YaBB SE 1.3.1";

function AllUsers()
{
  global $db_prefix;

  $request = mysql_query("SELECT id_member FROM {$db_prefix}ratings GROUP BY id_member");

  while ($row = mysql_fetch_row($request)) {
    $users[] = $row[0];
  }

  return $users;
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

function Reputations()
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

function VoteFactors()
{
  global $db_prefix;

  $request = mysql_query("SELECT id_member, count(rating) FROM {$db_prefix}ratings GROUP BY id_member");
  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $count = $row[1];
    $vf[$id] = $count;
  }

  return VectorDivide($vf, count($vf));
}

function AllInfluence()
{
  $reps = Reputations();
  $weights = Weights($reps);
  $vf = VoteFactors();

  foreach($weights as $id => $inf) {
    $influence[$id] = $weights[$id]*$vf[$id];
  }

  return $influence;
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

  $sql = "SELECT m.id_member, m.realName, m.memberName, r.reputation FROM {$db_prefix}reputation as r, {$db_prefix}members as m WHERE r.id_member=m.id_member ORDER BY m.realName";
  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];

    $ids[] = $id;
    $realNames[$id] = $row[1];
    $memberNames[$id] = $row[2];
    $reps[$id] = $row[3];
  }

  $vf = VoteFactors();
  $weights = AllInfluence();
  $equity = VectorDivide($weights, VectorSum($weights)/100);

  foreach($ids as $id)  {
    $n++;
    
    if ($n % 2 == 1) {
      $all .= "<tr class=windowbg>";
    }
    else {
      $all .= "<tr class=windowbg2>";
    }
    
    $profile = "$cgi;action=viewprofile;user=$memberNames[$id]";
    $namelink = "<a href=$profile>$realNames[$id]</a>";
    $rep = sprintf("%1.4f", $reps[$id]);
    $vot = sprintf("%1.2f", 100*$vf[$id]);
    $inf = sprintf("%1.2f", $weights[$id]);
    $equ = sprintf("%1.2f", $equity[$id]);

    $all .= "<td align=right>$n</td><td>$namelink</td>";
    $all .= "<td align=right>$rep&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$vot&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$inf&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$equ&nbsp;&nbsp;</td></tr>\n";
  }

  if ($n == 0) {
    $all = "<tr class=windowbg><td>No one has joined yet</td></tr>";
  }

  return $all;
}

function AllChoices()
{
  global $db_prefix, $ID_MEMBER, $joinedRep, $settings;

  $request = mysql_query("SELECT * FROM {$db_prefix}ratings WHERE id_member=$ID_MEMBER");

  while ($row = mysql_fetch_row($request)) {
    $other = $row[1];
    $rating = $row[2];
    $ratings[$other] = $rating;
  }

  $sql = "SELECT m.id_member, m.realName, r.reputation FROM {$db_prefix}reputation as r, {$db_prefix}members as m WHERE r.id_member=m.id_member ORDER BY r.reputation DESC";
  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $name = $row[1];
    $rep = $row[2];
    
    $n++;
    
    if ($n % 2 == 1) {
      $all .= "<tr class=windowbg>";
    }
    else {
      $all .= "<tr class=windowbg2>";
    }
    
    if ($ID_MEMBER == $id) {
      $joinedRep = 1;
    }

    if ($ratings[$id]) {
      $all .= "<td align=right>$n</td><td>$name</td>";
    }
    else {
      $all .= "<td align=right>$n</td><td><font color=yellow>$name</font></td>";
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
reminder: When we reached 35 members all the ratings were reset and we started over.
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
<tr align=center>
  <td>&nbsp;</td><td>&nbsp;</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td>
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
<li>I tend to disagree with them
<li>Slight negative opinion
<li>Neutral or no opinion
<li>Slight positive opinion
<li>I tend to agree with them
<li>Generally excellent contributions
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
