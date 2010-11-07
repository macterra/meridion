<?php
/*****************************************************************************/
/* Settings.php                                                              */
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

########## Board Info ##########
# Note: these settings must be properly changed for YaBB to work

$maintenance = 0;				# Set to 1 to enable Maintenance mode
$mtitle = "BBS down";                       # Subject for display
$mmessage = "Moving server";                   # Message Description for display

$guestaccess = 1;				# Set to 0 to disallow guests from doing anything but login or register

$yyForceIIS = 0;				# Set to 1 if you encounter errors while running on an MS IIS server
$yyblankpageIIS = 0;			# Set to 1 if you encounter blank pages after posting (usually on MS IIS servers)

$language = "english.lng";				# Change to language pack you want to use
$mbname = "Church of Virus BBS";					# The name of your YaBB forum
$boardurl = "http://www.churchofvirus.org/bbs";				# URL of your board's folder (without trailing '/')

$Cookie_Length = 60;			# Cookies will expire after XX minutes of person logging in (they will be logged out after)
$cookieusername = "COVusername";			# Name of the username cookie
$cookiepassword = "COVpassword";			# Name of the password cookie

$RegAgree = 1;					# Set to 1 to display the registration agreement when registering
$emailpassword = 1;			# 0 - instant registration. 1 - password emailed to new members
$emailnewpass = 0;				# Set to 1 to email a new password to members if they change their email address
$emailwelcome = 1;				# Set to 1 to email a welcome message to users even when you have mail password turned off

$mailprog = "/usr/sbin/sendmail";				# Location of your sendmail program
$smtp_server = "localhost";				# SMTP-Server
$webmaster_email = "webmaster@lucifer.com";		# Your e-mail address.
$mailtype = 0;					# 0 - sendmail, 1 - SMTP

########## Database Info ##########
$db_name = "yabbse";
$db_user = "root";
$db_passwd = "";
$db_server = "localhost";
$db_prefix = "cov_";

########## Directories/Files ##########
# Note: directories other than $imagesdir do not have to be changed unless you move things

$boarddir = "/home/virus/new/bbs/"; 				# The absolute path to the board's folder (usually can be left as '.')
$sourcedir = "/home/virus/new/bbs//Sources";        			# Directory with YaBB source files
$facesdir = "/home/virus/new/bbs//YaBBImages/avatars";				# Absolute Path to your avatars folder
$facesurl = "http://www.churchofvirus.org/bbs/YaBBImages/avatars";				# URL to your avatars folder
$imagesdir = "http://www.churchofvirus.org/bbs/YaBBImages";				# URL to your images directory
$ubbcjspath = "http://www.churchofvirus.org/bbs/ubbc.js";	                        # Web path to your 'ubbc.js' REQUIRED for post/modify to work properly!
$faderpath = "http://www.churchofvirus.org/bbs/fader.js";				# Web path to your 'fader.js'
$helpfile = "http://www.churchofvirus.org/bbs/YaBBHelp/index.html";				# Location of your help file


########## Colors ##########
# Note: equivalent to colors in CSS tag of template.html, so set to same colors preferrably
# for browsers without CSS compatibility and for some items that don't use the CSS tag

$color['titlebg'] = "#999966";		# Background color of the 'title-bar'
$color['titletext'] = "#FFFFFF";		# Color of text in the 'title-bar' (above each 'window')
$color['windowbg'] = "#000033";		# Background color for messages/forms etc.
$color['windowbg2'] = "#000000";		# Background color for messages/forms etc.
$color['windowbg3'] = "#000033";		# Color of horizontal rules in posts
$color['catbg'] = "#000033";			# Background color for category (at Board Index)
$color['bordercolor'] = "#999966";	# Table Border color for some tables
$color['fadertext']  = "#000033";		# Color of text in the NewsFader ("The Latest News" color)
$color['fadertext2']  = "#FFFFFF";	# Color of text in the NewsFader (news color)

########## Layout ##########

$MenuType = 0;					# 1 for text menu or anything else for images menu
$curposlinks = 1;				# 1 for links in navigation on current page, or 0 for text without link
$profilebutton = 1;			# 1 to show view profile button under post, or 0 for blank
$timeformatstring = "%Y-%m-%d %H:%M:%S";				# Select your preferred output Format of Time and Date
$allow_hide_email = 1;			# Allow users to hide their email from public. Set 0 to disable
$showlatestmember = 1;			# Set to 1 to display "Welcome Newest Member" on the Board Index
$shownewsfader = 0;			# 1 to allow or 0 to disallow NewsFader javascript on the Board Index
							# If 0, you'll have no news at all unless you put <yabb news> tag
							# back into template.html!!!
$Show_RecentBar = 1;			# Set to 1 to display the Recent Posts bar on Board Index
$Show_MemberBar = 1;			# Set to 1 to display the Members List table row on Board Index
$showmarkread = 1;				# Set to 1 to display and enable the mark as read buttons
$showmodify = 1;				# Set to 1 to display "Last modified: Realname - Date" under each message
$ShowBDescrip = 1;				# Set to 1 to display board descriptions on the topic (message) index for each board
$showuserpic = 1;				# Set to 1 to display each member's picture in the message view (by the ICQ.. etc.)
$showusertext = 1;				# Set to 1 to display each member's personal text in the message view (by the ICQ.. etc.)
$showgenderimage = 1;			# Set to 1 to display each member's gender in the message view (by the ICQ.. etc.)
$showyabbcbutt = 1;                       # Set to 1 to display the yabbc buttons on Posting and IM Send Pages

########## Feature Settings ##########

$enable_ubbc = 1;				# Set to 1 if you want to enable UBBC (Uniform Bulletin Board Code)
$enable_news = 1;				# Set to 1 to turn news on, or 0 to set news off
$allowpics = 1;				# set to 1 to allow members to choose avatars in their profile
$enable_guestposting = 0;		# Set to 0 if do not allow 1 is allow.
$enable_notification = 1;		# Allow e-mail notification
$autolinkurls = 1;				# Set to 1 to turn URLs into links, or 0 for no auto-linking.

$timeoffset = 0;				# Time Offset (so if your server is EST, this would be set to -1 for CST)
$TopAmmount = 100;				# No. of top posters to display on the top members list
$MembersPerPage = 30;			# No. of members to display per page of Members List - All
$maxdisplay = 20;				# Maximum of topics to display
$maxmessagedisplay = 15;		# Maximum of messages to display
$MaxMessLen = 99999;  				# Maximum Allowed Characters in a Posts
$MaxSigLen = 300;				# Maximum Allowed Characters in Signatures
$ClickLogTime = 600;				# Time in minutes to log every click to your forum (longer time means larger log file size)
$max_log_days_old = 21;			# If an entry in the user's log is older than ... days remove it
							# Set to 0 if you want it disabled
$fadertime = 500;				# Length in seconds to display each item in the news fader
$timeout = 5;					# Minimum time between 2 postings from the same IP


########## Membergroups ##########

$JrPostNum = 5;					# Number of Posts required to show person as 'junior' membergroup
$FullPostNum = 100;				# Number of Posts required to show person as 'full' membergroup
$SrPostNum = 500;					# Number of Posts required to show person as 'senior' membergroup
$GodPostNum = 1000;					# Number of Posts required to show person as 'god' membergroup


########## MemberPic Settings ##########

$userpic_width = 70;			# Set pixel size to which the selfselected userpics are resized, 0 disables this limit
$userpic_height = 70;			# Set pixel size to which the selfselected userpics are resized, 0 disables this limit
$userpic_limits = "Please note that your image has to be <b>gif</b> or <b>jpg</b> and that it will be resized!";			# Text To Describe The Limits
?>