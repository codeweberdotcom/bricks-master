/**
 * Desktop Mode widget — Recent Form Submissions.
 *
 * Регистрирует mount-колбэк в глобале window.desktopModeWidgets
 * по контракту desktop-mode v0.8.9:
 *   window.desktopModeWidgets[ id ] = (container, ctx) => teardown
 *
 * Рендер — только Bootstrap-классы темы (list-group, badge, text-*).
 * Данные приходят из codeweberDmRecentForms (wp_localize_script).
 */
( function () {
	'use strict';

	var WIDGET_ID = 'codeweber/recent-forms';
	var cfg = window.codeweberDmRecentForms || {};
	var i18n = cfg.i18n || {};

	// Цвет бейджа по типу формы (значения form_type из CodeweberFormsDatabase).
	var TYPE_COLORS = {
		form: 'secondary',
		newsletter: 'info',
		testimonial: 'primary',
		callback: 'danger',
		resume: 'primary',
		faq: 'warning',
		'event-registration': 'success',
		questionnaire: 'info',
		brief: 'dark'
	};

	window.desktopModeWidgets = window.desktopModeWidgets || {};

	window.desktopModeWidgets[ WIDGET_ID ] = function ( container, ctx ) {
		var aborted = false;
		var controller = ( typeof AbortController !== 'undefined' )
			? new AbortController()
			: null;

		var root = document.createElement( 'div' );
		root.className = 'p-2';
		container.appendChild( root );

		function setMessage( text ) {
			root.innerHTML = '';
			var msg = document.createElement( 'div' );
			msg.className = 'text-muted small p-2';
			msg.textContent = text;
			root.appendChild( msg );
		}

		function buildBadge( type ) {
			var color = TYPE_COLORS[ type ] || 'secondary';
			var badge = document.createElement( 'span' );
			badge.className = 'badge bg-' + color + ' ms-2 flex-shrink-0';
			badge.textContent = type;
			return badge;
		}

		function buildItem( item ) {
			var link = document.createElement( 'a' );
			link.href = item.viewUrl;
			link.className = 'list-group-item list-group-item-action px-2 py-2';

			var top = document.createElement( 'div' );
			top.className = 'd-flex align-items-center justify-content-between';

			var name = document.createElement( 'span' );
			name.className = 'fw-semibold text-truncate';
			name.textContent = item.formName;
			top.appendChild( name );
			top.appendChild( buildBadge( item.formType ) );
			link.appendChild( top );

			if ( item.preview ) {
				var preview = document.createElement( 'div' );
				preview.className = 'small text-muted text-truncate';
				preview.textContent = item.preview;
				link.appendChild( preview );
			}

			var date = document.createElement( 'div' );
			date.className = 'small text-muted';
			date.textContent = item.date;
			link.appendChild( date );

			return link;
		}

		function render( items ) {
			root.innerHTML = '';

			if ( ! items || ! items.length ) {
				setMessage( i18n.empty || 'No submissions yet' );
				return;
			}

			var list = document.createElement( 'div' );
			list.className = 'list-group list-group-flush';
			items.forEach( function ( item ) {
				list.appendChild( buildItem( item ) );
			} );
			root.appendChild( list );

			if ( cfg.adminUrl ) {
				var footer = document.createElement( 'a' );
				footer.href = cfg.adminUrl;
				footer.className = 'd-block text-center small mt-2';
				footer.textContent = i18n.viewAll || 'All submissions';
				root.appendChild( footer );
			}
		}

		setMessage( i18n.loading || 'Loading…' );

		if ( ! cfg.root ) {
			setMessage( i18n.error || 'Failed to load submissions' );
			return function teardown() {};
		}

		fetch( cfg.root, {
			headers: { 'X-WP-Nonce': cfg.nonce || '' },
			credentials: 'same-origin',
			signal: controller ? controller.signal : undefined
		} )
			.then( function ( response ) {
				return response.ok ? response.json() : Promise.reject( response );
			} )
			.then( function ( items ) {
				if ( aborted ) {
					return;
				}
				render( items );
			} )
			.catch( function () {
				if ( aborted ) {
					return;
				}
				setMessage( i18n.error || 'Failed to load submissions' );
			} );

		return function teardown() {
			aborted = true;
			if ( controller ) {
				try {
					controller.abort();
				} catch ( e ) {}
			}
			if ( root.parentNode ) {
				root.parentNode.removeChild( root );
			}
		};
	};
} )();
