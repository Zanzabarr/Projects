// JavaScript Document
$(document).ready(function(){
	
	modulePath = $('#module_path').val();
	
	$('#main_categories').change(function() {
		id = $(this).val();
		//alert(id);
		if(id == 0){
			$('#sub_categories').hide();
			$("label[for='sub_categories']").hide();
			$(".subcatbr").hide();
		}else{
			$('#sub_categories').show();
			$("label[for='sub_categories']").show();
			$(".subcatbr").show();
			$("#sub_categories").val($('#sub_categories')[0].defaultValue);
		}
		$('.subcat').hide();
		$('#sub_categories .'+id).show();
	});

	$(".deletecomment").live("click", function() {
		var id = $(this).attr("ref");
		var dataString = 'action=delete&id=' + id;
		if(confirm('Are you sure?'))
		{
			$.ajax({
				type: "POST",
				url: modulePath + "frontend/ajax/deletecomment.php",
				data: dataString, 
				cache: false,
				success: function(data){
					if (data == 'success') $("#div_"+id).fadeOut('3000');     
					else alert('You must be logged in to delete a comment.');
				}
			});
		}
		return false;
	});
	
	$(".approvecomment").live("click", function() {
		var id = $(this).attr("ref");
		var dataString = 'action=approve&id=' + id;
		if(confirm('Are you sure?'))
		{
			$.ajax({
				type: "POST",
				url: modulePath + "frontend/ajax/approvecomment.php",
				data: dataString, 
				cache: false,
				success: function(data){
					
					if (data == 'success') $("#approve_"+id).fadeOut('3000');     
					else alert('You must be logged in to delete a comment.');
				}
			});
		}
		return false;
	});

});

	
	