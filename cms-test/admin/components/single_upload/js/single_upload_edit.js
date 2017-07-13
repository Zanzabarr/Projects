jQuery(document).ready(function($) {
	
	// var config passed from head: passes all php config values
	var adminUrl				= $('#single_upload_admin_url').val(),
		ajaxPath				= adminUrl + "components/single_upload/ajax.php", 
		deletePath				= adminUrl + "js/jquery.fileupload/upload_handler.php",
		upload_path				= $('#uploadPath').val(),
		upload_url				= $('#uploadUrl').val(),
		idName					= $('#single_upload_target_id_name').val(),
		tableName				= $('#single_upload_table_name').val(),
		single_upload_id		= $('#single_upload_id').val(),
		file_name				= $('#single_upload_file_name').val(),
		single_upload_target_alt= $('#single_upload_target_image_alt').val(); 
		default_image_path= $('#single_upload_default_image_path').val(); 
		default_image_name= $('#single_upload_default_image_name').val(); 
		default_image_alt= $('#single_upload_default_image_alt').val(); 
		
	// show controls on hover
	$('#single_uploadUploads .image_wrap').live({
		mouseenter:	function(){	$('.option_navs').hide(); $(this).children('.option_navs').show();},
		mouseleave: function(){$('.option_navs').hide();}
	});
	
	// create banner if error/success message was posted
	var single_uploadMsg = $('#single_upload_msg').html();
	if( single_uploadMsg  ) {
		if ( $('#single_upload_msg').hasClass('successMsg') ) {
			single_uploadMsgClass = 'success';
			single_uploadMsgHeader = single_uploadMsg;
			single_uploadMsg = '';
		} else if ( $('#single_upload_msg').hasClass('errorMsg') ) { 
			single_uploadMsgClass = 'error';
			single_uploadMsgHeader = 'Error Uploading Image';
		}
		openBanner(single_uploadMsgClass, single_uploadMsgHeader, single_uploadMsg)
	}
	
	// clear the current error message when upload location pressed
	$('#single_uploadUploads #single_uploadUploader_file').click(function(){
		$("#single_uploadUploads #single_upload_msg").hide();
	});
	$('#single_uploadUploads #single_uploadUploader_file').change(function(){
		var thisfile = $(this).val().split("\\").pop();
		$("#single_upload-fake-file").html(thisfile);//.split("\").pop();' 
	});
	
			
	// update fancybox single_upload
	$("a.showImg").fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'fade',
		'speedIn'		:	600, 
		'speedOut'		:	400, 
	});
	

	// start uploads display closed
	$('#single_upload-toggle-wrap').hide();
	
	// set up modal
	$('#edit_single_image').jqm({overlay: 60, modal: true, trigger: false, target:false})
		.jqmAddClose('.close');
	// edit image modal
	$('.edit_single_image').live('click', function(e){
		e.preventDefault();
		
		// load the modal with data
		var $imageParent = $(this).parents('.imageData'),
			img_alt = $imageParent.children('.imgAlt').val(),
			img_id = $(this).attr('rel');

		$('#image_id').val(img_id);
		$('#alt').val(img_alt);
		// call the modal		
		$('#edit_single_image').jqmShow();
			
	});


	// ---------------------------------asynch actions---------------------------------------
	
	// ------------------------------------edit image------------------------------------
	$('#image_save_btn').live('click', function(e){
		e.preventDefault();
		var altVal = $('#alt').val();
		var imageId = $('#image_id').val();
		var that = this;
		$.ajax({
			url: ajaxPath,
			type: 'POST',
			data: {
				option : 'edit_image',
				alt : altVal, 
				image_id: imageId,
				uploadPath: 		upload_path,
				uploadUrl:			upload_url,
				target_id_name: 	idName,
				image_table_name:	tableName,
				target_alt:			single_upload_target_alt
			},
			//beforeSend: function(){$loadingImage.show();},
			error: function (response) { 
				// openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"'); 
				$('#single_upload_msg').html(strResponse).attr('class', 'errorMsg').show();
			},
			success: function (strResponse) 
			{
				if ( $.trim(strResponse) != "success")
				{
					//openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"');
					$('#err_alt').html(strResponse).attr('class', 'errorMsg').show();
				}
				else // success: update the images
				{	
					//set success msg & clear modal fields
					$('#single_upload_msg').html('Successfully Updated Image Info.').attr('class', 'successMsg').show();
					$('#err_alt').hide();
					
					// update info
					$(that).closest('#single_uploadUploads').find('.imgAlt').val(altVal);
					// close modal
					$('#edit_single_image').jqmHide();
					
				}
			}
		});	
	});
	
	// ----------------------------------delete image-----------------------------------
	// asynch delete of image: confirm on click
	$('.single_upload-del').live('click', function(e){
		var that = this;
		e.preventDefault();
		confirm('Remove this image?', function () { deleteSingle_uploadImage(that); } );

		return false;
	});
	// actual delete function
	function deleteSingle_uploadImage ( that )
	{	
		var imageName = $(that).attr('rel');	

		// ajax delete
		$.ajax({
			url: deletePath,
			type: 'GET',
			data: {
				_method : "DELETE", 
				target_id: single_upload_id,
				file_name: file_name,
				file: imageName
			},
			//beforeSend: function(){$loadingImage.show();},
			error: function (response) { 
				// openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"'); 
				$('#single_upload_msg').html(strResponse).attr('class', 'errorMsg').show();
			},
			success: function (strResponse) 
			{	
				resp = $.parseJSON(strResponse);
				if ( typeof resp.success === "undefined" || !resp.success)
				{
					//openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"');
					$('#single_upload_msg').html(strResponse).attr('class', 'errorMsg').show();
				}
				else // success: update the images
				{	
					// if default is set, replace with default image
					if(default_image_path)
					{
						var $imgData =  $(that).closest('.imageData'),
							$imgWrap = $imgData.children('.image_wrap'),
							$image = $imgWrap.children('img'),
							$options = $imgWrap.children('.option_navs');
							
						$image.attr('src',default_image_path + 'thumb/' + default_image_name);
						$image.attr('alt', default_image_alt);
						$imgData.children('.imgAlt').val( default_image_alt );
						$imgData.children('.imgName').val( default_image_name );
						$options.children('.showImg').attr('href', default_image_path + 'fullsize/' + default_image_name);
						$options.children('.single_upload-del').hide();
						
					}
					else
					{
						// get rid of image 
						$(that).closest('.image_wrap').fadeOut('slow',function(){$(that).closest('.image_wrap').remove();});
					}
				}
			}
		});	
	}
	

	
//-----------------------------------upload file-------------------------------------
	function enableUpload(){
		$('#uploadSingle_uploadImgBtn').removeClass('disabled');
	}
	
	$(document).bind('drop dragover', function (e) {
		e.preventDefault();
	});
			
	$('#image_corral').not('li').live('click', function() {$("#fileupload").click()});
			
	$('#single_upload_upload').fileupload({
		dataType: 'json',
		done: function (e, data) {
			// re-enable uploading, display error messages, and clear the upload field 
			//enableUpload();
			var result = data.result.files['0'],
				isImage = result.type.substring(1,5) == 'image';
			if( typeof result.error !== "undefined") 
				$("#single_upload_msg").attr('class', 'errorMsg').html($("#single_upload_msg").html() + result.name + ": " +result.error+ "<br>" ).show();
			else if (typeof result.fullsize_url === "undefined")
			{
				// fullsize image doesn't exist, set error message and delete original
				$("#single_upload_msg").attr('class', 'errorMsg').html($("#single_upload_msg").html() + result.name + ": is too large to handle please resize and try again<br>" ).show();
				$.ajax({
					url: result.delete_url,
					type: 'GET'
				});
			}
			else
			{
				//no error occured, lets build this thing
				var result_id = result.id,
					url_friendly_picture = result.url_friendly_name,
					picture= result.name,
					thumb_url = result.thumb_url,
					resultDiv = 
					"<div class='imageData'>"+
						"<input type='hidden' class='imgAlt' value='"+picture+"' />"+
						"<input type='hidden' class='imgName' value='"+picture+"' />"+
						"<div class='image_wrap'>"+
							"<span class='option_navs' >"+
								"<a class='showImg' href='"+result.fullsize_url+"' ><img alt='View Page' src='../../images/view_icon.png'></a>"+
								"<a class='edit_single_image' href='#' rel='"+result_id+"'><img src='../../images/edit.png' /></a>"+
								"<a class='single_upload-del' href='#' rel='"+picture+"'><img src='../../images/delete.png' ></a>"+
							"</span>"+
							"<img src='"+thumb_url+"' alt='"+picture+"'/>"+
						"</div>"+
					"</div>";

				// replace the current #imageData
				$(this).closest('#single_uploadUploads').find('.imageData').replaceWith(resultDiv);

			
				// update fancybox single_upload
				$("a.showImg").fancybox({
					'transitionIn'	:	'elastic',
					'transitionOut'	:	'fade',
					'speedIn'		:	600, 
					'speedOut'		:	400, 
				});
				
			}
					
		},
		progressall: function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('#single_upload-progress .bar').css(
				'width',
				progress + '%'
			);
		},
		dropZone: $('#image_corral'),
		formData: {
			file_name: file_name,
			target_id: single_upload_id,
			accept_file_types: "/\.(gif|jpe?g|png)$/i"
		},
		start: function (e){
			$('#single_upload-progress').show();
			$('#single_upload-no-progress').hide();
			$("#single_upload_msg").html('');
			
		},
		stop: function (e) {
			$('#single_upload-progress').hide();
			$('#single_upload-no-progress').show();
		},
		maxChunkSize: 1000000
		
	});
});
