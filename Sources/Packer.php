<?php
/*****************************************************************************/
/* Packer.php                                                                */
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

class Packer
{
  var $filename;
  var $fp;

  function Packer($fn)
  {
    $this->filename = $fn;
  }

  function addFiles($files, $root, $new = true)
  {
    if($new)
      $this->fp = fopen($this->filename,"wb");
    else
      $this->fp = fopen($this->filename,"ab");
    for($i=0 ; $i < count($files) ; ++$i)
    {
      $fullfilename = $files[$i];
      if(is_dir($fullfilename))
	$this->processdir($fullfilename, $root);
      else
      {
	$size = filesize($fullfilename);
	$time = filemtime($fullfilename);

	echo "$txt[package34] $fullfilename ...\n";
	// Write file to archive
	$file = fopen($fullfilename, "rb");
	$buffer = fread($file, $size);
	fclose($file);
	fwrite($this->fp, 'file|^|'.str_replace($root."/","",$fullfilename).'|^|'.strlen($buffer).'|^|'.$time."\n");
	fwrite($this->fp, $buffer, $size);
      }
    }
    fclose($this->fp);
  }

  function processdir($name, $root)
  {
    if($name != $root)
	fputs($this->fp, 'dir|^|'.str_replace($root."/", "", $name)."\n");
	
    $dir = @opendir($name) or die("Nope");
    while($file = readdir($dir))
    {
      if($file == "." || $file == "..")
	continue;
      $fullfilename = $name."/".$file;
      if(is_dir($fullfilename))
	$this->processdir($fullfilename, $root);
      else
      {
	$size = filesize($fullfilename);
	$time = filemtime($fullfilename);

	echo "$txt[package34] $fullfilename ...\n";
	// Write file to archive
	$file = fopen($fullfilename, "rb");
	$buffer = fread($file, $size);
	fclose($file);
	fwrite($this->fp, 'file|^|'.str_replace($root."/","",$fullfilename).'|^|'.strlen($buffer).'|^|'.$time."\n");
	fwrite($this->fp, $buffer, $size);
      }
    }
    closedir($dir);
  }

  function extract($directory, $prefix = "", $overwrite = false)
  {
    $output = "";

    $this->fp = fopen($this->filename, "rb");
    while(!feof($this->fp))
    {
      $data = explode('|^|', chop(fgets($this->fp, 4086)));
      if(count($data) < 2)
         break;
      $filename = $directory."/".$data[1];
      if(ereg("^".quotemeta($prefix), $data[1]))
      {
         if($data[0] == "dir" && !file_exists($filename) && $data[1] != ".")
         {
           mkdir($filename, 0777);
           @chmod($filename, 0777);
         }
         if($data[0] == "file")
         {
           if(file_exists($filename) && filemtime($filename) >= $data[3] && !$overwrite)
           {
             $buffer = fread($this->fp, $data[2]);
             $output .= "$txt[package35]$filename$txt[package36]\n";
           }
           else
           {
             $buffer = fread($this->fp, $data[2]);
             $fp = fopen($filename, "wb");
             fputs($fp, $buffer, $data[2]);
             fclose($fp);
             @chmod($filename, 0666);
             @touch($filename, $data[3]);
             $output .= "$txt[package31]$filename ...\n";
           }
         }
      }
      else
      {
         if($data[0] == "file")
             $buffer = fread($this->fp, $data[2]);
      }
    }
    fclose($this->fp);

    return $output;
  }

  function listFiles()
  {
    $files = array();

    $this->fp = fopen($this->filename, "rb");
    while(!feof($this->fp))
    {
      $data = explode('|^|', chop(fgets($this->fp, 4086)));
      if($data[0] == "file")
      {
      	$id = count($files);
      	$files[$id]['name'] = $data[1];
      	$files[$id]['size'] = $data[2];
      	$buffer = fread($this->fp, $data[2]);
      }
    }
    fclose($this->fp);
    return $files;
  }
}
?>