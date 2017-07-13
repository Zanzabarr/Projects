$(document).ready(function(){
//	$("td:last-child,th:last-child").css("padding-right", "10px");
//	$("td:first-child,th:first-child").css("padding-left", "10px");	



	

//left as template for delete
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
	


});