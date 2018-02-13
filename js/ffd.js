(function($) {

	$(document).ready(function(){

		// transform price input field in select box
		$('input#price').parent('p').append('<select id="price" name="price"><option></option></select>');
		$('input#price').remove();
		for (var p = 2; p < 51; p++) {
			if ( p == 2 ) { price = ffdBasePrice * 2; }
			else { price = ffdReducedPrice * p; }
			$('select#price').append('<option data-pilots="'+p+'" value="'+price+'">'+p+' pilotes: '+price+'€</option>');
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

		// adds maxlength to SIRET field
		$('input#_user_siret').attr('maxlength', '9');

		// changes type of phone input field to number
		$('input#_user_phone').attr('type', 'number');

		// changes type of email input field to email
		$('input#_user_legal_contact_mail').attr('type', 'email');

		// sets validation for the correct number of pilots fields
		// function of #price select field selected
		// when submit button is pressed
		if($('select#price').length != 0) { // if price select exists
			var fs = [
				{id: 'photo',req:true},
				{id: 'lastname',req: true},
				{id: 'firstname',req:true},
				{id: 'phone',req: false},
				{id: 'mail', req: false},
				{id: 'ed', req: true},
				{id: 'theory',req: true}
			];
			for ( var i = 1; i <= 50; i++ ) {
				$('#_user_pilot_'+i+'_div').hide();
				for (f of fs) {
					if ( f.req )
						$('#_user_pilot_'+i+'_'+f.id).removeAttr('required');
					$('#_user_pilot_'+i+'_'+f.id).attr('disabled','disabled');
					$('#_user_pilot_'+i+'_'+f.id+'_div').hide();
				}
			}
			$('select#price').change(function(){
				v = $(this).val();
				d = $('option[value="'+v+'"]').attr('data-pilots');
				for ( var i = 1; i <= 50; i++ ) {
					$('#_user_pilot_'+i+'_div').hide();
					for (f of fs) {
						if ( f.req )
							$('#_user_pilot_'+i+'_'+f.id).removeAttr('required');
						$('#_user_pilot_'+i+'_'+f.id).attr('disabled','disabled');
						$('#_user_pilot_'+i+'_'+f.id+'_div').hide();
					}
				}
				for ( var i = 1; i <= d; i++ ) {
					$('#_user_pilot_'+i+'_div').show();
					for (f of fs) {
						if ( f.req )
							$('#_user_pilot_'+i+'_'+f.id).attr('required','required');
						$('#_user_pilot_'+i+'_'+f.id).removeAttr('disabled');
						$('#_user_pilot_'+i+'_'+f.id+'_div').show();
					}
				}
			});
		}

	});

})(jQuery);
