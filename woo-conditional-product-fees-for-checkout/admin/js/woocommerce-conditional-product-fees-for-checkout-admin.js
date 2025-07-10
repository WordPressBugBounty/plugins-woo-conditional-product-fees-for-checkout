(function( $ ) {
	'use strict';
	$( '.multiselect2' ).select2();

	function allowSpeicalCharacter( str ) {
		return str.replace( '&#8211;', '–' ).replace( '&gt;', '>' ).replace( '&lt;', '<' ).replace( '&#197;', 'Å' );
	}

	function productFilter() {
		$( '.product_fees_conditions_values_product' ).each( function() {
			$( '.product_fees_conditions_values_product' ).select2( {
				placeholder: coditional_vars.select_product,
				ajax: {
					url: coditional_vars.ajaxurl,
					dataType: 'json',
					delay: 250,
					cache: true,
                    minimumInputLength: 3,
					data: function( params ) {
						return {
							value: params.term,
							action: 'wcpfc_pro_product_fees_conditions_values_product_ajax',
							security: coditional_vars.select2_ajax_nonce,
                            _page: params.page || 1,
                            posts_per_page: 10 
						};
					},
					processResults: function( data ) {
						var options = [], more = true;
						if ( data ) {
							$.each( data, function( index, text ) {
								options.push( { id: text[ 0 ], text: allowSpeicalCharacter( text[ 1 ] ) } );
							} );
						}
                        //for stop paination on all data laod 
                        if( 0 === options.length ){ 
                            more = false; 
                        }
						return {
							results: options,
                            pagination: {
                                more: more
                            }
						};
					},
				},
			} );
		} );
	}

	function varproductFilter() {
		$( '.product_fees_conditions_values_var_product' ).each( function() {
			$( '.product_fees_conditions_values_var_product' ).select2( {
				placeholder: coditional_vars.select_product,
				ajax: {
					url: coditional_vars.ajaxurl,
					dataType: 'json',
					delay: 250,
                    cache: true,
                    minimumInputLength: 3,
					data: function( params ) {
						return {
							value: params.term,
							action: 'wcpfc_pro_product_fees_conditions_varible_values_product_ajax',
							security: coditional_vars.select2_ajax_nonce,
                            _page: params.page || 1,
                            posts_per_page: 10 
						};
					},
					processResults: function( data ) {
						var options = [], more = true;
						if ( data ) {
							$.each( data, function( index, text ) {
								options.push( { id: text[ 0 ], text: allowSpeicalCharacter( text[ 1 ] ) } );
							} );
						}
                        //for stop paination on all data laod 
                        if( 0 === options.length ){ 
                            more = false; 
                        }
						return {
							results: options,
                            pagination: {
                                more: more
                            }
						};
					},
				}
			} );
		} );
	}

	function getProductListBasedOnThreeCharAfterUpdate() {
		$( '.fees_pricing_rules .ap_product, ' +
			'.fees_pricing_rules .ap_product_weight, ' +
			'.fees_pricing_rules .ap_product_subtotal' ).each( function() {
			$( '.fees_pricing_rules .ap_product, ' +
				'.fees_pricing_rules .ap_product_weight, ' +
				'.fees_pricing_rules .ap_product_subtotal' ).select2( {
				ajax: {
					url: coditional_vars.ajaxurl,
					dataType: 'json',
					delay: 250,
					data: function( params ) {
						return {
							value: params.term,
							action: 'wcpfc_pro_simple_and_variation_product_list_ajax',
							security: coditional_vars.select2_ajax_nonce
						};
					},
					processResults: function( data ) {
						var options = [];
						if ( data ) {
							$.each( data, function( index, text ) {
								options.push( { id: text[ 0 ], text: allowSpeicalCharacter( text[ 1 ] ) } );
							} );

						}
						return {
							results: options
						};
					},
					cache: true
				},
				minimumInputLength: 3,
				placeholder: coditional_vars.select_product,
			} );
		} );
	}

	function userFilter() {
		$( '.product_fees_conditions_values_user' ).each( function() {
			$( '.product_fees_conditions_values_user' ).select2( {
				placeholder: coditional_vars.select_user,
				ajax: {
					url: coditional_vars.ajaxurl,
					dataType: 'json',
					delay: 250,
					cache: true,
                    minimumInputLength: 3,
					data: function( params ) {
						return {
							value: params.term,
							action: 'wcpfc_pro_product_fees_conditions_values_user_ajax',
							security: coditional_vars.select2_ajax_nonce,
                            _page: params.page || 1,
                            posts_per_page: 10 
						};
					},
					processResults: function( data ) {
						var options = [], more = true;
						if ( data ) {
							$.each( data, function( index, text ) {
								options.push( { id: text[ 0 ], text: allowSpeicalCharacter( text[ 1 ] ) } );
							} );
						}
                        //for stop paination on all data laod 
                        if( 0 === options.length ){ 
                            more = false; 
                        }
						return {
							results: options,
                            pagination: {
                                more: more
                            }
						};
					},
				},
			} );
		} );
	}

	// Set cookies
	function setCookie(name, value, minutes) {
		var expires = '';
		if (minutes) {
			var date = new Date();
			date.setTime(date.getTime() + (minutes * 60 * 1000));
			expires = '; expires=' + date.toUTCString();
		}
		document.cookie = name + '=' + (value || '') + expires + '; path=/';
	}

	// Get cookies
    function getCookie(name) {
        let nameEQ = name + '=';
        let ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i].trim();
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
            }
        }
        return null;
    }

	/** Script for Freemius upgrade popup */
    function upgradeToProFreemius( couponCode ) {
        let handler;
        handler = FS.Checkout.configure({
            plugin_id: '3390',
            plan_id: '5474',
            public_key:'pk_9edf804dccd14eabfd00ff503acaf',
            image: 'https://www.thedotstore.com/wp-content/uploads/sites/1417/2023/09/WooCommerce-Extra-Fees-Banner-New.png',
            coupon: couponCode,
        });
        handler.open({
            name: 'WooCommerce Extra Fees Plugin',
            subtitle: 'You’re a step closer to our Pro features',
            licenses: jQuery('input[name="licence"]:checked').val(),
            purchaseCompleted: function( response ) {
                console.log (response);
            },
            success: function (response) {
                console.log (response);
            }
        });
    }

	// show loader after 2 seconds
	var loaderTimeout;
	loaderTimeout = setTimeout(function() {
	    $('.dotstore_plugin_page_loader').fadeIn(500);
  	}, 2000);
	
	$( window ).on( 'load', function() {
		$( '.multiselect2' ).select2();
		$( '.product_fees_conditions_values_country' ).select2({
			placeholder: coditional_vars.select_country
		});

		$( 'a[href="admin.php?page=wcpfc-pro-list"]' ).parents().addClass( 'current wp-has-current-submenu' );
		$( 'a[href="admin.php?page=wcpfc-pro-list"]' ).addClass( 'current' );

		$( '#fee_settings_start_date' ).datepicker( {
			dateFormat: 'dd-mm-yy',
			minDate: '0',
			onSelect: function() {
				var dt = $( this ).datepicker( 'getDate' );
				dt.setDate( dt.getDate() + 1 );
				$( '#fee_settings_end_date' ).datepicker( 'option', 'minDate', dt );
			}
		} );
		$( '#fee_settings_end_date' ).datepicker( {
			dateFormat: 'dd-mm-yy',
			minDate: '0',
			onSelect: function() {
				var dt = $( this ).datepicker( 'getDate' );
				dt.setDate( dt.getDate() - 1 );
				$( '#fee_settings_start_date' ).datepicker( 'option', 'maxDate', dt );
			}
		} );
		var ele = $( '#total_row' ).val();
		var count;
		if ( ele > 2 ) {
			count = ele;
		} else {
			count = 2;
		}
		$( 'body' ).on( 'click', '#fee-add-field', function() {
			var fee_add_field = $( '#tbl-product-fee tbody' ).get( 0 );

			var tr = document.createElement( 'tr' );
			tr = setAllAttributes( tr, { 'id': 'row_' + count } );
			fee_add_field.appendChild( tr );

			// generate td of condition
			var td = document.createElement( 'td' );
			td = setAllAttributes( td, { 'class': 'titledesc th_product_fees_conditions_condition' } );
			tr.appendChild( td );
			var conditions = document.createElement( 'select' );
			conditions = setAllAttributes( conditions, {
				'rel-id': count,
				'id': 'product_fees_conditions_condition_' + count,
				'name': 'fees[product_fees_conditions_condition][]',
				'class': 'product_fees_conditions_condition'
			} );
			conditions = insertOptions( conditions, get_all_condition() );
			td.appendChild( conditions );
			// td ends

			// generate td for equal or no equal to
			td = document.createElement( 'td' );
			td = setAllAttributes( td, { 'class': 'select_condition_for_in_notin' } );
			tr.appendChild( td );
			var conditions_is = document.createElement( 'select' );
			conditions_is = setAllAttributes( conditions_is, {
				'name': 'fees[product_fees_conditions_is][]',
				'class': 'product_fees_conditions_is product_fees_conditions_is_' + count
			} );
			conditions_is = insertOptions( conditions_is, condition_types( false ) );
			td.appendChild( conditions_is );
			// td ends

			// td for condition values
			td = document.createElement( 'td' );
			td = setAllAttributes( td, { 'id': 'column_' + count, 'class': 'condition-value', 'colspan': '2' } );
			tr.appendChild( td );
			condition_values( $( '#product_fees_conditions_condition_' + count ) );

			var condition_key = document.createElement( 'input' );
			condition_key = setAllAttributes( condition_key, {
				'type': 'hidden',
				'name': 'condition_key[value_' + count + '][]',
				'value': '',
			} );
			td.appendChild( condition_key );
			$( '.product_fees_conditions_values_' + count ).trigger( 'change' );
			// td ends

			// td for delete button
			td = document.createElement( 'td' );
			tr.appendChild( td );
			var delete_button = document.createElement( 'a' );
			delete_button = setAllAttributes( delete_button, {
				'id': 'fee-delete-field',
				'rel-id': count,
				'title': coditional_vars.delete,
				'class': 'delete-row',
				'href': 'javascript:;'
			} );
			var deleteicon = document.createElement( 'i' );
			deleteicon = setAllAttributes( deleteicon, {
				'class': 'fa fa-trash'
			} );
			delete_button.appendChild( deleteicon );
			td.appendChild( delete_button );
			// td ends

			count ++;

			// Enable/disable first row delete button
			let allDeleteRow = $('#tbl-product-fee .delete-row');
			if ( allDeleteRow.length === 1 ) {
		    	allDeleteRow.addClass('disable-delete-icon');
			} else {
				allDeleteRow.removeClass('disable-delete-icon');
			}
		} );

		function insertOptions( parentElement, options ) {
			var option;
			for ( var i = 0; i < options.length; i ++ ) {
				if ( options[ i ].type === 'optgroup' ) {
					var optgroup = document.createElement( 'optgroup' );
					optgroup = setAllAttributes( optgroup, options[ i ].attributes );
					for ( var j = 0; j < options[ i ].options.length; j ++ ) {
						option = document.createElement( 'option' );
						option = setAllAttributes( option, options[ i ].options[ j ].attributes );
						option.textContent = options[ i ].options[ j ].name;
						optgroup.appendChild( option );
					}
					parentElement.appendChild( optgroup );
				} else {
					option = document.createElement( 'option' );
					option = setAllAttributes( option, options[ i ].attributes );
					option.textContent = allowSpeicalCharacter( options[ i ].name );
					parentElement.appendChild( option );
				}

			}
			return parentElement;

		}

		function get_all_condition() {
			return [
				{
					'type': 'optgroup',
					'attributes': { 'label': coditional_vars.location_specific },
					'options': [
						{ 'name': coditional_vars.country, 'attributes': { 'value': 'country' } },
						{ 'name': coditional_vars.city, 'attributes': { 'value': 'city' } },
						{ 'name': coditional_vars.state_disabled, 'attributes': {'value': 'state_disabled'} },
						{ 'name': coditional_vars.postcode_disabled, 'attributes': {'value': 'postcode_disabled'} },
						{ 'name': coditional_vars.zone_disabled, 'attributes': {'value': 'zone_disabled'} },
					]
				},
				{
					'type': 'optgroup',
					'attributes': { 'label': coditional_vars.product_specific },
					'options': [
						{ 'name': coditional_vars.cart_contains_product, 'attributes': { 'value': 'product' } },
						{ 'name': coditional_vars.cart_contains_variable_product, 'attributes': { 'value': 'variableproduct' } },
						{ 'name': coditional_vars.cart_category_product_disabled, 'attributes': { 'value': 'category_disabled' } },
						{ 'name': coditional_vars.cart_contains_tag_product, 'attributes': { 'value': 'tag' } },
						{ 'name': coditional_vars.cart_contains_product_qty, 'attributes': { 'value': 'product_qty' } },
					]
				},
				{
					'type': 'optgroup',
					'attributes': { 'label': coditional_vars.attribute_specific },
					'options': [
						{ 'name': coditional_vars.attribute_list_disabled, 'attributes': {'value': 'attribute_list_disabled'} },
					]
				},
				{
					'type': 'optgroup',
					'attributes': { 'label': coditional_vars.user_specific },
					'options': [
						{ 'name': coditional_vars.user, 'attributes': { 'value': 'user' } },
						{ 'name': coditional_vars.user_role_disabled, 'attributes': {'value': 'user_role_disabled'} },
					]
				},
                {
                    'type': 'optgroup',
                    'attributes' : {'label' : coditional_vars.purchase_history},
                    'options': [
                        {'name' : coditional_vars.last_spent_order, 'attributes': {'value' : 'last_spent_order'}},
                        {'name' : coditional_vars.total_spent_order, 'attributes': {'value' : 'total_spent_order'}},
                        {'name' : coditional_vars.spent_order_count, 'attributes': {'value' : 'spent_order_count'}},
                    ]
                },
				{
					'type': 'optgroup',
					'attributes': { 'label': coditional_vars.cart_specific },
					'options': [
						{ 'name': coditional_vars.cart_subtotal_before_discount, 'attributes': { 'value': 'cart_total' } },
						{ 'name': coditional_vars.cart_subtotal_after_discount_disabled, 'attributes': {'value': 'cart_totalafter_disabled'} },
						{ 'name': coditional_vars.cart_subtotal_specific_products_disabled, 'attributes': {'value': 'cart_specificproduct_disabled'} },
						{ 'name': coditional_vars.quantity, 'attributes': { 'value': 'quantity' } },
						{ 'name': coditional_vars.weight_disabled, 'attributes': {'value': 'weight_disabled'} },
						{ 'name': coditional_vars.coupon_disabled, 'attributes': {'value': 'coupon_disabled'} },
						{ 'name': coditional_vars.shipping_class_disabled, 'attributes': {'value': 'shipping_class_disabled'} }
					]
				},
				{
					'type': 'optgroup',
					'attributes': { 'label': coditional_vars.payment_specific },
					'options': [
						{ 'name': coditional_vars.payment_gateway_disabled, 'attributes': {'value': 'payment_gateway_disabled'} },
					]
				},
				{
					'type': 'optgroup',
					'attributes': { 'label': coditional_vars.shipping_specific },
					'options': [
						{ 'name': coditional_vars.shipping_method_disabled, 'attributes': {'value': 'shipping_method_disabled'} },
					]
				}

			];
		}

		$( '#fee_settings_select_fee_type' ).change( function() {
			if ( $( this ).val() === 'fixed' ) {
				$( '#fee_settings_product_cost' ).attr('type', 'text');
				$( '#fee_settings_product_cost' ).attr( 'placeholder', coditional_vars.currency_symbol );
				$( '.fees_on_cart_total_wrap' ).hide();
			} else if ( $( this ).val() === 'percentage' ) {
				$( '#fee_settings_product_cost' ).attr('type', 'number');
				$( '#fee_settings_product_cost' ).attr( 'placeholder', '%' );
				$( '#fee_settings_product_cost' ).attr( 'step', '0.01' );
				$( '.fees_on_cart_total_wrap' ).show();
			} else if( $( this ).val() === 'both' ){
				$( '#fee_settings_product_cost' ).attr('type', 'text');
				$( '#fee_settings_product_cost' ).attr( 'placeholder', '% + $' );
				$( '.fees_on_cart_total_wrap' ).show();
			}
			$( '#fee_settings_product_cost' ).val('');
		} );
		if( $( '#fee_settings_select_fee_type' ).val() === 'fixed' ){
			$( '.fees_on_cart_total_wrap' ).hide();
		} else if( $( '#fee_settings_select_fee_type' ).val() === 'percentage' ) {
			$( '.fees_on_cart_total_wrap' ).show();
		}

		$( 'body' ).on( 'change', '.product_fees_conditions_condition', function() {
			let selectedOption = $(this).find(':selected').val();
            if( selectedOption.includes('_disabled') ){
                $(this).find(':selected').prop('selected', false);

                $('body').addClass('wcpfc-modal-visible');
                condition_values(this);
            } else {
                condition_values(this);
            }
		} );

		function condition_values( element ) {
			var posts_per_page = 3; // Post per page
			var page = 0; // What page we are on.
			var condition = $( element ).val();
			var count = $( element ).attr( 'rel-id' );
			var column = $( '#column_' + count ).get( 0 );
			$( column ).empty();
			var loader = document.createElement( 'img' );
			loader = setAllAttributes( loader, { 'src': coditional_vars.plugin_url + 'images/ajax-loader.gif' } );
			column.appendChild( loader );
            
			$.ajax( {
				type: 'GET',
				url: coditional_vars.ajaxurl,
				data: {
					'action': 'wcpfc_pro_product_fees_conditions_values_ajax',
					'wcpfc_pro_product_fees_conditions_values_ajax': $( '#wcpfc_pro_product_fees_conditions_values_ajax' ).val(),
					'condition': condition,
					'count': count,
					'posts_per_page': posts_per_page,
					'offset': (page * posts_per_page),
				},
				contentType: 'application/json',
				success: function( response ) {
					page ++;
					var condition_values;
					$( '.product_fees_conditions_is_' + count ).empty();
					var column = $( '#column_' + count ).get( 0 );
					var condition_is = $( '.product_fees_conditions_is_' + count ).get( 0 );
					if ( condition === 'cart_total'
						|| condition === 'quantity'
						|| condition === 'product_qty'
					) {
						condition_is = insertOptions( condition_is, condition_types( true ) );
					} else {
						condition_is = insertOptions( condition_is, condition_types( false ) );
					}
					$( '.product_fees_conditions_is_' + count ).trigger( 'change' );
					$( column ).empty();

					var condition_values_id = '';
					var extra_class = '';
					if ( condition === 'product' ) {
						condition_values_id = 'product-filter-' + count;
						extra_class = 'product_fees_conditions_values_product';
					}
					if ( condition === 'variableproduct' ) {
						condition_values_id = 'var-product-filter-' + count;
						extra_class = 'product_fees_conditions_values_var_product';
					}
					if ( condition === 'product_qty' ) {
						condition_values_id = 'product-qry-filter-' + count;
						extra_class = 'product_fees_conditions_values_product_qty';
					}
					if ( condition === 'user' ) {
						condition_values_id = 'user-filter-' + count;
						extra_class = 'product_fees_conditions_values_user';
					}

					if ( isJson( response ) ) {
						condition_values = document.createElement( 'select' );
						condition_values = setAllAttributes( condition_values, {
							'name': 'fees[product_fees_conditions_values][value_' + count + '][]',
							'class': 'wcpfc_select product_fees_conditions_values product_fees_conditions_values_' + count + ' multiselect2 ' + extra_class + ' multiselect2_' + count + '_' + condition,
							'multiple': 'multiple',
							'id': condition_values_id
						} );
						column.appendChild( condition_values );
						var data = JSON.parse( response );
						condition_values = insertOptions( condition_values, data );
					} else {
						var input_extra_class;
						if ( condition === 'quantity' ) {
							input_extra_class = ' qty-class';
						}
						if ( condition === 'weight' ) {
							input_extra_class = ' weight-class';
						}
						if ( condition === 'cart_total' || condition === 'cart_totalafter' || condition === 'product_qty' || condition === 'cart_specificproduct' || condition === 'cart_totalexclude_tax' || condition === 'cart_rowtotal' ) {
							input_extra_class = ' price-class';
						}

						let fieldPlaceholder;
						if ( condition === 'city' ) {
							fieldPlaceholder = coditional_vars.select_city;
						} else if ( condition === 'product_qty' || condition === 'quantity' ) {
							fieldPlaceholder = coditional_vars.select_integer_number;
						} else {
							fieldPlaceholder = coditional_vars.select_float_number;
						}
						condition_values = document.createElement( $.trim( response ) );
						condition_values = setAllAttributes( condition_values, {
							'name': 'fees[product_fees_conditions_values][value_' + count + ']',
							'class': 'product_fees_conditions_values' + input_extra_class,
							'type': 'text',
							'placeholder': fieldPlaceholder

						} );
						column.appendChild( condition_values );
					}
					column = $( '#column_' + count ).get( 0 );
					var input_node = document.createElement( 'input' );
					input_node = setAllAttributes( input_node, {
						'type': 'hidden',
						'name': 'condition_key[value_' + count + '][]',
						'value': ''
					} );
					column.appendChild( input_node );
					var p_node, b_node, b_text_node, text_node;
					if ( condition === 'product'
						|| condition === 'variableproduct'
						|| condition === 'category'
						|| condition === 'weight'
						|| condition === 'cart_totalafter'
						|| condition === 'cart_specificproduct'
						|| condition === 'cart_totalexclude_tax'
						|| condition === 'cart_rowtotal'
						|| condition === 'product_qty'
					) {
						p_node = document.createElement( 'p' );
						b_node = document.createElement( 'b' );
						b_node = setAllAttributes( b_node, {
							'style': 'color: red;',
						} );
						b_text_node = document.createTextNode( coditional_vars.note );
						b_node.appendChild( b_text_node );
						
						var doc_url = coditional_vars.doc_url;
						if ( condition === 'product' || condition === 'variableproduct' ) {
							text_node = document.createTextNode( coditional_vars.cart_contains_product_msg );
							doc_url = coditional_vars.product_doc_url;
						}
						if ( condition === 'category' ) {
							text_node = document.createTextNode( coditional_vars.cart_contains_category_msg );
							doc_url = coditional_vars.category_doc_url;
						}
						if ( condition === 'product_qty' ) {
							text_node = document.createTextNode( coditional_vars.product_qty_msg );
							doc_url = coditional_vars.product_qty_doc_url;
						}
						var a_node = document.createElement( 'a' );
						a_node = setAllAttributes( a_node, {
							'href': doc_url,
							'target': '_blank'
						} );

						var a_text_node = document.createTextNode( coditional_vars.click_here );
						a_node.appendChild( a_text_node );
						p_node.appendChild( b_node );
						p_node.appendChild( text_node );
						p_node.appendChild( a_node );
						column.appendChild( p_node );
					}
					if( condition === 'city' || condition === 'postcode' ){
						p_node = document.createElement( 'p' );
						b_node = document.createElement( 'b' );
						b_node = setAllAttributes( b_node, {
							'style': 'color: red;',
						} );
						b_text_node = document.createTextNode( coditional_vars.note );
						b_node.appendChild( b_text_node );

						if ( condition === 'city' ) {
							text_node = document.createTextNode( coditional_vars.city_msg );
						}
						if ( condition === 'postcode' ) {
							text_node = document.createTextNode( coditional_vars.postcode_msg );
						}
						p_node.appendChild( b_node );
						p_node.appendChild( text_node );
						column.appendChild( p_node );
					}

					let selectCoundition = coditional_vars['select_' + condition];
					$( '.multiselect2_' + count + '_' + condition ).select2({
						placeholder: selectCoundition
					});

					productFilter();
					varproductFilter();
					userFilter();
					getProductListBasedOnThreeCharAfterUpdate();
				}
			} );
		}

		function condition_types( text ) {
			if ( text === true ) {
				return [
					{ 'name': coditional_vars.equal_to, 'attributes': { 'value': 'is_equal_to' } },
					{ 'name': coditional_vars.less_or_equal_to, 'attributes': { 'value': 'less_equal_to' } },
					{ 'name': coditional_vars.less_than, 'attributes': { 'value': 'less_then' } },
					{ 'name': coditional_vars.greater_or_equal_to, 'attributes': { 'value': 'greater_equal_to' } },
					{ 'name': coditional_vars.greater_than, 'attributes': { 'value': 'greater_then' } },
					{ 'name': coditional_vars.not_equal_to, 'attributes': { 'value': 'not_in' } },
				];
			} else {
				return [
					{ 'name': coditional_vars.equal_to, 'attributes': { 'value': 'is_equal_to' } },
					{ 'name': coditional_vars.not_equal_to, 'attributes': { 'value': 'not_in' } },
				];

			}

		}

		function isJson( str ) {
			try {
				JSON.parse( str );
			} catch ( err ) {
				return false;
			}
			return true;
		}

		productFilter();
		varproductFilter();
		userFilter();

        var fixHelperModified = function( e, tr ) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each( function( index ) {
                $( this ).width( $originals.eq( index ).width() );
            } );
            return $helper;
        };
        
        //Make diagnosis table sortable
        $( '.wcpfc-main-table table.wp-list-table tbody' ).sortable( {
            helper: fixHelperModified,
            axis: 'y',
            start: function(event, ui) {
		      	let selectedElement = ui.helper.find('.column-title');
		      	let totalWidth = selectedElement.outerWidth(true);

		      	let tableCells = $('.wcpfc-main-table table.wp-list-table').find('.column-title');
		      	tableCells.css('width', totalWidth);
		    },
            stop: function() {
                $('.wcpfc-main-table').block({
                    message: null,
                    overlayCSS: {
                        background: 'rgb(255, 255, 255)',
                        opacity: 0.6,
                    },
                });
                var listing = [];
                var paged = $('.current-page').val();
                $( '.ui-sortable-handle' ).each(function() {
                    listing.push($( this ).find( 'input' ).val());
                });
                $.ajax( {
                    type: 'POST',
                    url: coditional_vars.ajaxurl,
                    data: {
                        'action': 'wcpfc_pro_product_fees_conditions_sorting',
                        'sorting_conditional_fee': $( '#sorting_conditional_fee' ).val(),
                        'listing': listing,
                        'paged': paged
                    },
                    success: function() {
                        $('.wcpfc-main-table').unblock();

                        let tableCells = $('.wcpfc-main-table table.wp-list-table').find('.column-title');
		      			tableCells.css('width', '');
                    }
                } );

            }
        } );
        $( '.wcpfc-main-table table.wp-list-table tbody' ).disableSelection();
		
		if( $( '#ds_time_from' ).length || $( '#ds_time_to' ).length ){
			var ds_time_from = $( '#ds_time_from' ).val();
			var ds_time_to = $( '#ds_time_to' ).val();
			$( '#ds_time_from' ).timepicker({
				timeFormat: 'h:mm p',
				interval: 30,
				minTime: '00:00 AM',
				maxTime: '11:59 PM',
				startTime: ds_time_from,
				dynamic: true,
				dropdown: true,
				scrollbar: true,
				change: function () {
					var newTime = $(this).val();					
					$( '#ds_time_to' ).timepicker( 'option', 'minTime', newTime );
				}
			});
			
			$('#ds_time_to').timepicker({
				timeFormat: 'h:mm p',
				interval: 30,
				minTime: '00:00AM',
				maxTime: '11:59PM',
				startTime: ds_time_to,
				dynamic: true,
				dropdown: true,
				scrollbar: true
			});
		}
		$( '.ds_reset_time' ).click(function(){
			$( '#ds_time_from' ).val('');
			$( '#ds_time_to' ).val('');
		});

		/* Add AP Category functionality end here */
		getProductListBasedOnThreeCharAfterUpdate();

		//validate Advanced pricing table data
		$( '.wcpfc-main-table input[name="submitFee"]' ).on( 'click', function( e ) {
			// fees_pricing_rules
			var fees_pricing_rules_validation = true;
			var product_based_validation = true;
			var apply_per_qty_validation = true;
			var div;
		
			/* Checking product qty validation start */
			var product_qty_fees_conditions_conditions = $('select[name=\'fees[product_fees_conditions_condition][]\']')
				.map(function () {
					return $(this).val();
				}).get();
			if ( -1 !== product_qty_fees_conditions_conditions.indexOf('product_qty') || -1 !== product_qty_fees_conditions_conditions.indexOf('cart_specificproduct') ) {
				if (product_qty_fees_conditions_conditions.indexOf('product') === -1
				    && product_qty_fees_conditions_conditions.indexOf('variableproduct') === -1
				    && product_qty_fees_conditions_conditions.indexOf('category') === -1
				    && product_qty_fees_conditions_conditions.indexOf('tag') === -1
				    && product_qty_fees_conditions_conditions.indexOf('sku') === -1) {
					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					if ( $( '#warning_msg_6' ).length < 1 ) {
						div = document.createElement( 'div' );
						div = setAllAttributes( div, {
							'class': 'warning_msg',
							'id': 'warning_msg_6'
						} );
						div.textContent = coditional_vars.warning_msg6;
						$( div ).insertBefore( '.wcpfc-section-left .wcpfc-main-table' );
					}
					if ( $( '#warning_msg_6' ).length ) {
						$( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );
						setTimeout( function() {
							$( '#warning_msg_6' ).remove();
						}, 7000 );
					}
					e.preventDefault();
					return false;
				}
			}

			//Time range validation
			var startTime = $('#ds_time_from').val();
			var endTime = $('#ds_time_to').val();
			if( '' !== startTime && '' !== endTime ){
				var diffTime = ( new Date('1970-1-1 ' + endTime) - new Date('1970-1-1 ' + startTime) ) / 1000 / 60 / 60;
				if( diffTime <= 0 ){
					if ( $( '#warning_msg_7' ).length < 1 ) {
						div = document.createElement( 'div' );
						div = setAllAttributes( div, {
							'class': 'warning_msg',
							'id': 'warning_msg_7'
						} );
						div.textContent = coditional_vars.warning_msg7;
						$( div ).insertBefore( '.wcpfc-section-left .wcpfc-main-table' );
					}
					if ( $( '#warning_msg_7' ).length ) {
						$( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );
						setTimeout( function() {
							$( '#warning_msg_7' ).remove();
						}, 7000 );
					}
					e.preventDefault();
					return false;
				}
			}
			
			if ( fees_pricing_rules_validation === false || product_based_validation === false || apply_per_qty_validation === false ) {
				if ( $( '#warning_msg_5' ).length < 1 ) {
					div = document.createElement( 'div' );
					div = setAllAttributes( div, {
						'class': 'warning_msg',
						'id': 'warning_msg_5'
					} );
					div.textContent = coditional_vars.warning_msg5;
					$( div ).insertBefore( '.wcpfc-section-left .wcpfc-main-table' );
				}
				if ( $( '#warning_msg_5' ).length ) {
					$( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );
					setTimeout( function() {
						$( '#warning_msg_5' ).remove();
					}, 7000 );
				}
				e.preventDefault();
				return false;
			} else {
				if ( $( '.fees-pricing-rules .fees-table' ).is( ':hidden' ) ) {
					$( '.fees-pricing-rules .fees-table tr td input' ).each( function() {
						$( this ).removeAttr( 'required' );
					} );
				}
				return true;
			}
		} );

		/*End: hide show pricing rules status*/

		$( '[id^=fee_settings_product_cost]' ).keypress( validateNumber );

		function validateNumber( event ) {
			if( 'fixed' === $('#fee_settings_select_fee_type').val() ){
				return true;
			}

			if ($('#fee_settings_select_fee_type').val() === 'both') {
				var key_val = event.which;
				var inputValue = $('#fee_settings_product_cost').val().trim();
		
				// Allow digits, dot, plus sign, and backspace
				if ((key_val >= 48 && key_val <= 57) || key_val === 46 || key_val === 43 || key_val === 8) {
					// Allow the first plus sign after a digit
					if (key_val === 43 && (inputValue.includes('+') || inputValue === '')) {
						return false;
					}
					return true;
				} else {
					return false;
				}
			}

			var key = window.event ? event.keyCode : event.which;
			if ( event.keyCode === 8 || event.keyCode === 46 ) {
				return true;
			} else if ( key < 48 || key > 57 ) {
				return false;
			} else if ( key === 45 ) {
				return true;
			} else if ( key === 37 ) {
				return true;
			} else {
				return true;
			}
		}

		/*Start: Change shipping status form list section*/
		$( document ).on( 'click', '#fees_status_id', function() {
			var current_fees_id = $( this ).attr( 'data-smid' );
			var current_value = $( this ).prop( 'checked' );
            $('.wcpfc-main-table').block({
                message: null,
                overlayCSS: {
                    background: 'rgb(255, 255, 255)',
                    opacity: 0.6,
                },
            });
			$.ajax( {
				type: 'GET',
				url: coditional_vars.ajaxurl,
				data: {
					'action': 'wcpfc_pro_change_status_from_list_section',
					'security': coditional_vars.disable_fees_ajax_nonce,
					'current_fees_id': current_fees_id,
					'current_value': current_value
				}, beforeSend: function() {
					var div = document.createElement( 'div' );
					div = setAllAttributes( div, {
						'class': 'loader-overlay',
					} );

					var img = document.createElement( 'img' );
					img = setAllAttributes( img, {
						'id': 'before_ajax_id',
						'src': coditional_vars.ajax_icon
					} );

					div.appendChild( img );
					$( '#conditional-fee-listing' ).after( div );
				}, complete: function() {
					$( '.wcpfc-main-table .loader-overlay' ).remove();
                    $('.wcpfc-main-table').unblock();
				}, success: function() {
                    $('.wcpfc-main-table').unblock();
				}
			} );
		} );
		/*End: Change shipping status form list section*/

		function setAllAttributes( element, attributes ) {
			Object.keys( attributes ).forEach( function( key ) {
				element.setAttribute( key, attributes[ key ] );
				// use val
			} );
			return element;
		}

		//remove tr on delete icon click
		$( 'body' ).on( 'click', '.delete-row', function() {
			$( this ).parent().parent().remove();

			// Enable/disable first row delete button
			let allDeleteRow = $('#tbl-product-fee .delete-row');
			if ( allDeleteRow.length === 1 ) {
		    	allDeleteRow.addClass('disable-delete-icon');
			} else {
				allDeleteRow.removeClass('disable-delete-icon');
			}
		} );

		// Add loader on page loading.
	    clearTimeout(loaderTimeout);
	    $('.dotstore_plugin_page_loader').delay(100).fadeOut('slow');
	} );

	$(document).ready(function() {
		// Weight based options on/off
		if ($('#is_allow_custom_weight_base').is(':checked')) {
			$('.wcpfc-section-left .wcpfc-main-table .depend_of_custom_weight_base').show();
		} else {
			$('.wcpfc-section-left .wcpfc-main-table .depend_of_custom_weight_base').hide();
		}
		$(document).on('change', '#is_allow_custom_weight_base', function () {
			if (this.checked) {
				$('.wcpfc-section-left .wcpfc-main-table .depend_of_custom_weight_base').show();
			} else {
				$('.wcpfc-section-left .wcpfc-main-table .depend_of_custom_weight_base').hide();
			}
		});

		// Optional fee hide show rules
		function taxable_fee_div() {     
			var flag_init = $('.fee_settings_select_taxable:checked').val();
			if( 'yes' === flag_init ) {
				$('.enable_taxable_checked').show();
			} else {
				$('.enable_taxable_checked').hide();
			}
		}
		
		// use setTimeout() to execute
		setTimeout(taxable_fee_div, 1000);
		 
		$(document).on('change', '.fee_settings_select_taxable', function () {
			var flag = $(this).val();
			if( 'yes' === flag ) {
				$('.enable_taxable_checked').show();
			} else {
				$('.enable_taxable_checked').hide();
			}
		});

		// Optional fee based options on/off
		function optional_fee_div() {
			let flag_init = $('.fee_settings_select_optional:checked').val();
			if( 'yes' === flag_init ) {
				$('.enable_optional_checked').show();
			} else {
				$('.enable_optional_checked').hide();
			}
		}
		setTimeout(optional_fee_div, 1000);
		$(document).on('change', '.fee_settings_select_optional', function () {
			let flag = $(this).val();
			if( 'yes' === flag ) {
				$('.enable_optional_checked').show();
			} else {
				$('.enable_optional_checked').hide();
			}
		});

		// Advanced fee rules options on/off
		hideShowPricingRulesBasedOnPricingRuleStatus();
		function hideShowPricingRulesBasedOnPricingRuleStatus() {
			if ( $( 'input[name="ap_rule_status"]' ).prop( 'checked' ) === true ) {
				$( '.fees_pricing_rules' ).show();
			} else if ( $( 'input[name="ap_rule_status"]' ).prop( 'checked' ) === false ) {
				$( '.fees_pricing_rules' ).hide();
			}
		}
		$( 'body' ).on( 'click', 'input[name="ap_rule_status"]', function() {
			if ( $( this ).prop( 'checked' ) === true ) {
				$( '.fees_pricing_rules' ).show();
			} else if ( $( this ).prop( 'checked' ) === false ) {
				$( '.fees_pricing_rules' ).hide();
			}
		} );

		// Enable/disable first row delete button
		let allDeleteRow = $('#tbl-product-fee .delete-row');
		if ( allDeleteRow.length === 1 ) {
	    	allDeleteRow.addClass('disable-delete-icon');
		}

		$('#ds_select_day_of_week').select2({
			placeholder: coditional_vars.select_days
		});

	    /** tiptip js implementation */
	    $( '.woocommerce-help-tip' ).tipTip( {
	        'attribute': 'data-tip',
	        'fadeIn': 50,
	        'fadeOut': 50,
	        'delay': 200,
	        'keepAlive': true
	    } );

	    $(document).on('click', '.notice-dismiss', function(){ 
	        $(this).parent().remove(); 
	    });

	    /** Upgrade Dashboard Script START */
	    // Dashboard features popup script
	    $(document).on('click', '.dotstore-upgrade-dashboard .premium-key-fetures .premium-feature-popup', function (event) {
	        let $trigger = $('.feature-explanation-popup, .feature-explanation-popup *');
	        if(!$trigger.is(event.target) && $trigger.has(event.target).length === 0){
	            $('.feature-explanation-popup-main').not($(this).find('.feature-explanation-popup-main')).hide();
	            $(this).parents('li').find('.feature-explanation-popup-main').show();
	            $('body').addClass('feature-explanation-popup-visible');
	        }
	    });
	    $(document).on('click', '.dotstore-upgrade-dashboard .popup-close-btn', function () {
	        $(this).parents('.feature-explanation-popup-main').hide();
	        $('body').removeClass('feature-explanation-popup-visible');
	    });
	    /** Upgrade Dashboard Script End */

	    // script for discount plugin modal
		$(document).on('input', '#dotsstoremain #fee_settings_product_cost, #dotsstoremain .fees-pricing-rules .number-field', function(){
			var priceValue = $(this).val();
		    if (parseInt(priceValue) < 0) {
		    	$('body').addClass('wcpfc-plugin-modal-visible');
		    } else {
		    	$('body').removeClass('wcpfc-plugin-modal-visible');
		    }
		});

		$(document).on('click', '#dotsstoremain .wcpfc-pro-label, .wcpfc-upgrade-pro-to-unlock, .wcpfc-section-left .upgrade-now', function(){
			$('body').addClass('wcpfc-modal-visible');
		});

		$(document).on('click', '#dotsstoremain .modal-close-btn', function(){
			$('body').removeClass('wcpfc-modal-visible');
			$('body').removeClass('wcpfc-plugin-modal-visible');
		});

		$('.wcpffc_chk_advanced_settings').click(function(){
	        $('.wcpffc_advanced_setting_section').toggle();
	    });

	    /** Dynamic Promotional Bar START */
        $(document).on('click', '.dpbpop-close', function () {
            var popupName 		= $(this).attr('data-popup-name');
            setCookie( 'banner_' + popupName, 'yes', 60 * 24 * 7);
            $('.' + popupName).hide();
        });

		$(document).on('click', '.dpb-popup .dpb-popup-meta a', function () {
            var promotional_id = $(this).parents().find('.dpbpop-close').attr('data-bar-id');

			//Create a new Student object using the values from the textfields
			var apiData = {
				'bar_id' : promotional_id
			};

			$.ajax({
				type: 'POST',
				url: coditional_vars.dpb_api_url + 'wp-content/plugins/dots-dynamic-promotional-banner/bar-response.php',
				data: JSON.stringify(apiData),// now data come in this function
		        dataType: 'json',
		        cors: true,
		        contentType:'application/json',
		        
				success: function (data) {
					console.log(data);
				},
				error: function () {
				}
			 });
        });
        /** Dynamic Promotional Bar END */

		/** Plugin Setup Wizard Script START */
		// Hide & show wizard steps based on the url params 
	  	var urlParams = new URLSearchParams(window.location.search);
	  	if (urlParams.has('require_license')) {
	    	$('.ds-plugin-setup-wizard-main .tab-panel').hide();
	    	$( '.ds-plugin-setup-wizard-main #step5' ).show();
	  	} else {
	  		$( '.ds-plugin-setup-wizard-main #step1' ).show();
	  	}
	  	
        // Plugin setup wizard steps script
        $(document).on('click', '.ds-plugin-setup-wizard-main .tab-panel .btn-primary:not(.ds-wizard-complete)', function () {
	        var curruntStep = jQuery(this).closest('.tab-panel').attr('id');
	        var nextStep = 'step' + ( parseInt( curruntStep.slice(4,5) ) + 1 ); // Masteringjs.io

	        if( 'step5' !== curruntStep ) {
	        	// Youtube videos stop on next step
                $('iframe[src*="https://www.youtube.com/embed/"]').each(function(){
                   $(this).attr('src', $(this).attr('src'));
                   return false;
                });
                
	         	jQuery( '#' + curruntStep ).hide();
	            jQuery( '#' + nextStep ).show();   
	        }
	    });

	    // Get allow for marketing or not
	    if ( $( '.ds-plugin-setup-wizard-main .ds_count_me_in' ).is( ':checked' ) ) {
	    	$('#fs_marketing_optin input[name="allow-marketing"][value="true"]').prop('checked', true);
	    } else {
	    	$('#fs_marketing_optin input[name="allow-marketing"][value="false"]').prop('checked', true);
	    }

		// Get allow for marketing or not on change	    
	    $(document).on( 'change', '.ds-plugin-setup-wizard-main .ds_count_me_in', function() {
			if ( this.checked ) {
				$('#fs_marketing_optin input[name="allow-marketing"][value="true"]').prop('checked', true);
			} else {
		    	$('#fs_marketing_optin input[name="allow-marketing"][value="false"]').prop('checked', true);
		    }
		});

	    // Complete setup wizard
	    $(document).on( 'click', '.ds-plugin-setup-wizard-main .tab-panel .ds-wizard-complete', function() {
			if ( $( '.ds-plugin-setup-wizard-main .ds_count_me_in' ).is( ':checked' ) ) {
				$( '.fs-actions button'  ).trigger('click');
			} else {
		    	$('.fs-actions #skip_activation')[0].click();
		    }
		});

	    // Send setup wizard data on Ajax callback
		$(document).on( 'click', '.ds-plugin-setup-wizard-main .fs-actions button', function() {
			var wizardData = {
                'action': 'wcpfc_plugin_setup_wizard_submit',
                'survey_list': $('.ds-plugin-setup-wizard-main .ds-wizard-where-hear-select').val(),
                'nonce': coditional_vars.setup_wizard_ajax_nonce
            };

            $.ajax({
                url: coditional_vars.ajaxurl,
                data: wizardData,
                success: function ( success ) {
                    console.log(success);
                }
            });
		});
		/** Plugin Setup Wizard Script End */

		// Script for Beacon configuration
        var helpBeaconCookie = getCookie( 'wcpfc-help-beacon-hide' );
        if ( ! helpBeaconCookie ) {
            Beacon('init', 'afe1c188-3c3b-4c5f-9dbd-87329301c920');
            Beacon('config', {
                display: {
                    style: 'icon',
                    iconImage: 'message',
                    zIndex: '99999'
                }
            });

            // Add plugin articles IDs to display in beacon
            Beacon('suggest', ['5df366a304286364bc92d258', '5df9d79a2c7d3a7e9ae54182', '5dfa012604286364bc92fecb', '5df9edef2c7d3a7e9ae54256', '5f61fdfe4cedfd00173b8786']);

            // Add custom close icon form beacon
            setTimeout(function() {
                if ( $( '.hsds-beacon .BeaconFabButtonFrame' ).length > 0 ) {
                    let newElement = document.createElement('span');
                    newElement.classList.add('dashicons', 'dashicons-no-alt', 'dots-beacon-close');
                    let container = document.getElementsByClassName('BeaconFabButtonFrame');
                    container[0].appendChild( newElement );
                }
            }, 3000);

            // Hide beacon
            $(document).on('click', '.dots-beacon-close', function(){
                Beacon('destroy');
                setCookie( 'wcpfc-help-beacon-hide' , 'true', 24 * 60 );
            });
        }

        /** Script for Freemius upgrade popup */
        $(document).on('click', '.dots-header .dots-upgrade-btn, .dotstore-upgrade-dashboard .upgrade-now', function(e){
            e.preventDefault();
            upgradeToProFreemius( '' );
        });
        $(document).on('click', '.upgrade-to-pro-modal-main .upgrade-now', function(e){
            e.preventDefault();
            $('body').removeClass('wcpfc-modal-visible');
            let couponCode = $('.upgrade-to-pro-discount-code').val();
            upgradeToProFreemius( couponCode );
        });
	});
})( jQuery );
