function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function loadHeaderHoverImages() {
    MM_preloadImages('images/top-products-button2-hover.png', 'images/top-blog-button2-hover.png');
}

$(document).ready(function() {
	/////////////////////////////////////////////////
	//                ALL SITES                    //
	/////////////////////////////////////////////////
		
	// standard lightbox
	$('.lightbox').live("click", function(e) {
		e.preventDefault();
		
		$tinyBody = $(this).closest('.mce-content-body');
		
		// don't trigger if in edit mode
		if($tinyBody.length)
		{
			return false;
		}
		
		if( typeof $(this).attr('href') === "undefined" ) 
		{
			return false;
		}	
		
		$.fancybox({
		'transitionIn' : 'none',
		'transitionOut' : 'none',
		'href': $(this).attr("href")
		});
	});
	
	$(".video-link, .fullscreenbox").live("click", function(e) {
		var win_width = $(window).width(),
			win_height = $(window).height(),
			vid_aspect = 16/9,
			vid_width = win_width,
			vid_height = win_height,
			vid_margin = 5;
			
		$tinyBody = $(this).closest('.mce-content-body');
		
		// don't trigger if in edit mode
		if($tinyBody.length)
		{
			return false;
		}
	
	
		if( typeof $(this).attr('href') === "undefined" ) 
		{
			return false;
		}	

		// figure out if we are doing large sized display
		// do large if web page/pdf
		
		if(win_height * vid_aspect > win_width)
		{
			// use the width
			if (win_width < 410)
				vid_width = win_width - 5;
			else vid_width = win_width -50;
			vid_height = vid_width/vid_aspect;
		} else {
			if (win_width < 410)
				vid_height = win_height - 5;
			else vid_height = win_height - 50;
			vid_width = vid_height * vid_aspect;
			
		}
		if(win_width < 410) 
		{
			vid_margin = 0;
			$('#fancybox-close').css({'top' : 0, 'right' : 0});
		} else {
			$('#fancybox-close').css({'top' : '-15px', 'right' : '-15px'});
		}
		
		$.fancybox({
	  		'hideOnContentClick': false,
			'padding' :0,
			'margin' : vid_margin,
  			'autoScale': true,
			'href' 	: $(this).attr("href").replace(new RegExp("watch\\?v=", "i"), 'v/'),
			'type' 	: 'iframe',
			'width'	: vid_width,
			'height': vid_height
		});
		
		return false;
	});
	
	// enable 'force download' of links
	$("body").on('click', 'a', function(e){
		if($(this).attr('rel') =='force_download')
		{
			var cur_href = $(this).attr('href'),
				url_operator = '?';

			if(cur_href.split('?').length > 1) url_operator = '&';

			$(this).attr('href', $(this).attr('href') + url_operator + "force_download")
		}	
	});
	

	
	/////////////////////////////////////////////////
	//            SITE SPECIFIC                    //
	/////////////////////////////////////////////////
	$(".portfolio > a").fancybox({
	  		'hideOnContentClick': true,
			'overlayColor': '#111111',
			'overlayOpacity': 0.75
	});
});
