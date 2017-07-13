
<!-- 
    VERY IMPORTANT! Update the form elements below ajaxUpload fields:
    1. form - the form to submit or the ID of a form (ex. this.form or standard_use)
    2. url_action - url to submit the form. like 'action' parameter of forms.
    3. id_element - element that will receive return of upload.
    4. html_show_loading - Text (or image) that will be show while loading
    5. html_error_http - Text (or image) that will be show if HTTP error.

    VARIABLE PASSED BY THE FORM:
    maximum allowed file size in bytes:
    maxSize		= 9999999999
    
    maximum image width in pixels:
    maxW			= 200
    
    maximum image height in pixels:
    maxH			= 300
    
    the full path to the image upload folder:
    fullPath		= http://www.atwebresults.com/php_ajax_image_upload/uploads/
    
    the relative path from scripts/ajaxupload.php -> uploads/ folder
    relPath		= ../uploads/
    
    The next 3 are for cunstom matte color of transparent images (gif,png), use RGB value
    colorR		= 255
    colorG		= 255
    colorB		= 255

    The form name of the file upload script
    filename		= filename
-->

<div class="image" style="margin-top:25px; vertical-align:bottom;">
<style type="text/css">
	#picture_seo{
		margin-top:220px;
	}
</style>
    
    <div id="upload_area">
    	<?php
			$revisePage = $_SERVER['SCRIPT_NAME']; //Pulls Page Info
	 		$revise = pathinfo($revisePage); //Puts page name and extension into an array
	  		$displayRevise = $revise['filename'].".".$revise['extension'];
			
			if ($displayRevise == "revise_blog.php") {
				$imageQuery=mysql_query("SELECT blog_thumbnail, blog_image FROM blog_revisions WHERE page_id='".$blogpost_id."'");
			} else {
			$imageQuery=mysql_query("SELECT blog_thumbnail, blog_image FROM blog_post WHERE id='".$blogpost_id."'");
			}
			$thumbExist=mysql_fetch_assoc($imageQuery);
			if ($thumbExist['blog_thumbnail'] == "") {
				echo "<img src=\"images/no_image.jpg\" />";
			} else {
				echo "<img src=\"../../../uploads/thumbnails/".$thumbExist['blog_thumbnail']."\" />";
			}
			if (!$thumbExist['blog_thumbnail'] == "") { 
				echo "<p><input id=\"imagepath\" type=\"text\" value=\"".$_config['uploads_path'], $thumbExist['blog_image']."\" /></p>";
			}
		?>
   		
    </div>
  		<?php if ($displayRevise == "edit_page.php" || $displayRevise == "blog_new.php") { ?>
        <form action="ajaxupload.php" method="post" name="standard_use" id="standard_use" enctype="multipart/form-data">
        
        <p><input id="fileinput" type="file" name="filename" /></p>
        <p><button onclick="ajaxUpload(this.form,'ajaxupload.php?from=blog&amp;filename=filename&amp;maxSize=9999999999&amp;maxW=200&amp;fullPath=<?php echo $_config['thumbnail_path']; ?>&amp;relPath=../../../uploads/thumbnails/&amp;colorR=255&amp;colorG=255&amp;colorB=255&amp;maxH=70&amp;page_id=<?php echo $blogpost_id; ?>','upload_area','File Uploading Please Wait...&lt;br /&gt;&lt;img src=\'../images/loader_light_blue.gif\' width=\'128\' height=\'15\' border=\'0\' /&gt;','&lt;img src=\'../images/error.gif\' width=\'16\' height=\'16\' border=\'0\' /&gt; Error in Upload, check settings and path info in source code.'); return false;">Upload Image</button>
    	
    Supported File Types: <b>gif, jpg, png</b></p>
    </form>
    <?php } ?>

</div>

