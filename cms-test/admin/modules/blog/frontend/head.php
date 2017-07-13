<?php 
$base_url = $_config['site_path'];
global $base_url;

// get categories array
$cats = logged_query("SELECT * FROM `blog_cat` WHERE `status` = '1' ORDER BY `title` ASC LIMIT 0 , 30",0,array());
$num_of_cats = $cats ? count($cats) : 0;

// get recent posts
$recent_posts = logged_query("SELECT * FROM `blog_post` WHERE `status` = '1' ORDER BY `date` DESC LIMIT 3",0,array());
$num_of_recent = $recent_posts ? count($recent_posts) : 0;

$fbmetas = "";	//metatag string for facebook share functionality

// get options array
$blog_options = getBlogOptions();
global $blog_options;

// cases:
// no extra parameters: display all blog entries
// one and only one extra parameter: a blog entry is selected 
// more than one extra parameter: second parm is 'category' & third parm is the category
//figure out the uri
$uri[1] = (isset($uri[1]) && $uri[1] == '' || ! isset($uri[1])) ? false : $uri[1];
$uri[2] = (isset($uri[2]) && $uri[2] == '' || ! isset($uri[2])) ? false : $uri[2];
$uri[3] = (isset($uri[3]) && $uri[3] == '' || ! isset($uri[3])) ? false : $uri[3];
$uri[4] = (isset($uri[4]) && $uri[4] == '' || ! isset($uri[4])) ? false : $uri[4];
$uri[5] = (isset($uri[5]) && $uri[5] == '' || ! isset($uri[5])) ? false : $uri[5];

$userPath = $_config['path']['blog'];
$index = 1;
// build userPath
while($uri[ $index ])
{
	$userPath .= '/'.$uri[$index];
	$index++;
}
// record last visited for returning to page
if( isset($_SESSION['blog']['userPath']) ) $_SESSION['blog']['lastPage'] = $_SESSION['blog']['userPath'];
$_SESSION['blog']['userPath'] = $userPath;

if ( isset($_SESSION['blog']['target']) && $_SESSION['blog']['userPath'] != $_SESSION['blog']['target'])
{
	unset($_SESSION['blog']['target']);
	if(isset($_SESSION['blog']['referrer'])) unset($_SESSION['blog']['referrer']);
	if(isset($_SESSION['blog']['referrerPart'])) unset($_SESSION['blog']['referrerPart']);
}


if ($uri[1] === false ||$uri[4] !== false) $arOutput = doBlog();
elseif ($uri[1] == 'about')
{
	if($uri[2] == 'blog' && $uri[3] === false) $arOutput = doAboutBlog();
	elseif ($uri[2] == 'category' && $uri[3] !== false ) $arOutput = doAboutCat($uri[3]);
	else $arOutput = doBlog();
}
elseif ($uri[1] == 'category' && $uri[2] !== false ) $arOutput = doCategory($uri[2]);
elseif ($uri[1] == 'date' && $uri[2] !== false ) $arOutput = doMonth($uri[2]);
elseif ($uri[1] == 'search') $arOutput = doSearch();
elseif ($uri[1] !== false && $uri[2] === false ) $arOutput = doPost($uri[1]);
else $arOutput = doBlog();

/** parts were here - no more **/

// sort data and perform redirects as needed
$seoData = $arOutput['seoData'];
// set seo data for use in the main head
$seot = $seoData['seo_title'];
$seod = $seoData['seo_description'];
$seok = $seoData['seo_keywords'];

// blog module includes
$latestblogcss = filectime($_config['rootpath'] . "admin/modules/{$module}/frontend/style.css");
$moduleLink = "
<link rel=\"stylesheet\" type=\"text/css\" href=\"{$_config['admin_url']}modules/{$module}/frontend/style.css?{$latestblogcss}\" />
<script type='text/javascript' src='{$_config['admin_url']}modules/{$module}/js/printThis.js'></script>
{$fbmetas}
";

//---------------------------------------------functions-----------------------------------------------------//

 // get main data about the blog: from blog_home and blog_options tables
 // user_id is the id of the blog owner: currently 1, no multi-user support yet.
function getBlogOptions($user_id = 1)
{
	$blog_options = logged_query("
		SELECT blog_options.user_id, post_per_pg, show_date, show_author, comments, approve, com_per_pg, title, url, `desc`, seo_title, seo_keywords, seo_description, status, date
		FROM blog_options, blog_home LIMIT 1",0,array());
	return isset($blog_options[0]) ? $blog_options[0] : false;
}

function buildDesc($desc, $editable = array())
{
	global $_config;
	$wrapper= array('open' => '', 'close' => '');
	// provide {{tagged_page}} insertion
	$count=0; //are there any substitutions?
	$desc = htmlspecialchars_decode($desc);
	$desc = preg_replace_callback('/{{(.*)}}/', create_function ('$taggedpg', 'return substitute_tags($taggedpg[1]);'), $desc, -1, $count);


	if(logged_in_as_admin() ) $wrapper = getContentWrapper($editable);

	// remove the wraps (if they were set) if inline editable declared as false
	if(isset($_SESSION['inline_editable']) && $_SESSION['inline_editable'] === false) $wrapper= array('open' => '', 'close' => '');

	$return = $wrapper['open'];

	$return .= $desc;
	$return .= $wrapper['close'];

	return $return;

}
function doPost($postUrl)
{
	$cleanUrl = trim($postUrl);
	global $blog_options, $_config, $base_url;
	
	// validate the post
	$result = getPost($postUrl);
	if ( $result === false ) //post doesn't exist, show blog instead
	{
		return doBlog();
	}
	
	$commentMsg = validateComment($result);
	
	// build the post
	$singlePost = buildPost($result);
	$seoData = array('seo_title' 		=> htmlspecialchars($result['seo_title']), 
					'seo_description' 	=> htmlspecialchars($result['seo_description']), 
					'seo_keywords' 		=> htmlspecialchars($result['seo_keywords'])
	);

	// build comments
	$whereClause = "WHERE `post` = '{$cleanUrl}' "; 
	if($blog_options['approve'] == '1' ) $whereClause .= ' AND `approve` = "1"'; 
	$whereClause .= ' ORDER BY `time` desc ';
	/* no page comment limit - new design */
	$commentPag = new paginationClass('blog_comments',$whereClause,3,$_config['path']['blog'].$cleanUrl,0,'buildComments',array(), array());
	
	// display blog description?
	$desc = false;
	$title = '';
	if ( $blog_options['status'] && ( ! isset($_POST['part']) || $_POST['part'] == 1   ))
	{
		$desc = buildDesc($blog_options['desc']) . '<div style="clear:both"></div>';
		$title = $blog_options['title'];
	}
	
	return array('commentMsg' => $commentMsg, 'postPag' => false , 'commentPag' => $commentPag, 'singlePost' => $singlePost, 'seoData' => $seoData, 'postData' => $result, 'prev' => array(), 'title' => $result['title'], 'blogDesc' => $desc);
}

function doBlog()
{
	global $blog_options, $_config, $base_url;
	
	$whereClause = "WHERE `status` = 1 ORDER BY `date` desc";
	$sql = "SELECT * FROM `blog_post` ".$whereClause;
	//$postPag = logged_query($sql, 0, array());
	$postPag = new paginationClass('blog_post',$whereClause,3,$_config['path']['blog'],$blog_options['post_per_pg'],'buildPosts',array($_POST), array());

	// get seo data from blog description
	$seoData = array('seo_title' 		=> htmlspecialchars($blog_options['seo_title']), 
					'seo_description' 	=> htmlspecialchars($blog_options['seo_description']), 
					'seo_keywords' 		=> htmlspecialchars($blog_options['seo_keywords'])
	);
	$_SESSION['blog']['userPath'] =  $_config['path']['blog'];

	//$about = $blog_options['status'] == 1 ? 'about/blog' : false;
	$commentPag = false; // don't show comments
	
	// display blog description?
	$desc = false;
	$title = '';
	if ( $blog_options['status'] && ( ! isset($_POST['part']) || $_POST['part'] == 1   ))
	{
		$desc = buildDesc($blog_options['desc']) . '<div style="clear:both"></div>';
		$title = $blog_options['title'];
	}
	
	return array('postPag' => $postPag , 'commentPag' => $commentPag, 'seoData' => $seoData, 'title' => $title, 'blogDesc' => $desc);
}

function doSearch() {
	global $blog_options, $_config;
	$tbl = "blog_post"; //table to search
	$searchQuery = ''; // search query is empty by default
	$searchCondition = "(seo_keywords LIKE '%%' OR content LIKE '%%')";

	$select = 'url, title, intro, image_name, image_alt';	//string of select fields for blog and events tables

	$field1 = 'seo_keywords';	//always searched first
	$field2 = '';	//will change depending on table being searched	
	
	$return = "<h1>Blog Search Result</h1>";
	if(isset($_POST['searchit']) && $_POST['searchit']!="") {
		$searchQ = trim($_POST['searchit']); // getting rid of unnecessary white space
		$searchQuery = $searchQ; // Prevent SQL Injection
		$searchTerms = explode(" ", $searchQuery); // Split the words
		$return .= "<p>Results from search for :&nbsp;&nbsp;&nbsp;&nbsp;<strong>{$_POST['searchit']}</strong></p>";
		$field2 = 'intro';
		$field3 = 'post';
		$searchCondition = "(`$field1` LIKE '%" . implode("%' OR `$field1` LIKE '%", $searchTerms) . "%' AND `id` >= 0)";
		$searchCondition .= " OR (`$field2` LIKE '%" . implode("%' OR `$field2` LIKE '%", $searchTerms) . "%' AND `id` >= 0)";
		$searchCondition .= " OR (`$field3` LIKE '%" . implode("%' OR `$field3` LIKE '%", $searchTerms) . "%' AND `id` >= 0)";
		$query = "SELECT $select FROM $tbl WHERE $searchCondition";
		
		$result = logged_query($query,0,array());
		
		if($result) {
			foreach($result as $row)
			{
				$return .= "<hr class='grey-rule'>";
				if($row['image_name']!="") $return .= "<img src='uploads/blog_post/thumb/{$row['image_name']}' alt='{$row['image_alt']}' class='blogpostimg-sidebarthumb' style='float:left;margin:0 .5em .5em 0;width:40px;height:40px;' />";
				$return .= "<a class='postTitle' href='blog/{$row['url']}'>{$row['title']}</a>
				<p>".htmlspecialchars_decode($row['intro'])."</p>";
			}
		} 
		else {
			$return .= "<h4>No results found.</h4>";
		}
		$return .= "<hr class='grey-rule'>";
		
		$seoData = array('seo_title' 		=> htmlspecialchars($blog_options['seo_title']), 
			'seo_description' 	=> htmlspecialchars($blog_options['seo_description']), 
			'seo_keywords' 		=> htmlspecialchars($blog_options['seo_keywords'])
		);
		
		return array('postPag' => false , 'commentPag' => false, 'page' => $return, 'seoData' => $seoData, 'title' => $blog_options['title']);
		
		
	} 
	else {
		if(isset($_POST['prevpage'])) {
			$return .= "<h3>No search term entered. Redirecting..</h3><script>location.href='{$_POST['prevpage']}';</script>";
		} else {
			$return .= "<h3>Returning to {$_config['company_name']} Home Page</h3><script>location.href='home';</script>";
		}
	}
}


function doMonth($month) { // archive pages
	global $blog_options, $_config;
	$year = date('Y');
	$monthname = date('F',strtotime($month."/01/".$year));
	$result = logged_query_assoc_array("SELECT * FROM `blog_post` WHERE `status` = 1 ORDER BY `date` DESC",null,0,array());
	$monthlist = array();
	
	foreach($result as $r) {
		if($month == date('m',strtotime($r['date']))) $monthlist[] = $r;
	}
	$seoData = array('seo_title' 		=> htmlspecialchars($blog_options['seo_title']), 
					'seo_description' 	=> htmlspecialchars($blog_options['seo_description']), 
					'seo_keywords' 		=> htmlspecialchars($blog_options['seo_keywords'])
	);
	
	$return = "
	<h1>Archive:&nbsp;&nbsp;{$monthname} {$year}</h1>
	<div class='clear-space'></div>";
	
	foreach($monthlist as $m) {
		
		$thisPost = htmlspecialchars_decode($m['intro']);
		
		include_once ($_config['admin_modules'] . 'blog/frontend/blogfunctions.php');
		
		$author_line = author_line( $m );
		
		$cate_line = cate_line( $m );
		$cate_line = (cate_line($m) !='') ? "in {$cate_line} | " : "";
		
		$comments_line = comments_line( $m );
		
		$date_box_line = date_box_line( $m );
		$date_box = $date_box_line[0];
		$date_line = $date_box_line[1];
		
		$blog_image = blog_image( $m, 'mini' ); // thumb || mini || fullsize
		
		$return .= "
		<div class='blogcontent'>
			<div class='blogteaserimgblock'>
				{$blog_image}
			</div>
			<div class='blogteasercontent'>
				<div class='blogteaserforcedminheight'>
					<div class='posts_head'>
						{$date_box}
						<a class='postTitle' href='{$_config['path']['blog']}{$m['url']}' title='{$m['title']}'>{$m['title']}</a>
						<div class='blogteaserdata'>
							{$author_line} 
							{$date_line}
							{$cate_line}
							{$comments_line}
						</div>
					</div>
					<div class='blogteasertext'>
						".htmlspecialchars_decode($m['intro'])."
					</div>
				</div>
				<a class='morePost' href='{$_config['path']['blog']}{$m['url']}' title='Read Post'>Read More</a>
			</div>
			<div style='clear:both;'></div>
		</div>
		<hr class='grey-rule' />";
	}

	if(empty($monthlist)) $return .= "<h3>There are no articles in this archive.</h3><a href='{$_config['path']['blog']}' title='Return to Blog'>Return</a>";
	
	return array('postPag' => false , 'commentPag' => false, 'page' => $return, 'seoData' => $seoData, 'title' => $blog_options['title']);
}

function doCategory($catUrl)
{

	$cleanUrl = $catUrl;
	global $blog_options, $_config;
	// find out if valid category, doBlog if not
	$result = logged_query_assoc_array("SELECT * FROM `blog_cat` WHERE `url` =  '{$cleanUrl}' AND `status` = 1 LIMIT 1",null,0,array());	
	// setup postPag
	if ( count($result) != 1 )
	{
		if (isset($_GET['part'])) unset($_GET['part']);
		return doBlog();
	}
	$result = $result[0];

	$whereClause = "WHERE `status` = 1 AND `cate` LIKE '%.{$result['id']}.%' ORDER BY `date` desc";
	$postPag = new paginationClass('blog_post',$whereClause,3,$_config['path']['blog'] . 'category/'.$cleanUrl,$blog_options['post_per_pg'],'buildPosts',array($_POST), array());

	$seoData = array('seo_title' 		=> htmlspecialchars($result['seo_title']), 
					'seo_description' 	=> htmlspecialchars($result['seo_description']), 
					'seo_keywords' 		=> htmlspecialchars($result['seo_keywords'])
	);
	$blogDesc = false;
	$title = '';
	if ( $blog_options['status'] && ( ! isset($_POST['part']) || $_POST['part'] == 1   ))
	{
		//$blogDesc = buildDesc($blog_options['desc']) . '<div style="clear:both"></div>';
		$title = $blog_options['title'];
	}
	return array('postPag' => $postPag , 'commentPag' => false, 'seoData' => $seoData, 'title' => $result['title'], 'cat' => $result, 'blogDesc' => $blogDesc);
}

function doAboutBlog()
{
	global $blog_options;
	
	if ($blog_options['status'] != 1) 
	{
		if (isset($_GET['part'])) unset($_GET['part']);
		return doBlog();
	}
	
	// get blog info and print if elligible
	$result = htmlspecialchars_decode($blog_options['desc']) . "<div style='clear:both'></div>";

	$seoData = array('seo_title' 		=> htmlspecialchars($blog_options['seo_title']), 
					'seo_description' 	=> htmlspecialchars($blog_options['seo_description']), 
					'seo_keywords' 		=> htmlspecialchars($blog_options['seo_keywords'])
	);	
	
	return array('postPag' => false , 'commentPag' => false, 'page' => $result, 'seoData' => $seoData, 'title' => $blog_options['title']);
}

function doAboutCat($catUrl)
{

	$cleanUrl = $catUrl;
	global $blog_options, $_config;
	// find out if valid category, doBlog if not
	$catData = logged_query_assoc_array("SELECT * FROM `blog_cat` WHERE `url` =  '{$cleanUrl}' AND `status` = 1 LIMIT 1",null,0,array());	
	
	if ( count($catData) != 1 ) 
	{
		if (isset($_GET['part'])) unset($_GET['part']);
		return doBlog();
	}
	
	$catData = $catData[0];
	// need to do more to pretty this up...later

	$result = buildDesc($catData['desc']);

	$seoData = array('seo_title' 		=> htmlspecialchars($catData['seo_title']), 
					'seo_description' 	=> htmlspecialchars($catData['seo_description']), 
					'seo_keywords' 		=> htmlspecialchars($catData['seo_keywords'])
	);
	
	return array('postPag' => false , 'commentPag' => false, 'page' => $result, 'seoData' => $seoData, 'title' => $catData['title']);
}

// get post by url
function getPost($postUrl)
{
	$postUrl = $postUrl;
	$post = logged_query_assoc_array("SELECT * FROM `blog_post` WHERE `status` = 1 AND `url` = '{$postUrl}' LIMIT 1",null,0,array());

	if (count($post) == 0 ) $post = false ;
	else $post = $post[0];

	return $post;
}

function buildPost( $result )
{
	global $base_url, $_config, $blog_options, $fbmetas;

	$editable = array(
		'editable_class' => 'inlineUploadable',
		'attributes' => array(
			'id' => 'blog1',
			'data-jump' => "admin/modules/blog/blog_new.php?blogid={$result['id']}",
			'data-jump-type' => 'back'
		),
		'secure_data' => array(
			'table' => 'blog_post',				// req for save
			'id-field' => 'id',				// req for save && upload
			'id-val' => $result['id'],	// req for save && upload
			'field' => 'post',			// req for save
			'upload-type' => 'blog_post'			// req for upload
		)
	);
	$thisPost = buildDesc($result['post'], $editable);
	
	include_once ($_config['admin_modules'] . 'blog/frontend/blogfunctions.php');
	
	$author_line = author_line( $result );
	
	$cate_line = cate_line( $result );
	$cate_line_nolink = strip_tags($cate_line);
	$cate_line = (cate_line($result) !='') ? "in {$cate_line} | " : "";
	
	$comments_line = comments_line( $result );
	
	$date_box_line = date_box_line( $result );
	$date_box = $date_box_line[0];
	$date_line = $date_box_line[1];
	
	$blog_image = blog_image( $result, 'fullsize' ); // thumb || mini || fullsize
	
	// SINGLE POST PAGE, EDITABLE TEXT
	return "
	<div class='blogcontent blogcontent-blogpost'>
		<div class='posts_head'>
			{$date_box}
			<h1>{$result['title']}</h1>
			<div class='blogpostdata'>
				{$author_line} 
				{$date_line}
				{$cate_line}
				{$comments_line}
			</div>
		</div>
		{$blog_image}
		<div class='blogposttext'>
			{$thisPost}
		</div>
	</div>
	<hr class='grey-rule' />";
}

function buildPosts( $result, $source )
{
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

function buildComments( $result )
{
	?>
		<div id="div_<?php echo $result['id']; ?>">  <!-- begin comment div -->

			<strong><?php echo $result['name']; ?></strong><br />
			<em><?php echo date("F jS, Y", strtotime($result['time'])); ?></em>

			<p class="commentpara"><?php echo nl2br($result['comments']);?></p>
			
		</div>	<!-- end comment div -->
	<?php
}

// returns error/success message
function validateComment($postData) 
{
	global $blog_options, $_config;
	
	if ($blog_options['comments'] == 1 && array_key_exists('submitComment', $_POST) ) 
	{
		$postUrl = $postData['url'];
		$postId = $postData['id'];
		$postTitle = $postData['title'];


		$commentname = trim($_POST['commentname']);
		$comment = trim($_POST['comment']);
		$commentemail = check_email_address(trim($_POST['commentemail']));
		$emailComment = htmlspecialchars($_POST['comment']);
		$emailName = htmlspecialchars($_POST['commentname']);
		
		if ( $commentname == '' || $comment == '' || !$commentemail) 
		{
			return '<span style="color:red;">All fields must be completed properly to leave a comment.</span>';
		}
		if($blog_options['approve']==1) {
			$approveVal = 0;
		} else {
			$approveVal = 1;
		}

		$savecomment = logged_query("INSERT INTO `blog_comments` (`post_id`, `post` ,`name` , `email`, `comments`, `approve`)	VALUES (:postId, :postUrl, :commentname, :commentemail, :comment, :approveVal);",0,array(
			":postId" => $postId,
			":postUrl" => $postUrl,
			":commentname" => $commentname,
			":commentemail" => $commentemail,
			":comment" => $comment,
			":approveVal" => $approveVal
		));
		if($savecomment !== false) {
			if($approveVal == 0) {
				$query = logged_query_assoc_array("SELECT email FROM auth_users LIMIT 1",null,0,array()); // currently only expects one result, takes first one.
				$email = $query[0]['email'];
				//-------------- mail
				$message = "A comment has been made on a <a href='{$_config['path']['blog']}{$postUrl}'>blog post</a>. <br /><a href='{$_config['site_path']}admin/'>Login to the CMS</a> and go to the Blog section to allow or deny this comment:<br/><br/>From: {$emailName} <br /> {$commentemail} <br/ > {$emailComment}";
				$to = $email;
				$subject = $_config['company_name'] . " Blog";
				$headers = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
				$headers .= "From: {$email}\r\n";
					
				mail($to,$subject,$message,$headers); // email is sent out
				return "<span style='color:red;'>Comment submitted for approval.</span>
				<script>$('html, body').animate({scrollTop: $('#comments').offset().top-30}, 2000);</script>";
			} else {
				return  "<span style='color:green;'>Comment Saved</span>
				<script>$('html, body').animate({scrollTop: $('#comments').offset().top-30}, 2000);</script>";
			}
		}
	}
	return ''; //No message
}

?>