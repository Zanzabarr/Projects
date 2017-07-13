<?php //session_start();


/* variables passed along from index.php (this is an include from index)
**	$_config	: contains all config data from admin/includes/config.php
**	$uri		: array of uri components: $uri[0] is the base (blog in this case)
**	$module = 'blog';
**	$pages		: pages object defined in admin/includes/functions.php - used to build menus and keep track of page info
*/

/* variables passed along from modules/blog/frontend/head.php (via index.php)
**	$cats			: all published categories
**	$num_of_cats	: total number of above
**  $recent_posts	: up to 5 most recent posts, regarldess of category
**  $num_of_recent	: total num of above
**	$blog_options		: (global) current user's blog options (data includes description of blog and choices
**	$base_url		: (global) the base site path
** 	$arOutput		: display data
*/
/* get total number of articles for ajax loading */
$posts = logged_query("SELECT * FROM `blog_post` WHERE `status` = '1'",0,array());
$total_posts = $posts ? count($posts) : 0;

$homePg = false;

function buildCatList($cats)
{ 
	global $base_url, $_config;

?>
	<?php if (count($cats) > 0) 
	{ ?>
		<div id="category-list-container">
			<h2 class="subpage ">CATEGORIES</h2>
			<div class="clear-divider"><hr class="color-divider"></div>
            <ul class="blog-list">
			
		<?php
		foreach($cats as $cat)
		{ 
			$checkcat = logged_query("SELECT `id` FROM `blog_post` WHERE `status` = 1 AND `cate` LIKE '%.{$cat['id']}.%'",0,array());
			if(!empty($checkcat)) {
				?>
				<li><span class='list-arrow' style="padding:5px 0;"><img src='admin/images/list-arrow.png' /></span><a class="subpage" href="<?php echo $_config['path']['blog'] .'category/'. $cat['url']; ?>"> <?php echo $cat['title']; ?> </a></li>
				<?php  
			}
		} ?>
        </ul>
		</div><!-- category-list-container -->
	<?php
	}
}

function buildArchives() {
	global $base_url, $_config, $posts;
	$curMonth = date('n');
	$curYear = date('Y');
	?>
	<div id="archives-container">
		<h2 class="subpage ">ARCHIVES</h2>
		<div class="clear-divider"><hr class="color-divider"></div>
        <ul class="blog-list">
		<?php
		for($x=1;$x<=$curMonth;$x++) {
			$monthyear = date('F Y',strtotime($x.'/1/'.$curYear));
			$month = date('m',strtotime($x.'/1/'.$curYear));
			$checkmonth = false;
			foreach($posts as $p) {
				if(date('m',strtotime($p['date'])) == $month) { 
					$checkmonth = true;
				}
			}
			if($checkmonth) {
				echo "<li><span class='list-arrow'><img src='admin/images/list-arrow.png' /></span><a href='{$_config['path']['blog']}date/{$month}' title=''>{$monthyear}</a></li>";
			}
		}
		?>
		</ul>
	</div><!-- end archives-container -->
	<?php
}

function buildRecent($recentPosts)
{
	?>			
		<div id="recent-posts-container">
			<h2 class="subpage ">RECENT POSTS</h2>
			<div class="clear-divider"><hr class="color-divider"></div>
			
			<ul class="blog-list">
				<?php // build the recent list
				foreach($recentPosts as $rec)
				{ 	

					global $_config, $blog_options;
					
					include_once ($_config['admin_modules'] . 'blog/frontend/blogfunctions.php');
					
					$author_line = author_line( $rec );
					if($blog_options['show_author']==1) $author_line = "<br />{$author_line}"; // sidebar
					
					$cate_line = cate_line( $rec );
					$cate_line_nolink = strip_tags($cate_line);
					$cate_line = (cate_line($rec) !='') ? "in {$cate_line} | " : "";
					
					$comments_line = comments_line( $rec );
					$comments_line = str_replace("&nbsp;", "", $comments_line);
					
					$date_box_line = date_box_line( $rec );
					$date_box = $date_box_line[0];
					$date_line = $date_box_line[1];
					$date_line = "<em class='dateBlog'>".str_replace(" | ", "", $date_line)."</em>"; // sidebar
					
					$blog_image = blog_image( $rec, 'thumb' ); // thumb || mini || fullsize
					?>
					<li>
				
						<div class="titleline">
							<?php echo "<a  class='subpage' href='{$_config['path']['blog']}{$rec['url']}'>{$rec['title']}</a>"; ?>
							
							<div class='byline clear'>
								<?php
								echo "
								{$blog_image}
								{$date_line}
								{$author_line}
								{$comments_line}";
								?>
							</div>
						</div>
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

//------------------ begin html ------------------

// this section has been entered from /index.php and this is the beginning of:
// <div id="content-area"><div id="content_area-inner">

// make the module path available to js
?>
<input type="hidden" id="module_path" value="<?php echo $_config['admin_url'] . 'modules/blog/'; ?>" />

<div id='content'>
	<div id='content-inner'>
	
		<?php
		// if this is a blog page, show it's description if enabled; but only if this is the first page of the blog
		if ( isset($arOutput['blogDesc']) 
				&& $arOutput['blogDesc'] 
				&& $arOutput['postPag'] != false  // this one so to not show intro on individual blog's page
				&& !uri::exists('category')  )  // not category
		{
			echo "<div id='blog-description'>{$arOutput['blogDesc']}</div>";
		}
		
		if( uri::exists('category') ) {
			echo "<div id='blog-description'><h1>{$arOutput['title']}</h1></div>";
		}
		?>
	
		<div id='blog-body'>
			
			<?php
			/* put title into html editor of description - client-editable and -styleable */
			//echo "<h1>{$arOutput['title']}</h1>" ;

			/* description moved above blog-body for full width */

			// display the appropriate page portions:
			if (isset($arOutput['singlePost'])) {
				echo $arOutput['singlePost'];
				?>
				<section id="socialbuttons" class="notMobileOnly">
					
					<?php /* FACEBOOK */ ?>
					<div class="fb-share-button" data-layout="button_count"></div>
					<script>
					$(document).ready( function() {
						$(".fb-share-button").attr("data-href", window.location.href);
					});
					</script>
					<script>(function(d, s, id) {
					  var js, fjs = d.getElementsByTagName(s)[0];
					  if (d.getElementById(id)) return;
					  js = d.createElement(s); js.id = id;
					  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0";
					  fjs.parentNode.insertBefore(js, fjs);
					}(document, 'script', 'facebook-jssdk'));</script>

					<?php //twitter
						$tweet = $arOutput['title']." at http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
					?>
					<a href="https://twitter.com/share" class="twitter-share-button" data-text="<?php echo $tweet; ?>" data-lang="en" data-size="medium" data-count="none" data-url="<?php echo rawurlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>">Tweet</a>
					<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>

					<?php //pinterest ?>
					<a href="//www.pinterest.com/pin/create/button/?url=<?php echo rawurlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&media=<?php echo rawurlencode('http://' . $_SERVER['HTTP_HOST'] . 'uploads/blog_post/mini/'. $arOutput['postData']['image_name']); ?>&description=Next%20stop%3A%20Pinterest" data-pin-do="buttonPin" data-pin-config="none"><img src="//assets.pinterest.com/images/pidgets/pinit_fg_en_rect_gray_20.png" /></a>
					<!-- Please call pinit.js only once per page -->
					<script type="text/javascript" async src="//assets.pinterest.com/js/pinit.js"></script>

					<button type='button' id='print-btn'><span class='print-text'>PRINT</span></button>

				</section>
				<?php
			}

			if ( isset($arOutput['page'] ) ) echo $arOutput['page'];
			if ( $arOutput['postPag'] != false ) 
			{
				$homePg = true;
				
				$arOutput['postPag']->showResults();
				//echo $arOutput['postPag']->uriPaginate();
				//print_r($arOutput['postPag']);
			}

			if ( $blog_options['comments'] > 0 && $arOutput['commentPag']!=false )
			{
				?>
					<h2>Comments</h2>
					<div class="clear-divider"><hr class="color-divider"></div>
					<div class="clear"></div>

					<div id="comments">
						
						<?php  
						// output the comments
						$post = $arOutput['postData'];
						$arOutput['commentPag']->showResults();

						// build the comment form
						?>
						
						<h2>Post a Reply</h2>
						<div class="clear-divider"><hr class="color-divider"></div>
						<div class="clear"></div>
						<div id="comment_area">
							
							<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" enctype="application/x-www-form-urlencoded" id="form-page" name="edititem">
								<span class='small' id='commentMsg'><?php echo isset($arOutput['commentMsg']) ? $arOutput['commentMsg'] : 'Write a Comment';?></span>
								<table id="tblComment"><tr><td><img src="<?php echo $_config['img_path']; ?>name.png" alt="Name" />
								<input id="commentname" name="commentname" type="text" title="Name" placeholder="Your Name" required /></td></tr>
								<tr><td><img src="<?php echo $_config['img_path']; ?>envelope-trans.png" alt="Email" />
								<input id="commentemail" name="commentemail" type="text" title="Email" placeholder="E-mail" required /></td></tr>

								
								<tr><td><div id="commentlabel"><img src="<?php echo $_config['img_path']; ?>letter.png" alt="Comment" /></div><div id="comment-container">
								<textarea id="comment" name="comment" placeholder="Message" required></textarea></div>
								</td></tr><tr><td>&nbsp;</td></tr><tr><td>
								<div class="commentBtn"></div><tr><td>&nbsp;</td></tr>
								<script>
									$('.commentBtn').html("<button id='submitComment' name='submitComment' type='submit' >Add Comment</button>");
								</script>
								</td></tr></table><div class='spacer'></div>
								<div style="clear: both; margin-bottom: 1.5em;"></div>
							</form>
						</div>
					</div>	
				<?php
			}
			?>
		</div><!-- end blog body -->
		<div style="clear: both;"></div>

		<?php
		// MORE ENTRIES BUTTON
		if ( $arOutput['postPag'] != false ) 
		{
			$datacat = false;
			if(isset($uri[1]) && $uri[1]=="category") {
				$datacat = $uri[2];
				$getcate = logged_query("SELECT `id` FROM `blog_cat` WHERE `url` = '{$datacat}' LIMIT 1",0,array());
				$catposts = logged_query("SELECT * FROM `blog_post` WHERE `status` = '1' AND `cate` LIKE '%.{$getcate[0]['id']}.%'",0,array());
				$total_posts = $catposts ? count($catposts) : 0;
			}
			else {
				//$datacat = count($arOutput['postPag']);
				//$total_posts = $datacat;
				// *** $total_posts is already set on top of page ***
			}
			?>
			
			<button class="morePost load_more" id="load_more_button" data-cat="<?php echo $datacat; ?>" data-count="<?php echo $blog_options['post_per_pg']; ?>">LOAD MORE ENTRIES</button>
			<div class="loading_image" style="display:none;"><img src="<?php echo $_config['img_path']; ?>ajax-loader.gif" alt="Loading..."></div>
			
			<?php 
		}
		?>
		
		<div style="clear:both;"></div>
	
	</div><!-- end content-inner -->
</div><!-- end content -->

<div id="sidebar" class="blog">
	
	
	<form name="frmSearch" id="frmSearch" action="blog/search" method="post">
		<input type="hidden" name="prevpage" value="<?php echo basename($_SERVER['REQUEST_URI']); ?>" />
		<input type="text" name="searchit" placeholder="Search" /><input type="submit" id="searchsubmit" name="searchsubmit" value="" />
		<div style="clear:both;"></div>
	</form>
	<div class="clear-space"></div>
	
	<?php
	// blog homepage exclusion
	if(isset($uri[1]) && $uri[1] != ""){
		// build most recent list
		if ($num_of_recent > 0) buildRecent($recent_posts);
	}
	
	// if there is more than one category, build a category list
	if ($num_of_cats > 0) buildCatList($cats);
	else buildCatList(array());
	
	// build archives list - search by month
	buildArchives();
	
	echo '<div class="spacer"></div>';
	
	?>
	<div class="clear"></div>
</div> <!--end sidebar -->

<div class="clear"></div>


<script>
$(document).ready( function() {

	
	var modulePath = $('#module_path').val();
	var total_posts = parseFloat(<?php echo $total_posts; ?>);
	var ppp = parseFloat(<?php echo $blog_options['post_per_pg']; ?>);
	var datacat = "<?php echo isset($datacat) ? $datacat : false; ?>";
	
	if($('.blogcontent').length >= total_posts) {
		$('#load_more_button').attr('data-count',total_posts);
		$("#load_more_button").text('TOP OF PAGE').addClass('TopPage');
	}
	
	function replaceRule() {
		/* replace clear-space with horizontal rule */
		$('.clear-space.rule-replace').replaceWith("<hr class='color-rule' />");
		/* replace last horizontal rule with clear-space div */
		var lastrule = $('#blog-body > .blogcontent:last > hr:last');
		lastrule.replaceWith("<div class='clear-space rule-replace'></div>");
	}
	
	$('#load_more_button').live('click', function() {
		if($(this).hasClass('TopPage')) {
			$("html, body").animate({scrollTop: $("#middle").offset().top}, 500);
		} else {
			$(this).hide(); 
			$('.loading_image').show(); //show loading image
			var lastcount = parseFloat($(this).attr('data-count'));
			
			if(lastcount < total_posts) {
				//post page number and load returned data into result element
				$('#newpage').remove();
				$("#blog-body").append("<div id='newpage'></div>");
				$.post(modulePath + 'frontend/ajax/loadmore.php',{'lastcount': lastcount,'datacat': datacat}, function(data) {
			   
					$("#load_more_button").show(); //bring back load more button
					$("#blog-body").append(data); //append data received from server
					
					//scroll page
					$("html, body").animate({scrollTop: $("#newpage").offset().top - 50}, 500);
				   
					//hide loading image
					$('.loading_image').hide();
					if((lastcount + ppp) >= total_posts) {
						$('#load_more_button').attr('data-count',total_posts);
						$("#load_more_button").text('TOP OF PAGE').addClass('TopPage');
					} else {
						$('#load_more_button').attr('data-count',lastcount + ppp);
					}
					replaceRule();
				});
			}
		}
	});

	$('#print-btn').live('click', function() {
		if($('.blogcontent').length) {
			var title = $('title').html();
			$(".blogcontent").printThis({
				debug: false,              //show the iframe for debugging
				importCSS: true,           //import page CSS
				printContainer: false,      //grab outer container as well as the contents of the selector
				loadCSS: "<?php echo $_config['admin_url'];?>modules/<?php echo $module;?>/frontend/print.css", 				//path to additional css file
				pageTitle: title,             //add title to print page
				addTitle: false,			//prepend title to printed section ** added by BEH **
				removeInline: false,       //remove all inline styles from print elements
				printDelay: 333,           //variable print delay S. Vance
				header: null               //prefix to html
			});
		}
	});
});
</script>