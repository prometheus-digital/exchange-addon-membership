jQuery(document).ready(function($) {
	contentAccessRulesSortable();

	$( '.it-exchange-membership-content-access-add-new-rule' ).live( 'click', function( event ) {
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
	
	$( '.it-exchange-membership-content-type-selections' ).live( 'change', function() { //not working with .on
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
	
	$( '.it-exchange-membership-addon-remove-content-access-rule, .it-exchange-membership-addon-remove-content-access-group' ).live('click', function( event ) { //not working with .on
		event.preventDefault();
		$( this ).closest( '.it-exchange-membership-addon-content-access-rule' ).remove();
	});
	
	
	$( '.it-exchange-membership-addon-ungroup-content-access-group' ).live('click', function( event ) {
		event.preventDefault();
		var group_parent = $( this ).closest( '.it-exchange-membership-addon-content-access-rule' );
		var grouped_items = $( group_parent ).find( '.it-exchange-membership-content-access-group-content > .it-exchange-membership-addon-content-access-rule' );
		grouped_items.each( function( index, item ) {
			$( '.it-exchange-content-access-group', item ).val( '' );
			$( item ).insertAfter( group_parent );
		});
		$( group_parent ).remove();
	});
	
	function contentAccessRulesSortable() {
		$( '.content-access-sortable' ).sortable({
			placeholder: 'it-exchange-membership-addon-sorting-placeholder',
			connectWith: '.content-access-sortable',
			stop: function(event, ui) {
				if ( false !== ( grouped_id = ui.item.parent().data( 'group-id' ) ) ) {
					$( ui.item ).children( '.it-exchange-content-access-group' ).each( function( index, child ) {
						$( child ).val( grouped_id );
					})
				} else {
					$( ui.item ).children( '.it-exchange-content-access-group' ).each( function( index, child ) {
						$( child ).val( '' );
					})
				}
		    }
		});
	}
		
	$( '.it-exchange-membership-content-access-add-new-group' ).live( 'click', function( event ) {
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
	
	$( '.group-layout' ).live( 'click', function( event ) {
		var parent = $( this ).parent();
		type = $( this ).data( 'type' );
		$( 'span', parent ).removeClass( 'active-group-layout' );
		$( this ).addClass( 'active-group-layout' );
		$( 'input.group-layout-input', parent ).val( type );
	});
	
	$( '.it-exchange-membership-addon-group-action-wrapper' ).live({
		mouseenter: function() {
			$( this ).children('.it-exchange-membership-addon-group-actions').show();
		}, 
		mouseleave: function() {
			$( this ).children('.it-exchange-membership-addon-group-actions').hide();
		}
	});
	
});