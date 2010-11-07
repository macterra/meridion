<?
/*****************************************************************************/
/* PackageGet.php                                                            */
/*****************************************************************************/
/* YaBB: Yet another Bulletin Board                                          */
/* Open-Source Project started by Zef Hemel (zef@zefnet.com)                 */
/* Software Version: YaBB SE                                                 */
/* ========================================================================= */
/* Software Distributed by:    http://www.yabb.info                          */
/* Support, News, Updates at:  http://www.yabb.info/community                */
/*                             http://yabb.xnull.com/community               */
/* ========================================================================= */
/* Copyright (c) 2001-2002 Lewis Media - All Rights Reserved                 */
/* Software by: The YaBB Development Team                                    */
/*****************************************************************************/
/* This program is free software; you can redistribute it and/or modify it   */
/* under the terms of the GNU General Public License as published by the     */
/* Free Software Foundation; either version 2 of the License, or (at your    */
/* option) any later version.                                                */
/*                                                                           */
/* This program is distributed in the hope that it will be useful, but       */
/* WITHOUT ANY WARRANTY; without even the implied warranty of                */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General */
/* Public License for more details.                                          */
/*                                                                           */
/* The GNU GPL can be found in gpl.txt in this directory                     */
/*****************************************************************************/

global $adminplver;
$adminplver="YaBB SE";
$safe_mode = ini_get("safe_mode");
	
$pacmanver = "0.8";


// verify the user is an administrator
is_admin();

function PackageGet()
{
   global $txt, $color, $scripturl, $yytitle, $override;

	$yytitle = $txt['yse182'];
	template_header();
	print <<<EOT
<table border="0" cellpadding="0" cellspacing="6" align="center" width="100%">
  <tr>
    <td colspan=2 width="100%">
    <table border="0" cellpadding="5" cellspacing="1" align="center" bgcolor="$color[bordercolor]" class="bordercolor" width="100%">
      <tr>
        <td bgcolor="$color[titlebg]" class="titlebg" height="23" align="center" colspan="2">
        <font size="4" color="$color[titletext]">$txt[yse183]</font></td>
      </tr><tr>
        <td class="windowbg" bgcolor="$color[windowbg]" valign="middle" align="left" width="100%">
EOT;
print "<ul>";
   $servers = file("Packages/server.list");
   for($i=0 ; $i<count($servers) ; ++$i)
   {
     list($name, $url) = explode("|^|", $servers[$i]);
     $name = stripslashes($name);
     echo "
       <li>$name
       <a href=\"$scripturl?action=pgbrowse;server=$i\">[ $txt[yse184] ]</a>
       <a href=\"$scripturl?action=pgremove;server=$i\">[ $txt[yse138] ]</a>
     ";
   }
   echo <<<EOT
 </td>
   </tr>
   </table></td></tr></table>
     <br><form action="$scripturl?action=pgadd" method="POST">
<table border="0" cellpadding="0" cellspacing="6" align="center" width="100%">
  <tr>
    <td colspan=2 width="100%">
    <table border="0" cellpadding="5" cellspacing="1" align="center" bgcolor="$color[bordercolor]" class="bordercolor" width="100%">
      <tr>
        <td bgcolor="$color[titlebg]" class="titlebg" height="23" align="center" colspan="2">
        <font size="4" color="$color[titletext]">$txt[yse185]</font></td>
      </tr><tr>
        <td class="windowbg" bgcolor="$color[windowbg]" valign="middle" align="left" width="100%">
            <b>$txt[yse186]:</b> <input type=text name="name" size=40 value="YaBB SE"><br>
            <b>$txt[yse187]:</b> <input type=text name="serverurl" size=50 value="http://"><br>
            <input type=submit value="Add">
         </td>
       </tr>
     </table>
   <br><br>
   <a href="$scripturl?action=packages">[ $txt[193] ]</a>
     </td></tr></table>
     </form>
EOT;

  	footer();
	obExit();
}

function PackageServerAdd()
{
  global $name, $serverurl;

  $fp = fopen("Packages/server.list", "a");
  fputs($fp, "$name|^|$serverurl\n");
  fclose($fp);
  
  header("Location: $scripturl?action=packageget");
}

function PackageServerRemove()
{
  global $server;

  $servers = file("Packages/server.list");
  $fp = fopen("Packages/server.list", "w");
  for($i=0 ; $i<count($servers) ; ++$i)
    if($i != $server)
      fputs($fp, chop($servers[$i])."\n");

   header("Location: $scripturl?action=packageget");
}

function PackageBrowse()
{
  global $server, $yytitle, $txt, $safe_mode, $boardurl;

  $servers = file("Packages/server.list");
  list($name, $url) = explode("|^|", $servers[$server]);
  $url = chop($url);
  $packages = array();
  if($safe_mode) { header("Location: $url/index.php?ref=$boardurl"); obExit(); }
  $rf = fopen($url."/package.list", "r") or die("Could not connect to server");
  while(!feof($rf))
    $packages[] = fgets($rf, 4096);
  fclose($rf);

  if(count($packages)==0)
    die("An error occured, could not connect or packages.list does not exist");

  $yytitle = $txt['yse188'];
	template_header();
	print <<<EOT
<table border="0" cellpadding="0" cellspacing="6" align="center" width="100%">
  <tr>
    <td colspan=2 width="100%">
    <table border="0" cellpadding="5" cellspacing="1" align="center" bgcolor="$color[bordercolor]" class="bordercolor" width="100%">
      <tr>
        <td bgcolor="$color[titlebg]" class="titlebg" height="23" align="center" colspan="2">
        <font size="4" color="$color[titletext]">$txt[yse188]</font></td>
      </tr><tr>
        <td class="windowbg" bgcolor="$color[windowbg]" valign="middle" align="left" width="100%">
EOT;
  if(count($packages)==1 && !chop($packages[0]))
     echo "<li>$txt[yse189]";
  else
   {
      $j = 1;
      for($i=0 ; $i<count($packages) ; ++$i)
      {
        list($name, $filename, $desc) = explode("|^|", $packages[$i]);
        if(chop($desc) == "") $desc = $txt[pacman8];
        if($name == '-')
          echo "<hr width=\"75%\" align=\"left\">";
        else if(chop($filename) == 'head') {
          echo "<b><font size=\"+1\">$name</font></b><br>";
          $j = 1;
        }
        else if(chop($filename) == 'text') {
          echo "<b><font size=\"+1\">$name</font></b><br>";
          $j = 1;
        }
        else {
           echo "<br>
              $j.  $name
              <a href=\"$scripturl?action=pgdownload;server=$server;package=$filename\">[ $txt[yse190] ]</a><br>
              $txt[pacman9]:&nbsp; $desc<br>
              $txt[pacman10]:&nbsp; <a href=\"$url/$filename\">$url/$filename</a><br>
           ";
           $j++;
        }
      }
   }
  echo <<<EOT
   <br><br>
   <a href="$scripturl?action=packageget">[ $txt[193] ]</a>
   </td>
   </tr>
   </table></td></tr></table>
EOT;
  	footer();
	obExit();
}


function PackageDownload()
{
  global $server, $package, $yytitle, $txt;

  $servers = file("Packages/server.list");
  list($name, $url) = explode("|^|", $servers[$server]);
  $url = chop($url);
  $rf = fopen($url."/".$package, "rb") or die($txt['yse191']);
  $buffer = "";
  while(!feof($rf))
    $buffer .= fread($rf, 1024);
  fclose($rf);

  $fp = fopen("Packages/$package", "wb");
  fputs($fp, $buffer);
  fclose($fp);

  $yytitle = $txt['yse192'];
	template_header();
	print <<<EOT
<table border="0" cellpadding="0" cellspacing="6" align="center" width="100%">
  <tr>
    <td colspan=2 width="100%">
    <table border="0" cellpadding="5" cellspacing="1" align="center" bgcolor="$color[bordercolor]" class="bordercolor" width="100%">
      <tr>
        <td bgcolor="$color[titlebg]" class="titlebg" height="23" align="center" colspan="2">
        <font size="4" color="$color[titletext]">$txt[yse192]</font></td>
      </tr><tr>
        <td class="windowbg" bgcolor="$color[windowbg]" valign="middle" align="left" width="100%">
         $txt[yse193]<br><br>
      <a href="$scripturl?action=pgbrowse;server=$server">[ $txt[193] ]</a>
       </td>
   </tr>
   </table></td></tr></table>
EOT;
  	footer();
	obExit();
}

function PackShow()
{
  global $server, $yytitle, $txt, $safe_mode, $boardurl;

  $rf = fopen("./package.list", "r") or die("Could not connect to server");
  while(!feof($rf))
    $packages[] = fgets($rf, 4096);
  fclose($rf);

  if(count($packages)==0)
    die("An error occured, could not connect or packages.list does not exist");

  $yytitle = $txt['yse188'];
	template_header();
	print <<<EOT
<table border="0" cellpadding="0" cellspacing="6" align="center" width="100%">
  <tr>
    <td colspan=2 width="100%">
    <table border="0" cellpadding="5" cellspacing="1" align="center" bgcolor="$color[bordercolor]" class="bordercolor" width="100%">
      <tr>
        <td bgcolor="$color[titlebg]" class="titlebg" height="23" align="center" colspan="2">
        <font size="4" color="$color[titletext]">$txt[yse188]</font></td>
      </tr><tr>
        <td class="windowbg" bgcolor="$color[windowbg]" valign="middle" align="left" width="100%">
EOT;
  if(count($packages)==1 && !chop($packages[0]))
     echo "<li>$txt[yse189]";
  else
   {
      $j = 1;
      for($i=0 ; $i<count($packages) ; ++$i)
      {
        list($name, $filename, $desc) = explode("|^|", $packages[$i]);
        if(chop($desc) == "") $desc = $txt[pacman8];
        if($name == '-')
          echo "<hr width=\"75%\" align=\"left\">";
        else if(chop($filename) == 'head') {
          echo "<b><font size=\"+1\">$name</font></b><br>";
          $j = 1;
        }
        else if(chop($filename) == 'text') {
          echo "<b><font size=\"+1\">$name</font></b><br>";
          $j = 1;
        }
        else {
           echo "<br>
              $j.  $name
              <a href=\"$scripturl?action=pgdownload;server=$server;package=$filename\">[ $txt[yse190] ]</a><br>
              $txt[pacman9]:&nbsp; $desc<br>
              $txt[pacman10]:&nbsp; <a href=\"$url/$filename\">$url/$filename</a><br>
           ";
           $j++;
        }
      }
   }
  echo <<<EOT
   <br><br>
   <a href="$ref?action=packageget">[ $txt[193] ]</a>
   </td>
   </tr>
   </table></td></tr></table>
EOT;
  	footer();
	obExit();
}
?>