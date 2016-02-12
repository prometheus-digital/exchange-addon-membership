jQuery( document ).ready( function ( $ ) {

	var metabox = $( '#it_exchange_membership_addon_membership_access_metabox' );
	var postID = $( 'input[name=post_ID]' ).val();
	var nonce = ITE_MEMBERSHIP.nonce;

	var datePickerOpts = {
		prevText  : '',
		nextText  : '',
		dateFormat: $( "#it_exchange_membership_df" ).val(),
		beforeShow: function ( input, instance ) {
			$( '#ui-datepicker-div' ).addClass( 'exchange-datepicker' );
		}
	};

	if ( $( '.datepicker', metabox ).prop( 'type' ) != 'date' ) {
		$( '.datepicker', metabox ).datepicker( datePickerOpts );
	}

	/**
	 * Fires when the Add Restriction button is clicked.
	 *
	 * Sends an AJAX request to get the HTML to build the new restriction form.
	 */
	metabox.on( 'click', '.it-exchange-add-new-restriction', function ( event ) {
		event.preventDefault();
		var data = {
			action: 'it-exchange-membership-addon-add-content-access-rule-to-post',
			ID    : postID,
			nonce : nonce
		};

		$.post( ajaxurl, data, function ( response ) {

			if ( typeof response == 'object' ) {

				if ( ! response.success ) {
					alert( response.data.message );

					return;
				}
			}

			$( '.it-exchange-membership-new-restrictions' ).append( response );
		} );
	} );

	/**
	 * Fires when the exemption checkbox is checked or unchecked.
	 *
	 * Exemptions are updated via AJAX on change, not on post save.
	 */
	metabox.on( 'click', '.it-exchange-restriction-exemptions', function ( event ) {
		var data = {
			action       : 'it-exchange-membership-addon-modify-restrictions-exemptions',
			post_id      : postID,
			membership_id: $( 'input[name=it_exchange_membership_id]', $( this ).closest( '.it-exchange-membership-restriction-group' ) ).val(),
			type         : $( this ).val(),
			checked      : $( this ).is( ':checked' ),
			rule_data    : {
				term     : $( this ).data( 'term' ),
				selection: $( this ).data( 'selection' )
			},
			nonce        : nonce
		};

		var checkbox = $( this );

		$.post( ajaxurl, data, function ( response ) {

			if ( typeof response == 'object' ) {

				if ( ! response.success ) {
					alert( response.data.message );

					checkbox.prop( 'checked', ! checkbox.prop( 'checked' ) );
				}
			}
		} );
	} );

	/**
	 * Fires when the 'x' icon in the new rule form is clicked.
	 *
	 * This hides the 'Add Restriction' form.
	 */
	metabox.on( 'click', '.it-exchange-membership-remove-new-rule', function ( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		parent.remove();
	} );

	/**
	 * Fires when the 'x' icon for a membership rule is clicked.
	 *
	 * This sends an AJAX request to remove the rule. Upon completion,
	 * the rule is removed from the DOM.
	 */
	metabox.on( 'click', '.it-exchange-membership-remove-rule', function ( event ) {
		event.preventDefault();

		var parent = $( this ).closest( '.it-exchange-membership-restriction-group' );

		var data = {
			action       : 'it-exchange-membership-addon-remove-rule-from-post',
			post_id      : postID,
			membership_id: $( 'input[name=it_exchange_membership_id]', parent ).val(),
			rule         : $( 'input[name="it_exchange_rule_id"]', parent ).val(),
			nonce        : nonce
		};

		$.post( ajaxurl, data, function ( response ) {

			if ( typeof response == 'object' ) {

				if ( ! response.success ) {
					alert( response.data.message );

					return;
				}
			}

			if ( 0 < response.length ) {
				$( ".it-exchange-membership-restrictions" ).replaceWith( function () {
					return $( response ).fadeOut( 'slow' ).fadeIn( 'slow' );
				} );
			}
		} );
	} );

	/**
	 * Fires when the 'OK' button in the add new restriction form is clicked.
	 *
	 * This sends an AJAX request to create the rule, then replaces the Add New form
	 * with the new rule HTML.
	 */
	metabox.on( 'click', '.it-exchange-add-new-restriction-ok-button', function ( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			action       : 'it-exchange-membership-addon-add-new-rule-to-post',
			post_id      : postID,
			membership_id: $( 'select[name=it_exchange_membership_id] option:selected', parent ).val(),
			delay        : $( "#it-exchange-membership-delay-type" ).val(),
			nonce        : nonce
		};

		$.post( ajaxurl, data, function ( response ) {

			if ( typeof response == 'object' ) {

				if ( ! response.success ) {
					alert( response.data.message );

					return;
				}
			}

			if ( 0 < response.length ) {
				$( ".it-exchange-membership-restrictions" ).replaceWith( function () {

					var $el = $( response );

					// Datepicker for the start and end dates.
					if ( $( '.datepicker', $el ).prop( 'type' ) != 'date' ) {
						$( '.datepicker', $el ).datepicker( datePickerOpts );
					}

					return $el.fadeOut( 'slow' ).fadeIn( 'slow' );
				} );
				$( parent ).remove();
			}
		} );
	} );

	metabox.on( 'change', '.it-exchange-membership-delay-rule input, .it-exchange-membership-delay-rule select', function ( e ) {

		var membershipID = $( 'input[name="it_exchange_membership_id"]', $( this ).closest( '.it-exchange-membership-restriction-group' ) ).val();

		var parent = $( this ).closest( '.it-exchange-membership-delay-rule' );

		var val = $( this ).val();
		var type = parent.data( 'type' );
		var name = $( this ).attr( 'name' );
		name = name.replace( '[' + membershipID + '][delay]', '' );
		name = name.replace( '[', '' );
		name = name.replace( ']', '' );

		var changes = {};
		changes[ name ] = val;

		updateDelayRule( postID, membershipID, type, changes );
	} );

	/**
	 * Update a drip rule.
	 *
	 * @param postID
	 * @param membershipID
	 * @param type
	 * @param changes
	 */
	function updateDelayRule( postID, membershipID, type, changes ) {

		var data = {
			post      : postID,
			membership: membershipID,
			type      : type,
			changes   : changes,
			action    : 'it-exchange-membership-update-delay-rule',
			nonce     : nonce
		};

		$.post( ajaxurl, data, function ( response ) {

			if ( typeof response == 'object' ) {

				if ( ! response.success ) {
					alert( response.data.message );
				}
			}
		} );
	}
} );