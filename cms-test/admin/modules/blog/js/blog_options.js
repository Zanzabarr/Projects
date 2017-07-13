$(document).ready(function() {
	// trigger warning on unsaved page exits if inputs have been changed
	bindNoDirtyPageExit('#form_data',[],['#submit-btn', '#cancel-btn']);

	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		e.preventDefault();

		$('#form_data').submit();
	});	

	
	// handle comments and comments approval
	if( $('#comments').val() == 0) {
		$('#approve').parents('.input_wrap').hide();
		$('#com_per_pg').parents('.input_wrap').hide();
	}
	$('#comments').change(function(){
		$('#approve').parents('.input_wrap').slideToggle();
		$('#com_per_pg').parents('.input_wrap').slideToggle();
	});
});	