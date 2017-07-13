<?php //session_start(); 

$home_content = logged_query("SELECT * FROM news_items_home WHERE status = 1 LIMIT 1",0,array());
echo "<div>".display_content($home_content[0]['desc'])."</div>";

 $query1 = logged_query("SELECT * FROM `news_items` WHERE `status` = 1 ORDER BY `date` DESC",0,array()); 

 foreach($query1 as $answer1)
	{
    	$title = htmlspecialchars_decode($answer1['news_item_title']);
    	$contributor = htmlspecialchars_decode($answer1['contributor_name']);
    	$content = htmlspecialchars_decode($answer1['content']);
    	$caption = htmlspecialchars_decode($answer1['caption']);
		if ($answer1['news_item_image']){$news_item_image = $_config['upload_url'] . 'news_item/fullsize/' . htmlspecialchars_decode($answer1['news_item_image']);}
		else {$news_item_image = $_config['admin_url'] . "modules/news_items/images/news_itemimages/default_l.jpg";};
		?>
        
        <div id="<?php echo $answer1['url']; ?>" class="news_item_box" >
			<div class="image_box">
				<img src="<?php print $news_item_image; ?>" width="137" />
				<div style="clear:both; height:10px;\"></div>
				<?php print $caption; ?>
			</div> <!-- image_box -->
			<h2 class="news_item_title" id="<?php print $title; ?>"><?php print $title; ?></h2>
			<h3>Submitted by: <?php print $contributor; ?></h3>
			<p class = "news_itemToggler" rel="(Hide News Item)" >(Show News Item)</p>
			<div class="news_item_content" title="Click for Full News Item">  <!-- ########################################################### THIS AREA SHOULD EXPAND WHEN THE TITLE IS CLICKED -->
				<?php print $content; ?>
			</div><!-- news_item content -->
		</div> <!-- news_item_box -->
	<img src="<?php print $news_items_url;?>/images/line.png" class="news_item_divider">
    <?php
	} // answer1

	?>



