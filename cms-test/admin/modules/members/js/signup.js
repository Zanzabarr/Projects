$(document).ready(function() {
	// trigger warning on unsaved page exits if inputs have been changed
	bindNoDirtyPageExit('#form_data',['tiny_desc','tiny_success','tiny_confirmed'],['#submit-btn', '#cancel-btn']);
	
	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		$('#form_data').submit();
	});	
	
	// if the notification radio is set to anything but 0(no notifications) hide the notifications options
	if(  $('input[name=email_notification]:checked', '#form_data').val() == 0) $('#notify-toggle-wrap, #notify-toggle').hide();
	
	// likewise, toggle the wrap on the same conditions as above
	$('input[name=email_notification]', '#form_data').live('change',function(){
		if($(this).val() != 0) $('#notify-toggle-wrap, #notify-toggle').show();
		else  $('#notify-toggle-wrap, #notify-toggle').hide();
	});
});
