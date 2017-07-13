window.addEvent('domready', function(){
	var managerSlide = new Fx.Slide('addPwdForm').hide();
	$('togglePwdForm').addEvent('click', function () { managerSlide.toggle(); });
});