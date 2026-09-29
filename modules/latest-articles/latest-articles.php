<?php
/**
 * Latest Articles module.
 *
 * A highly editable Elementor Latest Articles grid with category navigation and Load more.
 * Loaded by the main plugin file, which calls KMPB_Latest_Articles::init() directly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class KMPB_Latest_Articles {
	// Follows the plugin version so asset URLs change, and caches refresh, with every release.
	const VERSION = KMPB_VERSION;

	/** Widget name before this module was renamed; pages saved with it are migrated once. */
	const OLD_WIDGET_NAME = 'nnla-latest-articles';

	/** Option set once the migration has run. */
	const MIGRATED_OPTION = 'kmpb_latest_articles_migrated';

	/** Option recording which one-time style update has run. */
	const STYLE_OPTION = 'kmpb_latest_articles_style';

	/** Current style update; bump to run a new one. */
	const STYLE_VERSION = '3';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'migrate_widget_name' ), 20 );
		add_action( 'init', array( __CLASS__, 'migrate_style' ), 21 );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widget' ) );
		add_action( 'elementor/frontend/after_register_styles', array( __CLASS__, 'register_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( __CLASS__, 'register_scripts' ) );
	}

	public static function register_styles() {
		wp_register_style(
			'kmpb-latest-articles',
			plugins_url( 'assets/latest-articles.css', __FILE__ ),
			array(),
			self::VERSION
		);
	}

	public static function register_scripts() {
		wp_register_script(
			'kmpb-latest-articles',
			plugins_url( 'assets/latest-articles.js', __FILE__ ),
			array(),
			self::VERSION,
			true
		);
	}

	/**
	 * Elementor saves each widget's name inside _elementor_data. Pages built before the rename
	 * reference the old name, so rewrite it once to the current one and clear Elementor's
	 * generated CSS and element cache for those posts so they rebuild with the new selectors.
	 */
	public static function migrate_widget_name() {
		if ( get_option( self::MIGRATED_OPTION ) ) {
			return;
		}

		global $wpdb;
		$old = '"widgetType":"' . self::OLD_WIDGET_NAME . '"';
		$new = '"widgetType":"kmpb-latest-articles"';

		$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND meta_value LIKE %s",
				'%' . $wpdb->esc_like( $old ) . '%'
			)
		);

		foreach ( array_map( 'intval', $ids ) as $post_id ) {
			$data = get_post_meta( $post_id, '_elementor_data', true );
			if ( ! is_string( $data ) || false === strpos( $data, $old ) ) {
				continue;
			}
			update_post_meta( $post_id, '_elementor_data', wp_slash( str_replace( $old, $new, $data ) ) );
			delete_post_meta( $post_id, '_elementor_css' );
			delete_post_meta( $post_id, '_elementor_element_cache' );
		}

		if ( $ids && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		update_option( self::MIGRATED_OPTION, KMPB_VERSION );
	}

	/**
	 * Style update 3: card titles use the global Secondary colour.
	 *
	 * Style update 2: the widget now uses Arial. Fonts picked in the widget's own Typography
	 * controls are saved per page and would override that, so switch any saved font family
	 * on Latest Articles widgets to Arial, and drop the retired image height and fit settings.
	 * Colours, sizes and every other saved setting are left as they are.
	 */
	public static function migrate_style() {
		if ( self::STYLE_VERSION === get_option( self::STYLE_OPTION ) ) {
			return;
		}

		global $wpdb;
		$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND meta_value LIKE %s",
				'%' . $wpdb->esc_like( '"widgetType":"kmpb-latest-articles"' ) . '%'
			)
		);

		$changed = false;
		foreach ( array_map( 'intval', $ids ) as $post_id ) {
			$raw  = get_post_meta( $post_id, '_elementor_data', true );
			$data = is_string( $raw ) ? json_decode( $raw, true ) : null;
			if ( ! is_array( $data ) ) {
				continue;
			}
			$touched = false;
			$data    = self::restyle_elements( $data, $touched );
			if ( ! $touched ) {
				continue;
			}
			update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
			delete_post_meta( $post_id, '_elementor_css' );
			delete_post_meta( $post_id, '_elementor_element_cache' );
			$changed = true;
		}

		if ( $changed && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		update_option( self::STYLE_OPTION, self::STYLE_VERSION );
	}

	/**
	 * @param array $elements Elementor element tree.
	 * @param bool  $touched  Set to true when anything changes.
	 * @return array
	 */
	private static function restyle_elements( array $elements, &$touched ) {
		foreach ( $elements as $i => $element ) {
			if ( isset( $element['widgetType'] ) && 'kmpb-latest-articles' === $element['widgetType'] && ! empty( $element['settings'] ) && is_array( $element['settings'] ) ) {
				foreach ( array_keys( $element['settings'] ) as $key ) {
					if ( preg_match( '/_typography_font_family$/', $key ) && 'Arial' !== $element['settings'][ $key ] ) {
						$elements[ $i ]['settings'][ $key ] = 'Arial';
						$touched                            = true;
					} elseif ( preg_match( '/^image_(height|fit)(_tablet|_mobile)?$/', $key ) ) {
						unset( $elements[ $i ]['settings'][ $key ] );
						$touched = true;
					}
				}

				// Title colour: point it at the global Secondary colour, replacing a saved
				// global or custom colour.
				$globals = isset( $element['settings']['__globals__'] ) && is_array( $element['settings']['__globals__'] ) ? $element['settings']['__globals__'] : array();
				if ( ! isset( $globals['title_color'] ) || 'globals/colors?id=secondary' !== $globals['title_color'] ) {
					$globals['title_color']                    = 'globals/colors?id=secondary';
					$elements[ $i ]['settings']['__globals__'] = $globals;
					$touched                                   = true;
				}
				if ( isset( $element['settings']['title_color'] ) ) {
					unset( $elements[ $i ]['settings']['title_color'] );
					$touched = true;
				}
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$elements[ $i ]['elements'] = self::restyle_elements( $element['elements'], $touched );
			}
		}
		return $elements;
	}

	public static function register_widget( $widgets_manager ) {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		require_once __DIR__ . '/class-widget-latest-articles.php';
		$widgets_manager->register( new \KirollosMagdy\LatestArticles\Widget_Latest_Articles() );
	}
}
