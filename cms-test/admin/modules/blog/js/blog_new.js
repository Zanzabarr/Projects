$(document).ready(function() {
// trigger warning on unsaved page exits if inputs have been changed
	bindNoDirtyPageExit('#addblog',['intro', 'content'],['#submit-btn', '#cancel-btn']);

// handle form submissions
$('#submit-btn').live('click', function(e){
	var noTitle = $.trim($("#title").val())=='',
		noUrl = $.trim($("#url").val())=='';
	
	if( noTitle || noUrl || $('#url').hasClass('badUrl') )
	{
		openBanner('error', 'Minimum Requirements', 'at minimum, Title and Url fields must be completed and valid');
		e.preventDefault();
		if ( noTitle )$('#err_title').addClass('errorMsg').html('Required field');
		if ( noUrl ) $('#err_url').attr('class','errorMsg').html('Required field');
		$('#err_url').show()
		$('#prop-toggle-wrap').slideDown('slow');
		$('#prop-toggle-wrap').scrollView();
		return false;
	}
	$('#addblog').submit();
});
/*
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
		$('#addblog').submit();
	});	
*/	
	// handle delete page button
	$('#delete-btn').click(function(e) {
		e.preventDefault();
		confirm('Permanently remove this page and all its revisions?', function() { $('#delForm').submit(); } );
	});

	//validate the url, can't be in the array: usedSlugs (passed from php) unless it is the current page's slug name
	$('#url').keyup(function(e){ changeSlug( curSlug, usedSlugs, $(this), $(this) ) });
	// write title to url (converting to good slug) and provide validation feedback
	$('#title').keyup(function(e){ changeSlug( curSlug, usedSlugs, $(this), $('#url') ) });

});