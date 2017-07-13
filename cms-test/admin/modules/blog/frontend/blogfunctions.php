<?php

function author_line( $result )
{	//get author
	global $blog_options;
	$authorarr = logged_query("SELECT `first_name`, `last_name` FROM `auth_users` WHERE `user_id` = '{$result['user_id']}'",0,array());
	$author = $authorarr[0]['first_name']." ".$authorarr[0]['last_name'];
	$author_line = $blog_options['show_author']==1 ? " by <strong>{$author}</strong> | " : "";
	return $author_line;
}
function cate_line( $result )
{	//get category titles
	global $_config;
	$cate = $result['cate'];
	$cate = str_replace('.',',',trim($cate,'.'));
	$titles = logged_query("SELECT `url` as `cate_url`,`title` FROM `blog_cat` WHERE `id` IN($cate)",0,array());
	$cate_line = "";
	foreach($titles as $k=>$v) {
		$thiscat = "<a href='{$_config['path']['blog']}category/{$v['cate_url']}' title='Category: {$v['title']}'>{$v['title']}</a>";
		
		if($k == 0) $cate_line = $thiscat; // first category
		else $cate_line = $cate_line.", ".$thiscat; // if more categories
	}
	return $cate_line;
}
function comments_line( $result )
{	//get number comments
	$commentids = logged_query("SELECT `id` FROM `blog_comments` WHERE `post_id` = '{$result['id']}'",0,array());
	$numcomments = count($commentids);
	$comments_line = "&nbsp;&nbsp;&nbsp;{$numcomments}<img src='admin/images/comments.png' style='vertical-align: middle;margin-left:.5em;' alt='comments' />";
	return $comments_line;
}
function date_box_line( $result )
{	// blog date
	global $blog_options;
	$date_box = ""; $date_line = "";
	if($blog_options['show_date']==1) {
		$date_box = "
			<div class='pubdate'>
				<div class='day'>".date('d',strtotime($result['date']))."</div>
				<div class='month'>".date('M',strtotime($result['date']))."<br />".date('Y',strtotime($result['date']))."</div>
			</div>";
		
		$date_line = date('F jS, Y', strtotime($result['date']))." | ";
	}
	return array($date_box, $date_line);
}
function blog_image( $result, $size ) // $size = 'thumb' || 'mini' || 'fullsize'
{	// blog image
	$blog_image = "";
	if($result['image_name']!=""){
		if($size=='mini') {
			$blog_image = "<img src='uploads/blog_post/mini/{$result['image_name']}' alt='{$result['image_alt']}' />";
		}
		elseif($size=='fullsize') {
			$blog_image = "<img src='uploads/blog_post/fullsize/{$result['image_name']}' alt='{$result['image_alt']}' class='blogpostimg' />";
		}
		elseif($size=='thumb') {
			$blog_image = "<img src='uploads/blog_post/thumb/{$result['image_name']}' alt='{$result['image_alt']}' class='blogpostimg-sidebarthumb' style='float:left;margin:0 .5em .5em 0;width:40px;height:40px;' />";
		}
	}
	elseif($size=='mini'){
		$blog_image = "<img src='admin/modules/blog/images/default_post_image/mini/blog_post_dflt.jpg' alt='default image' />";
	}
	return $blog_image;
}

?>