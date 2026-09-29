/**
 * Animated Heading widget.
 *
 * Handles the rotating modes (typing, fade, slide, flip, clip, drop in) and
 * starts the highlighted shape drawing when it scrolls into view.
 *
 * Each heading owns its own timers so several on one page never interfere,
 * and every timer is cleared before a heading is re-initialised — the
 * Elementor editor re-renders a widget on every keystroke, which would
 * otherwise leave a stack of runaway intervals behind.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia
		&& window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function parseConfig( el ) {
		try {
			return JSON.parse( el.getAttribute( 'data-kmpb-ah' ) ) || {};
		} catch ( e ) {
			return {};
		}
	}

	function clearTimers( el ) {
		if ( el.__kmpbTimers ) {
			el.__kmpbTimers.forEach( clearTimeout );
		}
		el.__kmpbTimers = [];
	}

	function later( el, fn, delay ) {
		var id = setTimeout( fn, delay );
		el.__kmpbTimers.push( id );
		return id;
	}

	/**
	 * Locks the rotator to the width of its longest word so surrounding text
	 * does not shuffle sideways on every change.
	 */
	function lockWidth( focus, words ) {
		var widest = 0;

		words.forEach( function ( word ) {
			var w = word.offsetWidth;
			if ( w > widest ) {
				widest = w;
			}
		} );

		if ( widest ) {
			focus.style.minWidth = widest + 'px';
		}
	}

	function setActive( words, index ) {
		words.forEach( function ( word, i ) {
			word.classList.toggle( 'is-active', i === index );
		} );
	}

	/* ------------------------------------------------------------ *
	 * Rotating
	 * ------------------------------------------------------------ */

	function initRotate( el, cfg ) {

		var focus = el.querySelector( '.kmpb-ah__focus--rotate' );
		if ( ! focus ) {
			return;
		}

		var words = Array.prototype.slice.call( focus.querySelectorAll( '.kmpb-ah__word' ) );
		if ( ! words.length ) {
			return;
		}

		// Measure before stacking them, while they still occupy real space.
		lockWidth( focus, words );
		focus.classList.add( 'is-ready' );

		if ( words.length < 2 || reduceMotion ) {
			setActive( words, 0 );
			return;
		}

		var index = 0;
		var hold = cfg.speed || 2200;

		if ( 'typing' === cfg.animation ) {
			runTyping( el, words, cfg );
			return;
		}

		setActive( words, 0 );

		function next() {
			index = ( index + 1 ) % words.length;

			// Stop at the last word when looping is off.
			if ( ! cfg.loop && 0 === index ) {
				setActive( words, words.length - 1 );
				return;
			}

			setActive( words, index );
			later( el, next, hold );
		}

		later( el, next, hold );
	}

	/**
	 * Types each word out letter by letter, pauses, then deletes it.
	 *
	 * The full word is kept in a data attribute so the element's text can be
	 * rewritten freely without losing the original.
	 */
	function runTyping( el, words, cfg ) {

		words.forEach( function ( word ) {
			if ( ! word.hasAttribute( 'data-text' ) ) {
				word.setAttribute( 'data-text', word.textContent );
			}
			word.textContent = '';
		} );

		var index = 0;
		var speed = cfg.typeSpeed || 90;
		var hold = cfg.speed || 2200;

		function typeWord() {
			var word = words[ index ];
			var full = word.getAttribute( 'data-text' ) || '';
			var pos = 0;

			setActive( words, index );

			function typeChar() {
				word.textContent = full.slice( 0, pos + 1 );
				pos++;

				if ( pos < full.length ) {
					later( el, typeChar, speed );
				} else {
					later( el, eraseWord, hold );
				}
			}

			function eraseWord() {
				var isLast = index === words.length - 1;

				// Nothing left to type: leave the final word on screen.
				if ( ! cfg.loop && isLast ) {
					return;
				}

				var text = word.textContent;

				if ( text.length ) {
					word.textContent = text.slice( 0, -1 );
					// Deleting reads better at roughly half the typing speed.
					later( el, eraseWord, Math.max( 20, speed / 2 ) );
				} else {
					index = ( index + 1 ) % words.length;
					later( el, typeWord, 220 );
				}
			}

			typeChar();
		}

		typeWord();
	}

	/* ------------------------------------------------------------ *
	 * Highlighted
	 * ------------------------------------------------------------ */

	function initHighlight( el ) {

		// pathLength="1" normalises the dash maths for any shape.
		el.querySelectorAll( '.kmpb-ah__shape path' ).forEach( function ( path ) {
			path.setAttribute( 'pathLength', '1' );
		} );

		if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) {
			el.classList.add( 'is-visible' );
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-visible' );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ threshold: 0.25 }
		);

		observer.observe( el );
	}

	/* ------------------------------------------------------------ *
	 * Boot
	 * ------------------------------------------------------------ */

	function initOne( el ) {
		clearTimers( el );

		var cfg = parseConfig( el );

		if ( 'highlight' === cfg.type ) {
			initHighlight( el );
		} else {
			initRotate( el, cfg );
		}
	}

	function initAll( scope ) {
		var root = scope && scope.querySelectorAll ? scope : document;
		Array.prototype.forEach.call(
			root.querySelectorAll( '[data-kmpb-ah]' ),
			initOne
		);
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			initAll();
		} );
	} else {
		initAll();
	}

	// Elementor rebuilds the widget's markup as its controls change, so hook
	// the frontend API to re-initialise just that widget.
	window.addEventListener( 'elementor/frontend/init', function () {
		if ( ! window.elementorFrontend || ! window.elementorFrontend.hooks ) {
			return;
		}
		window.elementorFrontend.hooks.addAction(
			'frontend/element_ready/kmpb-animated-heading.default',
			function ( $scope ) {
				var node = $scope && $scope[ 0 ] ? $scope[ 0 ] : null;
				if ( node ) {
					initAll( node );
				}
			}
		);
	} );
} )();
