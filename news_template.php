<?
print <<<EOT
<table border=0 width=100% align=center>
<tr>
  <td><font face=verdana size=2>$news_icon <b>$news_title</b></font><font face=verdana size=1><br />$news_date</font><font face=verdana size=1>   $txt[525] $news_poster<br /><br /></font></td>
</tr>
<tr>
  <td><font face=verdana size=2>$news_body<br><br></font></td>
</tr>
<tr>
  <td><font face=verdana size=2>$news_comments | $news_newcomment</font></td>
</tr>
</table>
<br><hr width=100%><br>
EOT;
?>