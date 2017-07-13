<form id="reset_form" method="POST" action="<?php echo $_POST['formActionUrl']; ?>#content" >  
	<?php
	$msg= isset($_POST['msg']) ? $_POST['msg'] : '';
	if($msg) {
		echo "<div class='error'>{$msg}</div>";
	}
	?>	
	<table>
		<tr>
			<td>
				<input type="text" name="username" value="<?php if (isset($_POST['username'])) { echo htmlspecialchars($_POST['username']);} else {echo 'username'; } ?>" placeholder="username" onfocus="if(this.value == 'username') { this.value = ''; }" />
			</td>
		</tr>
		<tr>
			<td style="float:left">
				<input type="submit" name="ftp_member_login_full" value="Submit" />
			</td>
		</tr>
	</table>
</form>