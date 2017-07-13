<?php
class Single_image_upload {

    // options list
	public  $manage_title 		= "Blog Image";	// Area Heading
	public 	$manage_tip			= "Upload an image, the following are valid types: jpeg, gif, png"; //tooltip for above

	
	// passed by build buildUploadArea
	private $target_id			;	// the id of the element (page/item/whatever...) this single_upload is associated with
	
	
	// constructor parameters
	private $uploadPath			;	// string: absolute path to the uploads folder for this single_upload
	private $uploadUrl			;	// string: url path to the uploads folder for this single_upload
	private $image_table_name	;	// name of the image table
	private $target_id_name		;	// id of the item this is an image for
										//	image table requirements:
										//		$image_table_name(id,$target_id_name,posn,name,desc,alt,date)
										//		posn maintains sorted order, name is filename of image
										//		desc is image description, 
										//			alt is alternate text, date is date of upload
	private $target_image_name 	;   //  column name that the image name is in: eg `filename`
	private $target_image_alt	;	//  column name that the image's alt is in: eg `alt`
	public 	$file_name          ;	//  This is the name of the upload folder and the prefix for the upload_hanndler class extension
									//			eg $file_name = 'blog_post' :  
									//							uploads/blog_post 
									//							admin/includes/classes/blog_post_upload_handler.php
	private $default_image_path;	//  If blank, no default image is set
									//  If image exists at path, use it as the default, it needs to exist in three sizes
									//	This must be the path to a folder containing the upload file types associated with this image
									//		eg) mini thumb and fullsize
									//  path starts from the root
									//		eg) /admin/modules/blog/images/
	private $default_image_name;	//	This must be the name of the default image stored in the above folders
	private $default_image_alt;		//  This is the alt value for the default image
	
	public function __construct($uploadPath, $uploadUrl, $image_table_name = 'blog_post', $target_id_name = 'id', $target_image_name = 'image_name', $target_image_alt = 'image_alt', $file_name = "blog_post", $default_image_path = '', $default_image_name = '', $default_image_alt = '')
    {
		$this->file_name			= $file_name;
		$this->uploadPath			= $uploadPath;
		$this->uploadUrl			= $uploadUrl;
		$this->image_table_name 	= $image_table_name;
		$this->target_id_name		= $target_id_name;
		$this->target_image_name	= $target_image_name;
		$this->target_image_alt		= $target_image_alt;
		$this->obj_date				= new My_Date();
		$this->default_image_path 	= $default_image_path;
		$this->default_image_name 	= $default_image_name;
		$this->default_image_alt 	= $default_image_alt;
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
	public function headData($include_core = true)
	{
		global $_config;
		
		$tags =  "
			<link rel='stylesheet' type='text/css' href='{$_config['admin_url']}components/single_upload/style.css' />
			<script type='text/javascript' src='{$_config['admin_url']}js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js'></script>
			<link rel='stylesheet' href='{$_config['admin_url']}js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css' type='text/css' media='screen' />
			<script type=\"text/javascript\" src=\"".$_config['admin_url']."components/single_upload/js/single_upload_edit.js\"></script>
			";
		
		if($include_core)
		{
			$tags .= "
			<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.ui.widget.js\"></script>
			<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.iframe-transport.js\"></script>
			<script src=\"{$_config['admin_url']}js/jquery.fileupload/jquery.fileupload.js\"></script>
			";
		}

		return $tags;
	}
	
	public function buildUploadArea($target_id)
	{
		global $_config;
		// set target id and set image arrays
		$this->target_id = $target_id;
		$this->setCurImage();
		/*
		$this->curImage['image_name'] = '';
			$this->curImage['alt']
		*/
		if( (!$this->curImage['image_name'] || $this->curImage['image_name'] == $this->default_image_name) )
		{
			if(is_file($_config['rootpath'] . $this->default_image_path . 'thumb/' . $this->default_image_name))
			{
				$img_path = $_config['site_path'] . $this->default_image_path;
				$img_name = $this->default_image_name;
			}
			else
			{
				$img_path = false;
				$img_name = false;
			}
		}
		else
		{
			
			if( is_file($this->uploadPath . 'thumb/' . $this->curImage['image_name']) )
			{
				$img_path = $this->uploadUrl;
				$img_name = $this->curImage['image_name'];
			}
			else 
			{
				$img_path = false;
				$img_name = false;
			}
		}
		
		ob_start(); ?>
	<div id="single_uploadUploads">
		
		
			<div class="single_upload_left">
				<label class="tipRight" title="<?php echo $this->manage_tip; ?>" ><?php echo $this->manage_title; ?></label>
				<div class='input_inner'>
			<?php  if (using_ie()) : ?>
					<input id="single_upload_upload" type='file' name='files[]' style='width:180px;' data-url="../../js/jquery.fileupload/upload_handler.php">
			<?php else : ?>
					<div id="single_upload-fake-file-wrap" style='position:relative;'>
						<div id='single_upload-fake-file'>Choose New Image</div>
						<input type='file' name='files[]' id="single_upload_upload" style='position: relative;opacity:0; z-index:2;' data-url="../../js/jquery.fileupload/upload_handler.php" />
					</div><br />
			<?php endif; ?>
				</div>	
				<div id="single_upload-progress">
					<div class="bar" style="width: 0%;"></div>
				</div>
				<p id="single_upload-no-progress"></p>
				
				
				<div class='imageData'>
				<input type='hidden' class='imgAlt' value='<?php echo $this->curImage['alt']; ?>' />
				<input type='hidden' class='imgName' value='<?php echo $img_name; ?>' />
				<div class='image_wrap'>
					<span class='option_navs' >
						<a class='showImg' href='<?php echo "{$img_path}fullsize/{$img_name}"; ?>' ><img alt='View Page' src='../../images/view_icon.png'></a>
						<a class='edit_single_image' href='#' rel='<?php echo $this->target_id; ?>'><img src='../../images/edit.png' /></a>
<?php if ($img_name != $this->default_image_name) : ?>
						<a class='single_upload-del' href='#' rel='<?php echo $img_name; ?>'><img src='../../images/delete.png' ></a>
<?php endif; ?>						
					</span>

					<img src='<?php echo "{$img_path}thumb/{$img_name}"; ?>' alt='<?php echo $this->curImage['alt']; ?>'/>
				</div>
			</div>	
				
				
				
				
			</div>	
			<div class='message_wrap'>
				<span id='single_upload_msg' <?php echo isset($imageErr) ? "class='errorMsg'" : (isset($imageSuccess) ? "class='successMsg'" :''); ?>><?php echo isset($imageErr) ? $imageErr : (isset($imageSuccess) ? $imageSuccess : '' ); ?></span>
			</div>
				
			<div class='clearFix'></div>

			<input type="hidden" id="single_upload_target_id_name" value="<?php echo $this->target_id_name; ?>">
			<input type="hidden" id="single_upload_id" value="<?php echo $this->target_id; ?>">
			<input type="hidden" id="single_upload_file_name" value="<?php echo $this->file_name; ?>">
			<input type="hidden" id="single_upload_admin_url" value="<?php echo $_config['admin_url'] ?>" >
			<input type="hidden" id="single_upload_table_name" value="<?php echo $this->image_table_name ?>" >
			<input type="hidden" id="single_upload_target_image_alt" value="<?php echo $this->target_image_alt ?>" >
			<input type="hidden" id="single_upload_default_image_path" value="<?php echo rtrim($_config['site_path'], '/') . $this->default_image_path ?>" >
			<input type="hidden" id="single_upload_default_image_name" value="<?php echo $this->default_image_name ?>" >
			<input type="hidden" id="single_upload_default_image_alt" value="<?php echo $this->default_image_alt ?>" >
				

			

	
	
		<!-- Edit modal -->
		<div id="edit_single_image" class="jqmWindow dialog">
		   <h2>Edit Image Info</h2>
		   <form action="ajax.php" method="POST" id="add_note_form">
			  <input type="hidden" name="image_id" value="" id="image_id" />
			  <fieldset>
		<?php         // prepare all input fields
		if (! isset($message)) $message = array();
		$input_alt = new inputField( 'Alt Tag', 'alt' );	
		$input_alt->toolTip("Text that appears if image does not load.");

		$input_alt->counterWarning(65);
		$input_alt->counterMax(100);
		$input_alt->size('medium');
		$input_alt->arErr($message);
		$input_alt->createInputField();
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
	
	private function setCurImage()
	{
		$result = logged_query("SELECT `{$this->target_image_name}`, `{$this->target_image_alt}` FROM {$this->image_table_name} WHERE `{$this->target_id_name}`=:target_id",0,array(":target_id" => $this->target_id)
		);

		if(isset($result[0][$this->target_image_name]))
		{
			$this->curImage['image_name'] = $result[0][$this->target_image_name]; 
			$this->curImage['alt'] = $result[0][$this->target_image_alt] ? $result[0][$this->target_image_alt] : $this->default_image_alt;
		}
		else 
		{
			$this->curImage['image_name'] = '';
			$this->curImage['alt'] = $this->default_image_alt;
		}
	}
/*	
	private function buildImgHtml($imgData)	// build image divs from imgData( db data passed in)
	{
		global $_config;
		$url = $this->uploadUrl;
		foreach($imgData as $id => $data)
		{
		$name = $data['name']; //stored as an url friendly string
		$alt = htmlspecialchars($data['alt']);
	?>
		<li>
			<div id='imageData<?php echo $id; ?>' class='imageData'>
				<input type='hidden' class='imgAlt' value='<?php echo htmlspecialchars_decode($alt); ?>' />
				<input type='hidden' class='imgName' value='<?php echo htmlspecialchars_decode($name); ?>' />
				<div class='image_wrap'>
					<span class='option_navs' >
						<a class='showImg' href='<?php echo $url; ?>fullsize/<?php echo rawurlencode($name); ?>' ><img alt='View Page' src='../../images/view_icon.png'></a>
						<a class='edit_single_image' href='#' rel='<?php echo $id; ?>'><img src='../../images/edit.png' /></a>
						<a class='single_upload-del' href='#' rel='<?php echo $name; ?>'><img src='../../images/delete.png' /></a>
					</span>
					<img src='<?php echo $url; ?>thumb/<?php echo rawurlencode($name); ?>' alt='<?php echo $alt; ?>'/>
				</div>
			</div>	
		</li>
	<?php
		}
	}
*/	
}	
	