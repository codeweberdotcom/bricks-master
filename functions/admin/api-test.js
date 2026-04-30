( function ( $ ) {
	'use strict';

	$( document ).on( 'click', '.cw-api-test-btn', function ( e ) {
		e.preventDefault();

		var $btn     = $( this );
		var $result  = $btn.next( '.cw-api-test-result' );
		var action   = $btn.data( 'action' );
		var fieldId  = $btn.data( 'field' );
		var field2Id = $btn.data( 'field2' );
		var value    = $( '#' + fieldId ).val();
		var value2   = field2Id ? $( '#' + field2Id ).val() : '';

		if ( ! value ) {
			$result.html( '<span class="cw-api-test-error">&#x26A0; Введите ключ в поле выше</span>' );
			return;
		}

		$btn.prop( 'disabled', true ).text( codeweberApiTest.labels.testing );
		$result.html( '' );

		var data = {
			action: action,
			nonce:  codeweberApiTest.nonce,
		};

		if ( 'codeweber_api_test_dadata' === action ) {
			data.token = value;
		} else if ( 'codeweber_api_test_yandex' === action ) {
			data.key = value;
		} else if ( 'codeweber_api_test_smsru' === action ) {
			data.api_id = value;
		} else if ( 'codeweber_api_test_telegram' === action ) {
			data.token   = value;
			data.chat_id = value2;
		}

		$.post( codeweberApiTest.ajaxUrl, data )
			.done( function ( response ) {
				if ( response.success ) {
					$result.html( '<span class="cw-api-test-success">&#x2714; ' + response.data.message + '</span>' );
				} else {
					$result.html( '<span class="cw-api-test-error">&#x2718; ' + response.data.message + '</span>' );
				}
			} )
			.fail( function () {
				$result.html( '<span class="cw-api-test-error">&#x2718; Ошибка запроса</span>' );
			} )
			.always( function () {
				$btn.prop( 'disabled', false ).text( codeweberApiTest.labels.test );
			} );
	} );
} )( jQuery );
