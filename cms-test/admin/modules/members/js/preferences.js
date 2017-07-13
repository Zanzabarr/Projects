$(document).ready(function() {
	// trigger warning on unsaved page exits if inputs have been changed
	bindNoDirtyPageExit('#form_data',[],['#submit-btn', '#cancel-btn']);

	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		e.preventDefault();

		$('#form_data').submit();
	});	
	
	// requires login starts closed if display members not selected
	if(!$('#members_front').prop('checked')) $('#member_req_login').closest('fieldset').hide();
	
	$('#members_front').live('change',function(){
		if($(this).prop('checked')) $('#member_req_login').closest('fieldset').slideDown('fast');
		else $('#member_req_login').closest('fieldset').slideUp('fast');
	})
	
	

	
	// requires login starts closed if display members not selected
	if(!$('#online_signup').prop('checked')) $('#confirmation_period').closest('fieldset').hide();
	
	$('#online_signup').live('change',function(){
		var $fieldset = $('#confirmation_period').closest('fieldset');
		if($(this).prop('checked')) 
		{

			$fieldset.slideDown('fast');
		}
		else $fieldset.slideUp('fast');
	})


	
});	
