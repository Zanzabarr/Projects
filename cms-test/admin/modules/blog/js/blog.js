$(document).ready(function(){

 
	// open/close comments for confirm
	// start closed
	$('.need_approve').live('click', function(e){
		e.preventDefault();

		$(this).parents('.blog_row').children('.post_grp').slideToggle('slow');
	});
	
	// delete a comment
	$(".delete_comment").live("click", function(e) {
		e.preventDefault();
		var id = $(this).attr("rel");
		var dataString = 'action=delete&id=' + id;
		var that = this;

		confirm('Permanently delete this comment?', function()
		{
			$.ajax({
				type: "POST",
				url: "ajax/ajaxcomment.php",
				data: dataString, 
				cache: false,
				success: function(result){
					if(result == 'success')	$(that).parents('.comment_grp').slideUp('slow', function(){updateNeedApprove($(that))} );      
					
				}
			});
		});
		//return false;
	});	
	
	
	// approve a comment
	$(".approve_comment").live("click", function(e) {
		e.preventDefault();
		var id = $(this).attr("rel");
		var dataString = 'action=approve&id=' + id;
		var that = this;

		//if(false)
		{
			$.ajax({
				type: "POST",
				url: "ajax/ajaxcomment.php",
				data: dataString, 
				cache: false,
				success: function(result){
					if(result == 'success')	$(that).fadeOut('slow', function(){updateNeedApprove($(that))});      

				}
			});
		}
		//return false;
	});	
	
	// change the 'requires update' image if no items requiring updates remain.
	function updateNeedApprove($appButton)
	{
		// are there still any need approves?
		// if not, change image
		var $parent = $appButton.parents('.post_grp');
	//console.log ($appButton.parents('.comment_row_grp').find('.comment_grp').filter(':visible').length)	;
		// test and treat for no comments left
		if( $appButton.parents('.comment_row_grp').find('.comment_grp').filter(':visible').length == 0 )
		{
			// hide the dialogue box
			$parent.slideUp('slow');
			// remove comments image
			var $target = $parent.parents('.blog_row').find('a.need_approve').fadeOut('slow');
		}
		
		// are there still any need approves?
		// if not, change image

		if ($parent.find('.approve_comment').filter(':visible').length == 0)
		{
			var $target = $parent.parents('.blog_row').find('a.need_approve').children('img');
			$target.attr('src', '../../images/caption-shade.png');
			$target.attr('title',"No new comments");
			// restart these tips
			$('.tipTop').tipTip({defaultPosition:"top"});
		}
	}
	
	// ajax delete post and all associated comments 
	$(".deleteblogpost").live("click", function(e) {
		e.preventDefault();
		var id = $(this).attr("rel");
		var dataString = 'action=delete&id=' + id;
		var that = this;
		confirm('Delete this post and all its comments?', function()
		{
			$.ajax({
				type: "POST",
				url: "ajax/deleteblogpost.php",
				data: dataString, 
				cache: false,
				success: function(result){
					if (result=='success') 	$(that).parents('.blog_row').slideUp('slow');
					
				}
			});
		})
	});
	
	// ajax delete category, uncouple any pages tied to that category
	$(".deletecategory").live("click", function(e){
		e.preventDefault();
		var id = $(this).attr("rel");
		var dataString = 'action=delete&id=' + id;
		var that = this;
		confirm('Delete this category?<br />note: associated posts will not be lost.', function()
		{
			$.ajax({
				type: "POST",
				url: "ajax/deletecategory.php",
				data: dataString, 
				cache: false,
				success: function(result){
					if (result=='success') 	
					{
						$(that).parents('.menu_row').slideUp('slow'); 
						// update posts with categories
						
					}
				}
			});
		})
	});


	// handle change Featured button
	$('.changeFeatured').click(function(e) {
		e.preventDefault();

		var id = $(this).attr("rel"),
			$imgNode = $(this).children()
			changeTo = $imgNode.attr('rel'),
			changeFrom = changeTo == 1 ? 0 : 1,
			newIconSrc = changeTo == 1 ? 'images/icon-star.png' : 'images/icon-darkstar.png',
			newTipTitle = changeTo == 1 ? 'Make Product No Longer Featured' : 'Make Featured Product',
			newAlt = changeTo == 1 ?  'Not Featured' : 'Feature';

		$.ajax({
			type: "POST",
			url: "ajax/ajaxchangefeatured.php",
			data:{ action: "change_featured", id: id, change_to : changeTo },
			cache: false,
			success: function(result){
				if(result == 'success') {
					//$(that).parents('.menu_row').slideUp('slow');    
					// write success
					//openBanner('success','Product Changed' , '');
					$imgNode.attr('rel', changeFrom).attr('src', newIconSrc).attr('alt', newAlt).attr('title', newTipTitle);
					$('.tipTop').tipTip({defaultPosition:"top"});
				} else {
					openBanner('error','Error changing Featured Product' , result);
				}
			}
		});
	});

});