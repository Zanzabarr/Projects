
$(document).ready(function() {
	
	var curSrc;
	
	$('#home-icons-container .home-icon a').hover(function () {  
        curSrc = $(this).children('img').attr('src');
		curSrcArr = curSrc.split(".");
		newSrc = curSrcArr[0] + '-hover.' + curSrcArr[1];
		$(this).children('img').attr('src', newSrc); 
    }, function() {
        $(this).children('img').attr('src', curSrc);   
    });

});
