jQuery(document).ready(function($, tinyMCE) {

	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		$("#hidden_action").val("save_newsletter");
		$('#send_newsletter').submit();
	});
	
	$("#submit-send").live("click", function(e) {
		$("#hidden_action").val("send_newsletter");
		$('#send_newsletter').submit();
	});

	var adminUrl = $('#attach_admin_url').val();
	
	// handle delete newsletter button
	$('.deleteNewsletter').click(function(e) {
		e.preventDefault();
		var id = $(this).attr("rel"),
			that = this;
		confirm('Permanently Remove this Newsletter?&nbsp;&nbsp;<em>This <u>cannot</u> be undone!</em>', function() { 
			$.ajax({
				type: "POST",
				url: "ajax/ajax.php",
				data:{ action: "delete_letter", id: id },
				cache: false,
				success: function(result){
					if(result == 'success') { 
						$('#newsletter-list a').each( function() {
							if($(this).attr('rel')==id) $(this).parent().parent().remove();
						});
						// write success
						openBanner('success','Newsletter Deleted' , "In order to remove this newsletter from Mailchimp, you must login at <a href='https://login.mailchimp.com/' target='_blank'>https://login.mailchimp.com/</a>.")
						//setTimeout('location.reload()',3000);
					} else {
						openBanner('error','Error Removing Newsletter' , 'newsletter not found')
					}
				}
			});
		});
	});
});

