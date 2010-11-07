<?php
/*****************************************************************************/
/* Help.php                                                                  */
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
/* This file gives admins some help in the admin center                      */
/*****************************************************************************/
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> YaBBSE Admin Help </TITLE>
</HEAD>

<BODY BGCOLOR="#FFFFFF">
<TABLE>
<TR>
	<TD>
		<font face="verdana, serif" size="2">
		
		<?

		include("helpadmin.help");
		echo $helptxt[$help];
		?>

		</font>
	</TD>
</TR>
</TABLE>
</BODY>
</HTML>


