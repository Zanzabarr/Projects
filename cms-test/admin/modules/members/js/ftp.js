$(document).ready(function(){
	
	// ajax delete member
	$(".deletemember").live("click", function(e) {
		e.preventDefault();
		var id = $(this).attr("rel");
		var dataString = 'action=delete&id=' + id;
		var that = this;
		confirm('Delete this member?', function()
		{
			$.ajax({
				type: "POST",
				url: "ajax/deletemember.php",
				data: dataString, 
				cache: false,
				success: function(result){
					if (result=='success') 	$(that).parents('.menu_row').slideUp('slow'); 
					
				}
			});
		});
	});
	
	//hide all submit folder buttons
	$('.folder_row .submitName').hide();
	
	// hide new folder controls
	$('.new_folder_row .editFolder, .new_folder_row .deleteFolder').hide();
	
	// allow editing of a folder name
	$('.folder_grp').on('click','.editFolder', function(e){
		e.preventDefault();
		// close all open folders
		$('.folder input').prop('disabled', true).addClass('notInput');
		// close all open submit buttons
		$('.submitName').hide();

		var $row = $(this).parents('.folder_row'),
			$folder = $row.find('.folder input'),
			$submit = $row.find('.submitName');
		$submit.show();
		$folder.removeAttr('disabled').removeClass('notInput').focus();
	});
		
	$('.folder_grp').on('click','.submitName', function(e){
		e.preventDefault();
		
		var $row = $(this).parents('.folder_row, .new_folder_row'),
			$folder = $row.find('.folder input'),
			$ftp_mod = $row.find('.ftp_mod'),
			$ftp_status = $row.find('.ftp_status'),
			$submit = $row.find('.submitName'),
			id = $(this).attr("rel"),
			newname = $folder.val(),
			dataString = 'action=name_folder&id=' + id + '&newname=' + newname,
			$editButton = $row.find('.editFolder'),
			$delButton = $row.find('.deleteFolder'),
			$that = $(this);

		$.ajax({
			type: "POST",
			url: "ajax/folder.php",
			data: dataString, 
			cache: false,
			success: function(result){
				 if( result == 'success' || $.isNumeric(result))
				 {
				 
					var bannertype = 'success',
						bannerheading = 'Success',
						bannermsg = 'new folder created';						
					if(id) bannermsg = 'folder renamed';
					$folder.attr('rel', newname);
					$folder.val(newname);
					$('#no_folders').fadeOut('slow');
					
					// is this a new folder?
					if($.isNumeric(result)) {
						bannermsg = 'new folder created';
						$submit.attr('rel', result);
						$editButton.show();
						$delButton.attr('rel', result).show();
						$row.removeClass('new_folder_row').addClass('folder_row');
						$ftp_status.html('Active');
						d=new Date();
						month = d.getMonth() +1;
						month = month < 10 ? "0" + month : month;
						day = d.getDate();
						day = day < 10 ? "0" + day : day;
						hour = d.getHours();
						hour = hour < 10 ? "0" + hour : hour;
						minute = d.getMinutes();
						minute = minute < 10 ? "0" + minute : minute;
						second = d.getSeconds();
						second = second < 10 ? "0" + second : second;
						dateString = d.getFullYear()+'-'+ month +'-'+day +' '+hour +':'+ minute +':'+ second;
						$ftp_mod.html(dateString);
						$that.hide();
						$folder.attr('disabled', true).addClass('notInput').val($folder.attr('rel'));
					}
					//console.log(typeof newname);
					// put the folder in alphabetical order?
					$('.folder_row').each(function (i,e) {
					//console.log( typeof $(this).find('input').val().toString());
					//	if ( $(this).find('input').val().toString() > newname.toString() ) console.log('is the guy');
					});
				 } else {
					var bannertype = 'error',
						bannerheading = 'Error',
						bannermsg = result;
				 }
				 
				 if(result != 'nochange') openBanner(bannertype, bannerheading, bannermsg);
				
				
				
				
			}
		})
	});
	
	$('.folder_grp').on('click','.deleteFolder', function(e){
		e.preventDefault();
		
		var $row = $(this).parents('.folder_row, .new_folder_row'),
			$folder = $row.find('.folder input'), //needed?
			$submit = $row.find('.submitName'),   //needed?
			id = $(this).attr("rel"),
			dataString = 'action=delete&id=' + id,
			$that = $(this);
		
		$.ajax({
			type: "POST",
			url: "ajax/folder.php",
			data: dataString, 
			cache: false,
			success: function(result){
				if( result == 'success' )
				{
					$row.slideUp(function(){if( $('.folder_row:visible, .new_folder_row:visible').length == 0 ) $('#no_folders').fadeIn('slow');});
				} 
			}
		})
	});
	
	$(".active").hover(function() {
	  $(this).css('cursor','pointer');
	});


// -------------------------------ajax calls-------------------------------	
	$(".members_row .row_status").click(function(e){
		var $that = $(this),
			member_status = $(this).html(),
			new_status_val = member_status == 'Active' ? 0 : 1,
			id = $(this).closest('.menu_row').attr('rel'),
			new_status = member_status == 'Active' ? 'Inactive' : 'Active',
			dataString = 'action=status&id=' + id + '&new_status=' + new_status_val;
		// send the data to update db
		$.ajax({
			type: "POST",
			url: "ajax/user.php",
			data: dataString, 
			cache: false,
			success: function(result){
				if(result == 'success' )$that.html(new_status);
			}
		});
	});
		
	$(".folder_grp .ftp_status").click(function(e){
		var $that = $(this),
			ftp_status = $(this).html(),
			new_status_val = ftp_status == 'Active' ? 0 : 1,
			id = $(this).siblings('.op_group').children('.submitName').attr('rel'),
			new_status = ftp_status == 'Active' ? 'Inactive' : 'Active',
			dataString = 'action=status&id=' + id + '&new_status=' + new_status_val;
		// send the data to update db
		$.ajax({
			type: "POST",
			url: "ajax/folder.php",
			data: dataString, 
			cache: false,
			success: function(result){
				if(result == 'success' )$that.html(new_status);
			}
		});
	});
	
	$(".upDown input").live("change", function(e) {
	
		var changed_upDown = $(this).attr('name'),
			input = $("<input>").attr("type", "hidden").attr("name", "changed").val(changed_upDown);
		$('#sort-form').append($(input));
		$("#sort-form").submit();
	});
});