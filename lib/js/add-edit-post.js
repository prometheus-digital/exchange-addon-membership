jQuery(document).ready(function($) {
	$( '.it-exchange-add-new-restriction' ).live( 'click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			'action': 'it-exchange-membership-addon-add-content-access-rule-to-post',
		}
		$.post( ajaxurl, data, function( response ) {
			console.log( response );
			$( '.it-exchange-membership-new-restrictions' ).append( response );
		});
	});
	
	$( 'input.it-exchange-restriction-exceptions' ).live( 'click', function( event ) {
		var parent = $( this ).parent();
		var data = {
			'action':        'it-exchange-membership-addon-modify-restrictions-exemptions',
			'post_id':       $( 'input[name=post_ID]' ).val(),
			'membership_id': $( 'input[name=it_exchange_membership_id]', $( this ).closest('.it-exchange-membership-restriction-group' ) ).val(),
			'exemption':     $( this ).val(),
			'checked':       $( this ).is( ':checked' ),
		}
		$.post( ajaxurl, data );
	});
	
	$( '.it-exchange-membership-remove-new-rule' ).live( 'click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		parent.remove();
	});
	
	$( '.it-exchange-membership-remove-rule' ).live( 'click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			'action':        'it-exchange-membership-addon-remove-rule-from-post',
			'post_id':       $( 'input[name=post_ID]' ).val(),
			'membership_id': $( 'input[name=it_exchange_membership_id]', $( this ).closest('.it-exchange-membership-restriction-group' ) ).val(),
		}
		$.post( ajaxurl, data, function( response ) {
			console.log( response );
			if ( 0 < response.length ) {
				$(".it-exchange-membership-restrictions").replaceWith(function() {
					return $( response ).fadeOut( 'slow' ).fadeIn( 'slow' );
				});
			}
		});
	});
	
	$( '.it-exchange-add-new-restriction-ok-button' ).live( 'click', function( event ) {
		event.preventDefault();
		var parent = $( this ).parent();
		var data = {
			'action':        'it-exchange-membership-addon-add-new-rule-to-post',
			'post_id':       $( 'input[name=post_ID]' ).val(),
			'membership_id': $( 'select[name=it_exchange_membership_id] option:selected', parent ).val(),
		}
		$.post( ajaxurl, data, function( response ) {
			console.log( response );
			if ( 0 < response.length ) {
				$(".it-exchange-membership-restrictions").replaceWith(function() {
					return $( response ).fadeOut( 'slow' ).fadeIn( 'slow' );
				});
				$( parent ).remove();
			}
		});
	});
});