$(document).ready(function() {
    var $idPattern = RegExp('[0-9]+$');

    // two vars now MOVED TO inc-homebanner.php
	//var aspectRatio = 0.410156; // images height to width scale
    //var $autoslideTime = 6000; //milliseconds // initial set auto slide
	
    // don't do anything if aspectRatio hasn't been set
	if(typeof aspectRatio !== 'undefined') 
	{
		// set initial container height
		var $container = $(".slidedeck");
	
		// set initial heights
		setHeights();

		// height adjustments to the banner and tabs
		$(window).resize( function() {setHeights()} );
	
	}
    // home banner button click effects PREV
	// missing clickable dots
    $('#slidedeck_frame #banner-button-box-prev').click(function() {

            clearInterval($slideInterval);

            var $currentID = $('dl.slidedeck dd.active').attr('id');
            var $current = $idPattern.exec($currentID); //get index value of current banner-image
            var $n;        
        
            if($('dl.slidedeck dd.active').prev('dl.slidedeck dd').length == 0) {
                $n = $('dl.slidedeck dd:last').attr('id'); //if first element in banner list, get id of last element
            }
            else {  
                $n = $('dl.slidedeck dd.active').prev('dl.slidedeck dd').attr('id'); //if not first element, get the previous banner
            }   
        
            $n = $idPattern.exec($n); //get index value of the banner to be shown (previous in order)
            
            $('#banner-image-' + $current).removeClass('active');
            $('#banner-image-' + $n).addClass('active');
    
            $('#banner-image-' + $current).fadeOut(100, function() {   
                $('#banner-image-' + $current).removeClass('showbanner');
            });

            $('#banner-image-' + $n).fadeIn(80, function() {
                $('#banner-image-' + $n).addClass('showbanner');
            });

            $slideInterval = setInterval(slide, $autoslideTime);
    });

    // home banner button click effects NEXT
	// missing clickable dots
    $('#slidedeck_frame #banner-button-box-next').click(function() {

            clearInterval($slideInterval);

            var $currentID = $('dl.slidedeck dd.active').attr('id');
            var $current = $idPattern.exec($currentID); //get index value of current banner-image
            var $n;        
        
            if($('dl.slidedeck dd.active').next('dl.slidedeck dd').length == 0) {
                $n = $('dl.slidedeck dd:first').attr('id'); //if last element in banner list, get id of first element
            }
            else {  
                $n = $('dl.slidedeck dd.active').next('dl.slidedeck dd').attr('id'); //if not last element, get the next banner
            }   
        
            $n = $idPattern.exec($n); //get index value of the next banner to be shown
            
            $('#banner-image-' + $current).removeClass('active');
            $('#banner-image-' + $n).addClass('active');
    
            $('#banner-image-' + $current).fadeOut(100, function() {   
                $('#banner-image-' + $current).removeClass('showbanner');
            });

            $('#banner-image-' + $n).fadeIn(80, function() {
                $('#banner-image-' + $n).addClass('showbanner');
            });

            $slideInterval = setInterval(slide, $autoslideTime);
    });
	
	
	// home banner button click effects
    $('.click-dots').click(function() {

        clearInterval($slideInterval);

        if(!($(this).hasClass('click-dots-active'))) { //active banner-button does not need to swap classes

            var $nID = $(this).attr('id'); // click-dots-{$b}
            var $currentID = $('dl.slidedeck dd.active').attr('id');

            var $n = $idPattern.exec($nID); //get index value of clicked banner-button
            var $current = $idPattern.exec($currentID); //get index value of current banner-image
        

            $('#banner-image-' + $current).removeClass('active');
            $('#banner-image-' + $n).addClass('active');
    
            $('#banner-image-' + $current).fadeOut(100, function() {   
                $('#banner-image-' + $current).removeClass('showbanner');
            });

            $('#banner-image-' + $n).fadeIn(80, function() {
                $('#banner-image-' + $n).addClass('showbanner');
            });
			
			// click-dots-{$b}
            $('#click-dots-' + $current).removeClass('click-dots-active'); 
            $('#click-dots-' + $n).addClass('click-dots-active');

            $slideInterval = setInterval(slide, $autoslideTime);
        }
    });

    
    // home banner auto slide
	// will only load if more than one pic - condition below
    function slide() {
		var $currentID = $('dl.slidedeck dd.active').attr('id'); //get id of current banner
		var $current = $idPattern.exec($currentID); //get index value of current banner
		var $n;        

		if($('dl.slidedeck dd.active').next('dl.slidedeck dd').length == 0) {
			$n = $('dl.slidedeck dd:first').attr('id'); //if last element in banner list, get id of first element
		}
		else {  
			$n = $('dl.slidedeck dd.active').next('dl.slidedeck dd').attr('id'); //if not last element, get the next banner
		}   

		$n = $idPattern.exec($n); //get index value of the next banner to be shown

		$('#banner-image-' + $current).removeClass('active');
		$('#banner-image-' + $n).addClass('active');
		
		$('#banner-image-' + $current).fadeOut(2000, function() {
			$('#banner-image-' + $current).removeClass('showbanner');
		});

		$('#banner-image-' + $n).fadeIn(2500, function() {
			$('#banner-image-' + $n).addClass('showbanner');
		});
		
		$('#click-dots-' + $current).removeClass('click-dots-active');
		$('#click-dots-' + $n).addClass('click-dots-active');
    }

	// only load fader if more than one pic
    if($('dl.slidedeck dd:first').attr('id')!=$('dl.slidedeck dd:last').attr('id')){
		var $slideInterval = setInterval(slide, $autoslideTime);
	}



    function setHeights(){
        tmpWidth = $container.width();
        computedHeight = tmpWidth * aspectRatio;
        $container.height( computedHeight );
    }

});
