#!/usr/bin/php -q
<?php

include_once("importf.php"); // import functions

$DEBUG = true;

$trans = get_html_translation_table (HTML_ENTITIES);
$trans = array_flip ($trans);

$query = "SELECT * FROM {$db_prefix}messages LIMIT 1000";

$result = mysql_query($query);
$count = mysql_num_rows($result);

echo "$count messages\n";

while ($row = mysql_fetch_assoc($result)) {
    extract($row);

    if(ereg("This is a multi-part message in MIME format", $body)) {
        echo "$ID_MSG $subject\n";

	$data = strtr($body, $trans);

	if (preg_match("m/<HTML>(.*)<\/HTML>/i", $data, $regs)) {
	    echo "%%% $regs[1] %%%\n";
	}
	else {
	    echo "^^^ $data ^^^\n";
	}
    }
}


echo "Done.\n";
?>
