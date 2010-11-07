<?php

function AllUsers()
{

  global $db_prefix;

  // inner join with members table to automatically weed out deleted members
  $sql = "SELECT r.id_member, realName FROM cov_ratings AS r, cov_members AS m WHERE r.id_member=m.id_member GROUP BY id_member";
  //$sql = "SELECT r.id_member, realName FROM cov_ratings AS r, cov_members AS m WHERE (UNIX_TIMESTAMP()-lastLogin)<(90*24*60*60) AND r.id_member=m.id_member GROUP BY id_member";
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

function Weights($reps)
{
  $min = 1;
  $max = 9;
  $med = ($min + $max)/2;

  $count = count($reps);

  if ($count>0) {
    $scale = 2*log($count)/($max-$min);
  }
  else {
    $scale = 1;
  }

  foreach($reps as $user => $rep) {
    //    $weights[$user] = pow(10, ($reps[$user]-$min)/$scale);
    $weights[$user] = exp($scale*($reps[$user]-$med));
  }

  echo "Weights $count\n";

  //  return VectorDivide($weights, $count);
  return $weights;
}

function AllRepVals($col)
{
  global $db_prefix;
  
  $request = mysql_query("SELECT id_member, $col FROM {$db_prefix}reputation");
  while ($row = mysql_fetch_row($request)) {
    $id = $row[0];
    $val = $row[1];
    $vals[$id] = $val;
  }

  return $vals;
}

function AllReputation()
{
  return AllRepVals("reputation");
}

function AllInfluence()
{
  return AllRepVals("influence");
}

function AllEquity()
{
  return AllRepVals("equity");
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

?>