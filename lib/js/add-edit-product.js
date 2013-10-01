jQuery(document).ready(function($) {
	$( '.it-exchange-membership-content-access-add-new-rule' ).live( 'click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			'action': 'it-exchange-membership-addon-add-content-access-rule',
			'count':  it_exchange_membership_addon_content_access_interation,
		}
		it_exchange_membership_addon_content_access_interation++;
		$.post( ajaxurl, data, function( response ) {
			console.log( response );
			$( '.it-exchange-membership-addon-content-access-rules' ).append( response );
		});
	});
	
	$( '.it-exchange-membership-content-type-selections' ).live('change', function() {
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
	
	$( '.it-exchange-membership-addon-remove-content-access-rule' ).live('click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		parent.remove();
	});
	
	$( ".it-exchange-membership-addon-content-access-rules" ).sortable({
		placeholder: 'it-exchange-membership-addon-sorting-placeholder',
	});
});