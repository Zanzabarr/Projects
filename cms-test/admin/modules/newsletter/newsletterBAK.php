<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'newsletter';
include('../../includes/headerClass.php');
include ($_config['admin_includes'].'html2text.php');


// set mysql date functions to utc
logged_query("SET @@session.time_zone = '+0:00'");


$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];

// first, get rid of all attachments if this isn't a fresh page.
//if(! isset($_POST['action']) || $_POST['action'] != 'send_newsletter') delete_all_attachments(); 
// CHANGE?: don't get rid of attachments, we need to hold on to these even after they are sent

$attachments = array();

$temp_recips = array();

#==================
# process news_item info
#==================
// ok here is a send action: only available if previously saved
if(isset($_POST['action']) && $_POST['action'] == 'send_newsletter') {



	$list = array();

	foreach($_POST as $k=>$v)
	{
			if($k == 'content') ${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
			elseif($k == 'recipient_type') { ${$k} = $v; }
			else ${$k} = trim(mysql_real_escape_string($v));
			$list[$k] = $v;
			if (${$k} == '' ) {$message['inline'][$k] = array ('type' => 'errorMsg', 'msg' => 'Required field');}
		
	}
	$list['content'] = $list['content'];
	

	$errorsExist = count(isset($message['inline']) ? $message['inline'] : array());

	// get the To data
	$query = array();
	$curDate = date( "Y-m-d H:i:s", time() );
	if ( isset($_POST['recipient_type']) && 
		( 	in_array('active_members', $_POST['recipient_type'] ) 
		 || in_array('expired_members', $_POST['recipient_type'] ) 
		 || in_array('newsletter', $_POST['recipient_type'] )
		 || in_array('admin', $_POST['recipient_type'] ) 
		 || in_array('additional', $_POST['recipient_type'] ) 
		)
	)
	{
		$toType = $_POST['recipient_type'];
		if (in_array('active_members', $toType)  && $_config['newsletter']['recipient_type']['active_members'] )
		{
			$query[] = "
				SELECT first_name as name, email
				FROM members
				WHERE `id` > 0
				  AND `status` > 0
				  AND `email` > ''
				  AND `email` NOT LIKE '%NOEMAIL.COM'
				  AND '{$curDate}' <= `membership_expiry`
				  AND `payment_status` > 0
				  	
			";
		}
		if ( in_array('newsletter', $toType) && $_config['newsletter']['recipient_type']['newsletter'] )
		{
			$query[] = "
				SELECT name, email
				FROM newsletter_recipient
				WHERE `id` > 0
				  AND `status` > 0 
				  AND `email` > ''
				  AND `email` NOT LIKE '%NOEMAIL.COM'
			";
		}
		if ( in_array('newsletter', $toType) && $_config['newsletter']['recipient_type']['members_newsletter'] )
		{
			$query[] = "
				SELECT first_name as name, email
				FROM members
				WHERE `id` > 0
				  AND `status` > 0 
				  AND `email` > ''
				  AND `email` NOT LIKE '%NOEMAIL.COM'
				  AND `eBulletin` > 0
			";
		}
		if (in_array('expired_members', $toType) && $_config['newsletter']['recipient_type']['expired_members'] )
		{
			$query[] = "
				SELECT first_name as name, email
				FROM members
				WHERE `id` > 0
				  AND `status` > 0
				  AND `email` > ''
				  AND `email` NOT LIKE '%NOEMAIL.COM'
				  AND ('{$curDate}' > `membership_expiry`
						OR `payment_status` = 0
				  )		
				  
			";
			
		}
		if (in_array('admin', $toType) && $_config['newsletter']['recipient_type']['admin'] )
		{
			$query[] = "
				SELECT first_name as name, email
				FROM auth_users
				WHERE `user_id` > 0
				  AND `email` > ''
			";
		}		
		if (in_array('additional', $toType) && $_config['newsletter']['recipient_type']['additional'] )
		{
			$query[] = "SELECT `name`, `email` FROM `newsletter_tmp_recip`";
		}
	}

	$arTo = array();
	if (is_array($query) ) : foreach ($query as $query_bit)
		$arTo = array_merge($arTo, logged_query_assoc_array( $query_bit, 'email') );
	endif;
	//$arTo = array();
	 

	if ( isset($_config['newsletter']['test_addresses']) )
	{
		$arTo = $_config['newsletter']['test_addresses'];
		//var_dump($arTo);
		//die();
	}

	if ( count($arTo) == 0 ) 
	{
		$errorsExist = true;
	}

	if (!$errorsExist)
	{
		$successCount = 0;
		foreach ($arTo as $to) 
		{
			$success = send_newsletter($to, $subject, $content, $attachments);
			if (! $success)
			{
				$faildDelivery[] = $to;
			} else {
				$successCount++;
			}
			
		}
		if (isset($failedDelivery))
		{	
			$errorsExist = true;
			$msg = count($failedDelivery) . " undeliverable newsletters.";
						
			if ($successCount)
			{
				$type = 'warning';
				$msg .= "<br>{$successCount} delivered.";
			} else $type = 'error';	
			
		}
	} else{
		$msg = 'Please complete required fields.';
		$type = 'error';
	}
	
	// if an error was found, create the error banner
	if ($errorsExist)
	{
	
		if (count($arTo) == 0 || count($query) == 0 ) 
		{
			$tmp_msg = count($query) == 0 ? 'At least one recipient type must be selected' : "Currently, no members have chosen to receive Newsletters";
			$message['banner'] = array ('heading' => 'No Recipients', 'message' => $tmp_msg , 'type' => 'warning');
			unset($list);
			delete_all_attachments();
			$attachments = array();
		} 
		else
		{
			$message['banner'] = array ('heading' => 'Error Sending Newsletter', 'message' => $msg, 'type' => $type);
		}
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Success', 'message' => $successCount. " Newsletters Delivered" , 'type' => 'success');
		unset($list);
		delete_all_attachments();
		$attachments = array();
	}


}	

// save the newsletter content and subject (neither field is required for saving)
/*
if(isset($_GET['action']) && $_GET['action'] == 'edit') {
	// find the newsletter id (create one if not already set)
	if(! isset($_POST['id']) ) $newsletter_id = save_blank();
	else $newsletter_id = is_pos_int($_POST['id']) ? $_POST['id'] : false;
	
	if ($newsletter_id === false) 
	{
		$message['banner'] = array ('heading' => 'Newsletter Not Found', 'message' => 'Could not save newsletter', 'type' => 'error');
	}
	
	
}
*/
// set message array to empty if no errors
if (! isset($message)) $message=array();

// news_item title
$input_subject = new inputField('Subject', 'subject');
$input_subject->toolTip('Subject line in the Newsletter Email');
$input_subject->value(htmlspecialchars_decode(isset($list['subject']) ? $list['subject'] : '', ENT_QUOTES));
$input_subject->counterMax(100);
$input_subject->arErr($message);
?>

<?php
$pageResources ="
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/newsletter/style.css\" />
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/newsletter/js/newsletter.js\"></script>
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div id="news_item_new_page_container" class="page_container">
    <div id="h1"><h1>Newsletter</h1></div>
    <div id="info_container">
	<?php /*		// no subnav for this site
		$pg = 'main';	include("includes/subnav.php");
		
		echo "<hr>";
		*/  
		// ----------------------------------------subnav--------------------------------

		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
            echo '<div id="newsletter_banner">';
            createBanner($message); 
            echo '</div>';


		?>
		<form action="newsletter.php" method="post" enctype="multipart/form-data"  id="send_newsletter" class="form">
            <input type="hidden" name="action" value="send_newsletter" >
			<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Information about the News Item.">News Item Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_subject->createInputfield();
			?>
			<div class="input_wrap">
						<label>Recipients</label>
						<div class="input_inner">
						<?php
						if ( isset($errorsExist) && $errorsExist && isset($_POST['recipient_type']))
						{
							$checkedItem = $_POST['recipient_type'];
						} else $checkedItem[] = "newsletter";
						

						if (   $_config['newsletter']['recipient_type']['newsletter'] 
							|| $_config['newsletter']['recipient_type']['members_newsletter']
							) :
							$checked = in_array('newsletter', $checkedItem) ? 'checked="checked"': '';?>
							<input type="checkbox" class="no_counter" value="newsletter" name="recipient_type[]" <?php echo $checked; ?> /> Newsletter Recipients<br />
						<?php 
						endif;
						if ($_config['newsletter']['recipient_type']['admin']) :
							$checked = in_array('admin', $checkedItem) ? 'checked="checked"': '';?>
							<input type="checkbox" class="no_counter" value="admin" name="recipient_type[]" <?php echo $checked; ?> /> Administrators<br /> 
						<?php 
						endif;
						if ($_config['newsletter']['recipient_type']['active_members']) :
							$checked = in_array('active_members', $checkedItem) ? 'checked="checked"': '';?>	
							<input type="checkbox" class="no_counter" value="active_members" name="recipient_type[]" <?php echo $checked; ?> /> Active Members<br />  
						<?php 
						endif;
						if ($_config['newsletter']['recipient_type']['expired_members']) :
							$checked = in_array('expired_members', $checkedItem) ? 'checked="checked"': '';?>
							<input type="checkbox" class="no_counter" value="expired_members" name="recipient_type[]" <?php echo $checked; ?> /> Expired Members <br /> 
						<?php endif; 
						if ($_config['newsletter']['recipient_type']['additional']) :
							$checked = in_array('additional', $checkedItem) ? 'checked="checked"': '';?>	
							<input type="checkbox" class="no_counter" id='additional' value="additional" name="recipient_type[]" <?php echo $checked; ?> /> Additional Recipients <br /> 
							
							<div id="add-box" >
								<div id="add_recip" class="clearAfter">
									<input type="text" id="add-email" placeholder="Email">
									<input type="text" id="add-name" placeholder="Name">
									<input type="submit" id="add-submit" title="Add to Temporary Recipients" value=''>
								</div>
								<div id='add_recip_msg'  style="width:100%;margin:4px;">
									
								</div>
								
								<div id="recip-list">
								<?php foreach ($temp_recips as $recip) echo build_recip_row($recip); ?>	
								</div>
								<div id="clear-list">Clear All</div>
							</div>
						<?php endif;?> 
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
				<textarea class="mceEditor" name="content"><?php echo htmlspecialchars_decode(isset($list['content']) ? $list['content'] : ''); ?></textarea>
			</div>
			<!-- end content area -->
			<div class='clearFix' ></div>
		</form>	

<?php // --------------------------- include the image gallery ---------------------------------

			// $uploadPage and uploadPageType must be set before including uploads (they are page_id & page_type in tables: pictures & uploads
			$useFullPath = true;
			$uploadPage = 1;
			$uploadPageType = 'newsletter';
			include_once("{$_config['components']}uploading/uploads.php"); 

?>			
		<div class='clearFix' ></div>
		<h2 class="tiptip toggle" id="attach-toggle" title="Add and remove images available for this page." >Attachments</h2>
		<div id="attach-toggle-wrap">	
			<form id="attachUploader_form" action="<?php echo $_config['admin_url']; ?>modules/newsletter/ajax/ajax.php" method="post" enctype="multipart/form-data">
				
				<div class='input_wrap'>
					<label class="tipRight" title="Upload files for attachment." >Attach Filename</label>
					<div class='input_inner'>
						<div class='message_wrap' style="width:260px;">
							<span id='attach_msg' <?php echo isset($imageErr) ? "class='errorMsg'" : (isset($imageSuccess) ? "class='successMsg'" :''); ?>><?php echo isset($imageErr) ? $imageErr : (isset($imageSuccess) ? $imageSuccess : '' ); ?></span>
						</div>
						<input type="hidden" name="max_file_Mb" id="max_attach_Mb" value="<?php echo $_config['upload_max_file_size_Mb'];?>" />
						<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $_config['upload_max_file_size'];?>" />
						<?php  if (using_ie()) : ?>
						<input type='file' name='upload_file' style='width:180px;'>
						<?php else : ?>
						<div id="fake-att-wrap" style='position:relative;'>
							<div id='fake-att'>Click to Select a File</div>
							<input type='file' name='upload_file' id="imageUploader_attach" style='position: relative;opacity:0; z-index:2;' />
						</div><br />
						<?php endif; ?>
					</div>
				</div>	
				<div class='clearFix'></div>
				
			
				
				
				
				<div class='input_wrap'>
					<label class="tipRight" title="Files attached to this Newsletter" >Attached Files</label>
					<div class="input_inner">
						<div id="attached_box" >
<?php
						if ( $attachments && count($attachments) > 0 ) : foreach ($attachments as $attachment) :
?>
							<div class="attached_row"><?php echo $attachment['filename']; ?>
								<a class='file-del' rel='<?php echo $attachment['id']; ?>'><img alt='Delete' src='../../images/delete.png'></a>
							</div>
						<?php endforeach; else: ?>
							<div class="attached_row" id="default_attached_row">No Attachments</div>
						<?php endif; ?>
						</div>
					</div>
				</div>	
				<div class='clearFix'></div>

				<a id="upLoadAttBtn" class="blue button" href="#">Attach</a>
				<input type="hidden" id="attach_page_id" name="page_id" value="tmp" />
				<input type="hidden" id="attach_page_type" name="page_type" value="attachment" />
				<input type="hidden" id="attach_admin_url" name="admin_url" value="<?php echo $_config['admin_url'] ?>" />
				<input type="hidden" name="option" value="upload" />
				<iframe id="attach_iframe" name="attach_iframe" src=""></iframe>
			</form>
		</div>
		<br style="clear:both" />
		<hr />
		
		<!-- page buttons -->
		<a id="submit-btn" class="blue button" href="#">Send</a>

		<a class="grey button" id="cancel-btn" href="newsletter.php">Cancel</a>
		<div class='clearFix' ></div> 
	</div>
</div>	
	

<?php 
include($_config['admin_includes'] . "footer.php"); 
