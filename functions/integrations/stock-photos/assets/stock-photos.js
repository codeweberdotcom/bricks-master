/**
 * Stock Photos — admin UI.
 *
 * One reusable search component (SearchUI) mounted in three places:
 *   • a "Free Photos" router tab inside the wp.media modal,
 *   • an overlay opened from the Media Library button,
 *   • inline on the dedicated admin page (#cw-stock-app).
 *
 * API keys never touch this file — search & import go through admin-ajax.
 */
( function ( $ ) {
	'use strict';

	var CFG       = window.cwStockPhotos || {};
	var PROVIDERS = CFG.providers || [];
	var I18N      = CFG.i18n || {};

	if ( ! CFG.ajaxUrl || ! PROVIDERS.length ) {
		return;
	}

	// ─── AJAX helpers ─────────────────────────────────────────────────────────
	function ajaxSearch( params ) {
		return $.post(
			CFG.ajaxUrl,
			$.extend( { action: 'cw_stock_photos_search', nonce: CFG.nonce }, params )
		);
	}

	// Thumbnails are streamed through the server — some networks reset direct
	// browser connections to the provider CDNs.
	function thumbUrl( u ) {
		return CFG.ajaxUrl +
			'?action=cw_stock_photos_thumb' +
			'&nonce=' + encodeURIComponent( CFG.nonce ) +
			'&url=' + encodeURIComponent( u );
	}

	function ajaxImport( item ) {
		return $.post( CFG.ajaxUrl, {
			action:            'cw_stock_photos_import',
			nonce:             CFG.nonce,
			provider:          item.provider,
			url:               item.full,
			alt:               item.alt,
			author:            item.author,
			author_url:        item.author_url,
			source_url:        item.source_url,
			download_location: item.download_location || '',
		} );
	}

	// ─── SearchUI ─────────────────────────────────────────────────────────────
	// opts: { mode: 'insert' | 'standalone', onImport: fn(attachment, item) }
	function SearchUI( opts ) {
		this.opts     = opts || {};
		this.provider = PROVIDERS[ 0 ].slug;
		this.query    = '';
		this.page     = 1;
		this.hasMore  = false;
		this.loading  = false;
		this.el       = this._build();
		this._bind();
	}

	SearchUI.prototype._build = function () {
		var $root = $( '<div class="cw-stock-ui"></div>' );

		// Provider tabs (only when more than one is active).
		var $tabs = $( '<div class="cw-stock-providers"></div>' );
		if ( PROVIDERS.length > 1 ) {
			PROVIDERS.forEach( function ( p, i ) {
				$( '<button type="button" class="button cw-stock-prov' + ( 0 === i ? ' is-active' : '' ) + '"></button>' )
					.text( p.label )
					.attr( 'data-prov', p.slug )
					.appendTo( $tabs );
			} );
		}

		var $form = $(
			'<div class="cw-stock-searchbar">' +
				'<input type="search" class="cw-stock-input" placeholder="' + esc( I18N.searchPh ) + '">' +
				'<button type="button" class="button button-primary cw-stock-go">' + esc( I18N.search ) + '</button>' +
			'</div>'
		);

		var $status = $( '<div class="cw-stock-status"></div>' ).text( I18N.startHint || '' );
		var $grid   = $( '<div class="cw-stock-grid"></div>' );
		var $more   = $( '<div class="cw-stock-more"><button type="button" class="button cw-stock-loadmore">' + esc( I18N.loadMore ) + '</button></div>' ).hide();

		$root.append( $tabs, $form, $status, $grid, $more );

		this.$root   = $root;
		this.$input  = $form.find( '.cw-stock-input' );
		this.$status = $status;
		this.$grid   = $grid;
		this.$more   = $more;

		return $root;
	};

	SearchUI.prototype._bind = function () {
		var self = this;

		this.$root.on( 'click', '.cw-stock-prov', function () {
			self.$root.find( '.cw-stock-prov' ).removeClass( 'is-active' );
			$( this ).addClass( 'is-active' );
			self.provider = $( this ).data( 'prov' );
			if ( self.query ) {
				self.search( true );
			}
		} );

		this.$root.on( 'click', '.cw-stock-go', function () {
			self.query = $.trim( self.$input.val() );
			if ( self.query ) {
				self.search( true );
			}
		} );

		this.$input.on( 'keydown', function ( e ) {
			if ( 13 === e.which ) {
				e.preventDefault();
				self.$root.find( '.cw-stock-go' ).trigger( 'click' );
			}
		} );

		this.$root.on( 'click', '.cw-stock-loadmore', function () {
			self.search( false );
		} );

		this.$root.on( 'click', '.cw-stock-import', function () {
			var $btn = $( this );
			var item = $btn.closest( '.cw-stock-item' ).data( 'item' );
			self._import( item, $btn );
		} );
	};

	SearchUI.prototype.search = function ( reset ) {
		var self = this;
		if ( this.loading ) {
			return;
		}
		if ( reset ) {
			this.page = 1;
			this.$grid.empty();
		}
		this.loading = true;
		this.$status.text( '…' );
		this.$more.hide();

		ajaxSearch( {
			provider: this.provider,
			query:    this.query,
			page:     this.page,
			per_page: CFG.perPage || 24,
		} )
			.done( function ( res ) {
				if ( ! res || ! res.success ) {
					self.$status.text( ( res && res.data && res.data.message ) ? res.data.message : I18N.error );
					return;
				}
				var data = res.data;
				if ( ! data.items.length && 1 === self.page ) {
					self.$status.text( I18N.noResults );
					return;
				}
				self.$status.text( '' );
				self._render( data.items );
				self.hasMore = !! data.has_more;
				self.$more.toggle( self.hasMore );
				self.page += 1;
			} )
			.fail( function () {
				self.$status.text( I18N.error );
			} )
			.always( function () {
				self.loading = false;
			} );
	};

	SearchUI.prototype._render = function ( items ) {
		var self = this;
		items.forEach( function ( item ) {
			var $item = $( '<div class="cw-stock-item"></div>' ).data( 'item', item );
			var $img  = $( '<img loading="lazy" alt="">' ).attr( 'src', thumbUrl( item.thumb ) );
			var credit = item.author
				? ( I18N.photoBy + ' ' + item.author )
				: '';

			var $ov = $( '<div class="cw-stock-ov"></div>' );
			$( '<button type="button" class="button button-primary cw-stock-import"></button>' )
				.text( I18N.import )
				.appendTo( $ov );
			if ( credit ) {
				var $credit = item.author_url
					? $( '<a class="cw-stock-credit" target="_blank" rel="noopener"></a>' ).attr( 'href', item.author_url )
					: $( '<span class="cw-stock-credit"></span>' );
				$credit.text( credit ).appendTo( $ov );
			}

			$item.append( $img, $ov );
			self.$grid.append( $item );
		} );
	};

	SearchUI.prototype._import = function ( item, $btn ) {
		var self = this;
		if ( $btn.prop( 'disabled' ) ) {
			return;
		}
		$btn.prop( 'disabled', true ).text( I18N.importing );

		ajaxImport( item )
			.done( function ( res ) {
				if ( res && res.success ) {
					$btn.text( I18N.imported ).addClass( 'cw-stock-done' );
					$btn.closest( '.cw-stock-item' ).addClass( 'is-imported' );
					if ( typeof self.opts.onImport === 'function' ) {
						self.opts.onImport( res.data, item );
					}
				} else {
					$btn.prop( 'disabled', false ).text( I18N.import );
					window.alert( ( res && res.data && res.data.message ) ? res.data.message : I18N.error );
				}
			} )
			.fail( function () {
				$btn.prop( 'disabled', false ).text( I18N.import );
				window.alert( I18N.error );
			} );
	};

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	// ─── Overlay (Media Library button + standalone trigger) ──────────────────
	function openOverlay() {
		var $overlay = $(
			'<div class="cw-stock-overlay">' +
				'<div class="cw-stock-dialog">' +
					'<button type="button" class="cw-stock-close" aria-label="Close">&times;</button>' +
					'<h2 class="cw-stock-title">' + esc( I18N.tabTitle ) + '</h2>' +
					'<div class="cw-stock-mount"></div>' +
				'</div>' +
			'</div>'
		);

		var ui = new SearchUI( {
			mode: 'standalone',
			onImport: function ( data ) {
				// Optional: offer a link to the freshly imported file.
				if ( data && data.editLink ) {
					/* noop — item is marked imported in the grid */
				}
			},
		} );
		$overlay.find( '.cw-stock-mount' ).append( ui.el );

		$overlay.on( 'click', '.cw-stock-close', function () {
			$overlay.remove();
		} );
		$overlay.on( 'click', function ( e ) {
			if ( e.target === this ) {
				$overlay.remove();
			}
		} );
		$( document ).on( 'keydown.cwstock', function ( e ) {
			if ( 27 === e.which ) {
				$overlay.remove();
				$( document ).off( 'keydown.cwstock' );
			}
		} );

		$( 'body' ).append( $overlay );
		ui.$input.trigger( 'focus' );
	}

	$( document ).on( 'click', '.cw-stock-open', function ( e ) {
		e.preventDefault();
		openOverlay();
	} );

	// ─── Mount: dedicated admin page ──────────────────────────────────────────
	$( function () {
		var $app = $( '#cw-stock-app' );
		if ( $app.length ) {
			var ui = new SearchUI( { mode: 'standalone' } );
			$app.append( ui.el );
		}
	} );

	// ─── Mount: Media Library toolbar button ──────────────────────────────────
	$( function () {
		var tpl = document.getElementById( 'cw-stock-photos-library-trigger' );
		if ( ! tpl ) {
			return;
		}
		var $btn = $( tpl.innerHTML.trim() );
		// Classic list view: after the "Add New" action. Grid view: after H1.
		var $action = $( '.wrap .page-title-action' ).first();
		if ( $action.length ) {
			$btn.insertAfter( $action );
		} else {
			$( '.wrap h1' ).first().append( $btn );
		}
	} );

	// ─── Mount: wp.media modal router tab ─────────────────────────────────────
	if ( window.wp && wp.media && wp.media.view && wp.media.view.MediaFrame ) {
		registerFrameTab();
	}

	function registerFrameTab() {
		var ContentView = wp.media.View.extend( {
			className: 'cw-stock-frame',
			initialize: function () {
				var self = this;
				this.ui = new SearchUI( {
					mode: 'insert',
					onImport: function ( data ) {
						self.selectAttachment( data.id );
					},
				} );
				this.$el.append( this.ui.el );
			},
			selectAttachment: function ( id ) {
				var ctrl  = this.controller;
				var model = wp.media.model.Attachment.get( id );
				model.fetch().done( function () {
					var state = ctrl.state();
					var lib   = state.get( 'library' );
					if ( lib && lib.add ) {
						lib.add( model );
					}
					var sel = state.get( 'selection' );
					if ( sel ) {
						sel.reset( [ model ] );
					}
					// Jump to the library so the selection + Insert button show.
					ctrl.content.mode( 'browse' );
				} );
			},
		} );

		function extend( FrameCtor ) {
			if ( ! FrameCtor ) {
				return FrameCtor;
			}
			return FrameCtor.extend( {
				browseRouter: function ( routerView ) {
					FrameCtor.prototype.browseRouter.apply( this, arguments );
					routerView.set( {
						cwstock: {
							text:     I18N.tabTitle,
							priority: 60,
						},
					} );
				},
				bindHandlers: function () {
					FrameCtor.prototype.bindHandlers.apply( this, arguments );
					this.on( 'content:render:cwstock', this.cwStockRender, this );
				},
				cwStockRender: function () {
					this.content.set( new ContentView( { controller: this } ) );
				},
			} );
		}

		wp.media.view.MediaFrame.Post   = extend( wp.media.view.MediaFrame.Post );
		wp.media.view.MediaFrame.Select = extend( wp.media.view.MediaFrame.Select );
	}
} )( jQuery );
