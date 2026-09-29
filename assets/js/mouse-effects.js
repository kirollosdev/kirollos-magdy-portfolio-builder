/**
 * Mouse Effects for Elementor — frontend engine.
 *
 * Vanilla JS, no dependencies.
 *
 * Configuration is read from the element itself, NOT from data-* attributes:
 *   - toggles/choices  -> CSS classes written by Elementor's `prefix_class`
 *   - numeric values   -> CSS custom properties written by `selectors`
 *
 * Both live-update in the Elementor editor and render on the frontend, which
 * data-* attributes printed from `before_render` do not (elementor#9623).
 *
 * Floating Effects are handled purely in CSS. The only time JS touches them is
 * when an element ALSO has a Mouse Effect, since both drive `transform` and a
 * CSS animation would override the JS inline transform. In that case the JS
 * takes over the floating maths and composes everything into one transform.
 */
( function () {
	'use strict';

	var BREAKPOINT_MOBILE = 767;
	var BREAKPOINT_TABLET = 1024;

	var trackEls = [];
	var magneticEls = [];
	var loopEls = [];

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	var lastClientX = window.innerWidth / 2;
	var lastClientY = window.innerHeight / 2;
	var lastPageX = 0;
	var lastPageY = 0;

	var trackQueued = false;
	var magneticQueued = false;
	var loopRunning = false;
	var startTime = now();

	function now() {
		return ( window.performance && performance.now ) ? performance.now() : Date.now();
	}

	function clamp( v, min, max ) {
		return Math.min( max, Math.max( min, v ) );
	}

	function getDevice() {
		var w = window.innerWidth;
		if ( w <= BREAKPOINT_MOBILE ) {
			return 'mobile';
		}
		if ( w <= BREAKPOINT_TABLET ) {
			return 'tablet';
		}
		return 'desktop';
	}

	/**
	 * Reads a CSS custom property off the element as a number.
	 * Values arrive as strings like " 15px" / " 4" / " 3000ms".
	 */
	function cssVar( el, name, fallback ) {
		var raw = window.getComputedStyle( el ).getPropertyValue( name );
		if ( ! raw ) {
			return fallback;
		}
		var n = parseFloat( raw );
		return isNaN( n ) ? fallback : n;
	}

	function hasClass( el, name ) {
		return el.classList.contains( name );
	}

	function mouseAllowed( el ) {
		return ! hasClass( el, 'mefe-mhide-' + getDevice() + '-yes' );
	}

	function floatAllowed( el ) {
		return ! hasClass( el, 'mefe-fhide-' + getDevice() + '-yes' );
	}

	function getType( el ) {
		if ( hasClass( el, 'mefe-type-tilt' ) ) {
			return 'tilt';
		}
		if ( hasClass( el, 'mefe-type-magnetic' ) ) {
			return 'magnetic';
		}
		if ( hasClass( el, 'mefe-type-track' ) ) {
			return 'track';
		}
		return 'track'; // Elementor omits the class while the default is untouched.
	}

	function getDirection( el ) {
		return hasClass( el, 'mefe-dir-opposite' ) ? -1 : 1;
	}

	function getRelative( el ) {
		if ( hasClass( el, 'mefe-rel-page' ) ) {
			return 'page';
		}
		if ( hasClass( el, 'mefe-rel-default' ) ) {
			return 'default';
		}
		return 'viewport';
	}

	function ensurePositionContext( el ) {
		if ( 'static' === window.getComputedStyle( el ).position ) {
			el.style.position = 'relative';
		}
	}

	function getState( el ) {
		if ( ! el.__mefeState ) {
			el.__mefeState = {
				m: { tx: 0, ty: 0, rx: 0, ry: 0, scale: 1, perspective: false },
				f: { tx: 0, ty: 0, rot: 0, scale: 1 }
			};
		}
		return el.__mefeState;
	}

	/**
	 * Composes the mouse + float contributions into a single transform.
	 * Order matters: perspective must lead, scale trails.
	 */
	function applyTransform( el ) {
		var s = getState( el );
		var parts = [];

		if ( s.m.perspective ) {
			parts.push( 'perspective(1000px)' );
		}

		var tx = s.m.tx + s.f.tx;
		var ty = s.m.ty + s.f.ty;

		if ( tx || ty ) {
			parts.push( 'translate3d(' + tx.toFixed( 2 ) + 'px,' + ty.toFixed( 2 ) + 'px,0)' );
		}

		if ( s.m.rx || s.m.ry ) {
			parts.push( 'rotateX(' + s.m.rx.toFixed( 2 ) + 'deg)' );
			parts.push( 'rotateY(' + s.m.ry.toFixed( 2 ) + 'deg)' );
		}

		if ( s.f.rot ) {
			parts.push( 'rotate(' + s.f.rot.toFixed( 2 ) + 'deg)' );
		}

		var scale = s.m.scale * s.f.scale;
		if ( 1 !== scale ) {
			parts.push( 'scale(' + scale.toFixed( 4 ) + ')' );
		}

		el.style.transform = parts.length ? parts.join( ' ' ) : '';
	}

	/* ------------------------------------------------------------ *
	 * Discovery / (re)scan
	 * ------------------------------------------------------------ */

	function scan() {
		trackEls = [];
		magneticEls = [];
		loopEls = [];

		// Cursor trails are independent of the transform-based effects.
		trailEls = [];

		if ( ! reduceMotion ) {
			Array.prototype.forEach.call(
				document.querySelectorAll( '.mefe-trail-yes' ),
				function ( el ) {
					trailState( el ); // creates the canvas if it isn't there yet
					trailEls.push( el );
				}
			);
		}

		var nodes = document.querySelectorAll( '.mefe-mouse-yes, .mefe-float-yes' );

		Array.prototype.forEach.call( nodes, function ( el ) {
			var hasMouse = hasClass( el, 'mefe-mouse-yes' );
			var hasFloat = hasClass( el, 'mefe-float-yes' );

			getState( el );
			ensurePositionContext( el );

			// Floating alone stays pure CSS. Only when it has to share the
			// transform with a mouse effect does JS take it over.
			var jsFloat = hasFloat && hasMouse && ! reduceMotion;

			el.classList.toggle( 'mefe-float-js', jsFloat );

			if ( ! hasMouse ) {
				return;
			}

			var type = getType( el );

			if ( 'tilt' === type ) {
				initTilt( el );
				if ( jsFloat ) {
					loopEls.push( el );
				}
				return;
			}

			if ( jsFloat ) {
				loopEls.push( el );
			} else if ( 'track' === type ) {
				trackEls.push( el );
			} else if ( 'magnetic' === type ) {
				magneticEls.push( el );
			}
		} );

		if ( loopEls.length && ! loopRunning ) {
			loopRunning = true;
			requestAnimationFrame( loop );
		}

		// Editor: the panel rewrites classes/variables as the user drags a
		// slider, so re-read the config of whatever is currently hovered.
		if ( cursorHost ) {
			if ( document.contains( cursorHost ) && cursorAllowed( cursorHost ) ) {
				styleCursor( cursorHost );
			} else {
				hideCursor();
			}
		}
	}

	/* ------------------------------------------------------------ *
	 * Mouse Track
	 * ------------------------------------------------------------ */

	function computeTrack( el ) {
		var s = getState( el );

		if ( ! mouseAllowed( el ) ) {
			s.m.tx = 0;
			s.m.ty = 0;
			return;
		}

		var vw = window.innerWidth;
		var vh = window.innerHeight;
		var relative = getRelative( el );
		var px, py;

		if ( 'page' === relative ) {
			var docEl = document.documentElement;
			var pageW = Math.max( docEl.scrollWidth, vw );
			var pageH = Math.max( docEl.scrollHeight, vh );
			px = ( lastPageX - pageW / 2 ) / ( pageW / 2 );
			py = ( lastPageY - pageH / 2 ) / ( pageH / 2 );
		} else if ( 'default' === relative ) {
			var rect = el.getBoundingClientRect();
			px = clamp( ( lastClientX - ( rect.left + rect.width / 2 ) ) / ( vw / 2 ), -1, 1 );
			py = clamp( ( lastClientY - ( rect.top + rect.height / 2 ) ) / ( vh / 2 ), -1, 1 );
		} else {
			px = ( lastClientX - vw / 2 ) / ( vw / 2 );
			py = ( lastClientY - vh / 2 ) / ( vh / 2 );
		}

		var dir = getDirection( el );
		var speed = cssVar( el, '--mefe-track-speed', 4 ) * 4; // px per unit at full deflection

		s.m.tx = px * speed * dir;
		s.m.ty = py * speed * dir;
	}

	function updateTrack() {
		trackQueued = false;
		trackEls.forEach( function ( el ) {
			computeTrack( el );
			applyTransform( el );
		} );
	}

	/* ------------------------------------------------------------ *
	 * 3D Tilt (hover driven, per element)
	 * ------------------------------------------------------------ */

	function initTilt( el ) {
		if ( el.__mefeTiltInit ) {
			syncGlare( el );
			return;
		}
		el.__mefeTiltInit = true;

		syncGlare( el );

		el.addEventListener( 'mousemove', function ( e ) {
			var s = getState( el );

			if ( ! mouseAllowed( el ) ) {
				return;
			}

			var rect = el.getBoundingClientRect();
			var px, py;

			if ( 'page' === getRelative( el ) ) {
				px = clamp( e.clientX / window.innerWidth, 0, 1 );
				py = clamp( e.clientY / window.innerHeight, 0, 1 );
			} else {
				px = clamp( ( e.clientX - rect.left ) / rect.width, 0, 1 );
				py = clamp( ( e.clientY - rect.top ) / rect.height, 0, 1 );
			}

			var dir = getDirection( el );
			var speed = cssVar( el, '--mefe-tilt-speed', 6 );
			var scale = cssVar( el, '--mefe-tilt-scale', 1 );
			var glare = cssVar( el, '--mefe-tilt-glare', 0 );

			s.m.perspective = true;
			s.m.ry = ( px - 0.5 ) * speed * dir;
			s.m.rx = -( py - 0.5 ) * speed * dir;
			s.m.scale = scale > 1 ? scale : 1;

			applyTransform( el );

			if ( el.__mefeGlareEl ) {
				el.__mefeGlareEl.style.opacity = glare;
				el.__mefeGlareEl.style.background = 'radial-gradient(circle at ' + ( px * 100 ).toFixed( 1 ) + '% ' + ( py * 100 ).toFixed( 1 ) + '%, rgba(255,255,255,0.85) 0%, rgba(255,255,255,0) 60%)';
			}
		} );

		el.addEventListener( 'mouseleave', function () {
			var s = getState( el );
			s.m.rx = 0;
			s.m.ry = 0;
			s.m.scale = 1;
			applyTransform( el );

			if ( el.__mefeGlareEl ) {
				el.__mefeGlareEl.style.opacity = 0;
			}
		} );
	}

	function syncGlare( el ) {
		var glare = cssVar( el, '--mefe-tilt-glare', 0 );

		if ( glare > 0 && ! el.__mefeGlareEl ) {
			var node = document.createElement( 'div' );
			node.className = 'mefe-glare';
			el.appendChild( node );
			el.__mefeGlareEl = node;
		} else if ( glare <= 0 && el.__mefeGlareEl ) {
			el.__mefeGlareEl.parentNode.removeChild( el.__mefeGlareEl );
			el.__mefeGlareEl = null;
		}
	}

	/* ------------------------------------------------------------ *
	 * Magnetic Pull
	 * ------------------------------------------------------------ */

	function computeMagnetic( el ) {
		var s = getState( el );

		if ( ! mouseAllowed( el ) ) {
			s.m.tx = 0;
			s.m.ty = 0;
			s.m.scale = 1;
			return;
		}

		var rect = el.getBoundingClientRect();
		var scrollX = window.scrollX || window.pageXOffset;
		var scrollY = window.scrollY || window.pageYOffset;
		var dx = lastPageX - ( rect.left + scrollX + rect.width / 2 );
		var dy = lastPageY - ( rect.top + scrollY + rect.height / 2 );
		var dist = Math.sqrt( dx * dx + dy * dy );
		var radius = cssVar( el, '--mefe-mag-radius', 150 );

		if ( dist < radius ) {
			var strength = cssVar( el, '--mefe-mag-strength', 30 ) / 100;
			var scaleAmt = cssVar( el, '--mefe-mag-scale', 1 );
			var pull = 1 - dist / radius;
			s.m.tx = dx * strength * pull;
			s.m.ty = dy * strength * pull;
			s.m.scale = scaleAmt > 1 ? 1 + ( scaleAmt - 1 ) * pull : 1;
		} else {
			s.m.tx = 0;
			s.m.ty = 0;
			s.m.scale = 1;
		}
	}

	function updateMagnetic() {
		magneticQueued = false;
		magneticEls.forEach( function ( el ) {
			computeMagnetic( el );
			applyTransform( el );
		} );
	}

	/* ------------------------------------------------------------ *
	 * Floating (JS path — only when composed with a mouse effect)
	 * ------------------------------------------------------------ */

	function computeFloat( el, elapsed ) {
		var s = getState( el );

		if ( ! floatAllowed( el ) ) {
			s.f.tx = 0;
			s.f.ty = 0;
			s.f.rot = 0;
			s.f.scale = 1;
			return;
		}

		var duration = Math.max( 100, cssVar( el, '--mefe-fdur', 3000 ) );
		var delay = cssVar( el, '--mefe-fdelay', 0 );
		var t = ( elapsed - delay ) / duration;

		if ( t < 0 ) {
			s.f.tx = 0;
			s.f.ty = 0;
			s.f.rot = 0;
			s.f.scale = 1;
			return;
		}

		var wave = Math.sin( t * Math.PI * 2 );
		var scaleTo = cssVar( el, '--mefe-fscale-to', 1 );

		s.f.ty = cssVar( el, '--mefe-fy', 0 ) * wave;
		s.f.tx = cssVar( el, '--mefe-fx', 0 ) * wave;
		s.f.rot = cssVar( el, '--mefe-frot', 0 ) * wave;
		s.f.scale = scaleTo > 1 ? 1 + ( scaleTo - 1 ) * ( 0.5 + 0.5 * wave ) : 1;
	}

	function loop() {
		if ( ! loopEls.length ) {
			loopRunning = false;
			return;
		}

		var elapsed = now() - startTime;

		loopEls.forEach( function ( el ) {
			computeFloat( el, elapsed );

			var type = getType( el );
			if ( 'track' === type ) {
				computeTrack( el );
			} else if ( 'magnetic' === type ) {
				computeMagnetic( el );
			}

			applyTransform( el );
		} );

		requestAnimationFrame( loop );
	}

	/* ------------------------------------------------------------ *
	 * Mouse Cursor
	 *
	 * One shared node per document. Rather than binding listeners to every
	 * element (which would need re-binding on every editor re-render), the
	 * hovered target is resolved from the global mousemove with closest().
	 * ------------------------------------------------------------ */

	var cursorNode = null;
	var cursorHost = null;   // element currently providing the cursor config
	var cursorX = -100;
	var cursorY = -100;
	var cursorLerp = 0.4;
	var cursorLoopRunning = false;
	var finePointer = ! window.matchMedia || window.matchMedia( '(pointer: fine)' ).matches;

	/** Reads a CSS custom property as a trimmed string. */
	function cssVarRaw( el, name, fallback ) {
		var raw = window.getComputedStyle( el ).getPropertyValue( name );
		if ( ! raw ) {
			return fallback;
		}
		raw = raw.trim();
		return raw ? raw : fallback;
	}

	/** Strips the quotes CSS keeps around string custom properties. */
	function unquote( str ) {
		return str.replace( /^["']|["']$/g, '' );
	}

	function ensureCursorNode() {
		if ( cursorNode && cursorNode.parentNode ) {
			return cursorNode;
		}
		cursorNode = document.createElement( 'div' );
		cursorNode.className = 'mefe-cursor';
		document.body.appendChild( cursorNode );
		return cursorNode;
	}

	function cursorAllowed( el ) {
		return finePointer && ! hasClass( el, 'mefe-chide-' + getDevice() + '-yes' );
	}

	function cursorTypeOf( el ) {
		if ( hasClass( el, 'mefe-ctype-text' ) ) {
			return 'text';
		}
		if ( hasClass( el, 'mefe-ctype-image' ) ) {
			return 'image';
		}
		return 'dot';
	}

	/** Copies the host element's cursor settings onto the shared node. */
	function styleCursor( el ) {
		var node = ensureCursorNode();
		var type = cursorTypeOf( el );
		var size = cssVar( el, '--mefe-cursor-size', 40 );
		var opacity = cssVar( el, '--mefe-cursor-opacity', 1 );

		cursorLerp = 1 - clamp( cssVar( el, '--mefe-cursor-lag', 60 ), 0, 95 ) / 100;
		if ( cursorLerp < 0.05 ) {
			cursorLerp = 0.05;
		}

		node.style.width = size + 'px';
		node.style.height = size + 'px';
		node.style.marginLeft = ( -size / 2 ) + 'px';
		node.style.marginTop = ( -size / 2 ) + 'px';
		node.style.borderRadius = cssVarRaw( el, '--mefe-cursor-radius', '50%' );
		node.style.mixBlendMode = cssVarRaw( el, '--mefe-cursor-blend', 'normal' );
		node.style.setProperty( '--mefe-c-opacity', opacity );

		if ( 'image' === type ) {
			node.textContent = '';
			node.style.backgroundColor = 'transparent';
			node.style.backgroundImage = cssVarRaw( el, '--mefe-cursor-image', 'none' );
		} else {
			node.style.backgroundImage = 'none';
			node.style.backgroundColor = cssVarRaw( el, '--mefe-cursor-bg', '#7c3aed' );

			if ( 'text' === type ) {
				node.textContent = unquote( cssVarRaw( el, '--mefe-cursor-text', '' ) );
				node.style.color = cssVarRaw( el, '--mefe-cursor-color', '#fff' );
				node.style.fontSize = cssVar( el, '--mefe-cursor-font-size', 14 ) + 'px';
			} else {
				node.textContent = '';
			}
		}
	}

	function showCursor( el ) {
		var wasActive = cursorNode && cursorNode.classList.contains( 'is-active' );

		styleCursor( el );

		// Seed the position on first appearance, otherwise the node visibly
		// flies in from its offscreen resting spot.
		if ( ! wasActive ) {
			cursorX = lastClientX;
			cursorY = lastClientY;
			cursorNode.style.transform = 'translate3d(' + cursorX + 'px,' + cursorY + 'px,0) scale(1)';
		}

		cursorNode.classList.add( 'is-active' );

		if ( ! cursorLoopRunning ) {
			cursorLoopRunning = true;
			requestAnimationFrame( cursorLoop );
		}
	}

	function hideCursor() {
		if ( cursorNode ) {
			cursorNode.classList.remove( 'is-active' );
		}
		cursorHost = null;
	}

	function updateCursorTarget( e ) {
		if ( ! finePointer ) {
			return;
		}

		var target = e.target && e.target.closest ? e.target.closest( '.mefe-cursor-yes' ) : null;

		if ( target && ! cursorAllowed( target ) ) {
			target = null;
		}

		if ( target === cursorHost ) {
			return;
		}

		cursorHost = target;

		if ( target ) {
			showCursor( target );
		} else {
			hideCursor();
		}
	}

	function cursorLoop() {
		if ( ! cursorNode || ! cursorHost ) {
			// Settle the node offscreen, then stop burning frames.
			cursorLoopRunning = false;
			return;
		}

		cursorX += ( lastClientX - cursorX ) * cursorLerp;
		cursorY += ( lastClientY - cursorY ) * cursorLerp;

		cursorNode.style.transform = 'translate3d(' + cursorX.toFixed( 2 ) + 'px,' + cursorY.toFixed( 2 ) + 'px,0) scale(1)';

		requestAnimationFrame( cursorLoop );
	}

	/* ------------------------------------------------------------ *
	 * Cursor Trail
	 *
	 * The visitor keeps their normal pointer; particles / sparkles / a glow /
	 * a comet tail are painted on a canvas sitting behind the element content.
	 * One canvas per element, one shared rAF loop for all of them, and the
	 * loop parks itself as soon as nothing is alive.
	 * ------------------------------------------------------------ */

	var trailEls = [];
	var trailLoopRunning = false;
	var trailLastTime = 0;

	function trailAllowed( el ) {
		return ! hasClass( el, 'mefe-thide-' + getDevice() + '-yes' );
	}

	function trailTypeOf( el ) {
		if ( hasClass( el, 'mefe-ttype-sparkles' ) ) {
			return 'sparkles';
		}
		if ( hasClass( el, 'mefe-ttype-glow' ) ) {
			return 'glow';
		}
		if ( hasClass( el, 'mefe-ttype-comet' ) ) {
			return 'comet';
		}
		return 'particles';
	}

	function ensureTrailCanvas( el ) {
		var canvas = el.querySelector( ':scope > .mefe-trail-canvas' );

		if ( ! canvas ) {
			canvas = document.createElement( 'canvas' );
			canvas.className = 'mefe-trail-canvas';
			canvas.setAttribute( 'aria-hidden', 'true' );
			el.insertBefore( canvas, el.firstChild );
		}

		return canvas;
	}

	function sizeTrailCanvas( el, state ) {
		var rect = el.getBoundingClientRect();
		var dpr = Math.min( window.devicePixelRatio || 1, 2 );

		if ( rect.width === state.cssW && rect.height === state.cssH && dpr === state.dpr ) {
			return;
		}

		state.cssW = rect.width;
		state.cssH = rect.height;
		state.dpr = dpr;
		state.canvas.width = Math.max( 1, Math.round( rect.width * dpr ) );
		state.canvas.height = Math.max( 1, Math.round( rect.height * dpr ) );
		state.ctx.setTransform( dpr, 0, 0, dpr, 0, 0 );
	}

	function trailState( el ) {
		if ( ! el.__mefeTrail ) {
			var canvas = ensureTrailCanvas( el );
			el.__mefeTrail = {
				canvas: canvas,
				ctx: canvas.getContext( '2d' ),
				parts: [],
				points: [],
				glowX: null,
				glowY: null,
				px: 0,
				py: 0,
				inside: false,
				exclude: null,
				excludeRaw: null,
				cssW: -1,
				cssH: -1,
				dpr: 0
			};
		}
		return el.__mefeTrail;
	}

	/**
	 * Selector for anything layered on top of the element — a floating header,
	 * a fixed overlay — that should suppress the trail while the pointer is on
	 * it.
	 *
	 * The `inside` test in updateTrails() is pure geometry, so an element
	 * painted above this one is still "inside" as far as the trail is
	 * concerned. This is the hit-test that geometry alone cannot do.
	 *
	 * The value is typed by hand in the panel and an invalid selector makes
	 * closest() throw, which would take down the whole mousemove handler and
	 * the track / magnetic / tilt effects with it. So it is validated once and
	 * the verdict cached until the string itself changes.
	 */
	function trailExclude( el, state ) {
		var raw = unquote( cssVarRaw( el, '--mefe-trail-exclude', '' ) ).trim();

		if ( raw === state.excludeRaw ) {
			return state.exclude;
		}

		state.excludeRaw = raw;
		state.exclude = null;

		if ( raw ) {
			try {
				document.querySelector( raw );
				state.exclude = raw;
			} catch ( err ) {
				state.exclude = null;
			}
		}

		return state.exclude;
	}

	/** Picks between the two configured colours. */
	function trailColor( el, mix ) {
		var c1 = cssVarRaw( el, '--mefe-trail-color', '#7c3aed' );
		var c2 = cssVarRaw( el, '--mefe-trail-color2', '' );
		if ( ! c2 ) {
			return c1;
		}
		return mix < 0.5 ? c1 : c2;
	}

	function spawnTrail( el, state, x, y ) {
		var type = trailTypeOf( el );
		var size = cssVar( el, '--mefe-trail-size', 8 );

		if ( 'glow' === type ) {
			return; // the glow is a single follower, not spawned pieces
		}

		if ( 'comet' === type ) {
			state.points.push( { x: x, y: y } );
			var maxLen = Math.round( cssVar( el, '--mefe-trail-length', 25 ) );
			while ( state.points.length > maxLen ) {
				state.points.shift();
			}
			return;
		}

		var count = Math.round( cssVar( el, '--mefe-trail-count', 2 ) );

		for ( var i = 0; i < count; i++ ) {
			state.parts.push( {
				x: x,
				y: y,
				vx: ( Math.random() - 0.5 ) * 1.6,
				vy: ( Math.random() - 0.5 ) * 1.6 - 0.3,
				r: size * ( 0.5 + Math.random() * 0.7 ),
				life: 1,
				rot: Math.random() * Math.PI,
				color: trailColor( el, Math.random() )
			} );
		}

		// Hard ceiling so a frantic mouse can't queue thousands of particles.
		if ( state.parts.length > 400 ) {
			state.parts.splice( 0, state.parts.length - 400 );
		}
	}

	function paintBlob( ctx, x, y, radius, color, alpha ) {
		if ( alpha <= 0.001 || radius <= 0 ) {
			return;
		}
		var grad = ctx.createRadialGradient( x, y, 0, x, y, radius );
		grad.addColorStop( 0, color );
		grad.addColorStop( 1, 'transparent' );
		ctx.globalAlpha = alpha;
		ctx.fillStyle = grad;
		ctx.beginPath();
		ctx.arc( x, y, radius, 0, Math.PI * 2 );
		ctx.fill();
	}

	/**
	 * A single soft glow that follows the pointer.
	 *
	 * Nothing here runs unless the pointer is inside the element: the glow
	 * fades in on entry, fades out on exit, and the loop parks itself once it
	 * has fully faded. While it is visible a small idle offset keeps it
	 * wandering gently, so it still feels alive when the cursor stops moving.
	 * The two wander frequencies are deliberately unequal so the path is a
	 * slow open figure rather than a circle.
	 */
	function renderGlow( el, state, ctx, opacity, dt ) {
		state.glowAlpha = state.inside
			? Math.min( 1, ( state.glowAlpha || 0 ) + dt * 3 )
			: Math.max( 0, ( state.glowAlpha || 0 ) - dt * 2 );

		// Fully faded and the pointer is away: draw nothing, let the loop stop.
		if ( state.glowAlpha <= 0.001 ) {
			state.glowX = null;
			return false;
		}

		var lag = 1 - clamp( cssVar( el, '--mefe-trail-lag', 85 ), 0, 95 ) / 100;
		if ( lag < 0.02 ) {
			lag = 0.02;
		}

		if ( null === state.glowX ) {
			state.glowX = state.px;
			state.glowY = state.py;
		}
		state.glowX += ( state.px - state.glowX ) * lag;
		state.glowY += ( state.py - state.glowY ) * lag;

		var drift = cssVar( el, '--mefe-trail-drift', 25 );
		var ox = 0;
		var oy = 0;

		if ( drift > 0 ) {
			var w = cssVar( el, '--mefe-trail-drift-speed', 3 ) * 0.00018;
			var t = now();
			ox = Math.sin( t * w ) * drift;
			oy = Math.cos( t * w * 0.73 ) * drift;
		}

		paintBlob(
			ctx,
			state.glowX + ox,
			state.glowY + oy,
			Math.max( 1, cssVar( el, '--mefe-trail-size', 260 ) ),
			cssVarRaw( el, '--mefe-trail-color', '#7c3aed' ),
			opacity * state.glowAlpha
		);

		return true;
	}

	function drawSparkle( ctx, p ) {
		var r = p.r;
		ctx.save();
		ctx.translate( p.x, p.y );
		ctx.rotate( p.rot );
		ctx.beginPath();
		// Four-point star drawn as two crossed tapered diamonds.
		ctx.moveTo( 0, -r );
		ctx.quadraticCurveTo( 0, 0, r, 0 );
		ctx.quadraticCurveTo( 0, 0, 0, r );
		ctx.quadraticCurveTo( 0, 0, -r, 0 );
		ctx.quadraticCurveTo( 0, 0, 0, -r );
		ctx.closePath();
		ctx.fill();
		ctx.restore();
	}

	function renderTrail( el, dt ) {
		var state = trailState( el );

		if ( ! trailAllowed( el ) ) {
			state.ctx.clearRect( 0, 0, state.cssW, state.cssH );
			state.parts.length = 0;
			state.points.length = 0;
			return false;
		}

		sizeTrailCanvas( el, state );

		var ctx = state.ctx;
		var type = trailTypeOf( el );
		var opacity = cssVar( el, '--mefe-trail-opacity', 0.7 );
		var additive = hasClass( el, 'mefe-tadd-yes' );

		ctx.clearRect( 0, 0, state.cssW, state.cssH );
		ctx.globalCompositeOperation = additive ? 'lighter' : 'source-over';
		ctx.globalAlpha = opacity;

		var alive = false;

		if ( 'glow' === type ) {
			return renderGlow( el, state, ctx, opacity, dt );
		}

		if ( 'comet' === type ) {
			var pts = state.points;

			if ( ! state.inside && pts.length ) {
				pts.shift(); // drain the tail once the pointer leaves
			}

			if ( pts.length > 1 ) {
				var width = Math.max( 1, cssVar( el, '--mefe-trail-size', 8 ) );
				ctx.strokeStyle = cssVarRaw( el, '--mefe-trail-color', '#7c3aed' );
				ctx.lineCap = 'round';
				ctx.lineJoin = 'round';

				for ( var j = 1; j < pts.length; j++ ) {
					var t = j / pts.length; // taper and fade toward the tail end
					ctx.globalAlpha = opacity * t;
					ctx.lineWidth = width * t;
					ctx.beginPath();
					ctx.moveTo( pts[ j - 1 ].x, pts[ j - 1 ].y );
					ctx.lineTo( pts[ j ].x, pts[ j ].y );
					ctx.stroke();
				}
				alive = true;
			}

			return alive;
		}

		// particles / sparkles
		var decay = cssVar( el, '--mefe-trail-life', 6 ) / 10;

		for ( var i = state.parts.length - 1; i >= 0; i-- ) {
			var p = state.parts[ i ];

			p.life -= decay * dt;

			if ( p.life <= 0 ) {
				state.parts.splice( i, 1 );
				continue;
			}

			p.x += p.vx;
			p.y += p.vy;
			p.vy += 0.02;   // a touch of gravity
			p.rot += 0.05;

			ctx.globalAlpha = opacity * p.life;
			ctx.fillStyle = p.color;

			if ( 'sparkles' === type ) {
				drawSparkle( ctx, { x: p.x, y: p.y, r: p.r * p.life, rot: p.rot } );
			} else {
				ctx.beginPath();
				ctx.arc( p.x, p.y, Math.max( 0.1, p.r * p.life ), 0, Math.PI * 2 );
				ctx.fill();
			}

			alive = true;
		}

		return alive;
	}

	function trailLoop( ts ) {
		if ( ! trailEls.length ) {
			trailLoopRunning = false;
			return;
		}

		var dt = trailLastTime ? Math.min( ( ts - trailLastTime ) / 1000, 0.05 ) : 0.016;
		trailLastTime = ts;

		var anyAlive = false;

		trailEls.forEach( function ( el ) {
			if ( renderTrail( el, dt ) ) {
				anyAlive = true;
			}
		} );

		if ( anyAlive ) {
			requestAnimationFrame( trailLoop );
		} else {
			trailLoopRunning = false;
			trailLastTime = 0;
		}
	}

	function startTrailLoop() {
		if ( ! trailLoopRunning ) {
			trailLoopRunning = true;
			trailLastTime = 0;
			requestAnimationFrame( trailLoop );
		}
	}

	function updateTrails( e ) {
		if ( ! trailEls.length || reduceMotion ) {
			return;
		}

		trailEls.forEach( function ( el ) {
			var state = trailState( el );
			var rect = el.getBoundingClientRect();
			var x = e.clientX - rect.left;
			var y = e.clientY - rect.top;
			var inside = x >= 0 && y >= 0 && x <= rect.width && y <= rect.height;
			var exclude = trailExclude( el, state );

			if ( inside && exclude && e.target && e.target.closest && e.target.closest( exclude ) ) {
				inside = false;
			}

			state.inside = inside;

			// No need to nudge the loop here: it is already running from the
			// frames before the pointer left, and renderGlow() keeps it alive
			// until it has faded itself out.
			if ( ! inside ) {
				return;
			}

			state.px = x;
			state.py = y;
			spawnTrail( el, state, x, y );
			startTrailLoop();
		} );
	}

	/* ------------------------------------------------------------ *
	 * Global listeners
	 * ------------------------------------------------------------ */

	function onMouseMove( e ) {
		updateTrails( e );
		lastClientX = e.clientX;
		lastClientY = e.clientY;
		lastPageX = e.pageX;
		lastPageY = e.pageY;

		updateCursorTarget( e );

		if ( trackEls.length && ! trackQueued ) {
			trackQueued = true;
			requestAnimationFrame( updateTrack );
		}
		if ( magneticEls.length && ! magneticQueued ) {
			magneticQueued = true;
			requestAnimationFrame( updateMagnetic );
		}
	}

	function debounce( fn, wait ) {
		var timer;
		return function () {
			var args = arguments;
			clearTimeout( timer );
			timer = setTimeout( function () {
				fn.apply( null, args );
			}, wait );
		};
	}

	var rescan = debounce( scan, 150 );

	function init() {
		scan();
		window.addEventListener( 'mousemove', onMouseMove, { passive: true } );
		window.addEventListener( 'resize', rescan );

		// Pointer left the document entirely (out of the window, or out of the
		// editor preview iframe) — nothing is hovered any more.
		document.addEventListener( 'mouseleave', hideCursor );
		window.addEventListener( 'blur', hideCursor );

		// In the editor the panel rewrites classes/variables as the user types,
		// so watch the preview for changes and re-read the config.
		if ( window.MutationObserver && document.body ) {
			new MutationObserver( rescan ).observe( document.body, {
				subtree: true,
				childList: true,
				attributes: true,
				attributeFilter: [ 'class' ]
			} );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
		window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', rescan );
	}
} )();
