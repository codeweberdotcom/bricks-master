/**
 * Desktop Mode widget — Recent Form Submissions.
 *
 * Регистрирует mount-колбэк в глобале window.desktopModeWidgets
 * по контракту desktop-mode v0.8.9:
 *   window.desktopModeWidgets[ id ] = (container, ctx) => teardown
 *
 * ВАЖНО: виджет рендерится в wp-admin (desktop-shell), где Bootstrap-CSS
 * темы НЕ подключён. Поэтому стили — самодостаточные (префикс cwdm-),
 * завязаны на CSS-переменные desktop-mode (--desktop-mode-fg и т.д.)
 * с фолбэками, чтобы подхватывать светлую/тёмную тему shell.
 *
 * Данные приходят из codeweberDmRecentForms (wp_localize_script).
 */
( function () {
	'use strict';

	var WIDGET_ID = 'codeweber/recent-forms';
	var STYLE_ID = 'cwdm-recent-forms-style';
	var cfg = window.codeweberDmRecentForms || {};
	var i18n = cfg.i18n || {};

	// Цвета бейджей по типу формы (hex из CODEWEBER_FORMS.md → admin badge colours).
	var TYPE_COLORS = {
		form: '#607d8b',
		newsletter: '#00897b',
		testimonial: '#8e24aa',
		callback: '#e53935',
		resume: '#1e88e5',
		faq: '#f4511e',
		'event-registration': '#43a047',
		questionnaire: '#00897b',
		brief: '#6a1b9a'
	};

	var CSS =
		'.cwdm-rf{font:13px/1.4 var(--desktop-mode-font,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif);color:var(--desktop-mode-fg,#1e1e1e)}' +
		'.cwdm-rf-list{display:flex;flex-direction:column}' +
		'.cwdm-rf-item{display:block;padding:8px 10px;text-decoration:none;color:inherit;border-bottom:1px solid var(--desktop-mode-border,rgba(0,0,0,.08));transition:background .12s ease}' +
		'.cwdm-rf-item:hover{background:var(--desktop-mode-hover,rgba(0,0,0,.04))}' +
		'.cwdm-rf-head{display:flex;align-items:center;justify-content:space-between;gap:8px}' +
		'.cwdm-rf-name{font-weight:600;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}' +
		'.cwdm-rf-badge{flex:0 0 auto;font-size:11px;line-height:1;padding:3px 7px;border-radius:4px;color:#fff;white-space:nowrap}' +
		'.cwdm-rf-preview{margin-top:3px;font-size:12px;color:var(--desktop-mode-fg-muted,#6b6b6b);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}' +
		'.cwdm-rf-date{margin-top:3px;font-size:11px;color:var(--desktop-mode-fg-muted,#9a9a9a)}' +
		'.cwdm-rf-footer{display:block;padding:9px;text-align:center;font-size:12px;font-weight:500;text-decoration:none;color:var(--desktop-mode-link,var(--desktop-mode-accent,#2271b1))}' +
		'.cwdm-rf-footer:hover{text-decoration:underline}' +
		'.cwdm-rf-msg{padding:14px 10px;font-size:12px;color:var(--desktop-mode-fg-muted,#6b6b6b);text-align:center}';

	function injectStyleOnce() {
		if ( document.getElementById( STYLE_ID ) ) {
			return;
		}
		var style = document.createElement( 'style' );
		style.id = STYLE_ID;
		style.textContent = CSS;
		document.head.appendChild( style );
	}

	window.desktopModeWidgets = window.desktopModeWidgets || {};

	window.desktopModeWidgets[ WIDGET_ID ] = function ( container, ctx ) {
		var aborted = false;
		var controller = ( typeof AbortController !== 'undefined' )
			? new AbortController()
			: null;

		injectStyleOnce();

		var root = document.createElement( 'div' );
		root.className = 'cwdm-rf';
		container.appendChild( root );

		function setMessage( text ) {
			root.innerHTML = '';
			var msg = document.createElement( 'div' );
			msg.className = 'cwdm-rf-msg';
			msg.textContent = text;
			root.appendChild( msg );
		}

		function buildBadge( type ) {
			var badge = document.createElement( 'span' );
			badge.className = 'cwdm-rf-badge';
			badge.style.background = TYPE_COLORS[ type ] || '#607d8b';
			badge.textContent = type;
			return badge;
		}

		function buildItem( item ) {
			var link = document.createElement( 'a' );
			link.href = item.viewUrl;
			link.className = 'cwdm-rf-item';

			var head = document.createElement( 'div' );
			head.className = 'cwdm-rf-head';

			var name = document.createElement( 'span' );
			name.className = 'cwdm-rf-name';
			name.textContent = item.formName;
			head.appendChild( name );
			head.appendChild( buildBadge( item.formType ) );
			link.appendChild( head );

			if ( item.preview ) {
				var preview = document.createElement( 'div' );
				preview.className = 'cwdm-rf-preview';
				preview.textContent = item.preview;
				link.appendChild( preview );
			}

			var date = document.createElement( 'div' );
			date.className = 'cwdm-rf-date';
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
			list.className = 'cwdm-rf-list';
			items.forEach( function ( item ) {
				list.appendChild( buildItem( item ) );
			} );
			root.appendChild( list );

			if ( cfg.adminUrl ) {
				var footer = document.createElement( 'a' );
				footer.href = cfg.adminUrl;
				footer.className = 'cwdm-rf-footer';
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
