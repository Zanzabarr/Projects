<form action="<?php echo $_POST['referrer']; ?>#content" method="post" enctype="application/x-www-form-urlencoded" name="form" id="signup" class='styled'>
<?php	
$form_errors = isset($_POST['form_errors']) ? $_POST['form_errors'] : array(); 
$list = isset($_POST['list']) ? $_POST['list'] : array(); 

if($_POST['error_msg'] != 'false') echo $_POST['error_msg']; 
?>
	
	<div>
		<span class='error'><?php echo isset($form_errors['first_name']) ? $form_errors['first_name'] : ''; ?></span>
		<label>* First Name:</label> 
		<input type="text" name="first_name" id="first_name" value="<?php echo isset($list['first_name']) ? $list['first_name'] : '' ; ?>" />
	</div>
	
	<div>
		<span class='error'><?php echo isset($form_errors['last_name']) ? $form_errors['last_name'] : ''; ?></span>
		<label>* Last Name:</label> 
		<input type="text" name="last_name" id="last_name" value="<?php echo isset($list['last_name']) ? $list['last_name'] : '' ; ?>" />
	</div>
	<div>
		<span class='error'><?php echo isset($form_errors['mailing_address']) ? $form_errors['mailing_address'] : ''; ?></span>
		<label>* Physical Address:</label> 
		<input type="text" name="mailing_address" id="mailing_address" value="<?php echo isset($list['mailing_address']) ? $list['mailing_address'] : '' ; ?>" />
	</div>
	<div>
		<span class='error'><?php echo isset($form_errors['city']) ? $form_errors['city'] : ''; ?></span>
		<label>* City:</label> 
		<input type="text" name="city" id="city" value="<?php echo isset($list['city']) ? $list['city'] : '' ; ?>" />
	</div>
	<div>
		<span class='error'><?php echo isset($form_errors['country']) ? $form_errors['country'] : ''; ?></span>
		<label>* Country:</label> 
		<input type="text" name="country" id="country" value="<?php echo isset($list['country']) ? $list['country'] : '' ; ?>" />
	</div>
	<div>
		<span class='error'><?php echo isset($form_errors['province_state_region']) ? $form_errors['province_state_region'] : ''; ?></span>
		<label>Province/State:</label> 
		<input type="text" name="province_state_region" id="province_state_region" value="<?php echo isset($list['province_state_region']) ? $list['province_state_region'] : '' ; ?>" />
	</div>
	<div>
		<span class='error'><?php echo isset($form_errors['postal_code']) ? $form_errors['postal_code'] : ''; ?></span>
		<label>Business Postal Code:</label> 
		<input type="text" name="postal_code" id="postal_code" value="<?php echo isset($list['postal_code']) ? $list['postal_code'] : '' ; ?>" />
	</div>
	<div>
		<span class='error'><?php echo isset($form_errors['phone_number']) ? $form_errors['phone_number'] : ''; ?></span>
		<label>* Phone Number:</label> 
		<input type="text" name="phone_number" id="phone_number" value="<?php echo isset($list['phone_number']) ? $list['phone_number'] : '' ; ?>" />
	</div>
	<div>
		<span class='error'><?php echo isset($form_errors['eBulletin']) ? $form_errors['eBulletin'] : ''; ?></span>
		<label>Receive our Newsletter:</label> 
		<input type="checkbox" name="eBulletin" id="eBulletin" value='1'<?php echo isset($list['eBulletin']) && $list['eBulletin'] ? ' checked="checked"' : '' ; ?> />
	</div>	
	<div>
		<span>E-mail address acts as Username when logging in.</span>
		<span class='error'><?php echo isset($form_errors['email']) ? $form_errors['email'] : ''; ?></span>
		<label>* E-Mail</label> 
		<input type="text" name="email" id="email" value="<?php echo isset($list['email']) ? $list['email'] : '' ; ?>" />
	</div>
	
	<div>
		<span class='error'><?php echo isset($form_errors['password']) ? $form_errors['password'] : ''; ?></span>
		<label>* Password:</label> 
		<input type="password" name="password" id="password"  />
	</div>		
	<div>
		<span class='error'><?php echo isset($form_errors['re_pass']) ? $form_errors['re_pass'] : ''; ?></span>
		<label>* Retype Password:</label> 
		<input type="password" name="re_pass" id="re_pass" />
	</div>		

	
    <input name="submit" type="submit" id="submit" value="Signup">
  
</form>