<form id="form_login" method="POST" action="?action=login">
	<table>
		<tr>
			<td>
				<input id="username" type="text" name="username" value="<?php if (isset($_POST['username'])) { echo htmlspecialchars($_POST['username']);} else {echo 'username'; } ?>" placeholder="username" onfocus="if(this.value == 'username') { this.value = ''; }" />                    </td>
		</tr>
		<tr>
			<td>
				<input type="password" name="password"  placeholder="password"/>
			</td>
		</tr>
		<tr>
			<td>
				<a style='color:white;' href="forgot_password.php">Forgot your password?</a>
			</td>
		</tr>
		<tr>
			<td>
				<input type="submit" id="submit" name="submit" value="" />
			</td>
		</tr>
	</table>
</form>

<form id="form_forgot" method="POST" action="#">
	<table>
		<tr>
			<td>
				<input id="username" type="text" name="username" value="<?php if (isset($_POST['username'])) { echo htmlspecialchars($_POST['username']);} else {echo 'username'; } ?>" placeholder="username" onfocus="if(this.value == 'username') { this.value = ''; }" />                    </td>
		</tr>
		<tr>
			<td>
				<span style='color:white;'>Enter your username to receive a reset request email.</span>
			</td>
		</tr>
		<tr>
			<td>
				<input id="reset" type="submit"  name="submit" value="" />
			</td>
		</tr>
	</table>
</form>
