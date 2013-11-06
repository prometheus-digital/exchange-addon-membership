jQuery(document).ready(function($) {
	contentAccessRulesSortable();

	$( '.it-exchange-membership-content-access-add-new-rule' ).on( 'click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			'action': 'it-exchange-membership-addon-add-content-access-rule',
			'count':  it_exchange_membership_addon_content_access_iteration,
		}
		it_exchange_membership_addon_content_access_iteration++;
		$.post( ajaxurl, data, function( response ) {
			console.log( response );
			$( '.it-exchange-content-access-list' ).removeClass('hidden');
			$( '.it-exchange-content-no-rules ' ).addClass('hidden');
			$( '.it-exchange-membership-addon-content-access-rules' ).append( response );
		});
	});
	
	$( '.it-exchange-membership-content-type-selections' ).on('change', function() {
		var parent = $( this ).parent().parent();
		var data = {
			'action':  'it-exchange-membership-addon-content-type-terms',
			'type':    $( 'option:selected', this ).attr( 'data-type' ),
			'value':   $( 'option:selected', this ).val(),
			'count':   $( parent ).attr( 'data-count' ),
		}
		if ( data['type'] ) { //Only call AJAX if we have a content type
			$.post( ajaxurl, data, function( response ) {
				console.log( response );
				$( '.it-exchange-membership-content-type-terms', parent ).removeClass( 'hidden' );
				$( '.it-exchange-membership-content-type-terms', parent ).html( response )
				if ( 'posts' === data['type'] ) {
					$( '.it-exchange-membership-content-type-drip', parent ).removeClass( 'hidden' );
				} else {
					$( '.it-exchange-membership-content-type-drip', parent ).addClass( 'hidden' );
				}
			});
		} else {
			$( '.it-exchange-membership-content-type-terms', parent ).addClass( 'hidden' );
			$( '.it-exchange-membership-content-type-drip', parent ).addClass( 'hidden' );
		}
	});
	
	$( '.it-exchange-membership-addon-remove-content-access-rule, .it-exchange-membership-addon-remove-content-access-group' ).live('click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		parent.remove();
	});
	
	function contentAccessRulesSortable() {
		$( '.content-access-sortable' ).sortable({
			placeholder: 'it-exchange-membership-addon-sorting-placeholder',
			connectWith: '.content-access-sortable',
			stop: function(event, ui) {
				if ( false !== ( group_id = ui.item.parent().data( 'group-id' ) ) ) {
					$( '.it-exchange-content-access-group', ui.item ).val( group_id );
				} else {
					$( '.it-exchange-content-access-group', ui.item ).val( '' );
				}
		    }
		});
	}
		
	$( '.it-exchange-membership-content-access-add-new-group' ).on( 'click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			'action': 'it-exchange-membership-addon-add-content-access-group',
			'count':  it_exchange_membership_addon_content_access_iteration,
			'group_count':  it_exchange_membership_addon_content_access_group_iteration,
		}
		it_exchange_membership_addon_content_access_iteration++;
		it_exchange_membership_addon_content_access_group_iteration++;
		$.post( ajaxurl, data, function( response ) {
			console.log( response );
			$( '.it-exchange-membership-addon-content-access-rules' ).append( response );
			contentAccessRulesSortable();
		});
	});
	
});