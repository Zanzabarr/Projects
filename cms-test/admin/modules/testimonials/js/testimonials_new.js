$(document).ready(function() {
	// trigger warning on unsaved page exits if inputs have been changed
	bindNoDirtyPageExit('#addTestimonial',['content','short_test'],['#submit-btn', '#cancel-btn']);
	
	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		var noName = $.trim($("#name").val()) == ''
        
		if( noName)
		{
			e.preventDefault();
			openBanner('error', 'Minimum Requirements', 'at minimum, Name field must be completed');
			$('#err_name').html('');
			if (noName) $('#err_name').attr('class','errorMsg').html('Required field').show();
			$('.msg-wrap').scrollView();
			$('#prop-toggle-wrap').slideDown('slow', function() {
				
			});
			return false;
		}
    
		$('#addTestimonial').submit();
	});



});
