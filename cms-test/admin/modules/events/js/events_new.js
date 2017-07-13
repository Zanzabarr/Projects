$(document).ready(function() {
	bindNoDirtyPageExit('#addevent',['content'],['#submit-btn', '#cancel-btn']);
	
	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		var noTitle = $("#title").val().trim()==''
		var noURL =  $("#url").val().trim()=='';
        
		if( noTitle ||  noURL )
		{
			e.preventDefault();
			openBanner('error', 'Minimum Requirements', 'at minimum, Title and URL fields must be completed');
			$('#err_title, #err_url').html('');
			if (noTitle) $('#err_title').attr('class','errorMsg').html('Required field').show();
			if (noURL) $('#err_url').attr('class','errorMsg').html('Required field').show();
			$('.msg-wrap').scrollView();
			$('#prop-toggle-wrap').slideDown('slow', function() {
				
			});
			return false;
		}
    
		$('#addevent').submit();
	});


	$("#title").keyup(function() { 
		var value = $("#addevent #title").val();
		var value = value.replace(/[^A-Za-z0-9-]/g, '-');
		var value = value.replace(/-+/g, '-');
		var value = value.replace(/^-/, "");
		var value = value.replace(/-$/, "");
		$("#addevent #url").attr("value", value);
	});	

	$("#url").change(function() { 
		var value = $(this).val();
		var value = value.replace(/[^A-Za-z0-9-]/g, '-');
		var value = value.replace(/-+/g, '-');
		var value = value.replace(/^-/, "");
		var value = value.replace(/-$/, "");
		$(this).attr("value", value);
	});	


    // handle date override inputs. Custom date is only visible when date override is checked
    var checked = $("#override_date").attr("checked");

    if(checked != "checked") {
        $("#custom_date_container").hide();    
    }
    else {
        $("#custom_date_container").show();
    }

    $("#override_date").live("click", function(e) {
        $("#custom_date_container").slideToggle('slow', function() {});
    });
    
    var enable_time_checked = $("#enable_time").attr("checked");

    if(enable_time_checked != "checked") {
        $("#input_start_time_box").hide();
        $("#input_end_time_box").hide();
    }
    else {
        $("#input_start_time_box").show();
        $("#input_end_time_box").show();
    }

    $("#enable_time").live("click", function(e) {
        $("#input_start_time_box").toggle();
        $("#input_end_time_box").toggle();
    });
    

    // datepicker calendar anchoring
    $("#start_date").datepicker();
    $("#end_date").datepicker();

});
