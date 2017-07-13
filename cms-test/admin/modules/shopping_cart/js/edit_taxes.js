$(document).ready(function() {

	$('#submit-btn').click(function(e){

		$('#form_data').submit();
	});
	
		// handle delete tax button
	$('.deleteTax').click(function(e) {
		e.preventDefault();
		var id = $(this).attr("rel"),
			that = this;
		confirm('Permanently Remove this Tax Location?&nbsp;&nbsp;<em>This <u>cannot</u> be undone!</em>', function() { 
			$.ajax({
				type: "POST",
				url: "ajax/ajax.php",
				data:{ action: "delete_tax", id: id },
				cache: false,
				success: function(result){
					if(result == 'success') { 
						$('#tblCouponPage .op_group a').each( function() {
							if($(this).attr('rel')==id) $(this).parent().parent().parent().remove();
						});
						// write success
						openBanner('success','Tax Deleted' , '')
						//setTimeout('location.reload()',3000);
					} else {
						openBanner('error','Error Removing Tax Location' , 'location not found')
					}
				}
			});
		});
	});
});