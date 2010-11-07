<?php

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

function VectorMax($v) 
{
  $max = 0;

  foreach(array_values($v) as $val) {
    if ($val > $max) {
      $max = $val;
    }
  }

  return $max;
}

function VectorDiff($v1, $v2)
{
  foreach($v1 as $id => $val) {
    $diff[$id] = $val - $v2[$id];
  }

  return $diff;
}

function VectorDivide($v, $div)
{
  foreach($v as $id => $val) {
    $new[$id] = 1.0*$val/$div;
  }

  return $new;
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
  $n = 0;
  foreach($v as $key=>$val) {
    print "$n $name";
    print "[$key] = $val<br>\n";
    ++$n;
  }
}

?>