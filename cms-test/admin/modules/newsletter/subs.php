<?php
// initialize the page
$headerComponents = array();
$headerModule = 'newsletter';
include('../../includes/headerClass.php');
include('includes/functions.php');
include('classes/MailChimp.php');

$parent_page = "subs.php";

$pageInit = new headerClass($headerComponents,$headerModule);

// access user data from headerClass
global $curUser;

//encrypted fields
$enc_keys = array('email');

//get subscribers
$list = logged_query_assoc_array("SELECT * FROM newsletter_subs",null,0,array());

// decrypt encrypted values
foreach($list as $key=>$val) {
	foreach($val as $k=>$v) {
		if(in_array($k,$enc_keys) && $v!="") {
			$val[$k] = decrypt($v);
		}
		$list[$key] = $val;
	}
}
//get apikey and make a chimp object
$getkey = logged_query("SELECT apikey FROM newsletter_settings WHERE id = 1",0,array());
if(!empty($getkey)) {
	$apikey = decrypt($getkey[0]['apikey']);
	$chimp = new MailChimp($apikey);
} else {
	$apikey = "";
}

/* deal with posts here */
if(isset($_POST['subscribe'])) {

// validate:
	$errors = array();
	$editsub = false;
	
	$errorMsgType = 'errorMsg';
	$errorType = 'error';
	$errorHeading = 'Could Not Save';
	$errorMessage = 'all fields must be complete, not saved';
	
	unset($_POST['subscribe']);
	unset($_POST['submit-post']);
	
	$subemail = encrypt(trim($_POST['email']));
	$checksub = logged_query("SELECT id FROM newsletter_subs WHERE email = :email LIMIT 1",0,array(':email'=>$subemail));
	if(!empty($checksub) && $checksub !== false) $checksub = $checksub[0];
	
	if(!empty($checksub)) {
		$editsub = true;
		$subscribed = logged_query("SELECT * FROM newsletter_subs_groups WHERE sub_id = {$checksub['id']}",0,array());
	} else {
		$editsub = false;
		$subscribed = array();
	}
		
	foreach($_POST as $k=>$v) {
			if($k=='email') {
				if(!check_email_address(trim($v))) {
					$errors[$k] = 'Please Supply A Valid Email Address';
				} else {
					$tmplist[$k] = trim($v);
				}
			} else {
				foreach($v as $i) {
					$subgroups[$k][] = trim($i);
				}
			}
	}
	
	// if an error was found, create the error banner
	if ($errors)
	{
		// translate errors to messages
		foreach($errors as $k => $v)
		{
			if($k == 'email') {
				$message['inline'][$k] = array('type' => $errorMsgType, 'msg' => $v);
			} else {
				$message['inline'][$k] = array('type' => $errorMsgType, 'msg' => $v[0]);
			}
		}
	
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as draft
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
		if(!$editsub) {
			$result = logged_array_insert('newsletter_subs', $tmplist, $enc_keys);
			$saveError = (bool) $result['error'];
			$newId = $result['insert_id'];
		} else {
			$saveError = false;
			$newId = $checksub['id'];
			$result = logged_query("DELETE FROM newsletter_subs_groups WHERE sub_id = '{$newId}'",0,array());
		}
		
		//get new $list
		$list = logged_query_assoc_array("SELECT * FROM newsletter_subs",null,0,array());
		
		// decrypt encrypted values
		foreach($list as $key=>$val) {
			foreach($val as $k=>$v) {
				if(in_array($k,$enc_keys) && $v!="") {
					$val[$k] = decrypt($v);
				}
				$list[$key] = $val;
			}
		}
		
		// banners: if there was an error, overwrite the previously set success message
		if ($saveError)	{
			$message['banner'] = array ('heading' => 'Error Saving Subscription', 'message' => 'Subscription was not saved', 'type' => 'error' );
		} else {
			/* sample $subgroups
			Array ( [lists] => Array( [0] => 4ed425352b ) 
			*/			
			
			// save subscription data
			foreach($subgroups['lists'] as $k=>$v) {
				$result = logged_query("INSERT INTO newsletter_subs_groups VALUES(NULL, {$newId}, '{$v}')");
			}
			if($result === false) {
				$message['banner'] = array ('heading' => 'Error Subscribing to Group', 'message' => 'Subscription was not saved properly.', 'type' => 'error' );
			} else {
				//compare old and new lists
				$unsubscribed = array();
				foreach($subscribed as $subrow) {
					if(!in_array($subrow['list_id'],$subgroups['lists'])) {
						$unsubscribed[] = $subrow['list_id'];
					}
				}
				
				//send unsubscription to Mailchimp
				if(!empty($unsubscribed)) {
					foreach($unsubscribed as $k=>$v) {
						$unsubd = $chimp->call('/lists/unsubscribe',array('apikey'=>$apikey,'id'=>$v,'email'=>array('email'=>$tmplist['email'])));
					}
				}
				
				//send subscription to Mailchimp
				foreach($subgroups['lists'] as $k=>$v) {
					$subscribe = $chimp->call('/lists/subscribe',array('apikey'=>$apikey,'id'=>$v,'email'=>array('email'=>$tmplist['email'])));
				}
			}
		}
	}
	
}
/* end post handling*/

//sort by email address
$sorted = array();
$sortId = array();
foreach($list as $key=>$row) {
	$sorted[$row['email']] = $row['id'];
}
ksort($sorted);

//get lists
if(isset($chimp)) $clists = $chimp->call('/lists/list',array('apikey'=>$apikey));
if(!isset($clists) || (isset($clists['status']) && $clists['status']=="error")) {
	$lists = false;
} else {
	$lists = $clists['data'];
}

if (!isset($message)) $message = array();

//email
$val = isset($list['email']) ? $list['email'] : "";
$input_email = new inputField( 'Email Address', 'email' );	
$input_email->toolTip('Subscribe this Email address');
$input_email->value( $val );
$input_email->counterWarning(150);
$input_email->counterMax(254);
$input_email->arErr($message);

// set the header varables and create the header
$pageResources ="<link rel='stylesheet' type='text/css' href='{$_config['admin_url']}modules/newsletter/style.css' />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/newsletter/js/subs.js\"></script>
";
$pageInit->createPageTop($pageResources);

?>
	<div class="page_container">
		<div id="h1"><h1>Subscribers</h1></div>
		<div id="info_container">
			<?php
			// add subnav
			$pg = 'subs';	include("includes/subnav.php");
			
			//---------------------------------------Error Banner----------------------------- 
			// create a banner if $message['banner'] exists
			createBanner($message);
			
			if(!empty($sorted)) {
			?>
			<div class='table-scroll'>
			<table class='tblCouponPage' border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th style="text-align:left;">Subscriber Email</th>
				<th style='text-align:center;'>Operation</th>
			</tr>
			
			<?php
				foreach($sorted as $k=>$v) {
					echo "<tr><td style='padding-left:2em;'>{$k}</td>";
					echo "<td style='text-align:center'>
							<a href='' class='edit_sub' rel='{$k}'><img class='tipTop' title='Edit' src='../../images/edit.png' alt='Edit'></a>
							<a  href='' class='delete_sub' rel='{$v}' data-email='{$k}' data-key='{$apikey}'><img class='tipTop' title='Unsubscribe' src='../../images/delete.png' alt='Unsubscribe'></a>
						</td></tr>";
				}
			?>
			</table>
			</div>
			
			<?php
			}
			
			if($lists) {
			?>
			<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post" enctype="application/x-www-form-urlencoded" name="subs_form" id="subs_form" class="form">
				<input type="hidden" name="subscribe" value="1" />
				<!-- subscribe -->
				<h2 class="tiptip toggle" id="subs-toggle" title="Manually Subscribe Someone">Subscribe</h2>
				<div id="subs-toggle-wrap">
				
				<section class="explain">
					<p>Use this form to manually subscribe someone to your mailings.</p>
				</section>
				<?php
					$input_email->createInputField();
				?>
				
				<section class="explain">
					<p>Choose the list(s) for this subscription.</p>
				</section>
				
				<?php
					foreach ($lists as $l){
						$check_options[] = array( 
							'id' 	=> 	$l['id'],
							'title' =>	$l['name'],
							'class' => 	""
						);
					}
					// build the Lists table
					$checked_items = array();
					$check_group_name 		= 'lists';
					$check_group_label 		= 'Lists';
					$check_group_table_id	= 'list_table';
					$check_group_columns 	= 3;
					$check_group_title 		= 'Select the Lists to subscribe this email address to.';
					$as_radio				= false;
					$check_group_class = '';
							
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
				?>
				
				</div><!-- end subscribe -->
				<!-- page buttons -->
				<div class='clearFix' ></div>
				<input name="submit-post" type="hidden" value="submit" />
			</form>	
			<div class="clear1em"></div>
			<a id="submit-btn" class="blue button formbutton" href="#">Save</a>

			<a class="grey button formbutton" id="cancel-btn" href="<?php echo $parent_page; ?>">Cancel</a>
		<?php } else {
				echo "<section class='explain'><p>You must first setup a Mailchimp account and a List in order to submit a subscriber.</p></section>";
			}
		?>
			<div class="clear"></div>
			<hr />
		</div>
	</div>
<?php 
include($_config['admin_includes']."footer.php"); 
?>
