$(document).ready(function() {
	$('#submit-btn').click(function(e){
		$('#form_data').submit();
	});

	// set shipping details to closed if free shipping checked to start
	if ( $('#free_shipping').prop('checked') ) $('#shipping').hide();

	$('#free_shipping').change(function(e) {
		if ( $(this).prop('checked') ) 
		{
			$('#shipping').slideUp().find('input').val(0);
			$('#fedex_prefs').slideUp();
			$('#fedex-toggle-wrap').slideUp().find('#fedex_on').attr('checked',false);
		}	
		else $('#shipping').slideDown();
	});
	
	//start canpost_prefs closed
	$('#canpost_prefs').hide();
	$('#cp-toggle-wrap').hide();
	// open/close canpost_prefs based on fedex_on checked
	if( $('#canpost_on').prop('checked') ) {
		$('#canpost_prefs').show();
	} else {
		$('#canpost_prefs').hide();
	}
	
	$('#canpost_on').change(function() {
		if($(this).prop('checked')) {
			if($('#free_shipping').prop('checked')) {
				$(this).attr('checked',false);
				alert('You cannot use Canada Post with free shipping.');
			} else {
				$('#canpost_prefs').slideDown();
			}
		} else {
			$('#canpost_prefs').slideUp();
		}
	});
	
	// start fedex_prefs closed
	$('#fedex_prefs').hide();
	$('#fedex-toggle-wrap').hide();
	// open/close fedex_prefs based on fedex_on checked
	if( $('#fedex_on').prop('checked') ) {
		$('#fedex_prefs').show();
	} else {
		$('#fedex_prefs').hide();
	}
	
	$('#fedex_on').change(function() {
		if($(this).prop('checked')) {
			if($('#free_shipping').prop('checked')) {
				$(this).attr('checked',false);
				alert('You cannot use Fedex with free shipping.');
			} else {
				$('#fedex_prefs').slideDown();
			}
		} else {
			$('#fedex_prefs').slideUp();
		}
	});
	
	// make sure either free shipping or min charge is set
	$('#min_ship').change(function(e) {
		$('#free_ship').val(0);
	});
	$('#free_ship').change(function(e) {
		$('#min_ship').val(0);
	});
})