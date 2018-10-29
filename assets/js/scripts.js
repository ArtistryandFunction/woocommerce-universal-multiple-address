(function(window, $, wc_address_book_init) {

	$(document).ready( function() {

		//start debugging stuff
		var newTime = new Date();
		//end debugging stuff



		/*
		 * State/Country select boxes... same code as default woocommerce country-select js with added ids for custom form
		*/
		var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
			states = $.parseJSON( states_json );

		$( document.body ).on( 'change', 'select.country_to_state, input.country_to_state', function() {
			// Grab wrapping element to target only stateboxes in same 'group'
			var $wrapper    = $( this ).closest('.woocommerce-billing-fields, .woocommerce-shipping-fields, .woocommerce-shipping-calculator');

			if ( ! $wrapper.length ) {
				$wrapper = $( this ).closest('.form-row').parent();
			}

			var country     = $( this ).val(),
				$statebox   = $wrapper.find( '#billing_state, #shipping_state, #calc_shipping_state, #state' ),//added default #state id
				$parent     = $statebox.parent(),
				input_name  = $statebox.attr( 'name' ),
				input_id    = $statebox.attr( 'id' ),
				value       = $statebox.val(),
				placeholder = $statebox.attr( 'placeholder' ) || $statebox.attr( 'data-placeholder' ) || '';

			if ( states[ country ] ) {
				if ( $.isEmptyObject( states[ country ] ) ) {

					$statebox.parent().hide().find( '.select2-container' ).remove();
					$statebox.replaceWith( '<input type="hidden" class="hidden" name="' + input_name + '" id="' + input_id + '" value="" placeholder="' + placeholder + '" />' );

					$( document.body ).trigger( 'country_to_state_changed', [ country, $wrapper ] );

				} else {

					var options = '',
						state = states[ country ];

					for( var index in state ) {
						if ( state.hasOwnProperty( index ) ) {
							options = options + '<option value="' + index + '">' + state[ index ] + '</option>';
						}
					}

					$statebox.parent().show();

					if ( $statebox.is( 'input' ) ) {
						// Change for select
						$statebox.replaceWith( '<select name="' + input_name + '" id="' + input_id + '" class="state_select" data-placeholder="' + placeholder + '"></select>' );
						$statebox = $wrapper.find( '#billing_state, #shipping_state, #calc_shipping_state, #state' );//added default #state id
					}

					$statebox.html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
					$statebox.val( value ).change();

					$( document.body ).trigger( 'country_to_state_changed', [country, $wrapper ] );

				}
			} else {
				if ( $statebox.is( 'select' ) ) {

					$parent.show().find( '.select2-container' ).remove();
					$statebox.replaceWith( '<input type="text" class="input-text" name="' + input_name + '" id="' + input_id + '" placeholder="' + placeholder + '" />' );

					$( document.body ).trigger( 'country_to_state_changed', [country, $wrapper ] );

				} else if ( $statebox.is( 'input[type="hidden"]' ) ) {

					$parent.show().find( '.select2-container' ).remove();
					$statebox.replaceWith( '<input type="text" class="input-text" name="' + input_name + '" id="' + input_id + '" placeholder="' + placeholder + '" />' );

					$( document.body ).trigger( 'country_to_state_changed', [country, $wrapper ] );

				}
			}

			$( document.body ).trigger( 'country_to_state_changing', [country, $wrapper ] );

		});

		$(function() {
			$( ':input.country_to_state' ).change();
		});



		/*
		 * Add Address dropdown... Save address AJAX POST and close dropdown
		 */
		$(".add-address").click(function(){ //Click add address button to reveal form
			console.log(newTime); //debugging
			$(".new-address-form").slideDown("slow");
			$(".add-address").fadeOut();
		});

		$(".save-address").click(function(){ //click save...
			var valid = validate_new_address_fields();
			if( valid == false ){//...if it's valid...
				return;
			} else {
				var $inputs = $('.new-address-form :input');

	    	var address_fields_array = {};
	    	$inputs.each(function() { //...push field values into an array...
	        address_fields_array[this.name] = $(this).val();
	    	});
				var address_fields = JSON.stringify(address_fields_array);
				console.log(address_fields); //debugging
				$.ajax({ //...send address fields to server...
					url : wc_address_book.ajax_url,
					type : "POST",
					data : {
						action : "ajax_handler",
						address_fields : address_fields
					},
					error : function(data){
						console.log('error');
					},
					success : function(data){
						console.log('success');//@TODO add updating animation 
						$(".add-address").fadeIn();
						$(".new-address-form").slideUp("slow");
					}
				});
			}

		});



		/*
		 * Validating inputs
		 */
		function validate_new_address_fields(){
			var real_word = new RegExp(/^[a-zA-Z]+$/);
			var valid_first = real_word.test(WCUABAddressBookForm.first_name.value);
			var valid_last = real_word.test(WCUABAddressBookForm.last_name.value);
			var valid_city = real_word.test(WCUABAddressBookForm.city.value);
			var real_address = new RegExp(/^[a-zA-Z0-9\s,.'-]{3,}$/);
			var valid_address = real_address.test(WCUABAddressBookForm.address_1.value);
			var valid_name = real_address.test(WCUABAddressBookForm.address_name.value);
			var real_phone = new RegExp(/^((\+?\d{1,3}(-| |.|,|_)?\(?\d\)?(-| |.|,|_)?\d{1,5})|(\(?\d{2,6}\)?))(-| |.|,|_)?(\d{3,4})(-| |.|,|_)?(\d{4})(( x| ext)\d{1,5}){0,1}(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/);
			var valid_phone = real_phone.test(WCUABAddressBookForm.address_phone.value);
			var real_email = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
			var valid_email = real_email.test(WCUABAddressBookForm.address_email.value);
			var $selected_country = $("#country option:selected").text();
			var $selected_state = $("#state option:selected").text();
			//postalcodes by countries
			var real_us = new RegExp(/^\d{5}([ \-]\d{4})?$/);
			var valid_us = real_us.test(WCUABAddressBookForm.postcode.value);
			var real_mx = new RegExp(/^\d{5}$/);
			var valid_mx = real_mx.test(WCUABAddressBookForm.postcode.value);
			var real_uk = new RegExp(/^GIR[ ]?0AA|((AB|AL|B|BA|BB|BD|BH|BL|BN|BR|BS|BT|CA|CB|CF|CH|CM|CO|CR|CT|CV|CW|DA|DD|DE|DG|DH|DL|DN|DT|DY|E|EC|EH|EN|EX|FK|FY|G|GL|GY|GU|HA|HD|HG|HP|HR|HS|HU|HX|IG|IM|IP|IV|JE|KA|KT|KW|KY|L|LA|LD|LE|LL|LN|LS|LU|M|ME|MK|ML|N|NE|NG|NN|NP|NR|NW|OL|OX|PA|PE|PH|PL|PO|PR|RG|RH|RM|S|SA|SE|SG|SK|SL|SM|SN|SO|SP|SR|SS|ST|SW|SY|TA|TD|TF|TN|TQ|TR|TS|TW|UB|W|WA|WC|WD|WF|WN|WR|WS|WV|YO|ZE)(\d[\dA-Z]?[ ]?\d[ABD-HJLN-UW-Z]{2}))|BFPO[ ]?\d{1,4}$/);
			var valid_uk = real_uk.test(WCUABAddressBookForm.postcode.value);
			var real_ca = new RegExp(/^[ABCEGHJKLMNPRSTVXY]\d[ABCEGHJ-NPRSTV-Z][ ]?\d[ABCEGHJ-NPRSTV-Z]\d$/);
			var valid_ca = real_ca.test(WCUABAddressBookForm.postcode.value);
			var real_sg = new RegExp(/^\d{6}$/);//Singapore postcode regex
			var valid_sg = real_sg.test(WCUABAddressBookForm.postcode.value);
			var real_kr = new RegExp(/^\d{3}[\-]\d{3}$/);//South Korea postcode regex
			var valid_kr = real_kr.test(WCUABAddressBookForm.postcode.value);

			//Right now this shows the FIRST error in descending order ONLY... @TODO change this to switch statement that also checks to see if any li child nodes exist before hiding ul
			//@TODO put this validation inside a prototype function and extend... ESPECIALLY the switch case validation
			if( (valid_first == false) || (WCUABAddressBookForm.first_name.value == "" || null) ) {
				$("hr").attr("tabindex", "0").focus().css("outline", "none");
				$(".woocommerce-error, .woocommerce-error li:contains('first name')").slideDown();
				$("#first_name").val("");
				$("#first_name").mousedown(function(){
					$(".woocommerce-error, .woocommerce-error li:contains('first name')").slideUp("slow");
				});
				return false;
			}
			if( (valid_last == false) || (WCUABAddressBookForm.last_name.value == "" || null) ) {
				$("hr").attr("tabindex", "0").focus().css("outline", "none");
				$(".woocommerce-error, .woocommerce-error li:contains('last name')").slideDown();
				$("#last_name").val("");
				$("#last_name").mousedown(function(){
					$(".woocommerce-error, .woocommerce-error li:contains('last name')").slideUp("slow");
				});
				return false;
			}
			if( $selected_country == "Select a country..." ) {
				$("hr").attr("tabindex", "0").focus().css("outline", "none");
				$(".woocommerce-error, .woocommerce-error li:contains('country')").slideDown();
				$("#country").mousedown(function(){
					$(".woocommerce-error, .woocommerce-error li:contains('country')").slideUp("slow");
				});
				return false;
			}
			if( valid_address == false || (WCUABAddressBookForm.address_1.value == "" || null) ) {
				$("hr").attr("tabindex", "0").focus().css("outline", "none");
				$(".woocommerce-error, .woocommerce-error li:contains('address')").slideDown();
				$("#address_1").val("");
				$("#address_1").mousedown(function(){
					$(".woocommerce-error, .woocommerce-error li:contains('address')").slideUp("slow");
				});
				return false;
			}
			if( valid_city == false || (WCUABAddressBookForm.city.value == "" || null) ) {
				$("hr").attr("tabindex", "0").focus().css("outline", "none");
				$(".woocommerce-error, .woocommerce-error li:contains('city')").slideDown();
				$("#city").val("");
				$("#city").mousedown(function(){
					$(".woocommerce-error, .woocommerce-error li:contains('city')").slideUp("slow");
				});
				return false
			}
			if( $selected_state == "" ) {
				$("hr").attr("tabindex", "0").focus().css("outline", "none");
				$(".woocommerce-error, .woocommerce-error li:contains('state')").slideDown();
				$("#state").mousedown(function(){
					$(".woocommerce-error, .woocommerce-error li:contains('state')").slideUp("slow");
				});
				return false;
			}
			switch($selected_country) {
				case "Canada": {
					if( valid_ca == false || (WCUABAddressBookForm.postcode.value == "" || null) ) {
						$("hr").attr("tabindex", "0").focus().css("outline", "none");
						$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideDown();
						$("#postcode").val("");
						$("#postcode").mousedown(function(){
							$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideUp("slow");
						});
						return false;
					}
					break;
				}
				case "Mexico": {
					if( valid_mx == false || (WCUABAddressBookForm.postcode.value == ""|| null) ) {
						$("hr").attr("tabindex", "0").focus().css("outline", "none");
						$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideDown();
						$("#postcode").val("");
						$("#postcode").mousedown(function(){
							$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideUp("slow");
						});
						return false;
					}
					break;
				}
				case "Singapore": {
					if( valid_sg == false || (WCUABAddressBookForm.postcode.value == "" || null) ) {
						$("hr").attr("tabindex", "0").focus().css("outline", "none");
						$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideDown();
						$("#postcode").val("");
						$("#postcode").mousedown(function(){
							$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideUp("slow");
						});
						return false;
					}
					break;
				}
				case "South Korea": {
					if( valid_kr == false || (WCUABAddressBookForm.postcode.value == "" || null) ) {
						$("hr").attr("tabindex", "0").focus().css("outline", "none");
						$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideDown();
						$("#postcode").val("");
						$("#postcode").mousedown(function(){
							$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideUp("slow");
						});
						return false;
					}
					break;
				}
				case "United Kingdom (UK)": {
					if( valid_uk == false || (WCUABAddressBookForm.postcode.value == "" || null) ) {
						$("hr").attr("tabindex", "0").focus().css("outline", "none");
						$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideDown();
						$("#postcode").val("");
						$("#postcode").mousedown(function(){
							$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideUp("slow");
						});
						return false;
					}
					break;
				}
				case "United States (US)": {
					if( valid_us == false || (WCUABAddressBookForm.postcode.value == "" || null) ) {
						$("hr").attr("tabindex", "0").focus().css("outline", "none");
						$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideDown();
						$("#postcode").val("");
						$("#postcode").mousedown(function(){
							$(".woocommerce-error, .woocommerce-error li:contains('postcode')").slideUp("slow");
						});
						return false;
					}
					break;
				}
			}
			if( valid_phone == false || (WCUABAddressBookForm.address_phone.value == "" || null) ) {
				$("hr").attr("tabindex", "0").focus().css("outline", "none");
				$(".woocommerce-error, .woocommerce-error li:contains('phone')").slideDown();
				$("#address_phone").val("");
				$("#address_phone").mousedown(function(){
					$(".woocommerce-error, .woocommerce-error li:contains('phone')").slideUp("slow");
				});
				return false;
			}
			if( valid_email == false || (WCUABAddressBookForm.address_email.value == "" || null) ) {
				$("hr").attr("tabindex", "0").focus().css("outline", "none");
				$(".woocommerce-error, .woocommerce-error li:contains('email')").slideDown();
				$("#address_email").val("");
				$("#address_email").mousedown(function(){
					$(".woocommerce-error, .woocommerce-error li:contains('email')").slideUp("slow");
				});
				return false;
			}
			if( valid_name == false || (WCUABAddressBookForm.address_name.value == "" || null) ) {
				$("hr").attr("tabindex", "0").focus().css("outline", "none");
				$(".woocommerce-error, .woocommerce-error li:contains('nickname')").slideDown();
				$("#address_name").val("");
				$("#address_name").mousedown(function(){
					$(".woocommerce-error, .woocommerce-error li:contains('nickname')").slideUp("slow");
				});
				return false;
			}
			return(true);
		}

		/*
		 * AJAX call to delete address books.
		 */
		$('.address_book .wc-uab-address-book-delete').click( function( e ) {

			e.preventDefault();

			$(this).closest( '.wc-uab-address-book-address' ).addClass('blockUI blockOverlay wc-updating');

			var name = $(this).attr('id');

			$.ajax({
				url : wc_address_book.ajax_url,
				type : 'post',
				data : {
					action : 'wc_address_book_delete',
					name : name
				},
				success : function( response ) {
					$('.wc-updating').remove();
				}
			});
		});

		/*
		 * AJAX call to switch address to primary.
		 */
		$('.address_book .wc-uab-address-book-make-primary-shipping').click( function( e ) {

			e.preventDefault();

			var name = $(this).attr('id');
			var primary_address = $('.woocommerce-Addresses .u-column2.woocommerce-Address address');
			var alt_address = $(this).parent().siblings( 'address' );

			// Swap HTML values for address and label
			var pa_html = primary_address.html();
			var aa_html = alt_address.html();

			alt_address.html(pa_html);
			primary_address.html(aa_html);

			primary_address.addClass('blockUI blockOverlay wc-updating');
			alt_address.addClass('blockUI blockOverlay wc-updating');

			$.ajax({
				url : wc_address_book.ajax_url,
				type : 'post',
				data : {
					action : 'wc_address_book_make_primary',
					name : name
				},
				success : function( response ) {
					$('.wc-updating').removeClass('blockUI blockOverlay wc-updating');
				}
			});
		});

		/*
		 * AJAX call display address on checkout when selected.
		 */
		function shipping_checkout_field_prepop() {

			var that = $('#address_book_field #address_book');
			var name = $(that).val();

			if ( name !== undefined ) {

				if ( 'add_new' == name ) {

					// Clear values when adding a new address.
					$('.shipping_address input').not($('#shipping_country')).each( function() {
						$(this).val('');
					});

					// Set Country Dropdown.
					// Don't reset the value if only one country is available to choose.
					if ( typeof $('#shipping_country').attr('readonly') == 'undefined' ) {
						$('#shipping_country').val('').change();
						$("#shipping_country_chosen").find('span').html('');
					}

					// Set state dropdown.
					$('#shipping_state').val('').change();
					$("#shipping_state_chosen").find('span').html('');

					return;
				}

				if ( name.length > 0 ) {

					$(that).closest( '.shipping_address' ).addClass( 'blockUI blockOverlay wc-updating' );

					$.ajax({
						url : wc_address_book.ajax_url,
						type : 'post',
						data : {
							action : 'wc_address_book_checkout_update',
							name : name
						},
						dataType: 'json',
						success : function( response ) {

							// Loop through all fields incase there are custom ones.
							Object.keys(response).forEach( function(key) {
								$('#' + key).val(response[key]).change();
							});

							// Set Country Dropdown.
							$('#shipping_country').val(response.shipping_country).change();
							$("#shipping_country_chosen").find('span').html(response.shipping_country_text);

							// Set state dropdown.
							$('#shipping_state').val(response.shipping_state);
							var stateName = $('#shipping_state option[value="'+response.shipping_state+'"]').text();
							$("#s2id_shipping_state").find('.select2-chosen').html(stateName).parent().removeClass('select2-default');

							// Remove loading screen.
							$( '.shipping_address' ).removeClass('blockUI blockOverlay wc-updating');

						}
					});

				}
			}
		}

		shipping_checkout_field_prepop();

		$('#address_book_field #address_book').change( function() {
			shipping_checkout_field_prepop();
		});
	});

})(window, jQuery);
