/**
 * WooCommerce AJAX Review Submission
 *
 * Перехватывает форму отзыва и отправляет через admin-ajax.php.
 * Локализованный объект: cwReview { ajaxUrl, nonce, errorText }
 */
( function () {
	'use strict';

	const form = document.querySelector( '#review_form .comment-form' );
	if ( ! form ) return;

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		const submitBtn = form.querySelector( '[type="submit"]' );
		submitBtn.disabled = true;
		submitBtn.classList.add( 'loading' );

		const data = new FormData();
		data.append( 'action',          'cw_submit_review' );
		data.append( 'nonce',           cwReview.nonce );
		data.append( 'comment_post_ID', form.querySelector( '#comment_post_ID' )?.value || '' );
		data.append( 'comment',         form.querySelector( '#comment' )?.value || '' );
		data.append( 'rating',          form.querySelector( '#rating' )?.value || '' );
		data.append( 'author',          form.querySelector( '#author' )?.value || '' );
		data.append( 'email',           form.querySelector( '#email' )?.value || '' );

		fetch( cwReview.ajaxUrl, { method: 'POST', body: data } )
			.then( function ( res ) { return res.json(); } )
			.then( function ( json ) {
				submitBtn.disabled = false;
				submitBtn.classList.remove( 'loading' );

				if ( json.success ) {
					showMessage( form, json.data.message, 'success' );

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

					// Сбросить WC stars UI
					var stars = form.querySelector( '.stars' );
					if ( stars ) {
						stars.classList.remove( 'selected' );
						stars.querySelectorAll( 'a' ).forEach( function ( a ) {
							a.classList.remove( 'active' );
						} );
					}

				} else {
					showMessage( form, ( json.data && json.data.message ) ? json.data.message : cwReview.errorText, 'danger' );
				}
			} )
			.catch( function () {
				submitBtn.disabled = false;
				submitBtn.classList.remove( 'loading' );
				showMessage( form, cwReview.errorText, 'danger' );
			} );
	} );

	function showMessage( form, text, type ) {
		var old = form.querySelector( '.cw-review-notice' );
		if ( old ) old.remove();

		var div = document.createElement( 'div' );
		div.className = 'alert alert-' + type + ' cw-review-notice mt-3';
		div.textContent = text;

		var submitWrap = form.querySelector( '.form-submit' );
		if ( submitWrap ) {
			submitWrap.insertAdjacentElement( 'beforebegin', div );
		} else {
			form.appendChild( div );
		}
	}
} )();
