/**
 * Order rules (front end).
 *
 * The parent theme's +/- buttons always change the quantity by 1. For quantity inputs that carry a step
 * (data-step > 1) this script takes over: the buttons move by the step, and a typed value is rounded up to the
 * nearest allowed quantity. Inputs without a step are left to the parent theme.
 *
 * The listener is registered on window in the capture phase so it runs before the parent theme's handler.
 */
( function () {
	'use strict';

	function toInt( value, fallback ) {
		var number = parseInt( value, 10 );
		return isNaN( number ) ? fallback : number;
	}

	function limits( input ) {
		return {
			min: Math.max( 1, toInt( input.getAttribute( 'data-min' ), 1 ) ),
			step: Math.max( 1, toInt( input.getAttribute( 'data-step' ), 1 ) ),
			max: Math.max( 0, toInt( input.getAttribute( 'data-max' ), 0 ) )
		};
	}

	/** Nearest allowed quantity that is not lower than the value. */
	function snapUp( value, rule ) {
		var result = Math.max( value, rule.min );
		if ( rule.step > 1 ) {
			result = Math.ceil( result / rule.step ) * rule.step;
		}
		if ( rule.max > 0 && result > rule.max ) {
			result = rule.max;
		}
		return result;
	}

	function setValue( input, value ) {
		if ( String( value ) === input.value ) {
			return;
		}
		input.value = String( value );
		input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	window.addEventListener( 'click', function ( event ) {
		var button = event.target.closest ? event.target.closest( '[data-qty-inc],[data-qty-dec]' ) : null;
		if ( ! button ) {
			return;
		}
		var wrap = button.closest( '.input-group, .cw-qty-v' );
		var input = wrap ? wrap.querySelector( 'input' ) : null;
		if ( ! input || toInt( input.getAttribute( 'data-step' ), 1 ) <= 1 ) {
			return;
		}

		event.preventDefault();
		event.stopImmediatePropagation();

		var rule = limits( input );
		var current = toInt( input.value, rule.min );
		var next;

		if ( button.hasAttribute( 'data-qty-inc' ) ) {
			next = Math.floor( current / rule.step ) * rule.step + rule.step;
			if ( rule.max > 0 ) {
				next = Math.min( next, rule.max );
			}
		} else {
			next = Math.ceil( current / rule.step ) * rule.step - rule.step;
		}
		setValue( input, Math.max( next, rule.min ) );
	}, true );

	document.addEventListener( 'change', function ( event ) {
		var input = event.target;
		if ( ! input || ! input.matches || ! input.matches( 'input[data-step]' ) ) {
			return;
		}
		var rule = limits( input );
		if ( rule.step <= 1 ) {
			return;
		}
		setValue( input, snapUp( toInt( input.value, rule.min ), rule ) );
	} );
}() );
