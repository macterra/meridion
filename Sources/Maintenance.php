<?php
/*****************************************************************************/
/* Maintenance.php                                                           */
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

$maintenanceplver="YaBB SE 1.3.0";

function InMaintenance () {

	global $yytitle,$color,$cgi,$action,$Cookie_Length,$txt,$mtitle,$mmessage,$imagesdir,$mtxt;

	$yytitle = "$txt[155]";
	template_header();
	print <<<EOT
<table border="0" width="100%" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
   <td class="titlebg" bgcolor="$color[titlebg]" colspan="2" width="100%"><font size="2"
   class="text1">» <b>$mtitle</b></font></td>
   </tr>
    <tr>
      <td class="windowbg" bgcolor="$color[windowbg]" width="44"><p align="center"><font size="2"><br>
      <img src="$imagesdir/construction.gif"
      width="40" height="40" alt="$mtxt[3]"><br>
      &nbsp;
      </font>
     </td>
    <td class="windowbg" bgcolor="$color[windowbg]" width="100%"><font size="2"> $mmessage</font></td>
  </tr>
</table>

<table border="0" width="100%" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor">
 <tr>
  <td bgcolor="$color[titlebg]"><font size="2" color="$color[titletext]"><b> $txt[114]</b></font></td>
 </tr><tr>
  <td bgcolor="$color[windowbg]"><font size="2"><form action="$cgi;action=login2" method="POST">
   <table border="0" width="100%">
    <tr>
      <td><font size="2"><b>$txt[35]:</b></font></td>                         
      <td><font size="2"><input type="text" name="user" size="15"></font></td>
      <td><font size="2"><b>$txt[36]:</b></font></td>
      <td><font size="2"><input type="password" name="passwrd" size="10"></font> &nbsp;</td>
    </tr>
    <tr>
      <td><font size="2"><b>$txt[497]:</b></font></td>
      <td><font size="2"><input type=text name="cookielength" size="4" maxlength="4" value="$Cookie_Length"> &nbsp;</font></td>
      <td><font size="2"><b>$txt[508]:</B></font></td>
      <td><font size="2"><input type="checkbox" name="cookieneverexp"></font></td>
    </tr>
    <tr>
      <td align=center colspan=4><input type=submit value="$txt[34]"></td>
    </tr>
   </table></form></font>
  </td>
 </tr>
</table>
EOT;

    footer();
	obExit();
}

?>
