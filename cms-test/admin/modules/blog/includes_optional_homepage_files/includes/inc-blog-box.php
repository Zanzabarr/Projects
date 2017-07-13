<link rel="stylesheet" type="text/css" href="css/blogbox.css" />
<?php
function buildRecent($recentPosts)
{
	global $base_url, $_config;
?>			
			<div style="clear:both;height:2em;"></div>
			<div id="recent-posts-container">
				<h2 class="subpage ">RECENT POSTS</h2>
				<div class="clear-divider"><hr class="color-divider"></div>
				
                <ul class="blog-list">
			<?php // build the recent list
			foreach($recentPosts as $rec)
			{ 	
				//get author
				$authorarr = logged_query("SELECT `first_name`, `last_name` FROM `auth_users` WHERE `user_id` = '{$rec['user_id']}'",0,array());
				$author = $authorarr[0]['first_name']." ".$authorarr[0]['last_name'];
				//get number comments
				$commentids = logged_query("SELECT `id` FROM `blog_comments` WHERE `post_id` = '{$rec['id']}'",0,array());
				$numcomments = count($commentids);
			?>
				<li>
			<?php echo "<div class='pubdate'><div class='day'>".date('d',strtotime($rec['date']))."</div><div class='month'>".date('M',strtotime($rec['date']))."<br />".date('Y',strtotime($rec['date']))."</div></div>"; ?>
			<div class="titleline"><a  class="subpage" href="<?php echo $_config['path']['blog'] .$rec['url']; ?>"> <?php echo $rec['title']; ?> </a><div style="clear:both;height:1em;"></div><div class="byline clear">by <strong><?php echo $author; ?> | <?php echo $numcomments; ?></strong>&nbsp;&nbsp;<img src='admin/images/comments.png' /></div></div>
			<div class="sidebarIntro">
			<?php echo htmlspecialchars_decode($rec['intro']); ?>
			</div>
			</li>
				<?php
			}
			?>
			</ul>
            </div><!-- recent-posts-container -->
<?php
}
$recent_posts = logged_query("SELECT * FROM `blog_post` WHERE `status` = '1' ORDER BY `date` DESC LIMIT 3",0,array());
buildRecent($recent_posts);
?>