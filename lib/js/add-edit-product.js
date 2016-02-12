jQuery( document ).ready( function ( $ ) {

	contentAccessRulesSortable();

	var accessRules = $( '#it-exchange-product-membership-content-access-rules' );
	var hierarchy = $( '#it-exchange-product-membership-hierarchy' );
	var nonce = ITE_MEMBERSHIP.nonce;

	accessRules.on( 'click', '.it-exchange-membership-content-access-add-new-rule', function ( event ) {
		event.preventDefault();
		var data = {
			'action': 'it-exchange-membership-addon-add-content-access-rule',
			'count' : it_exchange_membership_addon_content_access_iteration,
			nonce   : nonce
		};

		it_exchange_membership_addon_content_access_iteration ++;
		$.post( ajaxurl, data, function ( response ) {

			if ( typeof response == 'object' ) {

				if ( ! response.success ) {
					alert( response.data.message );

					return;
				}
			}

			$( '.it-exchange-content-access-list' ).removeClass( 'hidden' );
			$( '.it-exchange-content-no-rules ' ).addClass( 'hidden' );
			$( '.it-exchange-membership-addon-content-access-rules' ).append( response );
		} );
	} );

	accessRules.on( 'change', '.it-exchange-membership-content-type-selections', function () { //not working with .on
		var parent = $( this ).parent().parent();
		var data = {
			action: 'it-exchange-membership-addon-content-type-terms',
			type  : $( 'option:selected', this ).attr( 'data-type' ),
			value : $( 'option:selected', this ).val(),
			count : $( parent ).attr( 'data-count' ),
			ID    : $( "#post_ID" ).val(),
			nonce : nonce
		};

		if ( data[ 'type' ] ) { //Only call AJAX if we have a content type
			$.post( ajaxurl, data, function ( response ) {

				if ( typeof response == 'object' ) {

					if ( ! response.success ) {
						alert( response.data.message );

						return;
					}
				}

				$( '.it-exchange-membership-content-type-terms', parent ).removeClass( 'hidden' );
				$( '.it-exchange-membership-content-type-terms', parent ).html( response );
			} );
		} else {
			$( '.it-exchange-membership-content-type-terms', parent ).addClass( 'hidden' );
			$( '.it-exchange-membership-content-type-drip', parent ).addClass( 'hidden' );
			$( '.it-exchange-content-access-delay-unavailable', parent ).addClass( 'hidden' );
		}

		var delay_data = {
			action   : 'it-exchange-membership-addon-content-delay-rules',
			count    : $( parent ).attr( 'data-count' ),
			delayable: $( 'option:selected', this ).data( 'delayable' ),
			nonce    : nonce
		};

		$.post( ajaxurl, delay_data, function ( response ) {

			if ( typeof response == 'object' ) {

				if ( ! response.success ) {
					alert( response.data.message );

					return;
				}
			}

			var $el = $( '.it-exchange-content-access-delay', parent ).html( response );

			if ( ( $( '.datepicker', $el ).prop( 'type' ) != 'date' ) ) {
				$( '.datepicker', $el ).datepicker( {
					prevText  : '',
					nextText  : '',
					minDate   : 0,
					dateFormat: $( 'input[name=it_exchange_availability_date_picker_format]' ).val(),
				} );
			}
		} );
	} );

	$( document ).on( 'click', '.it-exchange-membership-delay-rule-selection select', function ( e ) {

		var selected = $( this ).val();
		var parent = $( this ).closest( '.it-exchange-content-access-delay' );

		$( '.it-exchange-membership-content-delay-rule:not(.hidden)', parent ).addClass( 'hidden' );

		if ( selected.length > 0 ) {
			$( '.it-exchange-membership-content-delay-rule-' + selected, parent ).removeClass( 'hidden' );
		}
	} );

	accessRules.on( 'click', '.it-exchange-membership-addon-remove-content-access-rule, .it-exchange-membership-addon-remove-content-access-group', function ( event ) { //not working with .on
		event.preventDefault();
		$( this ).closest( '.it-exchange-membership-addon-content-access-rule' ).remove();
		if ( ! $.trim( $( '.it-exchange-membership-addon-content-access-rules' ).html() ) ) {
			$( '.it-exchange-content-access-list' ).addClass( 'hidden' );
			$( '.it-exchange-content-no-rules ' ).removeClass( 'hidden' );
		}
	} );

	accessRules.on( 'click', '.it-exchange-membership-addon-ungroup-content-access-group', function ( event ) {
		event.preventDefault();
		var group_parent = $( this ).closest( '.it-exchange-membership-addon-content-access-rule' );
		var grouped_items = $( group_parent ).find( '.it-exchange-membership-content-access-group-content > .it-exchange-membership-addon-content-access-rule' );
		grouped_items.each( function ( index, item ) {
			$( '.it-exchange-content-access-group', item ).val( '' );
			$( item ).insertAfter( group_parent );
		} );
		$( group_parent ).remove();
	} );

	function contentAccessRulesSortable() {
		$( '.content-access-sortable' ).sortable( {
			placeholder: 'it-exchange-membership-addon-sorting-placeholder',
			connectWith: '.content-access-sortable',
			items      : '.it-exchange-membership-addon-content-access-rule',
			stop       : function ( event, ui ) {
				if ( false !== ( grouped_id = ui.item.parent().data( 'group-id' ) ) ) {
					$( ui.item ).children( '.it-exchange-content-access-group' ).each( function ( index, child ) {
						$( child ).val( grouped_id );
					} )
				} else {
					$( ui.item ).children( '.it-exchange-content-access-group' ).each( function ( index, child ) {
						$( child ).val( '' );
					} )
				}
			},
			receive    : function ( event, ui ) {
				//show empty message on sender if applicable
				if ( $( '.it-exchange-membership-addon-content-access-rule', ui.sender ).length == 0 ) {
					$( '.nosort', ui.sender ).slideDown();
				} else {
					$( '.nosort', ui.sender ).slideUp();
				}
			}
		} );
	}

	accessRules.on( 'click', '.it-exchange-membership-content-access-add-new-group a', function ( event ) {
		event.preventDefault();

		var data = {
			'action'     : 'it-exchange-membership-addon-add-content-access-group',
			'count'      : it_exchange_membership_addon_content_access_iteration,
			'group_count': it_exchange_membership_addon_content_access_group_iteration,
			nonce        : nonce
		};

		it_exchange_membership_addon_content_access_iteration ++;
		it_exchange_membership_addon_content_access_group_iteration ++;
		$.post( ajaxurl, data, function ( response ) {

			if ( typeof response == 'object' ) {

				if ( ! response.success ) {
					alert( response.data.message );

					return;
				}
			}

			$( '.it-exchange-content-access-list' ).removeClass( 'hidden' );
			$( '.it-exchange-content-no-rules ' ).addClass( 'hidden' );
			$( '.it-exchange-membership-addon-content-access-rules' ).append( response );
			contentAccessRulesSortable();
		} );
	} );

	accessRules.on( 'click', '.group-layout', function ( event ) {
		var parent = $( this ).parent();
		type = $( this ).data( 'type' );
		$( 'span', parent ).removeClass( 'active-group-layout' );
		$( this ).addClass( 'active-group-layout' );
		$( 'input.group-layout-input', parent ).val( type );
	} );

	accessRules.on( {
		mouseenter: function () {
			$( this ).children( '.it-exchange-membership-addon-group-actions' ).show();
		},
		mouseleave: function () {
			$( this ).children( '.it-exchange-membership-addon-group-actions' ).hide();
		}
	}, '.it-exchange-membership-addon-group-action-wrapper' );

	function it_exchange_membership_hierarchy_duplicate_check( product_id ) {
		var found = false;
		$( 'div.it-exchange-membership-child-ids-list-div ul li' ).each( function () {
			if ( $( this ).data( 'child-id' ) == product_id ) {
				alert( 'Already a child of this membership' );
				found = true;
			}
		} );
		if ( ! found ) {
			$( 'div.it-exchange-membership-parent-ids-list-div ul li' ).each( function () {
				if ( $( this ).data( 'parent-id' ) == product_id ) {
					alert( 'Already a parent of this membership' );
					found = true;
				}
			} );
		}
		return found;
	}


	hierarchy.on( 'click', '.it-exchange-membership-hierarchy-add-child a', function ( event ) {

		event.preventDefault();

		var data = {
			'action'    : 'it-exchange-membership-addon-add-membership-child',
			'product_id': $( '.it-exchange-membership-child-id option:selected' ).val(),
			'child_ids' : $( 'input[name=it-exchange-membership-child-ids\\[\\]]' ).serializeArray(),
			'post_id'   : $( 'input[name=post_ID]' ).val(),
		};

		if ( 0 !== data[ 'product_id' ].length ) {
			var found = it_exchange_membership_hierarchy_duplicate_check( data[ 'product_id' ] );

			if ( ! found ) {
				$.post( ajaxurl, data, function ( response ) {
					$( 'ul li', response ).each( function () {
						child_id = $( this ).data( 'child-id' );
						$( 'div.it-exchange-membership-parent-ids-list-div ul li' ).each( function () {
							if ( $( this ).data( 'parent-id' ) == child_id ) {
								alert( 'Already a parent of this membership' );
								found = true;
								return;
							}
						} );
					} );

					if ( ! found )
						$( '.it-exchange-membership-child-ids-list-div' ).html( response );
				} );
			}
		}
	} );

	hierarchy.on( 'click', '.it-exchange-membership-hierarchy-add-parent a', function ( event ) {
		event.preventDefault();
		var data = {
			'action'    : 'it-exchange-membership-addon-add-membership-parent',
			'product_id': $( '.it-exchange-membership-parent-id option:selected' ).val(),
			'parent_ids': $( 'input[name=it-exchange-membership-parent-ids\\[\\]]' ).serializeArray(),
			'post_id'   : $( 'input[name=post_ID]' ).val(),
		};

		if ( 0 !== data[ 'product_id' ].length ) {
			var found = it_exchange_membership_hierarchy_duplicate_check( data[ 'product_id' ] );

			if ( ! found ) {
				$.post( ajaxurl, data, function ( response ) {
					$( '.it-exchange-membership-parent-ids-list-div' ).html( response );
				} );
			}
		}
	} );

	hierarchy.on( 'click', '.it-exchange-membership-addon-delete-membership-child, .it-exchange-membership-addon-delete-membership-parent', function ( event ) {
		event.preventDefault();
		$( this ).closest( 'li' ).remove();
	} );

	$( "#it-exchange-show-intended-audience" ).click( function () {
		if ( $( this ).is( ':checked' ) ) {
			$( "#it-exchange-intended-audience" ).removeClass( 'hide-if-js' );
		} else {
			$( "#it-exchange-intended-audience" ).addClass( 'hide-if-js' );
		}
	} );

	$( "#it-exchange-show-objectives" ).click( function () {
		if ( $( this ).is( ':checked' ) ) {
			$( "#it-exchange-objectives" ).removeClass( 'hide-if-js' );
		} else {
			$( "#it-exchange-objectives" ).addClass( 'hide-if-js' );
		}
	} );

	$( "#it-exchange-show-prerequisites" ).click( function () {
		if ( $( this ).is( ':checked' ) ) {
			$( "#it-exchange-prerequisites" ).removeClass( 'hide-if-js' );
		} else {
			$( "#it-exchange-prerequisites" ).addClass( 'hide-if-js' );
		}
	} );

	$( "#it-exchange-show-override-content-restricted" ).click( function () {
		if ( $( this ).is( ':checked' ) ) {
			$( "#it-exchange-override-content-restricted" ).removeClass( 'hide-if-js' );
		} else {
			$( "#it-exchange-override-content-restricted" ).addClass( 'hide-if-js' );
		}
	} );

	$( "#it-exchange-show-override-content-delayed" ).click( function () {
		if ( $( this ).is( ':checked' ) ) {
			$( "#it-exchange-override-content-delayed" ).removeClass( 'hide-if-js' );
		} else {
			$( "#it-exchange-override-content-delayed" ).addClass( 'hide-if-js' );
		}
	} );

} );