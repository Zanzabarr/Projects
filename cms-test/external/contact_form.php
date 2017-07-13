<?php $errors = isset($_POST['errors'])?$_POST['errors']: '';
	$post = isset($_POST['post'])?$_POST['post']: '';
	$sent = isset($_POST['sent'])?$_POST['sent']: '';
	$referrer = $_POST['referrer'];
?> 
<form action="<?php echo $referrer; ?>" method="post" enctype="application/x-www-form-urlencoded" >
	<div id="contact_form"><label>LEAVE US A MESSAGE</label></div>
	<input type="hidden" name="emailform">
	<table>
		<tr>
			<td><label for="name">NAME</label></td>
			<td><input <?php echo isset($errors['name']) ? 'class="formerror"'  : ""; ?> type="text" name="name" value="<?php echo isset($post['name']) && !$sent ? trim($post['name']) : '' ; ?>" required /></td>
		</tr>
		<tr>
			<td><label for="telephone">PHONE</label></td>
			<td><input <?php echo isset($errors['telephone']) ? 'class="formerror"'  : ""; ?> type="text" name="telephone" value="<?php echo isset($post['telephone']) && !$sent ? trim($post['telephone']) : '' ; ?>" required /></td>
		</tr>
		<tr>
			<td><label for="email">EMAIL</label></td>
			<td><input <?php echo isset($errors['email']) ? 'class="formerror"'  : ""; ?> type="text" name="email" value="<?php echo isset($post['email']) && !$sent ? trim($post['email']) : '' ; ?>" required /></td>
		</tr>
		<tr>
			<td style="vertical-align:top;"><label for="comments" style="vertical-align:top;">HOW CAN WE HELP YOU?</label></td>
			<td><textarea name="comments"><?php echo isset($post['comments']) && !$sent ? trim($post['comments']) : '' ; ?></textarea></td>
		</tr>
		<tr>
			<td colspan=2><input type="submit" name="submit" id="button" value="Submit Message" /></td>
		</tr>
	</table>
</form>
