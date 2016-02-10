jQuery( document ).ready( function ( $ ) {

	var metabox = $( '#it_exchange_membership_addon_membership_access_metabox' );
	var postID = $( 'input[name=post_ID]' ).val();
	var dateFormat = $( "#it_exchange_membership_df" ).val();

	$( '.datepicker', metabox ).datepicker( {
		prevText  : '',
		nextText  : '',
		dateFormat: dateFormat,
		beforeShow: function ( input, instance ) {
			console.log( input );
			console.log( instance );
			$( '#ui-datepicker-div' ).addClass( 'exchange-datepicker' );
		}
	} );

	/**
	 * Fires when the Add Restriction button is clicked.
	 *
	 * Sends an AJAX request to get the HTML to build the new restriction form.
	 */
	metabox.on( 'click', '.it-exchange-add-new-restriction', function ( event ) {
		event.preventDefault();
		var data = {
			action: 'it-exchange-membership-addon-add-content-access-rule-to-post',
			ID    : postID
		};

		$.post( ajaxurl, data, function ( response ) {
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
			}
		};

		$.post( ajaxurl, data );
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

		var data = {
			action       : 'it-exchange-membership-addon-remove-rule-from-post',
			post_id      : postID,
			membership_id: $( 'input[name=it_exchange_membership_id]', $( this ).closest( '.it-exchange-membership-restriction-group' ) ).val(),
		};

		$.post( ajaxurl, data, function ( response ) {
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
			delay        : $( "#it-exchange-membership-delay-type" ).val()
		};

		$.post( ajaxurl, data, function ( response ) {
			if ( 0 < response.length ) {
				$( ".it-exchange-membership-restrictions" ).replaceWith( function () {

					var $el = $( response );

					// Datepicker for the start and end dates.
					if ( $( '.datepicker', $el ).prop( 'type' ) != 'date' ) {
						$( '.datepicker', $el ).datepicker( {
							prevText  : '',
							nextText  : '',
							dateFormat: dateFormat
						} );
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
			action    : 'it-exchange-membership-update-delay-rule'
		};

		$.post( ajaxurl, data );
	}
} );