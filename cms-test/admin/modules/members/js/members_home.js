$(document).ready(function() {
	// trigger warning on unsaved page exits if inputs have been changed
	bindNoDirtyPageExit('#form_data',['content'],['#submit-btn', '#cancel-btn']);
	
	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		$('#form_data').submit();
	});	
});
