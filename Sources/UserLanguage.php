<?php
/*****************************************************************************/
/* UserLanguage.php                                                           */
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

$modsettingsphpver = "YaBB SE 1.3.0";

function LoadLanguage (){
	global $txt, $color,$yytitle,$yyheaderdone,$settings,$db_prefix,$username,$REMOTE_ADDR,$REQUEST_URI,$modSettings,$imagesdir,$mbname,$sourcedir,$language;
	$yytitle = "";
	if (!$yyheaderdone)
		template_header();
$usrlng_result = mysql_query("SELECT value FROM {$db_prefix}settings WHERE variable='userLanguage'");
$temp = mysql_fetch_array($usrlng_result);
$chkusrlng = $temp[0];
$lngfile_result = mysql_query("SELECT lngfile FROM {$db_prefix}members WHERE memberName='$username'");
$temp = mysql_fetch_array($lngfile_result);
$chklngfile = $temp[0];
$chklngfile2 = $temp[0];

		print("Forum Language: $language<br />");
	if ($chkusrlng = 1) {
		if ($chklngfile == Null) {
			echo("You are using the default language: $language");
		} else {
			echo("Your Language: $chklngfile2");
		}
	} else {
		echo("User Language Off: $language");
	}
footer();
exit();
}
?>