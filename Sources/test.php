<?php

$map['#virus'][0] = 1;
$map['#immortal'][1] = 2;
$map['#extropy'][2] = 3;

foreach ($map as $key => $arr) 
{
  print "$key => $val\n";

  foreach ($arr as $x => $y) {
    print "$x => $y\n";
  }
      
}


$ratings = array( 5 => array( 3 => 1, 5 => 7, 7 => 3), 3 => array( 3 => 7, 5 => 4, 7 => 5) );
$users = array(3, 5, 7);
$count = count($users);

foreach($users as $user) {
  foreach ($users as $other) {
    $all[$user][$other] = 4;
  }
}

foreach($ratings as $id => $idsRatings) {
  foreach ($idsRatings as $other => $rating) {
    $all[$id][$other] = $rating;
  }
}
		 
foreach($all as $id => $allRatings) {
  foreach($allRatings as $other => $rating) {
    print "$id gave $other a $rating\n";
  }
}

?>
