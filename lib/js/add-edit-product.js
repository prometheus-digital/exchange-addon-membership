jQuery(document).ready(function($) {
	$( '.it-exchange-membership-content-access-add-new-rule' ).live( 'click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			'action':  'it-exchange-membership-addon-show-empty-content-access-rule',
		}
		$.post( ajaxurl, data, function( response ) {
			console.log( response );
			$( '.it-exchange-membership-addon-new-content-access-rules' ).append( response );
		});
	});
	
	$( '.it-exchange-membership-content-type-selections' ).live('change', function() {
		var parent = $( this ).parent();
		var data = {
			'action':  'it-exchange-membership-addon-content-type-terms',
			'type':    $( 'option:selected', this ).attr( 'data-type' ),
			'value':   $( 'option:selected', this ).val(),
		}
		if ( data['type'] ) { //Only call AJAX if we have a content type
			$.post( ajaxurl, data, function( response ) {
				console.log( response );
				$( '.it-exchange-membership-content-type-terms', parent ).removeClass( 'hidden' );
				$( '.it-exchange-membership-content-type-terms', parent ).html( response )
				$( '.it-exchange-membership-content-rule-submit', parent ).removeClass( 'hidden' );
			});
		} else {
			$( '.it-exchange-membership-content-type-terms', parent ).addClass( 'hidden' );
			$( '.it-exchange-membership-content-rule-submit', parent ).addClass( 'hidden' );
		}
	});

	$( '.it-exchange-membership-content-rule-submit' ).live('click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = $( ':input', parent ).serializeArray();
		var action = { name: 'action', value: 'it-exchange-membership-addon-add-content-rule' };
		data.push( action );
		var count =  { name: 'count', value: it_exchange_membership_addon_content_access_interation };
		data.push( count );
		it_exchange_membership_addon_content_access_interation++;
		$.post( ajaxurl, data, function( response ) {
			console.log( response );
			$( parent ).html('')
			$( '.it-exchange-membership-addon-content-access-rules' ).append( response );
		});
	});
	
	$( '.it-exchange-membership-addon-remove-content-access-rule' ).live('click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		parent.remove();
	});
});