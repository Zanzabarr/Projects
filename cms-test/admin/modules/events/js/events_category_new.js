$(document).ready(function() {
	bindNoDirtyPageExit('#addcat',['content'],['#submit-btn', '#cancel-btn']);
	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		var noTitle = $("#title").val().trim()==''
		var noUrl =  $("#url").val().trim()=='';
		
		if( noTitle ||  noUrl )
		{
			e.preventDefault();
			openBanner('error', 'Minimum Requirements', 'at minimum, Title and Url fields must be completed');
			$('#err_title, #err_url').html('');
			if (noTitle) $('#err_title').attr('class','errorMsg').html('Required field').show();
			if (noUrl) $('#err_url').attr('class','errorMsg').html('Required field').show();
			$('.msg-wrap').scrollView();
			$('#prop-toggle-wrap').slideDown('slow', function() {
				
			});
			return false;
		}

		$('#addcat').submit();
	});

	
	$("#title").keyup(function() { 
		var value = $("#addcat #title").val();
		var value = value.replace(/[^A-Za-z0-9-]/g, '-');
		var value = value.replace(/--/g, '-');
		var value = value.replace(/--/g, '-');
		var value = value.replace(/--/g, '-');
		var value = value.replace(/^-/, "");
		var value = value.replace(/-$/, "");
		$("#addcat #url").attr("value", value);
	});
	
	$("#url").change(function() { 
		var value = $(this).val();
		var value = value.replace(/[^A-Za-z0-9-]/g, '-');
		var value = value.replace(/--/g, '-');
		var value = value.replace(/--/g, '-');
		var value = value.replace(/--/g, '-');
		var value = value.replace(/^-/, "");
		var value = value.replace(/-$/, "");
		$(this).attr("value", value);
	});	

});
