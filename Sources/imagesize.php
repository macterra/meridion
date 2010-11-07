<?php

list($width, $height, $type, $attr) = @getimagesize("http://ca.php.net/images/php.gif");

print "$width $height $type $attr";

?>
