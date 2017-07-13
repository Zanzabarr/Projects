<?php
class Banners {

	public  $manage_title 		= "Manage Banner Images";	// Area Heading
	public 	$manage_tip			= "Add and remove images for the banner."; //tooltip for above
	public 	$staged_title		= "Single Banners";  // Staged image area name
	public	$banner_title		= "Rotating Banner";	// Display image area name
	public	$text_desc			= true;				// If true, desc field is available: text only
	public 	$html_desc			= true;			// If true, html_desc is available: tinyMCE
	private $image_table_name	;	// name of the image table
	private	$obj_date			;
	private $corralImgs			;	// array of images stored in the corral
	private $posnImgs			;	// array of images in the display area, sorted by posn
	private $uploadPath			;	// string: absolute path to the uploads folder for this banner
	private $uploadUrl			;	// string: url path to the uploads folder for this banner
	
	public function __construct($uploadPath, $uploadUrl, $image_table_name = 'banners', $text_desc = true, $html_desc = true )
    {
		$this->uploadPath		= $uploadPath;
		$this->uploadUrl		= $uploadUrl;
		$this->text_desc		= $text_desc;
		$this->html_desc		= $html_desc;
		$this->image_table_name = $image_table_name;
		$this->obj_date			= new My_Date();
		
		
		// if the uploadPath doesn't exist...build it
		//buildUploadPathIfEmpty($uploadPath);
	}
	
	// setOptions: sets private variables with new values
	//
	// @parms	$arOptions	array:	key/value pairs where...
	//							key:	the private variable to change
	//							value:	the new value
	//	
	public function setOptions($arOptions)
	{
		foreach($arOptions as $key => $value)
		{
			$this->{$key} = $value;
		}
	}
	// returns required string of required <link> and <script> tags to go in <head>
	public function headData()
	{
		global $_config;
		return "
			<link rel='stylesheet' type='text/css' href='{$_config['admin_url']}modules/banners/style.css' />
			<script type='text/javascript' src='{$_config['admin_url']}js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js'></script>
			<link rel='stylesheet' href='{$_config['admin_url']}js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css' type='text/css' media='screen' />
			
						
			<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.ui.widget.js\"></script>
			<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.iframe-transport.js\"></script>
			<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.fileupload.js\"></script>
		";
	}
	
	public function buildBannerArea()
	{
		// set image arrays
		$this->setImageArrays();
		
		
		global $_config;
		ob_start(); ?>
	<div id="bannerUploads">

		<h2 class="tiptip toggle" id="banner-toggle" title="<?php echo $this->manage_tip; ?>" ><?php echo $this->manage_title; ?></h2>
	
		<div id="banner-wrap">
			<div class="input_wrap">
				<label class="tipRight" title="Upload images, the following are valid type jpeg, gif, png." >Upload Image</label>
				<div class='input_inner'>
			<?php  if (using_ie()) : ?>
					<input id="banner_upload" type='file' name='files[]' style='width:180px;' data-url="../../js/jquery.fileupload/upload_handler.php" multiple>
			<?php else : ?>
					<div id="banner-fake-file-wrap" style='position:relative;'>
						<div id='banner-fake-file'>Click to Select a File</div>
						<input type='file' name='files[]' id="banner_upload" style='position: relative;opacity:0; z-index:2;' data-url="../../js/jquery.fileupload/upload_handler.php" multiple />
					</div><br />
			<?php endif; ?>
				</div>	
				<div id="banner-progress">
					<div class="bar" style="width: 0%;"></div>
				</div>
				<p id="banner-no-progress">(Click above or drag and drop files into box below)</p>
			</div>	
			<div class='message_wrap'>
				<span id='banner_msg' <?php echo isset($imageErr) ? "class='errorMsg'" : (isset($imageSuccess) ? "class='successMsg'" :''); ?>><?php echo isset($imageErr) ? $imageErr : (isset($imageSuccess) ? $imageSuccess : '' ); ?></span>
			</div>
			
			<input type="hidden" name="uploadPath" id="uploadPath" value="<?php echo $this->uploadPath; ?>" />
			<input type="hidden" name="uploadUrl" id="uploadUrl" value="<?php echo $this->uploadUrl; ?>" />
			<input type="hidden" id="banners_admin_url" value="<?php echo $_config['admin_url'] ?>" >
			
<?php 
/*			
			<input type="hidden" id="banner_id" value="<?php echo $this->target_id; ?>">
			<input type="hidden" id="banner_file_name" value="<?php echo $this->file_name; ?>">
		
			<input type="hidden" id="banner_table_name" value="<?php echo $this->image_table_name ?>" >

			<input type="hidden" id="banner_has_html_desc" value="<?php echo $this->html_desc; ?>" >
			<input type="hidden" id="banner_has_text_desc" value="<?php echo $this->text_desc; ?>" >
*/
?>				
			<div class='clearFix'></div>
			
			
			
			
			<div class="windowHeading tipTop" title="These images are available for single banner pages."><h2><?php echo $this->staged_title; ?></h2></div>
			<div class='scroll'>	
				<ul id="image_corral" class='connected'>
		
				<?php $this->buildImgHtml($this->corralImgs); ?>
				
				</ul> <!-- end image_corral -->
				<div class='clearFix' ></div>
			</div>
			<div class="windowHeading tipTop" title="These images will appear in the Rotating Banner."><h2><?php echo $this->banner_title; ?></h2></div>
			<div id='pen_group' class = 'scroll'>	
				<ul id="image_pen" class='connected'>
				
				<?php $this->buildImgHtml($this->posnImgs); ?>
				
				</ul> <!-- end image pen -->
				<div class='clearFix' ></div>
			</div>	
			
		</div><!-- end "banner-toggle-wrap" -->
	
	
		<!-- modal -->
		<div id="edit_image" class="jqmWindow dialog">
		   <h2>Edit Image Info</h2>
		   <form action="ajax.php" method="POST" id="add_note_form">
			  <input type="hidden" name="image_id" value="" id="image_id" />
			  <fieldset>
		<?php         // prepare all input fields
		// seo title
		if (! isset($message)) $message = array();
		$input_alt = new inputField( 'Alt Tag', 'alt' );	
		$input_alt->toolTip("Text that appears if image does not load.");

		$input_alt->counterWarning(65);
		$input_alt->counterMax(100);
		$input_alt->size('medium');
		$input_alt->arErr($message);
		$input_alt->createInputField();
		
        // link
        $input_link = new inputField( 'Link', 'link' );	
		$input_link->toolTip("a url the banner image links to<br>Either a full link (starts with http://)<br>Or a relative link (starts with the first word after the site name)<br>Leave blank if banner doesn't link to another page");

		$input_link->counterWarning(200);
		$input_link->counterMax(250);
		$input_link->size('medium');
		$input_link->arErr($message);
		$input_link->createInputField();
		
		// Hover Text
		if($this->text_desc)
		{
			$input_description = new inputField( 'Hover Text', 'desc' );	
			$input_description->toolTip('Text that appears the cursor hovers over the banner image.');
			$input_description->type('textarea');
			$input_description->counterWarning(150);
			$input_description->counterMax(250);
			$input_description->size('medium');
			$input_description->arErr($message); 
			$input_description->createInputField();
		}
		
		// add tinyMCE as well: only if config option is set
		?>
			<label class="tipRight" title="This information appears over the banner image">Long Description</label>
			<textarea class="mceEditor" id="html_desc" name="html_desc"></textarea>
		
			  </fieldset>
		   </form>
			<div class="dialog_buttons">
				<div class='clearFix' ></div>
				<a class="blue button dialog_btn" href="#" id="image_save_btn">Save</a>
				<a class="grey button dialog_cancel_btn close"  href="#">Cancel</a>
				<div class='clearFix' ></div>
			</div>	
		</div>
		<!-- end modal -->	

	</div>
		<?php ob_end_flush();
		
		
	}
	
	private function setImageArrays()
	{
		$this->corralImgs = logged_query_assoc_array("
			SELECT * FROM {$this->image_table_name} 
			WHERE posn ='0'",
			'id',0,array()
		);
		$this->posnImgs = logged_query_assoc_array("
			SELECT * FROM {$this->image_table_name}
			WHERE posn !='0' 
			ORDER BY posn",
			'id',0,array()
		);
	}
	
	private function buildImgHtml($imgData)	// build image divs from imgData( db data passed in)
	{
		global $_config;
		$url = $this->uploadUrl;
		foreach($imgData as $id => $data)
		{
		$desc = htmlspecialchars($data['desc']);
		$html_desc = htmlspecialchars($data['html_desc']);
		$name = htmlspecialchars($data['name']);
		$posn = htmlspecialchars($data['posn']);
		$alt = htmlspecialchars($data['alt']);
		$link = htmlspecialchars($data['link']);
		
	?>
		<li>
			<div id='imageData<?php echo $id; ?>' class='imageData'>
				<input type='hidden' class='imgPosn' value='<?php echo $posn; ?>' />
				<input type='hidden' class='imgAlt' value='<?php echo htmlspecialchars_decode($alt); ?>' />
				<input type='hidden' class='imgLink' value='<?php echo htmlspecialchars_decode($link); ?>' />
				<input type='hidden' class='imgDesc' value='<?php echo htmlspecialchars_decode($desc); ?>' />
				<input type='hidden' class='imgHtmlDesc' value='<?php echo htmlspecialchars_decode($html_desc); ?>' />
				<div class='image_wrap'>
					<span class='option_navs' >
						<a class='showImg' href='<?php echo $url; ?><?php echo $name; ?>' ><img alt='View Page' src='../../images/view_icon.png'></a>
						<a class='edit_image' href='#' rel='<?php echo $id; ?>'><img src='../../images/edit.png' /></a>
						<a class='banner-del' href='#' rel='<?php echo $name; ?>'><img src='../../images/delete.png' ></a>
					</span>
					<img src='<?php echo $url; ?>thumb/<?php echo $name; ?>' alt='<?php echo $alt; ?>'/>
				</div>
			</div>	
		</li>
	<?php
		}
	}
}