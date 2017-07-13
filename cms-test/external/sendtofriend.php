<?php
if(isset($_GET['ref'])) {
	include('../includes/config.php');
}
?>
<style>
#friendContainer {
	font-family:"Roboto";
	text-align:center;
}
#friendContainer table {
	margin:0 auto;
	width:90%;
	text-align:left;
}
#friendContainer table td {
	padding:4px;
}
#formFriend input[type=text], #formFriend input[type=email] {
	width:100%;
	background:#fff;
	padding:4px;
	border-radius:6px;
	border:none;
}
#formFriend .shoppingbutton {
	height:auto;
	cursor:pointer;
}
</style>
<div id='friendContainer' style='width:100%;'>
	<span style='text-align:center;display:block;'><h1>SHARE ITEM WITH A FRIEND</h1></span>
	<form id="formFriend" action="external/processfriend.php" method="post">
		<input type='hidden' name='friendsubmit' value = 'friendsubmit' />
		<input type='hidden' name='ref' value = '<?php echo $_GET['ref']; ?>' />
		<table>
		<tr><td style='width:40%;'><label for=from_name>Your Name:</label></td>
		<td><input type="text" name="from_name" required /></td></tr>
		<tr><td><label for=from_email>Your Email:</label></td>
		<td><input type="email" name="from_email" required /></td></tr>
		<tr><td><label for=to_email>Friend's Email:</label></td>
		<td><input type="email" name="to_email" required /></td></tr>
		<tr><td colspan=2><button class="shoppingbutton" id="friendsubmit" value="Send to Friend">Send to Friend</button></td></tr>
		</table>
		
	</form>
</div>
<script>
$(document).ready( function() {
	$('#friendsubmit').click( function() {
		$(this).replaceWith("<img src='admin/images/loader_light_blue.gif' style='float:right;' />");
		$.ajax({
			url: $('#formFriend').attr("action"),
			type: 'POST',
			dataType: 'html',
			data: $('#formFriend').serialize(),
			success: function(data, textStatus, xhr) {
				$.fancybox({
					'content': data,
					'autoDimensions': false,
					'width': '30%',
					'height': '35%',
					'overlayColor': '#000',
					'overlayOpacity': 0.6,
					'transitionIn': 'elastic',
					'transitionOut': 'elastic',
					'centerOnScroll': true,
					'titlePosition': 'outside',
					'easingIn': 'easeOutBack',
					'easingOut': 'easeInBack'
				});
			},
			error: function(xhr, textStatus, errorThrown) {
			alert("An error occurred.");
			}
		});
		return false;
	});
});
</script>
