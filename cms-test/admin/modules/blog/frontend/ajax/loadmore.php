<?php
/* ajax pagination - "Load More Content" */
include("../../../../includes/config.php");
include("../../blog/functions.php");

if(isset($_POST['lastcount'])) {
// get recent posts
	$lastcount = $_POST['lastcount'];
	$blog_options = getBlogOptions();
	//$options = logged_query("SELECT `post_per_pg` FROM `blog_options` WHERE `id` = '1'",0,array());
	$ppp = $blog_options['post_per_pg'];
	if(!$_POST['datacat']) {
		$posts = logged_query("SELECT * FROM `blog_post` WHERE `status` = '1' ORDER BY `date` DESC LIMIT {$lastcount}, {$ppp}",0,array());
	} else {
		$getcateagain = logged_query("SELECT `id` FROM `blog_cat` WHERE `url` = '{$_POST['datacat']}' LIMIT 1",0,array());
		$posts = logged_query("SELECT * FROM `blog_post` WHERE `status` = '1' AND `cate` LIKE '%.{$getcateagain[0]['id']}.%' ORDER BY `date` DESC LIMIT {$lastcount},{$ppp}",0,array());
	}
	$total_posts = $posts ? count($posts) : 0;
	$newcount = ($lastcount + $ppp)<$total_posts ? $lastcount + $ppp : $total_posts;
	
	for($xi = 0; $xi < $newcount; $xi++) {
		$result = $posts[$xi];


		global $_config, $blog_options;

		include_once ($_config['admin_modules'] . 'blog/frontend/blogfunctions.php');

		$author_line = author_line( $result );

		$cate_line = cate_line( $result );
		$cate_line_nolink = "<div class='catelinenolink'>".strip_tags($cate_line)."</div>";
		$cate_line = (cate_line($result) !='') ? "in {$cate_line} | " : "";

		$comments_line = comments_line( $result );

		$date_box_line = date_box_line( $result );
		$date_box = $date_box_line[0];
		$date_line = $date_box_line[1];

		$blog_image = blog_image( $result, 'mini' ); // thumb || mini || fullsize
		
		echo "
		<div class='blogcontent blogcontent-homepage'>
			<div class='blogteaserimgblock'>
				{$blog_image}
			</div>
			<div class='blogteasercontent'>
				<div class='blogteaserforcedminheight'>
					<div class='posts_head'>
						{$date_box}
						<!-- {$cate_line_nolink}<br />-->
						<a class='postTitle' href='{$_config['path']['blog']}{$result['url']}' title='{$result['title']}'>{$result['title']}</a>
						<div class='blogteaserdata'>
							{$author_line} 
							{$date_line}
							{$cate_line}
							{$comments_line}
						</div>
					</div>
					<div class='blogteasertext'>
						".htmlspecialchars_decode($result['intro'])."
					</div>
				</div>
				<a class='morePost' href='{$_config['path']['blog']}{$result['url']}' title='Read Post'>Read More</a>
			</div>
			<div style='clear:both;'></div>
		</div>
		<hr class='grey-rule' />";
	}
}
?>