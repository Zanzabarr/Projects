<?php
class displaygallery {

	/* required links
	** rotates gallery thumbs
	**	<script src='{$_config['admin_url']}modules/shopping_cart/frontend/js/cycle.js' type='text/javascript'></script>
	** displays fullsize popout
	**	<link rel='stylesheet' type='text/css' href='{$_config['admin_url']}js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css' />
	**	<script type='text/javascript' src='{$_config['admin_url']}js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js'></script>
	** custom gallery css and js
	**	<link rel='stylesheet' type='text/css' href='{$_config['admin_url']}css/displaygallery.css'>
	**	<script type='text/javascript' src='{$_config['admin_url']}js/displaygallery.js'></script>
	** will likely require additional styles in main css to override defaults if more than one kind of gallery exists in the site.
	**
	*/
	
	// required parameters
	private $images;	/* array of imagenames in this format:
						** array(
						**	'name'	=> 'example_image.jpg',		[Required]name of imagefile	
						**	'alt'	=> 'Alt Text',				[Optional]Alt text for images(defaults to imagename if not provided
						**	'desc'	=> 'Image Caption'			[Optional]Caption used if provided
						**	'href'	=> 'path to alternate page' [Optional]This should only be used if the thumb bar is being used
						**											independant of the gallery. If set, navigates to alternate
						**											pages instead of triggering the gallery pager
						**
						)
						*/
	private $imagePath;	// path to where those images are stored
	
	// optional parameters: values passed in options array as third parameter
	private	$fullsizeFolder		= 'fullsize';	// name of the folder storing fullsize images
	private	$displaySizeFolder 	= 'mini';		// name of the folder storing images for main display
	private	$thumbFolder 		= 'thumb';		// name of the folder storing thumbs for the navigator
	private $sliderFolder		= 'slider';
	private $placeHolder		= 'admin/modules/galleries/frontend/dynamic_placeholder.php'; 
												// location of the placeholder image
												//   by default, uses the dynamically generated placeholder
												//   to customize display, it can be passed to get values: width and height
												//   if only width is passed, original aspect ratio is used (recommended)
	private $cycleSpeed			= 0;			// speed in miliseconds that image cycles: if 0, doesn't cycle automatically											
	private $noExplodedCaption	= false;		// by default, 	exploded image has caption if included in imgArray
	private $noScrollArrows		= false;		//				main display has navigation arrows
	private $noExplodedImage	= false;		//				main display can be clicked to show fullsize image
	private $numThumbs			= 4;			// the number of thumbs in nav bar
	private $leftArrow			= '<img src="admin/modules/galleries/frontend/images/left-shadowed.png" alt="Left">';// the values to use as arrows (can replace with an image tag)
	private $rightArrow			= '<img src="admin/modules/galleries/frontend/images/right-shadowed.png" alt="Right">';		//		in the main display area	
	private $rightThumbArrow	= '<img src="admin/modules/galleries/frontend/images/right-shadowed-sm.png" alt="Right">';	// the values to use as arrows (can replace with an image tag)
	private $leftThumbArrow		= '<img src="admin/modules/galleries/frontend/images/left-shadowed-sm.png" alt="Left">';	//		in the thumb gallery display area
	private $galleryId			= "0";			// if an id is passed create an identifier class to associate nav/caption/gallery together.
												// must be given if multiple galleries are to appear on the page
	private $uniqueClass		= "GalleryID_";	// the gallery Id gets attached to this class. used to keep multiple galleries unique. 
	private	$insetCaption		= false;		// if true, display the caption as part of the image
	private $sliderActiveBorder	= true;		// if true, the active slide gets a special border (as defined in displaygallery.css)
	private $sliderUrl			= false;		// if true, use the url data associated with the image to create a link for that image
	private $sliderImageBorder	= true;			// if true, the slider images have a border (as defined in displaygallery.css)
	
	public function __construct($images, $imagePath, $arOptions = array())
    {
		$this->images 				= $images;
		
		$this->imagePath 			= $imagePath;
		foreach($arOptions as $option => $value)
		{
			if( isset($this->{$option}) ) $this->{$option} = $value;
			else my_log("Error constructing miniGallery. Invalid Constructor Option:{$option}\n");
		}
		
		$this->uniqueClass = $this->uniqueClass . $this->galleryId;
	}

	public function buildGallery()
	{ 
	?>
	<!--[if gte IE 9]>
	  <style type="text/css">
		.gradient {
		   filter: none;
		}
	  </style>
	<![endif]-->
	<div class="galleryWrap" >
		
		<?php if(! $this->noScrollArrows) : ?>
			<div class="galleryNav" >
				<a class="prev gradient" href="#"><span><?php echo $this->leftArrow; ?></span></a>
				<a class="next gradient" href="#"><span><?php echo $this->rightArrow; ?></span></a>
			</div>
			<?php endif; ?>
			<div class="respGallery<?php echo $this->noExplodedImage ? '' : ' explodable'; ?>">
				<img class='placeholder' src='<?php echo $this->placeHolder; ?>'>
				<input class='cycle_speed' type='hidden' value='<?php echo $this->cycleSpeed; ?>'>
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
				if ($tmpCount) $srcLine = "src='admin/images/gray.gif' data-cycle-image='{$this->imagePath}{$this->displaySizeFolder}/{$name}'";
				else $srcLine = "src='{$this->imagePath}{$this->displaySizeFolder}/{$name}'";
				$tmpCount++;

				?>
			<div class='slide'>	
				<a class="gallery_group" <?php echo $explodeHref ?> rel="group_<?php echo $this->galleryId; ?>" title="<?php echo $desc;?>" >
					<img <?php echo $srcLine; ?>"  alt="<?php echo $alt;?>" />
					<?php if (
						$this->insetCaption 
						&& (
							(isset($image['title']) && trim($image['title'])) 
							|| $image['desc'])
					) { ?>
					<div class="insetCaption">
						<?php if(isset($image['title']) && trim($image['title'])) echo "<h3>{$image['title']}</h3>" ?>
						<div><?php echo htmlspecialchars_decode($image['desc']); ?></div>
					</div>	
					<?php }	?>
				</a>
			</div>		
			<?php
		}
		?>
		</div> <!-- END gallery -->
	</div> <!-- END gallery wrap-->	
	<?php
	}	

	
	// outputs thumb navs with arrows and scrolling function
	public function buildNav()
	{
		global $_config;
		$imageCount = count($this->images);

	?>
	<div class="galleryNavWrap">
		<?php if ($imageCount > 1) : ?>
		<span class="gallery-previous">
			<a href="#"><?php echo $this->leftThumbArrow; ?></a>
		</span>
		<?php endif; ?>
			<div class="galleryNavInner">
			<div class="galleryNavOuter">
				<ul>
				<?php
				foreach($this->images as $image)
				{
						$name = htmlspecialchars($image['name'], ENT_QUOTES);
						// if image has href set, navigate to that page instead of triggering pager
						//   only works if gallery isn't present (used with thumb navs solo)
						$href = isset($image['href']) ? $image['href'] : '#';
						
						
						echo "<li><a href='{$href}'><img src='{$this->imagePath}{$this->thumbFolder}/{$name}' alt='Nav'></a></li>";
				}
				?>
				</ul>
			</div></div>
		<?php //gallery arrows only appear if there is more than one page of thumbs
		if ($imageCount > 1) : ?>
		<span class="gallery-next"><a href="#"><?php echo $this->rightThumbArrow; ?></a></span>
		<?php endif; ?>
	</div><!-- end galleryNavWrap -->
	<?php
	}
	
	// outputs thumb navs but without scrolling thumbs
	public function buildStackedNav()
	{
		global $_config;
		$imageCount = count($this->images);

	?>
	<div style="clear:both"></div>
	<div class="galleryNavWrap stackedNav">
				<ul>
				<?php
				foreach($this->images as $image)
				{
						$name = htmlspecialchars($image['name'], ENT_QUOTES);
						// if image has href set, navigate to that page instead of triggering pager
						//   only works if gallery isn't present (used with thumb navs solo)
						$href = isset($image['href']) ? $image['href'] : '#';
						
						
						echo "<li><a href='{$href}'><img src='{$this->imagePath}{$this->thumbFolder}/{$name}' alt='Nav'></a></li>";
				}
				?>
				</ul>
	</div><!-- end galleryNavWrap -->
	<?php
	}
	
	// outputs variable width navs with arrows and scrolling function
	public function buildSlider( $asNav = true )
	{
		global $_config;
		$imageCount = count($this->images);

	?>

	
	<div class="sliderWrap<?php 
		echo $this->sliderActiveBorder ? " sliderActiveBorder" : "";
		echo $this->sliderImageBorder ? " sliderImageBorder" : "";
		?>">
		
		
		
		<div class="sliderOuter">
			<img class="sliderPlaceholder" src="<?php echo $this->imagePath.$this->sliderFolder."/".$this->images[0]['name'];?>">
			<ul class="sliderInner">
			<?php
			foreach($this->images as $image)
			{
				$name = htmlspecialchars($image['name'], ENT_QUOTES);
				// if image has href set, navigate to that page instead of triggering pager
				//   only works if gallery isn't present (used with thumb navs solo)
				$anchor = $this->sliderUrl && isset($image['url']) && $image['url'] ? "<a href='" . $image['url'] . "'>" : '';
				$hasAnchorClass = $anchor ? ' class="hasAnchor"' : "";
				$closeAnchor = $anchor ? '</a>' : '';
				
				echo "<li{$hasAnchorClass}>{$anchor}<img src='{$this->imagePath}{$this->sliderFolder}/{$name}' alt='Nav'>{$closeAnchor}</li>";
				}
				?>
			</ul>		
		</div>
		
		
		<?php //gallery arrows only appear if there is more than one page of thumbs
		if ($imageCount > 1) : ?>
		<a class="slider-left" href="#"><?php echo $this->leftThumbArrow; ?></a>
		<a class="slider-right" href="#"><?php echo $this->rightThumbArrow; ?></a>
		<?php endif; ?>
		
	</div>


<?php	
	}

	
	// build the frame that the ecom module uses to hold captions for a small image
	public function buildCaption()
	{
	?>
	<ul class="captionWrap">
	<?php $cnt=0; foreach($this->images as $image)
	{
		$titleh3 = isset($image['title']) && $image['title'] ? "<h3>{$image['title']}</h3>" : '';
		echo "<li class='image_caption'>{$titleh3}<span>".htmlspecialchars_decode($image['desc'])."</span></li>";
	}
	?>
	</ul>
	<?php
	}
	
	// build the frame, and fill with data, that the gallery module uses.
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