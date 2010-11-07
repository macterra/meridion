<?
/******************************************************************************
 Smilies.php
================
Mod Name:			    Smilies SE
File written by:		dOCda S!
Ported by:              Matt Siegman
******************************************************************************/

$smiliever = "SE"; // Version Of Smilies

function SmiliePanel() {
    global $asmtxt,$color,$imagesdir,$cgi,$db_prefix;
    is_admin(); // check for admin
	$yytitle = "$asmtxt[01]";
	template_header();
	
print <<<END
<br><br>
<a href="$cgi;action=smilieadd">[ $asmtxt[21] ]</a><br>
<a href="$cgi;action=smiliemod">[ $asmtxt[19] ]</a><br>
<a href="$cgi;action=smilieset">[ $asmtxt[22] ]</a>
END;

SmileyFoot();
footer();
obExit();
} // END \\

function SmiliePut() {
    global $yySetCookies,$headers,$db_prefix,$color,$asmtxt,$imagesdir;
print <<<END
<HTML>
<HEAD>
<TITLE>$mbname Smileys List!</TITLE>
<style type="text/css">
END;

$GrabURL = "template.html";
$GrabStart = '<style type="text/css">';  //- HTML Code To Start Grab. Must Be A Unique Bit Of Code!
$GrabEnd = "</style>";  //- HTML Code To End Grab. Must Be A Unique Bit Of Code!
$OpenFile = fopen("$GrabURL", "r"); //- DO NOT CHANGE
$RetrieveFile = fread($OpenFile, 200000);  //- Reduce This To Save Memory
$GrabData = eregi("$GrabStart(.*)$GrabEnd", $RetrieveFile, $DataPrint);
fclose($OpenFile); //- DO NOT CHANGE
echo $DataPrint[1]; //- DO NOT CHANGE

print <<<END
</style>
<script>
END;

     $SQL_query = mysql_query("SELECT * FROM ".$db_prefix."smilies ORDER BY id");
     $i = 0;
     while($smilies = mysql_fetch_array($SQL_query)) {
print <<<END
    function Smilie$i() {
        AddTxt="$smilies[code]";
        parent.AddText(AddTxt);
    }
END;
           $i++;
     }
print <<<END
</script>
<BODY bgcolor="$color[windowbg2]">
<table cellpadding="4" bgcolor="$color[bordercolor]" cellspacing="1" border="0" width="100%">
 <tr>
   <td colspan="4" bgcolor="$color[titlebg]" width="751"><font size="2"><b>$asmtxt[13]</b></font><br>
   <font size="1">$asmtxt[12]</font></td>
 </tr>
END;

     $SQL_query = mysql_query("SELECT * FROM ".$db_prefix."smilies ORDER BY id");
     $i = 0;
     while($smilie = mysql_fetch_array($SQL_query)) {
print <<<EOT
    <tr>
     <td class="windowbg2" bgcolor="$color[windowbg2]" width="125"><a href=javascript:Smilie$i()><img src="$imagesdir/$smilie[url]" border="0" alt="$smilie[code]"></a></td>
     <td class="windowbg2" bgcolor="$color[windowbg2]" width="125"><font size="2">$smilie[code]</font></td>
    </tr>
EOT;
        $i++;
    }

print <<<END
<tr>
     <td colspan="4" bgcolor="$color[windowbg2]" align="center"><font size="1">[ <a href="javascript:self.close()">close this window</a> ]</font></td>
</tr>
</table>
</TABLE>
</BODY>
</HTML>
END;
    obExit();
}

function SmilieAdd() 
{
    global $asmtxt,$color,$imagesdir,$cgi,$db_prefix;
    is_admin(); // check for admin
	$yytitle = "$asmtxt[21]";
	template_header();
print <<<EOT
<form action="$cgi&action=smilieadd2" method="POST">
<table border=0 width="80%" cellspacing=1 cellpadding=1 bgcolor="$color[bordercolor]" class="bordercolor" align=center>
<tr>
<td class="titlebg" bgcolor="$color[titlebg]" colspan="3" height=22>
<b><font size=2 class="text1" color="$color[titletext]">&nbsp;<img src="$imagesdir/grin.gif">&nbsp;$asmtxt[08]</font></b>
</td>
</tr>
<tr>
<td class='windowbg' bgcolor=$color[windowbg] width='40%' align=center>$asmtxt[02]</TD>
<td class='windowbg' bgcolor=$color[windowbg] width='40%' align=center>$asmtxt[03]</TD>
<td class='windowbg' bgcolor=$color[windowbg] width='20%' align='center'></TD>
</tr>
EOT;

$inew = 1;
while($inew <= "5") {
$i++;
print <<<EOT
<tr>
<td class='windowbg' bgcolor=$color[windowbg] width='40%' align=center><input type=text name=scd[$inew]></TD>
<td class='windowbg' bgcolor=$color[windowbg] width='40%' align=center><input type=text name=smimg[$inew]></TD>
<td class='windowbg' bgcolor=$color[windowbg] width='20%' align='center'></TD>
</tr>
EOT;
$inew++;
}
print <<<EOT
<tr>
<td class="catbg" bgcolor="$color[catbg]" align="center" colspan="6">
<input type=submit value="$asmtxt[09]">
<input type=reset value="$asmtxt[10]">
<br><br>
<a href="$cgi&action=smilies">$asmtxt[18]</a>
</td>
</tr>
</TABLE>
</form>
EOT;
SmileyFoot();
footer();
obExit();
}

function SmilieAdd2() {
    global $db_prefix,$new,$delbox,$smimg,$scd,$ids,$cgi,$hdetach,$wdetach,$vbsmileys;
    is_admin(); // check for admin
    
    $num = 0;
    
    while($num < 5)
    {
      eval("\$v1 = \$scd[$num];");
      eval("\$v2 = \$smimg[$num];");
      //echo "<br>$num<br>$v1<br>$v2<br>";
      if(($v1 != "") && ($v2 != ''))
      {
        eval("\$var1 = \$scd[$num];");
        eval("\$var2 = \$smimg[$num];");
        $qs = "INSERT INTO ".$db_prefix."smilies (code,url) VALUES ('".$var1."','".$var2."')";
        //echo "$qs";
        mysql_query("$qs");
      }
      $num++;
    }
    
	SmiliePanel();
}

function SmilieMod() {
    global $asmtxt,$color,$imagesdir,$cgi,$db_prefix;
    is_admin(); // check for admin
	$yytitle = "$asmtxt[19]";
	template_header();
print <<<EOT
<form action="$cgi&action=smiliemod2" method="POST">
<table border=0 width="80%" cellspacing=1 cellpadding=1 bgcolor="$color[bordercolor]" class="bordercolor" align=center>
<tr>
    <td class="titlebg" bgcolor="$color[titlebg]" colspan="6" height=22>
    <b><font size=2 class="text1" color="$color[titletext]">&nbsp;<img src="$imagesdir/grin.gif">&nbsp;$asmtxt[11]</font></b>
    </td>
</TR>
<TR>
	<td class="catbg" bgcolor="$color[catbg]" width="40%" height=22><b><font size=2>$asmtxt[02]</font></b></td>
	<td class="catbg" bgcolor="$color[catbg]" width="40%"><b><font size=2>$asmtxt[03]</font></b></td>
	<td class="catbg" bgcolor="$color[catbg]" width="14%" align=center><b><font size=2>$asmtxt[06]</font></b></td>
	<td class="catbg" bgcolor="$color[catbg]" width="6%" align=center><b><font size=2>$asmtxt[07]</font></b></td>
</TR>
EOT;
    $i = 0;
	$SQL_query = mysql_query("SELECT * FROM ".$db_prefix."smilies");
	while($smilie = mysql_fetch_array($SQL_query)) {
	   $i++;
print <<<EOT
       <TR>
       <td class='windowbg' bgcolor=$color[windowbg] width='40%' align=center>
         <input type=text name=scd[$i] value=$smilie[code]>
       </TD>
	   <td class='windowbg' bgcolor=$color[windowbg] width='40%' align=center>
	     <input type=text name=smimg[$i] value=$smilie[url]>
	   </TD>
	   <td class='windowbg' bgcolor=$color[windowbg] width='14%' align='center'>
	     <img src=$imagesdir/$smilie[url]>
	   </TD>
	   <td class='windowbg' bgcolor=$color[windowbg] width='6%' align=center>
	     <input type=checkbox name=delbox[$i] value=1>
	     <input type=hidden name=ids[$i] value=$smilie[id]>
	   </TD>
	   </TR>
EOT;
	}
print <<<END
<tr>
<td class="catbg" bgcolor="$color[catbg]" align="center" colspan="6">
<input type=hidden name="numsmilies" value=$i>
<input type=submit value="$asmtxt[09]">
<input type=reset value="$asmtxt[10]">
<br><br>
<a href="$cgi&action=smilies">$asmtxt[18]</a>
</td>
</tr>
</table>
</form>
END;
	SmileyFoot();
	footer();
	obExit();
}

function SmilieMod2() {
    global $db_prefix,$new,$delbox,$smimg,$scd,$numsmilies,$ids,$cgi,$hdetach,$wdetach,$vbsmileys;
    is_admin(); // check for admin
    
    $i = 1;
    while($i <= $numsmilies)
    {
      eval("\$code = \$scd[$i];");
      eval("\$image = \$smimg[$i];");
      eval("\$smid = \$ids[$i];");
      eval("\$delete = \$delbox[$i];");
      $qs;
      if($delete == 1)
        $qs = "DELETE FROM ".$db_prefix."smilies WHERE id='$smid'";
      else
        $qs = "UPDATE ".$db_prefix."smilies SET code='$code',url='$image' WHERE id='$smid'";
      
      //echo("$qs");  
      mysql_query("$qs");
      $i++;
    }
    
	SmiliePanel();
}

function SmilieSet() {
    global $asmtxt,$color,$imagesdir,$cgi,$db_prefix;
    is_admin(); // check for admin
	$yytitle = "$asmtxt[22]";
	template_header();
	    $settings;
	$SQL_query = mysql_query("SELECT * FROM ".$db_prefix."smilies_opts");
	while($settin = mysql_fetch_array($SQL_query)) {
	    $settings[$settin[variable]] = $settin[value];
	}

	if($settings[vbsmileys]) $vbsmileyschecked = " checked";

print <<<EOT
<form action="$cgi&action=smilieset2" method="POST">
<table border=0 width="80%" cellspacing=1 cellpadding=1 bgcolor="$color[bordercolor]" class="bordercolor" align=center>
  <tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$asmtxt[15]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="hdetach" size="10" value="$settings[hdetach]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$asmtxt[16]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="wdetach" size="10" value="$settings[wdetach]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color{'windowbg2'}"><font size="2">$asmtxt[17]</font></td>
    <td class="windowbg2" bgcolor="$color{'windowbg2'}"><input type=checkbox name="vbsmileys"$vbsmileyschecked></td>
  </tr>
<tr>
<td class="catbg" bgcolor="$color[catbg]" align="center" colspan="6">
<input type=submit value="$asmtxt[09]">
<input type=reset value="$asmtxt[10]">
<br><br>
<a href="$cgi&action=smilies">$asmtxt[18]</a>
</td>
</tr>
</TABLE>
</form>
EOT;
SmileyFoot();
footer();
obExit();
}

function SmilieSet2() {
    global $db_prefix,$new,$delbox,$smimg,$scd,$ids,$cgi,$hdetach,$wdetach,$vbsmileys;
    is_admin(); // check for admin
/* HEIGHT */
    if($hdetach != '')
        mysql_query("UPDATE ".$db_prefix."smilies_opts SET value=".$hdetach." WHERE variable='hdetach'");
    else
        mysql_query("UPDATE ".$db_prefix."smilies_opts SET value=200 WHERE variable='hdetach'");

/* Width */
    if($wdetach != '')
        mysql_query("UPDATE ".$db_prefix."smilies_opts SET value=".$wdetach." WHERE variable='wdetach'");
    else
        mysql_query("UPDATE ".$db_prefix."smilies_opts SET value=290 WHERE variable='wdetach'");

/* On/OFF */
    if($vbsmileys != '')
        mysql_query("UPDATE ".$db_prefix."smilies_opts SET value=".$vbsmileys." WHERE variable='vbsmileys'");
    else
        mysql_query("UPDATE ".$db_prefix."smilies_opts SET value=1 WHERE variable='vbsmileys'");

	SmiliePanel();
}

function SmileyFoot() {
print <<<END
<center><font size=1><B>Smilies SE - Admin Center Controlled!<BR>Written by <a href="http://www.mattsiegman.com">Matt Siegman</a>.<BR>Original by Sniser & dOCda S! & Big P</B></font></center>
END;
}

function retme($variable) { return $variable; }

?>