<?php $errors = isset($_POST['errors'])?$_POST['errors']: '';
	$post = isset($_POST['post'])?$_POST['post']: '';
	$sent = isset($_POST['sent'])?$_POST['sent']: '';
	$referrer = $_POST['referrer'];
?> 


<form action="<?php echo $referrer; ?>" method="post" enctype="application/x-www-form-urlencoded" >
	
	<input type="hidden" name="emailform">
	<table>
	
	<tr>
			
			<td><input <?php echo isset($errors['name']) ? 'class="formerror"'  : ""; ?> type="text" name="name" value="<?php echo isset($post['name']) && !$sent ? trim($post['name']) : '' ; ?>" placeholder="Name" required /></td>
		</tr>
				
		<tr>
			
			<td><input <?php echo isset($errors['email']) ? 'class="formerror"'  : ""; ?> type="text" name="email" value="<?php echo isset($post['email']) && !$sent ? trim($post['email']) : '' ; ?>" placeholder="Email Address" required /></td>
		</tr>
		</table>
		<input type="submit" name="submit" id="signupbutton" value="SEND" />
		
	
</form>
