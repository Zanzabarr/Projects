$(document).ready(function() {
// trigger warning on unsaved page exits if inputs have been changed
bindNoDirtyPageExit('#form_general',[],[]);
bindNoDirtyPageExit('#form_permissions',[],[]);
//bindNoDirtyPageExit('#form_password',[],['#general_update','#user_btn', '#permission_btn','#password_btn']);
//bindNoDirtyPageExit('#form_admin',[],[]);

//$.each(arUsers, function(i,e){console.log(e);})
//$.each(arModPermit, function(i,e){console.log(e);})
//alert(arUsers[0]['username']);

// ****************************** //
// ****   LOADING ACTIONS   ***** //
// ****************************** //

// set the default timezone by grabbing the current user's timezone
var dfltTZ = $('#timezone').val();

// ****************************** OPEN/CLOSE WRAPPERS ****************************** //
// all toggled form sections, deleteUserBtn, and the 'create/edit user' form within its wrap start hidden.
$('#admin-toggle-wrap, #permit-toggle-wrap, #pass-toggle-wrap, #gen-toggle-wrap, #userForm, #deleteUserBtn').hide();

// open toggled area if it has a message
if ( $('#gen-toggle-wrap .warningMsg, #gen-toggle-wrap .errorMsg').size() > 0 ) {
	// go to error message
	$('.msg-wrap').scrollView();
	$('#gen-toggle-wrap').show();
}
if ( $('#admin-toggle-wrap .warningMsg, #admin-toggle-wrap .errorMsg').size() > 0 ) {
	// change the button name
	$('#userFormToggle').html('Close');
	// change the action value
	// 	 if a user is selected, value = edit. Otherwise new
	tmpval = $('#admin_user').val() > 0 ? 'edit' : 'new';
	$('#admin_form').val(tmpval);
	$('.msg-wrap').scrollView();
	// need to open the inner form
	$('#userForm').show();
	// open outer wrap
	$('#admin-toggle-wrap').show();
}
if ( $('#permit-toggle-wrap .warningMsg, #permit-toggle-wrap .errorMsg').size() > 0 ) {
	$('.msg-wrap').scrollView();
	$('#permit-toggle-wrap').show();
}
if ( $('#pass-toggle-wrap .warningMsg, #pass-toggle-wrap .errorMsg').size() > 0 ) {
	$('.msg-wrap').scrollView();
	$('#pass-toggle-wrap').show();
}

// open anything that has been deliberately set to open by the message variable (hidden inputs)
$('.tagsToOpen').each(function(i,element){
	$( $(this).val() ).show();
});

// ****************************** OTHER START STATE ****************************** //	

// if a success banner has been set, scroll to it on refresh
$('#bannerType.success').scrollView();	

// Permissions Section: Check all boxes based on currently selected User
populateEditPermit( $('#permit_user').val() );

// temporary while working here
//$('#permit-toggle-wrap').show();

// ****************************** //
// ****    LIVE ACTIONS     ***** //
// ****************************** //

// ****************************** ADMIN SECTION ****************************** //
// Administration create button toggles form
$('#userFormToggle').live('click',function(e){
	e.preventDefault();
	var userId = $('#admin_user').val();
	//clear all error messages
	$('#userForm .message_wrap span').html('');

	// toggle open/closed
	$("#userForm").slideToggle('slow');
	// change the button's value
	if( $(this).html() == "Close") {
		// change it to the right word
		if( userId == 0 ) $(this).html('Create');
		else {
			$(this).html('Edit');
			clearAdminForm();
		}	
	} else {
		// change it to close
		$(this).html('Close');
		// show user data to edit
		if( userId )populateEditUser( userId );
		
	}
});

// admin_user onchange: all kinds of stuff	
//		change action type
//		change buttons
//		clear form data for new or propogate form data for edit	
$('#admin_user').live('change', function(e){
	var sel_id = $(this).val();

	if(sel_id > 0) {
	// edit existing user

		//	change action type
		$('#admin_form').val('edit');
		// hide delete button
		$('#deleteUserBtn').fadeIn('slow');
		
		//clear all error messages
		$('#userForm .message_wrap span').html('');
		
		// if the button isn't set to close, make sure it is set to Edit
		if ($('#userFormToggle').html() != 'Close') $('#userFormToggle').html('Edit');
		
		// populate user
		populateEditUser(sel_id);
	} else {
	// new user

		//	change action type
		$('#admin_form').val('new');
		// show delete button
		$('#deleteUserBtn').fadeOut('slow');
		clearAdminForm();
		
		// if button isn't set to close: set to create
		if ($('#userFormToggle').html() != 'Close') $('#userFormToggle').html('Create');
	}	
});

// Admin section delete button
$('#deleteUserBtn').live('click', function(e){
	e.preventDefault();
	
	// get the user to delete
	var deleteId = $('#admin_user').val();
	
	// confirm delete
	confirm('Permanently remove this user?', function() { window.location.href = "?delete=" + deleteId; } );
});
// ****************************** PERMISSION SECTION ****************************** //
// TODO populateEditPermit(id)
$('#permit_user').live('change', function(e){
	// change the data
	populateEditPermit( $(this).val() );
});

// ****************************** //
// ****      FUNCTIONS      ***** //
// ****************************** //

// clear admin form data
function clearAdminForm()
{
	// clear all form data except timezone, that should reflect the default(owners tz)
	$('#admin_username, #admin_pass, #admin_ver_pass, #admin_email, #admin_fname, #admin_lname').val('');
	$('#admin_timezone').val(dfltTZ);	
}

function populateEditUser(id)
{
	// find the user 
	$.each(arUsers, function(i,elem){
		
		if(elem.user_id == id) {
			// set data
			$('#admin_username').val(elem.username);
			$('#admin_fname').val(elem.first_name);
			$('#admin_lname').val(elem.last_name);
			$('#admin_pass').val('');
			$('#admin_ver_pass').val('');
			$('#admin_email').val(elem.email);
			$('#admin_timezone').val(elem.timezone);

			// end loop
			return false;
		} 
		
	});
}

function togglePermitBoxes()
{	//		   |	
	// change  v  the name
	$('.check:button').toggle(function(){
        $('#permit-toggle-wrap input:checkbox').attr('checked','checked');
        $(this).val('uncheck all')
    },function(){
        $('#permit-toggle-wrap input:checkbox').removeAttr('checked');
        $(this).val('check all');        
    })


}
// check all boxes
$('#permitCheck').live('click', function(e){
	e.preventDefault();
	$('#permit-toggle-wrap input:checkbox').attr('checked','checked');
});

// uncheck all boxes
$('#permitUnCheck').live('click', function(e){
	e.preventDefault();
	$('#permit-toggle-wrap input:checkbox').removeAttr('checked');
});

// arModPermit is passed from php as an array of valid permissions: array( user_id, module )
function populateEditPermit(id)
{
	// first, clear all checkboxes
	$('#permit-toggle-wrap input:checkbox').removeAttr('checked');
	
	// set all checkboxes for selected user
	$.each(arModPermit, function(i,elem){
		if(elem.user_id == id) {
			// set module as checked
			$('#permit_' + elem.module).attr('checked', 'checked');
		} 
		
	});
}
});