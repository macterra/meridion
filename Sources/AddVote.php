<?php

$sendtopicplver="YaBB SE";

function AddVote()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;

  $yytitle = "New Issue";
  template_header();

print <<<EOT
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Add a new issue</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
<form action="$cgi;action=vote6" method="post">
<table align=center cellpadding=5 cellspacing=0 border=0>
<tr><td colspan=2 align=left>$text</td><tr>
<tr>
  <td>Title</td><td><input name=title size=40></td>
</tr>
<tr>
  <td>Question</td><td><input name=question size=40></td>
</tr>
<tr>
  <td>Option 1</td><td><input name=option[] size=40></td>
</tr>
<tr>
  <td>Option 2</td><td><input name=option[] size=40></td>
</tr>
<tr>
  <td>Option 3</td><td><input name=option[] size=40></td>
</tr>
<tr>
  <td>Option 4</td><td><input name=option[] size=40></td>
</tr>
<tr>
  <td>Option 5</td><td><input name=option[] size=40></td>
</tr>
<tr>
  <td>Option 6</td><td><input name=option[] size=40></td>
</tr>
<tr>
  <td>Option 7</td><td><input name=option[] size=40></td>
</tr>
<tr>
  <td>Option 8</td><td><input name=option[] size=40></td>
</tr>
<tr>
  <td bgcolor="$color[windowbg]" class="windowbg" align=right valign=middle colspan=2>
  <input type="submit" value="Submit">
  </td>
</tr>
</table>
</form>
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
EOT;

  footer();
  obExit();
}

function AddVote2()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;
  global $ID_MEMBER, $title, $question, $option;

  $yytitle = "New Issue";
  template_header();

  if ($ID_MEMBER && $title && $question && $option) {
    print "title = $title<br>\n";
    print "question = $question<br>\n";

    $sql = "INSERT INTO {$db_prefix}votes (id_member,title,text) VALUES ($ID_MEMBER,'$title','$question')";
    print "$sql<br>\n";
    mysql_query($sql);
    $id_vote = mysql_insert_id();

    $n = 0;
    foreach($option as $opt) {
      $n++;
      if ($opt) {
	print "option $n = $opt<br>\n";
	$sql = "INSERT INTO {$db_prefix}vote_options (id_vote,text) VALUES ($id_vote,'$opt')";
	print "$sql<br>\n";
	mysql_query($sql);
      }
    }
    $res = "OK";
  }
  else {
    $res = "error";
  }

print <<<EOT
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Add a new issue</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
result = $res
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
EOT;

  footer();
  obExit();
}

?>
