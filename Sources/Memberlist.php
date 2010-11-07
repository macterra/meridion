<?php
/*****************************************************************************/
/* Memberlist.php                                                            */
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

$memberlistplver="YaBB SE 1.3.0";

// initialize variables
global $txt,$cgi,$color,$img,$TopAmmount,$action,$scripturl,$MOST_POSTS,$TableHeader,$TableFooter,$username,$db_prefix;
$Sort = $page = $LetterLinks = $TableHeader = '';

if($username == "Guest") { fatal_error($txt[223]); }
if($action == "mlall") { $Sort .= "$txt[303] | "; } else { $Sort .= "<a href=\"$cgi;action=mlall\"><font size=2 class=\"text1\" color=\"$color[titletext]\">$txt[303]</font></a> | "; }
if($action == "mlletter") { $Sort .= "$txt[304] | "; } else { $Sort .= "<a href=\"$cgi;action=mlletter\"><font size=2 class=\"text1\" color=\"$color[titletext]\">$txt[304]</font></a> | "; }
if($action == "mltop") { $Sort .= "$txt[305] $txt[411] $TopAmmount $txt[306]"; } else { $Sort .= "<a href=\"$cgi;action=mltop\"><font size=2 class=\"text1\" color=\"$color[titletext]\">$txt[305] $txt[411] $TopAmmount $txt[306]</font></a>"; }

//if($action == "mlletter") {
//	for ($i = 97; $i < 123; $i++)
//		$LetterLinks .= "<a href=\"$scripturl?action=mlletter;letter=".chr($i)."\">".strtoupper(chr($i))."</a> ";
if($action == "mlletter") {
   for ($i = 48; $i < 58; $i++)
      $LetterLinks .= "<a href=\"$scripturl?action=mlletter;letter=".chr($i)."\">".strtoupper(chr($i))."</a> ";
   for ($i = 97; $i < 123; $i++)
      $LetterLinks .= "<a href=\"$scripturl?action=mlletter;letter=".chr($i)."\">".strtoupper(chr($i))."</a> ";

}

$TableHeader .= "<table border=0 width=100% cellspacing=1 cellspacing=4 bgcolor=\"$color[bordercolor]\" class=\"bordercolor\" align=center>\n<tr>\n	<td class=\"titlebg\" bgcolor=\"$color[titlebg]\" colspan=8><b><font size=2 class=\"text1\" color=\"$color[titletext]\">$Sort</font></b></td></tr>";
if($LetterLinks != "") {
	$TableHeader .= "<tr>\n		<td class=\"catbg\" bgcolor=\"$color[catbg]\" colspan=8><b><font size=2>$LetterLinks</td>\n	</tr>\n";
}
$TableHeader .= <<<EOT
<tr>
	<td class="catbg" bgcolor="$color[catbg]" width="200"><b><font size=2>$txt[35]</font></b></td>
	<td class="catbg" bgcolor="$color[catbg]"><b><font size=2>$txt[online8]</font></b></td>
	<td class="catbg" bgcolor="$color[catbg]"><b><font size=2>$txt[307]</font></b></td>
	<td class="catbg" bgcolor="$color[catbg]"><b><font size=2>$txt[96]</font></b></td>
	<td class="catbg" bgcolor="$color[catbg]"><b><font size=2>$txt[86]</font></b></td>
	<td class="catbg" bgcolor="$color[catbg]"><b><font size=2>$txt[87]</font></b></td>
	<td class="catbg" bgcolor="$color[catbg]" width="20"><b><font size=2>$txt[513]</font></b></td>
	<td class="catbg" bgcolor="$color[catbg]"><b><font size=2>$txt[21]</font></b></td>
</tr>
EOT;

$TableFooter = "</table>";

global $NUM_MEMBERS;
$result = mysql_query ("SELECT COUNT(*) as memcount FROM {$db_prefix}members");
$row = mysql_fetch_row($result);
$NUM_MEMBERS = $row[0];
$result = mysql_query("SELECT MAX(posts) FROM {$db_prefix}members");
$row = mysql_fetch_row($result);
$MOST_POSTS = ($row[0]!=0)?$row[0]:1;

function LastOn($lastLogin)
{
  if ($lastLogin == 0) {
    return "";
  }
  
  $secs = time() - $lastLogin;
  
  if ($secs > 0) {
    if ($secs < 60) {
      return "$secs s";
    }

    if ($secs < 3600) {
      $mins = round($secs/60);
      return "$mins m";
    }

    if ($secs < (24*3600)) {
      $hours = round($secs/3600);
      return "$hours h";
    }
    
    if ($secs < (24*3600*365)) {
      $days = round($secs/3600/24);
      return "$days d";
    }
    else {
      $years = round($secs/3600/24/365);
      return "$years y";
    }
  }
  

  return "";
}

function MLAll (){
    global $user,$txt,$start,$MembersPerPage,$settings,$allow_hide_email,$color,$TableHeader,$MOST_POSTS;
	global $imagesdir,$cgi,$TableFooter,$db_prefix;
	global $NUM_MEMBERS,$yytitle;
	if ($MOST_POSTS==0) {$MOST_POSTS=1;}
	if($user == "Guest") { fatal_error($txt[223]); }
	# Get the number of members
//	$result = mysql_query ("SELECT COUNT(*) as memcount FROM {$db_prefix}members");
//	$row = mysql_fetch_row($result);
	$memcount = $NUM_MEMBERS;
	if($start == "") { $start = 0; }
//	$numshown=0;
	$numbegin = ($start + 1);
	$numend = ($start + $MembersPerPage);
	if($numend > $memcount) { $numend = $memcount; }
//	$b = $start;

	$yytitle = "$txt[308] $numbegin $txt[311] $numend";
	template_header();

	print <<<EOT
		<table border=0 cellspacing=0 cellpadding="3" align="center" width="100%">
		  <tr>
		    <td bgcolor="$color[titlebg]" align="center" class="titlebg">
		    <font size="2" class="text1" color="$color[titletext]"><B>$txt[308] $numbegin $txt[311] $numend ($txt[309] $memcount $txt[310])</B></font>
		    </td>
		  </tr>
		</table>
EOT;

	print $TableHeader;

	$request = mysql_query("SELECT memberName,realName,websiteTitle,websiteUrl,posts,memberGroup,ICQ,emailAddress,hideEmail,lastLogin FROM {$db_prefix}members WHERE 1 ORDER BY lastLogin DESC LIMIT $start,$MembersPerPage");
	while ($row = mysql_fetch_array($request)) {
		$name = $row['memberName'];
		if (OnlineStatus($row['memberName']) > 0) { $online = "$txt[online6]"; } else $online = "$txt[online7]";
		$Bar = "";
		$ICQ = "";

		$Bar = "&nbsp;";
		$ICQ = "";

		$barchart = round(($row['posts'] / $MOST_POSTS) * 100);
		if ($barchart <= 0) {$barchart = 1;}
		$Bar = "<img src=\"$imagesdir/bar.gif\" width=$barchart height=15 border=\"0\">";

		if(isset ($row['ICQ']) && $row['ICQ']) {
			$ICQ = "<a href=\"$cgi;action=icqpager;UIN=$row[ICQ]\" target=_blank><img src=\" http://web.icq.com/whitepages/online?icq=$row[ICQ]&img=5\" alt=\"$row[ICQ]\" border=0></a>";
		}
		$row['websiteTitle'] = isset($row['websiteTitle'])?$row['websiteTitle']:'';
		$row['websiteUrl'] = isset($row['websiteUrl'])?$row['websiteUrl']:'';
		$row['memberGroup'] = isset($row['memberGroup'])?$row['memberGroup']:'';
		//Fix by Omar Bazavilvazo -- Administrator & Global Moderator position shows members description instead of membergroups description
		$membergroup = $row['memberGroup']; //membergroup variable
		$mg_request = mysql_query("SELECT membergroup FROM {$db_prefix}membergroups ORDER BY ID_GROUP"); //query membergroups descriptions
		$membergroups = array();
		while ($mg_row = mysql_fetch_row($mg_request)) //retrieve all membergroups descriptions
			$membergroups[] = $mg_row[0];

		if ($membergroup == 'Administrator')
			$membergroup = $membergroups[0]; //admin description
		elseif ($membergroup == 'Global Moderator')
			$membergroup = $membergroups[7]; //Global moderator description
		//End Fix by Omar Bazavilvazo

		if($row['posts'] > 100000) { $row['posts'] = "$txt[683]"; }
		$euser=urlencode($row['memberName']);

		$lastOn = LastOn($row['lastLogin']);
		
		print <<<EOT
			<tr>
				<td class="windowbg" bgcolor="$color[windowbg]"><font size=2><a href="$cgi;action=viewprofile;user=$euser">$row[realName]</a></font></td>
				<td class="windowbg2" bgcolor="$color[windowbg2]" align="center"><a href="$cgi;action=imsend;to=$row[memberName]"><font size=2>$online $lastOn</font></td>
EOT;
				if ($row['hideEmail'] && $settings[7] != "Administrator" && $allow_hide_email) { print <<<EOT
					<td class="windowbg2" bgcolor="$color[windowbg2]"><font size=2><i>$txt[722]</i></font></td>
EOT;
				} else { print<<<EOT
					<td class="windowbg2" bgcolor="$color[windowbg2]"><font size=2><a href="mailto:$row[emailAddress]">$row[emailAddress]</a></font></td>
EOT;
				}
				print <<<EOT
				<td class="windowbg" bgcolor="$color[windowbg]"><font size=2><a href="$row[websiteUrl]" target="_blank">$row[websiteTitle]</a></font>&nbsp;</td>
				<td class="windowbg2" bgcolor="$color[windowbg2]" align="center"><font size=2>$row[posts]</font>&nbsp;</td>
				<td class="windowbg" bgcolor="$color[windowbg]"><font size=2>$membergroup</font>&nbsp;</td>
				<td class="windowbg2" bgcolor="$color[windowbg2]" align="center"><font size=2>$ICQ</font>&nbsp;</td>
				<td class="windowbg" bgcolor="$color[windowbg]">$Bar</td>
			</tr>

EOT;
	}

	print $TableFooter;

	print <<<EOT
	<table border=0 width=100% cellpadding=0 cellspacing=0>
	<tr>
		<td><font size=2><b>$txt[139]:</b>
EOT;
	$c=0;
	while(($c*$MembersPerPage) < $NUM_MEMBERS) {
		$viewc = $c+1;
		$strt = ($c*$MembersPerPage);
		if($start == $strt)
			print " $viewc";
		else
			print " <a href=\"$cgi;action=mlall;start=$strt\">$viewc</a>";
		++$c;
	}
	print<<<EOT
		</td>
	</tr>
	</table>
EOT;
	footer();
	obExit();
}

function MLByLetter (){
	global $username,$txt,$TableHeader,$TableFooter,$MOST_POSTS,$NUM_MEMBERS,$letter,$imagesdir,$cgi,$settings;
	global $color,$yytitle,$allow_hide_email,$db_prefix;
	if($username == "Guest") { fatal_error("$txt[223]"); }
	$yytitle = "$txt[312]";
	template_header();
	print $TableHeader;

	if (isset($letter))
		$request = mysql_query("SELECT * FROM {$db_prefix}members WHERE (SUBSTRING(realName,1,1)='$letter') ORDER BY realName");
	else
		$request = mysql_query("SELECT * FROM {$db_prefix}members WHERE ((LOWER(SUBSTRING(realName,1,1)) NOT BETWEEN 'a' AND 'z') AND (SUBSTRING(realName,1,1) NOT BETWEEN '0' AND '9')) ORDER BY realName");

	while ($row = mysql_fetch_array($request)) {
		$name = $row['memberName'];
		if (OnlineStatus($row['memberName']) > 0) { $online = "$txt[online6]"; } else $online = "$txt[online7]";
		$Bar = "";
		$ICQ = "";

		$barchart = (int)round(($row['posts'] / $MOST_POSTS) * 100);
		if ($barchart == 0) {$barchart = 1;}
		$Bar = "<img src=\"$imagesdir/bar.gif\" width=$barchart height=15 border=\"0\">";

		if(isset($row['ICQ']) && $row['ICQ']) {
			$ICQ = "<a href=\"$cgi;action=icqpager;UIN=$row[ICQ]\" target=_blank><img src=\" http://web.icq.com/whitepages/online?icq=$row[ICQ]&img=5\" alt=\"$row[ICQ]\" border=0></a>";
		}
		$row['websiteTitle'] = isset($row['websiteTitle'])?$row['websiteTitle']:'';
		$row['websiteUrl'] = isset($row['websiteUrl'])?$row['websiteUrl']:'';
		$row['memberGroup'] = isset($row['memberGroup'])?$row['memberGroup']:'';
		//Fix by Omar Bazavilvazo -- Administrator & Global Moderator position shows members description instead of membergroups description
		$membergroup = $row['memberGroup']; //membergroup variable
		$mg_request = mysql_query("SELECT membergroup FROM {$db_prefix}membergroups ORDER BY ID_GROUP"); //query membergroups descriptions
		$membergroups = array();
		while ($mg_row = mysql_fetch_row($mg_request)) //retrieve all membergroups descriptions
			$membergroups[] = $mg_row[0];

		if ($membergroup == 'Administrator')
			$membergroup = $membergroups[0]; //admin description
		elseif ($membergroup == 'Global Moderator')
			$membergroup = $membergroups[7]; //Global moderator description
		//End Fix by Omar Bazavilvazo

		if($row['posts'] > 100000) { $row['posts'] = "$txt[683]"; }
		$euser=urlencode($row['memberName']);

		$lastOn = LastOn($row['lastLogin']);

		print <<<EOT
			<tr>
				<td class="windowbg" bgcolor="$color[windowbg]"><font size=2><a href="$cgi;action=viewprofile;user=$euser">$row[realName]</a></font></td>
				<td align=left class="windowbg2" bgcolor="$color[windowbg2]" align="center"><a href="$cgi;action=imsend;to=$row[memberName]"><font size=2>$online $lastOn</font></td>
EOT;
				if ($row['hideEmail'] && $settings[7] != "Administrator" && $allow_hide_email) { print <<<EOT
					<td class="windowbg2" bgcolor="$color[windowbg2]"><font size=2><i>$txt[722]</i></font></td>
EOT;
				} else { print<<<EOT
					<td class="windowbg2" bgcolor="$color[windowbg2]"><font size=2><a href="mailto:$row[emailAddress]">$row[emailAddress]</a></font></td>
EOT;
				}
				print <<<EOT
				<td class="windowbg" bgcolor="$color[windowbg]"><font size=2><a href="$row[websiteUrl]" target="_blank">$row[websiteTitle]</a></font>&nbsp;</td>
				<td class="windowbg2" bgcolor="$color[windowbg2]" align="center"><font size=2>$row[posts]</font>&nbsp;</td>
				<td class="windowbg" bgcolor="$color[windowbg]"><font size=2>$membergroup</font>&nbsp;</td>
				<td class="windowbg2" bgcolor="$color[windowbg2]" align="center"><font size=2>$ICQ</font>&nbsp;</td>
				<td class="windowbg" bgcolor="$color[windowbg]">$Bar</td>
			</tr>
EOT;
	}
if (mysql_num_rows($request)==0)
	print "<tr><td colspan=8 class=\"windowbg\" bgcolor=\"$color[windowbg]\">$txt[170]</td></tr>";

	print $TableFooter;

	footer();
	obExit();
}

function MLTop () {
    global $user,$txt,$start,$MembersPerPage,$settings,$allow_hide_email,$TopAmmount,$color;
	global $TableHeader,$MOST_POSTS,$imagesdir,$cgi,$db_prefix;
	global $NUM_MEMBERS,$TableFooter,$yytitle;
 	if($user == "Guest") { fatal_error("$txt[223]"); }
	$yytitle = "$txt[313] $TopAmmount $txt[314]";
	template_header();
	print $TableHeader;

	$request = mysql_query("SELECT * FROM {$db_prefix}members WHERE 1 ORDER BY posts DESC LIMIT $TopAmmount");
	$num = 0;
	
	while ($row = mysql_fetch_array($request)) {
		$name = $row['memberName'];
		if (OnlineStatus($row['memberName']) > 0) { $online = "$txt[online6]"; } else $online = "$txt[online7]";
		$Bar = "";
		$ICQ = "";

		$Bar = "&nbsp;";
		$ICQ = "";

		$barchart = round(($row['posts'] / $MOST_POSTS) * 100);
		if ($barchart <= 0) {$barchart = 1;}
		$Bar = "<img src=\"$imagesdir/bar.gif\" width=$barchart height=15 border=\"0\">";

		if(isset($row['ICQ']) && $row['ICQ']) {
			$ICQ = "<a href=\"$cgi;action=icqpager;UIN=$row[ICQ]\" target=_blank><img src=\" http://web.icq.com/whitepages/online?icq=$row[ICQ]&img=5\" alt=\"$row[ICQ]\" border=0></a>";
		}
		$row['websiteTitle'] = isset($row['websiteTitle'])?$row['websiteTitle']:'';
		$row['websiteUrl'] = isset($row['websiteUrl'])?$row['websiteUrl']:'';
		$row['memberGroup'] = isset($row['memberGroup'])?$row['memberGroup']:'';

		if($row['posts'] > 100000) { $row['posts'] = "$txt[683]"; }
		$euser=urlencode($row['memberName']);

	$lastOn = LastOn($row['lastLogin']);
	$num += 1;
	
		print <<<EOT
			<tr>
				<td class="windowbg" bgcolor="$color[windowbg]"><font size=2>$num. <a href="$cgi;action=viewprofile;user=$euser">$row[realName]</a></font></td>
				<td align=left class="windowbg2" bgcolor="$color[windowbg2]" align="center"><a href="$cgi;action=imsend;to=$row[memberName]"><font size=2>$online $lastOn</font></td>
EOT;
				if ($row['hideEmail'] && $settings[7] != "Administrator" && $allow_hide_email) { print <<<EOT
					<td class="windowbg2" bgcolor="$color[windowbg2]"><font size=2><i>$txt[722]</i></font></td>
EOT;
				} else { print<<<EOT
					<td class="windowbg2" bgcolor="$color[windowbg2]"><font size=2><a href="mailto:$row[emailAddress]">$row[emailAddress]</a></font></td>
EOT;
				}
				print <<<EOT
				<td class="windowbg" bgcolor="$color[windowbg]"><font size=2><a href="$row[websiteUrl]" target="_blank">$row[websiteTitle]</a></font>&nbsp;</td>
				<td class="windowbg2" bgcolor="$color[windowbg2]" align="center"><font size=2>$row[posts]</font>&nbsp;</td>
				<td class="windowbg" bgcolor="$color[windowbg]"><font size=2>$row[memberGroup]</font>&nbsp;</td>
				<td class="windowbg2" bgcolor="$color[windowbg2]" align="center"><font size=2>$ICQ</font>&nbsp;</td>
				<td class="windowbg" bgcolor="$color[windowbg]">$Bar</td>
			</tr>
EOT;
	}

	print $TableFooter;

	footer();
	obExit();
}

?>
