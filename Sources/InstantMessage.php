<?php
/*****************************************************************************/
/* InstantMessage.php                                                        */
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

$instantmessageplver="YaBB SE 1.3.1";

function IMIndex (){
	global $username,$txt,$img,$yytitle,$imagesdir,$cgi,$action,$mbname,$censored,$scripturl;
	global $menusep,$color,$ID_MEMBER,$yyUDLoaded,$userprofile,$membergroups,$memberstar,$memberinfo,$icqad;
	global $yimon,$currentboard,$profilebutton,$enable_ubbc,$settings,$allow_hide_email,$db_prefix;
	global $modSettings,$sender;
	if( $username == 'Guest' ) { fatal_error($txt[147]); }
	$imbox = $txt[316];
	$txt[412] = str_replace("IMBOX",$imbox,$txt[412]);
	$img['im_delete'] = str_replace("IMBOX",$imbox,$img['im_delete']);

	// # Fix moderator showing in info
	$sender = "im";

	$yytitle = $txt[143];
	# Build the link tree
	$displayLinkTree = $modSettings['enableInlineLinks'] ? "<font size=\"1\" class=\"nav\"><B><a href=\"$scripturl\" class=\"nav\">$mbname</a> </b>&nbsp;|&nbsp;<b> " : "<font size=\"2\" class=\"nav\"><B><img src=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$scripturl\" class=\"nav\">$mbname</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<a href=\"$cgi&action=im\" class=\"nav\">$txt[144]</a> </b>&nbsp;|&nbsp;<b> " : "<img src=\"$imagesdir/tline.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$cgi&action=im\" class=\"nav\">$txt[144]</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "$txt[316]</b></font>" : "<img SRC=\"$imagesdir/tline2.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;$txt[316]</b></font>" ;

	template_header();

	print <<<EOT
<script language="JavaScript1.2" type="text/javascript"><!--
	function DoConfirm(message, url) {
		if(confirm(message)) location.href = url;
	}
//--></script>
<table border=0 width=100% cellspacing=0 cellpadding="0">
  <tr>
	<td valign=bottom>$displayLinkTree</td>
  </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td>
<table border=0 width="100%" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td align=right valign=bottom class="catbg" bgcolor="$color[catbg]" colspan=4><font size=-1>
EOT;
     /* Load membergroups */
    $request = mysql_query("SELECT membergroup FROM {$db_prefix}membergroups WHERE 1 ORDER BY ID_GROUP");
        $membergroups = array();

     while ($row = mysql_fetch_row($request))
          $membergroups[] = $row[0];

	/* Load Messages */
	$request = mysql_query("SELECT * FROM {$db_prefix}instant_messages WHERE (ID_MEMBER_TO='$ID_MEMBER' AND (deletedBy!=1)) ORDER BY msgtime");
	if( mysql_num_rows($request) > 0 )
		print "    <a href=\"$cgi&action=imremoveall&caller=1\">$img[im_delete]</a>$menusep";

	print <<<EOT
    <a href="$cgi&action=imoutbox">$img[im_outbox]</a>$menusep<a href="$cgi&action=imsend">$img[im_new]</a>$menusep<a href="$cgi&action=im">$img[im_reload]</a>$menusep<a href="$cgi&action=imprefs">$img[im_config]</a>
    </font></td>
  </tr>
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]" width="300"><font size=2 class="text1" color="$color[titletext]">&nbsp;<b>$txt[317]</b></font></td>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[318]</b></font></td>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[319]</b></font></td>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]">&nbsp;</font></td>
  </tr>
EOT;
	if ( mysql_num_rows($request)  == 0 ) {
		print <<<EOT
  <tr>
    <td class="windowbg" colspan=4 bgcolor="$color[windowbg]"><font size=2>$txt[151]</font></td>
  </tr>
EOT;
	}
	$bgcolors = array( $color['windowbg'], $color['windowbg2'] );
	$bgstyles = array ("windowbg", "windowbg2");
	$bgcolornum = sizeof($bgcolors);
	$bgstylenum = sizeof($bgstyles);

	$counter = 0;
	while ($row = mysql_fetch_array($request))
	{
		$counter++;
		$musername = $row['fromName'];
		$msub = $row['subject'];
		$mdate = $row['msgtime'];
		$immessage = $row['body'];
		$messageid = $row['ID_IM'];
		$windowbg = $bgcolors[($counter % $bgcolornum)];
		$windowcss = $bgstyles[($counter % $bgstylenum)];
		if( $msub == '' ) { $msub = $txt[24]; }
		CensorTxt($msub);
		$mydate = timeformat($mdate);
		print<<<EOT
  <tr>
    <td class="$windowcss" bgcolor="$windowbg" width=300><font size=2>$mydate</font></td>
    <td class="$windowcss" bgcolor="$windowbg"><font size=2>$musername</font></td>
    <td class="$windowcss" bgcolor="$windowbg"><font size=2><a href="#$messageid">$msub</a></font></td>
    <td class="$windowcss" bgcolor="$windowbg"><font size=2><a href="javascript:DoConfirm('$txt[154]?','$cgi&action=imremove&caller=1&id=$messageid');">$img[im_remove]</a></font> </td>
  </tr>
EOT;
	}
print <<<EOT
</table>
</td></tr></table>
<br>
EOT;
	if( mysql_num_rows($request) > 0 ) {
		print<<<EOT
<table border=0 width="100%" cellspacing=1 cellpadding="4" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
     <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]">&nbsp;<b>$txt[29]</b></font></td>
     <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[118]</b></font></td>
  </tr>
EOT;


	$request = mysql_query("SELECT * FROM {$db_prefix}instant_messages WHERE (ID_MEMBER_TO=$ID_MEMBER && deletedBy != 1) ORDER BY msgtime");
	$counter = 0;
	while ($row = mysql_fetch_array($request)) {
		$counter++;
		$windowbg = $bgcolors[($counter % $bgcolornum)];
		$windowcss = $bgstyles[($counter % $bgstylenum)];
		$musername = $row['fromName'];
		$msub = $row['subject'];
		$mdate = $row['msgtime'];
		$immessage = $row['body'];
		$messageid = $row['ID_IM'];
		if( $msub == '' ) { $msub = $txt[24]; }
		$mydate = timeformat($mdate);
		$result = mysql_query("SELECT ID_MEMBER FROM {$db_prefix}members WHERE memberName='$musername'");
		# If user is not in memory, s/he must be loaded.
		if( $musername != 'Guest' && ! isset($yyUDLoaded[$musername]) )
			LoadUserDisplay($musername);

		$online ='';
		$title = '';
		if( $yyUDLoaded[$musername] ) {
			$deleted = 0;
			$star = $memberstar[$musername];
			$memberinf = $memberinfo[$musername];
			$icq = $icqad[$musername];
			$yim = $yimon[$musername];
			$euser=urlencode($musername);
			$usernamelink = "<a href=\"$scripturl?board=$currentboard&action=viewprofile&user=$euser\"><font size=2><b>{$userprofile[$musername]['realName']}</b></font></a>";
			$profbutton = ($profilebutton != '' && $musername != 'Guest') ? "$menusep<a href=\"$scripturl?action=viewprofile&user=$euser\">$img[viewprofile]</a>" : '';
			$postinfo = "$txt[26]: {$userprofile[$musername]['posts']}<br>";
			$memail = $userprofile[$musername]['emailAddress'];
			if ($modSettings['titlesEnable'] != '0' && $userprofile[$musername]['usertitle'] != '')
				$title = "{$userprofile[$musername]['usertitle']}<br>";
			if ($modSettings['onlineEnable'] != '0') {
				if (OnlineStatus($musername) > 0) { $online = "$txt[online2]<br><br>\n"; } else $online = "$txt[online3]<br><br>\n";
			} else $online = '';
		}
		else // user has been deleted
		{
			$deleted = 1;
			$star = '';
			$memberinf = '';
			$icq = '';
			$yim = '';
			$usernamelink = "$musername";
			$postinfo = '';
			$memail = '';
			$profbutton = '';
		}

		CensorTxt($immessage);
		CensorTxt($msub);

		$message = $immessage; # put the message back in the proper variable for doing ubbc
		if($enable_ubbc) { $message = DoUBBC($message); }
		print <<<EOT
  <tr>
    <td class="$windowcss" bgcolor="$windowbg" width="160" valign="top" height="100%">
    $usernamelink<br><font size=1>$title
    $memberinf<br>
    $star<br><br>$online
    $postinfo
	{$userprofile[$musername]['gender']}
    <center>{$userprofile[$musername]['avatar']} {$userprofile[$musername]['personalText']} {$userprofile[$musername]['ICQ']} $icq &nbsp; {$userprofile[$musername]['YIM']} $yim &nbsp; {$userprofile[$musername]['AIM']}
    </center></font></td>
    <td class="$windowcss" bgcolor="$windowbg" valign=top>
    <table border="0" cellspacing="0" cellpadding="3" width="100%" height="100%" align="center" bgcolor="$color[bordercolor]" class="bordercolor">
      <tr class="$windowcss" bgcolor="$windowbg" height="10" width="100%">
        <td class="$windowcss" bgcolor="$windowbg"><a name="$messageid"><font size=1>&nbsp;<b>$msub</b></font></td>
        <td class="$windowcss" bgcolor="$windowbg" align="right"><font size=1><b>$txt[30]:</b> $mydate</font></td>
      </tr><tr height="*">
        <td colspan="2" class="$windowcss" bgcolor="$windowbg" height="100%">
        <hr width="100%" size="1" color="$color[windowbg3]">
        <font size=2>$message</font>
        </td>
      </tr><TR height="10">
        <td colspan="2" class="$windowcss" bgcolor="$windowbg" height="10">
        <font size=2>{$userprofile[$musername]['signature']}</font>
        <hr width="100%" size="1" color="$color[windowbg3]">
        </td>
      </tr><tr height="10" width="100%">
	<td class="$windowcss" bgcolor="$windowbg" height="10">
        <font size=2>
EOT;
if ($userprofile[$musername]['hideEmail'] != '1' || $settings[7] == "Administrator" || $allow_hide_email != 1)
	print ("	{$userprofile[$musername]['websiteUrl_IM']}<a href=\"mailto:$memail\">$img[email]</a>$profbutton\n");
else
	print ("	{$userprofile[$musername]['websiteUrl_IM']}$profbutton\n");

print <<<EOT
        </font></td>
        <td class="$windowcss" bgcolor="$windowbg" height="10" align="right"><font size=2>
EOT;
if (!$deleted) {
	print <<<EOT
	<a href="$cgi&action=imsend&caller=1&imsg=$messageid&quote=1&to=$musername">$img[replyquote]</a>$menusep<a href="$cgi&action=imsend&caller=1&imsg=$messageid&reply=1&to=$musername">$img[im_reply]</a>$menusep
EOT;
}
print <<<EOT
	<a href="javascript:DoConfirm('$txt[154]?','$cgi&action=imremove&caller=1&id=$messageid');">$img[im_remove]</a>
        </font></td>
      </tr>
    </table>
    </td>
  </tr>
EOT;
	}
	print <<<EOT
</table>
EOT;
}
	footer();
	obExit();
}

function IMOutbox (){
	global $username,$txt,$img,$yytitle,$imagesdir,$cgi,$action,$mbname,$censored,$scripturl;
	global $menusep,$color,$ID_MEMBER,$yyUDLoaded,$userprofile,$membergroups,$memberstar,$memberinfo,$icqad;
	global $yimon,$currentboard,$profilebutton,$enable_ubbc,$settings,$allow_hide_email,$db_prefix;
	global $modSettings,$sender;
	if ($username == 'Guest') { fatal_error($txt[147]); }
	$imbox = $txt[320];
	$txt[412] = str_replace("IMBOX",$imbox,$txt[412]);
	$img['im_delete'] = str_replace("IMBOX",$imbox,$img['im_delete']);

	# Fix moderator showing in info
	$sender = "im";

	$yytitle = $txt[143];
	template_header();

	# Build the link tree
	$displayLinkTree = $modSettings['enableInlineLinks'] ? "<font size=\"1\" class=\"nav\"><B><a href=\"$scripturl\" class=\"nav\">$mbname</a> </b>&nbsp;|&nbsp;<b> " : "<font size=\"2\" class=\"nav\"><B><img src=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$scripturl\" class=\"nav\">$mbname</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<a href=\"$cgi&action=im\" class=\"nav\">$txt[144]</a> </b>&nbsp;|&nbsp;<b> " : "<img src=\"$imagesdir/tline.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$cgi&action=im\" class=\"nav\">$txt[144]</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "$txt[320]</b></font>" : "<img SRC=\"$imagesdir/tline2.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;$txt[320]</b></font>" ;
	print <<<EOT
<script language="JavaScript1.2" type="text/javascript"><!--
	function DoConfirm(message, url) {
		if(confirm(message)) location.href = url;
	}
//--></script>
<table border=0 width=100% cellspacing=0>
  <tr>
    <td valign=bottom>$displayLinkTree</td>
  </tr><tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td>
<table border=0 width="100%" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td align=right valign=bottom class="catbg" bgcolor="$color[catbg]" colspan=4><font size=-1>
EOT;
     /* Load membergroups */
    $request = mysql_query("SELECT membergroup FROM {$db_prefix}membergroups WHERE 1 ORDER BY ID_GROUP");
        $membergroups = array();

     while ($row = mysql_fetch_row($request))
          $membergroups[] = $row[0];

	# Read all messages
	$request = mysql_query("SELECT * FROM {$db_prefix}instant_messages WHERE (ID_MEMBER_FROM=$ID_MEMBER && deletedBy != 0) ORDER BY msgtime");

	if( mysql_num_rows($request) > 0 )
		print "    <a href=\"$cgi&action=imremoveall&caller=2\">$img[im_delete]</a>$menusep";
	print <<<EOT
    <a href="$cgi&action=im">$img[im_inbox]</a>$menusep<a href="$cgi&action=imsend">$img[im_new]</a>$menusep<a href="$cgi&action=im">$img[im_reload]</a>$menusep<a href="$cgi&action=imprefs">$img[im_config]</a>
    </font></td>
  </tr>
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]" width="300"><font size=2 class="text1" color="$color[titletext]">&nbsp;<b>$txt[317]</b></font></td>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[324]</b></font></td>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[319]</b></font></td>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]">&nbsp;</font></td>
  </tr>
EOT;
	# Display Message if there are no Messages in Users Outbox
	if ( mysql_num_rows($request) == 0) {
		print <<<EOT
  <tr>
    <td class="windowbg" colspan=4 bgcolor="$color[windowbg]"><font size=2>$txt[151]</font></td>
  </tr>
EOT;
	}

	$bgcolors = array( $color['windowbg'], $color['windowbg2'] );
	$bgstyles = array("windowbg","windowbg2");
	$bgcolornum = sizeof($bgcolors);
	$bgstylenum = sizeof($bgstyles);

	$counter = 0;
	while ($row = mysql_fetch_array($request)) {
		$counter++;
		$windowbg = $bgcolors[($counter % $bgcolornum)];
		$windowcss = $bgstyles[($counter % $bgstylenum)];
		$musername = $row['toName'];
		$msub = $row['subject'];
		$mdate = $row['msgtime'];
		$immessage = $row['body'];
		$messageid = $row['ID_IM'];
		if( $msub == '' ) { $msub = $txt[24]; }

		CensorTxt($msub);

		$mydate = timeformat($mdate);
		print<<<EOT
  <tr>
    <td class="$windowcss" bgcolor="$windowbg" width=300><font size=2>$mydate</font></td>
    <td class="$windowcss" bgcolor="$windowbg"><font size=2>$musername</font></td>
    <td class="$windowcss" bgcolor="$windowbg"><font size=2><a href="#$messageid">$msub</a></font></td>
    <td class="$windowcss" bgcolor="$windowbg"><font size=2><a href="javascript:DoConfirm('$txt[154]?','$cgi&action=imremove&caller=2&id=$messageid');">$img[im_remove]</a></font></td>
  </tr>
EOT;
	}
print <<<EOT
</table>
</td></tr></table>
<br>
EOT;
	if(mysql_num_rows($request) > 0) {
		print <<<EOT
<table border=0 width=100% cellspacing=1 cellpadding="4" bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]">&nbsp;<b>$txt[535]</b></font></td>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[118]</b></font></td>
  </tr>
EOT;

	$request = mysql_query("SELECT * FROM {$db_prefix}instant_messages WHERE (fromName='$username' && deletedBy != 0) ORDER BY msgtime");
	$counter = 0;
	while ($row = mysql_fetch_array($request)) {
		$counter++;
		$windowbg = $bgcolors[($counter % $bgcolornum)];
		$windowcss = $bgstyles[($counter % $bgstylenum)];
		$musername = $row['toName'];
		$msub = $row['subject'];
		$mdate = $row['msgtime'];
		$immessage = $row['body'];
		$messageid = $row['ID_IM'];

		if( $msub == '' ) { $msub = $txt[24]; }
		$mydate = timeformat($mdate);
		$result = mysql_query("SELECT ID_MEMBER FROM {$db_prefix}members WHERE memberName='$musername'");
		# If user is not in memory, s/he must be loaded.
		$online = '';
		$title = '';
		if( $musername != 'Guest' && ! isset($yyUDLoaded[$musername]) && mysql_num_rows($result)>0 )
			LoadUserDisplay($musername);
		if( $yyUDLoaded[$musername] ) {
			$star = $memberstar[$musername];
			$memberinf = $memberinfo[$musername];
			$icq = $icqad[$musername];
			$yim = $yimon[$musername];
			$euser=urlencode($musername);
			$usernamelink = "<a href=\"$scripturl?board=$currentboard&action=viewprofile&user=$euser\"><font size=2><B>{$userprofile[$musername]['realName']}</b></font></a>";
			$profbutton = ($profilebutton != '' && $musername != 'Guest') ? "$menusep<a href=\"$scripturl?action=viewprofile&user=$euser\">$img[viewprofile]</a>" : '';
			$postinfo = "$txt[26]: {$userprofile[$musername]['posts']}<br>";
			$memail = $userprofile[$musername]['emailAddress'];
			if ($modSettings['titlesEnable'] != '0' && $userprofile[$musername]['usertitle'] != '')
				$title = "{$userprofile[$musername]['usertitle']}<br>";
			if ($modSettings['onlineEnable'] != '0') {
				if (OnlineStatus($musername) > 0) { $online = "$txt[online2]<br><br>\n"; } else $online = "$txt[online3]<br><br>\n";
			} else $online = '';
		}
		$message = $immessage; # put the message back in the proper variable for doing ubbc

		CensorTxt($immessage);
		CensorTxt($msub);

		$message = $immessage; # put the message back in the proper variable for doing ubbc
		if($enable_ubbc) { $message = DoUBBC($message); }
		print <<<EOT
  <tr>
    <td class="$windowcss" bgcolor="$windowbg" width="160" valign="top" height="100%">
    $usernamelink<br><font size=1>$title
    $memberinf<br>
    $star<br><br>$online
    $postinfo
	{$userprofile[$musername]['gender']}
    <center>{$userprofile[$musername]['avatar']}{$userprofile[$musername]['personalText']}
	{$userprofile[$musername]['ICQ']} $icq &nbsp; {$userprofile[$musername]['YIM']} $yim &nbsp; {$userprofile[$musername]['AIM']}
    </center></font></td>
    <td class="$windowcss" bgcolor="$windowbg" valign=top>
    <table border="0" cellspacing="0" cellpadding="3" width="100%" height="100%" align="center" bgcolor="$color[bordercolor]" class="bordercolor">
      <tr class="$windowcss" bgcolor="$windowbg" height="10" width="100%">
        <td class="$windowcss" bgcolor="$windowbg"><font size="1"><a name="$messageid">&nbsp;<b>$msub</b></font></td>
        <td class="$windowcss" bgcolor="$windowbg" align="right"><font size=1><b>$txt[30]:</b> $mydate</font></td>
      </tr><tr height="100%">
        <td colspan="2" class="$windowcss" bgcolor="$windowbg" height="100%">
        <hr width="100%" size="1" color="$color[windowbg3]">
        <font size=2>$message</font>
        </td>
      </tr><TR height="10">
        <td colspan="2" class="$windowcss" bgcolor="$windowbg" height="10">
        <font size=2>{$userprofile[$musername]['signature']}</font>
        <hr width="100%" size="1" color="$color[windowbg3]">
        </td>
      </tr><tr height="10" width="100%">
	<td class="$windowcss" bgcolor="$windowbg" height="10">
        <font size=2>
EOT;
if ($userprofile[$musername]['hideEmail'] != "1" || $settings[7] == "Administrator" || $allow_hide_email != 1)
	print "{$userprofile[$musername]['websiteUrl_IM']}<a href=\"mailto:$memail\">$img[email]</a>$profbutton\n";
else
	print "{$userprofile[$musername]['websiteUrl_IM']}$profbutton\n";
print <<<EOT
        </font></td>
        <td class="$windowcss" bgcolor="$windowbg" align="right" height="10"><font size=2>
        <a href="$cgi&action=imsend&caller=2&imsg=$messageid&quote=1&to=$musername">$img[replyquote]</a>$menusep<a href="$cgi&action=imsend&caller=2&imsg=$messageid&reply=1&to=$musername">$img[im_reply]</a>$menusep<a href="javascript:DoConfirm('$txt[154]?','$cgi&action=imremove&caller=2&id=$messageid');">$img[im_remove]</a>        </font></td>
      </tr>
    </table>
    </td>
  </tr>
EOT;
	}
	print "</table>\n";
	}
	footer();
	obExit();
}


function IMPost(){
	global $username,$txt,$yytitle,$cgi,$img,$imagesdir,$color,$ubbcjspath,$mbname,$menusep,$imsg;
	global $showyabbcbutt,$enable_ubbc,$form_subject,$scripturl,$to,$form_message,$reply,$quote;
	global $sourcedir,$db_prefix,$modSettings,$ID_MEMBER;
	if($username == 'Guest') { fatal_error($txt[147]); }
	$yytitle=$txt[148];
	template_header();
	if(isset($imsg)) {
		$request = mysql_query ("SELECT * FROM {$db_prefix}instant_messages WHERE (ID_IM='$imsg' && (ID_MEMBER_TO='$ID_MEMBER' || ID_MEMBER_FROM='$ID_MEMBER'))");
		if (mysql_num_rows($request)==0)
			fatal_error("Hacker?");

		$row  = mysql_fetch_array($request);
		$mfrom = $row['fromName'];
		$msubject = $row['subject'];
		$mdate = $row['msgtime'];
		$mmessage = $row['body'];
		$messageid = $row['ID_IM'];

		$form_subject = $msubject;
		if (!stristr(substr($form_subject,0,3),"re:") && ($reply=='1' || $quote=='1'))
			$form_subject = "Re:$form_subject";

		if($quote=='1') {
			$mmessage = str_replace("<br>","\n",$mmessage);
			$mmessage = preg_replace("/[quote](\S+?)\[\/quote]/","...",$mmessage);
			$form_message = "[quote] $mmessage [/quote]";
		}
	}

	$form_subject = isset($form_subject)?$form_subject:$txt[24];
	# Build the link tree
	$displayLinkTree = $modSettings['enableInlineLinks'] ? "<font size=\"1\" class=\"nav\"><B><a href=\"$scripturl\" class=\"nav\">$mbname</a> </b>&nbsp;|&nbsp;<b> " : "<font size=\"2\" class=\"nav\"><B><img src=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$scripturl\" class=\"nav\">$mbname</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<a href=\"$cgi&action=im\" class=\"nav\">$txt[144]</a> </b>&nbsp;|&nbsp;<b> " : "<img src=\"$imagesdir/tline.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$cgi&action=im\" class=\"nav\">$txt[144]</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "$txt[321]</b></font>" : "<img SRC=\"$imagesdir/tline2.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;$txt[321]</b></font>" ;

	print <<<EOT
<script language="JavaScript1.2" src="$ubbcjspath" type="text/javascript"></script>
<table border=0 width="700" cellpadding="3" align="center" cellspacing=0>
<tr>
	<td valign=bottom>$displayLinkTree</td>
</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="700" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td>
<table border=0 width="700" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td align=right valign=bottom class="catbg" bgcolor="$color[catbg]" colspan=4><font size=-1>
EOT;
	print <<<EOT
<a href="$cgi;action=im">$img[im_inbox]</a>$menusep<a href="$cgi;action=imoutbox">$img[im_outbox]</a>$menusep<a href="$cgi;action=im">$img[im_reload]</a>$menusep<a href="$cgi;action=imprefs">$img[im_config]</a>
</font></td>
</table>
</td></tr></table>
<table border=0 width="700" align="center" cellpadding="3" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]">$img[im_new_small]&nbsp;<b>$txt[321]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <form action="$cgi;action=imsend2" method="post" name="postmodify" onSubmit="submitonce(this);">
    <table border=0 cellpadding=3>
      <tr>
        <td align="right"><font size=2><b>$txt[150]:</b></font></td>
        <td><font size=2><input type=text name="to" value="$to" size="20" maxlength="50">
        <font size=1">$txt[748]</font></font></td>
      </tr><tr>
        <td align="right"><font size=2><b>$txt[70]:</b></font></td>
        <td><font size=2><input type=text name="subject" value="$form_subject" size="40" maxlength="50"></font></td>
      </tr>
EOT;
	  include_once("$sourcedir/Post.php");
	  printPostBox($form_message);
print <<<EOT
     <tr>
      <td align=center colspan=2>
       <input type="hidden" name="waction" value="imsend">
       <input type="submit" value="$txt[148]" onClick="WhichClicked('imsend');" accesskey="s">
       <input type="submit" name="preview" value="$txt[507]" onClick="WhichClicked('previewim');">
       <input type=reset value="$txt[329]">
      </td>
     </tr>
    </table>
   </form>
  </td>
 </tr>
</table>
EOT;
	footer();
	obExit();
}

function IMPost2()
{
global $subject,$username,$txt,$waction,$subject,$settings,$MaxMessLen,$message,$to,$yySetLocation,$cgi;
global $sourcedir,$db_prefix;
$nouser = array();
if($username == 'Guest') { fatal_error($txt[147]); }
$subject = trim ($subject);
if (strlen($message)>$MaxMessLen) { fatal_error($txt[499]); }

if ($subject==''){ fatal_error("$txt[77]"); }
if ($message==''){ fatal_error("$txt[78]"); }

$mmessage = $message;
$msubject = $subject;

if( $waction == 'previewim' ) {
	include_once ("$sourcedir/Post.php");
	Preview();
}
else {
	$message = htmlspecialchars($message);
	$subject = htmlspecialchars($subject);
}

$message = preparsecode($message, $realname, $name);

$message = str_replace("\r","",$message);
$message = str_replace("\n","<br>",$message);

$multiple = explode(",", $to);
foreach ($multiple as $db) {
	$db = trim($db);
	$ignored = 0;
	$db = preg_replace("/[^0-9A-Za-z#%+,-\.@^_ ]/","",$db);

	# Check Ignore-List
	$request = mysql_query("SELECT im_ignore_list,ID_MEMBER,im_email_notify,emailAddress FROM {$db_prefix}members WHERE memberName='$db'");
	$row = mysql_fetch_row($request);
	$ignore = explode(",",$row[0]);
	$toID = $row[1];
	$emailNotify = $row[2];
	$notifyAddress = $row[3];

	# If User is on Recipient's Ignore-List, show Error Message
	foreach ($ignore as $igname) {
		#adds ignored user's name to array which error list will be built from later
		if ($igname == $username || $igname == "*") {
			$nouser[] = $db;
			$ignored = 1;
		}
	}

	if (mysql_num_rows($request)==0) {
		#adds invalid user's name to array which error list will be built from later
		$nouser[] = $db;
		$ignored = 1;
	}

    if (get_magic_quotes_gpc()==0) {
        $subject = mysql_escape_string($subject);
        $message = mysql_escape_string($message);
        }

	if(!$ignored) {
	$result = mysql_query("SELECT ID_MEMBER FROM {$db_prefix}members WHERE memberName='$username'");
	$row = mysql_fetch_row($result);
	$fromID = $row[0];
	$result = mysql_query("INSERT INTO {$db_prefix}instant_messages (ID_MEMBER_FROM,ID_MEMBER_TO,fromName,toName,msgtime,subject,body) VALUES ($fromID,$toID,'$username','$db',".time().",'$subject','$message')");

	# Send notification
	if ($emailNotify==1) {
		$mydate = timeformat(time());
		if ($notifyAddress != ""){
			$fromname = $settings[1];
			$txt[561] = str_replace ("SUBJECT",$msubject,$txt[561]);
			$txt[561] = str_replace ("SENDER",$fromname,$txt[561]);
			$txt[562] = str_replace ("DATE",$mydate,$txt[562]);
   			$txt[562] = str_replace ("MESSAGE",$mmessage,$txt[562]);
			$txt[562] = str_replace ("SENDER",$fromname,$txt[562]);
   			sendmail($notifyAddress,$txt[561],$txt[562]);
		}
	}
	}
}  #end foreach loop

#if there were invalid usernames in the recipient list, these names are listed after all valid users have been IMed
if (sizeof($nouser) > 0) {
	$badusers = join(", ", $nouser);
	fatal_error("$badusers $txt[747]");
}

$yySetLocation = "$cgi;action=im";
redirectexit();
}

function IMRemove()
{
	global $username,$txt,$yySetLocation,$cgi,$caller,$id,$db_prefix, $ID_MEMBER;
	if($username == 'Guest') { fatal_error($txt[147]); }
	if ($caller == 1)
		$field = "1";
	else
		$field = "0";

	$check = mysql_query("SELECT deletedBy FROM {$db_prefix}instant_messages WHERE (ID_IM=$id AND (ID_MEMBER_TO='$ID_MEMBER' || ID_MEMBER_FROM='$ID_MEMBER'))");

	if(mysql_num_rows($check) < 1)
		fatal_error ("Hacker?");

	$request = mysql_query ("SELECT deletedBy FROM {$db_prefix}instant_messages WHERE (ID_IM=$id AND deletedBy!=-1)");
	if (mysql_num_rows($request) > 0)
		$request = mysql_query("DELETE FROM {$db_prefix}instant_messages WHERE ID_IM=$id");
	else
		$request = mysql_query ("UPDATE {$db_prefix}instant_messages SET deletedBy=$field WHERE ID_IM=$id");

	$redirect = ($caller == 1) ? 'im' : 'imoutbox';
	$yySetLocation = "$cgi;action=$redirect";
	redirectexit();
}

function IMPreferences (){
	global $username,$txt,$ID_MEMBER,$yytitle,$cgi,$img,$imagesdir,$scripturl,$color,$mbname;
	global $db_prefix, $settings, $menusep, $color, $modSettings;

	if ($username == 'Guest') { fatal_error($txt[147]); }
	$request = mysql_query("SELECT im_ignore_list,im_email_notify FROM {$db_prefix}members WHERE ID_MEMBER=$ID_MEMBER");
	$imconfig = mysql_fetch_row($request);

	$sel0 = $sel1 = '';
	if ($imconfig[1]) {
		$sel0='';
		$sel1=' selected';
	} else {
		$sel0=' selected';
		$sel1='';
	}
	$ignores = str_replace(",","\n",$imconfig[0]);
	$yytitle = "$txt[323]: $txt[144]";
	# Build the link tree
	$displayLinkTree = $modSettings['enableInlineLinks'] ? "<font size=\"1\" class=\"nav\"><B><a href=\"$scripturl\" class=\"nav\">$mbname</a> </b>&nbsp;|&nbsp;<b> " : "<font size=\"2\" class=\"nav\"><B><img src=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$scripturl\" class=\"nav\">$mbname</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<a href=\"$cgi&action=im\" class=\"nav\">$txt[144]</a> </b>&nbsp;|&nbsp;<b> " : "<img src=\"$imagesdir/tline.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<a href=\"$cgi&action=im\" class=\"nav\">$txt[144]</a><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "$txt[323]</b></font>" : "<img SRC=\"$imagesdir/tline2.gif\" border=\"0\" alt=\"\"><IMG SRC=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;$txt[323]</b></font>" ;
	template_header();
	print <<<EOT
<table border=0 width=100% cellspacing=0>
<tr>
	<td valign=bottom>$displayLinkTree</td>
		</tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td>
<table border=0 width="100%" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor">
  <tr>
    <td align=right valign=bottom class="catbg" bgcolor="$color[catbg]" colspan=4><font size=-1>
<a href="$cgi;action=im">$img[im_inbox]</a>$menusep<a href="$cgi;action=imoutbox">$img[im_outbox]</a>$menusep<a href="$cgi;action=imsend">$img[im_new]</a>$menusep<a href="$cgi;action=im">$img[im_reload]</a>
</font>
</td>
</table>
</td></tr></table>
<table border=0 width=100% cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor">
 <tr>
  <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]">$img[im_config_small]&nbsp;<b>$txt[323]</b></font></td>
 </tr>
 <tr>
  <td class="windowbg" bgcolor="$color[windowbg]">
   <form action="$cgi;action=imprefs2" method=post>
    <table border=0>
     <tr>
      <td valign=top>
       <font size=2><b>$txt[325]:</b></font><br><font size=1>$txt[326]</font>
      </td>
      <td>
       <font size=2><textarea name=ignore rows=10 cols=50 wrap=virtual>$ignores</textarea></font>
      </td>
     </tr>
     <tr>
      <td valign=top>
       <font size=2><b>$txt[327]:</b></font>
      </td>
      <td>
       <font size=2>
	<select name="notify">
	 <option value="0"$sel0>$txt[164]
	 <option value="1"$sel1>$txt[163]
	</select>
       </font>
      </td>
     </tr>
     <tr>
      <td>
      	&nbsp;
      </td>
      <td>
       <input type=submit value="$txt[328]">
       <input type=reset value="$txt[329]">
      </td>
     </tr>
    </table>
   </form>
  </td>
 </tr>
</table>
EOT;
	footer();
	obExit();
}

function IMPreferences2 () {
	global $username,$ID_MEMBER,$yySetLocation,$ignore,$notify,$cgi,$db_prefix;
	if($username == 'Guest') { fatal_error($txt[147]); }
	$ignorelist = $ignore;

	$ignorelist = str_replace("\n",",",trim($ignorelist));
	$request = mysql_query("UPDATE {$db_prefix}members SET im_ignore_list='$ignorelist',im_email_notify=$notify WHERE ID_MEMBER=$ID_MEMBER");
	$yySetLocation = "$cgi;action=imprefs";
	redirectexit();
}

function KillAll (){
	global $username,$caller,$id,$ID_MEMBER,$cgi,$yySetLocation,$db_prefix;
	if($username == 'Guest') { fatal_error($txt[147]); }
	if ($caller == 1) {
		$field2 = "0";
		$field = "ID_MEMBER_TO";
		$field3 = "1";
		$redirect = 'im';
	} else if ($caller == 2) {
		$redirect = 'imoutbox';
		$field2 = "1";
		$field = "ID_MEMBER_FROM";
		$field3 = "0";
	}

	$request = mysql_query("DELETE FROM {$db_prefix}instant_messages WHERE ($field=$ID_MEMBER && deletedBy = $field2)");
	$request = mysql_query ("UPDATE {$db_prefix}instant_messages SET deletedBy=$field3 WHERE $field=$ID_MEMBER");

	$redirect = ($caller == 1) ? 'im' : 'imoutbox';

	$yySetLocation = "$cgi;action=$redirect";
	redirectexit();
}

function KillAllQuery (){
	global $username,$txt,$cgi,$yytitle,$img,$caller,$color,$yySetLocation,$db_prefix;
	$imbox = '';
	if($username == 'Guest') { fatal_error($txt[147]); }
	if ($caller == 1) {
		$yytitle .= $txt[316];
		$imbox = $txt[316];
		$cgi2 = "$cgi&action=imremoveall2&caller=1";
		$cgi .= "&action=im";
	} else if ($caller == 2) {
		$yytitle .= $txt[320];
		$imbox = $txt[320];
		$cgi2 = "$cgi&action=imremoveall2&caller=2";
		$cgi .= "&action=imoutbox";
	}
	$txt[412] = str_replace("IMBOX",$imbox,$txt[412]);
	$img['im_delete'] = str_replace("IMBOX",$imbox,$img['im_delete']);
	template_header();
	$yytitle = $txt[412];
	print <<<EOT
<table border=0 width="80%" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor" align="center">
<tr>
	<td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[412]</b></font></td>
</tr>
<tr>
	<td class="windowbg" bgcolor="$color[windowbg]"><font size=2>
$txt[413]<br>
<b><a href="$cgi2">$txt[163]</a> - <a href="$cgi">$txt[164]</a></b>
</font></td>
</tr>
</table>
EOT;
	footer();
	obExit();
}

?>
