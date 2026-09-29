<?php
/**
 * Registers this plugin's own Elementor widgets and their assets.
 *
 * Elementor's widget API is available on the free version, so real widgets can
 * be registered rather than approximated with shortcodes. They appear in the
 * widget panel, have proper Content and Style tabs, and are editable exactly
 * like a built-in widget.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KMPB_Widgets {

	const CATEGORY = 'kmpb';

	/**
	 * handle => file, relative to the plugin.
	 *
	 * Registered rather than enqueued, so each widget pulls in only what it
	 * needs through get_style_depends() / get_script_depends().
	 *
	 * @var array
	 */
	private $styles = array(
		'kmpb-animated-heading'  => 'assets/css/animated-heading.css',
		'kmpb-portfolio-archive' => 'assets/css/portfolio-archive.css',
	);

	/** @var array */
	private $scripts = array(
		'kmpb-animated-heading'  => 'assets/js/animated-heading.js',
		'kmpb-portfolio-archive' => 'assets/js/portfolio-archive.js',
	);

	public function __construct() {
		add_action( 'elementor/elements/categories_registered', array( $this, 'category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register' ) );

		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_assets' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * @param \Elementor\Elements_Manager $manager
	 */
	public function category( $manager ) {
		$manager->add_category(
			self::CATEGORY,
			array(
				'title' => esc_html__( 'Kirollos Magdy', 'kirollos-magdy-portfolio-builder' ),
				'icon'  => 'eicon-font',
			)
		);
	}

	public function register_assets() {

		foreach ( $this->styles as $handle => $file ) {
			if ( ! wp_style_is( $handle, 'registered' ) ) {
				wp_register_style( $handle, KMPB_URL . $file, array(), KMPB_VERSION );
			}
		}

		foreach ( $this->scripts as $handle => $file ) {
			if ( ! wp_script_is( $handle, 'registered' ) ) {
				wp_register_script( $handle, KMPB_URL . $file, array(), KMPB_VERSION, true );
			}
		}
	}

	/**
	 * @param \Elementor\Widgets_Manager $manager
	 */
	public function register( $manager ) {

		// The card both portfolio widgets draw, loaded before either of them.
		require_once KMPB_PATH . 'includes/class-portfolio-card.php';

		require_once KMPB_PATH . 'includes/class-widget-animated-heading.php';
		$manager->register( new KMPB_Widget_Animated_Heading() );

		require_once KMPB_PATH . 'includes/class-widget-portfolio-archive.php';
		$manager->register( new KMPB_Widget_Portfolio_Archive() );

		require_once KMPB_PATH . 'includes/class-widget-portfolio-logos.php';
		$manager->register( new KMPB_Widget_Portfolio_Logos() );

		require_once KMPB_PATH . 'includes/class-widget-latest-projects.php';
		$manager->register( new KMPB_Widget_Latest_Projects() );

	}
}
