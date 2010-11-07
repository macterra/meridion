<?php

$DEBUG=1;

include_once("importf.php"); // import functions
include_once("importc.php"); // configuration

htmlheader();

if ($DEBUG) {
  echo "<pre>DEBUG\n";
  echo date("Y-m-d h:m:s"), "\n";
  echo "step=$step\n";
  echo "dirname=$dirname\n";
  echo "timezone=$timezone\n";
  echo "savedPath=$savedPath\n";
  echo "savedTZ=$savedTZ\n";
  echo "savedBoard=$savedBoard\n";
  echo "----------</pre>\n\n";
}

switch($step) {
 case 1:
  if ($dirname) {
    $out = shell_exec("/bin/ls -d $dirname");

    $files = explode("\n", $out);

    foreach ($files as $file) {
      if (is_file($file)) {
	$f = fopen($file, "r");
	if ($f) {
	  $line = fgets($f, 4096);
	  fclose($f);
	  if (eregi("^From ", $line)) {
	    $archives[] = $file;
	  }
	}
      }
    }
      
    if ($archives) {
      form2($archives);
    }
    else {
      echo "No readable mail archives found.\n";
    }
  }
  else {
    form1();
    echo "<font color=\"red\">You must enter a path to the archives to proceed.</font>\n";
  }
  break;

 case 2:
   echo "CONFIRM<p>\n";

   $count = count($archives);
   $boards = getBoards();
   foreach ($boards as $id => $name) {
     if ($board == $id) {
       break;
     }
   }

   if ($count == 0) {
     echo "No archives will be imported.\n";
   }
   else if ($count == 1) {
     echo "$archives[0] will be imported into Board #$board ($name) using a timezone adjust of $timezone.<br>\n";
   }
   else {
     echo "These ", count($archives), " archives will be imported into Board #$board ($name) using a timezone adjust of $timezone:<br>\n";
     foreach($archives as $file) {
       echo "$file<br>\n";
     }
   }

   form3();
   break;

 case 3:
   $f = fopen("./importc.php", "w");
   fputs($f, "<?php\n");
   fputs($f, "\$savedPath = \"$dirname\";\n");
   fputs($f, "\$savedTZ = \"$timezone\";\n");
   fputs($f, "\$savedBoard = $board;\n");
   fputs($f, "?>\n");
   fclose($f);

   foreach ($archives as $file) {
     importArchive($file);
   }

   form4();
   break;

 case 4:
   updateMembers();
   form5();
   break;

 case 99:
   cleanUp();
   echo "Clean up finished<p>\n";
   form1();
   break;

 default:
   form1();
   break;
}

if ($DEBUG) {
  form99();
}

htmlfooter();
?>

<?php function htmlheader() { ?>

<html>
<head>
	<title>YaBB Mailing List Importer</title>
<style>
<!--
-->
</style>
</head>
<body bgcolor="#FFFFFF">

<center>
<table border=0 cellspacing=1 cellpadding=4 bgcolor="#000000" width=90%>
<tr>
	<th bgcolor="#34699E"><font color="#FFFFFF">YaBB Mailing List Importer</font></th>
</tr>
<tr>
	<td bgcolor="#f0f0f0">

<?php } ?>

<?php function htmlfooter() { ?>

</tr>
</table>
</center>
</body>
</html>

<?php } ?>

<?php function form1() { global $savedPath, $PHP_SELF; ?>

<form action="<?= $PHP_SELF."?step=1" ?>" method=POST>
Enter a path: <input name="dirname" value="<?=$savedPath?>">
<input name="action" type="submit" value="Proceed">
</form>

<?php } ?>


<?php function form2() 
{ 
  global $archives, $dirname, $savedBoard, $savedTZ, $PHP_SELF; 
?>

<?=count($archives)?> archives look valid
<form action="<?= $PHP_SELF."?step=2" ?>" method=POST>
<select name="archives[]" multiple size="10">

<?php
   foreach ($archives as $file) {
     echo "<option selected>$file</option>\n";
   }
?>
</select>
<br>

Import messsages into this board:
<select name="board">
<?php
   $boards = getBoards();
   foreach ($boards as $id => $name) {
     if ($id == $savedBoard) {
       echo "<option value=\"$id\" selected>$name</option>\n";
     }
     else {
       echo "<option value=\"$id\">$name</option>\n";
     }
   }
?>
</select>
<br>

Select a time zone adjustment: 
<select name="timezone">

<?php
   $zones = array("-0800", "-0700", "-0600", "-0500", "-0400", "-0300");
   foreach ($zones as $zone) {
     if ($zone == $savedTZ) {
       echo "<option selected>$zone</option>\n";
     }
     else {
       echo "<option>$zone</option>\n";
     }
   }
?>

</select>

<input name="dirname" type="hidden" value="<?=$dirname?>">
<input name="action" type="submit" value="Proceed">
</form>

<?php } ?>

<?php 
function hide($name, $val) 
{
  return "<input name=\"$name\" type=\"hidden\" value=\"$val\">\n";
}

function form3() 
{ 
  global $archives, $id, $dirname, $timezone, $PHP_SELF;
 
  if (!$archives) {
    return;
  }

  echo "<form action=\"$PHP_SELF?step=3\" method=POST>\n";
  foreach($archives as $file) {
    echo "<input name=\"archives[]\" type=\"hidden\" value=\"$file\">\n";
  }
  echo "<input name=\"dirname\" type=\"hidden\" value=\"$dirname\">\n";
  echo "<input name=\"timezone\" type=\"hidden\" value=\"$timezone\">\n";
  echo "<input name=\"board\" type=\"hidden\" value=\"$id\">\n";
  echo "<input name=\"action\" type=\"submit\" value=\"Proceed\">\n";
  echo "</form>\n";
}
?>

<?php function form4() { global $PHP_SELF; ?>

<hr>
Update member list totals:
<form action="<?= $PHP_SELF."?step=4" ?>" method=POST>
<input name=action type="submit" value="Proceed">
</form>

<?php } ?>

<?php 
function form5() 
{ 
  global $PHP_SELF; 
  global $boardurl;
?>

<hr>
Final step: recount all board totals:
<form action="<?= $boardurl."/index.php?board=;action=boardrecount" ?>" method=GET>
<input type="submit" value="Finish">
</form>

<?php } ?>

<?php function form99() { global $PHP_SELF; ?>

<form action="<?=$PHP_SELF?>" method=POST>
<input name="step" type="hidden" value="99">
<input name=action type="submit" value="clean up">
</form>

<?php } ?>

