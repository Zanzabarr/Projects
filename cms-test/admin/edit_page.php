<?php 
//------------------------ asynchronous actions----------------------------------------------

// delete the page then go to Pages front page
if ( array_key_exists('option', $_GET) && $_GET['option'] == "delete" &&  array_key_exists('del_id', $_POST))
{
	include_once("includes/config.php");
	include_once( $_config['admin_includes'].'functions.php' );

	ajax_check_login();
	$baseUrl = $_config['admin_url'];
	
	if (! is_numeric($_POST['del_id'] ) ) header( "Location: " . $baseUrl . "pages.php?fail=delete" );	
	if (! hasNoChildren($_POST['del_id'] ) ) header( "Location: " . $baseUrl . "pages.php?fail=children" );

	// 	don't delete the page, instead set the id to a negative of itself so it can be restored later.
	// 		zero the has_menu and order data so it appears in menuless when restored 
	//		( don't interfere with order that may have changed since deletion )
	$update = "UPDATE pages 
SET id = :neg_del_id,
	parent_id='0',
	has_menu='0',
	menu_order='0'
WHERE id=:del_id";
	logged_query($update,0,array(":neg_del_id" => -$_POST['del_id'], ":del_id" => $_POST['del_id']));	

	// also need to delete all revisions;
	header( "Location: " . $baseUrl . "pages.php?success=delete" );
	exit;
}
//--------------------------page actions------------------------------------------------

// INITIALIZE the page
$headerComponents = array('revisions');
include_once('includes/headerClass.php');
$pageInit = new headerClass($headerComponents);
$pageResources ="
<script type=\"text/javascript\" src=\"".$baseUrl."js/edit_page.js\"></script>
";
$pageInit->createPageTop($pageResources);
$baseUrl = $_config['admin_url'];

// VARIABLES
$message = array(); 	// initialize error message
$spec_pages = array(); 	// an array of all special pages
						//   in admin/module/$module/system/config.php, /includes/custom_pages, or /includes/menuless_pages 
$hasSpecial = false;  	// system makes true if the site has Special Pages defined 
$is_module_page = false;// system makes true if special page is selected and special page is a module page						

// determine if this site has any special (menuless, custom, or module ) pages loaded
if(isset($_config['mod_special']) )foreach ($_config['mod_special'] as $spec_name => $spec_slug) 
	$spec_pages[ucwords($spec_name)] = array('url' => $spec_slug, 'type' => 'mod_special');
foreach ($_SESSION['menulessPages'] as $spec_name => $spec_slug) 
	$spec_pages[ucwords($spec_name)] = array('url' => $spec_name, 'type' => 'menuless');
foreach ($_SESSION['customPages'] as $spec_name => $spec_slug) 
	$spec_pages[ucwords($spec_name)] = array('url' => $spec_name, 'type' => 'custom');
foreach ( $_config['tinymce_secondary_content'] as $spec_name => $spec_slug)
	$spec_pages[$spec_name]= array('url' => $spec_name, 'type' => 'secondary');

if (count($spec_pages) > 0) $hasSpecial = true;

// this page has revisions, instaniate it
include_once($_config['components']."revisions/revisions.php");
$Revisions = new revisions('page_revisions','page_id', $_config['admin_url'] . 'view_page.php?option=revision&page=', 'id')	;

// we have arrived here in one of three ways: 
//		creating new page from pages,
//		loading a revision from this current item
//		loading an existing page from pages
$newPage = false;
if ( array_key_exists('option', $_GET) && $_GET['option'] == "new_page" )
{
	$newPage = true;
	$result = logged_query('
		SELECT MAX( CAST( SUBSTRING( `slug` , 5 ) AS Unsigned ) ) AS num
		FROM `pages`
		WHERE `id` >0
		AND `slug` REGEXP "^new_[0-9]*$"',0,array()
	);
	$slug_num = is_array($result) && count($result) ? (int)$result[0]['num'] : 0;
	$tmp_slug = 'new_' . ++$slug_num;
	$result = logged_query("INSERT INTO `pages` SET has_menu=-1, page_title=:tmp_slug, slug=:tmp_slug",0,array(":tmp_slug" => $tmp_slug));
	$page_id = $_config['db']->getLastInsertId();
	$page = logged_query("SELECT * FROM `pages` WHERE `id` =:page_id",0,array(":page_id" => $page_id));
	$page = $page[0];
} 	
// if this arrived from revision history, get info
elseif ( $Revisions->isRevision() ) {

	// $page_id is used later in the page, must be maintained consistantly across all three cases
	$page_id = $_GET['page_id'];
	$revise_id = $_POST['revise_id'];

	$page = $Revisions->getRevisionData($page_id,$revise_id);
	$message = $Revisions->getResultMessage();
}
// if this is an edit of an existing page, get the page info
elseif (array_key_exists('page_id', $_GET) && is_numeric($_GET['page_id']) ) {
		
	$page_id = $_GET['page_id'];
	/* Grabs Page Info from Database */
	$page=logged_query("SELECT * FROM pages WHERE id = :page_id and id > 0",0,array(":page_id" => $page_id) );
	if(isset($page[0])) $page= $page[0];
	else // no page data so get out
	{
		header( "Location: " . $baseUrl . "pages.php" );
		exit;
	}
}
// if we aren't editing or creating either, we shouldn't be here
elseif ( $_GET['option'] != "edit" && $_GET['option'] != "create" ) 
{
	header( "Location: " . $baseUrl . "pages.php" );
	exit;
}	

// is page being saved?
if ( isset($_GET['option']) && $_GET['option'] == "edit")
{

	// get inputs
	$page['page_title'] = $page_title = trim(htmlspecialchars($_POST['page_title'],ENT_QUOTES));
	$page['special_page'] = $special_page = trim(htmlspecialchars($_POST['special_page'],ENT_QUOTES));
	

	// if special_page has been set, use that slug...otherwise use a slug generated from page_title

	if($special_page)
	{
		$page['slug'] = $slug = $special_page;
		
	} else $page['slug'] = $slug = trim(htmlspecialchars($_POST['slug'],ENT_QUOTES));
	
	if(array_key_exists($page['slug'], $_config['tinymce_secondary_content'])) $is_secondary_tiny = true;
	else $is_secondary_tiny =false;
	
	if ( isset($_POST['no_follow']) ) $page['no_follow'] = $no_follow = htmlspecialchars($_POST['no_follow'],ENT_QUOTES);
	else $page['no_follow'] = $no_follow = 0;
	
	$is_module_page = isset($_config['mod_special']) && in_array($page['slug'], $_config['mod_special']);
	if($is_secondary_tiny)
	{
		$page['seo_keywords'] =$seo_keywords = '';
		$page['seo_description'] =$seo_description = '';
		$page['seo_title'] =$seo_title = '';
		$page['content'] =$content =  htmlspecialchars($_POST['content'],ENT_QUOTES);
	}
	else
	{
		$page['content'] =$content = $is_module_page ? '' : htmlspecialchars($_POST['content'],ENT_QUOTES);
		$page['seo_keywords'] =$seo_keywords = $is_module_page ? '' : trim(htmlspecialchars($_POST['seo_keywords'],ENT_QUOTES));
		$page['seo_description'] =$seo_description = $is_module_page ? '' : trim(htmlspecialchars($_POST['seo_description'],ENT_QUOTES));
		$page['seo_title'] =$seo_title = $is_module_page ? '' : trim(htmlspecialchars($_POST['seo_title'],ENT_QUOTES));
	}
	$page['visibility'] =$visibility = trim(htmlspecialchars($_POST['visibility'],ENT_QUOTES));
	$page['status'] =$status = trim(htmlspecialchars($_POST['status'],ENT_QUOTES));

	// validate:
	//	pick error type/messages based on if its status is draft or published
	if ($status == 1) // published: thus error
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Published Version';
		$errorMessage = 'all fields must be complete, saved as draft';
		
	}
	else  // draft: thus warning
	{
		$errorMsgType = 'warningMsg';
		$errorType = 'warning';
		$errorHeading = 'Draft Version';
		$errorMessage = 'saved with incomplete fields on page';
	}
	 
	// validation tests
	if (!$is_module_page  && $content == '') 
		{$message['inline']['content'] = array ('type' => $errorMsgType, 'msg' => 'Please enter page content below.');}
	
	if (!$is_module_page && !$is_secondary_tiny && $seo_title == '') 
		{$message['inline']['seo_title'] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	if ($page_title == '') {$message['inline']['page_title'] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	// need to do further validation of the page title
	
	
	if (!$is_module_page && !$is_secondary_tiny && $seo_keywords == '') 
		{$message['inline']['seo_keywords'] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	
	if (!$is_module_page && !$is_secondary_tiny && $seo_description == '') 
		{$message['inline']['seo_description'] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	//if ($name == '') {$message['inline']['name'] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
	
	// validate uniqueness of url if the posted url doesn't equal the page's url
	
	//if (validateUrl( $page['slug'] ) ) die( $page['slug'] );

	// if this is page creation:
	// determine parent_id from Menu Position assignment 
	if(isset($_POST['menu_posn'])) {
////////////////////////////////////////////////////////////////////////////////////////////////////
		$tmpId = trim(htmlspecialchars($_POST['menu_posn'],ENT_QUOTES));

		if ( ! is_numeric($tmpId) ) { // user has done something fishy, this should only be an int submitted as a string
			$message['inline']['menu_posn'] = array ('type' => $errorMsgType, 'msg' => 'Error with selection: Page saved as menuless.');
			$page['parent_id'] = 0;
			$page['has_menu'] = 0;
		} elseif ( $tmpId == -1 ) { // new menuless
			$page['parent_id'] = 0;
			$page['has_menu'] = 0;
		} elseif ( $tmpId == -2 ){  // new side menu
			$page['parent_id'] = 0;
			$page['has_menu'] = 2;
		} elseif ( $tmpId == 0 ){  	// new top menu
			$page['parent_id'] = 0;
			$page['has_menu'] = 1;
		}
		else 						// child of existing element
		{		
			$page['parent_id'] = $tmpId;
			// find parent's position to determine child's position
			$posnQuery = logged_query( "SELECT has_menu FROM pages WHERE id = :tmpId",0,array(":tmpId" => $tmpId) );
			if ( ! isset($posnQuery[0]['has_menu'] ) ) {
				die ('Invalid Page Data');
			}
			$page['has_menu'] = $posnQuery[0]['has_menu'];
		}
		$query = "SELECT MAX(menu_order) AS last_pos FROM `pages` WHERE id > 0 AND parent_id = :page_parent_id AND `has_menu` = :page_has_menu";
		$last_pos = logged_query($query,0,array(":page_parent_id" => $page['parent_id'], ":page_has_menu" => $page['has_menu']));
		$last_pos = $last_pos[0]['last_pos'];
		if ($last_pos === null) $menu_order = 0;
		else $menu_order = $last_pos + 1;
////////////////////////////////////////
	}
	// if there are any errors, set the banner message: we always save the changes, but if there are errors: save as draft
	if (isset ($message['inline']) && count($message['inline']))
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $page['status'] = 0; // saving as draft
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
	}
	// save edit (as draft if errors existed)
	// if this is a special page, assign its slug. Otherwise, use slug based on page title.
	
	if($_GET['option'] == "edit") {
		$bindings = array(
			":slug" => $slug,
			":page_title" => $page_title,
			":seo_title" => $seo_title,
			":no_follow" => $no_follow,
			":content" => $content,
			":seo_keywords" => $seo_keywords,
			":seo_description" => $seo_description,
			":visibility" => $visibility,
			":status" => $status,
			":get_page_id" => $_GET['page_id']
		);
		$update = "UPDATE pages 
SET slug=:slug,
	page_title=:page_title,
	seo_title=:seo_title,
	no_follow=:no_follow,
	content=:content,
	seo_keywords=:seo_keywords,
	seo_description=:seo_description,
	date=UTC_TIMESTAMP(),
	visibility=:visibility,
	status =:status";
	
	if(isset($page['menu_order']))
	{
		$bindings[':menu_order'] = $page['menu_order'];
		$bindings[':has_menu'] = $page['has_menu'];
		$bindings[':parent_id'] = $page['parent_id'];
		
		$update .= ",
	menu_order=:menu_order,
	has_menu=:has_menu,
	parent_id=:parent_id
	";
	}
	$update .="
WHERE id=:get_page_id";
		logged_query($update,0,$bindings) ;	
			
		$revision = "INSERT INTO page_revisions (page_id, slug, page_title, no_follow, content, seo_keywords, seo_description, seo_title, status, visibility, date) 
VALUES (:page_id, :slug, :page_title, :no_follow, :content, :seo_keywords, :seo_description, :seo_title, :status, :visibility, UTC_TIMESTAMP())";
		logged_query($revision,0,array(
			":page_id" => $page_id,
			":slug" => $slug,
			":page_title" => $page_title,
			":seo_title" => $seo_title,
			":no_follow" => $no_follow,
			":content" => $content,
			":seo_keywords" => $seo_keywords,
			":seo_description" => $seo_description,
			":visibility" => $visibility,
			":status" => $status
		));

	}
	
}	

// prepare all input fields
// no_follow
$val = isset($page['no_follow']) ? htmlspecialchars_decode($page['no_follow']) : '';
$input_no_follow = new inputField( 'No Search Engine', 'no_follow' );	
$input_no_follow->toolTip('Hide this page from Search Engines.');
$input_no_follow->value( $val  );
$input_no_follow->type( 'checkbox' );
$input_no_follow->arErr($message);


// seo title
$val = isset($page['seo_title']) ? htmlspecialchars_decode($page['seo_title']) : '';
$input_seo_title = new inputField( 'SEO Title', 'seo_title' );	
$input_seo_title->toolTip('Start with the most important words.');
$input_seo_title->value( $val  );
$input_seo_title->counterWarning(65);
$input_seo_title->counterMax(100);
$input_seo_title->size('large');
$input_seo_title->arErr($message);

// seo description
$val = isset($page['seo_description']) ? htmlspecialchars_decode($page['seo_description']) : '';
$input_seo_description = new inputField( 'Description', 'seo_description' );	
$input_seo_description->toolTip('Start with the same words used in the title.');
$input_seo_description->type('textarea');
$input_seo_description->value(  $val);
$input_seo_description->counterWarning(150);
$input_seo_description->counterMax(250);
$input_seo_description->size('large');
$input_seo_description->arErr($message);


// seo description
$val = isset($page['seo_keywords']) ? htmlspecialchars_decode($page['seo_keywords']) : '';
$input_seo_keywords = new inputField( 'Keywords', 'seo_keywords' );	
$input_seo_keywords->toolTip('List of phrases, separated by commas, with most important phrases first. <br /> Note: the words have to appear somewhere on the page.');
$input_seo_keywords->type('textarea');
$input_seo_keywords->value(  $val );
$input_seo_keywords->counterMax(1000);
$input_seo_keywords->size('large');
$input_seo_keywords->arErr($message);

// page title
$val = isset($page['page_title']) ? htmlspecialchars_decode($page['page_title']) : '';
$input_page_title = new inputField( 'Page Title', 'page_title' );	
$input_page_title->toolTip('Start with the most important words.');
$input_page_title->value(  $val );
$input_page_title->counterMax(100);
$input_page_title->size('small');
$input_page_title->arErr($message);

// url	
$val = isset($page['slug']) ? htmlspecialchars_decode($page['slug']) : '';
$input_url = new inputField( 'Url', 'slug' );	
$input_url->toolTip('Title as it appears in the url:<br />Use only letters, numbers, underscores and hyphens. No Spaces!');
$input_url->value($val );
$input_url->counterMax(100);
$input_url->size('small');
$input_url->arErr($message);

// visibility
$val = isset($page['visibility']) ? htmlspecialchars_decode($page['visibility']) : '';
$input_visibility = new inputField( 'Visibility', 'visibility' );	
$input_visibility->toolTip('Controls who can see the page.');
$input_visibility->type('select');
$input_visibility->selected( $val);
$input_visibility->option(  0, 'All'  );
$input_visibility->option( 1, 'Admin' );
if(in_array('members', $_SESSION['activeModules'])) $input_visibility->option( 2, 'Members' );
if(in_array('ftp', $_SESSION['activeModules'])) $input_visibility->option( 3, 'FTP Members');
$input_visibility->arErr($message);

// status
$val = isset($page['status']) ? htmlspecialchars_decode($page['status']) : '';
$input_status = new inputField( 'Status', 'status' );	
$input_status->toolTip('A draft version can have blank fields. A published version must have all fields completed.');
$input_status->type('select');
$input_status->selected( $val);
$input_status->option( 0, 'Draft' );
$input_status->option( 1, 'Published' );
$input_status->arErr($message);

// used in (special pages and menu construction)
$cur_pages = logged_query_assoc_array("SELECT id, parent_id, has_menu, menu_order, page_title, slug FROM pages WHERE `id`>0 ORDER BY menu_order",'slug',0,array());
if ($hasSpecial) {
	$val =  isset($page['slug']) ?  htmlspecialchars_decode($page['slug']) : '';
	$input_special_page = new inputField( 'Special Page', 'special_page' );	
	$input_special_page->toolTip('Make this one of the site&rsquo;s special pages.');
	$input_special_page->type('select');
	$input_special_page->selected( $val);
	$input_special_page->arErr($message);
	// create options
	$input_special_page->option( '0', 'Regular Page', array('data-type' => 'regular') );
	foreach($spec_pages as $name => $value) 
	{
		if(!array_key_exists($value['url'],$cur_pages) || $cur_pages[$value['url']]['id'] == $page['id'])
		{
			$input_special_page->option( $value['url'], ucwords(str_replace('_',' ',$name)), array('data-type' => $value['type']) );
			if($page['slug'] == $value['url']) $is_module_page = true;
		}
	}
}
// Menu Position
if ($page['has_menu']==-1) 
{
	// Menu Position  -- create version
	$input_menu_posn = new inputField( 'Menu Position', 'menu_posn', 'menuPosn' );
	$input_menu_posn->toolTip("Select this page&#39;s position in the menu tree.<br />Note: Position within the menu system can be adjusted by clicking &#39;Pages&#39; in the sidebar and using &#39;drag and drop&#39; to reposition." );
	$input_menu_posn->type('select');
	$input_menu_posn->arErr($message);
	
	// build the option set
		// get all menu items, order them parentally
	// get the menus data
	$cur_pages = logged_query("SELECT id, parent_id, has_menu, menu_order, page_title FROM pages ORDER BY menu_order",0,array());
	
	$menus = array();
	foreach($cur_pages as $menu )
	{
		$menus[] = $menu;
	}

	// take those data and format as tree array up to three levels deep
	// or load the noMenu array if menu order is 0 (not linked to a menu) 
	$arTop = array();
	$arSide = array();
	//first, construct the top menu structure
	foreach($menus as $menu)
	{
		if ($menu['parent_id'] == 0 && $menu['has_menu'] == 1)
		{	
			$curId = $menu['id'];
			$arTop[$curId] = $menu;
			foreach($menus as $child)
			{
				if ( $arTop[$curId]['id'] == $child['parent_id'] && $child['has_menu'] != 0)
				{	
					$curChildId = $child['id'];
					$arTop[$curId]['child'][$curChildId] = $child;
				}
			}
		}
		elseif ($menu['parent_id'] == 0 && $menu['has_menu'] == 2)
		{	
			$curId = $menu['id'];
			$arSide[$curId] = $menu;
			foreach($menus as $child)
			{
				if ( $arSide[$curId]['id'] == $child['parent_id'] && $child['has_menu'] != 0)
				{	
					$curChildId = $child['id'];
					$arSide[$curId]['child'][$curChildId] = $child;
				}
			}
		}
	}

	//create  options: value represents the parent's id with parent 0 indicating top level
	$input_menu_posn->option(  0, 'New Top Menu Page', array('class' => 'tipRight', 'title' => 'Make this page one of the<br \> main menu items.', 'style' => 'text-decoration: underline;' ) );
	
	foreach ($arTop as $parent)
	{
		$input_menu_posn->option(  $parent['id'], $parent['page_title'], array('class' => 'tipRight', 'title' => "Make this page a sub-menu of <br \> \"{$parent['page_title']}\""));
		
		if ( isset($parent['child']) && $parent['child'] ){
			foreach ( $parent['child'] as $child ) {
				$input_menu_posn->option(  $child['id'], '&nbsp;&nbsp;&nbsp;'.$child['page_title'], array('class' => 'tipRight', 'title' => "Make this page a sub-menu of <br \> \"{$child['page_title']}\""));
			}
		}
	}
	$input_menu_posn->option(  -2, 'New Side Menu Page', array('class' => 'tipRight', 'title' => 'Make this page a new  <br \> side menu item.', 'style' => 'text-decoration: underline;' ));
	foreach ($arSide as $parent)
	{
		$input_menu_posn->option(  $parent['id'], $parent['page_title'], array('class' => 'tipRight', 'title' => "Make this page a sub-menu of <br \> \"{$parent['page_title']}\""));
		if ( isset($parent['child']) && $parent['child'] ){
			foreach ( $parent['child'] as $child ) {
				$input_menu_posn->option(  $child['id'], '&nbsp;&nbsp;&nbsp;'.$child['page_title'], array('class' => 'tipRight', 'title' => "Make this page a sub-menu of <br \> \"{$child['page_title']}\""));
			}
		}
	}
	$input_menu_posn->option(  -1, 'New Menuless Page', array('class' => 'tipRight', 'title' => 'This page is not part of the <br \>  menu structure.', 'style' => 'text-decoration: overline;' ));
} 
else  // this is not a new page, identify this page's parent page
{
	if ( ! $page['has_menu'] ) {
		$parentName = "Menuless Page";
	} elseif ( $page['parent_id'] == 0 ) {
		$parentName = "Top Level Page";
	} else {
		$parentQuery=logged_query("SELECT page_title FROM pages WHERE id = :page_parent_id",0, array(":page_parent_id" => $page['parent_id']) );
		if (! isset($parentQuery[0]['page_title'])) die ($err);
		$parentName = $parentQuery[0]['page_title'];
	}	
	// Menu Position  -- edit version
	$input_menu_posn = new inputField( 'Menu Position', 'menu_posn', 'menuPosn' );	
	$input_menu_posn->toolTip("This is the current position in the menu system.<br />To change, click &#39;Pages&#39; in the sidebar and &#39;drag and drop&#39; to reposition.");
	$input_menu_posn->value( htmlspecialchars_decode($parentName) );
	$input_menu_posn->size('small');
	$input_menu_posn->disable();
	$input_menu_posn->arErr($message);
	
}

function hasNoChildren($tmpId)
{
	$childrenQuery=logged_query("SELECT * from pages where parent_id = :tmpId",0,array(":tmpId" => $tmpId) ) or header( "Location: " . $baseUrl . "pages.php?fail=page" );
	if ( $childrenQuery < 1 ) return true;
	return false;
}

//** create a list of reserved page names: **//
// can't be claimed system names like:	'images'...
// or customNames like: 				'blog/news', 'cart'...
// or config defined names like:		'admin_folder : admin', 'home_slug : home'
// or existing page names
// get system reserved slugs
$usedSlugs = reservedSlugs();
// get used slugs from pages
$slugs=logged_query("SELECT `slug` FROM pages WHERE id > 0",0,array());
if ( $slugs && count($slugs) > 0 ) : foreach($slugs as $row) $usedSlugs[] = $row['slug']; endif;

?>
<script type="text/javascript">
	curSlug = "<?php echo isset($page['slug']) ? $page['slug'] : '';?>";
	usedSlugs = new Array();
<?php foreach($usedSlugs as $slug) : ?>
	usedSlugs.push("<?php echo $slug; ?>");
<?php endforeach; ?>
	
</script>
<div class="page_container">
	<div id="h1">
		<h1>
			<?php echo $newPage ? 'Create New Page' : 'Edit Page - ' . $page['page_title']; ?>
		</h1>
	</div>
	
	<div class="edit_container" style="margin-bottom: 15px">
		<span style="float:right; margin: -15px 15px 0 0;font-size: 12px"> <?php echo !$newPage ? 'Page Slug: ' . $page['slug'] : ''; ?> </span>
		
		<?php 
		// set the form's GET data depending on whether it is a new page or not
		//if ($newPage) $formOpt = 'option=create';
		//else 
		$formOpt = "option=edit&page_id={$page['id']}";
		?>
		<form action="edit_page.php?<?php echo $formOpt;?>" method="post" enctype="application/x-www-form-urlencoded" id="form-page" name="edititem">
			
			
			<?php //---------------------------------------Error Banner----------------------------- 
			// create a banner if $message['banner'] exists
			createBanner($message); 
		?>


			
			<!-- properties area -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the page.">Page Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_page_title->createInputField();
				if($hasSpecial) $input_special_page->createInputField();
				$input_url->createInputField();
				$input_menu_posn->createInputField();
				$input_visibility->createInputField();
				$input_status->createInputField();
				$input_no_follow->createInputField();
			?>
			
			</div><!-- end prop_wrap -->
<div id="not_module_wrap" <?php echo $is_module_page ? 'class="is_module"':'';?>>			
		
			<!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="The actual content displayed on the page.">Page Content</h2>
			<?php if (isset ($message['inline']) && array_key_exists('content', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['content']['type'] ;?>"><?php echo $message['inline']['content']['msg'] ;?> </span>
			<?php endif; ?>
			<br />
			<?php
			// create tinymce
			if(array_key_exists($page['slug'], $_config['tinymce_secondary_content'])) 
				$jump_target = $_config['tinymce_secondary_content'][$page['slug']];
			else $jump_target = $page['slug'];
			
			$editable = array(
				'editable_class' => 'mceUploadable',
				'attributes' => array(
					'name' => 'content',
					'id' => 'content',
					'data-jump-type' => "front",
					'data-jump' => "../".$jump_target
				),
				'secure_data' => array(
					'id-field' => 'id',				// req for save && upload
					'id-val' => $page['id'],	// req for save && upload
					'upload-type' => 'dflt'			// req for upload
				)
			);
			$wrapper = getContentWrapper($editable);
			echo $wrapper['open'];
			echo isset($page['content']) ? htmlspecialchars_decode($page['content']) : '' ;
			echo $wrapper['close'];
						
			?>
			<!-- end content area -->
	<div id="seo_area_wrap">		
			<!-- SEO area -->
			<h2 class="tiptip toggle" id="seo-toggle" ;" title="Search Engine Optimization fields">Meta Tags</h2><br />

			<div id="seo-toggle-wrap">
				<?php
				// create the previously prepared input fields
				$input_seo_title->createInputField();
				$input_seo_description->createInputField();
				$input_seo_keywords->createInputField();
				?>    
			</div><!-- end SEO area -->
	</div> <!-- end seo_area_wrap -->		
</div><!-- end no_module_wrap -->
		</form>	
			<!-- page buttons -->
			<div class='clearFix' ></div>
				<a id="submit-btn" class="blue button" href="#">Save</a>
			
				<?php
					// removed delete button, may use again in the future so leaving code
					if (false) :  // begin cancelled delete button
					if (isset($page['id']) && $page['id'] > 0)  : ?>
					<?php if ( hasNoChildren( $page['id']  ) ) : ?>
				<form name='delForm' id='delForm' action='edit_page.php?option=delete' method="post" enctype="application/x-www-form-urlencoded">
					<input type='hidden' name='del_id' value='<?php echo $page['id']; ?>' />
					<input type='submit' id="delete-btn" class="red button" value='Delete' />
				</form>
					<?php else : ?>
				<input type='submit' value='Delete' class='tipTop button grey' title='Cannot delete: this page has other pages associated with it. Click on <i>Pages</i> in Sidebar to move children first.' />
					<?php endif; ?>
				<?php endif ; 
					  endif; // end of cancelled delete button?>
				<a class="grey button" id="cancel-btn" href="pages.php">Cancel</a>
			<div class='clearFix' ></div> 
			<!-- end page buttons -->

		
		
		
<?php	// ----------------------------------- revisions -------------------------------------
		// extra post data to fill in the blanks that aren't saved in revision data
		$extraPOST = array( 
			'has_menu' => isset($page['has_menu']) ? $page['has_menu'] : '',
			'parent_id' => isset($page['parent_id']) ? $page['parent_id'] : '',
			'menu_order' => isset($page['menu_order']) ? $page['menu_order'] : ''
		);
		// add GET data to the return url
		$extraGET = array('page_id' => isset($page['id']) ? $page['id'] : '');
		// build the Revision Area
		$Revisions->createRevisionsArea(isset($page['id']) ? $page['id'] : '',$extraPOST,$extraGET);
		// end revisions
?>
		


	</div> <!-- end edit_container -->
 </div> <!-- end page_container -->

<?php include_once("includes/footer.php"); ?>