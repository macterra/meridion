<?php
/*****************************************************************************/
/* ModSettings.php                                                           */
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

// verify the user is an administrator
is_admin();

function ModifyModSettings() {
	global $txt,$img,$yytitle,$cgi,$imagesdir,$color,$mtxt,$modSettings,$db_prefix;
	$yytitle = $txt['yse2'];
	template_header();
	$stickyTopicsChecked = ($modSettings['enableStickyTopics']=='1')?' checked':'';
	$titlesEnableChecked = ($modSettings['titlesEnable']=='1')?' checked':'';
	$onlineEnableChecked = ($modSettings['onlineEnable']=='1')?' checked':'';
	$todayModChecked = ($modSettings['todayMod']=='1')?' checked':'';
	$previousNextModChecked = ($modSettings['enablePreviousNext']=='1')?' checked':'';
	$enableVBStyleLoginChecked = ($modSettings['enableVBStyleLogin'] == '1')?' checked':'';
	$enableCompressedOutputChecked = ($modSettings['enableCompressedOutput'] == '1')?' checked':'';
	$disableCachingChecked = ($modSettings['disableCaching'] == '1')?' checked':'';
	$enableInlineLinksChecked = ($modSettings['enableInlineLinks'] == '1')?' checked':'';
	$enableSP1Info = ($modSettings['enableSP1Info'] == '1')?' checked':'';
	$enableUserTopicLockingChecked = ($modSettings['enableUserTopicLocking'] == '1')?' checked':'';
	$enableReportToModChecked = ($modSettings['enableReportToMod'] == '1')?' checked':'';
	$enableErrorLoggingChecked = ($modSettings['enableErrorLogging'] == '1')?' checked':'';
	$enableViewNewestFirstChecked = ($modSettings['viewNewestFirst'] == '1')?' checked':'';
  	$enableUserLanguageChecked = ($modSettings['userLanguage'] == '1')?' checked':'';
   	$enableTrackStatsChecked = ($modSettings['trackStats'] == '1')?' checked':'';
    	$enableHitStatsChecked = ($modSettings['hitStats'] == '1')?' checked':'';


	$pollModeDisabled = ($modSettings['pollMode']=='0')?' selected':'';
	$pollModeEnabled = ($modSettings['pollMode']=='1')?' selected':'';
	$pollModeTopics = ($modSettings['pollMode']=='2')?' selected':'';

	$pollRestrictionsNone = ($modSettings['pollPostingRestrictions']=='0')?' selected':'';
	$pollRestrictionsAdminOnly = ($modSettings['pollPostingRestrictions']=='1')?' selected':'';

	$pollEditModeAdmin = ($modSettings['pollEditMode']=='0')?' selected':'';
	$pollEditModeMods = ($modSettings['pollEditMode']=='1')?' selected':'';
	$pollEditModeStarter = ($modSettings['pollEditMode']=='2')?' selected':'';

	$karmaModeDisabled = ($modSettings['karmaMode']=='0')?' selected':'';
	$karmaModeTotal = ($modSettings['karmaMode']=='1')?' selected':'';
	$karmaModePosNeg = ($modSettings['karmaMode']=='2')?' selected':'';
	$karmaModes = explode("|",$txt['yse64']);
	$karmaLabels = explode("|",$txt['yse69']);
	$karmaTRAChecked = ($modSettings['karmaTimeRestrictAdmins'] == '1')?' checked':'';
	$karmaMemberGroups = implode(",",$modSettings['karmaMemberGroups']);

	//Attachment variables added by Meriadoc 12/11/2001
	$attachModeDisabled = ($modSettings['attachmentEnable']=='0')?' selected':'';
	$attachModeEnable = ($modSettings['attachmentEnable']=='1')?' selected':'';
	$attachModeNoNew = ($modSettings['attachmentEnable']=='2')?' selected':'';
	$attachModes = explode("|",$txt['yse111']);
	$attachCheckExten = ($modSettings['attachmentCheckExtensions']=='1')?' checked':'';
	$attachGuest = ($modSettings['attachmentEnableGuest']=='1')?' checked':'';
	$attachShow = ($modSettings['attachmentShowImages']=='1')?' checked':'';

	$notifyAnncmnts_UserDisable = ($modSettings['notifyAnncmnts_UserDisable']=='1')?' checked':'';
	$censorWholeWordChecked = ($modSettings['censorWholeWord']=='1')?' checked':'';
	$compactTopicPagesChecked = ($modSettings['compactTopicPagesEnable']=='1')?' checked':'';

	$request = mysql_query("SELECT membergroup FROM {$db_prefix}membergroups WHERE (ID_GROUP=1 OR ID_GROUP > 7)");
	$membergrps = '';
	while ($row = mysql_fetch_row($request))
		$membergrps .= "<option>".trim($row[0])."</option>";

	print <<<EOT
<form action="$cgi;action=modifyModSettings2" method="POST" name="form1">
<table width="80%" border="0" cellspacing="1" cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
  <td>
  <table border="0" cellspacing="0" cellpadding="4" align="center" width="100%">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]" colspan="2">
    <script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=400,height=200,resizable=no");
        }
		function memberGroups(theForm)
		{
		if (window.popup)
			popup.close();
		popup = window.open("","membergroups","toolbar=no,location=no,status=no,menubar=no,scrollbars=no,width=280,height=300,resizable=no");
		popup.document.write("<html><head><title>$txt[57]</title>\\n");
		popup.document.write("<script language='javascript' TYPE='text/javascript'>\\n");
		popup.document.write("function changeSelectedGroups(listObject) {\\n");
		popup.document.write("var newMemberGroups = '';\\n");
		popup.document.write("for (var i = 0; i < listObject.length; i++){\\n");
		popup.document.write("if (listObject.options[i].selected){\\n");
		popup.document.write("newMemberGroups += listObject.options[i].text +',';\\n");
		popup.document.write("}\\n}\\n");
		popup.document.write("if (newMemberGroups.length > 0){\\n");
		popup.document.write("newMemberGroups=newMemberGroups.substr(0,newMemberGroups.length-1)");
		popup.document.write("}\\nwindow.opener.form1."+theForm+".value=newMemberGroups;\\n}");
		popup.document.write("\\n</script>\\n</head>\\n<body>\\n");
		popup.document.write("<font face='verdana,arial' size=2><b>$txt[57]</b><br>$txt[yse55]\\n");
		popup.document.write("<p align=center><form name='form1'><select size=6 name='myOptions' multiple onchange='changeSelectedGroups(this)'>\\n");
		popup.document.write("$membergrps\\n");
		popup.document.write("</select><br> <br> <input type=button onclick=\"window.close()\" value=\"$txt[17]\"></form></p></font></body></html>");
		}
		// -->
	</script>
    <a href="javascript:reqWin('help.php?help=10')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]"></a>
    <font size="2" class="text1" color="$color[titletext]"><b>$txt[yse2]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" colspan="2"><BR><font size="1">$txt[yse3]</font><BR><BR></td>
  </tr><tr>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[468] $txt[21]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="topicSummaryPosts" value="$modSettings[topicSummaryPosts]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse4]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="stickyTopicsChecked" $stickyTopicsChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse224]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="userLanguageChecked" $enableUserLanguageChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse221]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="trackStatsChecked" $enableTrackStatsChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse222]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="hitStatsChecked" $enableHitStatsChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[title3]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="titlesEnableChecked" $titlesEnableChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[online1]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="onlineEnableChecked" $onlineEnableChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse218]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="enableUserTopicLockingChecked" $enableUserTopicLockingChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse220]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="viewNewestFirstChecked" $enableViewNewestFirstChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse9]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="todayModChecked" $todayModChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse17]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="previousNextModChecked" $previousNextModChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse18]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="PreviousNext_back" value="$modSettings[PreviousNext_back]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse19]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="PreviousNext_forward" value="$modSettings[PreviousNext_forward]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse35]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><select name="pollMode"><option value="0" $pollModeDisabled>$txt[yse34]</option><option value="1" $pollModeEnabled>$txt[yse32]</option><option value="2" $pollModeTopics>$txt[yse33]</option></select></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse36]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><select name="pollPostingRestrictions"><option value="0" $pollRestrictionsNone>$txt[yse38]</option><option value="1" $pollRestrictionsAdminOnly>$txt[yse37]</option></select></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse44]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><select name="pollEditMode"><option value="0" $pollEditModeAdmin>$txt[yse37]</option><option value="1" $pollEditModeMods>$txt[yse45]</option><option value="2" $pollEditModeStarter>$txt[yse46]</option></select></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse51]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="enableVBStyleLoginChecked" $enableVBStyleLoginChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse58]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="enableCompressedOutputChecked" $enableCompressedOutputChecked></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]">&nbsp;</td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="1">$txt[yse59]</font></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse103]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="disableCachingChecked" $disableCachingChecked></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]">&nbsp;</td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="1">$txt[yse104]</font></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse105]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="enableInlineLinksChecked" $enableInlineLinksChecked></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]">&nbsp;</td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="1">$txt[yse106]</font></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse200]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="enableSP1Info" $enableSP1Info></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[rtm12]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="enableReportToModChecked" $enableReportToModChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[errlog3]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="enableErrorLoggingChecked" $enableErrorLoggingChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse70]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><select name="karmaMode"><option value="0" $karmaModeDisabled>$karmaModes[0]</option><option value="1" $karmaModeTotal>$karmaModes[1]</option><option value="2" $karmaModePosNeg>$karmaModes[2]</option></select></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse65]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="karmaTRAChecked" $karmaTRAChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$karmaLabels[0]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="karmaLabel" value="$modSettings[karmaLabel]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$karmaLabels[1]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="karmaSmiteLabel" value="$modSettings[karmaSmiteLabel]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$karmaLabels[2]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="karmaApplaudLabel" value="$modSettings[karmaApplaudLabel]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse68]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="karmaMemberGroups" value="$karmaMemberGroups"> <a href="javascript:memberGroups('karmaMemberGroups');"><img src="$imagesdir/assist.gif" border="0" alt=""></a></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse67]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="karmaMinPosts" value="$modSettings[karmaMinPosts]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse66]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="karmaWaitTime" value="$modSettings[karmaWaitTime]"></td>
<!-- Added form inputs by Meriadoc 12/11/2001 -->
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse110]</font></td>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><select name="attachmentEnable"><option value="0" $attachModeDisabled>$attachModes[0]</option><option value="1" $attachModeEnable>$attachModes[1]</option><option value="2" $attachModeNoNew>$attachModes[2]</option></select></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse127]</font></td>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="attachGuest" $attachGuest></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse113]</font></td>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="attachCheckExten" $attachCheckExten></td>
  </tr><tr>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse112]</font></td>
	<td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="attachShow" $attachShow></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse128]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="attachmentMemberGroups" value="$modSettings[attachmentMemberGroups]"> <a href="javascript:memberGroups('attachmentMemberGroups');"><img src="$imagesdir/assist.gif" border="0" alt=""></a></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse114]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="attachmentExtensions" value="$modSettings[attachmentExtensions]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse115]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="attachmentUploadDir" value="$modSettings[attachmentUploadDir]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse116]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="attachmentUrl" value="$modSettings[attachmentUrl]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse117]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="attachmentSizeLimit" value="$modSettings[attachmentSizeLimit]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse118]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="attachmentDirSizeLimit" value="$modSettings[attachmentDirSizeLimit]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[notifyXAnn1]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="notifyAnncmnts_UserDisable" $notifyAnncmnts_UserDisable></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse231]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="censorWholeWordChecked" $censorWholeWordChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse234]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=checkbox name="compactTopicPagesChecked" $compactTopicPagesChecked></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse235]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]">
      <table border=0>
        <tr>
          <td rowspan=2 valign=center><input type=text name="compactTopicPagesContiguous" value="$modSettings[compactTopicPagesContiguous]"></td>
          <td><font size=1>"3" $txt[yse236]: <b>1 ... 4 [5] 6 ... 9</b></font></td>
        </tr>
        <tr>
          <td><font size=1>"5" $txt[yse236]: <b>1 ... 3 4 [5] 6 7 ... 9</b></font</td>
        </tr>
      </table>
    </td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan=2><HR size="1" width="100%" class="windowbg3"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse_maxwidth]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="maxwidth" value="$modSettings[maxwidth]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><font size="2">$txt[yse_maxheight]</font></td>
    <td class="windowbg2" bgcolor="$color[windowbg2]"><input type=text name="maxheight" value="$modSettings[maxheight]"></td>
  </tr><tr>
    <td class="windowbg2" bgcolor="$color[windowbg2]" colspan="2" align="center" valign="middle">
    <HR size="1" width="100%" class="windowbg3"><input type="submit" value="$txt[10]">
    </td>
  </tr>
</table>
</td>
</tr>
</table>
</form>
EOT;
	footer();
	obExit();
}

function ModifyModSettings2()
{
	global $HTTP_POST_VARS,$db_prefix,$sourcedir;

	// let's do all the checkbox values....
	$onoffArray = array('stickyTopicsChecked'=>'enableStickyTopics', 'titlesEnableChecked'=>'titlesEnable', 'onlineEnableChecked'=>'onlineEnable', 'todayModChecked'=>'todayMod', 'previousNextModChecked'=>'enablePreviousNext', 'enableVBStyleLoginChecked'=>'enableVBStyleLogin', 'enableCompressedOutputChecked'=>'enableCompressedOutput', 'karmaTRAChecked'=>'karmaTimeRestrictAdmins', 'disableCachingChecked'=>'disableCaching', 'enableInlineLinksChecked'=>'enableInlineLinks', 'attachCheckExten'=>'attachmentCheckExtensions', 'attachGuest'=>'attachmentEnableGuest', 'attachShow'=>'attachmentShowImages', 'enableSP1Info'=>'enableSP1Info', 'enableUserTopicLockingChecked'=>'enableUserTopicLocking', 'enableReportToModChecked'=>'enableReportToMod', 'enableErrorLoggingChecked'=>'enableErrorLogging', 'viewNewestFirstChecked'=>'viewNewestFirst',  'userLanguageChecked'=>'userLanguage', 'trackStatsChecked'=>'trackStats', 'hitStatsChecked'=>'hitStats', 'notifyAnncmnts_UserDisable'=>'notifyAnncmnts_UserDisable', 'censorWholeWordChecked'=>'censorWholeWord', 'compactTopicPagesChecked'=>'compactTopicPagesEnable');

	foreach ($onoffArray as $check => $var)
	{
		$onoff = isset($HTTP_POST_VARS[$check]) ? 1 : 0;
		$request = mysql_query("UPDATE {$db_prefix}settings SET value='$onoff' WHERE variable='$var'");
	}

	// now let's do all the textfield/select values
	// set default values
	if (!isset($HTTP_POST_VARS['pollPostingRestrictions'])) { $HTTP_POST_VARS['pollPostingRestrictions']='0'; }
	if (!isset($HTTP_POST_VARS['pollMode'])) { $HTTP_POST_VARS['pollMode']='0'; }
	if (!isset($HTTP_POST_VARS['pollEditMode'])) { $HTTP_POST_VARS['pollEditMode']='0'; }
	if (!isset($HTTP_POST_VARS['PreviousNext_back'])) { $HTTP_POST_VARS['PreviousNext_back']='previous'; }
	if (!isset($HTTP_POST_VARS['PreviousNext_forward'])) { $HTTP_POST_VARS['PreviousNext_forward']='next'; }

	if (!isset($HTTP_POST_VARS['attachmentEnable'])) { $HTTP_POST_VARS['attachmentEnable']='0'; }
	if ($HTTP_POST_VARS['attachmentSizeLimit'] == "") { $HTTP_POST_VARS['attachmentSizeLimit']='0'; }
	if ($HTTP_POST_VARS['attachmentDirSizeLimit'] == "") { $HTTP_POST_VARS['attachmentDirSizeLimit']='0'; }
	if ($HTTP_POST_VARS['attachmentUrl'] == "") { $HTTP_POST_VARS['attachmentUrl']="$boardurl/attachments"; }
	if ($HTTP_POST_VARS['attachmentUploadDir'] == "") { $HTTP_POST_VARS['attachmentUploadDir']="$boarddir/attachments"; }
   if ($HTTP_POST_VARS['maxheight'] == "") { $HTTP_POST_VARS['maxheight']='0'; }
	if ($HTTP_POST_VARS['maxwidth'] == "") { $HTTP_POST_VARS['maxwidth']='0'; }
	if (!isset($HTTP_POST_VARS['compactTopicPagesContiguous'])) { $HTTP_POST_VARS['compactTopicPagesContiguous']='5'; }

 	$textVars = array('PreviousNext_back', 'PreviousNext_forward', 'pollMode', 'pollPostingRestrictions', 'pollEditMode', 'karmaMode', 'karmaLabel', 'karmaSmiteLabel', 'karmaApplaudLabel', 'karmaWaitTime', 'karmaMinPosts', 'karmaMemberGroups', 'attachmentEnable', 'attachmentSizeLimit', 'attachmentDirSizeLimit', 'attachmentUrl', 'attachmentUploadDir', 'attachmentExtensions', 'attachmentMemberGroups','topicSummaryPosts','maxwidth','maxheight', 'compactTopicPagesContiguous');

	foreach($textVars as $txtVar)
		$request = mysql_query("UPDATE {$db_prefix}settings SET value='$HTTP_POST_VARS[$txtVar]' WHERE variable='$txtVar'");

	LoadUserSettings();
	WriteLog();
	include_once("$sourcedir/Admin.php");
	Admin();
}

?>
