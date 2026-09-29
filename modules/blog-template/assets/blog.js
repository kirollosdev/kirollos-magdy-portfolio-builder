/* KM Blog Template: reading progress, table-of-contents highlight, copy link. */
( function () {
	'use strict';

	var article = document.querySelector( '.kmbt-content' );
	if ( ! article ) {
		return;
	}

	/* Reading progress: scaled with transform, updated once per frame. */
	var bar = document.querySelector( '.kmbt-progress__bar' );
	if ( bar ) {
		var ticking = false;
		var update = function () {
			var rect = article.getBoundingClientRect();
			var total = rect.height - window.innerHeight * 0.6;
			var done = total > 0 ? Math.min( 1, Math.max( 0, -rect.top / total ) ) : 1;
			bar.style.transform = 'scaleX(' + done.toFixed( 4 ) + ')';
			ticking = false;
		};
		var request = function () {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		};
		window.addEventListener( 'scroll', request, { passive: true } );
		window.addEventListener( 'resize', request );
		update();
	}

	/* Table of contents: mark the section being read. */
	var links = Array.prototype.slice.call( document.querySelectorAll( '.kmbt-toc--side .kmbt-toc__item a' ) );
	if ( links.length && 'IntersectionObserver' in window ) {
		var byId = {};
		links.forEach( function ( link ) {
			byId[ decodeURIComponent( link.hash.slice( 1 ) ) ] = link;
		} );
		var headings = Object.keys( byId ).map( function ( id ) {
			return document.getElementById( id );
		} ).filter( Boolean );

		var setActive = function ( id ) {
			links.forEach( function ( link ) {
				var on = link === byId[ id ];
				link.classList.toggle( 'is-active', on );
				if ( on ) {
					link.setAttribute( 'aria-current', 'location' );
				} else {
					link.removeAttribute( 'aria-current' );
				}
			} );
		};

		var observer = new IntersectionObserver( function () {
			// The active section is the last heading that has scrolled above 35% of the viewport.
			var line = window.innerHeight * 0.35;
			var current = headings[ 0 ];
			headings.forEach( function ( h ) {
				if ( h.getBoundingClientRect().top <= line ) {
					current = h;
				}
			} );
			setActive( current.id );
		}, { rootMargin: '0px 0px -60% 0px', threshold: [ 0, 1 ] } );

		headings.forEach( function ( h ) {
			observer.observe( h );
		} );
		setActive( headings[ 0 ] && headings[ 0 ].id );
	}

	/* Copy link. */
	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.kmbt-share__copy' );
		if ( ! btn || ! navigator.clipboard ) {
			return;
		}
		navigator.clipboard.writeText( btn.getAttribute( 'data-url' ) ).then( function () {
			var label = btn.textContent;
			btn.textContent = btn.getAttribute( 'data-done' );
			btn.classList.add( 'is-done' );
			window.setTimeout( function () {
				btn.textContent = label;
				btn.classList.remove( 'is-done' );
			}, 2000 );
		} );
	} );
}() );
