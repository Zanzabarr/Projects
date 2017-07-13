jQuery(document).ready(function($) {
	
	// var config passed from head: passes all php config values
	var adminUrl	= $('#gallery_admin_url').val(),
		ajaxPath	= adminUrl + "components/gallery/ajax.php",
		deletePath	= adminUrl + "js/jquery.fileupload/upload_handler.php",
		upload_path	= $('#uploadPath').val(),
		upload_url	= $('#uploadUrl').val(),
		idName		= $('#target_id_name').val(),
		tableName	= $('#gallery_table_name').val(),
		gallery_id	= $('#gallery_id').val(),
		file_name	= $('#gallery_file_name').val(),					// name of the main file(eg ecom)
		galleryHasHTMLdesc = $('#gallery_has_html_desc').val(),
		galleryHasTextDesc = $('#gallery_has_text_desc').val(); 
	
	// show controls on hover
	$('#galleryUploads .image_wrap').live({
		mouseenter:	function(){	$('.option_navs').hide(); $(this).children('.option_navs').show();},
		mouseleave: function(){$('.option_navs').hide();}
	});
	
	// create banner if error/success message was posted
	var galleryMsg = $('#gallery_msg').html();
	if( galleryMsg  ) {
		if ( $('#gallery_msg').hasClass('successMsg') ) {
			galleryMsgClass = 'success';
			galleryMsgHeader = galleryMsg;
			galleryMsg = '';
		} else if ( $('#gallery_msg').hasClass('errorMsg') ) { 
			galleryMsgClass = 'error';
			galleryMsgHeader = 'Error Uploading Image';
		}
		openBanner(galleryMsgClass, galleryMsgHeader, galleryMsg)
	}
	
	// clear the current error message when upload location pressed
	$('#galleryUploads #galleryUploader_file').click(function(){
		$("#galleryUploads #gallery_msg").hide();
	});
	$('#galleryUploads #galleryUploader_file').change(function(){
		var thisfile = $(this).val().split("\\").pop();
		$("#gallery-fake-file").html(thisfile);//.split("\").pop();' 
	});
	
	// called after every moved item: updates the image position asynchronously
	function savePosns(){
		// get sort-order data
		// get all id's from corral
		var postData = {
			option: 			'save_posn',
			corralIds: 			[], 
			penIds: 			[],
			image_table_name:	tableName
		};

		$('#image_corral .edit_image').each(function(){
			postData.corralIds.push( $(this).attr('rel'));
		});
		$('#image_pen .edit_image').each(function(){
			postData.penIds.push( $(this).attr('rel'));
		});
		// get all from pen in order
		// post the data asynchronously to update posns
		$.ajax({
			url: ajaxPath,
			type: 'POST',
			data: postData
		});	
	};
	
	// enable sortables /
	$('#image_corral, #image_pen').sortable({
		connectWith: ".connected",
		stop: savePosns
	});
			
	// update fancybox gallery
	$("a.showImg").fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'fade',
		'speedIn'		:	600, 
		'speedOut'		:	400, 
	});
	

	// start uploads display closed
	$('#gallery-toggle-wrap').hide();
	
	// set up modal
	$('#edit_image').jqm({overlay: 60, modal: false, trigger: false, target:false})
		.jqmAddClose('.close');
	// edit image modal
	$('.edit_image').live('click', function(e){
		e.preventDefault();
		
		// load the modal with data
		var $imageParent = $(this).parents('.imageData'),
			img_alt = $imageParent.children('.imgAlt').val(),
			img_desc = $imageParent.children('.imgDesc').val(),
			img_html_desc = $imageParent.children('.imgHtmlDesc').val(),
			img_url = $imageParent.children('.imgURL').val(),
			img_id = $(this).attr('rel');

		$('#image_id').val(img_id);
		$('#alt').val(img_alt);
		$('#url').val(img_url);
		$('#gallery_text_desc').val(img_desc);
		if (galleryHasHTMLdesc) tinyMCE.get('gallery_html_desc').setContent(img_html_desc);
		// call the modal and set it's height (setModalTop defined in admin/js/functions.js)		
		$('#edit_image').setModalTop().jqmShow();
			
	});
	



	// ---------------------------------asynch actions---------------------------------------
	
	// ------------------------------------edit image------------------------------------
	$('#image_save_btn').live('click', function(e){
		e.preventDefault();
		var altVal = $('#edit_image #alt').val();
		var urlVal = $('#edit_image #url').length ? $('#edit_image #url').val() : null;
		var descVal = $('#gallery_text_desc').val();
		var htmlDescVal = galleryHasHTMLdesc ?  tinyMCE.get('gallery_html_desc').getContent() : '';
		var imageId = $('#image_id').val();
		var $dataTarget = $('#imageData'+imageId);
		
		var arOptions = {
				option : 'edit_image',
				alt : altVal, 
				desc: descVal, 
				html_desc: htmlDescVal,
				image_id: imageId,
				uploadPath: 		upload_path,
				uploadUrl:			upload_url,
				target_id_name: 	idName,
				image_table_name:	tableName 
			};
			
		//url : urlVal,
			
		$.ajax({
			url: ajaxPath,
			type: 'POST',
			data: arOptions,
			//beforeSend: function(){$loadingImage.show();},
			error: function (response) { 
				// openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"'); 
				$('#gallery_msg').html(strResponse).attr('class', 'errorMsg').show();
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
					$('#gallery_msg').html('Successfully Updated Image Info.').attr('class', 'successMsg').show();
					$('#err_alt').hide();
					
					// update info
					$dataTarget.children('.imgAlt').val(altVal);
					$dataTarget.children('.imgDesc').val(descVal);
					$dataTarget.children('.imgHtmlDesc').val(htmlDescVal);
					$dataTarget.children('.imgURL').val(urlVal);
					// close modal
					$('#edit_image').jqmHide();
					
				}
			}
		});	
	});
	
	// ----------------------------------delete image-----------------------------------
	// asynch delete of image: confirm on click
	$('.gallery-del').live('click', function(e){
		var that = this;
		e.preventDefault();
		confirm('Remove this image?', function () { deleteGalleryImage(that); } );

		return false;
	});
	// actual delete function
	function deleteGalleryImage ( that )
	{	
		var imageName = $(that).attr('rel');	

		// ajax delete
		$.ajax({
			url: deletePath,
			type: 'GET',
			data: {
				_method : "DELETE", 
				file_name: file_name,
				file: imageName
			},
			//beforeSend: function(){$loadingImage.show();},
			error: function (response) { 
				// openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"'); 
				$('#gallery_msg').html(strResponse).attr('class', 'errorMsg').show();
			},
			success: function (strResponse) 
			{	
				resp = $.parseJSON(strResponse);
				if ( typeof resp.success === "undefined" || !resp.success)
				{
					//openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"');
					$('#gallery_msg').html(strResponse).attr('class', 'errorMsg').show();
				}
				else // success: update the images
				{	
					// get rid of image 
					$(that).closest('li').fadeOut('slow',function(){$(that).closest('li').remove();});
				}
			}
		});	
	}
	

	
//-----------------------------------upload file-------------------------------------
	function enableUpload(){
		$('#uploadGalleryImgBtn').removeClass('disabled');
	}
	
	$(document).bind('drop dragover', function (e) {
		e.preventDefault();
	});
			
	$('#image_corral').not('li').live('click', function() {$("#fileupload").click()});
			
	$('#gallery_upload').fileupload({
		dataType: 'json',
		done: function (e, data) {
			// re-enable uploading, display error messages, and clear the upload field 
			//enableUpload();
			var result = data.result.files['0'],
				isImage = result.type.substring(1,5) == 'image';
			if( typeof result.error !== "undefined") 
				$("#gallery_msg").attr('class', 'errorMsg').html($("#gallery_msg").html() + result.name + ": " +result.error+ "<br>" ).show();
			else if (typeof result.fullsize_url === "undefined")
			{
				// fullsize image doesn't exist, set error message and delete original
				$("#gallery_msg").attr('class', 'errorMsg').html($("#gallery_msg").html() + result.name + ": is too large to handle please resize and try again<br>" ).show();
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
					resultDiv = "<li>"+
					"<div id='imageData"+result_id+"' class='imageData'>"+
						"<input type='hidden' class='imgDesc' value='' />"+
						"<input type='hidden' class='imgAlt' value='"+picture+"' />"+
						"<input type='hidden' class='imgPosn' value='0' />"+
						"<input type='hidden' class='imgHtmlDesc' value='' />"+
						"<input type='hidden' class='imgName' value='"+picture+"' />"+
						"<div class='image_wrap'>"+
							"<span class='option_navs' >"+
								"<a class='showImg' href='"+result.fullsize_url+"' ><img alt='View Page' src='../../images/view_icon.png'></a>"+
								"<a class='edit_image' href='#' rel='"+result_id+"'><img src='../../images/edit.png' /></a>"+
								"<a class='gallery-del' href='#' rel='"+picture+"'><img src='../../images/delete.png' ></a>"+
							"</span>"+

							"<img src='"+thumb_url+"' alt='"+picture+"'/>"+
						"</div>"+
					"</div>"+	
				"</li>";

				// place in the corral
				$('#image_pen').prepend(resultDiv);
				savePosns();
				// make it sortable
				$('#image_corral, #image_pen').sortable({
					connectWith: ".connected",
					stop: savePosns
				});
			
				// update fancybox gallery
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
			$('#gallery-progress .bar').css(
				'width',
				progress + '%'
			);
		},
		dropZone: $('#image_corral'),
		formData: {
			file_name: file_name,
			target_id: gallery_id,
			accept_file_types: "/\.(gif|jpe?g|png)$/i"
		},
		start: function (e){
			$('#gallery-progress').show();
			$('#gallery-no-progress').hide();
			$("#gallery_msg").html('');
			
		},
		stop: function (e) {
			$('#gallery-progress').hide();
			$('#gallery-no-progress').show();
		},
		maxChunkSize: 1000000
		
	});
});