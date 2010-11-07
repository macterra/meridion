<?php
/*****************************************************************************/
/* Repair.php                                                                */
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

$repairboardsphpver = "YaBB SE 1.3.0";

function RepairBoards() {
global $db_prefix,$fixErrors,$txt;

//Make sure the user is an admin
//is_admin();


//print out the top of the webpage
template_header();


//Giant if/else. The first displays the forum errors if a variable is not set and asks if you would like to continue,
//the other fixes the errors.

		####Start displaying errors without fixing them.####

if(!isset($fixErrors)) { $fixErrors = "0"; }
if($fixErrors == "0") {
echo("
<table width=100% cellspacing=0 cellpadding=10 align=center bgcolor=#FFFFFF>
      <tr> 
        <td valign=TOP width=100%>
<table border=0 width=100% cellspacing=1 bgcolor=#6394BD class=bordercolor>
  <tr>
    <td class=titlebg bgcolor=#6E94B7><font size=2 class=text1 color=#FFFFFF><b>$txt[yse73]</b></font></td>
  </tr><tr>
    <td class=windowbg bgcolor=#AFC6DB>
$txt[yse74]:<p>
");

//grab all the posts in the db and order by topic id and msgid
//search for all messages and group by topic id and then msg id

$resultMsg = mysql_query("SELECT ID_TOPIC, ID_MSG FROM {$db_prefix}messages ORDER BY ID_TOPIC, ID_MSG");


//variable to be sure that salvage cats/boards only get created once
$createOnce = "0";

$topicOnce = "";
while($msgArray = mysql_fetch_array($resultMsg)) {

//get the topic ids from the topic table to compare and verify with the messages

	$resultTopics = mysql_query("SELECT ID_TOPIC,ID_FIRST_MSG,ID_LAST_MSG FROM {$db_prefix}topics WHERE ID_TOPIC = '$msgArray[ID_TOPIC]'");
	$topicArray = mysql_fetch_array($resultTopics);

	if($topicArray['ID_TOPIC'] == $msgArray['ID_TOPIC']) {

		//okay, the msg has a topic...now make sure that topic has good first/last msg

		if($topicOnce != $msgArray['ID_TOPIC']) {		
			$resultMsg2 = mysql_query("SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_MSG = '$topicArray[ID_FIRST_MSG]'");
			$msgArray2 = mysql_fetch_array($resultMsg2);
			if($topicArray['ID_FIRST_MSG'] != $msgArray2['ID_MSG']) {
				echo("$txt[118] $topicArray[ID_TOPIC] $txt[yse75] $topicArray[ID_FIRST_MSG]<Br>");
			}

			$resultMsg2 = mysql_query("SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_MSG = '$topicArray[ID_LAST_MSG]'");
			$msgArray2 = mysql_fetch_array($resultMsg2);
			if($topicArray['ID_LAST_MSG'] != $msgArray2['ID_MSG']) {
				echo("$txt[118] $topicArray[ID_TOPIC] $txt[yse76] $topicArray[ID_LAST_MSG]<Br>");
			}
		}
	}
	$topicOnce = $msgArray['ID_TOPIC'];
}



//Now work on the boards...order the topics by board, and then topic id. make sure the boards exist, or create them.

$resultTopics = mysql_query("SELECT ID_BOARD,ID_TOPIC,numReplies FROM {$db_prefix}topics ORDER BY ID_BOARD, ID_TOPIC");

//variable to be sure that boards only get created once
$createOnce = "0";

while($arrayTopics = mysql_fetch_array($resultTopics)) {

	//check that the board id of every topic exists

	$resultBoards = mysql_query("SELECT ID_BOARD FROM {$db_prefix}boards WHERE ID_BOARD = '$arrayTopics[ID_BOARD]'");
	$arrayBoards = mysql_fetch_array($resultBoards);

	if($arrayTopics['ID_BOARD'] == "0") {
		echo("$txt[yse78]: $txt[118] $arrayTopics[ID_TOPIC]$txt[yse79] $arrayTopics[ID_BOARD]. $txt[yse80]<br>");
	} elseif($arrayBoards['ID_BOARD'] != $arrayTopics['ID_BOARD']) {
		//There was no matching board: create board in the Salvage Area. Only do it once
		echo("$txt[118] $arrayTopics[ID_TOPIC] $txt[yse81] ($txt[yse82] $arrayTopics[ID_BOARD]).<br>");
	}

	//Count topics per board

	$resultTopics2 = mysql_query("SELECT ID_TOPIC,numReplies FROM {$db_prefix}topics WHERE ID_BOARD = '$arrayTopics[ID_BOARD]'");
	$numTopics = mysql_num_rows($resultTopics2);

	
	$totalReplies = 0;
	while($arrayReplies = mysql_fetch_array($resultTopics2)) {
		$totalReplies = $totalReplies + $arrayReplies['numReplies'];
	}

}


//Sort boards by cat id, make sure all cats exist, if they do not. Create them.

$resultBoards = mysql_query("SELECT ID_CAT,ID_BOARD FROM {$db_prefix}boards ORDER BY ID_CAT");

//variable to be sure that cats only get created once
$createOnce = "0";

while($arrayBoards = mysql_fetch_array($resultBoards)) {

	//check that the board id of every topic exists

	$resultCats = mysql_query("SELECT ID_CAT FROM {$db_prefix}categories WHERE ID_CAT = '$arrayBoards[ID_CAT]'");
	$arrayCats = mysql_fetch_array($resultCats);

	if($arrayCats['ID_CAT'] != $arrayBoards['ID_CAT']) {
		//There was no matching cat: create cat. Only do it once, and make it admin access only.
		echo("$txt[yse82] $arrayBoards[ID_BOARD] $txt[yse83] ($txt[yse84] $arrayBoards[ID_CAT]).<br>");
	}
}


echo("
<p><font size=2>$txt[yse85]<br>
<b><a href=?action=repairboards;fixErrors=1>$txt[163]</a> - <a href=?action=admin>$txt[164]</a></b><br>
</td></tr></table></td></tr></table>
");

} elseif($fixErrors == "1") {


		####End display of errors and now fix them####

echo("
<table width=100% cellspacing=0 cellpadding=10 align=center bgcolor=#FFFFFF>
      <tr> 
        <td valign=TOP width=100%>
<table border=0 width=100% cellspacing=1 bgcolor=#6394BD class=bordercolor>
  <tr>
    <td class=titlebg bgcolor=#6E94B7><font size=2 class=text1 color=#FFFFFF><b>$txt[yse86]</b></font></td>
  </tr><tr>
    <td class=windowbg bgcolor=#AFC6DB>
");


//grab all the posts in the db and order by topic id and msgid
//search for all messages and group by topic id and then msg id

$str = "SELECT ID_TOPIC, ID_MSG FROM {$db_prefix}messages ORDER BY ID_TOPIC, ID_MSG";
$resultMsg = mysql_query($str);


//variable to be sure that salvage cats/boards only get created once
$createOnce = "0";

$topicOnce = "";
while($msgArray = mysql_fetch_array($resultMsg)) {
	
//get the topic ids from the topic table to compare and verify with the messages

	$resultTopics = mysql_query("SELECT ID_TOPIC,ID_FIRST_MSG,ID_LAST_MSG FROM {$db_prefix}topics WHERE ID_TOPIC = '$msgArray[ID_TOPIC]'");
	$topicArray = mysql_fetch_array($resultTopics);

	if($topicArray['ID_TOPIC'] == $msgArray['ID_TOPIC']) {

		//okay, the msg has a topic...now make sure that topic has good first/last msg
		
		if($topicOnce != $msgArray['ID_TOPIC']) {		
			$resultMsg2 = mysql_query("SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_MSG = '$topicArray[ID_FIRST_MSG]'");
			$msgArray2 = mysql_fetch_array($resultMsg2);
			if($topicArray['ID_FIRST_MSG'] != $msgArray2['ID_MSG']) {

				$resultFirstMsg = mysql_query("SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_TOPIC = '$topicArray[ID_TOPIC]' ORDER BY ID_MSG ASC");
				if($firstMsgArray = mysql_fetch_array($resultFirstMsg)) {
					mysql_query("UPDATE {$db_prefix}topics SET ID_FIRST_MSG = '$firstMsgArray[0]' WHERE ID_TOPIC = '$topicArray[ID_TOPIC]'");
				} else {
                    //Lines 211-219 all there to delete attachments on thread deletion - Jeff
                    $request = mysql_query("SELECT attachmentFilename FROM {$db_prefix}messages WHERE ID_TOPIC=$topicArray[ID_TOPIC] AND attachmentFilename<> NULL");

                    if (mysql_numrows($request)>0){
                    while($row = mysql_fetch_array($request)){
                    unlink($modSettings['attachmentUploadDir'] . "/" . $row['attachmentFilename']);

                       }
                     }
					mysql_query("DELETE FROM {$db_prefix}topics WHERE ID_TOPIC = '$topicArray[ID_TOPIC]'");
				}
			}
	
			$resultMsg2 = mysql_query("SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_MSG = '$topicArray[ID_LAST_MSG]'");
			$msgArray2 = mysql_fetch_array($resultMsg2);
			if($topicArray['ID_LAST_MSG'] != $msgArray2['ID_MSG']) {

				$resultLastMsg = mysql_query("SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_TOPIC = '$topicArray[ID_TOPIC]' ORDER BY ID_MSG DESC");
				if($lastMsgArray = mysql_fetch_array($resultLastMsg)) {
					mysql_query("UPDATE {$db_prefix}topics SET ID_LAST_MSG = '$lastMsgArray[0]' WHERE ID_TOPIC = '$topicArray[ID_TOPIC]'");
					if(mysql_affected_rows() < 0) {echo("$txt[yse87] ID_LAST_MSG $txt[yse88] {$db_prefix}topics (ID_TOPIC = $topicArray[ID_TOPIC])"); }
				} else {
                    //Lines 233-241 all there to delete attachments on thread deletion - Jeff
                    $request = mysql_query("SELECT attachmentFilename FROM {$db_prefix}messages WHERE ID_TOPIC=$topicArray[ID_TOPIC] AND attachmentFilename<>NULL");

                    if (mysql_numrows($request)>0){
                    while($row = mysql_fetch_array($request)){
                    unlink($modSettings['attachmentUploadDir'] . "/" . $row['attachmentFilename']);

                       }
                     }
					mysql_query("DELETE FROM {$db_prefix}topics WHERE ID_TOPIC = '$topicArray[ID_TOPIC]'");
				}
			}
		}
} else {
		//first create a cat/board for the msg w/out topic
		//only do it once

	if($createOnce < 1) {
		$result = mysql_query("SELECT ID_CAT FROM {$db_prefix}categories WHERE name = 'Salvage Area' AND memberGroups = 'Administrator' ORDER BY ID_CAT DESC");
		$arraySalvage = mysql_fetch_array($result);
		if($arraySalvage['0'] == 0) {
			$insert = "INSERT INTO {$db_prefix}categories (name,memberGroups,catOrder) VALUES ('Salvage Area','Administrator','-1')";
			mysql_query($insert);
			if(mysql_affected_rows() < 0) { echo("$txt[yse89] $txt[yse82]"); }
		}
			$result = mysql_query("SELECT ID_CAT FROM {$db_prefix}categories ORDER BY ID_CAT DESC");
			$arrayCatNum = mysql_fetch_array($result);

			$insert = "INSERT INTO {$db_prefix}boards (name,description,moderators,ID_CAT) VALUES ('Salvaged Messages','Topics created for messages with non-existant topic ids','','$arrayCatNum[0]')";
			mysql_query($insert);
			if(mysql_affected_rows() < 0) { echo("$txt[yse89] $txt[yse84]"); }

			$createOnce = 1;
	}

		//create a topic for msgArray['ID_MSG']

		$resultFindLast = mysql_query("SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_TOPIC = '$msgArray[ID_TOPIC]' ORDER BY ID_MSG DESC");
		$findLastArray = mysql_fetch_array($resultFindLast);

		$result = mysql_query("SELECT ID_BOARD FROM {$db_prefix}boards ORDER BY ID_BOARD DESC");
		$arrayBoardNum = mysql_fetch_array($result);

		$resultRepliesNum = mysql_query("SELECT ID_MSG FROM {$db_prefix}messages WHERE ID_TOPIC = '$msgArray[ID_TOPIC]'");
		$numReplies = mysql_num_rows($resultRepliesNum) - 1;

		$insert = "INSERT INTO {$db_prefix}topics (ID_TOPIC,ID_BOARD,ID_MEMBER_STARTED,ID_MEMBER_UPDATED,ID_FIRST_MSG,ID_LAST_MSG,numReplies) VALUES ('$msgArray[ID_TOPIC]','$arrayBoardNum','repairBoards','repairBoards','$msgArray[ID_MSG]','$findLastArray[0]','$numReplies')";
		$result = mysql_query($insert);
		if(mysql_affected_rows() < 0) { echo("$txt[yse89] $txt[118]. ID_TOPIC = $msgArray[ID_TOPIC], ID_MSG = $msgArray[ID_MSG]"); }
}
	$topicOnce = $msgArray['ID_TOPIC'];
}


//Now work on the boards...order the topics by board, and then topic id. make sure the boards exist, or create them.

$resultTopics = mysql_query("SELECT ID_BOARD,ID_TOPIC,numReplies FROM {$db_prefix}topics ORDER BY ID_BOARD, ID_TOPIC");

//variable to be sure that boards only get created once
$createOnce = 0;

while($arrayTopics = mysql_fetch_array($resultTopics)) {

	//check that the board id of every topic exists

	$resultBoards = mysql_query("SELECT ID_BOARD FROM {$db_prefix}boards WHERE ID_BOARD = '$arrayTopics[ID_BOARD]'");
	$arrayBoards = mysql_fetch_array($resultBoards);



	if($arrayTopics['ID_BOARD'] == "0") {
        //Lines 304-312 all there to delete attachments on thread deletion - Jeff
                    $request = mysql_query("SELECT attachmentFilename FROM {$db_prefix}messages WHERE ID_TOPIC=$topicArray[ID_TOPIC] AND attachmentFilename<>NULL");

                    if (mysql_numrows($request)>0){
                    while($row = mysql_fetch_array($request)){
                    unlink($modSettings['attachmentUploadDir'] . "/" . $row['attachmentFilename']);

                       }
                     }
		mysql_query("DELETE FROM {$db_prefix}topics WHERE ID_TOPIC = '$arrayTopics[ID_TOPIC]'");
		mysql_query("DELETE FROM {$db_prefix}messages WHERE ID_TOPIC = '$arrayTopics[ID_TOPIC]'");
	} elseif($arrayBoards['ID_BOARD'] != $arrayTopics['ID_BOARD']) {
		//There was no matching board: create board in the Salvage Area. Only do it once

		$result = mysql_query("SELECT ID_CAT FROM {$db_prefix}categories WHERE name = 'Salvage Area' AND memberGroups = 'Administrator' ORDER BY ID_CAT DESC");
		$arraySalvage = mysql_fetch_array($result);
		if($arraySalvage['0'] == 0) {
			$insert = "INSERT INTO {$db_prefix}categories (name,memberGroups,catOrder) VALUES ('Salvage Area','Administrator','-1')";
			if(!mysql_query($insert)) { echo("Error creating a category 2"); }
			$result = mysql_query("SELECT ID_CAT FROM {$db_prefix}categories WHERE name = 'Salvage Area' AND memberGroups = 'Administrator' ORDER BY ID_CAT DESC");
			$arraySalvage = mysql_fetch_array($result);
		}

		$insert = "INSERT INTO {$db_prefix}boards (name,description,moderators,ID_CAT,ID_BOARD) VALUES ('Repair Board ID $arrayTopics[ID_BOARD]','This board was automatically created by the Repair Boards function. It is for topics with bad boards.','','$arraySalvage[0]','$arrayTopics[ID_BOARD]')";

		if(!mysql_query($insert)) { echo("Error creating a board2"); }
	} 


	//Count topics per board

	$resultTopics2 = mysql_query("SELECT ID_TOPIC,numReplies FROM {$db_prefix}topics WHERE ID_BOARD = '$arrayTopics[ID_BOARD]'");
	$numTopics = mysql_num_rows($resultTopics2);
	mysql_query("UPDATE {$db_prefix}boards SET numTopics = '$numTopics' WHERE ID_BOARD = '$arrayTopics[ID_BOARD]'");
	if(mysql_affected_rows() < 0) {
		echo("$txt[yse87] $txt[yse90] $tst[yse82] $arrayTopics[ID_BOARD]");
	}

	$totalReplies = 0;
	while($arrayReplies = mysql_fetch_array($resultTopics2)) {
		$totalReplies = $totalReplies + $arrayReplies['numReplies'];
	}
	$result = mysql_query("UPDATE {$db_prefix}boards SET numPosts = '$totalReplies' WHERE ID_BOARD = '$arrayTopics[ID_BOARD]'");
	if(mysql_affected_rows() < 0) {
		echo("$txt[yse87] $txt[yse91] $tst[yse82] $arrayTopics[ID_BOARD]");
	}
}


//Sort boards by cat id, make sure all cats exist, if they do not. Create them.

$resultBoards = mysql_query("SELECT ID_CAT FROM {$db_prefix}boards ORDER BY ID_CAT");

//variable to be sure that cats only get created once
$createOnce = 0;

while($arrayBoards = mysql_fetch_array($resultBoards)) {

	//check that the cat id of every board exists

	$resultCats = mysql_query("SELECT ID_CAT FROM {$db_prefix}categories WHERE ID_CAT = '$arrayBoards[ID_CAT]'");
	$arrayCats = mysql_fetch_array($resultCats);

	if($arrayCats['ID_CAT'] != $arrayBoards['ID_CAT']) {
		//There was no matching cat: create cat. Only do it once, and make it admin access only.

		if($createOnce < 1) {
			$insert = "INSERT INTO {$db_prefix}categories (ID_CAT,name,memberGroups,catOrder) VALUES ('$arrayBoards[ID_CAT]','Repair Category ID $arrayBoards[ID_CAT]','Administrator','-1')";
			if($result != mysql_query($insert)) { echo("$txt[yse89] $txt[yse89]"); }
			$createOnce = 1;
		}
	}
}


//Last step-make sure all non-guest posters still exist

$resultMember = mysql_query("SELECT ID_MEMBER FROM {$db_prefix}messages WHERE ID_MEMBER != '-1'");

while($memberArray = mysql_fetch_array($resultMember)) {
	$resultGuest = mysql_query("SELECT ID_MEMBER FROM {$db_prefix}members WHERE ID_MEMBER = '$memberArray[ID_MEMBER]'");

	if(!@mysql_fetch_array($resultGuest)) {
		mysql_query("UPDATE {$db_prefix}messages SET ID_MEMBER = '-1' WHERE ID_MEMBER = '$memberArray[ID_MEMBER]'");
	}
}

$resultMember = mysql_query("SELECT ID_MEMBER_STARTED FROM {$db_prefix}topics WHERE ID_MEMBER_STARTED != '-1'");

while($memberArray = mysql_fetch_array($resultMember)) {
	$resultGuest = mysql_query("SELECT ID_MEMBER FROM {$db_prefix}members WHERE ID_MEMBER = '$memberArray[ID_MEMBER_STARTED]'");

	if(!@mysql_fetch_array($resultGuest)) {
		mysql_query("UPDATE {$db_prefix}topics SET ID_MEMBER_STARTED = '-1' WHERE ID_MEMBER_STARTED = '$memberArray[ID_MEMBER_STARTED]'");
	}
}

echo("
<p><font size=2>$txt[yse92]<p>
<a href=?action=admin>$txt[137]</a><br>
<a href=>$txt[236] $txt[237]</a><br>
</td></tr></table></td></tr></table>
");

}

footer();
obExit();
}
?>
