jQuery(document).ready(function($) {

	// submit new image button
	$('#upLoadImgBtn').click(function(e){
		e.preventDefault();
		$('#imageUploader_form').submit();
	});
	/* *************************gallery************************* */
	
	// We only want these styles applied when javascript is enabled
	$('div.content').css('display', 'block');

	// Initialize Advanced Galleriffic Gallery
if ( $('#thumbs').length > 0)	
{	
	var gallery = $('#thumbs').galleriffic({
		delay:                     2500,
		numThumbs:                 6,
		preloadAhead:              10,
		enableTopPager:            false,
		enableBottomPager:         false,
		maxPagesToShow:            7,
		imageContainerSel:         '.slideshow',
		controlsContainerSel:      '#controls',
		captionContainerSel:       '#caption',
		loadingContainerSel:       '#loading',
		renderSSControls:          true,
		renderNavControls:         true,
		nextPageLinkText:          'Next &rsaquo;',
		prevPageLinkText:          '&lsaquo; Prev',
		enableHistory:             false,
		autoStart:                 false,
		syncTransitions:           true,
		defaultTransitionDuration: 900,
		enableKeyboardNavFunc: 		false,
		onSlideChange:             function(prevIndex, nextIndex) {
			// 'this' refers to the gallery, which is an extension of $('#thumbs')
			this.find('ul.thumbs').children()
				.eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
				.eq(nextIndex).fadeTo('fast', 1.0);
		},
		onPageTransitionOut:       function(callback) {
			this.fadeTo('fast', 0.0, callback);
		},
		onPageTransitionIn:        function() {
			var prevPageLink = this.find('a.prev').css('visibility', 'hidden');
			var nextPageLink = this.find('a.next').css('visibility', 'hidden');
					
			// Show appropriate next / prev page links
			if (this.displayedPage > 0)
				prevPageLink.css('visibility', 'visible');

			var lastPage = this.getNumPages() - 1;
			if (this.displayedPage < lastPage)
				nextPageLink.css('visibility', 'visible');

			this.fadeTo('fast', 1.0);
		},
		onImageAdded:              function(imageData, $li) {
			$li.opacityrollover({
				mouseOutOpacity:   onMouseOutOpacity,
				mouseOverOpacity:  1.0,
				fadeSpeed:         'fast',
				exemptionSelector: '.selected'
			});
		}
	});
	/*****************Event handlers for delete image*********************/
	$('.thumbs li').hover(
		function(){
			$(this).find('.thumb-del').show();
			
			},
		function(){
			$(this).find('.thumb-del').hide();
		}
	);
	
	function deleteImage ( that )
	{	
			var name = $(that).attr('name');
			var imageId = $(that).attr('rel'); 
			var pageId = $('#page_id').val();
			
			// find the remove item's and selected item's indexes
			var selIndex = ($('.thumbs a').index( $('.thumbs').children('.selected').children('a') )) /2 +1;
			var remIndex = ($('.thumbs a').index(that) -1) / 2 + 1;

			// ajax delete
			$.ajax({
				url: 'page_image.php',
				type: 'POST',
				data: {picture : "removep", picture_id : imageId, page_id : pageId},
				//beforeSend: function(){$loadingImage.show();},
				error: function (response) { 
					openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"'); },
				success: function (strResponse) {
				
				if ( $.trim(strResponse) != "success")
				{
					openBanner('error', 'Failed to Delete Image', '"' + strResponse +'"');
				}
				else // success: update the images
				{
					// change the main image if the deleted item is the selected one
						var $parent = $(that).parent();
						var imageName = "";
						if ($parent.hasClass('selected'))
						{
							//see if a next picture exists (1 is used because there is always a next page element)
							if ( $parent.next().length != 0  )
							{
								//change the slideshow image to the next image
								imageName = $parent.next().find('img').attr('src').replace('/thumb/','/original/');
								$('.slideshow').find('img').attr('src',imageName);
																// make the previous the new selected
								$parent.next().addClass('selected')
								$parent.next().css('opacity', 1);
								var exploded = $parent.find('img').attr('src').split('/');
								var oldName = exploded.pop();
								exploded = $parent.next().find('img').attr('src').split('/');
								var newName = exploded.pop();
								
								var newDesc = $('.thumb_img').html().replace(new RegExp(oldName, 'g'), newName);
								$('.thumb_img').html(newDesc);
								newDesc = $('.full_img').html().replace(new RegExp(oldName, 'g'), newName);
								$('.full_img').html(newDesc);
								
							}
							else if  ( $parent.prev().length != 0 )
							{
								// there is an image ahead instead, so change based on it
								imageName = $parent.prev().find('img').attr('src').replace('/thumb/','/original/');
								$('.slideshow').find('img').attr('src',imageName);
								// make the previous the new selected
								$parent.prev().addClass('selected')
								$parent.prev().css('opacity', 1);
								
								var exploded = $parent.find('img').attr('src').split('/');
								var oldName = exploded.pop();
								exploded = $parent.prev().find('img').attr('src').split('/');
								var newName = exploded.pop();
								
								var newDesc = $('.thumb_img').html().replace(new RegExp(oldName, 'g'), newName);
								$('.thumb_img').html(newDesc);
								newDesc = $('.full_img').html().replace(new RegExp(oldName, 'g'), newName);
								$('.full_img').html(newDesc);
							}
							else 
							{
								// that was the last image, show 'no image'
								imageName = 'images/no_image_listing.jpg';
								$('.slideshow').find('img').attr('src',imageName);
								$('.image-desc').html('');
							}
						}

						gallery.removeImageByHash(name);
						openBanner('success', 'Image Deleted');
						$('#image_msg').html('Successfully Deleted').attr('class', 'successMsg').show();
					}
				}
			});
			
	}
	$('.thumb-del').live('click', function(e){
		var that = this;
		e.preventDefault();
		confirm('Remove this image?', function () { deleteImage(that); } );

		return false;
	});
	

	
	/**************** Event handlers for custom next / prev page links **********************/

	gallery.find('a.prev').click(function(e) {
		gallery.previousPage();
		e.preventDefault();
	});

	gallery.find('a.next').click(function(e) {
		gallery.nextPage();
		e.preventDefault();
	});


	/****************************************************************************************/
	/********************** Attach click event to the Add Image Link ************************/	
	// Initially set opacity on thumbs and add
	// additional styling for hover effect on thumbs
	var onMouseOutOpacity = 0.67;
	$('#thumbs ul.thumbs li, div.navigation a.pageLink').opacityrollover({
		mouseOutOpacity:   onMouseOutOpacity,
		mouseOverOpacity:  1.0,
		fadeSpeed:         'fast',
		exemptionSelector: '.selected'
	});
	

}
	
	// create banner if error/success message was posted
	var imgMsg = $('#image_msg').html();
	if( imgMsg ) {
		if ( $('#image_msg').hasClass('successMsg') ) {
			imgMsgClass = 'success';
			imgMsgHeader = imgMsg;
			imgMsg = '';
		} else if ( $('#image_msg').hasClass('errorMsg') ) { 
			imgMsgClass = 'error';
			imgMsgHeader = 'Error Uploading Image';
		}
		openBanner(imgMsgClass, imgMsgHeader, imgMsg)
	}
	
	// hide the image when opening the page
	if ( $('#image-wrap').find('.errorMsg, .successMsg').length < 1)
	{
		$('#image-wrap').hide();
		$('.slideshow').hide();
	}
	
	$('#image-toggle').click(function(){
		$('#image-wrap').slideToggle('slow');
		$('.slideshow').fadeToggle('slow');
		return false;
	});
});