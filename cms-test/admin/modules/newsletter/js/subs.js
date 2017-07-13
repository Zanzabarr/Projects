$(document).ready( function() {
	$('#submit-btn').click(function(e){
		$('#subs_form').submit();
	});
	$('a.edit_sub').click(function(e) {
		e.preventDefault();
		email = $(this).attr('rel');
		$('input#email').val(email);
	});
	$('a.delete_sub').click(function(e) {
		e.preventDefault();
		id = $(this).attr('rel');
		apikey = $(this).attr('data-key');
		email = $(this).attr('data-email');
		
		$.ajax({
			type: "POST",
			url: "ajax/unsubscribe.php",
			data:{ email: email, apikey: apikey, id: id },
			cache: false,
			success: function(result){
				location.reload(true);
			}
		});
	});

});