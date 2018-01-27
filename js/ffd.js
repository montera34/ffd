(function($) {

	var selectOut = '<select id="price" name="price"><option></option></select>'
	$(document).ready(function(){
// transform price input field in select box
		$('input#price').parent('p').append(selectOut);
		$('input#price').remove();
		for (var p = 2; p < 51; p++) {
			if ( p == 2 ) { price = ffdBasePrice * 2; }
			else { price = ffdReducedPrice * p; }
			$('select#price').append('<option value="'+price+'">'+p+' pilotes: '+price+'€</option>');
		}
	});

})(jQuery);
