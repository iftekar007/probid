
$(function(){
		$('#wpcf7-f237-p23-o1').find('.wpcf7-text').focus(function(){
			$(this).removeClass('wpcf7-not-valid');
		});
		
		
		$('.gotoenroll').click(function(){
			var formid = $('.wpcf7').attr('id');
			$('html,body').animate({scrollTop: $("#"+formid).offset().top},'normal');
		});
		
		
});


function showmodal1(){
	$('#thankyou_popup').modal('show');
	
	setTimeout(function(){
		$('#thankyou_popup').modal('hide');
	},8000);
}
function showmodal2(){
	$('#thankyou_popup1').modal('show');
	
	setTimeout(function(){
		$('#thankyou_popup1').modal('hide');
	},8000);
}