$(document).ready(function(){
	// handle form submissions
	$("#submit-btn").live("click", function(e) {
		e.preventDefault();
		$('#form_data').submit();
	});	

	// var config passed from head: passes all php config values
	var adminUrl	= $('#banners_admin_url').val(),
		ajaxPath	= adminUrl + "modules/banners/ajax.php",
		deletePath	= adminUrl + "js/jquery.fileupload/upload_handler.php",
		upload_path	= $('#uploadPath').val(),
		upload_url	= $('#uploadUrl').val(),
		file_name	= 'banners';
		tableName	= $('#image_table_name').val();

	// show controls on hover
	$('#bannerUploads .image_wrap').live({
		mouseenter:	function(){	$('.option_navs').hide(); $(this).children('.option_navs').show();},
		mouseleave: function(){$('.option_navs').hide();}
	});
	
	// create banner if error/success message was posted
	var bannerMsg = $('#banner_msg').html();
	if( bannerMsg  ) {
		if ( $('#banner_msg').hasClass('successMsg') ) {
			bannerMsgClass = 'success';
			bannerMsgHeader = bannerMsg;
			bannerMsg = '';
		} else if ( $('#banner_msg').hasClass('errorMsg') ) { 
			bannerMsgClass = 'error';
			bannerMsgHeader = 'Error Uploading Image';
		}
		openBanner(bannerMsgClass, bannerMsgHeader, bannerMsg)
	}
	
	// clear the current error message when upload location pressed
	$('#bannerUploads #bannerUploader_file').click(function(){
		$("#bannerUploads #banner_msg").hide();
	});
	$('#bannerUploads #bannerUploader_file').change(function(){
		var thisfile = $(this).val().split("\\").pop();
		$("#banner-fake-file").html(thisfile);//.split("\").pop();' 
	});
	
	// called after every moved item: updates the image position asynchronously
	function savePosns(){
		// get sort-order data
		// get all id's from corral
		var postData = {
			option: 			'save_posn',
			corralIds: 			[], 
			penIds: 			[],
			uploadPath: 		upload_path,
			uploadUrl:			upload_url,
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
			data: postData,
		});	
	};
	
	// enable sortables /
	$('#image_corral, #image_pen').sortable({
		connectWith: ".connected",
		stop: savePosns
	});
			
	// update fancybox banner
	$("a.showImg").fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'fade',
		'speedIn'		:	600, 
		'speedOut'		:	400, 
	});
	
	
	// start uploads display closed
//	$('#banner-toggle-wrap').hide();
	
	// set up modal
	$('#edit_image').jqm({overlay: 60, modal: true, trigger: false, target:false})
		.jqmAddClose('.close');
	// edit image modal
	$('.edit_image').live('click', function(e){
		e.preventDefault();
		
		// clear error messages
		$('#err_link').html('');
		$('#err_alt').html('');
		$('#banner_msg').html('');
		
		// load the modal with data
		var img_alt = $(this).parents('.imageData').children('.imgAlt').val();
		var img_link = $(this).parents('.imageData').children('.imgLink').val();
		var img_desc = $(this).parents('.imageData').children('.imgDesc').val();
		var img_html_desc = $(this).parents('.imageData').children('.imgHtmlDesc').val();
		var img_id = $(this).attr('rel');

		$('#image_id').val(img_id);
		$('#alt').val(img_alt);
		$('#link').val(img_link);
		$('#desc').val(img_desc);
		tinyMCE.get('html_desc').setContent(img_html_desc);
		// call the modal		
		$('#edit_image').jqmShow();
			
	});

	// ---------------------------------asynch actions---------------------------------------
	
	// ------------------------------------edit image------------------------------------
	$('#image_save_btn').live('click', function(e){
		e.preventDefault();
		var altVal = $('#alt').val();
		var linkVal = $('#link').val();
		var descVal = $('#desc').val();
		var htmlDescVal = tinyMCE.get('html_desc').getContent();
		var imageId = $('#image_id').val();
		var $dataTarget = $('#imageData'+imageId);
		$.ajax({
			url: ajaxPath,
			type: 'POST',
			data: {
				option : 'edit_image',
				alt : altVal, 
				link : linkVal, 
				desc: descVal, 
				html_desc: htmlDescVal,
				image_id: imageId,
				uploadPath: 		upload_path,
				uploadUrl:			upload_url,
				image_table_name:	tableName 
			},
			//beforeSend: function(){$loadingImage.show();},
			error: function (response) { 
				// openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"'); 
				$('#banner_msg').html(strResponse).attr('class', 'errorMsg').show();
			},
			success: function (strResponse) 
			{
				var resp = $.parseJSON(strResponse);
				if ( typeof resp.error !== "undefined")
				{
					if(typeof resp.error.alt !== "undefined")
					{
						//openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"');
						$('#err_alt').html(resp.error.alt).attr('class', 'errorMsg').show();
					}
					
					if(typeof resp.error.link !== "undefined")
					{
						//openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"');
						$('#err_link').html(resp.error.link).attr('class', 'errorMsg').show();
					}
					
				}
				else // success: update the images
				{	
					
					//set success msg & clear modal fields
					$('#banner_msg').html('Successfully Updated Image Info.').attr('class', 'successMsg').show();
					$('#err_alt').hide();
					
					// update info
					$dataTarget.children('.imgAlt').val(altVal);
					$dataTarget.children('.imgLink').val(resp.link);
					$dataTarget.children('.imgDesc').val(descVal);
					$dataTarget.children('.imgHtmlDesc').val(htmlDescVal);
					// close modal
					$('#edit_image').jqmHide();
					
				}
			}
		});	
	});
	
	// ----------------------------------delete image-----------------------------------
	// asynch delete of image: confirm on click
	$('.banner-del').live('click', function(e){
		var that = this;
		e.preventDefault();
		confirm('Remove this image?', function () { deleteBannerImage(that); } );

		return false;
	});
	// actual delete function
	function deleteBannerImage ( that )
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
				$('#banner_msg').html(strResponse).attr('class', 'errorMsg').show();
			},
			success: function (strResponse) 
			{
				resp = $.parseJSON(strResponse);
				if ( typeof resp.success === "undefined" || !resp.success)
				{
					//openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"');
					$('#banner_msg').html(strResponse).attr('class', 'errorMsg').show();
				}
				else // success: update the images
				{	
					// get rid of image 
					$(that).closest('li').fadeOut('slow',function(){$(that).closest('li').remove();});
				}
			}
		});	
	}
	
	$('#image_corral').not('li').live('click', function() {$("#fileupload").click()});
				
	$('#banner_upload').fileupload({
		dataType: 'json',
		done: function (e, data) {
			// re-enable uploading, display error messages, and clear the upload field 
			//enableUpload();
			var result = data.result.files['0'],
				isImage = result.type.substring(1,5) == 'image';
			if( typeof result.error !== "undefined") 
				$("#banner_msg").attr('class', 'errorMsg').html($("#banner_msg").html() + result.name + ": " +result.error+ "<br>" ).show();
			else if (typeof result.banner_url === "undefined" && typeof result.rotating_url === "undefined" )
			{
				// fullsize image doesn't exist, set error message and delete original
				$("#banner_msg").attr('class', 'errorMsg').html($("#banner_msg").html() + result.name + ": is too large to handle please resize and try again<br>" ).show();
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
								"<a class='banner-del' href='#' rel='"+picture+"'><img src='../../images/delete.png' ></a>"+
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
			
				// update fancybox banner
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
			$('#banner-progress .bar').css(
				'width',
				progress + '%'
			);
		},
		dropZone: $('#image_corral'),
		formData: {
			file_name: file_name,
		//	target_id: banner_id,
			accept_file_types: "/\.(gif|jpe?g|png)$/i"
		},
		start: function (e){
			$('#banner-progress').show();
			$('#banner-no-progress').hide();
			$("#banner_msg").html('');
			
		},
		stop: function (e) {
			$('#banner-progress').hide();
			$('#banner-no-progress').show();
		},
		maxChunkSize: 1000000
		
	});
});
