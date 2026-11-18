/**
 * Order rules (admin): show the custom rule fields only when the product mode is "custom".
 */
( function ( $ ) {
	'use strict';

	function toggle() {
		var mode = $( '#_cw_qty_mode' ).val();
		$( '.codeweber-order-rules-custom' ).toggle( 'custom' === mode );
	}

	$( function () {
		toggle();
		$( document ).on( 'change', '#_cw_qty_mode', toggle );
	} );
}( jQuery ) );
