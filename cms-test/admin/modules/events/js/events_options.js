$(document).ready(function() {
	bindNoDirtyPageExit('#form_data',[],['#submit-btn', '#cancel-btn']);
	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		e.preventDefault();

		$('#form_data').submit();
	});	
});	
