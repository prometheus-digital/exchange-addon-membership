(function( $ ) {
	$( '.it-exchange-content-group' ).on( 'click', '.it-exchange-group-content-label', function( event ) {
		$( this ).parent().toggleClass( 'open' ).find( 'ul' ).toggleClass( 'it-exchange-hidden' );;
	});
})( jQuery );