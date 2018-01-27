(function($) {

	var selectOut = '<p><select id="price" name="price"><option></option><option value="120">2 pilotes: 120€</option><option value="165">3 pilotes: 165€</option></select></p>'
	$(document).ready(function(){
// transform price input field in select box
		$('input#price').parent('p').append(selectOut);
		$('input#price').remove();
	});

})(jQuery);
