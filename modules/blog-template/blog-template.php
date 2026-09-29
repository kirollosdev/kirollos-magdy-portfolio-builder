<?php
/**
 * Blog Template module (formerly the standalone "KM Blog Template" plugin, v1.0.0).
 *
 * Article template for blog posts in the kirollosmagdy.com style: dark hero, featured image,
 * reading column with a sticky table of contents, reading progress, author box, related articles
 * and a call to action. Posts are still written in the normal WordPress editor. Posts built with
 * Elementor are left alone. Loaded by the main plugin file.
 *
 * @package KM_Blog_Template
 */

defined( 'ABSPATH' ) || exit;

// Follows the plugin version so asset URLs change, and caches refresh, with every release.
define( 'KMBT_VERSION', KMPB_VERSION );
define( 'KMBT_URL', plugin_dir_url( __FILE__ ) );
define( 'KMBT_DIR', plugin_dir_path( __FILE__ ) );

/**
 * True on a single blog post that should use this template.
 */
function kmbt_applies() {
	if ( ! is_singular( 'post' ) || is_embed() ) {
		return false;
	}
	return 'builder' !== get_post_meta( get_queried_object_id(), '_elementor_edit_mode', true );
}

add_filter(
	'template_include',
	static function ( $template ) {
		return kmbt_applies() ? KMBT_DIR . 'templates/single.php' : $template;
	},
	99
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! kmbt_applies() ) {
			return;
		}
		wp_enqueue_style( 'kmbt-font', 'https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;500;700&display=swap', array(), null );
		wp_enqueue_style( 'kmbt', KMBT_URL . 'assets/blog.css', array(), KMBT_VERSION );
		wp_enqueue_script(
			'kmbt',
			KMBT_URL . 'assets/blog.js',
			array(),
			KMBT_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
);

// Preconnect for the heading font.
add_filter(
	'wp_resource_hints',
	static function ( $urls, $relation ) {
		if ( 'preconnect' === $relation && kmbt_applies() ) {
			$urls[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
		}
		return $urls;
	},
	10,
	2
);

/*
 * Kadence: transparent header over the dark hero, as on the homepage and project pages.
 */
add_filter(
	'get_post_metadata',
	static function ( $value, $object_id, $meta_key ) {
		if ( '_kad_post_transparent' === $meta_key && ! is_admin() && is_singular( 'post' ) && (int) $object_id === get_queried_object_id() && kmbt_applies() ) {
			return array( 'enable' );
		}
		return $value;
	},
	10,
	3
);

/*
 * Helpers used by the template.
 */

/**
 * The author's full name from their profile (First Name + Last Name), falling back to the
 * display name when both are empty, so the username never shows on articles.
 */
function kmbt_author_name( $user_id ) {
	$name = trim( get_the_author_meta( 'first_name', $user_id ) . ' ' . get_the_author_meta( 'last_name', $user_id ) );
	return '' !== $name ? $name : get_the_author_meta( 'display_name', $user_id );
}

function kmbt_reading_minutes( $post ) {
	$words = str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
	return max( 1, (int) ceil( $words / 220 ) );
}

function kmbt_blog_url() {
	$page = (int) get_option( 'page_for_posts' );
	return $page ? get_permalink( $page ) : home_url( '/blog/' );
}

/**
 * The post's main category (Yoast primary category when set).
 */
function kmbt_primary_category( $post_id ) {
	$primary = (int) get_post_meta( $post_id, '_yoast_wpseo_primary_category', true );
	$term    = $primary ? get_term( $primary, 'category' ) : null;
	if ( $term && ! is_wp_error( $term ) ) {
		return $term;
	}
	$terms = get_the_category( $post_id );
	foreach ( $terms as $term ) {
		if ( 'uncategorized' !== $term->slug ) {
			return $term;
		}
	}
	return $terms ? $terms[0] : null;
}

/**
 * Give every H2/H3 in the content an id and return [ content, table of contents ].
 * The table of contents starts at the highest heading level the post actually uses.
 */
function kmbt_toc( $html ) {
	$items = array();
	$used  = array();
	$html  = preg_replace_callback(
		'#<h([23])([^>]*)>(.*?)</h\1>#is',
		static function ( $m ) use ( &$items, &$used ) {
			$text = trim( wp_strip_all_tags( $m[3] ) );
			if ( '' === $text ) {
				return $m[0];
			}
			$attrs = $m[2];
			if ( preg_match( '/\sid=["\']([^"\']+)["\']/i', $attrs, $id_match ) ) {
				$id = $id_match[1];
			} else {
				$base = sanitize_title( $text );
				$base = '' !== $base ? $base : 'section';
				$id   = $base;
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n++;
				}
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}
			$used[ $id ] = true;
			$items[]     = array(
				'level' => (int) $m[1],
				'id'    => $id,
				'text'  => $text,
			);
			return '<h' . $m[1] . $attrs . '>' . $m[3] . '</h' . $m[1] . '>';
		},
		$html
	);

	if ( $items ) {
		$top = min( wp_list_pluck( $items, 'level' ) );
		foreach ( $items as &$item ) {
			$item['depth'] = $item['level'] - $top;
		}
		unset( $item );
	}
	return array( $html, $items );
}

function kmbt_toc_list( array $items ) {
	$html = '<ol class="kmbt-toc__list">';
	foreach ( $items as $item ) {
		$html .= sprintf(
			'<li class="kmbt-toc__item kmbt-toc__item--d%1$d"><a href="#%2$s">%3$s</a></li>',
			(int) $item['depth'],
			esc_attr( $item['id'] ),
			esc_html( $item['text'] )
		);
	}
	return $html . '</ol>';
}

/**
 * Up to three published posts: same category first, then the most recent.
 */
function kmbt_related( $post, $count = 3 ) {
	$cats = wp_get_post_categories( $post->ID );
	$base = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	$ids  = $cats ? get_posts(
		$base + array(
			'category__in'   => $cats,
			'post__not_in'   => array( $post->ID ),
			'posts_per_page' => $count,
			'fields'         => 'ids',
		)
	) : array();
	if ( count( $ids ) < $count ) {
		$ids = array_merge(
			$ids,
			get_posts(
				$base + array(
					'post__not_in'   => array_merge( array( $post->ID ), $ids ),
					'posts_per_page' => $count - count( $ids ),
					'fields'         => 'ids',
				)
			)
		);
	}
	return array_map( 'get_post', $ids );
}

function kmbt_share_links( $post ) {
	$url   = rawurlencode( get_permalink( $post ) );
	$title = rawurlencode( get_the_title( $post ) );
	return array(
		'LinkedIn' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url,
		'X'        => 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title,
		'Facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
		'WhatsApp' => 'https://wa.me/?text=' . $title . '%20' . $url,
	);
}

function kmbt_share( $post, $modifier = '' ) {
	$html = '<div class="kmbt-share' . ( $modifier ? ' kmbt-share--' . esc_attr( $modifier ) : '' ) . '">';
	foreach ( kmbt_share_links( $post ) as $name => $href ) {
		$html .= sprintf(
			'<a class="kmbt-share__btn" href="%1$s" target="_blank" rel="noopener nofollow" aria-label="%2$s">%3$s</a>',
			esc_url( $href ),
			/* translators: %s: network name */
			esc_attr( sprintf( __( 'Share on %s', 'km-blog-template' ), $name ) ),
			esc_html( $name )
		);
	}
	$html .= sprintf(
		'<button type="button" class="kmbt-share__btn kmbt-share__copy" data-url="%1$s" data-done="%2$s">%3$s</button>',
		esc_url( get_permalink( $post ) ),
		esc_attr__( 'Link copied', 'km-blog-template' ),
		esc_html__( 'Copy link', 'km-blog-template' )
	);
	return $html . '</div>';
}
