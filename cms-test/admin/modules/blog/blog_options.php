<?php 

// initialize the page
$headerComponents = array();
$headerModule = 'blog';
include('../../includes/headerClass.php');
$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];
global $curUser; // user details

// set the db variables
$table 				= 'blog_options';		// name of the table this page posts to
$parent_page		= 'blog.php';		// the page that calls this one: used for delete and cancel form buttons, sets where to go afterwards

$page_get_id_val	= 1;				// this variable is used to store the GET id value of above. Or, in single element sections(like blog comments) enter the value of the default id

$message			= array();			// will hold error/success message info

// show the current values
$new = logged_query_assoc_array("SELECT * FROM {$table} LIMIT 1",null,0,array()); 
$list=$new[0];

#==================
# process post
#==================
if(isset($_POST['submit-post'])){

	$list = array();
	foreach($_POST as $k=>$v)
	{
		${$k} = trim($v) ;
		$list[$k] = ${$k};
		if (! is_numeric(${$k}) ) {$message['inline'][$k] = array ('type' => 'errorMsg', 'msg' => 'Invalid Data');}
	}
	
	// if an error was found, create the error banner
	if (isset($message['inline']) && count($message['inline']))
	{
		$message['banner'] = array ('heading' => 'Unexpected Values', 'message' => 'could not update records', 'type' => 'error');
	}
	else // set the success message and save data
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		if (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
		{
			$result = logged_query("UPDATE `{$table}` SET 
			`user_id` = :user_id,
			`post_per_pg`	= :post_per_pg,
			`show_date` = :show_date,
			`show_author` = :show_author,
			`approve` = :approve,
			`comments` = :comments,
			`com_per_pg` = :com_per_pg
			WHERE `id` = :page_get_id_val LIMIT 1;",0,array(
				":user_id" => $curUser['user_id'],
				":post_per_pg" => $post_per_pg,
				":show_date" => $show_date,
				":show_author" => $show_author,
				":approve" => $approve,
				":comments" => $comments,
				":com_per_pg" => $com_per_pg,
				":page_get_id_val" => $page_get_id_val
			));

			// banners: if there was an error, overwrite the previously set success message
			if ($result === false)	$message['banner'] = array ('heading' => 'Error Saving Options', 'message' => 'there was an error writing to the database', 'type' => 'error' );
		}
	}
}
if(! isset($message))$message = array();

// comments per page
$input_post_per_page = new inputField( 'Posts Per Page', 'post_per_pg' );	
$input_post_per_page->toolTip('Set the number of Blog Posts that will be displayed at a time.');
$input_post_per_page->type('select');
$input_post_per_page->size('tiny');
$input_post_per_page->selected($list['post_per_pg']);
$input_post_per_page->option( 1, '1');
$input_post_per_page->option( 2, '2');
$input_post_per_page->option( 3, '3');
$input_post_per_page->option( 4, '4');
$input_post_per_page->option( 5, '5');
$input_post_per_page->option( 7, '7');
$input_post_per_page->option( 8, '8' );
$input_post_per_page->option( 10, '10' );
$input_post_per_page->option( 20, '20' );
$input_post_per_page->option( 50, '50' );
$input_post_per_page->option( -1, 'All' );

$input_post_per_page->arErr($message);

// show_date
$input_show_date = new inputField( 'Show Date', 'show_date' );	
$input_show_date->toolTip('When enabled, published date will be displayed on blog articles.');
$input_show_date->type('select');
$input_show_date->size('small');
$input_show_date->selected($list['show_date']);
$input_show_date->option( 1, 'Enabled' );
$input_show_date->option( 0, 'Disabled' );
$input_show_date->arErr($message);

// show_author
$input_show_author = new inputField( 'Show Author', 'show_author' );	
$input_show_author->toolTip('When enabled, blog articles will display the name of the author.');
$input_show_author->type('select');
$input_show_author->size('small');
$input_show_author->selected($list['show_author']);
$input_show_author->option( 1, 'Enabled' );
$input_show_author->option( 0, 'Disabled' );
$input_show_author->arErr($message);

// comments
$input_comments = new inputField( 'Comments', 'comments' );	
$input_comments->toolTip('When enabled, visitors to your blog will be able to leave comments.');
$input_comments->type('select');
$input_comments->selected($list['comments']);
$input_comments->option( 1, 'Enabled' );
$input_comments->option( 0, 'Disabled' );
$input_comments->arErr($message);

// approve
$input_approve = new inputField( 'Comment Approval', 'approve' );	
$input_approve->toolTip('If approval is required, the email set in the admin section will be notified. <br />Comments can be approved or declined on the Blog Admin main page.');
$input_approve->type('select');
$input_approve->selected($list['approve']);
$input_approve->option( 1, 'Required' );
$input_approve->option( 0, 'Not Required' );
$input_approve->arErr($message);

// comments per page
$input_com_per_page = new inputField( 'Comments Per Page', 'com_per_pg' );	
$input_com_per_page->toolTip('Set the number of user comments that will be displayed at a time.');
$input_com_per_page->type('select');
$input_com_per_page->size('tiny');
$input_com_per_page->selected($list['com_per_pg']);
$input_com_per_page->option( 1, '1');
$input_com_per_page->option( 2, '2');
$input_com_per_page->option( 3, '3');
$input_com_per_page->option( 4, '4');
$input_com_per_page->option( 5, '5');
$input_com_per_page->option( 7, '7');
$input_com_per_page->option( 8, '8' );
$input_com_per_page->option( 10, '10' );
$input_com_per_page->option( 20, '20' );
$input_com_per_page->option( 50, '50' );
$input_com_per_page->option( -1, 'All' );

$input_com_per_page->arErr($message);


$pageResources ="
<link rel='stylesheet' type='text/css' href='style.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/blog/js/blog_options.js\"></script>
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div class="page_container">
	<div id="h1"><h1>Blog Options</h1></div>
    <div id="info_container">
		<?php 
		// ------------------------------------sub nav----------------------------
		$selectedOpts = 'tabSel';
		$selectedPosts = '';
		$selectedCats = '';
		include("blog/subnav.php"); 
		echo '<hr />';		
		
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 

		
		$propId = '';
		$propId = 'prop-toggle'; // currently disabled since there is only one heading.
		
		$parms = "?option=edit";
		?>
		<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?><?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="form_data" id="form_data" class="form">
  
			<!--  properties_wrap -->
			<h2 id="<?php echo $propId; ?>" class="tiptip toggle" title="Choose your blog's title, decide how to handle user comments and decide if your 'About the Blog' link is ready to be viewed.">Blog Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_post_per_page->createInputField();
				$input_show_date->createInputField();
				$input_show_author->createInputField();
				$input_comments->createInputField();
				$input_com_per_page->createInputField();
				$input_approve->createInputField();
				
			?>
			</div><!-- end prop_wrap -->		   

 
                       
                            
			<!-- page buttons -->
			<div class='clearFix' ></div>

			<input name="submit-post" type="hidden" value='1'  />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
	</div>
</div>	
	

<?php 

include($_config['admin_includes'] . "footer.php"); ?>