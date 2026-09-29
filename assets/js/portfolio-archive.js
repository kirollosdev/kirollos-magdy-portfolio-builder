/**
 * Portfolio Archive + Logo Wall filtering.
 *
 * Runs entirely in the browser against the term ids printed on each card, so
 * switching a filter is instant. Within one taxonomy the checked terms are
 * OR-ed (a project matching any ticked industry stays), and the two taxonomies
 * are AND-ed (it must also match a ticked service). That is what people expect
 * from a faceted sidebar.
 */
( function () {
	'use strict';

	var ROOT = '.kmpb-pa';

	function ids( card, key ) {
		var raw = card.getAttribute( 'data-' + key ) || '';
		return raw.split( ' ' ).filter( Boolean );
	}

	function matches( card, key, wanted ) {
		if ( ! wanted.length ) {
			return true;
		}

		var have = ids( card, key );

		for ( var i = 0; i < wanted.length; i++ ) {
			if ( have.indexOf( wanted[ i ] ) !== -1 ) {
				return true;
			}
		}

		return false;
	}

	function apply( root ) {
		var industry = [];
		var service = [];

		Array.prototype.forEach.call(
			root.querySelectorAll( 'input[data-kmpb-filter]:checked' ),
			function ( input ) {
				var bucket = input.getAttribute( 'data-kmpb-filter' ) === 'service' ? service : industry;
				bucket.push( input.value );
			}
		);

		var pill = root.querySelector( '.kmpb-pa__pill.is-active' );

		if ( pill && pill.value ) {
			( pill.getAttribute( 'data-kmpb-pill' ) === 'service' ? service : industry ).push( pill.value );
		}

		var shown = 0;

		Array.prototype.forEach.call( root.querySelectorAll( '.kmpb-pa__card' ), function ( card ) {
			var keep = matches( card, 'industry', industry ) && matches( card, 'service', service );

			card.classList.toggle( 'is-hidden', ! keep );

			if ( keep ) {
				shown++;
			}
		} );

		var empty = root.querySelector( '.kmpb-pa__empty' );

		if ( empty ) {
			empty.hidden = shown > 0;
		}

		var reset = root.querySelector( '.kmpb-pa__reset' );

		if ( reset ) {
			reset.hidden = ! ( industry.length || service.length );
		}

		// The badge on the floating button, so the count is visible with the
		// drawer shut.
		var badge = root.querySelector( '.kmpb-pa__fab-count' );

		if ( badge ) {
			var active = industry.length + service.length;

			badge.textContent = String( active );
			badge.hidden = ( active === 0 );
		}
	}

	/**
	 * Opens or closes the slide-in filter panel.
	 *
	 * The page behind it is locked while it is open, otherwise scrolling the
	 * drawer to its end carries on scrolling the grid underneath.
	 */
	function drawer( root, open ) {
		root.classList.toggle( 'is-filters-open', open );

		var scrim = root.querySelector( '.kmpb-pa__scrim' );
		var fab = root.querySelector( '.kmpb-pa__fab' );

		if ( scrim ) {
			scrim.hidden = ! open;
		}

		if ( fab ) {
			fab.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		}

		document.body.style.overflow = open ? 'hidden' : '';

		if ( open ) {
			var first = root.querySelector( '.kmpb-pa__close' );
			if ( first ) {
				first.focus();
			}
		}
	}

	function init( root ) {
		if ( ! root || root.kmpbArchiveReady ) {
			return;
		}

		root.kmpbArchiveReady = true;

		root.addEventListener( 'change', function ( event ) {
			if ( event.target.matches( 'input[data-kmpb-filter]' ) ) {
				apply( root );
			}
		} );

		root.addEventListener( 'click', function ( event ) {
			var pill = event.target.closest( '.kmpb-pa__pill' );

			if ( pill ) {
				Array.prototype.forEach.call( root.querySelectorAll( '.kmpb-pa__pill' ), function ( other ) {
					other.classList.toggle( 'is-active', other === pill );
				} );
				apply( root );
				return;
			}

			if ( event.target.closest( '.kmpb-pa__fab' ) ) {
				drawer( root, ! root.classList.contains( 'is-filters-open' ) );
				return;
			}

			if (
				event.target.closest( '.kmpb-pa__close' ) ||
				event.target.closest( '.kmpb-pa__apply' ) ||
				event.target.classList.contains( 'kmpb-pa__scrim' )
			) {
				drawer( root, false );
				return;
			}

			if ( event.target.closest( '.kmpb-pa__reset' ) ) {
				Array.prototype.forEach.call(
					root.querySelectorAll( 'input[data-kmpb-filter]' ),
					function ( input ) {
						input.checked = false;
					}
				);
				apply( root );
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && root.classList.contains( 'is-filters-open' ) ) {
				drawer( root, false );
			}
		} );

		// Resizing past the breakpoint leaves the drawer open over a layout that
		// no longer has one, and the body still locked.
		window.addEventListener( 'resize', function () {
			if ( root.classList.contains( 'is-filters-open' ) && window.innerWidth > 1024 ) {
				drawer( root, false );
			}
		} );

		apply( root );

		if ( root.classList.contains( 'kmpb-pa--slider' ) ) {
			slider( root );
		}
	}

	/**
	 * The slider.
	 *
	 * The track is a real scroll container, so swiping, trackpads, keyboard and
	 * screen readers already work. This only adds the arrows, the dots, and the
	 * optional drift, and reads its state back off scrollLeft rather than
	 * keeping a separate index that could disagree with what is on screen.
	 */
	function slider( root ) {
		var track = root.querySelector( '.kmpb-pa__grid' );

		if ( ! track ) {
			return;
		}

		var prev = root.querySelector( '.kmpb-pa__arrow--prev' );
		var next = root.querySelector( '.kmpb-pa__arrow--next' );
		var dots = root.querySelector( '.kmpb-pa__dots' );

		function pages() {
			return Math.max( 1, Math.round( track.scrollWidth / Math.max( 1, track.clientWidth ) ) );
		}

		function current() {
			return Math.round( track.scrollLeft / Math.max( 1, track.clientWidth ) );
		}

		function buildDots() {
			if ( ! dots ) {
				return;
			}

			var total = pages();

			if ( dots.children.length === total ) {
				return;
			}

			dots.textContent = '';

			for ( var i = 0; i < total; i++ ) {
				var dot = document.createElement( 'button' );
				dot.type = 'button';
				dot.className = 'kmpb-pa__dot';
				dot.setAttribute( 'aria-label', 'Go to slide ' + ( i + 1 ) );
				dot.dataset.page = String( i );
				dots.appendChild( dot );
			}
		}

		function sync() {
			var at = current();
			var last = pages() - 1;

			if ( prev ) {
				prev.disabled = ( track.scrollLeft <= 2 );
			}

			if ( next ) {
				next.disabled = ( track.scrollLeft + track.clientWidth >= track.scrollWidth - 2 );
			}

			if ( dots ) {
				Array.prototype.forEach.call( dots.children, function ( dot, i ) {
					dot.classList.toggle( 'is-active', i === Math.min( at, last ) );
				} );
			}
		}

		function go( page ) {
			track.scrollTo( { left: page * track.clientWidth, behavior: 'smooth' } );
		}

		if ( prev ) {
			prev.addEventListener( 'click', function () {
				go( Math.max( 0, current() - 1 ) );
			} );
		}

		if ( next ) {
			next.addEventListener( 'click', function () {
				go( Math.min( pages() - 1, current() + 1 ) );
			} );
		}

		if ( dots ) {
			dots.addEventListener( 'click', function ( event ) {
				var dot = event.target.closest( '.kmpb-pa__dot' );
				if ( dot ) {
					go( parseInt( dot.dataset.page, 10 ) || 0 );
				}
			} );
		}

		var ticking = false;

		track.addEventListener( 'scroll', function () {
			if ( ticking ) {
				return;
			}
			ticking = true;
			window.requestAnimationFrame( function () {
				sync();
				ticking = false;
			} );
		} );

		window.addEventListener( 'resize', function () {
			buildDots();
			sync();
		} );

		buildDots();
		sync();

		// Drifting on its own, if asked for and if the visitor has not said no
		// to motion. Any interaction ends it for good rather than fighting the
		// person for control of the strip.
		var seconds = parseInt( root.getAttribute( 'data-kmpb-autoplay' ), 10 );
		var reduced = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		if ( ! seconds || reduced ) {
			return;
		}

		var timer = window.setInterval( function () {
			var last = pages() - 1;
			go( current() >= last ? 0 : current() + 1 );
		}, seconds * 1000 );

		[ 'pointerdown', 'wheel', 'touchstart', 'mouseenter', 'focusin' ].forEach( function ( name ) {
			root.addEventListener( name, function () {
				window.clearInterval( timer );
			}, { once: true, passive: true } );
		} );
	}

	function scan( scope ) {
		var context = scope || document;
		Array.prototype.forEach.call( context.querySelectorAll( ROOT ), init );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			scan();
		} );
	} else {
		scan();
	}

	// Elementor re-renders a widget on every panel change; re-bind the new node.
	window.addEventListener( 'elementor/frontend/init', function () {
		if ( ! window.elementorFrontend || ! window.elementorFrontend.hooks ) {
			return;
		}

		[ 'kmpb-portfolio-archive.default', 'kmpb-portfolio-logos.default', 'kmpb-latest-projects.default' ].forEach( function ( hook ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/' + hook, function ( $scope ) {
				var node = $scope && $scope[ 0 ] ? $scope[ 0 ] : null;

				if ( node ) {
					scan( node );
				}
			} );
		} );
	} );
}() );
