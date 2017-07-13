<?php 
$home_page = $pages->get_page_by_slug('home');
$content = $home_page['content'];

$home2 = $pages->get_page_by_slug('home_part_2');
$content2 = $home2['content'];
// this page contains editable fields...create the tinySalt for encryption

$editable = array(
	'editable_class' => 'inlineUploadable',
	'attributes' => array(
		'id' => 'home1',
		'data-jump' => 'admin/edit_page.php?page_id='.$home_page['id'].'#content',
		'data-jump-type' => 'back'
	),
	'secure_data' => array(
		'table' => 'pages',				// req for save
		'id-field' => 'id',				// req for save && upload
		'id-val' => $home_page['id'],	// req for save && upload
		'field' => 'content',			// req for save
		'upload-type' => 'dflt'			// req for upload
	)
);
$editable2 = array(
	'editable_class' => 'inlineUploadable',
	'attributes' => array(
		'id' => 'home2',
		'data-jump' => 'admin/edit_page.php?page_id='.$home2['id'].'#content',
		'data-jump-type' => 'back'
	),
	'secure_data' => array(
		'table' => 'pages',				// req for save
		'id-field' => 'id',				// req for save && upload
		'id-val' => $home2['id'],	// req for save && upload
		'field' => 'content',			// req for save
		'upload-type' => 'dflt'			// req for upload
	)
);
?>
<!-- ************* START OF CONTENT AREA ********************* -->

<div id="content_area" class="wrap">


	<div style="float:left;  margin-left:3%; margin-right:3%; width:94%">
		
		<?php display_content($content, $editable); 
		?>
		<div style="clear:both"></div>
		<div class="boxesWrap">
		
		<?php 
			$favs = blog_functions::get_favourites(4,true);
			$favcount = 0;
			if( is_array($favs) && count($favs) ) : foreach($favs as $fav) : 
			if($fav['image_name'] && $image_path = blog_functions::get_image_path($fav['image_name'], 'mini') )
			{
				$img = "<img src='{$image_path}' alt='{$fav['image_alt']}'>";
			}
			else $img = $fav['image_alt'];
			$favClass = $favcount++ == 3 ? "homePageBoxesR" : "homePageBoxes";
			?>
			<div class="<?php echo $favClass; ?>"><a href="blog/<?php echo $fav['url']; ?>"><?php echo $img; ?></a><br /><h5><a href="blog/<?php echo $fav['url']; ?>"><?php echo  $fav['image_alt']; ?></a></h5><?php echo  html_entity_decode($fav['intro']); ?><div class="boxButtons"><a href="blog/<?php echo $fav['url']; ?>">See More</a></div>
			</div>
		
		<?php endforeach;endif; ?>
		</div>
		<div style="clear:both"></div>
		<?php
			//include "{$_config['rootpath']}includes/inc-blog-box.php";
			
			if($content2)	display_content($content2, $editable2);	
		?>

    </div><!-- content -->

	<div style="clear:both"></div>
</div>	<!-- content_area -->


<!-- ************* END OF CONTENT AREA ********************* -->