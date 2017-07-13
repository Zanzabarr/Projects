<?php
include_once('functions.php');
// make sure we have option data and signup data
$nav_options = isset($memberPreferences) ? $memberPreferences : getMembersOptions();
$nav_signup_options = getMembersSignupMessage();

/*
<style>.sidebar {display:none;}</style>
*/
?>
<script type='text/javascript'>
function showPopup(url) {
newwindow=window.open(url,'_blank','width=700,resizable=yes,toolbar=yes,titlebar=yes,scrollbars=yes,status=yes,left=0,top=0');
if (window.focus) {newwindow.focus()}
}
(function($){

	// Creating a jQuery plugin:

	$.generateFile = function(options){
		options = options || {};

		if(!options.script || !options.filename || !options.content){
			throw new Error("Please enter all the required config options!");
		}

		// Creating a 1 by 1 px invisible iframe:

		var iframe = $('<iframe>',{
			width:1,
			height:1,
			frameborder:0,
			css:{
				display:'none'
			}
		}).appendTo('body');

		var formHTML = '<form action="" method="post">'+
			'<input type="hidden" name="filename" />'+
			'<input type="hidden" name="content" />'+
			'</form>';

		// Giving IE a chance to build the DOM in
		// the iframe with a short timeout:

		setTimeout(function(){

			// The body element of the iframe document:

			var body = (iframe.prop('contentDocument') !== undefined) ?
							iframe.prop('contentDocument').body :
							iframe.prop('document').body;	// IE

			body = $(body);

			// Adding the form to the body:
			body.html(formHTML);

			var form = body.find('form');

			form.attr('action',options.script);
			form.find('input[name=filename]').val(options.filename);
			form.find('input[name=content]').val(options.content);

			// Submitting the form to download.php. This will
			// cause the file download dialog box to appear.
			form.submit();
		},50);
	};

})(jQuery);

$(document).ready(function(){

	$('#download').click(function(e){
e.preventDefault();
		$.generateFile({
			filename	: 'member_label.csv',
			content		: 'dummy',
			script		: '<?php echo $_config['admin_url']; ?>modules/members/download.php'
		});
	});

});
</script>
<?php 
	$selectedMembers = isset($selectedMembers) ? $selectedMembers : '';
	$selectedHome = isset($selectedHome) ? $selectedHome : '';
	$selectedFolders = isset($selectedFolders) ? $selectedFolders : '';
	$selectedOpts = isset($selectedOpts) ? $selectedOpts : '';
	$selectedSignup = isset($selectedSignup) ? $selectedSignup : '';
	$selectedPaypal = isset($selectedPaypal) ? $selectedPaypal : '';
	$selectedTitle = "Online Signup Information.";
	$selectedStyle = '';
	if(!$nav_signup_options['status'])
	{
		$selectedTitle .= "<br>WARNING: Signups are currently Disabled. Correct errors on this form to enable Online Signup";
		$selectedStyle = 'style="color:red"';
	}
	
	// need lots of work for paypal here
?>
<div id="membersnav">
    <ul>
        <li><a href="members.php" class='<?php echo $selectedMembers; ?>' >MEMBERS</a></li>
		<?php if($nav_options['members_front']) : ?>
		<li><a class="<?php echo $selectedHome; ?> tipTop" title="Write a description about the list of members." href="members_home.php">Homepage</a></li>
		<?php endif;
		if($nav_options['online_signup']) : ?>
		<li><a class="<?php echo $selectedSignup; ?> tipTop" title="<?php echo $selectedTitle; ?>" <?php echo $selectedStyle; ?> href="signup.php">Signup</a></li>
		<?php endif; 
		if($nav_options['pay_signup']) : ?>
		<li><a class="<?php echo $selectedPaypal; ?> tipTop" title="Select your Paypal Options" href="members_paypal.php">Paypal</a></li>
		<?php endif; ?>
	</ul>
	<?php if($nav_options['ftp_front']) : ?>
	<ul>	
		<li><a href="folders.php" class='<?php echo $selectedFolders; ?>' >FTP</a></li>
	</ul>
	<?php endif; ?>
	<ul>
		<li><a href="preferences.php" class='<?php echo $selectedOpts; ?>' >Preferences</a></li>
    </ul>
	<div id='membersnavbtn'>
		<a class="blue button tipTop" title='Add a member to the list of members.' href="members_new.php?option=create">Add Member</a>
		
	<?php if ($nav_options['ftp_front']) :	?>
		<a id="newFolder" class="blue button tipTop" title="Create a new FTP folder." href="folders.php?new_folder=<?php echo rand_string(15); ?>#users-list">Create Folder</a>
	<?php endif; ?>
		<a class='green button tipTop' target='_blank' onClick='showPopup(this.href);return(false);' title='View a printable list of members.' 
			href='print_list.php'>Printable Member List</a>
		<a class='green button tipTop' id="download"title="Download member_label.csv: for use with label makers such as Mail Merge in Word."  
			href='#' >Download Label</a>

		<div class='clearFix'></div>
	</div>	
</div>
