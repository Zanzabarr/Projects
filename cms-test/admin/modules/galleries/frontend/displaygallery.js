$(document).ready(function() {

	// set initial width of slider galleries
	$('.jsGallery:has( .sliderWrap )').initSlider();
	
	// resize during resize transitions
	$(window).resize(function () {
		$('.respGallery.multiImage').each( function(){
				doResize( $(this) ); 
				resizeThumbs( $(this) );
		});
		$('.jsGallery:has( .sliderWrap )').setSliderWidth();
		$("a.gallery_group").each(setGalleryFancyWidth);
	});
	
	// if this is a single gallery, reveal it's captions
	$('.singleGallery').closest('.jsGallery').find('.captionWrap, .HTMLcaptionWrap .html-caption').css({'display':'block'});
	
	// only show thumbs if more than one image presented
	$('.galleryNavWrap').each(function(){
		var $navWrap = $(this),
			$gallery = $(this).closest('.jsGallery'),
			liCount = $navWrap.find('li').length;
		if(liCount > 1) $navWrap.css('display', 'block');
		else if (liCount == 0 ) // hide the gallery
		{
			$gallery.hide().after("<p>No Images Currently Available, Please Try Again Later</p>");
		}  // there is one image, show it
		else 
		{
			$gallery.find('.slide').show();
			$gallery.find('.galleryNav').hide();
		}	
	});
	
	//set the initial caption/htmlcaption heights
	$('.captionWrap').each(function (){
		$(this).height($(this).children('li').first().height());
	});
	
	// start the cycle routine
	$('.jsGallery .respGallery img').not('.placeholder').css({'display':'block'});
	
	$('.jsGallery .galleryNav').css({'display':'block'});
	// reveal the inset captions if present, and set opacity (needed for ie8)
	$('.insetCaption').css({opacity:0.8, display:'block'})
	
	$('.respGallery').each(function(){
		var $this = $(this);
		
		if($this.find('.slide').length > 1)
		{
			$this.addClass('multiImage');
			resizeThumbs($this);
					var $navUL = $this.closest('.jsGallery').find('.sliderWrap');
			if($navUL.length)// if using slider
			{
				var $nextButton = $navUL.find('.slider-right').add($this.siblings('.galleryNav').find('.next')),
					$prevButton = $navUL.find('.slider-left').add($this.siblings('.galleryNav').find('.prev'));
			}
			else 
			{
				var $navUL = $this.closest('.jsGallery').find('.galleryNavWrap'),
					$nextButton = $this.siblings('.galleryNav').find('.next'),
					$prevButton = $this.siblings('.galleryNav').find('.prev');
			}
	
			$this.cycle({
				// Just being fancy, you can add an extra class to
				// the rest of the slides and use it here.
				slideExpr: "img:not(.placeholder)",
				// Don't let the plugin handle the sizes
				slideResize: false,
				containerResize: false,
				next: 				$nextButton,
				prev: 				$prevButton,
				timeout:			parseInt($this.find('.cycle_speed').val()),
				pager: 				$navUL,
				clickCount:			0,
				pagerAnchorBuilder: function(idx, slide){
					return $navUL.find('li:eq(' + idx  + ')');
				},
				before: function(curr,next,obj) {
					var data_cycle_image = $(next).attr('data-cycle-image');
					
					// lazyload images
					// once loaded, resize (covers the posibility of auto-cycle during resize)
					if (typeof data_cycle_image !== 'undefined' && data_cycle_image !== false) {  
						// Pause Cycle to prevent auto-cycle until we have loaded and resized the image  
						$this.cycle('pause'); 
						
						// temporarily hide the image as there may be a flicker of bad positioning between image load and resize 
						$(next).css('visibility', 'hidden');
						
						// assign the image src, getting a fresh image each time (no cache) to keep ie from choking on the .load
						$(next).attr('src', data_cycle_image + "?" + new Date().getTime()).removeAttr('data-cycle-image').load(function(){
							
							// figure out the current size and set image margins
							doResize($this, $(next));
							
							// now that it is loaded and sized, make visible and restart cycle
							$(next).css('visibility', 'visible');
							$this.cycle('resume'); // Resume Cycle now everything is in place  						
						});
					} 
					else  // image is already loaded, nothing fancy, just resize the margins
						doResize($this, $(next)); 
						
					$('.gallery-caption').hide();
					$('.gallery-caption').html("").append($(next).attr('title')).fadeIn('slow');
					updateNavPosn(obj);	
					
					updateDesc(curr, next,obj);
				},
				updateActivePagerLink: function(pager, currSlideIndex) { 
					// this is only used when slider is part of the gallery
					if(pager.hasClass('sliderWrap'))
					{
						setActiveSlide($(pager), $(pager).find('li').filter('li:eq('+currSlideIndex+')'));
						
					}
					else
					{
						$(pager).find('li').removeClass('activeLI') 
								.filter('li:eq('+currSlideIndex+')').addClass('activeLI'); 
					}
				}				
			});
		} else // single image
		{
			$this.find('.placeholder').remove();
		}
	});

	
	// only launch the fancybox for images with href set
	$("a.gallery_group").each(setGalleryFancyWidth);
	
	function setGalleryFancyWidth()
	{
		var attr = $(this).attr('href'),
			win_width = $(window).width(),
			win_height = $(window).height()
			fancyData = {
				'transitionIn'	:	'elastic',
				'transitionOut'	:	'fade',
				'speedIn'		:	600, 
				'speedOut'		:	400,
				'showNavArrows'	:	true
			};
			
			if(win_width < 550)
			{
				fancyData.margin = 5;
				fancyData.padding = 5;
				
			}
			
			if(win_width < 550) 
			{
				$("#fancybox-right, #fancybox-left").unbind('mouseenter mouseleave');
				$('#fancybox-close').css({'top' : 0, 'right' : 0});
				$("#fancybox-left-ico").css({left:"0"});
				$("#fancybox-right-ico").css({left:"auto", right:"0"});

				
			} else {
				
				$('#fancybox-close').css({'top' : '-15px', 'right' : '-15px'});
				$("#fancybox-left-ico").css({left:"-99999px"});
				$("#fancybox-right-ico").css({left:"-99999px", right:"auto"});
				
				$("#fancybox-left").hover(function(){
					$("#fancybox-left-ico").css({left:"20px"});
				},function(){
					$("#fancybox-left-ico").css({left:"-99999px"});
				});
				
				$("#fancybox-right").hover(function(){
					$("#fancybox-right-ico").css({right:"20px",left:"auto"});
				},function(){
					$("#fancybox-right-ico").css({right:"auto",left:"-99999px"});
				});

			}
			
			

			
		
		// For some browsers, `attr` is undefined; for others,
		// `attr` is false.  Check for both.
		if (typeof attr !== 'undefined' && attr !== false) {
			$(this).fancybox(fancyData);
		}
		
	}

	$('.gallery-previous').click(function(e){
		e.preventDefault();
		var $gallery = $(this).closest('.jsGallery'),
			$navWrap = $gallery.find('.galleryNavWrap'),
			wrapWidth = $navWrap.find('.galleryNavInner').innerWidth(),
			$navUL = $navWrap.find('.galleryNavOuter > ul'),
			ulWidth	= $navUL.innerWidth(),
			curMargin = parseInt($navUL.css('margin-left')),
			newMargin = curMargin + wrapWidth,
			maxMargin = 0,
			finalMargin = newMargin > maxMargin ? maxMargin : newMargin,
			$thumbNext = $gallery.find('.gallery-next');
			
			
		if (ulWidth > wrapWidth)
		{
			$thumbNext.children('a').fadeIn('slow');
			$navUL.stop(true,false).animate({'margin-left':  finalMargin + 'px'});
			if(finalMargin == maxMargin) $(this).children('a').fadeOut('slow');	
		}
	});
	
	$('.gallery-next').click(function(e){
		
		e.preventDefault();
		var $gallery = $(this).closest('.jsGallery'),
			$navWrap = $gallery.find('.galleryNavWrap'),
			wrapWidth = $navWrap.find('.galleryNavInner').innerWidth(),
			$navUL = $navWrap.find('.galleryNavOuter > ul'),
			ulWidth	= $navUL.innerWidth(),
			curMargin = parseInt($navUL.css('margin-left')),
			newMargin = curMargin - wrapWidth,
			maxMargin = -(ulWidth - wrapWidth  + wrapWidth * .02),
			
			finalMargin = newMargin < maxMargin ? maxMargin : newMargin,
			$thumbPrev = $gallery.find('.gallery-previous');
		
		
		if (ulWidth > wrapWidth)
		{
			$thumbPrev.children('a').fadeIn('slow');
			// show the previous button
			$navUL.stop(true,false).animate({'margin-left':  finalMargin + 'px'});
			// hide the button IF this is the end
			if(finalMargin == maxMargin) $(this).children('a').fadeOut('slow');		
		}

	});
	
	$('.slider-right').click(function(e){

		// if this is part of a gallery, cycle will take care of this functionality
		// so exit now
		if($(this).closest(".jsGallery").find('.galleryWrap').length) return false;
		
		e.preventDefault();

		var $slider = $(this).closest('.sliderWrap'),
			$next = $slider.find('.activeLI').next(),
			hasGallery = $(this).closest('.jsGallery').find('galleryWrap').length,
			hasActiveBorder = $(this).closest('.sliderWrap').hasClass('sliderActiveBorder');
	

		if(!$next.length) $next = $slider.find('.sliderInner > li:first-child');
		// if all the remaining slides are already displayed, go to the front
		// note: if a gallery is attached this won't work so don't even try
		else if(!hasGallery && !hasActiveBorder)
		{
			// get all the remaining slides
			$slider = $(this).closest('.sliderWrap');
			$frontSlide = getfrontSlide($slider);
			$remainingSlides = $slider.find('ul > li').slice($frontSlide.index()+1);
			
			// get the current maxWidth of the display area
			maxWidth = $slider.width();
			
			// if the width of $remainingSlides <= maxWidth
			//    move to the first slide in the show
			workingWidth = $frontSlide.width();
			allDisplayed = true;
			$remainingSlides.each(function(){
				// if current item width + total item width is greater than maximum item width, stop
				curSlideWidth = $(this).outerWidth();
				
				workingWidth += curSlideWidth;
				
				if(workingWidth > maxWidth) 
				{
					allDisplayed = false;
					return false;
				}
			});
			// note: if this is part of a gallery, the gallery's pager will override this placement
			//       cycle's updateActivePagerLink function runs after this function and will, therefore, do the next link regardless
			//       this is a desirable behaviour since we want this slide to continue to match the slide in the cycle gallery
			if(allDisplayed) $next = $slider.find('.sliderInner > li:first-child');
		}

		setActiveSlide($slider, $next);

	});
	
	$('.slider-left').click(function(e){
		// if this is part of a gallery, cycle will take care of this functionality
		// so exit now
		if($(this).closest(".jsGallery").find('.galleryWrap').length) return false;
		
		e.preventDefault();

		var $gallery = $(this).closest('.jsGallery'),
			$slider = $(this).closest('.sliderWrap'),
			$next = $slider.find('.activeLI').prev();
	

		if(!$next.length) $next = $slider.find('.sliderInner > li:last-child');
	
		setActiveSlide($slider, $next);

	});

	// swipe handling
	$(".jsGallery .galleryWrap")
		.on('touchstart', function (e) {
			$(this).data("xDown", e.originalEvent.touches[0].clientX);
		})
		.on('touchmove',function (e) {
			
			var xUp = e.originalEvent.touches[0].clientX,
				slideThreshold = 50;
			if($(this).data('xDown'))
			{
				if( $(this).data("xDown") + slideThreshold < xUp ) 
				{
					$(this).find('.respGallery').cycle('prev');
					$(this).data('xDown', null);
				}
				else if( $(this).data("xDown") - slideThreshold > xUp  ) 
				{
					$(this).find('.respGallery').cycle('next');
					$(this).data('xDown', null);
				}
			}
		})
	;
	
	
});	



$.fn.initSlider = function () 
{
	
	$(this).find('ul li:first-child')
		   .addClass('activeLI')
		   .addClass('frontSlide');
	$(this).setSliderWidth();
	return this;
	
}

$.fn.setSliderWidth = function ($frontSlide) 
{
		var $activeGallery = $(this),
			// find the maximum width
			maxWidth = $activeGallery.children('.sliderWrap').width(),		
			// find the current first item
			$frontSlide = typeof $frontSlide == "undefined" ? getfrontSlide($activeGallery) : $frontSlide,
			// get all elements after the $frontSlide
			$remainingSlides = $activeGallery.find('ul > li').slice($frontSlide.index()+1),
			// get all elements before the frontSlide
			$leadingSlides =  $activeGallery.find('ul > li').slice(0,$frontSlide.index()),
			// curWidth must include the first slide
			workingWidth = $frontSlide.outerWidth();
	
		// iterate over items starting with first item
		$remainingSlides.each(function(){
			// if current item width + total item width is greater than maximum item width, stop
			curSlideWidth = $(this).outerWidth();
			if(workingWidth + curSlideWidth  > maxWidth) return false;
			
			// otherwise, add current item width to total item window
			workingWidth += curSlideWidth;
		});
		
 
		// apply total item width to wrap (css ensures this wrap stays centered)
		$activeGallery.find('.sliderOuter').stop().animate({width: workingWidth}, 200);
		return this;
}

// sets the active slide and adjusts the front slide and resets the total width
function setActiveSlide($activeSlider, $newActiveSlide)
{
	var newActiveIndex = $newActiveSlide.index(),
		$oldFrontSlide = $activeSlider.find('ul li.frontSlide'),
		// find the maximum width
		maxWidth = $activeSlider.width(),
		// get all the slides
		$slides = $activeSlider.find('ul > li'),
		// get all elements after the $newActiveSlide
		$remainingSlides = $slides.slice(newActiveIndex+1),
		// get all elements before the $newActiveSlide
		$leadingSlides = $($slides.slice(0,newActiveIndex).get().reverse()),
		// workingWidth must include the first slide
		workingWidth = $newActiveSlide.outerWidth(),
		// make the $newActiveSlide the $newFrontSlide
		$newFrontSlide = $newActiveSlide;
		// find the curent slider margin, this is what we'll edit to move the slider
		curentSliderMargin = parseInt($activeSlider.find('.sliderInner').css('marginLeft')),
		// this is how much the margin will change
		sliderMarginChange = 0,
		hasGallery = $activeSlider.closest('.jsGallery').find('galleryWrap').length,
		hasActiveBorder = $activeSlider.hasClass('sliderActiveBorder');
		
	rCount = 0;
	$remainingSlides.each(function(){
		// if current item width + total item width is greater than maximum item width, stop
		curSlideWidth = $(this).width();
		if(workingWidth + curSlideWidth  > maxWidth) return false;
		
		// otherwise, add current item width to total item window
		workingWidth += curSlideWidth;
		rCount++;
	});
	
	// if there is still room, add as many of the leading slides as possible
	// if we've used up all of the remaining slides, check the leading slides
	//    this is to cover the case where their is room form more slides in front but not in back
	if(rCount == $remainingSlides.length)
	{
		$leadingSlides.each(function(i){
			curSlideWidth = $(this).width();
			if(workingWidth + curSlideWidth  > maxWidth)return false;
			
			//else add the width and make it the newFrontSlide
			workingWidth += curSlideWidth;
			$newFrontSlide = $(this);
		});
	}
	
	// if this gallery doesn't show highlighted active element, make the active slide the same as the front slide
	//    by doing this, we make sure that clicking prev while the first slide is active, the slider will jump to the end
	//    and make the first visible slide the active slide. This way, further prev clicks will move appropriately. Meanwhile, 
	//    the logic for handling next clicks will still make next clicks work appropriately
	if(!hasGallery && !hasActiveBorder) $newActiveSlide = $newFrontSlide;
	   
	// remove the old activeLI and frontSlide classes
	$slides.removeClass('activeLI').removeClass('frontSlide');
	
	// add the new activeLI and frontSlide classes to the appropriate slide
	$newActiveSlide.addClass("activeLI");
	$newFrontSlide.addClass("frontSlide");
	
	// now we need to actually move the dang thing...duh
	// so, we need the array of LIs between oldFront and newFront, inclusive

	newIndex = $newFrontSlide.index();
	if(newIndex)
	{
		$slides.slice(0,newIndex).each(function(){
			
			sliderMarginChange -= $(this).outerWidth();
		});
	}
		
	$activeSlider.find('.sliderInner').stop().animate({"margin-left": sliderMarginChange + "px"}, 300);
	$activeSlider.find('.sliderOuter').stop().animate({width: workingWidth + "px"}, 200);
}


// find the front slider slide
function getfrontSlide($activeGallery)
{
	if($activeGallery.find('.sliderInner .frontSlide').length)
	{
		return $activeGallery.find('.sliderInner .frontSlide');
	}
	else // set the frontSlide class and return the item
	{
		$frontSlide = $activeGallery.find('sliderInner li:first-child')
		$frontSlide.addClass('frontSlide');
		return $frontSlide;
	}
}

// square thumbs
function resizeThumbs($this)
{
	var newWidth = $this.parent('div').width();
		$navWrap = $this.closest('.jsGallery').find('.galleryNavWrap'),
		wrapWidth = $navWrap.find('.galleryNavInner').innerWidth(),
		$navUL	= $navWrap.find('ul'),
		ulWidth	= $navUL.innerWidth();
			
		// if the thumbs are smaller than the container, center them
		if (ulWidth < wrapWidth)
		{
			// center the ul
			$navUL.css({'margin-left' : (wrapWidth - ulWidth - newWidth*.02) / 2 + 'px'});
			
			
		}else $navUL.css({'margin-left' : 0});	
}

function updateDesc(curr,next,obj)
{	

	var $gallery = $(next).closest('.jsGallery'),
		next_idx = obj.clickCount > 1 ? obj.nextSlide : 0,
		old_idx = obj.currSlide,
		$captionWrap = $gallery.find('.captionWrap'),
		$oldCaption = $captionWrap.children('li:eq(' + old_idx  + ')'),
		$newCaption = $captionWrap.children('li:eq(' + next_idx  + ')'),
		$HTMLcaptionWrap = $gallery.find('.HTMLcaptionWrap'),
		$oldHTMLcaption = $HTMLcaptionWrap.children('li:eq(' + old_idx  + ')'),
		$newHTMLcaption = $HTMLcaptionWrap.children('li:eq(' + next_idx  + ')');

		if ($newCaption.height() != $oldCaption.height())
			$captionWrap.stop(true,true).animate({'height':$newCaption.height()});
		$oldCaption.stop(true,true).fadeOut('slow');
		$newCaption.stop(true, true).fadeIn('slow');
		
	//	if ($newHTMLcaption.height() != $oldHTMLcaption.height())
	//		$HTMLcaptionWrap.stop(true,false).animate({'height':$newHTMLcaption.height()});
		$oldHTMLcaption.stop(true,false).hide();
		$newHTMLcaption.stop(true,true).fadeIn('slow');
}


function updateNavPosn(obj)
{
	var $gallery = $(obj.$cont).closest('.jsGallery'),
		$navWrap = $gallery.find('.galleryNavWrap'),
		wrapWidth = $navWrap.find('.galleryNavInner').innerWidth(),
		$navUL = $navWrap.find('.galleryNavOuter > ul'),
		ulWidth	= $navUL.innerWidth(),
		targetSlide = obj.clickCount ? obj.nextSlide : 0,
		$thumbPrev = $gallery.find('.gallery-previous a'),
		$thumbNext = $gallery.find('.gallery-next a');
		
		obj.clickCount++;
	
		// only adjust position if the thumbs are narrower than their container
		if (ulWidth > wrapWidth)
		{
			// find the position of the selected element 

			// the currSlide
			// slideCount
			var unitWidth = ulWidth / obj.slideCount,
				newMargin = Math.round(-((targetSlide ) * unitWidth ) * 1000) / 1000
				maxMargin = -(ulWidth - wrapWidth  + wrapWidth * .02),
				finalMargin = newMargin < maxMargin ? maxMargin : newMargin;


			$navUL.stop(true,false).animate({'margin-left':  finalMargin + 'px'});
			
			if(finalMargin == 0 ) 
			{
				$thumbPrev.fadeOut('slow');
				$thumbNext.fadeIn('slow');
			}
			else if(finalMargin == maxMargin ) 
			{
				$thumbNext.fadeOut('slow');
				$thumbPrev.fadeIn('slow');
			}
			else
			{
				$thumbNext.fadeIn('slow');
				$thumbPrev.fadeIn('slow');
			}
		} 
		else 
		{
			$thumbPrev.hide();
			$thumbNext.hide();
		}

}

function calcResize()
{
	var $currentImg = $(".jsGallery .slide img:visible"),
		$sliderplaceholder = $('.placeholder');
	$currentImg.each(function(){
		$(this).css('margin-left', (($sliderplaceholder.width() - $(this).width()) / 2) + 'px');
		$(this).css('margin-top', (($sliderplaceholder.height() - $(this).height()) / 2) + 'px');
	});
}
	
function doResize($this, $currentImg)
{
	var	$sliderplaceholder = $this.find('.placeholder');
	
	// if current image isn't passed, find it dynamically
	if(typeof $currentImg === "undefined" ) $currentImg = $this.find(".slide img:visible");
	
	// resize the main image
	$currentImg.each(function(){
		$(this).css('margin-left', (($sliderplaceholder.width() - $(this).width()) / 2) + 'px');
		$(this).css('margin-top', (($sliderplaceholder.height() - $(this).height()) / 2) + 'px');
	});
}

