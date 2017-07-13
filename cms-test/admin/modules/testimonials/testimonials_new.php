<?php 
// initialize the page
$headerComponents = array();
$headerModule = 'testimonials';
include('../../includes/headerClass.php');
include('inc/functions.php');

$pageInit = new headerClass($headerComponents,$headerModule);

$baseUrl = $_config['admin_url'];

$newTestimonial = false;

if ( array_key_exists('option', $_GET) && ($_GET['option'] == 'create') ) 
{
	$newTestimonial = true;
}
// if this is an edit of an existing testimonial, get the testimonial info
elseif (array_key_exists('testimonialid', $_GET) && is_numeric($_GET['testimonialid']) && ! array_key_exists('option', $_GET) ) {	
	$list = logged_query("SELECT * FROM `testimonials` WHERE `id` = :testid",0,array(":testid" => $_GET['testimonialid'] ));
    if(!isset($list[0]))
	{
		header( "Location: " . $_config['admin_url'] . "modules/testimonials/testimonials.php" );
		exit;
	}
	$list = $list[0];
    $testimonial_id = $_GET['testimonialid'];
}
// if we aren't editing or creating either, we shouldn't be here
elseif (! array_key_exists('option', $_GET) ) 
{
	header( "Location: " . $_config['admin_url'] . "modules/testimonials/testimonials.php" );
	exit;
}	


#==================
# process testimonial info
#==================
if(isset($_POST['submit-testimonial'])) {

	// validate:
	//	pick error type/messages based on if its status is Inactive or Active
	if ($_POST['status'] == 1) // Active: thus error
	{
		$errorMsgType = 'errorMsg';
		$errorType = 'error';
		$errorHeading = 'Active Version';
		$errorMessage = 'all fields must be complete, saved as Inactive';
	}
	else  // Inactive: thus warning
	{
		$errorMsgType = 'warningMsg';
		$errorType = 'warning';
		$errorHeading = 'Inactive Version';
		$errorMessage = 'saved with incomplete fields on page';
	}

	$list = array();

	foreach($_POST as $k=>$v)
	{

		${$k} = trim(htmlspecialchars($v, ENT_QUOTES));
        $list[$k] = ${$k};

	    if (${$k} == '' && $k != 'short_test' && $k != 'submit-testimonial' && $k != 'testimonial_id' && $k != 'testimonialid' && $k != 'business') {$message['inline'][$k] = array ('type' => $errorMsgType, 'msg' => 'Required field');}
    }
            
	// if an error was found, create the error banner
	if ($errorsExist = count(isset($message['inline']) ? $message['inline'] : array()))
	{
		$message['banner'] = array ('heading' => $errorHeading, 'message' => $errorMessage, 'type' => $errorType);
		$status = $list['status'] = 0; // saving as Inactive
	}
	else // set the success message
	{
		$message['banner'] = array ('heading' => 'Successfully Saved', 'type' => 'success');
	}

    // save even if errors exist: but save as inactive
	if (array_key_exists('option', $_GET) && ($_GET['option'] == 'create'))
	{   
        // insert testimonial
		$success = logged_query("INSERT INTO `testimonials` (`name`, `business`, `short_test`, `full_test`, `status`, `date`) 
VALUES (:name, :business, :short_test, :full_test, :status, NOW());",0,array(
			":name" => $name,
			":business" => $business,
			":short_test" => $short_test,
			":full_test" => $full_test,
			":status" => $status
		)); 
		if($success) 
		{	
			$testimonial_id = $_config['db']->getLastInsertId();    
			$newTestimonial= false;
		}
		// banners: if there was an error, overwrite the previously set success message
		else $message['banner'] = array ('heading' => 'Error Saving Testimonial', 'message' => 'there was an error writing to the database', 'type' => 'error' );
	}
	elseif (array_key_exists('option', $_GET) && ($_GET['option'] == 'edit'))
	{
        $testimonial_id = $_GET['testimonialid'];

        // update testimonial
		$success = logged_query("UPDATE `testimonials` SET 
	`name` = :name,
    `business` = :business, 
    `short_test` = :short_test, 
    `full_test` = :full_test, 
	`status` = :status,
    `date` = NOW()
WHERE `id` = :testimonial_id;",0,array(
			":name" => $name,
			":business" => $business,
			":short_test" => $short_test,
			":full_test" => $full_test,
			":status" => $status,
			":testimonial_id" => $testimonial_id
		));
		
		// banners
		// banners: if there was an error, overwrite the previously set success message
		if (!$success) $message['banner'] = array ('heading' => 'Error Saving Testimonial', 'message' => 'there was an error writing to the database', 'type' => 'error' );
	}
}	

if (! isset($message)) $message=array();

// name
$input_name = new inputField('Name', 'name');
$input_name->toolTip('Name for the testimonial');
$input_name->value(isset($list['name']) ? $list['name'] : '');
$input_name->counterMax(100);
$input_name->size('small');
$input_name->arErr($message);


// business
$input_business = new inputField('Business', 'business');
$input_business->toolTip('Business for the testimonial');
$input_business->value(isset($list['business']) ? $list['business'] : '');
$input_business->size('small');
$input_business->arErr($message);

// status
$input_status = new inputField( 'Status', 'status' );	
$input_status->toolTip('An Inactive version can have blank fields. An Active version must have all fields completed.');
$input_status->type('select');
$input_status->selected(isset($list['status']) ? $list['status'] : 1);
$input_status->option( 0, 'Inactive' );
$input_status->option( 1, 'Active' );
$input_status->arErr($message);

$pageResources ="
<script type=\"text/javascript\" src=\"".$_config['admin_url']."modules/testimonials/js/testimonials_new.js\"></script>
<link rel=\"stylesheet\" type=\"text/css\" href=\"".$_config['admin_url']."modules/testimonials/style.css\" />
";
$pageInit->createPageTop($pageResources);
 
 ?>
 <div class="page_container">
    <div id="h1">
        <?php if($newTestimonial) {
            echo '<h1>Add Testimonial</h1>';
        } else {
            echo '<h1>Edit Testimonial</h1>';
        } ?>
    </div>
    <div id="info_container">
		<?php 
		// ----------------------------------------subnav--------------------------------
		$selectedTestimonials = 'tabSel';
		include("inc/subnav.php"); 
		echo '<hr />';
		//---------------------------------------Error Banner----------------------------- 
		// create a banner if $message['banner'] exists
		createBanner($message); 
		
		$parms = $newTestimonial ? '?option=create' : "?testimonialid={$testimonial_id}&option=edit";
		?>
		<form action="testimonials_new.php<?php echo $parms; ?>" method="post" enctype="application/x-www-form-urlencoded" name="addTestimonial" id="addTestimonial" class="form">
            <input type="hidden" name="testimonial_id" id="testimonial_id" value="<?php echo isset($testimonial_id) ? $testimonial_id : ''; ?>" /> 
	
    		<!--  properties_wrap -->
			<h2 id="prop-toggle" class="tiptip toggle" title="Set properties for the testimonial.">Testimonial Properties</h2><br />
			<div id="prop-toggle-wrap">
			<?php
				$input_name->createInputField();
                $input_business->createInputField();
				$input_status->createInputField();
			?>
            </div><!-- end prop-toggle-wrap -->	
             <!-- content area -->
			<h2 id="content-toggle" class="tiptip toggle" title="The actual testimonials.">Testimonials Content</h2>
            <div id="content-toggle-wrap">
			 
            <div id="short_test_container">
            <!-- short description -->
            <label class="tipRight" title="Short version of the testimonial">Short Testimonial</label>
			<?php if (isset($message['inline']) && array_key_exists('short_test', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['short_test']['type'] ;?>"><?php echo $message['inline']['short_test']['msg'] ;?> </span>
			<?php endif; ?>
			<br />
	
				<textarea class="mceEditor" name="short_test" id="short_test"><?php echo htmlspecialchars_decode(isset($list['short_test']) ? $list['short_test'] : ''); ?></textarea>
            <br />
            </div><!-- short_test_container -->

            <!-- full description -->
            <label class="tipRight" title="Full version of the testimonial ">Full Testimonial</label>
            <?php if (isset($message['inline']) && array_key_exists('full_test', $message['inline'])) :?>
				<span class="<?php echo $message['inline']['full_test']['type'] ;?>"><?php echo $message['inline']['full_test']['msg'] ;?> </span>
			<?php endif; ?>
			<br />

				<textarea class="mceEditor" name="full_test" id="content"><?php echo htmlspecialchars_decode(isset($list['full_test']) ? $list['full_test'] : ''); ?></textarea>
			</div>
			<!-- end content area -->

			<!-- page buttons -->
			<div class='clearFix' ></div>
			<input name="submit-testimonial" type="hidden" />
		</form>	
		<a id="submit-btn" class="blue button" href="#">Save</a>

		<a class="grey button" id="cancel-btn" href="testimonials.php">Cancel</a>
		<div class='clearFix' ></div> 
		<!-- end page buttons -->
	
    </div>
</div>	
	

<?php 
include($_config['admin_includes'] . "footer.php"); 
?>
