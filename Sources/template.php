<?php

$sendtopicplver="YaBB SE";

function Vote()
{
  global $threadid,$board,$cgi,$txt,$img,$imagesdir,$sourcedir,$color,$settings,$username,$db_prefix,$yytitle;

  $yytitle = "Join a chat";
  template_header();

print <<<EOT
<table border=0  align="center" cellspacing=1 cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td width="100%" bgcolor="$color[windowbg]" class="windowbg">
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
      <tr>
        <td class="titlebg" bgcolor="$color[titlebg]">
        <font size=2 class="text1" color="$color[titletext]"><b>Title Here</b></font></td>
      </tr><tr>
        <td bgcolor="$color[windowbg]" class="windowbg">
text here
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
