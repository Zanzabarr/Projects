<?php
$member_data = $_POST;
?>
    <form id="contact-info-form" method="POST" action="<?php echo $member_data['referrer'] . '?contact-info=&edit='; ?>">
        <div class="input_container">
        	<label>First Name:</label>
        	<input type="text" id="first_name" name="first_name" value="<?php echo isset($member_data['first_name']) ?$member_data['first_name']: ''; ?>" />
        </div>
        
        <div class="input_container">
        	<label>Last Name:</label>
        	<input type="text" id="last_name" name="last_name" value="<?php echo isset($member_data['last_name']) ? $member_data['last_name']: ''; ?>" />
        </div>
        
        <div class="input_container">
        	<label>Phone Number:</label>
        	<input type="text" id="phone_number" name="phone_number" value="<?php echo isset($member_data['phone_number']) ? $member_data['phone_number']: ''; ?>" />
        </div>
        
        <div class="input_container">
        	<label>Mailing Address:</label>
        	<input type="text" id="mailing_address" name="mailing_address" value="<?php echo isset($member_data['mailing_address']) ? $member_data['mailing_address'] : ''; ?>" />
        </div>
        
        <div class="input_container">
        	<label>City:</label>
        	<input type="text" id="city" name="city" value="<?php echo isset($member_data['city']) ? $member_data['city'] : ''; ?>" />
        </div>
        
        <div class="input_container">
        	<label>Province/State/<br />Region:</label>
        	<input type="text" id="province_state_region" name="province_state_region" value="<?php echo isset($member_data['province_state_region']) ? $member_data['province_state_region'] : ''; ?>" />
        </div>
        
        <div class="input_container">
        	<label>Country:</label>
        	<input type="text" id="country" name="country" value="<?php echo isset($member_data['country']) ? $member_data['country'] : ''; ?>" />
        </div>
        
        <div class="input_container">
        	<label>Postal Code:</label>
        	<input type="text" id="postal_code" name="postal_code" value="<?php echo isset($member_data['postal_code']) ? $member_data['postal_code']: ''; ?>" />
        </div>
        
        
        <!--<div class="input_container">
        	<label>eBulletin</label>
        	<input type="checkbox" id="eBulletin" name="eBulletin" value="1" <?php //echo ($member_data['eBulletin'] == 1) ? 'checked="checked"': ''; ?> />
        </div>-->
        
        <div class="input_container">
        	<label>List Name</label>
        	<input type="checkbox" id="list_name" name="list_name" value="1" <?php echo ($member_data['list_name'] == 1) ? 'checked="checked"': ''; ?> />
        </div>
        
        <div class="input_container">
        	<label>List Address</label>
        	<input type="checkbox" id="list_address" name="list_address" value="1" <?php echo ($member_data['list_address'] == 1) ? 'checked="checked"': ''; ?> />
        </div>
        
        <div class="input_container">
        	<label>List Phone</label>
        	<input type="checkbox" id="list_phone" name="list_phone" value="1" <?php echo ($member_data['list_phone'] == 1) ? 'checked="checked"': ''; ?> />
        </div>
        
        <div class="input_container">
        	<label>List Email</label>
        	<input type="checkbox" id="list_email" name="list_email" value="1" <?php echo ($member_data['list_email'] == 1) ? 'checked="checked"': ''; ?> />
    	</div>

        <input type="submit" id="submit-contact-info" name="submit-contact-info" value="Submit" />
    </form> 