/* revisions.js */
$(document).ready(function(){
	// common variables: defined in php and accessed through hidden inputs at the top of header.php
	adminURL = $('#adminUrl').val();
	
	// start hidden revision section
	$('#version-toggle-wrap').hide();
 
	$('#revert').click(function(e){
	e.preventDefault();
	if ( $('#revise_id option').size() == 0 )
	{
		return false;
	}
	$('#version-form').submit();
});

// asynchronous delete of older version
function deleteImage ()
{
	// get data
	var versionId = $('#revise_id').val(); // from revisions.php
	var tName = $('#tname').val();			// from revisions.php
	
	// ajax delete
	$.ajax({
		url: adminURL + 'components/revisions/ajax.php',
		type: 'POST',
		data: {version : "removev", version_id : versionId, tname : tName},
		//beforeSend: function(){$loadingImage.show();},
		error: function (response) { 
			alert('Error: could not delete version'); },
		success: function (strResponse) {
			if ( $.trim(strResponse) != "success")
			{
			console.log(strResponse);
				openBanner('error', 'Error', 'Failure deleting older version');
				$('#err_revise_id').addClass('successMsg').html('Error: version not removed');
			}
			else // success: update the select box
			{	
				// now remvoe from list of options
				$('#revise_id option[value="' + versionId + '"]').remove();
				
				// clean up if that was the last item
				if ( $('#revise_id option').size() == 0 )
				{
					$('#del-version, #preview').removeClass('red').addClass('grey');
					$('#revert').removeClass('blue').addClass('grey');
					$('#err_revise_id').addClass('warningMsg').html('Last version successfully removed');
					openBanner('warning', 'Removed Last Revision', 'Consider saving to ensure a backup exits.' );
				}
				else 
				{	
					$('#err_revise_id').addClass('successMsg').html('Version successfully removed');
					openBanner('success', 'Removed Older Version');
				}	
			}
		}
	});	// end ajax
}	// end deleteImage
// triggering the delete
$('#del-version').click(function(e){
	e.preventDefault();

	if ( $('#revise_id option').size() == 0 )
	{
		return false;
	}
	confirm('Delete this version?', deleteImage );
}); 

// triggering the optional preview button
$('#preview').click(function(e) {


	// assign the rel of the selected option as the button's href
	var target_href = $('#revise_id :selected').attr('rel');
	console.log (target_href);
	$('#preview').attr('href', target_href);
	
});

});