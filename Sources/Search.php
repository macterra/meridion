<?php
/*****************************************************************************/
/* Search.php                                                                */
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

$searchplver="YaBB SE 1.3.0";

function plushSearch1 (){
	global $yytitle,$txt,$censored,$curposlinks,$mbname,$imagesdir,$settings,$scripturl,$color,$db_prefix,$modSettings;
	$yytitle = $txt[183];
    template_header();
	//LoadCensorList();
	$searchpageurl = $curposlinks ? "<a href=\"$scripturl?action=search\" class=\"nav\">$txt[182]</a>" : $txt[182];

# Build the link tree
	$displayLinkTree = $modSettings['enableInlineLinks'] ? "<font size=1 class=\"nav\"><b><a href=\"$scripturl\" class=\"nav\">$mbname</a> </b>&nbsp;|&nbsp;<b> " : "<font size=2 class=\"nav\"><img src=\"$imagesdir/open.gif\" BORDER=\"0\" alt=\"\">&nbsp;&nbsp;<b><a href=\"$scripturl\" class=\"nav\">$mbname</a></b><br>" ;
	$displayLinkTree .= $modSettings['enableInlineLinks'] ? "<b>$searchpageurl</b></font>" : "<img src=\"$imagesdir/tline.gif\" BORDER=\"0\" alt=\"\"><img src=\"$imagesdir/open.gif\" border=\"0\" alt=\"\">&nbsp;&nbsp;<b>$searchpageurl</b></font>" ;

	print <<<EOT
<script language="JavaScript1.2" type="text/javascript">
<!-- Begin
function changeBox(cbox) {
box = eval(cbox);
box.checked = !box.checked;
}
//  End -->
</script>
<script language="JavaScript" TYPE="text/javascript">
<!--
function checkAll() {
  for (var i = 0; i < document.searchform.elements.length; i++) {
  	if(document.searchform.elements[i].name != "subfield" && document.searchform.elements[i].name != "msgfield") {
    		document.searchform.elements[i].checked = true;
    	}
  }
}
function uncheckAll() {
  for (var i = 0; i < document.searchform.elements.length; i++) {
  	if(document.searchform.elements[i].name != "subfield" && document.searchform.elements[i].name != "msgfield") {
    		document.searchform.elements[i].checked = false;
    	}
  }
}
//-->
</script>
<form action="$scripturl?action=search2" method="post" name="searchform">
<table width="80%" align="center" border="0" cellpadding="3" cellspacing="0">
  <tr>
    <td>$displayLinkTree</td>
  </tr>
</table>

<table border=0 width="80%" cellspacing=1 cellpadding=4 bgcolor="$color[bordercolor]" class="bordercolor" align="center">
  <tr>
    <td class="titlebg" bgcolor="$color[titlebg]"><font size=2 class="text1" color="$color[titletext]">$txt[183]</font></td>
  </tr><tr>
    <td bgcolor="$color[windowbg]">
    <font size="2"><B>$txt[582]:</B><br>
    <input type=text size=40 name=search>&nbsp;
    <select name="searchtype">
     <option value="allwords" selected>$txt[343]</option>
     <option value="anywords">$txt[344]</option>
     <option value="asphrase">$txt[345]</option>
    </select><BR>
    <B>$txt[583]:</B><br>
    <input type=text size=40 name=userspec>&nbsp;
    <select name="userkind">
     <option value="any">$txt[577]</option>
     <option value="starter">$txt[186]</option>
     <option value="poster">$txt[187]</option>
     <option value="noguests" selected>$txt[346]</option>
     <option value="onlyguests">$txt[572]</option>
    </select></font><br>
    <font size="2"><B>$txt[189]:</B><br></font><br>
    <table width="80%" border="0" cellpadding="1" cellspacing="0">
      <tr>
EOT;
	$request = mysql_query("SELECT b.ID_BOARD,b.name FROM {$db_prefix}boards as b, {$db_prefix}categories as c WHERE (b.ID_CAT=c.ID_CAT && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || '$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || c.memberGroups=''))");
	$counter = 1;
	while ($row = mysql_fetch_row($request))
	{
		print "<td width=\"50%\"><font size=\"2\"><input type=checkbox name=\"brd$row[0]\" value=\"1\" checked><span id=\"spanbrd$row[0]\" style=\"cursor:hand;\" onClick=\"changeBox('document.searchform.brd$row[0]')\">$row[1]</span></font></td>";
		if( !($counter % 2) )
			print "</tr><tr>";
		$counter++;
	}
	print <<<EOT
	  <td></td>
      </tr>
    </table><BR>
    <INPUT TYPE="checkbox" ONCLICK="if (this.checked) checkAll(); else uncheckAll();" checked><font size="2"><i>$txt[737]</i></font>
    <BR><br><font size="2">
    <B>$txt[573]:</B></font><br>
    <table border="0" cellpadding="2" cellspacing="0">
      <tr>
        <td><font size="2">
        <input type=checkbox name=subfield value=on checked><span id="spansubfield" style="cursor:hand;" onClick="changeBox('document.searchform.subfield')">$txt[70]</span></font></td>
        <td><font size="2">
        <input type=checkbox name=msgfield value=on checked><span id="spanmsgfield" style="cursor:hand;" onClick="changeBox('document.searchform.msgfield')">$txt[72]</span></font></td>
      </tr>
    </table>
    <br>
    <table border="0" cellpadding="2" cellspacing="0">
      <tr>
        <td><font size="2">
        <B>$txt[575] $txt[574]:</B><br>
        <input type=text name=minripe value=0 size=3 maxlength=5> $txt[578] +
        <input type=text name=minage value=0 size=5 maxlength=5> $txt[579].
        </font></td>
        <td><font size="2">
        <B>$txt[576] $txt[574]:</B><br>
        <input type=text name=maxripe value=0 size=3 maxlength=5> $txt[578] +
        <input type=text name=maxage value=7 size=5 maxlength=5> $txt[579].</font></td>
      </tr>
    </table>
    <br>
    <table border="0" cellpadding="2" cellspacing="0" align="left">
      <tr>
        <td valign="bottom"><font size="2">
        <B>$txt[191]</B><br></font>
        <input type="text" size="5" name="numberreturned" maxlength="5" value="25"></td></tr><tr>
        <td valign="bottom">
        <input type="hidden" name="action" value="dosearch">
        <input type="submit" name="submit" value="$txt[182]"></td>
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

function plushSearch2 (){
	global $minripe, $minage, $maxripe, $maxage, $numberreturned,$userkind,$userspec,$searchtype,$search;
	global $subfield,$msgfield,$timeoffset,$settings,$yytitle,$txt,$maxmessagedisplay,$enable_notification;
	global $menusep,$scripturl,$cgi,$img,$color,$db_prefix,$imagesdir,$HTTP_POST_VARS;

	$display = $numberreturned;

	// get the age of the latest post
	$request = mysql_query("SELECT posterTime FROM {$db_prefix}messages ORDER BY ID_MSG DESC LIMIT 1");
	list ($latestAge) = mysql_fetch_row($request);
	if(!is_numeric($minripe) || !is_numeric($minage) || !is_numeric($maxripe) || !is_numeric($maxage) || !is_numeric($display)) { fatal_error($txt[337]); }

	if( $userkind == 'starter' ) { $userkind = 1; }
	else if( $userkind == 'poster' ) { $userkind = 2; }
	else if( $userkind == 'noguests' ) { $userkind = 3; }
	else if( $userkind == 'onlyguests' ) { $userkind = 4; }
	else { $userkind = 0; $userspec = ''; }

	$userspec = trim($userspec);
	$userspec = preg_replace("/[^0-9A-Za-z#%+,-\.@^_]/","",$userspec);

	if( $searchtype == 'anywords' ) { $searchtype = 2; }
	else if( $searchtype == 'asphrase' ) { $searchtype = 3; }
	else { $searchtype = 1; }

	$searchsubject = ($subfield == 'on');
	$searchmessage = ($msgfield == 'on');

	$search = trim($search);
	$find = array("&","\"","<",">","\t","|");
	$replace = array("&amp;","&quot;","&lt;","&gt;","&nbsp;&nbsp;&nbsp;","&#124;");
	$search = str_replace($find,$replace,$search);

	$searcharray = array($search);
	if ($searchtype!=3)
	{
		$searcharray = array();
		foreach(explode(" ",$search) as $s)
			if (trim($s) != '')
				$searcharray[]=trim($s);
	}

	// Building the where statement to only search the boards specified
	$boardsWhere = "(";
	foreach($HTTP_POST_VARS as $postName => $postValue) {
		if (substr($postName, 0, 3) == "brd") {
			$boardsWhere .= "b.ID_BOARD=". substr($postName,3,strlen($postName)) ." || ";
		}
	}
	$boardsWhere = substr($boardsWhere,0,(strlen($boardsWhere)-4)) . ") && ";

	$curtime = $latestAge + (isset($settings[18])?(3600*$settings[18]):0) + (3600*$timeoffset);
	$mintime = $curtime - (($minage*86400)+($minripe*3600));
	$maxtime = $curtime - (($maxage*86400)+($maxripe*3600));

	$yytitle = $txt[166];
	template_header();

	$results = array();
	foreach ($searcharray as $query)
	{
		$where = '';
		$query = strtolower($query);
		if ($userkind == 1 && $userspec == '')
			$where = "($boardsWhere b.ID_CAT=c.ID_CAT && t.ID_BOARD=b.ID_BOARD && m.ID_MSG=t.ID_FIRST_MSG && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || '$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || c.memberGroups='') && (INSTR(LOWER(m.subject),'$query') || INSTR(LOWER(m.body),'$query')) && m.posterTime <= $mintime && m.posterTime > $maxtime)";
		else if ($userkind == 1 && $userspec != '')
			$where = "($boardsWhere b.ID_CAT=c.ID_CAT && t.ID_BOARD=b.ID_BOARD && m.ID_MSG=t.ID_FIRST_MSG && m.posterName='$userspec' && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || '$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || c.memberGroups='') && (INSTR(LOWER(m.subject),'$query') || INSTR(LOWER(m.body),'$query')) && m.posterTime <= $mintime && m.posterTime > $maxtime)";
		else if ($userkind == 2 && $userspec == '')
			$where = "($boardsWhere b.ID_CAT=c.ID_CAT && t.ID_BOARD=b.ID_BOARD && m.ID_TOPIC=t.ID_TOPIC && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || '$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || c.memberGroups='') && (INSTR(LOWER(m.subject),'$query') || INSTR(LOWER(m.body),'$query')) && m.posterTime <= $mintime && m.posterTime > $maxtime)";
		else if ($userkind == 2 && $userspec != '')
			$where = "($boardsWhere b.ID_CAT=c.ID_CAT && t.ID_BOARD=b.ID_BOARD && m.ID_TOPIC=t.ID_TOPIC && m.posterName='$userspec' && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || '$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || c.memberGroups='') && (INSTR(LOWER(m.subject),'$query') || INSTR(LOWER(m.body),'$query')) && m.posterTime <= $mintime && m.posterTime > $maxtime)";
		else if ($userkind == 3 && $userspec == '')
			$where = "($boardsWhere b.ID_CAT=c.ID_CAT && t.ID_BOARD=b.ID_BOARD && m.ID_TOPIC=t.ID_TOPIC && m.ID_MEMBER!=-1 && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || '$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || c.memberGroups='') && (INSTR(LOWER(m.subject),'$query') || INSTR(LOWER(m.body),'$query')) && m.posterTime <= $mintime && m.posterTime > $maxtime)";
		else if ($userkind == 3 && $userspec != '')
			$where = "($boardsWhere b.ID_CAT=c.ID_CAT && t.ID_BOARD=b.ID_BOARD && m.ID_TOPIC=t.ID_TOPIC && m.ID_MEMBER!=-1 && m.posterName='$userspec' && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || '$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || c.memberGroups='') && (INSTR(LOWER(m.subject),'$query') || INSTR(LOWER(m.body),'$query')) && m.posterTime <= $mintime && m.posterTime > $maxtime)";
		else if ($userkind == 4 && $userspec == '')
			$where = "($boardsWhere b.ID_CAT=c.ID_CAT && t.ID_BOARD=b.ID_BOARD && m.ID_TOPIC=t.ID_TOPIC && m.ID_MEMBER=-1 && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || '$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || c.memberGroups='') && (INSTR(LOWER(m.subject),'$query') || INSTR(LOWER(m.body),'$query')) && m.posterTime <= $mintime && m.posterTime > $maxtime)";
		else if ($userkind == 4 && $userspec != '')
			$where = "($boardsWhere b.ID_CAT=c.ID_CAT && t.ID_BOARD=b.ID_BOARD && m.ID_TOPIC=t.ID_TOPIC && m.ID_MEMBER=-1 && m.posterName='$userspec' && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || '$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || c.memberGroups='') && (INSTR(LOWER(m.subject),'$query') || INSTR(LOWER(m.body),'$query')) && m.posterTime <= $mintime && m.posterTime > $maxtime)";
		else
			$where = "($boardsWhere b.ID_CAT=c.ID_CAT && t.ID_BOARD=b.ID_BOARD && m.ID_TOPIC=t.ID_TOPIC && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || '$settings[7]'='Administrator' || '$settings[7]'='Global Moderator' || c.memberGroups='') && (INSTR(LOWER(m.subject),'$query') || INSTR(LOWER(m.body),'$query')) && m.posterTime <= $mintime && m.posterTime > $maxtime)";


		$request = mysql_query("SELECT m.ID_MSG FROM {$db_prefix}boards as b, {$db_prefix}categories as c,{$db_prefix}topics as t, {$db_prefix}messages as m WHERE $where LIMIT $display");
        if(mysql_errno())
               fatal_error($txt['yse230']);

		while ($row = mysql_fetch_row($request))
		{
			if (!isset($results[$row[0]]))
				$results[$row[0]]=1;
			else
				$results[$row[0]]++;
		}
	}
	$display = ($display < sizeof($results))?$display:sizeof($results);
	$results2 = arsort($results);

	$count = 0;
	foreach ($results as $msg => $counts)
	{
		if ($searchtype == 1 && $counts < sizeof($searcharray))
			continue;
		if ($count == $display)
			break;
		else
			$count++;
		$request = mysql_query("SELECT mes.ID_MEMBER as starterID, mes.posterName as starterName, m.subject, m.body, m.posterName, m.posterTime, m.smiliesEnabled, m.ID_MEMBER, t.ID_TOPIC, b.name as bname, b.ID_BOARD, c.name as cname FROM {$db_prefix}messages as m, {$db_prefix}topics as t, {$db_prefix}categories as c, {$db_prefix}boards as b,{$db_prefix}messages as mes WHERE (m.ID_TOPIC=t.ID_TOPIC && t.ID_BOARD=b.ID_BOARD && b.ID_CAT=c.ID_CAT && m.ID_MSG=$msg && t.ID_FIRST_MSG=mes.ID_MSG) LIMIT 1");
		$row = mysql_fetch_assoc($request);

		// create the "topic started by" string
		$starterString = "$txt[109] $row[starterName]";
		if ($row['starterID'] != '-1')
		{
			$request2 = mysql_query("SELECT memberName,realName FROM {$db_prefix}members WHERE ID_MEMBER='$row[starterID]' LIMIT 1");
			$row2 = mysql_fetch_array($request2);
			$euser=urlencode($row2['memberName']);
			$starterString = "$txt[109] <a href=\"$cgi;action=viewprofile;user=$euser\">$row2[realName]</a>";
		}

		$request = mysql_query("SELECT COUNT(*) FROM {$db_prefix}messages WHERE (ID_MSG < $msg && ID_TOPIC=$row[ID_TOPIC])");
        if (mysql_error())
         fatal_error("An error has occured: ".mysql_error());
		list($start) = mysql_fetch_row($request);
		$start = (floor($start/$maxmessagedisplay))*$maxmessagedisplay;

      // And parse UBBC (Zef)
      $row['body'] = doUBBC($row['body'], $row['smiliesEnabled']);

		foreach ($searcharray as $query)
         $row['body'] = preg_replace("/((\][^\[]*)|(^[^\[]*))(".str_replace("/", "\\/", quotemeta($query)).")/i","\\1<b>$query</b>",$row['body']);
			//$row['body'] = str_replace($query,"<b>$query</b>",$row['body']);

		$row['posterTime']=timeformat($row['posterTime']);

		$notify='';
		if($enable_notification) {
			$notify = "$menusep<a href=\"$scripturl?board=$row[ID_BOARD];action=notify;threadid=$row[ID_TOPIC];start=$start\"><img src=\"$imagesdir/notify_sm.gif\" border=0></a>";
		}

		if ($row['ID_MEMBER'] != -1)
		{
			$request2 = mysql_query("SELECT realName FROM {$db_prefix}members WHERE ID_MEMBER=$row[ID_MEMBER] LIMIT 1");
			list ($realName) = mysql_fetch_row($request2);
		}
		$euser=urlencode($row['posterName']);
		$namelink = isset($realName)?"<a href=\"$cgi;action=viewprofile;user=$euser\">$realName</a>":$row['posterName'];

		print <<<EOT
<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="$color[bordercolor]" class="bordercolor"><tr><td>
<table border="0" width="100%" cellpadding="2" cellspacing="1" bgcolor="$color[bordercolor]" class="bordercolor">
<tr>
	<td align="left" bgcolor="$color[titlebg]" class="title"><font class="text1" color="$color[titletext]" size=2>&nbsp;$count&nbsp;</font></td>
	<td bgcolor="$color[titlebg]"><font class="text1" color="$color[titletext]" size=2><b>&nbsp;$row[cname] / $row[bname] / <a href="$scripturl?board=$row[ID_BOARD];action=display;threadid=$row[ID_TOPIC];start=$start"><font class="text1" color="$color[titletext]" size=2>$row[subject]</font></a></b></font></td>
	<td align=right bgcolor="$color[titlebg]">&nbsp;<font class="text1" color="$color[titletext]" size=2>$txt[30]: $row[posterTime]&nbsp;</font></td>
</tr>
<tr>
	<td colspan=3 bgcolor="$color[catbg]" class="catbg"><font class="catbg" size=2>$starterString, $txt[105] $txt[525] $namelink</font></td>
</tr>
<tr>
	<td colspan=3 bgcolor="$color[windowbg2]" valign=top  height=80><font size=2>$row[body]</font></td>
</tr>
<tr>
	<td colspan=3 bgcolor="$color[catbg]"><font size=2>
		&nbsp;<a href="$scripturl?board=$row[ID_BOARD];action=post;threadid=$row[ID_TOPIC];title=Post+reply"><img src="$imagesdir/reply_sm.gif" border=0></a>$menusep<a href="$scripturl?board=$row[ID_BOARD];action=post;threadid=$row[ID_TOPIC];quote=$msg;title=Post+reply">$img[replyquote]</a>$notify
	</font></td>
</tr>
</table></td></tr></table><br>
EOT;
	}
print <<<EOT
$txt[167]<hr>
<font size=1><a href="$cgi">$txt[236]</a>
$txt[237]<br></font>
EOT;
	footer();
	obExit();
}

?>
