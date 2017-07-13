<?php
session_start();
set_time_limit(180);
if(! isset($_SESSION['loggedInAsMember']) ) die('Log in to FTP');

// ok, starting over:
$initialpath = rtrim($_GET['path'],'/');
$truepath = truepath($initialpath);



if ($initialpath != $truepath ) die('Invalid Path');


$basepath = rtrim($_SESSION['ftp']['basepath'], '/'); 
$folderpath = trim(str_replace($basepath, '', $initialpath, $valid_count), '/');




if ( $valid_count !== 1) die('Illegal Path');

// find the branch name: branch = '' if this is the base level
if($folderpath == '') $branch = '';
else $branch =  substr($folderpath, 0, strpos($folderpath, '/'));
if(!$branch) $branch = $folderpath;


/*

$ses= $_SESSION;

var_dump('ftp_logged_in',$ses['ftp_logged_in']);echo "<br><br>";
unset($ses['ftp_logged_in']);
var_dump('SESS ftp',$ses['ftp']);echo "<br><br>";
unset($ses['ftp']);
unset($ses['activeModules']);
unset($ses['menulessPages']);
unset($ses['customPages']);
unset($ses['ftp_tz_offset']);
var_dump('ftp_folders',$ses['ftp_folders']);echo "<br><br>";
unset($ses['ftp_folders']);
var_dump('_SESSION',$ses);echo "<br><br>";
var_dump('_GET',$_GET);echo "<br><br>";
var_dump('initialpath',$initialpath);	echo "<br><br>";
var_dump('truepath',$truepath);			echo "<br><br>";	
var_dump('basepath',$basepath);echo "<br><br>";
var_dump('folderpath',$folderpath);		echo "<br><br>";
var_dump('branch',$branch);echo "<br><br>";
var_dump('safepath',$safepath);die();echo "<br><br>";

die();
*/

if( $folderpath != '' && (! array_key_exists($branch, $_SESSION['ftp']['readable_branches']) 
	|| $_SESSION['ftp']['readable_branches'][$branch] == 'write only') 
){
	die('Not for download');
}

$safepath = $basepath;
$safepath .= $folderpath ? "/{$folderpath}" : '';


if(is_dir($safepath)) 
{
	if (!$branch ) $zipname = "All_Folders";
	else
	{
		$zipname = end( explode('/',$safepath ) );
	}
	$zipname .= array_key_exists('all', $_GET) ? "_archive" : '';
	$zipname .= '.zip';
	$zipname = str_replace(' ', '_', $zipname);
	$tmpfile = tempnam("tmp", "zip"); 
	$za = new FlxZipArchive;
	$res = $za->open($tmpfile, ZipArchive::OVERWRITE);
	

	
	if($res === TRUE) {
		if($branch == '') // pack all user readable folders and files recursively
		{ 
			foreach( $_SESSION['ftp']['readable_branches'] as $the_folder => $restriction)
			{	
				if($restriction != 'write only') $za->addDir($safepath .'/'.$the_folder, basename($safepath .'/'.$the_folder));
			} 
		} 
		elseif (array_key_exists('all', $_GET))// pack all folders & files in the branch recursively 		
		{
			$za->addDir($safepath, basename($safepath));
		}
		else  // pack all files (no folders) in the passed folder 
		{
			$dir = opendir ($safepath);
			while ($file = readdir($dir))
			{
				if ($file == '.' || $file == '..' || filetype($safepath.'/'.$file) == 'dir') continue;
				$za->addFile($safepath.'/'.$file, $file);
			}

		}
		$za->close();	
	
		// Stream the file to the client
		header("Content-Type: application/zip");
		header("Content-Length: " . filesize($tmpfile));
		header("Content-Disposition: attachment; filename=\"{$zipname}\"");
		ob_clean();
		flush();
		readfile_chunked($tmpfile);
	
	}
	unlink($tmpfile); 
} 
elseif (file_exists($safepath)) 
{
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.str_replace(' ', '_', basename($safepath))  );
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($safepath));
    ob_clean();
    flush();
    readfile_chunked($safepath);
    exit;
}  else die('oops');


function truepath($path){
    // whether $path is unix or not
    $unipath=strlen($path)==0 || $path{0}!='/';
    // attempts to detect if path is relative in which case, add cwd
    if(strpos($path,':')===false && $unipath)
        $path=getcwd().DIRECTORY_SEPARATOR.$path;
    // resolve path parts (single dot, double dot and double delimiters)
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.'  == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    $path=implode('/', $absolutes);
    // resolve any symlinks
    if(file_exists($path) && linkinfo($path)>0)$path=readlink($path);
    // put initial separator that could have been lost
    $path=!$unipath ? '/'.$path : $path;
    return $path;
}

class FlxZipArchive extends ZipArchive {
    public function addDir($location, $name) {
        $this->addEmptyDir($name);

        $this->addDirDo($location, $name);
     } 
    private function addDirDo($location, $name) {
        $name .= '/';
        $location .= '/';

        $dir = opendir ($location);
        while ($file = readdir($dir))
        {
            if ($file == '.' || $file == '..') continue;

            $do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
            $this->$do($location . $file, $name . $file);
        }
    } 
}

function readfile_chunked($filename,$retbytes=false) {
   $chunksize = 1*(1024*1024); // how many bytes per chunk
   $buffer = '';
   $cnt =0;
   // $handle = fopen($filename, 'rb');
   $handle = fopen($filename, 'rb');
   if ($handle === false) {
       return false;
   }
   while (!feof($handle)) {
       $buffer = fread($handle, $chunksize);
       echo $buffer;
       ob_flush();
       flush();
       if ($retbytes) {
           $cnt += strlen($buffer);
       }
   }
       $status = fclose($handle);
   if ($retbytes && $status) {
       return $cnt; // return num. bytes delivered like readfile() does.
   }
   return $status;

} 