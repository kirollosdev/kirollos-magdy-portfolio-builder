<?php
/**
 * Plugin Name:       Kirollos Magdy Portfolio Builder
 * Plugin URI:        https://wa.me/+201016324429
 * Description:       Portfolio toolkit for Elementor by Kirollos Magdy: a portfolio post type with categories, services and industries, 14 Elementor widgets, Mouse Effects, Floating Effects, Mouse Cursor and Cursor Trail panels in the Advanced tab of every element, the Kirollos Portfolio icon library, Elementor case-study pages for every project (Portfolios > Elementor Pages) and a blog article template.
 * Version:           3.9.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Kirollos Magdy - WordPress Developer
 * Author URI:        https://wa.me/+201016324429
 * Text Domain:       kirollos-magdy-portfolio-builder
 * License:           Proprietary. All rights reserved.
 * License URI:       https://github.com/kirollosdev/kirollos-magdy-portfolio-builder/blob/main/LICENSE
 * Elementor requires at least: 3.5.0
 * Elementor tested up to: 3.26.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'KMPB_VERSION', '3.9.0' );
define( 'KMPB_FILE', __FILE__ );
define( 'KMPB_PATH', plugin_dir_path( __FILE__ ) );
define( 'KMPB_URL', plugin_dir_url( __FILE__ ) );
define( 'KMPB_AUTHOR', 'Kirollos Magdy - WordPress Developer' );
define( 'KMPB_AUTHOR_URL', 'https://wa.me/+201016324429' );

/*
 * Guarded: the module declares its Module class with no
 * class_exists check of its own. If the standalone "Portfolio Widgets"
 * test-harness plugin is also active it loads the same class from a different
 * path, which require_once cannot dedupe, and PHP fatals on the duplicate
 * declaration. Skipping the require lets the already-loaded copy serve.
 */
if ( ! class_exists( '\\KirollosMagdy\\PortfolioWidgets\\Module' ) ) {
	require_once KMPB_PATH . 'modules/portfolio-widgets/module.php';
}

/**
 * Main plugin bootstrap.
 *
 * This plugin merges two earlier plugins:
 *   - Mouse Effects for Elementor  (the Advanced-tab effect panels)
 *   - Kirollos Portfolio Icons     (the Elementor icon library)
 *
 * Both of those must be deactivated. Their PHP class names have been changed
 * here so that leaving one active by accident cannot fatal the site with a
 * "class already declared" error, but they would still register duplicate
 * controls and a duplicate icon library.
 */
final class Kirollos_Magdy_Portfolio_Builder {

	const MIN_ELEMENTOR_VERSION = '3.5.0';
	const MIN_PHP_VERSION       = '7.4';

	/** @var self|null */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
	}

	public function on_plugins_loaded() {

		// These do not depend on Elementor, so they load regardless.
		require_once KMPB_PATH . 'includes/class-admin.php';
		new KMPB_Admin();

		require_once KMPB_PATH . 'includes/class-portfolio-meta.php';
		new KMPB_Portfolio_Meta();

		$this->load_portfolio_module();
		$this->load_merged_modules();

		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_missing_elementor' ) );
			return;
		}

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, self::MIN_ELEMENTOR_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_min_elementor_version' ) );
			return;
		}

		if ( version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_min_php_version' ) );
			return;
		}

		require_once KMPB_PATH . 'includes/class-controls.php';
		require_once KMPB_PATH . 'includes/class-assets.php';
		require_once KMPB_PATH . 'includes/class-icons.php';
		require_once KMPB_PATH . 'includes/class-widgets.php';

		new KMPB_Controls();
		new KMPB_Assets();
		new KMPB_Icons();
		new KMPB_Widgets();
	}

	/**
	 * The portfolio system is the portfolio-widgets module: it owns the
	 * portfolios post type, its taxonomies and the nine Elementor widgets that
	 * query them. It boots even without Elementor because the post type has to
	 * exist regardless, and it must run before elementor/widgets/register.
	 */
	private function load_portfolio_module() {
		if ( ! class_exists( '\\KirollosMagdy\\PortfolioWidgets\\Module' ) ) {
			return;
		}

		\KirollosMagdy\PortfolioWidgets\Module::init(
			array(
				'path'    => KMPB_PATH . 'modules/portfolio-widgets/',
				'url'     => KMPB_URL . 'modules/portfolio-widgets/',
				'version' => KMPB_VERSION,
			)
		);
	}

	/**
	 * Standalone plugins that were merged into this one, keyed by module folder.
	 * 'loaded' tells whether a copy of that code is already in memory, which is how a
	 * still-active standalone plugin is detected. Loading a second copy would fatal on
	 * duplicate function and class names, so in that case the bundled module is skipped
	 * and an admin notice asks for the standalone plugin to be deactivated.
	 *
	 * @return array[]
	 */
	private function merged_modules() {
		return array(
			'portfolio-pages' => array(
				'name'   => 'KM Portfolio Pages',
				'file'   => 'modules/portfolio-pages/portfolio-pages.php',
				'loaded' => defined( 'KMPP_POST_TYPE' ),
			),
			'blog-template'   => array(
				'name'   => 'KM Blog Template',
				'file'   => 'modules/blog-template/blog-template.php',
				'loaded' => defined( 'KMBT_VERSION' ),
			),
			// Uses its own class and widget names, so an older standalone copy cannot collide with it.
			'latest-articles' => array(
				'name'   => 'Latest Articles',
				'file'   => 'modules/latest-articles/latest-articles.php',
				'loaded' => class_exists( 'KMPB_Latest_Articles', false ),
			),
		);
	}

	/**
	 * Portfolio Pages, Blog Template and Latest Articles. They run on plugins_loaded, after
	 * every active plugin file has been included, so a standalone copy can be detected first.
	 * None of them needs Elementor to load; Latest Articles only registers its widget when
	 * Elementor fires its widget hook.
	 */
	private function load_merged_modules() {
		$duplicates = array();

		foreach ( $this->merged_modules() as $slug => $module ) {
			if ( $module['loaded'] ) {
				$duplicates[] = $module['name'];
				continue;
			}
			require_once KMPB_PATH . $module['file'];
			if ( 'latest-articles' === $slug ) {
				KMPB_Latest_Articles::init();
			}
		}

		if ( $duplicates ) {
			add_action(
				'admin_notices',
				function () use ( $duplicates ) {
					$this->notice(
						sprintf(
							/* translators: 1: Plugin name 2: Comma separated list of plugin names */
							esc_html__( '"%1$s" now includes %2$s. Deactivate and delete the separate plugin(s) so the built-in version can take over.', 'kirollos-magdy-portfolio-builder' ),
							'<strong>' . esc_html__( 'Kirollos Magdy Portfolio Builder', 'kirollos-magdy-portfolio-builder' ) . '</strong>',
							'<strong>' . esc_html( implode( ', ', $duplicates ) ) . '</strong>'
						)
					);
				}
			);
		}
	}

	public function notice_missing_elementor() {
		$this->notice(
			sprintf(
				/* translators: 1: Plugin name 2: Elementor */
				esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'kirollos-magdy-portfolio-builder' ),
				'<strong>' . esc_html__( 'Kirollos Magdy Portfolio Builder', 'kirollos-magdy-portfolio-builder' ) . '</strong>',
				'<strong>' . esc_html__( 'Elementor', 'kirollos-magdy-portfolio-builder' ) . '</strong>'
			)
		);
	}

	public function notice_min_elementor_version() {
		$this->notice(
			sprintf(
				/* translators: 1: Plugin name 2: Elementor 3: Required version */
				esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'kirollos-magdy-portfolio-builder' ),
				'<strong>' . esc_html__( 'Kirollos Magdy Portfolio Builder', 'kirollos-magdy-portfolio-builder' ) . '</strong>',
				'<strong>' . esc_html__( 'Elementor', 'kirollos-magdy-portfolio-builder' ) . '</strong>',
				self::MIN_ELEMENTOR_VERSION
			)
		);
	}

	public function notice_min_php_version() {
		$this->notice(
			sprintf(
				/* translators: 1: Plugin name 2: Required PHP version */
				esc_html__( '"%1$s" requires PHP version %2$s or greater.', 'kirollos-magdy-portfolio-builder' ),
				'<strong>' . esc_html__( 'Kirollos Magdy Portfolio Builder', 'kirollos-magdy-portfolio-builder' ) . '</strong>',
				self::MIN_PHP_VERSION
			)
		);
	}

	/**
	 * @param string $message Already-escaped message, may contain <strong>.
	 */
	private function notice( $message ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			wp_kses( $message, array( 'strong' => array() ) )
		);
	}
}

// Flush rewrites once on activation so the category archives resolve.
register_activation_hook(
	__FILE__,
	function () {
		// The module registers a post type with rewrite rules.
		flush_rewrite_rules();
		// Portfolio Pages: clearing this makes its init hook flush once more after it turns
		// the portfolios archive off, so /portfolio/<slug>/ resolves.
		delete_option( 'kmpp_rewrite_version' );
	}
);

// Portfolio Pages: undo its rewrite changes when the plugin is switched off.
register_deactivation_hook(
	__FILE__,
	function () {
		delete_option( 'kmpp_rewrite_version' );
		flush_rewrite_rules( false );
	}
);

Kirollos_Magdy_Portfolio_Builder::instance();
