$(document).ready(function(){
	
	// ajax delete testimonial
	$(".deletetestimonial").live("click", function(e) {
		e.preventDefault();
		var id = $(this).attr("rel");
		var dataString = 'action=delete&id=' + id;
		var that = this;
		confirm('Delete this testimonial?', function()
		{
			$.ajax({
				type: "POST",
				url: "ajax/deletetestimonial.php",
				data: dataString, 
				cache: false,
				success: function(result){
					if (result=='success') 	$(that).parents('.menu_row').slideUp('slow'); 
					
				}
			});
		})
	});

    // for toggle active/inactive for testimonials
    $(".active").hover(function() {
      $(this).css('cursor','pointer');
    });

    $(".active").live("click", function(e) {
        e.preventDefault();
        var id = $(this).attr("id");
        var ref = $(this).attr("ref");
        
        var dataString = 'action=' + ref + '&id=' + id;
        
        $.ajax({
            type: "POST",
            url: "ajax/active.php",
            data: dataString, 
            cache: false,
            success: function(result) {
                         if (ref == 'on') {
                            $("img#"+id).attr('src', 'images/dislike.png'); 
                            $("img#"+id).attr("ref", "off");
                         } else {
                            $("img#"+id).attr('src', 'images/check.png'); 
                            $("img#"+id).attr("ref", "on");
                         }
                     }
        });
    }); 
});
