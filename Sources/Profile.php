<?php
/*****************************************************************************/
/* Profile.php                                                               */
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

$profileplver="YaBB SE 1.3.1";

function ModifyProfile (){
	global $user,$username,$txt,$yytitle,$settings,$img,$cgi,$timeformatstring,$color,$imagesdir;
	global $GenderMale,$GenderFemale,$allowpics,$facesdir,$userpic_limits,$facesurl,$allow_hide_email,$boarddir,$userpic_width,$userpic_height;
	global $MaxSigLen,$timeformatstring,$db_prefix,$locale,$modSettings,$language,$timeoffset;

	$user=urldecode($user);
	if($username != $user && $settings[7] != 'Administrator')
		fatal_error($txt[80]);

	$yytitle = $txt[79];
	$request = mysql_query("SELECT passwd,realName,emailAddress,websiteTitle,websiteUrl,signature,posts,memberGroup,ICQ,AIM,YIM,gender,personalText,avatar,dateRegistered,location,birthdate,timeFormat,timeOffset,hideEmail,ID_MEMBER,usertitle,karmaBad,karmaGood,lngfile,MSN,secretQuestion,secretAnswer FROM {$db_prefix}members WHERE memberName='$user'");
	$memsettings = mysql_fetch_array($request);

	$timeadjust = ((isset($settings[18])?$settings[18]:0) + $timeoffset) * 3600;
	if ($memsettings['dateRegistered'] && $memsettings['dateRegistered'] != '0000-00-00') {
		$fmt = "%d %b %Y " . (substr_count($settings[17], "%H") == 0?"%I:%M:%S %p":"%T");
		$dr = strftime($fmt, $memsettings['dateRegistered'] + $timeadjust);
	}
	else
		$dr = $txt[470];

	if (isset($memsettings['gender'])) {
		if ($memsettings['gender'] == 'Male')
			$GenderMale = ' selected';
		else if ($memsettings['gender'] == 'Female')
			$GenderFemale = ' selected';
	}
	$signature = isset($memsettings['signature'])?$memsettings['signature']:'';
	$signature = str_replace("&lt;","<",$signature);
	$signature = str_replace("&gt;",">",$signature);
	list($uyear,$umonth,$uday) = explode("-",$memsettings['birthdate']);

	$memsettings['AIM'] = isset($memsettings['AIM'])?str_replace("+"," ",$memsettings['AIM']):'';

	// set up the default values
	$memsettings['realName'] = isset($memsettings['realName'])?$memsettings['realName']:'';
	$memsettings['usertitle'] = isset($memsettings['usertitle'])?$memsettings['usertitle']:'';
	$memsettings['location'] = isset($memsettings['location'])?$memsettings['location']:'';
	$memsettings['websiteTitle'] = isset($memsettings['websiteTitle'])?$memsettings['websiteTitle']:'';
	$memsettings['websiteUrl'] = isset($memsettings['websiteUrl'])?$memsettings['websiteUrl']:'';
	$memsettings['ICQ'] = isset($memsettings['ICQ'])?$memsettings['ICQ']:'';
	$memsettings['YIM'] = isset($memsettings['YIM'])?$memsettings['YIM']:'';
	$memsettings['timeFormat'] = isset($memsettings['timeFormat'])?$memsettings['timeFormat']:'';
	$memsettings['timeOffset'] = isset($memsettings['timeOffset'])?$memsettings['timeOffset']:'0';
    $memsettings['lngfile'] = isset($memsettings['lngfile'])?$memsettings['lngfile']:$language;

	// create the custom title field if allowed
	$userTitleField = '';
	if ($modSettings['titlesEnable'] == 1)
	{
		if ($settings[7]=='Administrator' || $settings[7]=='Global Moderator')
			$userTitleField = "<td width=\"45%\"><font size=2><b>$txt[title1]: </b></font></td>\n<td><input type=\"text\" name=\"usertitle\" size=\"30\" value=\"$memsettings[usertitle]\"></font></td>\n	  </tr><tr>";
		else
			$userTitleField = "<td width=\"45%\"><font size=2><b>$txt[title1]: </b></font></td>\n<td>$memsettings[usertitle]</td>\n	  </tr><tr>";
	}

	template_header();

	$proftime = date("h:i:s a", time() + $timeoffset * 3600);
	$maxAvatarWidth = Max($userpic_width, 100);
	$maxAvatarHeight = Max($userpic_height, 100);
    $ptext=$memsettings[personalText];
    $ptext = str_replace ("&quot", "&quot;", $ptext);
    $ptext = str_replace ("&#039", "'", $ptext);
    $ptext = str_replace ("&amp", "&", $ptext);
    $ptext = str_replace ("&lt", "<", $ptext);
    $ptext = str_replace ("&gt", ">", $ptext);

	print <<<EOT

<form action="$cgi;action=profile2" method="POST" name="creator">
<table border=0 width="80%" cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]" height="30">
    &nbsp;<img src="$imagesdir/profile_sm.gif" alt="" border="0">&nbsp;
    <font size=2 class="text1" color="$color[titletext]"><b>$txt[79]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]" height="25"><BR><font size=1>$txt[698]</font><BR><BR></td>
  </tr><tr>
    <td class="catbg" bgcolor="$color[catbg]" height="25"><font size=2><b>$txt[517]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <table border=0 width="100%" cellpadding="3">
      <tr>
	<td width="45%"><font size=2><b>$txt[35]: </b></font></td>
	<td><font size=2><input type="hidden" name="userID" value="$memsettings[ID_MEMBER]"><input type="hidden" name="user" value="$user">$user</font></td>
      </tr><tr>
	$userTitleField
	<td width="45%"><font size=2><b>$txt[81]: </b></font><BR>
	<font size=1>$txt[596]</font></td>
	<td><input type="password" name="passwrd1" size="20"></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[82]: </b></font></td>
	<td><input type="password" name="passwrd2" size="20"></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[68]: </b></font><BR>
	<font size=1>$txt[518]</font></td>
	<td><input type="text" name="name" size="30" value="$memsettings[realName]"></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[69]: </b></font><BR>
        <font size="1">$txt[679]</font></td>
	<td><input type="text" name="email" size="30" value="$memsettings[emailAddress]"></td>
      </tr>
    </table><BR>
    </td>
  </tr><tr>
    <td class="catbg" bgcolor="$color[catbg]" height="25"><font size=2><b>$txt[597]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <table border=0 width="100%" cellpadding="3">
      <tr>
	<td width="45%"><font size=2><b>$txt[231]: </b></font></td>
	<td>
	<select name="gender" size="1">
	 <option value=""></option>
	 <option value="Male"$GenderMale>$txt[238]</option>
	 <option value="Female"$GenderFemale>$txt[239]</option>
	</select>
	</td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[563]:</b></font></td>
	<td><font size=1>$txt[564]<input type="text" name="bday1" size="2" maxlength="2" value="$umonth">$txt[565]<input type="text" name="bday2" size="2" maxlength="2" value="$uday">$txt[566]<input type="text" name="bday3" size="4" maxlength="4" value="$uyear"></font></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[227]: </b></font></td>
	<td><font size=2><input type="text" name="location" size="50" value="$memsettings[location]"></font></td>
      </tr><tr>
	<td colspan=2>
	<BR><hr width="100%" size="1" class="windowbg3"></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[83]: </b></font><BR>
	<font size=1>$txt[598]</font></td>
	<td><font size=2><input type=text name=websitetitle size=50 value="$memsettings[websiteTitle]"></font></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[84]: </b></font><BR>
	<font size=1>$txt[599]</font></td>
	<td><font size=2><input type=text name=websiteurl size=50 value="$memsettings[websiteUrl]"></font></td>
      </tr><tr>
	<td colspan=2>
	<BR><hr width="100%" size="1" class="windowbg3"></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[513]: </b></font><BR>
	<font size=1>$txt[600]</font></td>
	<td><font size=2><input type=text name=icq size=20 value="$memsettings[ICQ]"></font></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[603]: </b></font><BR>
	<font size=1>$txt[601]</font></td>
	<td><font size=2><input type=text name=aim size=20 value="$memsettings[AIM]"></font></td>
      </tr><tr>
        <td width="45%"><font size=2><b>MSN: </b></font><BR>
        <font size=1>Your MSN messenger email address:</font></td>
         <td><font size=2><input type=text name=msn size=20 value="$memsettings[MSN]"></font></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[604]: </b></font><BR>
	<font size=1>$txt[602]</font></td>
	<td><font size=2><input type=text name=yim size=20 value="$memsettings[YIM]"></font></td>
      </tr><tr>
	<td width="45%"><font size=2><b>IRC: </b></font><BR>
	<font size=1>This is your IRC nick</font></td>
	<td><font size=2><input type=text name=irc size=20 value="$memsettings[IRC]"></font></td>
      </tr><tr>
	<td colspan=2>
	<BR><hr width="100%" size="1" class="windowbg3"></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[228]: </b></font></td>
	<td><font size=2><input type=text name=usertext size=50 maxlength=50 value="$ptext"></font></td>
      </tr>
EOT;
if($allowpics) {
	$dir = opendir("$facesdir");
	$contents = array();
	while ($contents[] = readdir($dir)){;}
	closedir($dir);
	$images = "";
	natcasesort ($contents);
	foreach ($contents as $line){
		$filename = substr($line,0,(strlen($line)-strlen(strrchr($line,'.'))));
		$extension = substr(strrchr($line,'.'), 1);
		$checked = "";
		if ($line == $memsettings[13]) { $checked = ' selected'; }
		if (stristr($memsettings[13],"http://") && $line == 'blank.gif') { $checked = ' selected'; }
		if (strcasecmp($extension,"gif")==0 || strcasecmp($extension,"jpg")==0 || strcasecmp($extension,"jpeg")==0 || strcasecmp($extension,"png")==0 ){
			if ($line == 'blank.gif') { $filename = $txt[422]; }
			$filename = str_replace("_", " ", $filename);
			$images .= "<option value=\"$line\"$checked>$filename</option>\n";
		}
	}
	if (stristr($memsettings[13],"http://")) {
		$pic = 'blank.gif';
		$checked = ' checked';
		$tmp = $memsettings[13];
	}
	else {
		$pic = $memsettings[13];
		$tmp = 'http://';
	}

	print <<<EOT
      <tr>
	<td width="45%"><font size=2><b>$txt[229]:</B></font><BR>
	<font size=1>$txt[474] $userpic_limits</font></td>
        <td>
        <script language="JavaScript1.2" type="text/javascript">
        function showimage(){
       			document.images.icons.src="$facesurl/"+document.creator.userpic.options[document.creator.userpic.selectedIndex].value;
        }
        </script>
        <select name="userpic" size=6 onChange="showimage()">$images</select>
        &nbsp;&nbsp;<img src="$facesurl/$pic" name="icons" border=0 hspace=15 alt="">
	</td>
      </tr><tr>
	<td width="45%"><font size="2"><B>$txt[475]</B></font></td>
	<td><input type="checkbox" name="userpicpersonalcheck"$checked>
	<input type="text" name="userpicpersonal" size="45" value="$tmp"></td>
      </tr>
EOT;
}
print <<<EOT
    </table><BR>
    </td>
  </tr><tr>
    <td class="catbg" bgcolor="$color[catbg]" height="25"><font size=2><b>$txt[605]</b></font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg]">
    <table border=0 cellpadding="3" cellspacing="0">
EOT;
if ($allow_hide_email) {
	$memsettings['hideEmail'] = ($memsettings['hideEmail']==1)?' checked':'';
	print <<<EOT
    <tr>
	<td width="45%"><font size=2><b>$txt[721]</b></font></td>
	<td><input type="checkbox" name="hideemail"$memsettings[hideEmail]></td>
    </tr>
EOT;
}
if ($modSettings['userLanguage'] == '1') {
print <<<EOT
      <tr>
	<td width="45%"><font size=2><b>$txt[349]:</b></font></td>
	<td width="50">
<select name="language">
EOT;
$dir = dir($boarddir);
while ($entry = $dir->read()){
	$n = ucfirst(substr($entry,0,(strlen($entry)-4)));
	$e = substr($entry,(strlen($entry)-4),4);
	if ($e == '.lng'){
		$selected = "";
		if ($entry == $memsettings[lngfile]) { $selected = " selected"; }
		print "    <option value=\"$entry\"$selected>$n</option>\n";
	}
}
print <<<EOT
      </select></td>
  </tr>
EOT;
}
print <<<EOT
<tr>
	<td width="45%"><script language="javascript" TYPE="text/javascript">
		<!--
		function reqWin(desktopURL){
        desktop =window.open(desktopURL,"name","toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,width=480,height=200,resizable=no");
        }
		// -->
	</script><font size=2><b>$txt[486]:</b></font><BR>
	<a href="javascript:reqWin('help.php?help=12')" class="help"><img src="$imagesdir/helptopics.gif" border="0" alt="$txt[119]" align="left"></a><font size=1>$txt[479]</font></td>
	<td width="50">
	<input type="text" name="usertimeformat" value="$memsettings[timeFormat]">
	</td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[371]:</b></font><BR>
	<font size=1>$txt[519]</font></td>
	<td><font size=1>
	<input name="usertimeoffset" size=5 maxlength=5 value=$memsettings[18]>
	<BR>$txt[741]: <i>$proftime</i></font></td>
      </tr><tr>
	<td colspan=2><hr width="100%" size="1" class="windowbg3"></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[85]:</b></font><BR>
	<font size=1>$txt[606]</font></td>
	<td><font size=2><textarea name="signature" rows="4" cols="50">$signature</textarea><BR>
	<FONT SIZE="1">$txt[664] <input value="$MaxSigLen" size="3" name="msgCL" disabled></FONT><BR><BR></font>
	<script language="JavaScript" TYPE="text/javascript">
	<!--
	var supportsKeys = false
	function tick() {
	calcCharLeft(document.forms[0])
	if (!supportsKeys) timerID = setTimeout("tick()",$MaxSigLen)
	}

	function calcCharLeft(sig) {
	clipped = false
	maxLength = $MaxSigLen
	if (document.creator.signature.value.length > maxLength) {
		document.creator.signature.value = document.creator.signature.value.substring(0,maxLength)
		charleft = 0
		clipped = true
	} else {
		charleft = maxLength - document.creator.signature.value.length
	}
	document.creator.msgCL.value = charleft
	return clipped
	}

	tick();
	//-->
	</script>

	</td></tr>
	<td colspan=2><a name="secret"><hr width="100%" size="1" class="windowbg3"></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[pswd1]:</b></font></td>
	<td><input name="secretQuestion" size=50 value="$memsettings[26]"></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[pswd2]:</b></font></td>
	<td><input name="secretAnswer" value="$memsettings[27]"></td>
      </tr><tr>
EOT;
	$request = mysql_query("SELECT membergroup FROM {$db_prefix}membergroups ORDER BY ID_GROUP");
	$lines = array();
	while ($row = mysql_fetch_row($request))
		$lines[] = $row[0];
	if($settings[7] == 'Administrator') {
		$position='';
		foreach ($lines as $curl) {
			if($curl != $lines[1] && $curl != $lines[2] && $curl != $lines[3] && $curl != $lines[4] && $curl != $lines[5] && $curl != $lines[6]) {
				if($curl != $lines[0] && $curl != $lines[7]) { $position= "$position<option>$curl</option>"; }
				elseif($curl == $lines[0]) { $position= "$position<option value=\"Administrator\">$curl</option>"; }
				elseif($curl == $lines[7]) { $position= "$position<option value=\"Global Moderator\">$curl</option>"; }
			}
		}
		if($memsettings[7] == 'Administrator') { $tt = $lines[0]; }
		elseif($memsettings[7] == 'Global Moderator') { $tt = $lines[7]; }
		else { $tt = $memsettings[7]; }
		print <<<EOT
      <tr>
	<td colspan=2><hr width="100%" size="1" class="windowbg3"></td>
      </tr><tr>
	<td width="45%"><font size=2><b>$txt[86]: </b></font></td>
	<td><font size=2><input type=text name=settings6 size=4 value="$memsettings[6]"></font></td>
      </tr><tr>
EOT;
	if ($modSettings['karmaMode']!=0)
	{
		$totalKarma = $memsettings[23]-$memsettings[22];
		print <<<EOT
	<td><font size=2><b>$modSettings[karmaLabel]</b></font></td>
	<td><font size=2>$modSettings[karmaSmiteLabel] <input type=text name="karmaBad" size=4 value="$memsettings[22]">&nbsp;&nbsp;&nbsp;&nbsp;$modSettings[karmaApplaudLabel] <input type=text name="karmaGood" size=4 value="$memsettings[23]">&nbsp;&nbsp;&nbsp;&nbsp;($txt[94]: $totalKarma)</td>
	  </tr><tr>
EOT;
	}
	print <<<EOT
	<td><font size=2><b>$txt[87]: </b></font></td>
	<td><font size=2><select name="settings7">
	 <option value="$memsettings[7]">$tt
	 <option value="$memsettings[7]">---------------
	 <option value="">
	 $position
	</select></font></td>
      </tr><tr>
        <td width="45%"><font size=2><b>$txt[233]:</b></font></td>
        <td><input type="text" name="dr" size="35" value="$dr"></td></tr>
EOT;
	}
	print <<<EOT
	<tr><td align="center" colspan="2"><BR><input type="hidden" name="moda" value="1">
	<input type=button value="$txt[88]" onclick="creator.moda.value='1'; submit();">
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

function ModifyProfile2 (){
	global $HTTP_POST_VARS,$username,$settings,$txt,$allowpics,$emailnewpass,$MaxSigLen,$Cookie_Length;
	global $newpassemail,$yySetCookies,$password,$realname,$realemail,$pwseed,$cookieusername,$cookiepassword;
	global $usertimeoffset,$userID,$sourcedir,$scripturl,$cgi,$boardurl,$db_prefix,$yySetLocation,$modSettings,$timeoffset;
	global $usertitle,$REMOTE_ADDR, $userpic_width, $userpic_height;
	foreach ($HTTP_POST_VARS as $key=>$value)
		$member[$key]=str_replace(array("&","\"","<",">"),array('&amp','&quot','&lt','&gt'),trim($value));

	# make sure this person has access to this profile
	if($username != $member['user'] && $settings[7] != 'Administrator')
		fatal_error($txt[80]);

	$karmaStr = '';
	if( $settings[7] != 'Administrator' )
	{
		$member['user'] = $username;
		$member['settings6'] = $settings[6];
		$member['settings7'] = $settings[7];
	}
	elseif ($settings[7] == 'Administrator' && $modSettings['karmaMode'] != 0)
	{
		$karmaStr = ",karmaGood='$member[karmaGood]',karmaBad='$member[karmaBad]'";
	}
	if(!is_numeric($member['settings6'])) { fatal_error("$txt[749]"); }
	$user = $member['user'];
	if( isset($member['userpicpersonalcheck']) )
	{
		$member['userpic'] = $member['userpicpersonal'];
		// now let's validate the avatar

		$sizes = @getimagesize($member['userpic']);
		if ($sizes)
		{
/*
			if (($sizes[0] > $userpic_width && $userpic_width != 0) ||
				($sizes[1] > $userpic_height && $userpic_height != 0))
			
			fatal_error("$txt[yse227]  $userpic_width x $userpic_height)");
*/
	    
		}
	}

	if($member['userpic'] == "") { $member['userpic'] = "blank.gif"; }
	if(!$allowpics) { $member['userpic'] = "blank.gif"; }
	$queryPasswdPart = '';
	mt_srand(34028934023); //pseudo-random seed
	if( $member['passwrd1'] == '' && $emailnewpass && strtolower($member['email']) != strtolower($settings[2]) && $settings[7] != 'Administrator') {
		$member['passwrd1'] = crypt(mt_rand(-100000,100000));
		$newpassemail = 1;
	} else {
		if($member['passwrd1'] != $member['passwrd2']){ fatal_error("($member[username]) $txt[213]"); }
		if($member['passwrd1'] != ''){ $queryPasswdPart="passwd='".crypt($member['passwrd1'], substr($member['passwrd1'],0,2))."',"; }
	}
	if($member['name'] == ''){ fatal_error("$txt[75]");}
	if($member['email'] == '') { fatal_error("$txt[76]"); }
	if(!preg_match("/^[0-9A-Za-z@\._\-]+$/",$member['email'])){ fatal_error("$txt[243]"); }
	if(preg_match("/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)|(\.$)/",$member['email'])){ fatal_error("$txt[500]"); }


if($member['moda'] != '-1')	// if we aren't saying "Delete user";
{
		if (strlen($member['signature']) > $MaxSigLen)
		{
			$member['signature'] = substr($member['signature'],0,$MaxSigLen);
		}
	    $member['icq'] = preg_replace("/[^0-9]/","",$member['icq']);
	    $member['bday1'] = preg_replace("/[^0-9]/","",$member['bday1']);
	    $member['bday2'] = preg_replace("/[^0-9]/","",$member['bday2']);
	    $member['bday3'] = preg_replace("/[^0-9]/","",$member['bday3']);
		if($member['bday1'])
		{
			$member['bday'] = "$member[bday3]-$member[bday1]-$member[bday2]";
		}
		else
		{
			$member['bday'] = '';
		}
		$member['signature'] = str_replace("<","&lt;",$member['signature']);
		$member['signature'] = str_replace(">","&gt;",$member['signature']);
		$member['aim'] = str_replace(" ","+",$member['aim']);
        $member['msn'] = str_replace(" ","+",$member['msn']);
		$member['yim'] = str_replace(" ","+",$member['yim']);
		if($settings[7] != "Administrator" || $member['dr']==$txt[470])
			$member['dr'] = "";
		else {
			if (($member['dr'] = strtotime($member['dr'])) === -1) {
				$fmt = "%d %b %Y " . (substr_count($settings[17], "%H") == 0?"%I:%M:%S %p":"%T");
				$dr = strftime($fmt, time()+(($timeoffset+$time)*3600));
				fatal_error("$txt[yse233] $dr");
			}
			else {
				$timeadjust = ((isset($settings[18])?$settings[18]:0) + $timeoffset) * 3600;
				$member['dr'] = $member['dr'] - $timeadjust;
				$member['dr'] = "dateRegistered='$member[dr]',";
			}
		}

		# store the name temorarily so we can restore any _'s later
		$tempname = $member['name'];
		$member['name'] = str_replace("_"," ",$member['name']);

		if ( strlen($member['signature']) > 1000 ) { $member['signature'] = substr( $member['signature'], 0, 1000 ); }
		$member['usertimeoffset'] = str_replace(",",".",$member['usertimeoffset']);
		$member['usertimeoffset'] = preg_replace("/[^\d*|\.|\-|w*]/","",$member['usertimeoffset']);
		if (( $member['usertimeoffset'] < -23.5) || ( $member['usertimeoffset'] > 23.5)) { fatal_error($txt[487]); }

		$request = mysql_query("SELECT ID_MEMBER FROM {$db_prefix}members WHERE ('$member[name]'!='' && memberName!='$member[user]' && realName='$member[name]')");
		if (mysql_num_rows($request) > 0)
			fatal_error("($member[name]) $txt[473]");
		$request = mysql_query("SELECT ID_MEMBER FROM {$db_prefix}members WHERE (memberName!='$member[user]' &&  emailAddress='$member[email]')");
		if (mysql_num_rows($request) > 0)
			fatal_error("$txt[730] ($member[email]) $txt[731]");

		$request = mysql_query("SELECT setting,value FROM {$db_prefix}reserved_names WHERE 1");
		$reserve = array();
		$matchcase = $matchname = $matchuser = $matchword = 0;
		while ($row = mysql_fetch_row($request))
		{
			if ($row[0] == "word")
				$reserve[] = trim($row[1]);
			else
				${$row[0]} = trim($row[1]);
		}
		$namecheck = $matchcase ? $member['name'] : strtolower ($member['name']);

		foreach ($reserve as $reserved) {
			$reservecheck = $matchcase ? $reserved : strtolower ($reserved);
			if ($matchname) {
				if ($matchword) {
					if ($namecheck == $reservecheck) { fatal_error("$txt[244] $reserved"); }
				}
				else {
					if (strstr($namecheck,$reservecheck)) { fatal_error("$txt[244] $reserved"); }
				}
			}
		}

		# let's restore the name now
		//ToHTML($tempname);
		$member['name'] = $tempname;

		$hideEmail = 0;
		if (isset($member['hideemail']))
			$hideEmail = 1;
		$timeOffest = 0;
		if ($usertimeoffset != '')
			$timeOffest = $usertimeoffset;

        if (strlen($member[websiteurl])>=1){
            //echo "String: ".strlen($member[websiteurl]);
        if (!stristr($member[websiteurl],"://")) {
           $member[websiteurl]="http://".$member[websiteurl];
        }
     }
     else
      $member[websiteurl]="";

        if ($member['name']==" "){
         $member['name']=$user;}

		$member['usertitle'] = isset($member['usertitle'])?$member['usertitle']:'';
		if (get_magic_quotes_gpc()==0) {
			$member['name'] = mysql_escape_string($member['name']);
	        $member['websitetitle'] = mysql_escape_string($member['websitetitle']);
		    $member['signature'] = mysql_escape_string($member['signature']);
	        $member['usertext'] = mysql_escape_string($member['usertext']);
			$member['usertitle'] = mysql_escape_string($member['usertitle']);
		    $member['location'] = mysql_escape_string($member['location']);
        }

		$customTitlePart = '';
		if ($modSettings['titlesEnable'] == 1 && ($settings[7]=='Administrator' || $settings[7]=='Global Moderator'))
			$customTitlePart = "usertitle='$member[usertitle]',";
   $memIP=$REMOTE_ADDR;
   $member['secretQuestion'] = mysql_escape_string($member['secretQuestion']);
   $member['secretAnswer'] = mysql_escape_string($member['secretAnswer']);
   $foo =  "UPDATE {$db_prefix}members SET $queryPasswdPart $customTitlePart realName='$member[name]',emailAddress='$member[email]',websiteTitle='$member[websitetitle]',websiteUrl='$member[websiteurl]',signature='$member[signature]',posts=$member[settings6],memberGroup='$member[settings7]',ICQ='$member[icq]',MSN='$member[msn]',AIM='$member[aim]',YIM='$member[yim]',gender='$member[gender]',personalText='$member[usertext]',avatar='$member[userpic]',$member[dr]location='$member[location]',birthdate='$member[bday]',lngfile='$member[language]',memberIP='$memIP',timeFormat='$member[usertimeformat]',timeOffset=$timeOffest,secretQuestion='$member[secretQuestion]',secretAnswer='$member[secretAnswer]',hideEmail=$hideEmail$karmaStr WHERE memberName='$user'";

   $request = mysql_query("UPDATE {$db_prefix}members SET $queryPasswdPart $customTitlePart realName='$member[name]',emailAddress='$member[email]',websiteTitle='$member[websitetitle]',websiteUrl='$member[websiteurl]',signature='$member[signature]',posts=$member[settings6],memberGroup='$member[settings7]',ICQ='$member[icq]',MSN='$member[msn]',AIM='$member[aim]',YIM='$member[yim]',gender='$member[gender]',personalText='$member[usertext]',avatar='$member[userpic]',$member[dr]location='$member[location]',birthdate='$member[bday]',lngfile='$member[language]',memberIP='$memIP',timeFormat='$member[usertimeformat]',timeOffset=$timeOffest,secretQuestion='$member[secretQuestion]',secretAnswer='$member[secretAnswer]',hideEmail=$hideEmail$karmaStr WHERE memberName='$user'");

  if(!$request) {
	 fatal_error("db write failed: $foo");
  }

    if($newpassemail) {

	$request = mysql_query("DELETE FROM {$db_prefix}log_online WHERE identity='$username'");

	$yySetCookies = "Set-Cookie: $cookieusername=; expires=Thu, 01-Jan-1970 00:00:00 GMT;\n";
	$yySetCookies .= "Set-Cookie: $cookiepassword=; expires=Thu, 01-Jan-1970 00:00:00 GMT;\n";
	$yySetCookies .= "Set-Cookie: $cookieusername=; path=/; expires=Thu, 01-Jan-1970 00:00:00 GMT;\n";
	$yySetCookies .= "Set-Cookie: $cookiepassword=; path=/; expires=Thu, 01-Jan-1970 00:00:00 GMT;\n";
	$username = 'Guest';
	$password = '';
	$settings = array(7=>'');
	$realname = '';
	$realemail = '';
	$HTTP_COOKIE_VARS = array();

	$yytitle="$txt[245]";
	template_header();
		$euser=urlencode($member['user']);
		sendmail($member['email'],"$txt[700] $mbname", "$txt[733] $member[passwrd1] $txt[734] $member[user].\n\n$txt[701] $scripturl?action=profile;user=$euser\n\n$txt[130]");
		print <<<EOT
<BR>
<table border=0 width=100% cellspacing=1 bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]"><b>$txt[97]</b></font></td>
  </tr><tr>
   <td class="windowbg" bgcolor="$color[windowbg]" align="left" cellpadding=3><font size=2>$txt[638]</font></td>
  </tr><tr>
    <td class="windowbg" bgcolor="$color[windowbg2]" valign=top><form action="$cgi;action=login2" method="POST">
    <table border=0>
      <tr>
        <td><font size=2><b>$txt[35]:</b></font></td>
        <td><font size=2><input type=text name="user" size=15 value="$member[user]"></font></td>
        <td><font size=2><b>$txt[36]:</b></font></td>
        <td><font size=2><input type=password name="passwrd" size=15></font> &nbsp;</td>
        <td><font size=2><b>$txt[497]:</b></font></td>
        <td><font size=2><input type=text name="cookielength" size=4 value="$Cookie_Length">
        <td><font size=2><b>$txt[508]:</B></font></td>
        <td><font size=2><input type=checkbox name="cookieneverexp"></font></td> &nbsp;</font></td>
        <td align=center colspan=2><input type=submit value="$txt[34]"></td>
      </tr>
    </table>
    </td>
  </tr>
</table>
EOT;
	footer();
	}
	else {
		if ( $member['user'] == $username ) {
			$Cookie_Exp_Date = 'Sun, 17-Jan-2038 00:00:00 GMT';
			$password = ($member['passwrd1']!= '')?crypt("$member[passwrd1]",$pwseed):$password;

			setCookie($cookieusername,$username, time()+30240000);
			setCookie($cookiepassword,$password, time()+30240000);

			LoadUserSettings();
			WriteLog();
		}
		ViewProfile();
	}
}
else	// if we did say "delete user"
{

		if(($settings[7] == 'Administrator' || $member['user'] == $username)) {
				$request = mysql_query("UPDATE {$db_prefix}messages SET ID_MEMBER='-1' WHERE ID_MEMBER='$userID'");
				$request = mysql_query("DELETE FROM {$db_prefix}members WHERE memberName='$member[user]'");
				$request = mysql_query("DELETE FROM {$db_prefix}log_topics WHERE memberName='$member[user]'");
				$request = mysql_query("DELETE FROM {$db_prefix}log_boards WHERE memberName='$member[user]'");
				$request = mysql_query("DELETE FROM {$db_prefix}log_mark_read WHERE memberName='$member[user]'");
				$request = mysql_query("DELETE FROM {$db_prefix}instant_messages WHERE (toName='$member[user]' AND deletedBy=0)");
				$request = mysql_query("DELETE FROM {$db_prefix}instant_messages WHERE (fromName='$member[user]' AND deletedBy=1)");
				$request = mysql_query("UPDATE {$db_prefix}instant_messages SET deletedBy=1 WHERE toName='$member[user]'");
				$request = mysql_query("UPDATE {$db_prefix}instant_messages SET deletedBy=0 WHERE fromName='$member[user]'");

		}

		$request = mysql_query("SELECT ID_TOPIC,notifies FROM {$db_prefix}topics WHERE notifies!=''");
		while ($row = mysql_fetch_row($request))
		{
			$entries = explode(",",$row[1]);
			$entries2 = array();
			foreach($entries as $entry)
				if (strcasecmp($entry,$userID)!=0)
					$entries2[] = $entry;
			$request2 = mysql_query("UPDATE {$db_prefix}topics SET notifies='".implode(",",$entries2)."' WHERE ID_TOPIC=$row[0]");
		}

		if($settings[7] != 'Administrator') {
			include_once ("$sourcedir/LogInOut.php");
			Logout();
		}
		$yySetLocation = "$scripturl";
		redirectexit();
	}
}

function ViewProfile (){
    global $user, $modify, $userpic_width, $facesurl, $userpic_height, $allowpics, $txt, $color, $cgi, $GodPostNum;
	global $SrPostNum, $FullPostNum, $JrPostNum, $settings, $allow_hide_email, $imagesdir,$yytitle;
	global $username,$password,$pwseed,$action,$cookiepassword,$cookieusername,$db_prefix,$modSettings;

	$user=urldecode($user);
	$request = mysql_query("SELECT ID_MEMBER FROM {$db_prefix}members WHERE memberName='$user'");

	if(!$request || mysql_num_rows($request) == 0){ fatal_error("$txt[453] -- ".htmlentities($user)); }

	$yytitle = "$txt[92] $user";
    template_header();

	# get the member's info
	$request = mysql_query("SELECT passwd,realName,emailAddress,websiteTitle,websiteUrl,signature,posts,memberGroup,ICQ,AIM,YIM,gender,personalText,avatar,dateRegistered,location,birthdate,timeFormat,timeOffset,hideEmail,ID_MEMBER,usertitle,karmaBad,karmaGood,lngfile,MSN FROM {$db_prefix}members WHERE memberName='$user'");
	$memsettings = mysql_fetch_row($request);

    if (strlen($memsettings[25])>1)
    $msn = "<a href=http://members.msn.com/$memsettings[25]>$memsettings[25]</a>&nbsp; <a href=javascript:MsgrApp.LaunchAddContactUI(\"".$memsettings[25]."\")><img src=\"$imagesdir/msnadd.gif\" border=\"0\"></a>&nbsp;&nbsp;<a id=\"lll\" href=javascript:MsgrApp.LaunchIMUI(\"".$memsettings[25]."\")><img src=\"$imagesdir/msntalk.gif\" border=\"0\"></a>";
    else
    $msn="";

	$icq = $memsettings[8];
	$memsettings[9] = str_replace("+"," ",$memsettings[9]);;
	$dr = "";
	if (!isset($memsettings[14]) || $memsettings[14] == "") { $dr = "$txt[470]"; }
	else { $dr = "$memsettings[14]"; $dr = timeformat($dr); }
	$datearray = getdate(time());
	$age = '';
	$isbday = false;
	if (!isset($memsettings[16]) || $memsettings[16] =='0000-00-00' || $memsettings[16]=='')
		$age = "N/A";
	else
	{
		$age = $datearray['year']-substr($memsettings[16],0,4)-(($datearray['mon']>substr($memsettings[16],5,2)||$datearray['mon']==substr($memsettings[16],5,2) && $datearray['mday']>=substr($memsettings[16],8,2))?0:1);
		$isbday = ($datearray['mon']==substr($memsettings[16],5,2) && $datearray['mday']==substr($memsettings[16],8,2));
	}

	if($isbday)
		$isbday = "<img src=\"$imagesdir/bdaycake.gif\" width=\"40\" alt=\"\">";
	else
		$isbday = '';

	if ($modSettings['userLanguage'] == '1') {
$usrlngfile = ucfirst(substr($memsettings[24],0,(strlen($memsettings[24])-4)));
$usrlng = "<tr><td><font size=2><b>$txt[yse225]:</b></font></td><td><font size=2>$usrlngfile</font></td></tr>";
}
else { $usrlng = ""; }

	$request = mysql_query("SELECT membergroup FROM {$db_prefix}membergroups WHERE 1 ORDER BY ID_GROUP");

	$membergroups = array();

	while ($row = mysql_fetch_row($request))
		$membergroups[] = $row[0];

	if($memsettings[6] > $GodPostNum) { $memberinfo = "$membergroups[6]"; }
 	elseif($memsettings[6] > $SrPostNum) { $memberinfo = "$membergroups[5]"; }
	elseif($memsettings[6] > $FullPostNum) { $memberinfo = "$membergroups[4]"; }
	elseif($memsettings[6] > $JrPostNum) { $memberinfo = "$membergroups[3]"; }
	else { $memberinfo = "$membergroups[2]"; }

	if($memsettings[7] != "") { $memberinfo = "$memsettings[7]"; }
	if($memsettings[7] == "Administrator") { $memberinfo = "$membergroups[0]"; }

	if ($username == $user || $settings[7] == "Administrator") {
		$euser=urlencode($user);
		$modify = "&quot; <a href=\"$cgi;action=profile;user=$euser\"><font size=2 class=\"text1\" color=\"$color[titletext]\">$txt[17]</font></a> &quot;";
	}

  	if ($memsettings[19] != "1" || $settings[7] == "Administrator" || $allow_hide_email != '1') {
		$email = "<a href=\"mailto:$memsettings[2]\">$memsettings[2]</a>";
	}
	else { $email = "<i>$txt[722]</i>"; }
	$gender = "";

	if ($memsettings[11] == "Male") { $gender = "$txt[238]"; }
	if ($memsettings[11] == "Female") { $gender = "$txt[239]"; }
	if($allowpics) {
	if (stristr($memsettings[13],"http://") ) {
		if ($userpic_width != 0) { $tmp_width = "width=$userpic_width"; } else { $tmp_width=""; }
		if ($userpic_height != 0) { $tmp_height = "height=$userpic_height"; } else { $tmp_height=""; }

		$pic = "<a href=\"$memsettings[13]\" target=\"_blank\" onClick=\"window.fopen('$memsettings[13]', 'ppic$user', 'resizable,width=200,height=200'); return false;\">";
		$pic = "<img src=\"$memsettings[13]\" $tmp_width $tmp_height border=\"0\" alt=\"\">";
	}
	else {
		$pic = "<a href=\"$facesurl/$memsettings[13]\" target=\"_blank\" onClick=\"window.fopen('$facesurl/$memsettings[13]', 'ppic$user', 'resizable,width=200,height=200'); return false;\">";
		$pic = "<img src=\"$facesurl/$memsettings[13]\" border=\"0\" alt=\"\">";
	}
	}

	$online = "$txt[113] ?";
	if (OnlineStatus($user) > 0) { $online = str_replace ("?","<i>$txt[online2]</i>",$online); }
	else $online = str_replace("?","<i>$txt[online3]</i>",$online);
	if($memsettings[6] > 100000) { $memsettings[6] = "$txt[683]"; }

	$userTitle = '';
	if ($modSettings['titlesEnable']=='1' && $memsettings[21]!='')
		$userTitle = "<td><font size=2><b>$txt[title1]: </b></font></td>\n        <td><font size=2>$memsettings[21]</font></td>\n      </tr><tr>";

	$karmastr = '';
	if ($modSettings['karmaMode'] == '1')
		$karmastr = "<td><font size=2><b>$modSettings[karmaLabel] </b></font></td>\n       <td><font size=2>".($memsettings[23]-$memsettings[22])."</font></td>\n</tr><tr>";
	else if ($modSettings['karmaMode'] == '2')
		$karmastr = "<td><font size=2><b>$modSettings[karmaLabel] </b></font></td>\n       <td><font size=2>+$memsettings[23]/-$memsettings[22]</font></td>\n</tr><tr>";

print <<<EOT
 <table border="0" cellpadding="4" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" colspan="2" bgcolor="$color[titlebg]">
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
      <tr>
	<td width="220" height="30">
        <img src="$imagesdir/profile_sm.gif" alt="" border="0">&nbsp;
        <font size=2 class="text1" color="$color[titletext]"><B>$txt[35]: $user</b></font></td>
	<td align="center" width="200">
        <font size=2 class="text1" color="$color[titletext]">$modify</font></td>
	<td align="center" width="150">
        <font size=2 class="text1" color="$color[titletext]">$txt[232]</font></td>
      </tr>
    </table>
    </td>
  </tr><tr>
    <td bgcolor="$color[windowbg]" class="windowbg" width="420">
    <table border=0 cellspacing="0" cellpadding="2" width="100%">
      <tr>
	<td><font size=2><b>$txt[68]: </b></font></td>
	<td><font size=2>$memsettings[1]</font></td>
	</tr><tr>
	$userTitle
        <td><font size=2><b>$txt[86]: </b></font></td>
        <td><font size=2>$memsettings[6]</font></td>
      </tr><tr>
        <td><font size=2><b>$txt[87]: </b></font></td>
        <td><font size=2>$memberinfo</font></td>
      </tr><tr>
	$karmastr
        <td><font size=2><b>$txt[233]: </b></font></td>
        <td><font size=2>$dr</font></td>
      </tr><tr>
	<td colspan="2"><hr size="1" width="100%" class="windowbg3"></td>
      </tr><tr>
        <td><font size=2><b>$txt[513]:</b></font></td>
        <td><font size=2><a href="$cgi;action=icqpager;UIN=$icq" target=_blank>$memsettings[8]</a></font></td>
      </tr><tr>
        <td><font size=2><b>$txt[603]: </b></font></td>
        <td><font size=2><a href="aim:goim?screenname=$memsettings[9]&amp;message=Hi.+Are+you+there?">$memsettings[9]</a></font></td>
      </tr><tr>
      <td><font size=2><b>MSN: </b></font></td>
        <td><font size=2>$msn</font></td>
      </tr><tr>
        <td><font size=2><b>$txt[604]: </b></font></td>
        <td><font size=2><a href="http://edit.yahoo.com/config/send_webmesg?.target=$memsettings[10]">$memsettings[10]</a></font></td>
      </tr><tr>
	<td><font size=2><b>$txt[69]: </b></font></td>
	<td><font size=2>$email</font></td>
      </tr><tr>
        <td><font size=2><b>$txt[96]: </b></font></td>
        <td><font size=2><a href="$memsettings[4]" target=_blank>$memsettings[3]</a></font></td>
      </tr><tr>
	<td colspan="2"><hr size="1" width="100%" class="windowbg3"></td>
      </tr><tr>
	<td><font size=2><b>$txt[231]: </b></font></td>
	<td><font size=2>$gender</font></td>
      </tr><tr>
        <td><font size=2><b>$txt[420]:</b></font></td>
        <td><font size=2>$age</font> &nbsp; $isbday</td>
      </tr><tr>
        <td><font size=2><b>$txt[227]: </b></font></td>
        <td><font size=2>$memsettings[15]</font></td>
      </tr>$usrlng
    </table>
    </td>
    <td bgcolor="$color[windowbg]" class="windowbg" valign="middle" align="center" width="150">
    $pic<BR><BR>
    <font size=2>$memsettings[12]</font></td>
  </tr><tr>
    <td class="titlebg" bgcolor="$color[titlebg]" colspan="2" height="25">
    &nbsp;<font size=2 class="text1" color="$color[titletext]"><b>$txt[459]:</b></font></td>
  </tr><tr>
    <td colspan="2" bgcolor="$color[windowbg2]" class="windowbg2" valign="middle">
    <form action="$cgi;action=usersrecentposts;userid=$memsettings[20];user=$user" method="POST">
    <font size=2>
    $online<BR><BR>
    <a href="$cgi;action=imsend;to=$user">$txt[688]</a>.<BR><BR>
    $txt[460] <select name="viewscount" size="1">
     <option value="5">5</option>
     <option value="10" selected>10</option>
     <option value="50">50</option>
     <option value="0">$txt[190]</option>
    </select> $txt[461]. <input type=submit value="$txt[462]">
	 </font></form></td>
  </tr>
</table>
EOT;

footer();
	obExit();
}


function usersrecentposts (){
	global $userid,$viewscount,$censored,$user,$membergroups,$settings,$username,$yyitle,$txt,$enable_ubbc;
	global $cgi,$img,$imagesdir,$enable_notification,$maxmessagedisplay,$color,$scripturl,$menusep,$db_prefix;
	$display = $viewscount;
	if (!is_numeric($display)){fatal_error($txt[337]);}

	$request = mysql_query("SELECT realName FROM {$db_prefix}members WHERE ID_MEMBER=$userid LIMIT 1");
	list($realName) = mysql_fetch_row($request);

	$permit = 0;
	if ($settings[7]=='Administrator' || $settings[7]=='Global Moderator')
		$permit = 1;

	$limitString = ($display != '0') ? " LIMIT $display" : '';

	$request = mysql_query("SELECT m.*,t.numReplies,c.memberGroups,c.name as cname,b.name as bname,b.ID_BOARD FROM {$db_prefix}messages as m, {$db_prefix}topics as t, {$db_prefix}boards as b, {$db_prefix}categories as c, {$db_prefix}members as mem WHERE (m.ID_MEMBER=$userid && m.ID_TOPIC=t.ID_TOPIC && t.ID_BOARD=b.ID_BOARD && b.ID_CAT=c.ID_CAT && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || $permit || c.memberGroups='') && mem.ID_MEMBER=m.ID_MEMBER) ORDER BY m.posterTime DESC$limitString");

	$yytitle = "$txt[458] $user";
	template_header();
	$euser=urlencode($user);
	print<<<EOT
<p align=left><a href="$cgi&action=viewprofile&user=$euser"><font size=2><b>$txt[92] $realName</b></font></a></p>
EOT;
	$counter = 1;
	while ($row = mysql_fetch_assoc($request))
	{
		CensorTxt($row['body']);
		CensorTxt($row['subject']);

		$start = (floor($row['numReplies']/$maxmessagedisplay))*$maxmessagedisplay;
		if($enable_ubbc) { $row['body'] = DoUBBC($row['body'], $row['smiliesEnabled']); }
		$notify = $enable_notification ? "$menusep<a href=\"$scripturl?board=$row[ID_BOARD];action=notify;threadid=$row[ID_TOPIC];start=$start\">$img[notify_sm]</a>" :'';
		$row['posterTime']=timeformat($row['posterTime']);
		print <<<EOT
<table border=0 width=100% cellspacing=1 bgcolor="$color[bordercolor]">
<tr>
	<td align=left bgcolor="$color[titlebg]"><font class="text1" color="$color[titletext]" size=2>&nbsp;$counter&nbsp;</font></td>
	<td width=75% bgcolor="$color[titlebg]"><font class="text1" color="$color[titletext]" size=2><b>&nbsp;$row[cname] / $row[bname] / <a href="$scripturl?board=$row[ID_BOARD];action=display;threadid=$row[ID_TOPIC];start=$start"><font class="text1" color="$color[titletext]" size=2>$row[subject]</font></a></b></font></td>
	<td align=right bgcolor="$color[titlebg]"><nobr>&nbsp;<font class="text1" color="$color[titletext]" size=2>$txt[30]: $row[posterTime]&nbsp;</font></nobr></td>
</tr>
<tr height=50>
	<td colspan=3 bgcolor="$color[windowbg2]" valign=top><font size=2>$row[body]</font></td>
</tr>
<tr>
	<td colspan=3 bgcolor="$color[catbg]"><font size=2>
		&nbsp;<a href="$scripturl?board=$row[ID_BOARD];action=post;threadid=$row[ID_TOPIC];start=$start;title=Post+reply">$img[reply_sm]</a>$menusep<a href="$scripturl?board=$row[ID_BOARD];action=post;threadid=$row[ID_TOPIC];quote=$row[ID_MSG];title=Post+reply">$img[replyquote]</a>$notify
	</font></td>
</tr>
</table><br>
EOT;
		++$counter;
	}
if ($counter == 1) // there were no posts
	print "$txt[170]<br>";
print <<<EOT
<p align=left><a href="$cgi&action=viewprofile;user=$euser"><font size=2><b>$txt[92] $realName</b></font></a></p></font>
EOT;
	footer();
	obExit();
}
?>
