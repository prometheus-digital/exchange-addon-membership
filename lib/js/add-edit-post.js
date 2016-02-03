jQuery( document ).ready( function ( $ ) {

	var metabox = $( '#it_exchange_membership_addon_membership_access_metabox' );
	var postID = $( 'input[name=post_ID]' ).val();

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
			interval     : $( 'input[name=it_exchange_membership_drip_interval]', parent ).val(),
			duration     : $( 'select[name=it_exchange_membership_drip_duration] option:selected', parent ).val(),
		};

		$.post( ajaxurl, data, function ( response ) {
			if ( 0 < response.length ) {
				$( ".it-exchange-membership-restrictions" ).replaceWith( function () {
					return $( response ).fadeOut( 'slow' ).fadeIn( 'slow' );
				} );
				$( parent ).remove();
			}
		} );
	} );

	/**
	 * Fires whenever the interval changes.
	 *
	 * The interval is changed in real time via AJAX.
	 */
	metabox.on( 'input keyup change', '.it-exchange-membership-drip-rule input.it-exchange-membership-drip-rule-interval', function ( event ) {

		event.preventDefault();

		var membershipID = $( 'input[name=it_exchange_membership_id]', $( this ).closest( '.it-exchange-membership-restriction-group' ) ).val();

		updateDripRule( postID, membershipID, {
			interval: $( this ).val()
		} );
	} );

	/**
	 * Fires whenever the duration ( days, weeks, months ) changes.
	 *
	 * The duration is changed in real time via AJAX.
	 */
	metabox.on( 'change', '.it-exchange-membership-drip-rule select.it-exchange-membership-drip-rule-duration', function ( event ) {
		event.preventDefault();

		var membershipID = $( 'input[name=it_exchange_membership_id]', $( this ).closest( '.it-exchange-membership-restriction-group' ) ).val();

		updateDripRule( postID, membershipID, {
			duration: $( 'option:selected', this ).val()
		} );
	} );

	/**
	 * Update a drip rule.
	 *
	 * @param postID
	 * @param membershipID
	 * @param changes
	 */
	function updateDripRule( postID, membershipID, changes ) {

		var data = {
			post      : postID,
			membership: membershipID,
			changes   : changes,
			action    : 'it-exchange-membership-update-drip-rule'
		};

		$.post( ajaxurl, data );
	}
} );