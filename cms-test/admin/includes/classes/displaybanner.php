<?php
class displayBanner {

	/* required links
	** rotates banners
	**	<script src='{$_config['admin_url']}js/cycle.js' type='text/javascript'></script>
	** custom css and js
	**	<link rel='stylesheet' type='text/css' href='{$_config['admin_url']}css/displaybanner.css'>
	**	<script type='text/javascript' src='{$_config['admin_url']}js/displaybanner.js'></script>
	** will likely require additional styles in main css to override defaults if more than one kind of banner exists in the site.
	**
	*/
	
	// required parameters
	private $images;	/* array of imagenames in this format:
						** array(
						**	'name'	=> 'example_image.jpg',		[Required]name of imagefile	
						**	'alt'	=> 'Alt Text',				[Optional]Alt text for images(defaults to imagename if not provided
						**	'desc'	=> 'Image Caption'			[Optional]Caption used if provided
						**	'html_desc'	=>	'Caption with html'	[Optional]Caption with html content if text required in banner
						**	'link'	=> 'path to alternate page' [Optional] path for banner click navigation
						)
						*/
	private $imagePath;	// path to where those images are stored
	
	// optional parameters: values passed in options array as third parameter
	private	$fullsizeFolder		= 'fullsize';	// name of the folder storing fullsize images
	private	$displaySizeFolder 	= 'mini';		// name of the folder storing mini images
	private	$thumbFolder 		= 'thumb';		// name of the folder storing thumbs
	private $placeHolder		= 'admin/images/resp_banner_placeholder.gif';
												// location of the placeholder image
												// 		placeholder's width and height sets the largest image size allowed and
												//		forms the borders of the banner
	private $cycleSpeed			= 0;			// speed in miliseconds that image cycles: if 0, doesn't cycle automatically											
	private $noExplodedCaption	= false;		// by default, 	exploded image has caption if included in imgArray
	private $noScrollArrows		= false;		//				main display has navigation arrows
	private $noExplodedImage	= false;		//				main display can be clicked to show fullsize image
	private $numThumbs			= 3;			// the number of banners in nav bar
	private $leftArrow			= "&larr;";		// the values to use as arrows (can replace with an image tag)
	private $rightArrow			= "&rarr;";		//		in the main display area	
	private $rightThumbArrow	= "&raquo;";	// the values to use as arrows (can replace with an image tag)
	private $leftThumbArrow		= "&laquo;";	//		in the thumb banner display area
	private $bannerId			= "0";			// if an id is passed create an identifier class to associate nav/caption/banner together.
												// must be given if multiple galleries are to appear on the page
	private $uniqueClass		= "BannerID_";	// the banner Id gets attached to this class. used to keep multiple galleries unique. 
	private	$insetCaption		= false;		// if true, display the caption as part of the image
	
	public function __construct($images, $imagePath, $arOptions = array())
    {
		$this->images 				= $images;
		$this->imagePath 			= $imagePath;
		foreach($arOptions as $option => $value)
		{
			if( isset($this->{$option}) ) $this->{$option} = $value;
			else my_log("Error constructing miniBanner. Invalid Constructor Option:{$option}\n");
		}
		
		$this->uniqueClass = $this->uniqueClass . $this->bannerId;
	}

	
	public function buildSingleImageBanner()
	{
		$image = $this->images[0];
		
		$name = htmlspecialchars($image['name'], ENT_QUOTES);
		// if image has alt text, use it. Otherwise use the name for alt
		$alt = isset($image['alt']) && trim($image['alt']) != '' ? htmlspecialchars($image['alt'], ENT_QUOTES) : $name;
		// if a description is provided, show it.
		$desc = ! $this->noExplodedCaption 
			&& isset($image['desc']) 
			&& trim($image['desc']) != '' 
			? htmlspecialchars($image['desc'], ENT_QUOTES) : '';
		$explodeHref = (! $this->noExplodedImage) ? "href='{$this->imagePath}{$this->fullsizeFolder}/{$name}'" : '';

		$srcLine = "src='{$this->imagePath}{$this->displaySizeFolder}/{$name}'";

	?>
	<div class="bannerWrap">
		<div class="singleBanner">
			<a class="banner_group" <?php echo $explodeHref ?> title="<?php echo $desc;?>" ><img <?php echo $srcLine; ?>  alt="<?php echo $alt;?>"/><?php if (
				$this->insetCaption 
				&& ($image['desc'])
				) { ?>	<div class="insetCaption">
							<div><?php echo htmlspecialchars_decode($image['desc']); ?></div>
						</div>	
			<?php }	?>
			</a>
		</div>
	</div><!-- end bannerWrap -->
	<?php
	}
	
	public function fullSlideBanner()
	{	
	if(! $this->noScrollArrows) : ?>
	  <div id='left_scroll'><img src='admin/images/l_arrow.png' /></div>
	<?php endif; ?>
		<div id='carousel_inner'>
		<input class='cycle_speed' type='hidden' value='<?php echo $this->cycleSpeed; ?>'>
			<ul id='carousel_ul'>
	<?php
			$tmpCount=0;
			foreach ($this->images as $image)
			{	
				$name = htmlspecialchars($image['name'], ENT_QUOTES);
				// if image has alt text, use it. Otherwise use the name for alt
				$alt = isset($image['alt']) && trim($image['alt']) != '' ? htmlspecialchars($image['alt'], ENT_QUOTES) : $name;
				// if a description is provided, show it.
				$desc = ! $this->noExplodedCaption 
					&& isset($image['desc']) 
					&& trim($image['desc']) != '' 
					? htmlspecialchars($image['desc'], ENT_QUOTES) : '';
				$explodeHref = (! $this->noExplodedImage) ? "href='{$this->imagePath}{$this->fullsizeFolder}/{$name}'" : '';

				// if this isn't the first image, set up lazyloading, if it is, just present the image
				$srcLine = "src='{$this->imagePath}{$this->fullsizeFolder}/{$name}'";
				$tmpCount++;
				?>
					<li><a class="banner_group" <?php echo $explodeHref ?> title="<?php echo $desc;?>" ><img <?php echo $srcLine; ?>  alt="<?php echo $alt; ?>"/>
				<?php /* if ($this->insetCaption && $image['desc']) { ?>
						<div class="insetCaption">
						<div><?php echo htmlspecialchars_decode($image['desc']); ?></div>
						</div>
				*/ ?>
					</a></li>
				<?php
			} ?>
			</ul>
		</div>
	<?php if(! $this->noScrollArrows) : ?>
	  <div id='right_scroll'><img src='admin/images/r_arrow.png' /></div>
	<?php endif;
	}
	
	// build the frame that the ecom module uses to hold captions for a small image
	public function buildCaption()
	{
	?>
	<ul class="captionWrap">
	<?php $cnt=0; foreach($this->images as $image)
	{
		echo "<li class='image_caption'><span>".htmlspecialchars_decode($image['desc'])."</span></li>";
	}
	?>
	</ul>
	<?php
	}
	
	// build the frame, and fill with data, that the banner module uses.
	public function buildHTMLCaption()
	{
	?>
	<ul class="HTMLcaptionWrap">
	<?php $cnt=0;  foreach($this->images as $image)
	{
		echo "<li class='html-caption'>".htmlspecialchars_decode($image['html_desc'])."</li>";
	}
	?>
	</ul>
	<?php
	}
}