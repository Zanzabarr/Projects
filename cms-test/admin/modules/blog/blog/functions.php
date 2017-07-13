<?php
/*
// redirect to admin if user not permitted to post to blog
// currently, if admin = yes, have permission: more will follow later, update then.
function checkBlogPermission()
{
	global $_config;
	global $curUser;

	$location = $_config['admin_url'] . 'index.php';
	// 	we already know this is a valid session thanks to login tests in header.
	// 	confirm that this user has permission to be here: redirect if not
	if ( $curUser['admin'] != 'yes' ) // currently, test is a formality: eventually we will have different classes of admin and tests
	{
		header("Location: " . $location);
		exit;
	}
}
 */

 // get main data about the blog: from blog_home and blog_options tables
 // user_id is the id of the blog owner: currently 1, no multi-user support yet.
function getBlogOptions($user_id = 1)
{
	$options = logged_query_assoc_array("
		SELECT blog_options.user_id, post_per_pg, show_date, show_author, comments, approve, com_per_pg, title, url, `desc`, seo_title, seo_keywords, seo_description, status, date
		FROM blog_options, blog_home LIMIT 1
	",null,0,array());
	return $options[0];
}

function getPageData($admin)
{
		// get comments if they are enabled
	$comments = false;

	if($admin['comments'] == 1)
	{
		$comments = logged_query_assoc_array("SELECT * FROM `blog_comments` ORDER BY `time` desc",null,0,array());
		if (count($comments) == 0) $comments = false;
	}


	//create page array
	$pages = array();
	$tmppages=logged_query_assoc_array("SELECT * FROM `blog_post` ORDER BY `date` DESC",null,0,array());
	foreach ($tmppages as $page){
		// convert pages data to useable format
		$page['status_word'] = $page['status'] == 0 ? 'Draft' : 'Published';
		$page['arCat'] = categoryArray($page['cate']);
		// set default data
		$page['comments'] = array();
		$page['need_approve'] = false;

		// update the pages array
		$pages[$page['id']] = $page;
	}

	// add comments to the pages array
	if ($comments != false)
	{
		foreach($comments as $comment)
		{
			// if page exists, add to it
			if (isset($comment['post_id']) && array_key_exists($comment['post_id'], $pages))
			{
				$pages[$comment['post_id']]['comments'][] = $comment;
				// if approval is required, and it hasn't been approved: flag page as needing updating
				if ($admin['approve'] == '1' && $comment['approve'] != '1')
				{
					$pages[$comment['post_id']]['need_approve'] = true;
				}
				//else $pages[$comment['post_id']]['need_approve'] = false;
			}
		}
	}
	return $pages;
}

function buildCatRows($categories)
{
	global $_config;
	foreach($categories as $category)
	{
		$status = $category['status'] ? 'Published' : 'Draft';
?>
		<div class='menu_row'>
			<span class='cat_title'><?php echo $category['title']; ?></span>
			<span class="cat_status"><?php echo $status; ?></span>
			<span class="op_group">
				<a target='_blank' href='<?php echo $_config['site_path']; ?>blog/category/<?php echo $category['url'];?>' >
					<img class='tipTop' title='See this category live.' src="../../images/view_icon.png" alt="View">
				</a>
				<a href="<?php echo $_config['admin_url']; ?>modules/blog/blog_category.php?catid=<?php echo $category['id'];?>">
					<img class='tipTop' title='Edit this Category' src="../../images/edit.png" alt="Edit">
				</a>
				<a  href="#" class="deletecategory" rel="<?php echo $category['id']; ?>">
					<img class='tipTop' title='Permanently remove this category. <br /> note: related pages will not be lost.' src="../../images/delete.png" alt="Delete">
				</a>
				<div class='clearFix'></div>
			</span>

		</div>
		<div class='clearFix'></div>
<?php
	}
}
function buildBlogMenu($pages, $admin)
{
	$menu = "<div class ='blog_grp' >";
	foreach($pages as $row)
	{
		$menu .= "<div class ='blog_row' >"."\n";
		$menu .=	"<div class ='menu_row'>"."\n";
		$menu .= 		buildMenuRow($row,$admin)."\n";
		$menu .=	"</div>"."\n";

		$menu .= 	"<div class ='post_grp'>"."\n";
		$menu .= 	"<table><tr><td>";
		$menu .=		"<div class ='post_row'>"."\n";
//		$menu .= 			buildPost($row);
		$menu .=		"</div>"."\n";
		$menu .=		"<div class ='comment_row_grp'>"."\n";
		$menu .= 			buildComments($row, $admin)."\n";
		$menu .=		"</div>"."\n";
		$menu .= "</td></tr></table>";
		$menu .=	"</div>"."\n"; //end of post group

		$menu .= "</div>"."\n";// end of blog row
	}
	$menu .= "</div>";// end of blog grp
	return $menu;
}

function buildMenuRow($page, $admin)
{
	global $_config;
	// access user data from headerClass
	global $curUser;
	$isAdmin = $curUser['admin'] == 'yes';

	$menu ="<span class='row_title'>".$page['title']."</span>"."\n";
	$desc = writeCategory($page['arCat']);
	$menu .="<span class='row_cat' >".$desc."</span>"."\n";
	$menu .= "<span class='row_status'>".$page['status_word']."</span>"."\n";

	$menu .="<span class='op_group'>";

	// only the admin can make favourite
	if($isAdmin) :
		if($page['featured'] == 1){
			$menu .="
				<a class='changeFeatured' rel='{$page['id']}' href='#' >
					<img src='images/icon-star.png' class='tipTop' rel='0' title='Make Post No Longer Featured' alt='Not Featured'>
				</a>";
		} else {
			$menu .="
				<a class='changeFeatured' rel='{$page['id']}' href='#' >
					<img src='images/icon-darkstar.png' class='tipTop' rel='1' title='Make Post Featured' alt='Featured'>
				</a>";
			}
	else : // non admin view of featured
		if($page['featured'] == 1){
			$menu .= "<img src='images/icon-star.png' class='tipTop' title='Featured Post' alt='Featured'>";
		} else{
			$menu .= "<img src='images/icon-darkstar.png' class='tipTop' title='Not a Featured Post' alt='Not Featured'>";
		}
	endif;




	$menu .="
		<a target='_blank' href='{$_config['site_path']}blog/".$page['url']."'>
		<img class='tipTop' title='See it as it will appear live.' src=\"../../images/view_icon.png\" alt=\"Edit\">
		</a>
		<a href='blog_new.php?blogid=".$page['id']."'>
			<img class='tipTop' title='edit me' src=\"../../images/edit.png\" alt=\"Edit\">
		</a>
		<a  href='blog.php?delete=".$page['id']."' class=\"deleteblogpost\" rel=\"".$page['id']."\">
			<img src=\"../../images/delete.png\" alt=\"Delete\">
		</a>
	"."\n";
	//&& $admin['approve'] == '1' && count($page['comments']) > 0 )
	if($admin['comments'] == '1')
	{
		if (count($page['comments']) > 0 )
		{
			$capImg = ($page['need_approve'] && $admin['approve'] == '1') || $admin['approve'] !=1 ? 'caption.png' : 'caption-shade.png';

			$capTitle = $page['need_approve'] ? 'Some comments need to be approved.' : 'No new comments.';
			$capTitle = $admin['approve'] == '1' ? $capTitle:'View or delete comments.';
			$capClass = $page['need_approve'] ? 'need_approve' : 'need_approve';

			$menu .="
				<a class='{$capClass}' href='#'>
					<img src=\"../../images/{$capImg}\" title='{$capTitle}' class='tipTop ' alt=\"Edit\">
				</a>
			"."\n";
		}
		else $menu .="<span class='spacer'>&nbsp</span>";
	}
	$menu .="</span><div class='clearFix'></div>"."\n";// end class op_group
	return $menu;
}

function buildComments($page, $admin)
{
	$menu = '';
	foreach($page['comments'] as $incomment)
	{
		$needApprove = $admin['comments'] == '1' && $admin['approve'] == '1' && ! $incomment['approve'] ;
		$grpClass = $needApprove ? 'approve_grp' :'';
		// show commnt
		$menu .= 	"<div class='comment_grp {$grpClass}' >"."\n";
		$menu .= 		"<div class='title_line' >"."\n";
		$menu .=			"<span class='comment_name'>"."\n";
		$menu .= 'Author: '.$incomment['name'];
		$menu .=			"</span>";
		$menu .=			"<span class='comment_date'>"."\n";
		$menu .= 'Date: '.$incomment['time'];
		$menu .=			"</span>"."\n";
		$menu .= 			"<span class='comment_status'>Comment</span>"."\n";

		$menu .= 			"<span class='comment_btn_grp'>"."\n";
		// create buttons
		$menu .= 				"<a  href='#' class=\"delete_comment\" rel=\"".$incomment['id']."\">
									<img class='tipTop' title='Permanently delete this comment' src=\"../../images/delete.png\" alt=\"Delete\" />
								</a>
								"."\n";

		if( $needApprove )
		{

			$menu .="
				<a  href='#' class=\"approve_comment\" rel='{$incomment['id']}' >
					<img src='../../images/success.gif' title='Approve this comment for display in the blog' height='24px' width ='24px' class='tipTop ' alt=\"Approve\" />
				</a>

			"."\n";
		}
		$menu .=			"</span><div class='clearFix'></div>"."\n";// end of button span

		$menu .=		"</div>"."\n";
		$menu .= 		"<div class='comment_content' >{$incomment['comments']}</div>"."\n";

		$menu .=	"</div><div class='clearFix'></div>"."\n";


	}
	return $menu;
}

function categoryArray($catWord)
{
	$cleancate = explode(".", $catWord);
	$return = array();
	if ($catWord != '')
	{
		foreach ($cleancate as $cat)
		{
			$return[] = $cat;
		}
	}
	return $return;
}


function urlfriendly($mess)
{
	$x = ereg_replace("[^A-Za-z0-9_-]", "_", $mess);
	while(strpos($x, "__")){
		$clean = ereg_replace("__", "_", $x);
	}
	return $clean;

}

function errors()
{
	global $error;
	if(isset($error)){
		echo "<div class=\"error\">".$error."</div>";
	}
	if(isset($_GET['wrongfiletype'])){
		echo "<div class=\"error\">That file type is not supported</div>";
	}

	if(isset($_SESSION['SESS_MSG'])){
		echo "<div class=\"error\">".$_SESSION['SESS_MSG']."</div>";
		unset($_SESSION['SESS_MSG']);
	}

	if(isset($_SESSION['SESS_MSG_green'])){
		echo "<div class=\"success\">".$_SESSION['SESS_MSG_green']."</div>";
		unset($_SESSION['SESS_MSG_green']);
	}
}
?>
