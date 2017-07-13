$(document).ready(function() {
	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		var noFirst = $("#contributor_name").val().trim()==''
		var noLast =  $("#caption").val().trim()=='';
        
        var pass = $("#password").val();	
        var cfm_pass = $("#cfm_password").val();
        
        var passNotEqual = (pass !== cfm_pass);
	
		if( noFirst ||  noLast )
		{
			e.preventDefault();
			openBanner('error', 'Minimum Requirements', 'at minimum, First Name and Image Caption fields must be completed');
			$('#err_contributor_name, #err_caption').html('');
			if (noFirst) $('#err_contributor_name').attr('class','errorMsg').html('Required field').show();
			if (noLast) $('#err_caption').attr('class','errorMsg').html('Required field').show();
			$('.msg-wrap').scrollView();
			$('#prop-toggle-wrap').slideDown('slow', function() {
				
			});
			return false;
		}
    


		$('#addnews_item').submit();
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
            // update news_item password using ajax
            var id = $('#news_item_id').val();

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
	
	// handle delete news_item button
	$('#delete-btn').click(function(e) {
		e.preventDefault();
		confirm('Permanently remove this News Item?', function() { $('#delForm').submit(); } );
	});

    // start change password area closed
    $('#togglebutton-wrap').hide();

    // start change news_item image area closed
    $('#togglenews_itemimage-wrap').hide();    

    // ajax delete news_item image
    $(".deletenews_itemimage").live("click", function(e) {
        e.preventDefault();
        var id = $(this).attr("rel");
        var originalSrc = $("#profile-image").attr("src");
        var dataString = 'action=delete&id=' + id;
        var that = this;
        confirm('Delete this News Item\'s profile image?', function()
        {
            $.ajax({
                type: "POST",
                url: "ajax/deletenews_itemimage.php",
                data: dataString,
                cache: false,
                success: function(result) {
                    if (result=='success') {
                        var originalSrcArray = originalSrc.split("/");
                        var defaultSrc = (originalSrcArray.splice(-3, 2)).join("/") + '/default_s.jpg';

                        $(that).hide();
                        $("#profile-image").attr('src', defaultSrc);
						$("#togglenews_itemimage").remove();
						$("#togglenews_itemimage-wrap").show();
						$("#togglenews_itemimage-wrap").prepend("<label class=\"tipRight\" title=\"News Item's profile image (optional).\">News Item Image</label>");
                    }
                }
            });
        })
    });


    // news_item profile image upload path text
    $("#news_item_image").change(function() {
    
        var fileName = $("#news_item_image").attr("value");

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
		$(errPwdID).attr('class','errorMsg').html('Passwords must be the same').show();
		$('.msg-wrap').scrollView();
		$('#prop-toggle-wrap').slideDown('slow', function() {
				
		});
    }
});
