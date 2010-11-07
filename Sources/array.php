<?php


function foo()
{
  return array(1, 2, 3);
}


list($a, $b, $c) = foo();

print "$a $b $c\n";


?>
