<?php
/**
 * The "Kirollos Portfolio" icon library, merged in from the separate
 * Kirollos Portfolio Icons plugin.
 *
 * The tab key, the CSS prefix (kpio-) and the font family are intentionally
 * unchanged. Elementor stores a chosen icon as its class string, e.g.
 * "kpio kpio-wordpress", so renaming any of those would silently blank out
 * every icon already placed on the site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KMPB_Icons {

	const HANDLE = 'kpio-icons';

	/**
	 * Icons contained in the font, in the order they appear in the library.
	 *
	 * @var string[]
	 */
	private $icons = array(
		'wordpress',
		'custom-plugin',
		'custom-theme',
		'woocommerce',
		'shopify',
		'performance',

		// Freelance platforms. Kept after the originals so the library order
		// stays stable for anyone scanning it.
		'mostaql',
		'khamsat',
		'freelancer',
		'upwork',
		'fiverr',
		'behance',

		// AI tools.
		'chatgpt',
		'claude',

		// Stack and tooling.
		'gutenberg',
		'wpml',
		'acf',
		'google-tag',
		'meta-pixel',
		'ga4',
		'mysql',
		'git',
	);

	public function __construct() {
		add_filter( 'elementor/icons_manager/additional_tabs', array( $this, 'add_library' ) );

		// The font has to be present anywhere an icon can be shown.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue' ) );
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue' ) );
	}

	public function style_url() {
		return KMPB_URL . 'assets/icons/kpio.css';
	}

	public function enqueue() {
		wp_enqueue_style( self::HANDLE, $this->style_url(), array(), KMPB_VERSION );
	}

	/**
	 * @param array $tabs
	 * @return array
	 */
	public function add_library( $tabs ) {

		if ( ! class_exists( '\Elementor\Icons_Manager' ) ) {
			return $tabs;
		}

		wp_register_style( self::HANDLE, $this->style_url(), array(), KMPB_VERSION );

		$tabs['kpio'] = array(
			'name'          => 'kpio',
			'label'         => esc_html__( 'Kirollos Portfolio', 'kirollos-magdy-portfolio-builder' ),
			'url'           => $this->style_url(),
			'enqueue'       => array( self::HANDLE ),
			'prefix'        => 'kpio-',
			'displayPrefix' => 'kpio',
			'labelIcon'     => 'kpio kpio-wordpress',
			'ver'           => KMPB_VERSION,
			'fetchJson'     => false,
			'native'        => false,
			'icons'         => $this->icons,
		);

		return $tabs;
	}
}
