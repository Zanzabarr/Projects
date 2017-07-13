<?php 
include "../../../includes/config.php";

$cur_salt = getTinySalt();

$page_type = isset($_GET['page_type']) ? $_GET['page_type'] : false;
$page_id = isset($_GET['page_id']) ? $_GET['page_id'] : false;
$type = isset($_GET['type']) ? $_GET['type'] : false;

$uploadurl = $_config['upload_url'] . 'content/';
$uploadpath = $_config['upload_path']. 'content/';

if($type === false || $page_id === false || $page_type === false ) 	
	errorResult();
if($type != 'file' && $type != 'media' && $type != 'image') errorResult();


$size_subfolders = array();
$tmp_subfolders = $_config['multi_uploader']['content'];
foreach($tmp_subfolders as $folder => $dummy)
{
	$size_subfolders[] = $folder;
}

if($type == 'image') 
{
	$files = logged_query(
	"SELECT id as id, filename, alt FROM `pictures`
	WHERE md5(CONCAT(`page_type`,'{$cur_salt}'))=:page_type 
	  AND md5(CONCAT(`page_id`,'{$cur_salt}'))=:page_id
	ORDER BY filename"
	,0,array(
		":page_type" => $page_type,
		":page_id" => $page_id
	));
}
elseif($type == 'file') 
{
	$table = '';
	
	$files = logged_query(
	' SELECT id, filename, alt 
FROM `pictures`
WHERE md5(CONCAT(`page_type`,"'.$cur_salt.'"))=:page_type 
  AND md5(CONCAT(`page_id`,"'.$cur_salt.'"))=:page_id 
UNION
 SELECT id, filename, alt
FROM `upload_file`
WHERE md5(CONCAT(`page_type`,"'.$cur_salt.'"))=:page_type 
  AND md5(CONCAT(`page_id`,"'.$cur_salt.'"))=:page_id 
ORDER BY filename'
	,0,array(
		":page_type" => $page_type,
		":page_id" => $page_id
	));
}
elseif($type == 'media') 
{
	$files = logged_query('
	SELECT id, filename, alt FROM `upload_file`
	WHERE md5(CONCAT(`page_type`,"'.$cur_salt.'"))=:page_type 
	  AND md5(CONCAT(`page_id`,"'.$cur_salt.'"))=:page_id
	  AND filename REGEXP ".*\.(avi|AVI|wmv|WMV|flv|FLV|mpg|MPG|mp4|MP4|webm|WEBM)$"
	ORDER BY filename'
	,0,array(
		":page_type" => $page_type,
		":page_id" => $page_id
	));
 }



?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<base href="<?php echo $_config['admin_url'];?>">

<link href="components/uploading/file_manager/file_manager.css" type="text/css" rel="stylesheet">
<link href="css/tipTip.css" type="text/css" rel="stylesheet">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<link href="js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css" type="text/css" rel="stylesheet">
<script src="js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js" type="text/javascript"></script>
<script src="components/uploading/file_manager/file_manager.js" type="text/javascript"></script>
<script src="js/jquery.tiptip.js" type="text/javascript"></script>
<script src="js/jquery.modal.js" type="text/javascript"></script>
<script src="js/functions.js" type="text/javascript"></script>
</head>
<body>
<input type="hidden" id="uploadurl" value="<?php echo $uploadurl; ?>">
<input type="hidden" id="adminurl" value="<?php echo $_config['admin_url']; ?>">
<input type="hidden" id="max_file_size" value="<?php echo $_config['upload_max_file_size_Mb']; ?>">
<ul id="subfolders" style="display:none">
<?php
foreach($size_subfolders as $subfolder)
{
?>	
	<li data-key="<?php echo htmlspecialchars($subfolder,ENT_QUOTES); ?>"><?php echo ucwords(str_replace('_',' ',$subfolder)); ?></li>

<?php
}
?>

</ul>

<?php 

if(!is_array($files) || !count($files)) echo "<h3 id='nofiles' >No Files Found</h3>"; ?>
<div id='files'>
<?php
$count=0;

if(is_array($files) && count($files)) : foreach($files as $file)
{	
	$count++;
	$image_data = image_data($uploadpath,$size_subfolders, $file['filename']);
	
	// do the image version if it is...you know...an image
	if($image_data) :
		
?>
	<div class="file_wrap" data-file_id="<?php echo $file['id'];?>" data-type="image" data-name="<?php echo htmlspecialchars($file['filename'],ENT_QUOTES); ?>" data-alt="<?php echo htmlspecialchars($file['alt'],ENT_QUOTES); ?>">
		<div class="button_group">
			<img class="delete_button" src="<?php echo $_config['admin_url'];?>images/delete.png" alt='Delete'>
			<ul class="subfolders">
		<?php
		
		foreach($image_data as $subfolder => $data)
		{	
			$subfolderPath = $subfolder == 'original' ? '' : $subfolder;
		?>
				<li data-subfolder="<?php echo $subfolder;?>">
					<a class='grouped_elements' rel='<?php echo 'group_'.$file['id'];?>' href="<?php echo $uploadurl .$subfolderPath . "/". htmlspecialchars($file['filename'],ENT_QUOTES); ?>"><img src="<?php echo $_config['admin_url'];?>images/view_icon.png" alt='View'/></a>
					<span><?php echo ucwords(str_replace('_',' ',$subfolder)); ?></span>
				</li>
		<?php
		}
		?>
			</ul>
		</div>
		<div class="img_wrap">
			<span></span><img src="<?php echo $uploadurl; ?>thumb/<?php echo htmlspecialchars($file['filename'],ENT_QUOTES); ?>" alt="IMAGE" >
		</div>
		<p class="name tipTop" title="Click to Change Alt Tag"><?php echo $file['alt']; ?></p>
		<input type="text" class="name_mask" value="">
	</div>
<?php	
	else :
?>
	<div class="file_wrap isFile" data-file_id="<?php echo $file['id'];?>" data-type="file" data-name="<?php echo htmlspecialchars($file['filename'],ENT_QUOTES); ?>" data-alt="<?php echo htmlspecialchars($file['alt'],ENT_QUOTES); ?>">
		<div class="button_group">
			<img class="delete_button" src="<?php echo $_config['admin_url'];?>images/delete.png" alt='Delete'>
		</div>
		<div class="img_wrap">
			<span></span><img src="images/page_icons/<?php echo getImageByType($file['filename']); ?>" alt="FILE" >
		</div>
		<p class="name tipTop" title="Click to Change Alt Tag"><?php echo $file['alt']; ?></p>
		<input type="text" class="name_mask" value="">
	</div>
<?php	
	endif;
} endif;


?>

</div>
<div id="footer">
	<div id="uploading">
		<ul id="up_files"></ul>
		<div id="total_progress">
			<div class="bar" style="width: 0%;"></div>
		</div>
	</div>
	<ul id="messages">
	</ul>
	<?php  if ( using_ie()) : ?>
	<input class="ie_upload" id="upload_btn" type='file' name='files[]' data-url="<?php echo $_config['admin_url']; ?>js/jquery.fileupload/upload_handler.php" multiple />
	<?php else : ?>
	<div id="fake-file-wrap" style='position:relative;'>
		<div id='fake-file'>Upload Files and Images</div>
		<input type='file' name='files[]' id="upload_btn" style='position: relative;opacity:0; z-index:2;'  data-url="<?php echo $_config['admin_url']; ?>js/jquery.fileupload/upload_handler.php" multiple  />
	</div><br />
	<?php endif; ?>
</div>
<?php include $_config['admin_path']."includes/modal_confirm.php"; ?>

<script src="js/jquery.fileupload/jquery.ui.widget.js"></script>
<script src="js/jquery.fileupload/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload/jquery.fileupload.js"></script>

</body>
</html>

<?php // functions

function errorResult($msg = "<p class='error'>Unable to Open File Information</p>")
{
	die($msg);
}

function image_data($uploadpath,$size_subfolders,$file)
{
	$data=false;
//	var_dump($uploadpath);
//	var_dump($file);
	if(file_exists($uploadpath.'fullsize/'.$file))
	{
		$data=array();
		foreach($size_subfolders as $subfolder)
		{
			$data[$subfolder] = getimagesize($uploadpath.$subfolder.'/'.$file);
		}
	}
	return $data;
}

function getImageByType($filename)
{
	$knownTypes = array('default' => 'new.png','tar' => 'Archive.png', 'doc' => 'doc.png', 'docx' => 'doc.png', 'avi' => 'avi.png', 'css' => 'css.png', 'eps' => 'eps.png', 'flv' => 'fla.png', 'html' => 'html.png', 'htm' => 'html.png', 'mp3' => 'mp3.png', 'pdf' => 'pdf.png', 'ppt' => 'ppt.png' , 'pps' => 'ppt.png', 'ppt' => 'pptx.png' , 'txt' => 'text_doc.png' , 'log' => 'text_doc.png' , 'msg' => 'text_doc.png' , 'odt' => 'text_doc.png' , 'pages' => 'text_doc.png' , 'rtf' => 'text_doc.png' , 'tex' => 'text_doc.png' , 'wpd' => 'text_doc.png' , 'wps' => 'text_doc.png', 'wav' => 'wav.png', 'xls' => 'xls.png', 'xlsx' => 'xls.png', 'xlr' => 'xls.png', 'zip' => 'zip.png', 'zipx' => 'zip.png', 'aif' => 'music_doc.png', 'iff' => 'music_doc.png', 'm3u' => 'music_doc.png', 'm4a' => 'music_doc.png', 'mid' => 'music_doc.png', 'mpa' => 'music_doc.png', 'ra' => 'music_doc.png', 'wma' => 'music_doc.png', 'mov' => 'mov.png', '3g2' => 'mov.png', '3gp' => 'mov.png', 'asf' => 'mov.png', 'asx' => 'mov.png', 'flv' => 'mov.png', 'mp4' => 'mov.png', 'webm' => 'mov.png', 'mpg' => 'mov.png', 'rm' => 'mov.png', 'srt' => 'mov.png', 'swf' => 'mov.png', 'vob' => 'mov.png', 'wmv' => 'mov.png', 'max' => 'image_doc.png', 'obj' => 'image_doc.png', 'bmp' => 'image_doc.png', 'dds' => 'image_doc.png', 'psd' => 'image_doc.png', 'pspimage' => 'image_doc.png', 'tga' => 'image_doc.png', 'thm' => 'image_doc.png', 'tif' => 'image_doc.png', 'yuv' => 'image_doc.png', 'ai' => 'image_doc.png', 'eps' => 'image_doc.png', 'ps' => 'image_doc.png', 'svg' => 'image_doc.png', '7z' => 'Archive.png', 'deb' => 'Archive.png', 'gz' => 'Archive.png', 'pkg' => 'Archive.png', 'rar' => 'Archive.png', 'rpm' => 'Archive.png', 'sit' => 'Archive.png', 'sitx' => 'Archive.png', 'gz' => 'Archive.png', 'tar.gz' => 'Archive.png');

	$tmp_type = explode(".", $filename);
	$tmp_type = end($tmp_type);
	$type = strtolower($tmp_type);
	$type_pic = array_key_exists($type, $knownTypes) ? $knownTypes[$type] : $knownTypes['default'];
	
	return $type_pic;

}