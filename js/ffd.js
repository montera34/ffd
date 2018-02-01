(function($) {

	$(document).ready(function(){

		// transform price input field in select box
		$('input#price').parent('p').append('<select id="price" name="price"><option></option></select>');
		$('input#price').remove();
		for (var p = 2; p < 51; p++) {
			if ( p == 2 ) { price = ffdBasePrice * 2; }
			else { price = ffdReducedPrice * p; }
			$('select#price').append('<option value="'+price+'">'+p+' pilotes: '+price+'€</option>');
		}

		// automatic fill in fields
		$('input[name="_user_pilot_equal_contact"]').change(function() {
		        if($(this).is(":checked")) {
				lastname = $('input[name="_user_legal_contact_lastname"]').val();
				firstname = $('input[name="_user_legal_contact_firstname"]').val();
				$('#_user_pilot_1_lastname').val(lastname);
				$('#_user_pilot_1_firstname').val(firstname);
			}
		})
	});

})(jQuery);
