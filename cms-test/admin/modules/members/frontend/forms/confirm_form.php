<form id="confirm_form" method="POST" action="<?php echo $_POST['formActionUrl']; ?>" >  
	<?php
	$form_errors = isset($_POST['form_errors']) ? $_POST['form_errors'] : array(); 
	$list = isset($_POST['list']) ? $_POST['list'] : array(); 
	
	
	if(!empty($form_errors)) {
		echo "<div class='error'>";
		foreach($form_errors as $error) {
			echo "<p>{$error}</p>";
		}
		echo "</div>";
	}
	?>	
	<table>
		<tr>
			<td>
				<input type="text" name="username" value="<?php if (isset($list['username'])) { echo htmlspecialchars($list['username']);} else {echo 'username'; } ?>" placeholder="username" onfocus="if(this.value == 'username') { this.value = ''; }" />
			</td>
		</tr>
		<tr>
			<td>
				<input type="password" name="password"  placeholder="password"/>
			</td>
		</tr>
		<tr>
			<td>
				<input type="text" name="confirmation_code" value="<?php if (isset($list['confirmation_code'])) { echo htmlspecialchars($list['confirmation_code']);} else {echo 'confirmation code'; } ?>" placeholder="confirmation code" onfocus="if(this.value == 'confirmation code') { this.value = ''; }" />
			</td>
		</tr>
		<tr>
			<td style="float:left">
				<input type="submit" name="submit" value="Submit" />
			</td>
		</tr>
	</table>
</form>