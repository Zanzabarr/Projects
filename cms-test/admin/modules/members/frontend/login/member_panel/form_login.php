<form class="login-form" action="<?php echo $_POST['formActionUrl']; ?>" method="POST">
	<div class="member_panel_notifications">
	<?php
	$errors = isset($_POST['errors']) ? $_POST['errors'] : array();
	if(!empty($errors)) {
		foreach($errors as $error) {
			echo '<p class="error">'.$error.'</p>';
		}
	}
	?>	
	</div><!-- member_panel_notifications -->
	<input type="text" name="login_email" class="email" placeholder="Email" value="<?php echo isset($_POST['login_email']) ? $_POST['login_email'] : ''; ?>" />
	<input type="password" name="password" class="password" placeholder="Password" value="" />
	<input type="hidden" id="tz_offset_1" name="tz_offset">
	<input type="submit" name="submit-login" class="submit-login" value="Login" />
	<div style="clear:both"></div>
</form>
<div style="clear:both"></div>
<script type="text/javascript">

function get_time_zone_offset( ) {
    var current_date = new Date( );
    var gmt_offset = current_date.getTimezoneOffset( ) ;
    return gmt_offset;
}
 
$('#tz_offset').val( get_time_zone_offset( ) );
</script>