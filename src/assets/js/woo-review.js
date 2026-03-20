/**
 * WooCommerce AJAX Review Submission
 *
 * Перехватывает форму отзыва в capture-фазе (раньше WC jQuery),
 * блокирует нативный alert WooCommerce и отправляет через admin-ajax.php.
 * Уведомления — через CWNotify (тема).
 *
 * Локализованный объект: cwReview { ajaxUrl, nonce, i18n: { ratingRequired, error } }
 */
( function () {
	'use strict';

	var form = document.querySelector( '#review_form .comment-form' );
	if ( ! form ) return;

	// capture: true — наш обработчик срабатывает ДО jQuery-обработчика WooCommerce,
	// который показывает нативный alert() при незаполненном рейтинге.
	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		e.stopPropagation();

		var ratingEl   = form.querySelector( '#rating' );
		var ratingVal  = ratingEl ? ratingEl.value : '';
		var isRequired = ( typeof wc_single_product_params !== 'undefined' )
			? wc_single_product_params.review_rating_required === 'yes'
			: true;

		// Валидация рейтинга — показываем тост вместо нативного alert
		if ( isRequired && ! ratingVal ) {
			notify( cwReview.i18n.ratingRequired, 'warning' );
			return;
		}

		var submitBtn = form.querySelector( '[type="submit"]' );
		submitBtn.disabled = true;
		submitBtn.classList.add( 'loading' );

		var data = new FormData();
		data.append( 'action',          'cw_submit_review' );
		data.append( 'nonce',           cwReview.nonce );
		data.append( 'comment_post_ID', form.querySelector( '#comment_post_ID' ) ? form.querySelector( '#comment_post_ID' ).value : '' );
		data.append( 'comment',         form.querySelector( '#comment' )         ? form.querySelector( '#comment' ).value         : '' );
		data.append( 'rating',          ratingVal );
		data.append( 'author',          form.querySelector( '#author' )          ? form.querySelector( '#author' ).value          : '' );
		data.append( 'email',           form.querySelector( '#email' )           ? form.querySelector( '#email' ).value           : '' );

		fetch( cwReview.ajaxUrl, { method: 'POST', body: data } )
			.then( function ( res ) { return res.json(); } )
			.then( function ( json ) {
				submitBtn.disabled = false;
				submitBtn.classList.remove( 'loading' );

				if ( json.success ) {
					notify( json.data.message, 'success' );

					if ( json.data.status === 'approved' && json.data.html ) {
						var commentList = document.querySelector( '.commentlist' );
						if ( commentList ) {
							commentList.insertAdjacentHTML( 'afterbegin', json.data.html );
						} else {
							var listEl = document.createElement( 'ol' );
							listEl.className = 'commentlist';
							listEl.innerHTML = json.data.html;
							var noReviews = document.querySelector( '.woocommerce-noreviews' );
							if ( noReviews ) {
								noReviews.replaceWith( listEl );
							} else {
								var commentsDiv = document.querySelector( '#comments' );
								if ( commentsDiv ) commentsDiv.prepend( listEl );
							}
						}
					}

					form.reset();

					// Сброс WC stars UI
					var stars = form.querySelector( '.stars' );
					if ( stars ) {
						stars.classList.remove( 'selected' );
						stars.querySelectorAll( 'a' ).forEach( function ( a ) {
							a.classList.remove( 'active' );
						} );
					}

				} else {
					notify( ( json.data && json.data.message ) ? json.data.message : cwReview.i18n.error, 'danger' );
				}
			} )
			.catch( function () {
				submitBtn.disabled = false;
				submitBtn.classList.remove( 'loading' );
				notify( cwReview.i18n.error, 'danger' );
			} );

	}, true ); // true = capture phase

	function notify( message, type ) {
		if ( typeof CWNotify !== 'undefined' ) {
			CWNotify.show( message, { type: type, event: 'review' } );
		}
	}

} )();
