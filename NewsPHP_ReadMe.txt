News.php - News Script for YaBB SE 1.3.0
Copyright (C) 2002 YaBB SE Dev Team. All Rights Reserved.
Web: http://www.yabb.info/

This was made for YaBB SE 1.1.0 and may not work with YaBB SE 1.0.0 as hasn't been tested.

1. Setup your Include statement on the page you wish your news to appear according to your type of file (either PHP or SHTML).  In this case, we are going to show you PHP Includes.  Add the include to point to your YaBB SE index.php file.

Eg. <? include("http://www.yabb.info/community/index.php"); ?>

2. Add the ?action=News;board=# to the end of the URL above.  Make sure you replace the # with the board number you want news to appear from.

Eg. <? include("http://www.yabb.info/community/index.php?action=news;board=9"); ?>

That is all you need to get a basic script running.  You can add the following to the end of that. but make sure you put a ; at the end of each variable.  These aren't needed but each are optional.

limit=#
Limits the Amount of News Items to show (Default is 5)

template=name
name is the filename of a custom template, without the extension. (default is news_template)

ext=name
name in this case, is the extension of the template file, incase the file isn't php. However this can't be used without template being before it.  (default is php)

Eg. <? include("http://www.yabb.info/community/index.php?action=news;board=9;limit=7;template=mytemplate;ext=shtml"); ?>
The above is a possible example of what it could look like.  The one just above won't work like the others though.

Eg. <? include("http://www.yabb.info/community/index.php?action=news;board=9;limit=7"); ?>
This is a working possibility, if you wanted to change the limit of News Items.


Release History:
1.1.3:	Bug Fixed - Script now displays first latest news post again.

1.1.2:	Security Hole Fixed - Private Board in categories are no longer able to be displayed as news
	Now sorts by original post date
	Sticky Topics are no longer at the top of the displayed news

1.1.1: 	First Public Release
	Added Variables called from URL rather than stored in file
	Allowed Template to be called from URL
	Made date & time format be called from YaBB SE Settings
	Truely Multilingual Use

1.1.0: Initial Internal Release