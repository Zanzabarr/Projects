<form id="ftp_login" method="POST" action="<?php echo $_POST['formActionUrl']; ?>" >  
	<?php
	$errors = isset($_POST['login_errors']) && is_array($_POST['login_errors']) ? $_POST['login_errors'] : array();
	if(!empty($errors)) {
		foreach($errors as $error) {
			echo "<div class='error'>{$error}</div>";
		}
	}
	?>	
	<table>
		<tr>
			<td>
				<input type="text" name="username" value="<?php if (isset($_POST['username'])) { echo htmlspecialchars($_POST['username']);} else {echo 'username'; } ?>" placeholder="username" onfocus="if(this.value == 'username') { this.value = ''; }" />
			</td>
		</tr>
		<tr>
			<td>
				<input type="password" name="password"  placeholder="password"/>
			</td>
		</tr>
		<tr>
			<td style="float:left">
				<input type="submit" name="ftp_member_login_full" value="Submit" />
				<input type="hidden" id="tz_offset" name="tz_offset">
			</td>
		</tr>
	</table>
</form>
<script type="text/javascript">

function get_time_zone_offset( ) {
    var current_date = new Date( );
    var gmt_offset = current_date.getTimezoneOffset( ) ;
    return gmt_offset;
}
 
$('#tz_offset').val( get_time_zone_offset( ) );
</script>