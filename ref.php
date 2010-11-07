<?php

global $foos;

class Foo
{
  var $name;

  function Foo($name) {
    global $foos;

    $this->name = $name;
    $foos[$name] = $this;
  }

  function rename($name) {
    global $foos;

    unset($foos[$this->name]);
    $this->name = $name;
    $foos[$name] = $this;
  }

  function dump() {
    return "This foo is called $this->name.\n";
  }
}

function Foobar($name)
{
  $foo = new Foo($name);

  return $foo;
}

function Barfoo(&$foo) 
{
  $foo->rename("xxx");
}

$foo = new Foo("one");
echo $foo->dump();

$foo = Foobar("two");
echo $foo->dump();

$foo = $foos["one"];

if ($foo) {
  echo $foo->dump();
}
else {
  echo "can't find it\n";
}

$foo->rename("three");
echo $foo->dump();

Barfoo($foo);
echo $foo->dump();

echo "done\n";

?>

