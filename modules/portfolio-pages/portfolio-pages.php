<?php
/**
 * Portfolio Pages module (formerly the standalone "KM Portfolio Pages" plugin, v2.2.3).
 *
 * Builds an Elementor case-study page for every Portfolio project from the project's own title,
 * excerpt, content, logo, gallery, website link, date and terms. Go to Portfolios > Elementor Pages.
 * Loaded by the main plugin file, which also owns this module's activation and deactivation hooks.
 *
 * @package KM_Portfolio_Pages
 */

defined( 'ABSPATH' ) || exit;

define( 'KMPP_POST_TYPE', 'portfolios' );
define( 'KMPP_BACKUP_KEY', '_kmpp_backup_elementor_data' );

/**
 * Design tokens, matched to kirollosmagdy.com.
 */
function kmpp_tokens() {
	return array(
		'font'     => 'Comfortaa',
		'accent'   => '#9D00FF', // Buttons and highlighted heading words.
		'deep'     => '#3C0061', // Heading base colour and the hero glow.
		'lilac'    => '#C9A6FF', // Accent text on dark backgrounds.
		'ink'      => '#0B0B12',
		'muted'    => '#474747',
		'subtle'   => '#8A8A99',
		'line'     => '#E4E9F0',
		'surface'  => '#F8F7FC',
		'radius'   => 18,
		'width'    => 1140,
	);
}

/*
 * Setup: let Elementor edit the portfolio post type, and flush the stale rewrite rules
 * that were making /portfolio/<slug>/ return 404.
 */
define( 'KMPP_REWRITE_VERSION', '2' );

// Activation and deactivation reset 'kmpp_rewrite_version'; see the hooks in kirollos-magdy-portfolio-builder.php.

/*
 * The post type archive shares the /portfolio/ URL with the Elementor "Portfolio" page and
 * wins once rewrite rules are fresh. Turn the archive off so the page keeps /portfolio/;
 * single projects stay at /portfolio/<slug>/.
 */
add_filter(
	'register_post_type_args',
	static function ( $args, $post_type ) {
		if ( KMPP_POST_TYPE === $post_type ) {
			$args['has_archive'] = false;
		}
		return $args;
	},
	10,
	2
);

add_action(
	'init',
	static function () {
		if ( ! post_type_exists( KMPP_POST_TYPE ) ) {
			return;
		}
		add_post_type_support( KMPP_POST_TYPE, array( 'elementor', 'editor' ) );

		$cpt_support = get_option( 'elementor_cpt_support', array( 'post', 'page' ) );
		if ( is_array( $cpt_support ) && ! in_array( KMPP_POST_TYPE, $cpt_support, true ) ) {
			$cpt_support[] = KMPP_POST_TYPE;
			update_option( 'elementor_cpt_support', $cpt_support );
		}

		if ( KMPP_REWRITE_VERSION !== get_option( 'kmpp_rewrite_version' ) ) {
			update_option( 'kmpp_rewrite_version', KMPP_REWRITE_VERSION );
			delete_option( 'kmpp_flush_rewrites' );
			flush_rewrite_rules( false );
		}
	},
	99
);

/*
 * Related projects: [kmpp_related post="123" count="3"].
 * Same card as the homepage "Latest projects" (logo, screen collage, title, terms, link, date),
 * plus the project description. Rendered live, so new projects show up without rebuilding pages.
 */
add_shortcode(
	'kmpp_related',
	static function ( $atts ) {
		$atts = shortcode_atts(
			array(
				'post'  => get_the_ID(),
				'count' => 3,
			),
			$atts,
			'kmpp_related'
		);
		$post = get_post( (int) $atts['post'] );
		if ( ! $post ) {
			return '';
		}
		$items = kmpp_related( $post, max( 1, min( 6, (int) $atts['count'] ) ) );
		if ( ! $items ) {
			return '';
		}

		static $printed_css = false;
		$html = '';
		if ( ! $printed_css ) {
			$printed_css = true;
			$html       .= '<style id="kmpp-related-css">' . kmpp_related_css() . '</style>';
		}

		$arrow = '<svg viewBox="0 0 16 16" width="12" height="12" aria-hidden="true" focusable="false"><path d="M5 11l6-6M6 5h5v5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		$html .= '<div class="kmpp-rel">';
		foreach ( $items as $item ) {
			$link                 = get_permalink( $item );
			$title                = get_the_title( $item );
			list( $logo, $shots ) = kmpp_project_images( $item );
			$shots                = array_slice( $shots, 0, count( $shots ) >= 4 ? 4 : min( 2, count( $shots ) ) );
			$website              = kmpp_project_url( $item->ID );
			$date                 = kmpp_project_date( $item->ID );
			$terms                = array_merge( kmpp_term_names( $item->ID, 'portfolio_industry' ), kmpp_term_names( $item->ID, 'portfolio_service' ) );

			$cover = '';
			if ( $logo && ! $shots ) {
				// No screens yet: the logo alone, centred in a panel as tall as the logo row plus the collage.
				$cover .= '<span class="kmpp-rel__solo"><span class="kmpp-rel__solo-in">' . wp_get_attachment_image( $logo, 'medium', false, array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ) . '</span></span>';
			} elseif ( $logo ) {
				$cover .= '<span class="kmpp-rel__logo">' . wp_get_attachment_image( $logo, 'medium', false, array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ) . '</span>';
			}
			if ( $shots ) {
				$cover .= '<span class="kmpp-rel__shots" data-count="' . count( $shots ) . '">';
				foreach ( $shots as $id ) {
					$cover .= '<span class="kmpp-rel__shot">' . wp_get_attachment_image( $id, 'medium_large', false, array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ) . '</span>';
				}
				$cover .= '</span>';
			}

			$html .= '<article class="kmpp-rel__card">';
			$html .= sprintf(
				'<a class="kmpp-rel__cover" href="%1$s" tabindex="-1" aria-hidden="true">%2$s</a>',
				esc_url( $link ),
				$cover
			);
			$html .= '<div class="kmpp-rel__body">';
			$html .= sprintf( '<h3 class="kmpp-rel__title"><a href="%1$s">%2$s</a></h3>', esc_url( $link ), esc_html( $title ) );
			if ( has_excerpt( $item ) ) {
				$html .= '<p class="kmpp-rel__desc">' . esc_html( get_the_excerpt( $item ) ) . '</p>';
			}
			if ( $terms ) {
				$html .= '<p class="kmpp-rel__terms">' . esc_html( implode( ' • ', $terms ) ) . '</p>';
			}
			if ( $website ) {
				$html .= sprintf( '<a class="kmpp-rel__url" href="%1$s" target="_blank" rel="noopener">%2$s</a>', esc_url( $website[0] ), esc_html( $website[1] ) );
			}
			$html .= sprintf(
				'<div class="kmpp-rel__foot"><a class="kmpp-rel__btn" href="%1$s" aria-label="%2$s">%3$s %4$s</a>%5$s</div>',
				esc_url( $link ),
				/* translators: %s: project title */
				esc_attr( sprintf( __( 'View project: %s', 'km-portfolio-pages' ), $title ) ),
				esc_html__( 'View Project', 'km-portfolio-pages' ),
				$arrow,
				$date ? '<span class="kmpp-rel__date">' . esc_html( $date ) . '</span>' : ''
			);
			$html .= '</div></article>';
		}
		return $html . '</div>';
	}
);

/**
 * Card styles. The purple border, lift and glow appear on hover or keyboard focus only.
 * Motion uses one long ease-out curve on transform/opacity alone, so it stays smooth.
 */
function kmpp_related_css() {
	$t = kmpp_tokens();
	return '
.kmpp-rel{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:24px;align-items:stretch}
.kmpp-rel__card{--kmpp-ease:cubic-bezier(.22,1,.36,1);position:relative;display:flex;flex-direction:column;background:#fff;border:1px solid ' . $t['line'] . ';border-radius:16px;overflow:hidden;transform:translateZ(0);transition:transform .6s var(--kmpp-ease),border-color .45s var(--kmpp-ease),box-shadow .6s var(--kmpp-ease)}
.kmpp-rel__card:hover,.kmpp-rel__card:focus-within{transform:translateY(-6px);border-color:' . $t['accent'] . ';box-shadow:0 26px 50px -30px rgba(157,0,255,.5)}
.kmpp-rel__cover{display:block;text-decoration:none}
.kmpp-rel__logo{display:flex;align-items:center;height:72px;padding:14px 20px}
.kmpp-rel__logo img{display:block;width:auto;height:auto;max-height:44px;max-width:150px;object-fit:contain}
.kmpp-rel__shots{display:grid;grid-template-columns:1fr 1fr;gap:2px;background:' . $t['line'] . ';overflow:hidden}
.kmpp-rel__shots[data-count="1"]{grid-template-columns:1fr}
.kmpp-rel__shot{display:block;aspect-ratio:16/10;overflow:hidden;background:' . $t['surface'] . '}
.kmpp-rel__solo{display:block;padding:36px 0;background:' . $t['surface'] . '}
.kmpp-rel__solo-in{display:flex;align-items:center;justify-content:center;aspect-ratio:16/10}
.kmpp-rel__solo-in img{display:block;width:auto;height:auto;max-width:55%;max-height:60%;object-fit:contain;transition:transform .9s var(--kmpp-ease)}
.kmpp-rel__card:hover .kmpp-rel__solo-in img{transform:scale(1.04)}
.kmpp-rel__shots[data-count="2"] .kmpp-rel__shot{aspect-ratio:4/5}
.kmpp-rel__shot img{display:block;width:100%;height:100%;object-fit:cover;object-position:top center;transition:transform .9s var(--kmpp-ease)}
.kmpp-rel__card:hover .kmpp-rel__shot img{transform:scale(1.04)}
.kmpp-rel__body{display:flex;flex-direction:column;flex:1;padding:20px 20px 22px}
.kmpp-rel .kmpp-rel__title{margin:0 0 8px;font-size:19px;line-height:1.3;font-weight:700}
.kmpp-rel__title a{color:' . $t['ink'] . ';text-decoration:none;transition:color .3s ease}
.kmpp-rel__title a:hover{color:' . $t['accent'] . '}
.kmpp-rel .kmpp-rel__desc{margin:0 0 10px;font-size:14.5px;line-height:1.6;color:' . $t['muted'] . '}
.kmpp-rel .kmpp-rel__terms{margin:0 0 10px;font-size:13px;line-height:1.6;color:' . $t['subtle'] . '}
.kmpp-rel__url{align-self:flex-start;font-size:13px;color:' . $t['muted'] . ';text-decoration:none;border-bottom:1px dashed currentColor;padding-bottom:1px;transition:color .3s ease}
.kmpp-rel__url:hover{color:' . $t['accent'] . '}
.kmpp-rel__foot{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:auto;padding-top:18px}
.kmpp-rel__btn{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border-radius:999px;background:' . $t['accent'] . ';color:#fff !important;font-size:14px;font-weight:700;line-height:1;text-decoration:none;transition:background-color .3s ease}
.kmpp-rel__btn:hover{background:' . $t['deep'] . '}
.kmpp-rel__btn svg{transition:transform .5s var(--kmpp-ease)}
.kmpp-rel__btn:hover svg{transform:translate(2px,-2px)}
.kmpp-rel__btn:focus-visible,.kmpp-rel__title a:focus-visible,.kmpp-rel__url:focus-visible{outline:2px solid ' . $t['accent'] . ';outline-offset:3px}
.kmpp-rel__date{font-size:13px;font-weight:600;color:' . $t['subtle'] . ';white-space:nowrap}
@media (max-width:1024px){.kmpp-rel{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:640px){.kmpp-rel{grid-template-columns:1fr;gap:20px}}
@media (prefers-reduced-motion:reduce){.kmpp-rel__card,.kmpp-rel__shot img,.kmpp-rel__btn svg{transition:border-color .2s ease,box-shadow .2s ease}.kmpp-rel__card:hover,.kmpp-rel__card:focus-within,.kmpp-rel__card:hover .kmpp-rel__shot img,.kmpp-rel__btn:hover svg{transform:none}}
';
}

/*
 * Lightbox arrows. On this site (Elementor 4.3) the slideshow's prev/next buttons lose their
 * positioning and render below the screen, so the arrows never show. This puts them back:
 * round glass buttons at the left and right edges, purple on hover.
 */
add_action(
	'wp_head',
	static function () {
		?>
		<style id="kmpp-lightbox-css">
			.elementor-lightbox .elementor-swiper-button{position:absolute;top:50%;z-index:10;display:flex;align-items:center;justify-content:center;box-sizing:border-box;width:52px!important;height:52px!important;min-width:0!important;min-height:0!important;padding:0!important;margin:-26px 0 0!important;border-radius:50%;background:rgba(255,255,255,.12);-webkit-backdrop-filter:blur(8px);backdrop-filter:blur(8px);color:#fff;font-size:20px;cursor:pointer;opacity:1!important;transition:background-color .3s ease,opacity .3s ease}
			.elementor-lightbox .elementor-swiper-button-prev{left:24px;right:auto}
			.elementor-lightbox .elementor-swiper-button-next{right:24px;left:auto}
			.elementor-lightbox .elementor-swiper-button svg{width:20px!important;height:20px!important;fill:currentColor}
			.elementor-lightbox .elementor-swiper-button:hover{background:#9D00FF}
			.elementor-lightbox .elementor-swiper-button:focus-visible{outline:2px solid #fff;outline-offset:3px}
			.elementor-lightbox .elementor-swiper-button.swiper-button-disabled{opacity:.3!important;cursor:default}
			@media (max-width:767px){.elementor-lightbox .elementor-swiper-button{width:40px!important;height:40px!important;margin:-20px 0 0!important}.elementor-lightbox .elementor-swiper-button-prev{left:8px}.elementor-lightbox .elementor-swiper-button-next{right:8px}}
		</style>
		<?php
	}
);

/*
 * Elementor layout helpers.
 */
function kmpp_id() {
	return substr( md5( wp_generate_uuid4() ), 0, 7 );
}

function kmpp_px( $size, $unit = 'px' ) {
	return array(
		'unit'  => $unit,
		'size'  => $size,
		'sizes' => array(),
	);
}

function kmpp_box( $top, $right, $bottom, $left ) {
	return array(
		'unit'     => 'px',
		'top'      => (string) $top,
		'right'    => (string) $right,
		'bottom'   => (string) $bottom,
		'left'     => (string) $left,
		'isLinked' => false,
	);
}

function kmpp_round( $r ) {
	$box             = kmpp_box( $r, $r, $r, $r );
	$box['isLinked'] = true;
	return $box;
}

/**
 * Typography settings. Large text gets tighter tracking and leading, small text looser.
 */
function kmpp_type( $size, $weight = '400', $mobile = null, $line_height = null, $tracking = null ) {
	$t = kmpp_tokens();
	$s = array(
		'typography_typography'  => 'custom',
		'typography_font_family' => $t['font'],
		'typography_font_size'   => kmpp_px( $size ),
		'typography_font_weight' => $weight,
	);
	if ( null !== $mobile ) {
		$s['typography_font_size_mobile'] = kmpp_px( $mobile );
	}
	if ( null !== $line_height ) {
		$s['typography_line_height'] = kmpp_px( $line_height, 'em' );
	}
	if ( null !== $tracking ) {
		$s['typography_letter_spacing'] = kmpp_px( $tracking );
	}
	return $s;
}

function kmpp_widget( $type, array $settings ) {
	return array(
		'id'         => kmpp_id(),
		'elType'     => 'widget',
		'widgetType' => $type,
		'settings'   => $settings,
		'elements'   => array(),
	);
}

function kmpp_column( $size, array $widgets, array $settings = array() ) {
	return array(
		'id'       => kmpp_id(),
		'elType'   => 'column',
		'settings' => array_merge(
			array(
				'_column_size' => $size,
				'_inline_size' => 100 === $size ? null : $size,
			),
			$settings
		),
		'elements' => array_values( array_filter( $widgets ) ),
	);
}

function kmpp_section( array $columns, array $settings = array() ) {
	$t = kmpp_tokens();
	return array(
		'id'       => kmpp_id(),
		'elType'   => 'section',
		'settings' => array_merge(
			array(
				'layout'         => 'boxed',
				'content_width'  => kmpp_px( $t['width'] ),
				'gap'            => 'extended',
				'padding'        => kmpp_box( 50, 20, 50, 20 ),
				'padding_mobile' => kmpp_box( 32, 16, 32, 16 ),
			),
			$settings
		),
		'elements' => $columns,
		'isInner'  => false,
	);
}

/**
 * Dark section with the purple glow used by the homepage hero.
 */
function kmpp_dark_bg() {
	$t = kmpp_tokens();
	return array(
		'background_background'     => 'gradient',
		'background_color'          => $t['deep'],
		'background_color_stop'     => kmpp_px( 0, '%' ),
		'background_color_b'        => '#000000',
		'background_color_b_stop'   => kmpp_px( 70, '%' ),
		'background_gradient_type'  => 'radial',
		'background_gradient_position' => 'top right',
	);
}

function kmpp_heading( $text, $tag, $size, $mobile, array $extra = array() ) {
	$t = kmpp_tokens();
	return kmpp_widget(
		'heading',
		array_merge(
			array(
				'title'       => $text,
				'header_size' => $tag,
				'title_color' => $t['ink'],
			),
			kmpp_type( $size, '700', $mobile, $size >= 36 ? 1.15 : 1.3, $size >= 36 ? -0.5 : null ),
			$extra
		)
	);
}

/**
 * Centered section title in the homepage style: uppercase, wide tracking, one word in purple.
 */
function kmpp_section_title( $before, $highlight, $after = '', $intro = '' ) {
	$t     = kmpp_tokens();
	$title = trim( esc_html( $before ) . ' <span style="color:' . esc_attr( $t['accent'] ) . '">' . esc_html( $highlight ) . '</span> ' . esc_html( $after ) );
	$out   = array(
		kmpp_widget(
			'heading',
			array_merge(
				array(
					'title'                     => $title,
					'header_size'               => 'h2',
					'align'                     => 'center',
					'title_color'               => $t['deep'],
					'typography_text_transform' => 'uppercase',
				),
				kmpp_type( 36, '500', 24, 1.25, 4 )
			)
		),
	);
	if ( $intro ) {
		$out[] = kmpp_text( '<p>' . esc_html( $intro ) . '</p>', 16, array( 'align' => 'center' ) );
	}
	return $out;
}

function kmpp_text( $html, $size = 17, array $extra = array() ) {
	$t = kmpp_tokens();
	return kmpp_widget(
		'text-editor',
		array_merge(
			array(
				'editor'     => $html,
				'text_color' => $t['muted'],
			),
			kmpp_type( $size, '400', min( $size, 16 ), 1.75 ),
			$extra
		)
	);
}

/**
 * Small uppercase label: section eyebrows and snapshot field names.
 */
function kmpp_label( $text, $color = null, $margin = 8 ) {
	$t = kmpp_tokens();
	return kmpp_widget(
		'heading',
		array_merge(
			array(
				'title'                     => esc_html( $text ),
				'header_size'               => 'div',
				'title_color'               => $color ? $color : $t['accent'],
				'typography_text_transform' => 'uppercase',
				'_margin'                   => kmpp_box( 0, 0, $margin, 0 ),
			),
			kmpp_type( 12, '700', null, 1.4, 1.5 )
		)
	);
}

/**
 * Pill button in the site style. $variant: solid (purple), ghost (outline on dark), light (white on dark).
 */
function kmpp_button( $text, $url, $variant = 'solid', $external = false, array $extra = array() ) {
	$t      = kmpp_tokens();
	$styles = array(
		'solid' => array(
			'background_color'              => $t['accent'],
			'button_text_color'             => '#FFFFFF',
			'button_background_hover_color' => $t['deep'],
			'hover_color'                   => '#FFFFFF',
		),
		'ghost' => array(
			'background_color'              => 'rgba(255,255,255,0)',
			'button_text_color'             => '#FFFFFF',
			'border_border'                 => 'solid',
			'border_width'                  => kmpp_round( 1 ),
			'border_color'                  => 'rgba(255,255,255,0.35)',
			'button_background_hover_color' => '#FFFFFF',
			'hover_color'                   => $t['ink'],
		),
	);
	$link = array( 'url' => $url );
	if ( $external ) {
		$link['is_external'] = 'on';
	}
	return kmpp_widget(
		'button',
		array_merge(
			array(
				'text'          => $text,
				'link'          => $link,
				'size'          => 'md',
				'border_radius' => kmpp_round( 999 ),
				'text_padding'  => kmpp_box( 15, 30, 15, 30 ),
			),
			$styles[ $variant ],
			kmpp_type( 15, '700' ),
			$extra
		)
	);
}

function kmpp_image_widget( $attachment_id, $size, array $extra = array() ) {
	$url = wp_get_attachment_image_url( $attachment_id, 'full' );
	if ( ! $url ) {
		return null;
	}
	return kmpp_widget(
		'image',
		array_merge(
			array(
				'image'      => array(
					'url' => $url,
					'id'  => (int) $attachment_id,
					'alt' => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				),
				'image_size' => $size,
				'align'      => 'left',
			),
			$extra
		)
	);
}

/**
 * A project screenshot: rounded, hairline border, soft purple shadow, opens full size in the lightbox.
 * Screens that share a $group open as one lightbox slideshow, with arrows to move between them.
 */
function kmpp_shot( $attachment_id, $size = 'large', array $extra = array(), $group = '' ) {
	$t    = kmpp_tokens();
	$link = array(
		'url'               => (string) wp_get_attachment_image_url( $attachment_id, 'full' ),
		'custom_attributes' => 'data-elementor-open-lightbox|yes' . ( $group ? ',data-elementor-lightbox-slideshow|' . $group : '' ),
	);
	return kmpp_image_widget(
		$attachment_id,
		$size,
		array_merge(
			array(
				'width'                            => kmpp_px( 100, '%' ),
				'link_to'                          => 'custom',
				'link'                             => $link,
				'open_lightbox'                    => 'yes',
				'image_border_radius'              => kmpp_round( $t['radius'] ),
				'image_border_border'              => 'solid',
				'image_border_width'               => kmpp_round( 1 ),
				'image_border_color'               => $t['line'],
				'image_box_shadow_box_shadow_type' => 'yes',
				'image_box_shadow_box_shadow'      => array(
					'horizontal' => 0,
					'vertical'   => 24,
					'blur'       => 60,
					'spread'     => -12,
					'color'      => 'rgba(60,0,97,0.18)',
				),
			),
			$extra
		)
	);
}

/**
 * The client logo at its natural proportions, about 64px tall and never upscaled.
 */
function kmpp_logo( $attachment_id ) {
	$meta = wp_get_attachment_metadata( $attachment_id );
	$w    = ! empty( $meta['width'] ) ? (int) $meta['width'] : 200;
	$h    = ! empty( $meta['height'] ) ? (int) $meta['height'] : 64;
	$size = (int) min( 200, $w, round( $w * 64 / max( 1, $h ) ) );
	return kmpp_image_widget(
		$attachment_id,
		'medium',
		array(
			'width'   => kmpp_px( $size ),
			'_margin' => kmpp_box( 0, 0, 24, 0 ),
		)
	);
}

function kmpp_pills( array $names ) {
	$t    = kmpp_tokens();
	$html = '';
	foreach ( $names as $name ) {
		$html .= sprintf(
			'<span style="display:inline-block;margin:0 6px 8px 0;padding:5px 12px;border-radius:999px;background:#FFFFFF;border:1px solid %s;color:%s;font-size:13px;line-height:1.4;">%s</span>',
			esc_attr( $t['line'] ),
			esc_attr( $t['deep'] ),
			esc_html( $name )
		);
	}
	return $html;
}

/*
 * Project data.
 */

/**
 * Read a field saved by the KM Portfolio Builder meta boxes (project_url, work_date, gallery),
 * whatever prefix the builder stores it under.
 */
function kmpp_field( $post_id, $name ) {
	$all = get_post_meta( $post_id );
	foreach ( array( "_kmpb_{$name}", "kmpb_{$name}", "_kmpb_portfolio_{$name}", "_{$name}", $name ) as $key ) {
		if ( isset( $all[ $key ][0] ) && '' !== $all[ $key ][0] ) {
			return maybe_unserialize( $all[ $key ][0] );
		}
	}
	foreach ( $all as $key => $values ) {
		if ( 0 !== strpos( $key, '_elementor' ) && substr( $key, -strlen( $name ) ) === $name && isset( $values[0] ) && '' !== $values[0] ) {
			return maybe_unserialize( $values[0] );
		}
	}
	return '';
}

/**
 * Live website as [ href, label ], or null.
 */
function kmpp_project_url( $post_id ) {
	$url = trim( (string) kmpp_field( $post_id, 'project_url' ) );
	if ( '' === $url ) {
		return null;
	}
	$href  = preg_match( '#^https?://#i', $url ) ? $url : 'https://' . $url;
	$label = preg_replace( '#^(https?://)?(www\.)?#i', '', untrailingslashit( $url ) );
	return array( esc_url_raw( $href ), $label );
}

/**
 * Completion month, e.g. "July 2026", from the builder's YYYY-MM work date.
 */
function kmpp_project_date( $post_id ) {
	$date = trim( (string) kmpp_field( $post_id, 'work_date' ) );
	if ( preg_match( '/^(\d{4})-(\d{1,2})/', $date, $m ) ) {
		return date_i18n( 'F Y', mktime( 0, 0, 0, (int) $m[2], 1, (int) $m[1] ) );
	}
	return $date;
}

/**
 * The client name: the part of the title before " - " / " – ".
 */
function kmpp_client_name( WP_Post $post ) {
	$parts = preg_split( '/\s+[-–—]\s+/u', get_the_title( $post ), 2 );
	return trim( $parts[0] );
}

/**
 * Sort a project's images into its logo and its screenshots.
 * Screenshots come from the builder's gallery in its order; otherwise from the attached images at least 1000px wide.
 */
function kmpp_project_images( WP_Post $post ) {
	$featured = (int) get_post_thumbnail_id( $post );
	$gallery  = kmpp_field( $post->ID, 'gallery' );
	$gallery  = is_array( $gallery ) ? $gallery : explode( ',', (string) $gallery );
	$gallery  = array_values( array_filter( array_map( 'absint', $gallery ) ) );

	$candidates = $gallery;
	if ( ! $candidates ) {
		$attached = get_children(
			array(
				'post_parent'    => $post->ID,
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'fields'         => 'ids',
				'numberposts'    => -1,
			)
		);
		$titles   = array();
		foreach ( array_map( 'intval', $attached ) as $id ) {
			$titles[ $id ] = get_the_title( $id );
		}
		natcasesort( $titles );
		$candidates = array_keys( $titles );
	}

	$logo  = 0;
	$shots = array();
	foreach ( array_unique( array_merge( array( $featured ), $candidates ) ) as $id ) {
		if ( ! $id || ! wp_attachment_is_image( $id ) ) {
			continue;
		}
		$meta = wp_get_attachment_metadata( $id );
		$wide = ! empty( $meta['width'] ) && (int) $meta['width'] >= 1000;
		if ( $wide && ( $id !== $featured || ! $gallery ) ) {
			$shots[] = $id;
		} elseif ( ! $wide && ! $logo ) {
			$logo = $id;
		}
	}
	return array( $logo, array_values( array_unique( $shots ) ) );
}

function kmpp_term_names( $post_id, $taxonomy ) {
	$terms = get_the_terms( $post_id, $taxonomy );
	return ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'name' ) : array();
}

/**
 * Split post content into [ heading, html ] blocks on its <h2> tags.
 */
function kmpp_content_blocks( $content ) {
	$content = preg_replace( '/<!--\s*\/?wp:[^>]*-->/', '', (string) $content );
	$content = trim( $content );
	if ( '' === $content ) {
		return array();
	}

	$parts  = preg_split( '/<h2[^>]*>(.*?)<\/h2>/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
	$blocks = array();

	$intro = trim( array_shift( $parts ) );
	if ( '' !== wp_strip_all_tags( $intro ) ) {
		$blocks[] = array( __( 'Overview', 'km-portfolio-pages' ), $intro );
	}
	for ( $i = 0; $i < count( $parts ); $i += 2 ) {
		$heading = trim( wp_strip_all_tags( $parts[ $i ] ) );
		$body    = isset( $parts[ $i + 1 ] ) ? trim( $parts[ $i + 1 ] ) : '';
		if ( '' !== $heading || '' !== wp_strip_all_tags( $body ) ) {
			$blocks[] = array( $heading, $body );
		}
	}
	return $blocks;
}

/**
 * Up to three published projects to link to: same category first, then the most recent.
 */
function kmpp_related( WP_Post $post, $count = 3 ) {
	$ids   = array();
	$terms = wp_get_post_terms( $post->ID, 'portfolio_category', array( 'fields' => 'ids' ) );
	$base  = array(
		'post_type'      => KMPP_POST_TYPE,
		'post_status'    => 'publish',
		'posts_per_page' => $count,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	);
	if ( $terms && ! is_wp_error( $terms ) ) {
		$ids = get_posts(
			$base + array(
				'post__not_in' => array( $post->ID ),
				'tax_query'    => array(
					array(
						'taxonomy' => 'portfolio_category',
						'terms'    => $terms,
					),
				),
			)
		);
	}
	if ( count( $ids ) < $count ) {
		$more = get_posts(
			array_merge(
				$base,
				array(
					'posts_per_page' => $count - count( $ids ),
					'post__not_in'   => array_merge( array( $post->ID ), $ids ),
				)
			)
		);
		$ids  = array_merge( $ids, $more );
	}
	return array_map( 'get_post', $ids );
}

/*
 * The page.
 */

/**
 * Build the Elementor layout for one portfolio post.
 */
function kmpp_build_layout( WP_Post $post ) {
	$t          = kmpp_tokens();
	$title      = get_the_title( $post );
	$client     = kmpp_client_name( $post );
	$categories = kmpp_term_names( $post->ID, 'portfolio_category' );
	$services   = kmpp_term_names( $post->ID, 'portfolio_service' );
	$industries = kmpp_term_names( $post->ID, 'portfolio_industry' );
	$website    = kmpp_project_url( $post->ID );
	$date       = kmpp_project_date( $post->ID );
	$portfolio  = home_url( '/portfolio/' );
	$contact    = home_url( '/contact/' );

	list( $logo, $shots ) = kmpp_project_images( $post );
	$lead                 = $shots ? array_shift( $shots ) : 0;
	$slideshow            = 'kmpp-' . $post->ID;
	$sections             = array();

	/*
	 * 1. Hero — dark with the purple glow, under the transparent header like the homepage.
	 */
	$crumb_link = 'color:rgba(255,255,255,0.65);text-decoration:none;';
	$breadcrumb = sprintf(
		'<nav aria-label="%1$s"><a href="%2$s" style="%5$s">%3$s</a> <span aria-hidden="true" style="opacity:.4;margin:0 6px">/</span> <a href="%4$s" style="%5$s">%6$s</a> <span aria-hidden="true" style="opacity:.4;margin:0 6px">/</span> <span aria-current="page" style="color:#FFFFFF">%7$s</span></nav>',
		esc_attr__( 'Breadcrumb', 'km-portfolio-pages' ),
		esc_url( home_url( '/' ) ),
		esc_html__( 'Home', 'km-portfolio-pages' ),
		esc_url( $portfolio ),
		$crumb_link,
		esc_html__( 'Portfolio', 'km-portfolio-pages' ),
		esc_html( $client )
	);

	$hero = array(
		kmpp_text( $breadcrumb, 13, array( 'text_color' => 'rgba(255,255,255,0.65)', '_margin' => kmpp_box( 0, 0, 28, 0 ) ) ),
		$categories ? kmpp_label( implode( ' · ', $categories ), $t['lilac'], 14 ) : null,
		kmpp_heading(
			'<span style="display:block;max-width:900px">' . esc_html( $title ) . '</span>',
			'h1',
			52,
			32,
			array(
				'title_color' => '#FFFFFF',
				'_margin'     => kmpp_box( 0, 0, 20, 0 ),
			)
		),
	);
	if ( has_excerpt( $post ) ) {
		$hero[] = kmpp_text(
			'<p style="max-width:720px">' . esc_html( get_the_excerpt( $post ) ) . '</p>',
			19,
			array(
				'text_color' => 'rgba(255,255,255,0.75)',
				'_margin'    => kmpp_box( 0, 0, 16, 0 ),
			)
		);
	}
	// Buttons share one row (auto width); they wrap under each other on narrow screens.
	$inline = array(
		'_element_width' => 'auto',
		'_margin'        => kmpp_box( 8, 12, 0, 0 ),
		'_margin_mobile' => kmpp_box( 8, 10, 0, 0 ),
	);
	$hero[] = kmpp_button( __( 'Start a similar project', 'km-portfolio-pages' ), $contact, 'ghost', false, $inline );
	if ( $website ) {
		$hero[] = kmpp_button( __( 'Visit Website ↗', 'km-portfolio-pages' ), $website[0], 'solid', true, $inline );
	}

	$sections[] = kmpp_section(
		array( kmpp_column( 100, $hero ) ),
		array_merge(
			kmpp_dark_bg(),
			array(
				'padding'        => kmpp_box( 190, 20, $lead ? 240 : 110, 20 ),
				'padding_tablet' => kmpp_box( 160, 30, $lead ? 200 : 90, 30 ),
				'padding_mobile' => kmpp_box( 140, 16, $lead ? 120 : 70, 16 ),
			)
		)
	);

	/*
	 * 2. Lead screenshot, lifted over the bottom of the hero.
	 */
	if ( $lead ) {
		$sections[] = kmpp_section(
			array( kmpp_column( 100, array( kmpp_shot( $lead, 'full', array(), $slideshow ) ) ) ),
			array(
				'margin'         => kmpp_box( -180, 0, 0, 0 ),
				'margin_tablet'  => kmpp_box( -150, 0, 0, 0 ),
				'margin_mobile'  => kmpp_box( -90, 0, 0, 0 ),
				'padding'        => kmpp_box( 0, 20, 30, 20 ),
				'padding_mobile' => kmpp_box( 0, 16, 16, 16 ),
				'z_index'        => 2,
			)
		);
	}

	/*
	 * 3. Case study — project snapshot beside the story.
	 */
	$facts = array(
		$logo ? kmpp_logo( $logo ) : kmpp_heading( esc_html( $client ), 'div', 22, 20, array( '_margin' => kmpp_box( 0, 0, 20, 0 ) ) ),
	);
	$rows  = array(
		__( 'Industry', 'km-portfolio-pages' )  => $industries ? esc_html( implode( ', ', $industries ) ) : '',
		__( 'Completed', 'km-portfolio-pages' ) => esc_html( $date ),
		__( 'Website', 'km-portfolio-pages' )   => $website ? sprintf( '<a href="%s" target="_blank" rel="noopener" style="color:%s;text-decoration:underline;text-underline-offset:3px">%s ↗</a>', esc_url( $website[0] ), esc_attr( $t['accent'] ), esc_html( $website[1] ) ) : '',
		__( 'Services', 'km-portfolio-pages' )  => $services ? kmpp_pills( $services ) : '',
	);
	foreach ( array_filter( $rows ) as $name => $value ) {
		$facts[] = kmpp_widget( 'divider', array( 'color' => $t['line'], 'gap' => kmpp_px( 12 ) ) );
		$facts[] = kmpp_label( $name, $t['subtle'], 6 );
		$facts[] = kmpp_text( false === strpos( $value, '<span' ) ? '<p>' . $value . '</p>' : $value, 15, array( 'text_color' => $t['ink'] ) );
	}

	$story = array(
		kmpp_label( __( 'Case study', 'km-portfolio-pages' ) ),
	);
	$blocks = kmpp_content_blocks( $post->post_content );
	foreach ( $blocks as $n => $block ) {
		$story[] = kmpp_heading(
			'<span style="color:' . esc_attr( $t['accent'] ) . ';font-size:.55em;letter-spacing:2px;vertical-align:middle;margin-right:12px">' . sprintf( '%02d', $n + 1 ) . '</span>' . esc_html( $block[0] ),
			'h2',
			30,
			24,
			array( '_margin' => kmpp_box( 0 === $n ? 0 : 36, 0, 12, 0 ) )
		);
		$story[] = kmpp_text( $block[1], 17 );
	}
	if ( ! $blocks && has_excerpt( $post ) ) {
		$story[] = kmpp_text( '<p>' . esc_html( get_the_excerpt( $post ) ) . '</p>', 17 );
	}

	$sections[] = kmpp_section(
		array(
			kmpp_column(
				33,
				$facts,
				array(
					'background_background' => 'classic',
					'background_color'      => $t['surface'],
					'border_border'         => 'solid',
					'border_width'          => kmpp_round( 1 ),
					'border_color'          => $t['line'],
					'border_radius'         => kmpp_round( $t['radius'] ),
					'padding'               => kmpp_box( 32, 28, 24, 28 ),
					'margin'                => kmpp_box( 0, 24, 0, 0 ),
					'margin_mobile'         => kmpp_box( 0, 0, 32, 0 ),
					'content_position'      => 'top',
				)
			),
			kmpp_column( 66, $story, array( 'padding' => kmpp_box( 8, 0, 0, 24 ), 'padding_mobile' => kmpp_box( 0, 0, 0, 0 ) ) ),
		),
		array(
			'html_tag'       => 'article',
			'content_position' => 'top',
			'padding'        => kmpp_box( 60, 20, 60, 20 ),
		)
	);

	/*
	 * 4. Gallery — the remaining screens stacked at full size, then the live-site button.
	 */
	if ( $shots ) {
		$sections[] = kmpp_section(
			array( kmpp_column( 100, kmpp_section_title( __( 'A', 'km-portfolio-pages' ), __( 'closer', 'km-portfolio-pages' ), __( 'look', 'km-portfolio-pages' ), __( 'Key screens from the finished website. Click any screen to view it full size.', 'km-portfolio-pages' ) ) ) ),
			array(
				'background_background' => 'classic',
				'background_color'      => $t['surface'],
				'padding'               => kmpp_box( 80, 20, 10, 20 ),
				'padding_mobile'        => kmpp_box( 56, 16, 8, 16 ),
			)
		);
		// Every screen at full size, one under the other, inside the boxed width; each opens in the lightbox.
		$screens = array();
		foreach ( $shots as $id ) {
			$screens[] = kmpp_shot( $id, 'full', array( '_margin' => kmpp_box( 0, 0, 40, 0 ), '_margin_mobile' => kmpp_box( 0, 0, 24, 0 ) ), $slideshow );
		}
		if ( $website ) {
			$screens[] = kmpp_button(
				/* translators: %s: website host, e.g. seaqui.com */
				sprintf( __( 'Visit %s ↗', 'km-portfolio-pages' ), $website[1] ),
				$website[0],
				'solid',
				true,
				array( 'align' => 'center', '_margin' => kmpp_box( 16, 0, 0, 0 ) )
			);
		}
		$sections[] = kmpp_section(
			array( kmpp_column( 100, $screens ) ),
			array(
				'background_background' => 'classic',
				'background_color'      => $t['surface'],
				'padding'               => kmpp_box( 20, 20, 90, 20 ),
				'padding_mobile'        => kmpp_box( 8, 16, 56, 16 ),
			)
		);
	}

	/*
	 * 5. More projects — internal links to related case studies.
	 */
	if ( kmpp_related( $post ) ) {
		$sections[] = kmpp_section(
			array(
				kmpp_column(
					100,
					array_merge(
						kmpp_section_title( __( 'More', 'km-portfolio-pages' ), __( 'projects', 'km-portfolio-pages' ) ),
						array(
							kmpp_widget( 'shortcode', array( 'shortcode' => '[kmpp_related post="' . $post->ID . '"]', '_margin' => kmpp_box( 24, 0, 40, 0 ) ) ),
							kmpp_button( __( 'View all projects', 'km-portfolio-pages' ), $portfolio, 'solid', false, array( 'align' => 'center' ) ),
						)
					)
				),
			),
			array( 'padding' => kmpp_box( 90, 20, 90, 20 ), 'padding_mobile' => kmpp_box( 56, 16, 56, 16 ) )
		);
	}

	/*
	 * 6. Call to action — dark bookend matching the hero.
	 */
	$sections[] = kmpp_section(
		array(
			kmpp_column(
				100,
				array(
					kmpp_heading(
						esc_html__( 'Have a', 'km-portfolio-pages' ) . ' <span style="color:' . esc_attr( $t['lilac'] ) . '">' . esc_html__( 'similar project', 'km-portfolio-pages' ) . '</span> ' . esc_html__( 'in mind?', 'km-portfolio-pages' ),
						'h2',
						40,
						26,
						array( 'title_color' => '#FFFFFF', 'align' => 'center', '_margin' => kmpp_box( 0, 0, 12, 0 ) )
					),
					kmpp_text( '<p>' . esc_html__( 'Tell me what you need and I will reply with a clear plan, timeline and quote.', 'km-portfolio-pages' ) . '</p>', 17, array( 'text_color' => 'rgba(255,255,255,0.75)', 'align' => 'center', '_margin' => kmpp_box( 0, 0, 12, 0 ) ) ),
					kmpp_button( __( 'Start a project ↗', 'km-portfolio-pages' ), $contact, 'solid', false, array( 'align' => 'center' ) ),
				)
			),
		),
		array_merge(
			kmpp_dark_bg(),
			array(
				'padding'        => kmpp_box( 100, 20, 100, 20 ),
				'padding_mobile' => kmpp_box( 64, 16, 64, 16 ),
			)
		)
	);

	return $sections;
}

/**
 * SEO housekeeping that only fills gaps and never overwrites your own values:
 * a Yoast meta description from the excerpt, and alt text on the logo and screenshots.
 */
function kmpp_fill_seo( WP_Post $post ) {
	if ( has_excerpt( $post ) && '' === (string) get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ) ) {
		update_post_meta( $post->ID, '_yoast_wpseo_metadesc', wp_html_excerpt( get_the_excerpt( $post ), 155, '…' ) );
	}

	$client               = kmpp_client_name( $post );
	list( $logo, $shots ) = kmpp_project_images( $post );
	$alts                 = array();
	if ( $logo ) {
		/* translators: %s: client name */
		$alts[ $logo ] = sprintf( __( '%s logo', 'km-portfolio-pages' ), $client );
	}
	foreach ( $shots as $n => $id ) {
		/* translators: 1: client name, 2: screen number */
		$alts[ $id ] = sprintf( __( '%1$s website, screen %2$d', 'km-portfolio-pages' ), $client, $n + 1 );
	}
	foreach ( $alts as $id => $alt ) {
		if ( '' === trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) ) ) {
			update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		}
	}
}

/**
 * Write the layout to a post, keeping a backup of any Elementor data it replaces.
 */
function kmpp_build_page( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || KMPP_POST_TYPE !== $post->post_type ) {
		return false;
	}

	// Back up a hand-made layout; a layout this plugin generated is simply replaced.
	$existing = get_post_meta( $post_id, '_elementor_data', true );
	if ( $existing && ! kmpp_is_generated( $post_id ) ) {
		update_post_meta( $post_id, KMPP_BACKUP_KEY, wp_slash( $existing ) );
	}

	kmpp_fill_seo( $post );

	$data = wp_json_encode( kmpp_build_layout( $post ) );
	update_post_meta( $post_id, '_elementor_data', wp_slash( $data ) );
	update_post_meta( $post_id, '_kmpp_generated', md5( $data ) );
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-post' );
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $post_id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );
	update_post_meta( $post_id, '_wp_page_template', 'elementor_header_footer' );

	// Kadence: transparent header over the dark hero, as on the homepage.
	update_post_meta( $post_id, '_kad_post_transparent', 'enable' );
	update_post_meta( $post_id, '_kad_post_title', 'hide' );
	update_post_meta( $post_id, '_kad_post_layout', 'fullwidth' );
	update_post_meta( $post_id, '_kad_post_content_style', 'unboxed' );
	update_post_meta( $post_id, '_kad_post_vertical_padding', 'hide' );
	update_post_meta( $post_id, '_kad_post_feature', 'hide' );

	delete_post_meta( $post_id, '_elementor_css' );
	delete_post_meta( $post_id, '_elementor_element_cache' );
	return true;
}

/**
 * True when the post's Elementor layout is still the one this plugin generated
 * (v1.0.0 layouts had no marker; they are recognised by their "All projects" link and having no backup).
 */
function kmpp_is_generated( $post_id ) {
	$hash = get_post_meta( $post_id, '_kmpp_generated', true );
	$data = get_post_meta( $post_id, '_elementor_data', true );
	if ( $hash ) {
		return is_string( $data ) && md5( $data ) === $hash;
	}
	return is_string( $data ) && false !== strpos( $data, 'All projects' ) && ! get_post_meta( $post_id, KMPP_BACKUP_KEY, true );
}

function kmpp_restore_page( $post_id ) {
	$backup = get_post_meta( $post_id, KMPP_BACKUP_KEY, true );
	if ( $backup ) {
		update_post_meta( $post_id, '_elementor_data', wp_slash( $backup ) );
	} else {
		// Nothing was there before: go back to the plain post.
		foreach ( array( '_elementor_data', '_elementor_edit_mode', '_elementor_template_type', '_elementor_version', '_elementor_page_settings', '_wp_page_template', '_kad_post_transparent', '_kad_post_title', '_kad_post_layout', '_kad_post_content_style', '_kad_post_vertical_padding', '_kad_post_feature' ) as $key ) {
			delete_post_meta( $post_id, $key );
		}
	}
	delete_post_meta( $post_id, KMPP_BACKUP_KEY );
	delete_post_meta( $post_id, '_kmpp_generated' );
	delete_post_meta( $post_id, '_elementor_css' );
	delete_post_meta( $post_id, '_elementor_element_cache' );
	return true;
}

/*
 * Admin screen: Portfolios → Elementor Pages.
 */
add_action(
	'admin_menu',
	static function () {
		add_submenu_page(
			'edit.php?post_type=' . KMPP_POST_TYPE,
			__( 'Elementor Pages', 'km-portfolio-pages' ),
			__( 'Elementor Pages', 'km-portfolio-pages' ),
			'edit_others_posts',
			'kmpp',
			'kmpp_render_admin'
		);
	}
);

add_action(
	'admin_post_kmpp_run',
	static function () {
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'km-portfolio-pages' ) );
		}
		check_admin_referer( 'kmpp_run' );

		$do        = isset( $_POST['kmpp_do'] ) ? sanitize_key( $_POST['kmpp_do'] ) : '';
		$ids       = isset( $_POST['kmpp_ids'] ) ? array_map( 'absint', (array) $_POST['kmpp_ids'] ) : array();
		$overwrite = ! empty( $_POST['kmpp_overwrite'] );
		$done      = 0;
		$skipped   = 0;

		foreach ( $ids as $id ) {
			if ( ! current_user_can( 'edit_post', $id ) ) {
				continue;
			}
			if ( 'restore' === $do ) {
				$done += kmpp_restore_page( $id ) ? 1 : 0;
			} elseif ( ! $overwrite && get_post_meta( $id, '_elementor_data', true ) && ! kmpp_is_generated( $id ) ) {
				++$skipped;
			} else {
				$done += kmpp_build_page( $id ) ? 1 : 0;
			}
		}

		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'post_type' => KMPP_POST_TYPE,
					'page'      => 'kmpp',
					'kmpp_done' => $done,
					'kmpp_skip' => $skipped,
					'kmpp_act'  => $do,
				),
				admin_url( 'edit.php' )
			)
		);
		exit;
	}
);

function kmpp_render_admin() {
	$posts = get_posts(
		array(
			'post_type'      => KMPP_POST_TYPE,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => -1,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
		)
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Portfolio Elementor Pages', 'km-portfolio-pages' ); ?></h1>
		<p><?php esc_html_e( 'Builds an Elementor case-study page for each project from its title, excerpt, logo, gallery, website link, date, categories, services, industries and its Challenge / Solution / Result content. It also fills an empty Yoast meta description from the excerpt and empty image alt text. Post status is not changed: drafts stay drafts.', 'km-portfolio-pages' ); ?></p>

		<?php if ( isset( $_GET['kmpp_done'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php
				$act = isset( $_GET['kmpp_act'] ) ? sanitize_key( $_GET['kmpp_act'] ) : '';
				printf(
					/* translators: 1: pages done, 2: pages skipped */
					esc_html( 'restore' === $act ? __( 'Restored %1$d page(s).', 'km-portfolio-pages' ) : __( 'Built %1$d page(s). Skipped %2$d that already had an Elementor layout.', 'km-portfolio-pages' ) ),
					absint( $_GET['kmpp_done'] ),
					absint( isset( $_GET['kmpp_skip'] ) ? $_GET['kmpp_skip'] : 0 )
				);
				?>
			</p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="kmpp_run">
			<?php wp_nonce_field( 'kmpp_run' ); ?>
			<table class="widefat striped" style="max-width:1000px">
				<thead><tr>
					<td class="check-column"><input type="checkbox" onclick="document.querySelectorAll('.kmpp-cb').forEach(c=>c.checked=this.checked)" checked></td>
					<th><?php esc_html_e( 'Project', 'km-portfolio-pages' ); ?></th>
					<th><?php esc_html_e( 'Status', 'km-portfolio-pages' ); ?></th>
					<th><?php esc_html_e( 'Elementor layout', 'km-portfolio-pages' ); ?></th>
					<th></th>
				</tr></thead>
				<tbody>
				<?php foreach ( $posts as $p ) : ?>
					<?php
					$has_layout = (bool) get_post_meta( $p->ID, '_elementor_data', true );
					$edit_url   = add_query_arg( array( 'post' => $p->ID, 'action' => 'elementor' ), admin_url( 'post.php' ) );
					$view_url   = 'publish' === $p->post_status ? get_permalink( $p ) : get_preview_post_link( $p );
					?>
					<tr>
						<th class="check-column"><input class="kmpp-cb" type="checkbox" name="kmpp_ids[]" value="<?php echo esc_attr( $p->ID ); ?>" checked></th>
						<td><strong><?php echo esc_html( get_the_title( $p ) ); ?></strong></td>
						<td><?php echo esc_html( get_post_status_object( $p->post_status )->label ); ?></td>
						<td><?php echo $has_layout ? '✅' : '—'; ?></td>
						<td>
							<?php if ( $has_layout ) : ?>
								<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit with Elementor', 'km-portfolio-pages' ); ?></a> |
							<?php endif; ?>
							<a href="<?php echo esc_url( $view_url ); ?>" target="_blank"><?php esc_html_e( 'View', 'km-portfolio-pages' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p>
				<label><input type="checkbox" name="kmpp_overwrite" value="1"> <?php esc_html_e( 'Overwrite projects that already have an Elementor layout (the old layout is backed up)', 'km-portfolio-pages' ); ?></label>
			</p>
			<p>
				<button class="button button-primary" name="kmpp_do" value="build"><?php esc_html_e( 'Build pages for selected', 'km-portfolio-pages' ); ?></button>
				<button class="button" name="kmpp_do" value="restore" onclick="return confirm('<?php echo esc_js( __( 'Undo the generated layout on the selected projects?', 'km-portfolio-pages' ) ); ?>')"><?php esc_html_e( 'Undo for selected', 'km-portfolio-pages' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}
