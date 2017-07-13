<form id="password-change-form" method="POST" action="<?php echo $_POST['referrer'] . '?change-password='; ?>">
	<div class="input_container">
		<label><strong>Password:</strong></label>
		<input type="password" name="password" id="password" /> 
	</div>
	<div class="input_container">
		<label><strong>Confirm Password:</strong></label>
		<input type="password" name="cfm_password" id="cfm_password" /> 
	</div>
	<input type="submit" name="submit-password-change" id="submit-password-change" value="Change Password" />
</form>