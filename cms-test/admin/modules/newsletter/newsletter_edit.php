<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'newsletter';
include('../../includes/headerClass.php');
include ($_config['admin_includes'].'html2text.php');
include('includes/functions.php');
include('classes/MailChimp.php');

// set the db variables
$table 				= 'newsletter';			// name of the table this page posts to
$mce_content_name	= 'content';					// the tinyMCE editor always uses the id:content, 
												//   but db tables usually use something specific to that table for main content,
												//   eg, blog has 'post', category has 'desc', but page does use 'content': set the value here
$page_type			= 'newsletter';			// name of uploadPageType for uploads component, also used to differentiate kinds of delete on parent page
$page_get_id		= 'newsid';					// the GET key passed forward with the db id for this item(page/blog post/...)
$page_get_id_val	= '';						// this variable is used to store the GET id value of above
$parent_page = "newsletter.php";
$newPost = false;

$pageInit = new headerClass($headerComponents,$headerModule);

// access user data from headerClass
global $curUser;
$isAdmin = $curUser['admin'] == 'yes';

$baseUrl = $_config['admin_url'];
$pg = "edit";

//get apikey and make a chimp object
$getkey = logged_query("SELECT * FROM newsletter_settings WHERE id = 1",0,array());
$apikey = decrypt($getkey[0]['apikey']);
$to_name = $getkey[0]['to_name'];
$chimp = new MailChimp($apikey);

//get lists
$clists = $chimp->call('/lists/list',array('apikey'=>$apikey));
if(isset($clists['status']) && $clists['status']=="error") {
	$lists = false;
	$message['banner'] = array ('heading' => 'Mailchimp List Not Found', 'message' => 'A list must exist before a newsletter campaign can be created', 'type' => 'error');
} else {
	$lists = $clists['data'];
}

/*deal with options*/

if ( array_key_exists('option', $_GET) && ($_GET['option'] == 'create') ) 
{
	$newPost = true;
	
	$result = logged_query('
		SELECT MAX( CAST( SUBSTRING( `subject` , 5 ) AS Unsigned ) ) AS num
		FROM `newsletter`
		WHERE `id` >0 AND `status` > 0
		AND `subject` REGEXP "^newsletter_[0-9]*$"',0,array()
	);
	$slug_num = is_array($result) && count($result) ? (int)$result[0]['num'] : 0;
	$tmp_slug = 'newsletter_' . ++$slug_num;
	$result = logged_query("INSERT INTO {$table} SET  subject=:tmp_slug, status=0, date_created='".date("Y-m-d H:i:s")."', date_updated='".date("Y-m-d H:i:s")."'",0,array(":tmp_slug" => $tmp_slug));
	$page_get_id_val = $_config['db']->getLastInsertId();

	$list = logged_query("SELECT * FROM `newsletter` WHERE `id` =:id",0,array(":id" => $page_get_id_val));
	$list = $list[0];
}
// if this is an edit of an existing post, get the post info
elseif (array_key_exists($page_get_id, $_GET) && is_numeric($_GET[$page_get_id])) {	
	$new = logged_query_assoc_array("SELECT * FROM {$table} WHERE id = {$_GET[$page_get_id]} ORDER BY id DESC LIMIT 1",null,0,array()); 
	$list=$new[0];
	// if the current user isn't an admin and isn't the listed agent, kick out the current user
	// also, kick out non-admin if status = published
	if(! $isAdmin && ( ($curUser['user_id'] != $list['agent']) || $list['status'] ) ) {echo 'hey, you do not belong';}

	$page_get_id_val = $_GET[$page_get_id];

}
// if we aren't editing or creating either, we shouldn't be here
elseif ( !array_key_exists('option', $_GET) && !array_key_exists('action', $_POST) )
{
	header( "Location: " . $_config['admin_url'] . "modules/{$headerModule}/{$parent_page}" );
	exit;
}
/*end deal with options*/

$newsid = $page_get_id_val;

//get associated lists
$recips = logged_query_assoc_array("SELECT * FROM newsletter_recip WHERE news_id = '{$newsid}'",null,0,array());
$checked_items = array();

if(!empty($recips)) {
	foreach($recips as $row) {
		$checked_items[] = $row['list_id'];
	}
}

#========================
# process newsletter info
#========================
/* sample $_POST
Array ( [action] => save_newsletter [subject] => First Newsletter [status] => 1 [lists] => Array ( [4ed425352b] => 4ed425352b [a4a9bfc44d] => a4a9bfc44d ) [content] =>
First Newsletter

Bacon ipsum dolor sit amet brisket meatball frankfurter pork chop jerky leberkas. Ham hock prosciutto t-bone frankfurter spare ribs drumstick chicken landjaeger chuck pig jerky filet mignon ham short ribs. Pig fatback bresaola salami. Ham hock capicola filet mignon jerky.
) 
*/

// save newsletter - must be saved as "Ready" status before it can be sent
if(isset($_POST['action'])) {

	$list = array();
	$errors = array();
	$checked_items = array();
	
	$errorMsgType = 'errorMsg';
	$errorType = 'error';
	$errorHeading = 'Could Not Save';
	$errorMessage = 'all fields must be complete, not saved';

	foreach($_POST as $k=>$v)
	{
		if($k == 'content') ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
		elseif($k == 'lists') { 
			foreach($v as $key=>$val) {
				$checked_items[] = $val;
			}
		}
		else ${$k} = trim($v);
		if($k != 'action') $list[$k] = $v;
		if (${$k} == '') {
			$errors[$k] = "Required Field";
		}
	}
	
	if(empty($checked_items)) {
		$errors['lists'] = "Required Field";
	}
	// if an error was found, create the error banner
	if ($errors)
	{
		// translate errors to messages
		foreach($errors as $k => $v)
		{
			$message['inline'][$k] = array('type' => $errorMsgType, 'msg' => $v[0]);
		}
	
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as draft
		$where_clause = "WHERE id = {$id}";
		$result = logged_array_update($table, $list, $where_clause);
	}
	else // set the success message
	{
		unset($list['lists']);
		unset($list['id']);
		
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		$where_clause = "WHERE id = {$id}";
		
		$result = logged_array_update($table, $list, $where_clause);
		$saveError = (bool) $result['error'];
		$newsid = $id;
		
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	{
			$message['banner'] = array ('heading' => 'Error Saving Subscription', 'message' => 'Subscription was not saved', 'type' => 'error' );
		} else {
		//save/replace recipients
			$delete = logged_query("DELETE FROM newsletter_recip WHERE news_id = {$newsid}",0,array());
			foreach($checked_items as $k=>$v) {
				$result = logged_query("INSERT INTO newsletter_recip (`news_id`, `list_id`) VALUES('{$newsid}', '{$v}')",0,array());
			}
		}
		
		//get new $list
		$list = logged_query_assoc_array("SELECT * FROM newsletter WHERE id = {$newsid}",null,0,array());
		$list = $list[0];
		//get new $checked_items
		$checked = logged_query_assoc_array("SELECT list_id FROM newsletter_recip WHERE news_id = {$newsid}",null,0,array());
		$checked_items = array();
		foreach($checked as $ch) {
			$checked_items[] = $ch['list_id'];
		}
		
		if(!$saveError && $result !== false && $action == "send_newsletter") {
			
			foreach($checked_items as $k=>$v) {
				foreach($lists as $row) {
					if($row['id']==$v) {
						$from_email = $row['default_from_email'];
						$from_name = $row['default_from_name'];
					}
				}
				$chimpopts = array(
					"list_id" => $v,
					"subject" => $list['subject'],
					"from_email" => $from_email,
					"from_name" => $from_name,
					"to_name" => $to_name
				);
				$chimpcontent = array(
					"html" => htmlspecialchars_decode($list['content'])
				);
				
				$send = $chimp->call('/campaigns/create',array('apikey'=>$apikey,'type'=>'regular','options'=>$chimpopts,'content'=>$chimpcontent));
				
				$sent = $chimp->call('/campaigns/send',array('apikey'=>$apikey,'cid'=>$send['id']));
				if($sent['complete']) {
					$message['banner'] = array ('heading' => 'Newsletter Successfully Sent', 'type' => 'success');
					$where_clause = "WHERE id = {$id}";
					$upd['date_sent'] = date("Y-m-d H:i:s");
					$result = logged_array_update('newsletter_recip', $upd, $where_clause);
					$result1 = logged_array_update($table,array('status'=>2),$where_clause);
				} else {
					$message['banner'] = array ('heading' => 'Mailchimp Error - Newsletter Not Sent', 'type' => 'error');
				}
			}
		}
	}
}

#========================

// set message array to empty if no errors
if (! isset($message)) $message=array();

// news_item title
$input_subject = new inputField('Subject', 'subject');
$input_subject->toolTip('Subject line in the Newsletter Email');
$input_subject->value(htmlspecialchars_decode(isset($list['subject']) ? $list['subject'] : '', ENT_QUOTES));
$input_subject->counterMax(100);
$input_subject->arErr($message);

// status
$val = isset($list['status']) ? htmlspecialchars_decode($list['status']) : '0';
$input_status = new inputField( 'Status', 'status' );	
$input_status->toolTip('A draft version can have blank fields. A Ready version must have all fields completed.');
$input_status->type('select');
$input_status->selected( $val );
$input_status->option( 0, 'Draft' );
$input_status->option( 1, 'Ready' );
$input_status->arErr($message);

// -----------------------------------------------html------------------------------------

// head/header/sidebar
$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/newsletter/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/newsletter/js/newsletter.js\"></script>
";
$pageInit->createPageTop($pageResources);

?>
<div class="page_container">
	<div id="h1"><h1>Newsletter</h1></div>
    <div id="info_container">
		<?php        
        echo '<div>';
        include("includes/subnav.php");
        echo '</div>'; ?>
		<div class='clearFix' ></div>
		<?php
		// create a banner if $message['banner'] exists
            echo '<div id="newsletter_banner">';
            createBanner($message);
            echo '</div>';
		?>
		<form action="newsletter_edit.php" method="post" enctype="multipart/form-data"  id="send_newsletter" class="form">
            <input type="hidden" id="hidden_action" name="action" value="save_newsletter" />
			<input type="hidden" name="id" value="<?php echo $newsid; ?>" />
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the News Item.">Newsletter Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_subject->createInputfield();
				$input_status->createInputfield();
			?>
			<div class="input_wrap">
						<label>Recipients</label>
						<div class="input_inner">
						<?php
						if($lists) {
							foreach ($lists as $l){
								$check_options[] = array( 
									'id' 	=> 	$l['id'],
									'title' =>	$l['name'],
									'class' => 	""
								);
							}
							// build the Lists table
							//$checked_items = array(); /* built above with $recips
							$check_group_name 		= 'lists';
							$check_group_label 		= '';
							$check_group_table_id	= 'list_table';
							$check_group_columns 	= 3;
							$check_group_title 		= 'Select the Lists to subscribe this email address to.';
							$as_radio				= false;
							$check_group_class = 'noleftpadding';
									
							//display it as a list
							$response = display_checkbox_list(
								$check_options,
								$check_group_name,
								$check_group_label,
								$checked_items,
								$check_group_table_id,
								$check_group_title,
								$check_group_columns,
								$as_radio,
								$check_group_class
							);
						} else {
							echo "No lists are active at this time.";
						}
						?>
						</div><!-- END input_inner -->
					</div><!-- END input_wrap -->	
			</div><!-- end prop_wrap -->		   
			<div class='clearFix' ></div>
             <!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="Newsletter content.">Newsletter Content</h2>
			<?php if (isset($message['inline']) && array_key_exists('content', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['content']['type'] ;?>"><?php echo $message['inline']['content']['msg'] ;?> </span>
			<?php endif; ?>
			<br />
			<div id="content-toggle-wrap">
			<?php
				$saveid = isset($list['id']) ? $list['id'] : $newsid;
				// create tinymce
				$editable = array(
					'editable_class' => 'mceUploadable',
					'attributes' => array(
						'name' => 'content',
						'id' => 'newsletter_content'
					),
					'secure_data' => array(
						'id-field' => 'id',				// req for save && upload
						'id-val' => $saveid,	// req for save && upload
						'upload-type' => $page_type
					)
				); 
				$wrapper = getContentWrapper($editable);
				echo $wrapper['open'];
				echo isset($list['content']) ? htmlspecialchars_decode($list['content']) : '' ;
				echo $wrapper['close'];
			?>
			</div>
			<!-- end content area -->
			<div class='clearFix' ></div>
		</form>
		<?php
			if($list['status']==1) {
				echo "<a id='submit-send' class='green button formbutton' href='#'>Send</a>";
			}
		?>
		<a id="submit-btn" class="blue button formbutton" href="#">Save</a>
		<a class="grey button formbutton" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
		<hr />
	</div> <!--end infoContainer -->
</div>
<?php 
include($_config['admin_includes']."footer.php"); 
?>