$(document).ready( function() {
	$('#submit-btn').click(function(e){
		$('#form_data').submit();
	});
    // datepicker calendar anchoring
    $("#exp").datepicker();
	
	// handle delete coupon button
	$('.deleteCoupon').click(function(e) {
		e.preventDefault();
		var id = $(this).attr("rel"),
			that = this;
		confirm('Permanently Remove this Coupon?&nbsp;&nbsp;<em>This <u>cannot</u> be undone!</em>', function() { 
			$.ajax({
				type: "POST",
				url: "ajax/ajax.php",
				data:{ action: "delete_coupon", id: id },
				cache: false,
				success: function(result){
					if(result == 'success') { 
						$('#tblCouponPage .op_group a').each( function() {
							if($(this).attr('rel')==id) $(this).parent().parent().parent().remove();
						});
						// write success
						openBanner('success','Coupon Deleted' , '')
						//setTimeout('location.reload()',3000);
					} else {
						openBanner('error','Error Removing Coupon' , 'coupon not found')
					}
				}
			});
		});
	});
	
});