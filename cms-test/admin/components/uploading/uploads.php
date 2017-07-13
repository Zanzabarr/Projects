<?php
/*
*	Prerequisites: these variables must be set before including the uploads component
*		$uploadPage 		: current page's id
*		$uploadPageType 	: regular pages are 'pages', 
*								it must be the table name that holds $uploadPage's column 
*								must have a column: status, where 0 or null means not published
*		$uploadTitle		: the upload sections title can be switched from the default
*
*	Optional
*		$useFullPath		: by default, uploader uses a relative path to display a/img
*								to use the full path(for externals like emails, $useFullPath must be set to true
*		$linkTargetID		: by default, uploader sends links to tinyMCE instance: #content
*								to use target different/more tinyMCE tinyMCE instances: enter an array of id names
*								eg $linkTargetID = array ('desc', 'short_desc');
*		$noLinkButton		: by default, uploader provides a button to create a link to download/show movie
*								to disable this button, set $noLinkButton = true
*		$uploadFolder		: by default, uploadfolder is 'content', uses a different value if set
*								NOTE: if a different value is set /admin/includes/classes/content_uploadhelper.php in classes will not be used!
*									  a new upload handler will need to be used for such a custom uploader
*/
$localUploadTitle = isset($uploadTitle) ? $uploadTitle : "Images and Other Files";
$uploadPageType = isset($uploadPageType) ? $uploadPageType : "pages";
if(!isset($linkTargetID) ) $linkTargetID = array('content'); 
$noLinkButton = (isset($noLinkButton) && $noLinkButton) ? true : false;
$uploadFolder = (isset($uploadFolder) && $uploadFolder) ? $uploadFolder: 'content';


// include uploads functions
include($_config['components'] . 'uploading/functions.php');
$useFullPath = isset($useFullPath) ? $useFullPath : false;
// get info to display images and uploads
$uploads = getFiles($uploadPage, $uploadPageType);
// assign images based on their type (or default)
$uploads = setImageByType($uploads);

// get the images for display
$images = getImages($uploadPage, $uploadPageType);

// success/error messages
if (array_key_exists('error', $_GET) && is_numeric($_GET['error']))
{
	switch (mysql_real_escape_string($_GET['error'])) {
		case 0:
			$imageSuccess = "Successfully Uploaded";
			break;
		case 1:
			$imageErr = "Invalid Upload";
			break;
		case 2:
			$imageErr = "Please upload only pictures smaller than 3mb.";
			break;
		case 3:
			$imageErr = "Please upload only images, no other files are supported.";
			break;
		case 4:
			$imageErr = "Please upload only images of type JPEG, GIF or PNG.";
			break;	
		case 5:
			$imageErr = "Please upload an image larger than 124px.";
			break;
		case 6:
			$imageErr = "Image limit reached:<br />remove extra images before uploading more.";
			break;	
	}
}


?>
		<h2 class="tiptip toggle" id="image-toggle" title="Add and remove images available for this page." ><?php echo $localUploadTitle; ?></h2>
		<div id="image-toggle-wrap">
			
				<div class='input_wrap'>
					<label class="tipRight" title="Upload images (jpeg, gif, png) for display on page or any file type for download." >Upload Filename</label>
					<div class='input_inner'>
						<div class='message_wrap'>
							<span id='image_msg' <?php echo isset($imageErr) ? "class='errorMsg'" : (isset($imageSuccess) ? "class='successMsg'" :''); ?>><?php echo isset($imageErr) ? $imageErr : (isset($imageSuccess) ? $imageSuccess : '' ); ?></span>
						</div>
						<?php  if (using_ie()) : ?>
						<input type='file' name='files[]' style='width:180px;'data-url="<?php echo $_config['admin_url']; ?>js/jquery.fileupload/upload_handler.php" multiple>
						<?php else : ?>
						<div id="fake-file-wrap" style='position:relative;'>
							<div id='fake-file'>Click to Select a File</div>
							<input type='file' name='files[]' id="imageUploader_file" style='position: relative;opacity:0; z-index:2;'  data-url="<?php echo $_config['admin_url']; ?>js/jquery.fileupload/upload_handler.php" multiple  />
						</div><br />
						<?php endif; ?>
					</div>
				</div>	
				<div id="image-progress">
					<div class="bar" style="width: 0%;"></div>
				</div>
				<p id="image-no-progress">(Click above or drag and drop videos into field above)</p>
			
			<div class='message_wrap'>
				<span id='image_msg' <?php echo isset($imageErr) ? "class='errorMsg'" : (isset($imageSuccess) ? "class='successMsg'" :''); ?>><?php echo isset($imageErr) ? $imageErr : (isset($imageSuccess) ? $imageSuccess : '' ); ?></span>
			</div>	
				<div class='clearFix'></div>


				<input type="hidden" id="page_id"  value="<?php echo $uploadPage; ?>" />
				<input type="hidden" id="page_type" value="<?php echo $uploadPageType; ?>" />
				<input type="hidden" id="page_upload_folder" value="<?php echo $uploadFolder; ?>" />
				<input type="hidden" id="image_admin_url"  value="<?php echo $_config['admin_url'] ?>" />
				<input type="hidden" id="images_upload_base"  value="<?php echo $_config['upload_base'] ?>" />

			<?php if ($useFullPath) : ?>
				<input type="hidden" id="image_useFullPath" value='1' />
			<?php endif; ?>
			<?php if ($noLinkButton) : ?>
				<input type="hidden" id="image_noLinkButton" value='1' />
			<?php endif; ?>
			<?php foreach($linkTargetID as $linkTID) :?>
				<input type="hidden" class="image_linkTargetID" value='<?php echo $linkTID;?>' />
			<?php endforeach;?>
<!--  end of new stuff    -->


			
			<br style="clear:both" />
					

			
	<!-- <a href="<?php  /* echo $_config['upload_url'].'files/AdobeDownloadAssistant.exe' ; */ ?>">Download</a>		-->
	
			<?php 
			$hasImages = count($images) > 0;
			$hasImagesClass = $hasImages ? ' hasImages':'';
			?>
	
			<div class="uploadInfoWrap">
				<label class="tipRight<?php echo $hasImagesClass; ?>" title="Images available for display on the page or to provide a link for visitors to download a copy." >Images</label>
				<div class="clearFix"></div>
				<ul class="uploadImageInfo">
					
<?php
 if ($hasImages) : // create image gallery if images exist
	foreach ($images as $image) : 
?>
					<li class="imageInfo"	>
						<div class="button_group">
<!--	future development	<a class="cpyBtn_fullsize blue button" > Gallery</a> -->
							<a class="cpyBtn_fullsize blue button" > Display</a>
							<a class="cpyBtn_original blue button" > Download</a>
							<a class="image-del red button" rel="<?php echo $image['filename']; ?>">Delete</a>
							<div class="clearFix"></div>
						</div>	

							<div class="thumb_wrap">
								<a class='grouped_elements' rel='group1' href="<?php echo $_config['upload_url']; ?><?php echo $uploadFolder; ?>/fullsize/<?php echo $image['filename']; ?>">
									<img src="<?php echo $_config['upload_url']; ?><?php echo $uploadFolder; ?>/thumb/<?php echo $image['filename']; ?>" alt=''/>
								</a>
							</div>	

						<div class="clearFix"></div>
						<input type='text' class="imageName" data-type="image" data-id="<?php echo $image['id']; ?>" value="<?php echo $image['alt'];?>" />
						<p class="imagePath_fullsize">uploads/<?php echo $uploadFolder; ?>/fullsize/<?php echo $image['filename'];?></p>
						<p class="imagePath_original" >uploads/<?php echo $uploadFolder; ?>/<?php echo $image['filename'];?>?force_download</p>
					</li><!--end imageInfo -->
<?php	
	endforeach;
endif; 
?>				</ul><!--end uploadImageInfo -->
				<div class="clearFix"></div>
				
			<?php 
			$hasFiles = count($uploads) > 0;
			$hasFilesClass = $hasFiles ? ' hasFiles':'';
			?>
				<label class="tipRight<?php echo $hasFilesClass; ?>" title="Files available to provide a link for visitors to download a copy of the file." >Files</label>
				<div class="clearFix"></div>
				<ul class="uploadFileInfo">
					
<?php
 if ($hasFiles) : // create image gallery if images exist 
	foreach ($uploads as $upFile) :

?>	
					<li class="fileInfo"	>
						<div class="button_group">
							<a class="view_pdf blue button" > Display</a>
							<a class="cpyBtn_original blue button" > Download</a>
							<a class="file-del red button" rel="<?php echo $upFile['filename']; ?>">Delete </a>
							<div class="clearFix"></div>
						</div>

							<div class="thumb_wrap">
					<a  href='<?php echo $_config['upload_url']; ?><?php echo $uploadFolder; ?>/<?php echo $upFile['filename']; ?>' target="_BLANK">		
								<img src='<?php echo $_config['img_path']; ?><?php echo $upFile['type_pic']; ?>' alt=''/>
					</a>			
							</div>	

						<div class="clearFix"></div>
						<input type='text' class="imageName" data-type="file" data-id="<?php echo $upFile['id']; ?>" value="<?php echo $upFile['alt'];?>" />
						<p class="imagePath_fullsize"><?php echo  'uploads/'. $uploadFolder . '/' . $upFile['filename']; ?></p>
						<p class="imagePath_original" ><?php echo 'uploads/'. $uploadFolder . '/' . $upFile['filename'];?>?force_download</p>
					</li><!--end fileInfo -->
<?php	
	endforeach;
endif; 
?>				
				
				</ul><!--end uploadFileInfo --><div class="clearFix"></div>
			</div><!--end uploadInfoWrap -->
			
		</div><!--end image-wrap -->