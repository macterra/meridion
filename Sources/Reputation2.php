<?php

$notifyplver="YaBB SE 1.3.1";

include_once "$sourcedir/Matrix.php";
include_once "$sourcedir/Meridion.php";

// Returns full matrix of ratings with 5s filled in for missing ratings
function AllRatings()
{
  global $db_prefix;
  
  $users = AllUsers();

  foreach($users as $user) {
    foreach ($users as $other) {
      $ratings[$other][$user] = 5;
    }	
  }

  // filter out ratings of deleted members
  $request = mysql_query("SELECT r.*, realName FROM cov_ratings r, cov_members m where r.id_other=m.id_member");

  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $other = $row[1];
    $rating = $row[2];
    $ratings[$other][$id] = $rating;
  }

  return $ratings;
}

// Returns table of entered ratings
function AllRatings2()
{
  global $db_prefix;
  
  $users = AllUsers();

  // filter out ratings of deleted members
  $request = mysql_query("SELECT r.*, realName FROM cov_ratings r, cov_members m where r.id_other=m.id_member");

  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $other = $row[1];
    $rating = $row[2];
    $ratings[$other][$id] = $rating;
  }

  return $ratings;
}

function Influence($weights, $activity)
{
  foreach($weights as $user => $weight) {
    //    $inf[$user] = $weight * log(1 + $activity[$user])/log(2);
    $inf[$user] = $weight * $activity[$user];
  }

  return $inf;
}

function AllActivity()
{
  global $db_prefix;
  
  $request = mysql_query("SELECT * FROM {$db_prefix}mdn_activity");

  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $level = $row[1];
    $msgActivity[$id] = $level;
    $ids[$id] = $id;
  }

  $sql = "SELECT id_member, sum(lcount) as irclines FROM cov_mdn_irc_activity a, cov_irc_nicks n where a.nick=n.nick group by id_member";
  $request = mysql_query($sql);

  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $lines = $row[1];
    $ircActivity[$id] = $lines;
    $ids[$id] = $id;
  }

  foreach($ids as $id) {
    //    $activity[$id] = 1 + $msgActivity[$id] + $ircActivity[$id]/100;
    $activity[$id] = log(1 + $msgActivity[$id] + $ircActivity[$id]/100)/log(2);
  }

  return $activity;
}

// Recalcuates all reputations from ratings
function RecalculateReps()
{
  global $db_prefix;

  $users = AllUsers();
  $ratings = AllRatings();
  $activity = AllActivity();

  foreach($users as $user) {
    $reps[$user] = 5;
  }

  $repscount = count($reps);
  print "reps count $repscount\n";

  for($i=0; $i<20; $i++) {
    $weights = Weights($reps);
    $infs = Influence($weights, $activity);
    $foo = MatrixMultiply($infs, $ratings);
    $newreps = VectorDivide($foo, VectorSum($infs));
    $diff = VectorDiff($reps, $newreps);
    $norm = VectorNorm($diff);

    /*
    PrintVector($reps, "reps");
    PrintVector($weights, "weights");
    PrintVector($infs, "infs");
    PrintVector($foo, "foo");
    PrintVector($newreps, "newreps");
    */

    $equity = VectorDivide($infs, VectorSum($infs)/100);

    print "$i norm = $norm<br>\n";

    if ($norm < 0.001) {
      break;
    }

    $reps = $newreps;
  }

  mysql_query("DELETE FROM {$db_prefix}reputation");

  foreach($users as $user) {
    $rep = $reps[$user];
    $act = $activity[$user];
    $inf = $infs[$user];
    $equ = $equity[$user];

    if (!isset($act)) {
      $act = 0;
    }

    $sql = "INSERT INTO {$db_prefix}reputation (id_member,reputation,activity,influence,equity) VALUES ($user,$rep,$act,$inf,$equ)";
    $res = mysql_query($sql);
    print "$res $sql\n";
    print "user $user got a reputation of $reps[$user]<br>\n";
  }

  $usercount = count($users);
  print "user count $usercount\n";
}

function Timestamp2Date($timestamp)
{
    $year = substr($timestamp, 0, 4);
    $month = substr($timestamp, 4, 2);
    $day = substr($timestamp, 6, 2);
    $date = date('M d, Y', mktime(0, 0, 0, $month, $day, $year));

    return $date;
}

function TimeAgo($stamp) {
  $secs = time()-$stamp;

  if ($secs < 60) {
    return sprintf("%ds", $secs);
  }

  $mins = $secs/60;

  if ($mins < 60) {
    return sprintf("%dm", $mins);
  }

  $hours = $mins/60;

  if ($hours < 24) {
    return sprintf("%dh", $hours);
  }

  $days = $hours/24;

  if ($days < 365.25) {
    return sprintf("%dd", $days);
  }

  return sprintf("%dy %3dd", $days/365.25, $days%365.25);
}

function VirianTitle($rep)
{
  if ($rep>8) return "Archon";
  if ($rep>7) return "Adept";
  if ($rep>6) return "Magister";
  if ($rep>5) return "Initiate";
  if ($rep>4) return "Acolyte";
  if ($rep>3) return "Anarch";
  return "Heretic";
}

function AllByRep()
{
  global $db_prefix, $cgi;
  global $sortby;
  global $userID;

  if (!$sortby) {
    $sortby = "realName";
  }

  $sql = "SELECT m.id_member, m.realName, m.memberName, m.dateRegistered, m.lastLogin, rep.reputation, rep.activity, rep.influence, rep.equity, count(rating) as ratings, avg(rating) as average FROM cov_reputation rep left outer join cov_ratings rat on (rep.id_member=rat.id_other), cov_members m where (rep.id_member=m.id_member) group by rat.id_other order by $sortby";

  $request = mysql_query($sql);
  $n = 0;
  
  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];

    $ids[] = $id;
    $realNames[$id] = $row[1];
    $memberNames[$id] = $row[2];
    $dateReg[$id] = $row[3];
    $dateAct[$id] = $row[4];
    $reps[$id] = $row[5];
    $activity[$id] = $row[6];
    $weights[$id] = $row[7];
    $equity[$id] = $row[8];
    $rating_count[$id] = $row[9];
    $rating_avg[$id] = $row[10];
  }

  $count = count($weights);
  $ratings = AllRatings2();

  $all = "<tr><td>&nbsp;</td><td>";
  $all .= "<b><u>member</u></b></td>";
  $all .= "<td align=center><b><u>reputation</u></b></td>";
  $all .= "<td align=center><b><u>title</u></b></td>";
  $all .= "<td align=center><b><u>activity</u></b></td>";
  $all .= "<td align=center><b><u>influence</u></b></td>";
  $all .= "<td align=center><b><u>equity</u></b></td>";
  $all .= "<td><b><u>ratings</u></b></td>";
  $all .= "<td><b><u>average</u></b></td>";
  $all .= "<td align=center><b><u>registered</u></b></td>";
  $all .= "<td align=center><b><u>last login</u></b></td>";

  if ($userID) {
    $all .= "<td><b><u>To</u></b></td>";
    $all .= "<td><b><u>Fr</u></b></td>";
  }

  $all .= "</tr>\n";

  foreach($ids as $id)  {
    $n++;
    
    if ($n % 2 == 1) {
      $class = "windowbg";
    }
    else {
      $class = "windowbg2";
    }
    
    if ($id == $userID) {
      $class = "windowbg4";
    }

    $memberName = str_replace(" ", "%20", $memberNames[$id]);
    $profile = "$cgi;action=viewprofile;user=$memberName";
    $namelink = "<a href=$profile>$realNames[$id]</a>";
    $rep = sprintf("%1.4f", $reps[$id]);
    $title = VirianTitle($reps[$id]);
    $replink = "<a href=$cgi;action=repIndex2;userID=$id>$rep</a>";
    $act = sprintf("%1.2f", $activity[$id]);
    $actlink = "<a href=$cgi;action=repIndex2;activityID=$id>$act</a>";
    $inf = sprintf("%1.2f", $weights[$id]);
    //$inflink = "<a href=http://www.google.com/search?q=e^(2*ln($count)*(${reps[$id]}-5)/(9-1))*ln(${activity[$id]})/ln(2)>$inf</a>";
    //$inflink = "<a href=http://www.google.com/search?q=e^(2*ln($count)*(${reps[$id]}-5)/(9-1))*${activity[$id]}>$inf</a>";
    $inflink = "<a href=http://www.google.com/search?q=${activity[$id]}*e^(2*ln($count)*(${reps[$id]}-5)/(9-1))>$inf</a>";
    $equ = sprintf("%1.2f", $equity[$id]);
    $rcount = $rating_count[$id];
    $average = $rating_avg[$id];
    $regdate = strftime("%d %b %Y", $dateReg[$id]);
    $actdate = strftime("%d %b %Y", $dateAct[$id]);
    $regago = TimeAgo($dateReg[$id]);
    $actago = TimeAgo($dateAct[$id]);

    $all .= "<tr class=$class>";
    $all .= "<td align=right>$n</td><td>$namelink</td>";
    $all .= "<td align=right>$replink&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$title&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$actlink&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$inflink&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$equ&nbsp;&nbsp;</td>\n";
    $all .= "<td align=right>$rcount&nbsp;&nbsp;</td>\n";
    $all .= "<td align=right>$average&nbsp;&nbsp;</td>\n";
    $all .= "<td align=right>$regago ago&nbsp;</td>\n";
    $all .= "<td align=right>$actago ago&nbsp;</td>\n";

    if ($userID) {
      $r1 = $ratings[$id][$userID];
      $r2 = $ratings[$userID][$id];
      $all .= "<td align=right>$r1&nbsp;&nbsp;</td>\n";
      $all .= "<td align=right>$r2&nbsp;&nbsp;</td>\n";
    }

    $all .= "</tr>\n";
  }

  if ($n == 0) {
    $all = "<tr class=windowbg><td>No one has joined yet</td></tr>";
  }
  else {
    $sumRep = VectorSum($reps);
    $avgRep = $sumRep/$count;
    $avgRep = sprintf("%.4f", $avgRep);
    $sumRep = sprintf("%.4f", $sumRep);

    $sumAct = VectorSum($activity);
    $avgAct = $sumAct/$count;
    $avgAct = sprintf("%.2f", $avgAct);
    $sumAct = sprintf("%.2f", $sumAct);

    $sumInf = VectorSum($weights);
    $avgInf = $sumInf/$count;
    $avgInf = sprintf("%.2f", $avgInf);
    $sumInf = sprintf("%.2f", $sumInf);

    $sumEqu = VectorSum($equity);
    $avgEqu = $sumEqu/$count;
    $avgEqu = sprintf("%.2f", $avgEqu);
    $sumEqu = sprintf("%.2f", $sumEqu);

    $sumRat = VectorSum($rating_count);
    $avgRat = $sumRat/count($rating_count);
    $avgRat = sprintf("%.2f", $avgRat);

    $sumAvg = VectorSum($rating_avg);
    $avgAvg = $sumAvg/count($rating_avg);
    $avgAvg = sprintf("%.4f", $avgAvg);

    $all .= "<tr><td colspan=9><hr></td></tr>\n";

    $all .= "<tr><td></td><td align=left>mean</td>";
    $all .= "<td align=right>$avgRep&nbsp;&nbsp;</td>";
    $all .= "<td align=right>&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$avgAct&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$avgInf&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$avgEqu&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$avgRat&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$avgAvg&nbsp;&nbsp;</td>";
    $all .= "</tr>\n";

    $all .= "<tr><td></td><td align=left>total</td>";
    $all .= "<td align=right>&nbsp;&nbsp;</td>";
    $all .= "<td align=right>&nbsp;&nbsp;</td>";
    $all .= "<td align=right>&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$sumInf&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$sumEqu&nbsp;&nbsp;</td>";
    $all .= "<td align=right>$sumRat&nbsp;&nbsp;</td>";
    $all .= "</tr>\n";
  }

  return $all;
}

function AllChoices()
{
  global $db_prefix, $ID_MEMBER, $joinedRep, $settings, $cgi;

  $ratings = AllRatings2();

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

    $all .= "<td>{$ratings[$ID_MEMBER][$id]}</td>";
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
  global $userID;
  global $activityID;

  $yytitle = "Reputation";
  template_header();

  if ($userID && UserReputation($userID) > 0) {
    GenProfile();
  }

  if ($activityID && UserReputation($activityID) > 0) {
    GenActivity();
  }

  $all = AllByRep();

  print <<<EOT
<p>
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="50%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Active Users</b></font></td>
      </tr>
      <tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
    <form action="$cgi;action=repRate2" method="post">
    <input type="submit" value="Enter Ratings"> In order to join Meridion you must rate at least one member (even yourself).
    </form>
<p>
<table align=center>

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

function GenRateForm($realName)
{
  global $cgi, $ID_MEMBER, $userID;

  $ratings = AllRatings2();

  for ($i = 1; $i < 10; $i++) {
    if ($ratings[$userID][$ID_MEMBER] == $i) {
      $all .= "<td><input type=radio name=$userID value=$i checked></td>";
    }
    else {
      $all .= "<td><input type=radio name=$userID value=$i></td>";
    }
  }

  return <<<EOT
<p>
<form action="$cgi;action=repSubmitSingle" method="post">
<table>
<tr align=center>
<td align=left colspan=3>&lt;-ignore</td><td align=center colspan=3><b><u>rating</u></b></td><td align=right colspan=3>endorse-&gt;</td>
</tr>
<tr align=center>
<td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td>
</tr>
<tr class=windowbg>
$all
</tr>
</table>
<center><input type=submit value="Rate '$realName'"></center>
</form>
EOT;
}

function GenProfile()
{
  global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
  global $userID;

  $sql = "SELECT memberName, realName FROM {$db_prefix}members WHERE id_member=$userID";
  $request = mysql_query($sql);
  if ($row = mysql_fetch_row($request)) {
    $memberName = $row[0];
    $realName = $row[1];
  }

  $namelink = "<a href=$cgi;action=viewprofile;user=$memberName>{$realName}'s BBS profile</a>";
  $detailslink = "<a href=$cgi;action=repDetails2;userID=$userID>{$realName}'s ratings</a>";

  $header1 = "How $realName rated others";
  $header2 = "How others rated $realName";

  $profile1 = ProfileHistogram("id_member", $userID);
  $profile2 = ProfileHistogram("id_other", $userID);

  if ($ID_MEMBER>0) {
    $rateForm = GenRateForm($realName);
  }

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
    </td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg]" class="windowbg" align=center valign=middle>
$rateForm
    </td>
  </tr>
</table>
EOT;
}

function ActivityForUser($userID)
{
  $sql = "SELECT * FROM cov_mdn_activity WHERE id_member=$userID";
  $request = mysql_query($sql);
  $row = mysql_fetch_assoc($request);

  $mcount = $row['mcount'];
  $history = unserialize($row['history']);

  $sql = "SELECT a.* FROM cov_mdn_irc_activity a, cov_irc_nicks n where a.nick=n.nick and id_member=$userID";
  $request = mysql_query($sql);

  while ($row = mysql_fetch_assoc($request)) {
    $nicks[] = $row['nick'];
    $lcount[] = $row['lcount'];
    $irchistory[] = unserialize($row['history']);
  }

  $discount = 1.0;
  $msum = 0.0;
  $lsum = 0.0;

  $decay = 0.707;
  $window = 24;

  $rows = "";
  $rows .= "<tr>";
  $rows .= "<td align=right><b><u>months<br>ago</u></b></td>";
  $rows .= "<td align=right><b><u>BBS<br>messages</u></b></td>";
  $rows .= "<td align=right><b><u>discounted<br>messages</u></b></td>";
  $rows .= "<td align=right><b><u>IRC<br>lines</u></b></td>";
  $rows .= "<td align=right><b><u>discounted<br>lines</u></b></td>";
  $rows .= "</tr>";

  for ($i = $window; $i > 0; --$i) {
    
    if ($i % 2 == 1) {
      $class = "windowbg";
    }
    else {
      $class = "windowbg2";
    }
    
    $ago = $window-$i+1;
    $msgs = $history[$i-1];
    $msgcon = $discount * $msgs;
    $msum += $msgcon;

    $lines = 0;
    for($j = 0; $j < count($irchistory); $j++) {
      $lines += $irchistory[$j][$i-1];
    }

    $linecon = $discount * $lines;
    $lsum += $linecon;

    $msgcon = sprintf("%.2f", $msgcon);
    $linecon = sprintf("%.2f", $linecon);

    $rows .= "<tr class=$class><td align=right>$ago</td>";
    $rows .= "<td align=right>$msgs</td>";
    $rows .= "<td align=right>$msgcon</td>";
    $rows .= "<td align=right>&nbsp;&nbsp;$lines</td>";
    $rows .= "<td align=right>$linecon</td></tr>";

    $discount *= $decay;
  }

  $activity = log(1 + $msum + $lsum/100)/log(2);
  $activity = sprintf("%.2f", $activity);
  $actlink = "<a href=\"http://www.google.com/search?q=ln(1 %2B $msum %2B $lsum/100)/ln(2)\">$activity</a>";

  $msum = sprintf("%.2f", $msum);
  $lsum = sprintf("%.2f", $lsum);

  $rows .= "<tr><td colspan=5><hr></td></tr>\n";

  $rows .= "<tr><td align=right>total</td>";
  $rows .= "<td>&nbsp;</td>";
  $rows .= "<td align=right>$msum</td>";
  $rows .= "<td>&nbsp;</td>";
  $rows .= "<td align=right>$lsum</td></tr>";
  
  $rows .= "<tr><td align=right>activity level</td>";
  $rows .= "<td colspan=3>&nbsp;</td>";
  $rows .= "<td align=right>$actlink</td></tr>";

  $rows .= "<tr><td>IRC nicks used:</td><td colspan=4>";

  if ($nicks) {
    foreach($nicks as $nick) {
      $rows .= "$nick &nbsp;";
    }
  }
  else {
    $rows .= "none registered. Contact <a href=mailto:david@lucifer.com>sysadmin</a>";
  }

  $rows .= "</td></tr>";

  return $rows;
}

function GenActivity()
{
  global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
  global $activityID;

  $sql = "SELECT memberName, realName FROM {$db_prefix}members WHERE id_member=$activityID";
  $request = mysql_query($sql);
  if ($row = mysql_fetch_row($request)) {
    $memberName = $row[0];
    $realName = $row[1];
  }

  $title = "$realName activity report";
  $rows = ActivityForUser($activityID);

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
<table align=center>
$rows
    <tr><td colspan=5>Notes:</td></tr>
    <tr><td colspan=5>The number of BBS messages and IRC lines are discounted<br>at a rate of 70.7% per month so that recent activity counts for more.</td></tr>
    <tr><td colspan=5>100 IRC lines is equivalent to 1 BBS message.</td></tr>
</table>

        </td>
      </tr>
    </table>

    </td>
  </tr>
</table>
EOT;
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
  $detailslink = "<a href=$cgi;action=repDetails2;userID=$userID>{$realName}'s ratings</a>";

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
<br>
View details of {$detailslink}
<p>
Return to <a href=$cgi;action=repIndex2>Reputation Index</a>
<p>
    </td>
</table>
EOT;

  footer();
  obExit();
}

function RatingDetails()
{
  global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
  global $userID;
  global $joinedRep;

  if($username == "Guest") { fatal_error("$txt[138]"); }
  $yytitle = "Rating Details";
  template_header();

  $all = AllRatingsForUser($userID);

  print <<<EOT
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
<tr><td>&nbsp;</td><td><b><u>member</u></b></td><td align=center><b><u>reputation</u></b></td><td align=center><b><u>influence</u></b></td><td align=center><b><u>equity</u></b></td><td align=center><b><u>to</u></b></td><td align=center><b><u>from</u></b></td><td align=center><b><u>diff</u></b></td></tr>
$all
</table>
        </td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=middle>
    </td>
  <tr>
    <td bgcolor="$color[windowbg2]" class="windowbg2" valign=middle>
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
  global $joinedRep;

  if($username == "Guest") { fatal_error("$txt[138]"); }
  $yytitle = "Reputation";
  template_header();

  $all = AllChoices();

  print <<<EOT
<form action="$cgi;action=repSubmit2" method="post">
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
  <td>&nbsp;</td><td><b><u>member</u></b></td><td align=left colspan=3>&lt;-ignore</td><td align=center colspan=3><b><u>rating</u></b></td><td align=right colspan=3>endorse-&gt;</td>
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
<li>This person should be ignored
<li>Largely irrelevant
<li>Strongly discount
<li>Slightly discount
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

  foreach($_POST as $id => $rating) {
    print "$ID_MEMBER gave $id a $rating<br>\n";
    // !!! use REPLACE here when index set up
    mysql_query("DELETE from {$db_prefix}ratings WHERE id_member=$ID_MEMBER AND id_other=$id");
    mysql_query("INSERT INTO {$db_prefix}ratings (id_member,id_other,rating) VALUES ($ID_MEMBER,$id,$rating)");
  }


  RecalculateReps();

  $yySetLocation = "$cgi;action=repIndex2;userID=$ID_MEMBER";
  redirectexit();
}

function SubmitRating()
{
  global $board,$username,$txt,$cgi,$scripturl,$img,$imagesdir,$yytitle,$threadid,$ID_MEMBER,$start,$color,$db_prefix;
  global $yySetLocation, $cgi;
  global $r1, $r2, $r3;

  if($username == "Guest") { fatal_error("$txt[138]"); }
  $yytitle = "Submit Rating";
  template_header();

  foreach($_POST as $id => $rating) {
    print "$ID_MEMBER gave $id a $rating<br>\n";
    // !!! use REPLACE here when index set up
    mysql_query("DELETE from {$db_prefix}ratings WHERE id_member=$ID_MEMBER AND id_other=$id");
    mysql_query("INSERT INTO {$db_prefix}ratings (id_member,id_other,rating) VALUES ($ID_MEMBER,$id,$rating)");
  }


  //footer();
  //obExit();

  RecalculateReps();

  $yySetLocation = "$cgi;action=repIndex2;userID=$id";
  redirectexit();
}

?>
