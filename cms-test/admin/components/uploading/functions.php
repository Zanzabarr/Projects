<?php
function getImages($uploadPage, $uploadPageType)
{
	return logged_query("SELECT * FROM pictures WHERE page_id =:page_id AND page_type =:uploadPageType",0,array(":page_id" => $uploadPage, ":uploadPageType" => $uploadPageType) );

	global $_config;

	$pageImageQuery=mysql_query("SELECT * FROM pictures WHERE page_id = '".$uploadPage."' AND page_type ='".$uploadPageType."'" );

	$images=array();
	while($pageImg = mysql_fetch_assoc($pageImageQuery)) 
	{
		$images[] = $pageImg;
	}
	return $images;
}

function getFiles($uploadPage, $uploadPageType)
{
	return logged_query("SELECT * FROM upload_file WHERE page_id =:page_id AND page_type =:uploadPageType",0,array(":page_id" => $uploadPage, ":uploadPageType" => $uploadPageType) );
}

// parm: 	array of results from table upload_file formatted as an array
// return:	2d array with appropriate 'picture' element added: name of image that corresponds with type
// 
// note: knownTypes array must be updated in uploads.js, at top of file, as well:
//		
function setImageByType($uploads)
{
	$knownTypes = array('default' => 'new.png','tar' => 'Archive.png', 'doc' => 'doc.png', 'docx' => 'doc.png', 'avi' => 'avi.png', 'css' => 'css.png', 'eps' => 'eps.png', 'flv' => 'fla.png', 'html' => 'html.png', 'htm' => 'html.png', 'mp3' => 'mp3.png', 'pdf' => 'pdf.png', 'ppt' => 'ppt.png' , 'pps' => 'ppt.png', 'ppt' => 'pptx.png' , 'txt' => 'text_doc.png' , 'log' => 'text_doc.png' , 'msg' => 'text_doc.png' , 'odt' => 'text_doc.png' , 'pages' => 'text_doc.png' , 'rtf' => 'text_doc.png' , 'tex' => 'text_doc.png' , 'wpd' => 'text_doc.png' , 'wps' => 'text_doc.png', 'wav' => 'wav.png', 'xls' => 'xls.png', 'xlsx' => 'xls.png', 'xlr' => 'xls.png', 'zip' => 'zip.png', 'zipx' => 'zip.png', 'aif' => 'music_doc.png', 'iff' => 'music_doc.png', 'm3u' => 'music_doc.png', 'm4a' => 'music_doc.png', 'mid' => 'music_doc.png', 'mpa' => 'music_doc.png', 'ra' => 'music_doc.png', 'wma' => 'music_doc.png', 'mov' => 'mov.png', '3g2' => 'mov.png', '3gp' => 'mov.png', 'asf' => 'mov.png', 'asx' => 'mov.png', 'flv' => 'mov.png', 'mp4' => 'mov.png', 'mpg' => 'mov.png', 'rm' => 'mov.png', 'srt' => 'mov.png', 'swf' => 'mov.png', 'vob' => 'mov.png', 'wmv' => 'mov.png', 'max' => 'image_doc.png', 'obj' => 'image_doc.png', 'bmp' => 'image_doc.png', 'dds' => 'image_doc.png', 'psd' => 'image_doc.png', 'pspimage' => 'image_doc.png', 'tga' => 'image_doc.png', 'thm' => 'image_doc.png', 'tif' => 'image_doc.png', 'yuv' => 'image_doc.png', 'ai' => 'image_doc.png', 'eps' => 'image_doc.png', 'ps' => 'image_doc.png', 'svg' => 'image_doc.png', '7z' => 'Archive.png', 'deb' => 'Archive.png', 'gz' => 'Archive.png', 'pkg' => 'Archive.png', 'rar' => 'Archive.png', 'rpm' => 'Archive.png', 'sit' => 'Archive.png', 'sitx' => 'Archive.png', 'gz' => 'Archive.png', 'tar.gz' => 'Archive.png');
	$tmpUploads = array();
	foreach ($uploads as $upload)
	{
		$tmp_type = explode(".", $upload['filename']);
		$tmp_type = end($tmp_type);
		$type = strtolower($tmp_type);
		$type_pic = array_key_exists($type, $knownTypes) ? $knownTypes[$type] : $knownTypes['default'];
		$upload['type_pic'] = 'page_icons/' . $type_pic;
		$tmpUploads[]=$upload;
	}
	return $tmpUploads;

}
?>