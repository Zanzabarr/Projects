$(document).ready(function(){
	
	// ajax delete news_item
	$(".deletenews_item").live("click", function(e) {
		e.preventDefault();
		var id = $(this).attr("rel");
		var dataString = 'action=delete&id=' + id;
		var that = this;
		confirm('Delete this news_item?', function()
		{
			$.ajax({
				type: "POST",
				url: "ajax/deletenews_item.php",
				data: dataString, 
				cache: false,
				success: function(result){
					if (result=='success') 	$(that).parents('tr').slideUp('slow'); 
					
				}
			});
		})
	});
		
	$(".active").hover(function() {
	  $(this).css('cursor','pointer');
	});

});
