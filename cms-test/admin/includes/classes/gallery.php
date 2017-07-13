<?php
class Gallery {
	public 	$file_name			= "gallery";
	public  $manage_title 		= "Manage Images";	// Area Heading
	public 	$manage_tip			= "Add and remove images available for this page."; //tooltip for above
	public 	$staged_title		= "Ready Images";  // Staged image area name
	public	$gallery_title		= "Display Images";	// Display image area name
	
	// optional fields in the edit image modal
	public	$text_desc			= true;				// If true, desc field is available: text only
	public 	$html_desc			= false;			// If true, html_desc is available: tinyMCE
	public 	$has_url			= false;			// If true, url field is available
													// Currently, only galleries module uses this field, 
													// but other gallery users may require in future
	public	$url_title			= "Slider Destination URL (Only works with Slider)";  // Title for the url field
	// Popup caption for the url
	public  $url_caption		= "Used with Slider only: clicking the Slider image will load the page of this url";
													
													
	private $target_id			;	// the id of the element (page/item/whatever...) this gallery is associated with
	private $image_table_name	;	// name of the image table
	private $target_id_name		;	// id of the item this is an image for
										//	image table requirements:
										//		$image_table_name(id,$target_id_name,posn,name,desc,alt,date)
										//		posn maintains sorted order, name is filename of image
										//		desc is image description, 
										//			alt is alternate text, date is date of upload
	private	$obj_date			;
	private $corralImgs			;	// array of images stored in the corral
	private $posnImgs			;	// array of images in the display area, sorted by posn
	private $uploadPath			;	// string: absolute path to the uploads folder for this gallery
	private $uploadUrl			;	// string: url path to the uploads folder for this gallery
	
	public function __construct($uploadPath, $uploadUrl, $image_table_name = 'gallery_image', $target_id_name = 'gallery_id', $text_desc = true, $html_desc = false, $file_name = "gallery" )
    {
		$this->file_name		= $file_name;
		$this->uploadPath		= $uploadPath;
		$this->uploadUrl		= $uploadUrl;
		$this->text_desc		= $text_desc;
		$this->html_desc		= $html_desc;
		$this->image_table_name = $image_table_name;
		$this->target_id_name	= $target_id_name;
		$this->obj_date			= new My_Date();
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
			<link rel='stylesheet' type='text/css' href='{$_config['admin_url']}components/gallery/style.css' />
			<script type='text/javascript' src='{$_config['admin_url']}js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js'></script>
			<link rel='stylesheet' href='{$_config['admin_url']}js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css' type='text/css' media='screen' />
			<script type=\"text/javascript\" src=\"".$_config['admin_url']."components/gallery/js/gallery_edit.js\"></script>
			
			<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.ui.widget.js\"></script>
			<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.iframe-transport.js\"></script>
			<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.fileupload.js\"></script>
		";
	}
	
	public function buildGalleryArea($target_id)
	{
		// set target id and set image arrays
		$this->target_id = $target_id;
		$this->setImageArrays();
		
		global $_config;
		ob_start(); ?>
	<div id="galleryUploads">
		
		<h2 class="tiptip toggle" id="gallery-toggle" title="<?php echo $this->manage_tip; ?>" ><?php echo $this->manage_title; ?></h2>
		
		<div id="gallery-toggle-wrap">
			<div class="gallery_left">
				<label class="tipRight" title="Upload images, the following are valid type jpeg, gif, png." >Upload Image</label>
				<div class='input_inner'>
			<?php  if (using_ie()) : ?>
					<input id="gallery_upload" type='file' name='files[]' style='width:180px;' data-url="../../js/jquery.fileupload/upload_handler.php" multiple>
			<?php else : ?>
					<div id="gallery-fake-file-wrap" style='position:relative;'>
						<div id='gallery-fake-file'>Click to Select a File</div>
						<input type='file' name='files[]' id="gallery_upload" style='position: relative;opacity:0; z-index:2;' data-url="../../js/jquery.fileupload/upload_handler.php" multiple />
					</div><br />
			<?php endif; ?>
				</div>	
				<div id="gallery-progress">
					<div class="bar" style="width: 0%;"></div>
				</div>
				<p id="gallery-no-progress">(Click above or drag and drop files into box below)</p>
			</div>	
			<div class='message_wrap'>
				<span id='gallery_msg' <?php echo isset($imageErr) ? "class='errorMsg'" : (isset($imageSuccess) ? "class='successMsg'" :''); ?>><?php echo isset($imageErr) ? $imageErr : (isset($imageSuccess) ? $imageSuccess : '' ); ?></span>
			</div>
				
			<div class='clearFix'></div>

			<input type="hidden" id="gallery_id" value="<?php echo $this->target_id; ?>">
			<input type="hidden" id="gallery_file_name" value="<?php echo $this->file_name; ?>">
			<input type="hidden" id="gallery_admin_url" value="<?php echo $_config['admin_url'] ?>" >
			<input type="hidden" id="gallery_table_name" value="<?php echo $this->image_table_name ?>" >

			<input type="hidden" id="gallery_has_html_desc" value="<?php echo $this->html_desc; ?>" >
			<input type="hidden" id="gallery_has_text_desc" value="<?php echo $this->text_desc; ?>" >
				
		
			<div class='clearFix' ></div> 
			<div class="windowHeading tipTop" title="These images are not in the gallery; drag them to the Gallery Images section."><h2><?php echo $this->staged_title; ?></h2></div>
			<div class='scroll'>	
				<ul id="image_corral" class='connected'>
		
				<?php $this->buildImgHtml($this->corralImgs); ?>
				
				</ul> <!-- end image_corral -->
				<div class='clearFix' ></div>
			</div>
			<div class="windowHeading tipTop" title="These images will appear in the Gallery."><h2><?php echo $this->gallery_title; ?></h2></div>
			<div id='pen_group' class = 'scroll'>	
				<ul id="image_pen" class='connected'>
				
				<?php $this->buildImgHtml($this->posnImgs); ?>
				
				</ul> <!-- end image pen -->
				<div class='clearFix' ></div>
			</div>	
			
		</div><!-- end "gallery-toggle-wrap" -->
	
	
		<!-- Edit modal -->
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


		// seo description
		if($this->text_desc)
		{
			$input_description = new inputField( 'Description', 'desc', 'gallery_text_desc' );	
			$input_description->toolTip('Describe the image.');
			$input_description->type('textarea');
			$input_description->counterWarning(150);
			$input_description->counterMax(250);
			$input_description->size('medium');
			$input_description->arErr($message); 
			$input_description->createInputField();
		}
		
		// add tinyMCE as well: only if config option is set
		if($this->html_desc)
		{ ?><label class="tipRight" title="This information appears in the individual item description">Long Description</label>
			<textarea class="mceEditor" id="gallery_html_desc" name="html_desc"></textarea>
		<?php
		}
		
		if($this->has_url)
		{
			$input_alt = new inputField( $this->url_title, 'url' );	
			$input_alt->toolTip($this->url_caption);
			$input_alt->counterWarning(240);
			$input_alt->counterMax(250);
			$input_alt->size('large');
			$input_alt->arErr($message);
			$input_alt->createInputField();
		}	
		?>
			
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
		$this->corralImgs = logged_query_assoc_array("SELECT * FROM {$this->image_table_name} 
WHERE {$this->target_id_name}=:target_id
  AND posn ='0'",'id',0,array(":target_id" => $this->target_id)
		);
		$this->posnImgs = logged_query_assoc_array("SELECT * FROM {$this->image_table_name}
WHERE {$this->target_id_name}=:target_id 
AND posn !='0' ORDER BY posn", 'id',0,array(":target_id" => $this->target_id)
		);
	}
	
	private function buildImgHtml($imgData)	// build image divs from imgData( db data passed in)
	{
		global $_config;
		$basepath = $this->uploadUrl;
		foreach($imgData as $id => $data)
		{
		$desc = htmlspecialchars($data['desc']);
		$html_desc = htmlspecialchars($data['html_desc']);
		$name = $data['name']; //stored as an url friendly string
		$alt = htmlspecialchars($data['alt']);
		$posn = htmlspecialchars($data['posn']);
		if($this->has_url) $url = htmlspecialchars($data['url']);
		
	?>
		<li>
			<div id='imageData<?php echo $id; ?>' class='imageData'>
				<input type='hidden' class='imgPosn' value='<?php echo $posn; ?>' />
				<input type='hidden' class='imgAlt' value='<?php echo htmlspecialchars_decode($alt); ?>' />
				<input type='hidden' class='imgDesc' value='<?php echo htmlspecialchars_decode($desc); ?>' />
				<input type='hidden' class='imgHtmlDesc' value='<?php echo htmlspecialchars_decode($html_desc); ?>' />
				<input type='hidden' class='imgName' value='<?php echo htmlspecialchars_decode($name); ?>' />
			<?php if($this->has_url) : ?>
				<input type='hidden' class='imgURL' value='<?php echo htmlspecialchars_decode($url); ?>' />
			<?php endif; ?>
				<div class='image_wrap'>
					<span class='option_navs' >
						<a class='showImg' href='<?php echo $basepath; ?>fullsize/<?php echo rawurlencode($name); ?>' ><img alt='View Page' src='../../images/view_icon.png'></a>
						<a class='edit_image' href='#' rel='<?php echo $id; ?>'><img src='../../images/edit.png' /></a>
						<a class='gallery-del' href='#' rel='<?php echo $name; ?>'><img src='../../images/delete.png' /></a>
					</span>
					<img src='<?php echo $basepath; ?>thumb/<?php echo rawurlencode($name); ?>' alt='<?php echo $alt; ?>'/>
				</div>
			</div>	
		</li>
	<?php
		}
	}
	
}	
	