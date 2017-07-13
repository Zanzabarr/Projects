$(document).ready(function() {
	$('#submit-btn').click(function(e){
		$('#form_data').submit();
	});
	
	/* if hasCart turned off */
	$('#hasCart').change(function(e) {
		if ( $(this).not('checked') ) {
			$('#paypal-toggle-wrap').slideUp().find('#paypal_on option[value=0]').attr('selected','selected');
			$('#paypal_state').html("Paypal is <span style='color:#F00;'>OFF</span>");
			$('#purchasing_on').attr('checked',false);
			$('#shipping_on').attr('checked',false);
		}
	});
	/* if includePricing turned off */
	$('#includePricing').change(function(e) {
		if ( $(this).not('checked') ) {
			$('#paypal-toggle-wrap').slideUp().find('#paypal_on option[value=0]').attr('selected','selected');
			$('#paypal_state').html("Paypal is <span style='color:#F00;'>OFF</span>");
			$('#purchasing_on').attr('checked',false);
		}
	});
	/* show/hide gateway and purchasing options */
	$('#purchasing_on').change(function(e) {
		if ($(this).attr('checked')) {
			$('#gateway-options').slideDown();
		} else {
			$('#paypal-toggle-wrap').slideUp().find('#paypal_on option[value=0]').attr('selected','selected');
			$('#paypal_state').html("Paypal is <span style='color:#F00;'>OFF</span>");
			$('#gateway-options').slideUp().find('#nogateway').attr('checked', 'checked');
		}
	});
	
	$('select#gateway').change(function(e) {
		if($(this).val() == "nogateway") {
			$('#gateway-toggle-wrap').slideUp();
		} else {
			$('#gateway-toggle-wrap').slideDown();
			$.ajax({
				type: "POST",
				url: "ajax/gateway_frame.php",
				data:{ gateway: $(this).val() },
				cache: false,
				success: function(result){
					$('#gateway-toggle-wrap').html(result);
					$('#gateway-toggle-wrap').slideDown();
				}
			});
		}
	});
	
});