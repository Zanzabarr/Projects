$(document).ready(function() {
	// trigger warning on unsaved page exits if inputs have been changed
	bindNoDirtyPageExit('#addmember',['bio'],['#submit-btn', '#cancel-btn']);
	
	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		var noEmail = $.trim($("#email").val())=='';
        
        var emailReg = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;

        var invalidEmail = !emailReg.test( $("#email").val()); 
        
        var pass = $("#password").val();	
        var cfm_pass = $("#cfm_password").val();
        
        var passNotEqual = (pass !== cfm_pass);
	
		if( noEmail )
		{
			e.preventDefault();
			openBanner('error', 'Minimum Requirements', 'at minimum, Email field must be completed');
			$('#err_email').html('');
			if (noEmail) $('#err_email').attr('class','errorMsg').html('Required field').show();
			$('.msg-wrap').scrollView();
			$('#prop-toggle-wrap').slideDown('slow', function() {
				
			});
			return false;
		}
        else if ( invalidEmail )
        {
			e.preventDefault();
			openBanner('error', 'Invalid Email', 'member email address must be a valid email address');
			$('#err_email').html('');
			if (noEmail) $('#err_email').attr('class','errorMsg').html('Invalid Format').show();
			$('.msg-wrap').scrollView();
			$('#prop-toggle-wrap').slideDown('slow', function() {
				
			});
			return false;
        }   
 
        if(passNotEqual)
        {
		    e.preventDefault();
            displayPwdErrors('password', 'cfm_password');
            return false;
        }

		$('#addmember').submit();
	});
	
	// set shipping details to closed if free shipping checked to start
	if (! $('#ftp_access').prop('checked') ) $('#ftp_section').hide();

	$('#ftp_access').change(function(e) {
		if (! $(this).prop('checked') ) 
		{
			$('#ftp_section').slideUp().find('input').val(0);
			$('#ftp_section').find(":checkbox").prop('checked', false);
		}	
		else $('#ftp_section').slideDown();
		
	});

    // change password button
    $('#submit-pass').live("click", function(e) {
        e.preventDefault();
        
        var new_pass = $('#new_pass').val();
        var cfm_new_pass = $('#cfm_new_pass').val();	
        
        var newPassNotEqual = (new_pass !== cfm_new_pass);

        if(newPassNotEqual)
        {
            displayPwdErrors('new_pass', 'cfm_new_pass');
            return false;
        }
        else {
            // update member password using ajax
            var id = $('#member_id').val();

            var dataString = 'submit-pass=&id=' + id + '&password=' + new_pass;

            $.ajax({
                type: "POST",
                url: "ajax/changepassword.php",
                data: dataString,
                cache: false,
                success: function(result) {
                    if (result=='success') { 
		                openBanner('success', 'Password Changed Successfully', '');
                        $('#new_pass').val("");
                        $('#cfm_new_pass').val("");
	                    $('#err_new_pass', '#err_cfm_new_pass').html('');
		                $('#err_new_pass').attr('class','errorMsg').html('').hide();
		                $('#togglebutton-wrap').slideUp('slow', function() {
                        });
		                $('.msg-wrap').scrollView();
		                $('#prop-toggle-wrap').slideDown('slow', function() {
		                });
                    }
                }
            });

            return false;
        }
    });


    // renew membership button       
    $('#submit-renewal').live("click", function(e) {
        e.preventDefault();
       
        confirm("Are you sure you want to renew this member's subscription?", function() { 
            // renew member subscription using ajax
            var id = $('#member_id').val();
            var expiry_status = $('#expiry_status').val();

            var dataString = 'submit-renewal=&id=' + id + '&expiry_status=' + expiry_status;

            $.ajax({
                type: "POST",
                url: "ajax/renewmembership.php",
                data: dataString,
                cache: false,
                success: function(result) {
		            openBanner('success', 'Membership Renewed Successfully', '');
		            $('#submit-renewal').hide();
                    $('#expiry_date_div span').removeClass();
                    $('#expiry_date_div span').html(result);
                }
            });
        });

        return false;
    });

	
    // start change password area closed
    $('#togglebutton-wrap').hide();

    // start change member image area closed
    $('#togglememberimage-wrap').hide();    

    // ajax delete member image
    $(".deletememberimage").live("click", function(e) {
        e.preventDefault();
        var id = $(this).attr("rel");
        var originalSrc = $("#profile-image").attr("src");
        var dataString = 'action=delete&id=' + id;
        var that = this;
        confirm('Delete this member\'s profile image?', function()
        {
            $.ajax({
                type: "POST",
                url: "ajax/deletememberimage.php",
                data: dataString,
                cache: false,
                success: function(result) {
                    if (result=='success') {
                        var originalSrcArray = originalSrc.split("/");
                        var defaultSrc = (originalSrcArray.splice(-3, 2)).join("/") + '/default_s.jpg';

                        $(that).hide();
                        $("#profile-image").attr('src', defaultSrc);
						$("#togglememberimage").remove();
						$("#togglememberimage-wrap").show();
						$("#togglememberimage-wrap").prepend("<label class=\"tipRight\" title=\"Member's profile image (optional).\">Member Image</label>");
                    }
                }
            });
        })
    });


    // member profile image upload path text
    $("#member_image").change(function() {
    
        var fileName = $("#member_image").attr("value");

        $(".fakefile input").attr("value", fileName);

    });

    function displayPwdErrors(pwdID, cfmPwdID) {

        var errPwdID = '#err_' + pwdID;
        var errCfmPwdID = '#err_' + cfmPwdID;

        pwdID = '#' + pwdID;
        cfmPwdID = '#' + cfmPwdID;

		openBanner('error', 'Password Mismatch', 'Password and Confirm Password must be equal');
        $(pwdID).val("");
        $(cfmPwdID).val("");
	    $(errPwdID, errCfmPwdID).html('');
		$(errPwdID).attr('class','errorMsg').html('Mismatch').show();
		$('.msg-wrap').scrollView();
		$('#prop-toggle-wrap').slideDown('slow', function() {
				
		});
    }
	
	
});
