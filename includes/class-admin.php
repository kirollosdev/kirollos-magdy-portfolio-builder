<?php
/**
 * Plugins-screen presentation.
 *
 * The plugin header already makes the author name a link (Author + Author URI),
 * but WordPress renders that anchor without a target, and it strips
 * target="_blank" from the header itself: _get_plugin_data_markup_translate()
 * runs the author through wp_kses() with an allowed-tag list that permits only
 * href and title on <a>. The only clean way to open it in a new tab is to
 * rewrite the finished row meta, which is what happens below.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KMPB_Admin {

	public function __construct() {
		add_filter( 'plugin_row_meta', array( $this, 'row_meta' ), 10, 2 );
	}

	/**
	 * @param string[] $meta        Row meta entries ("Version x", "By author", ...).
	 * @param string   $plugin_file Plugin file, relative to the plugins directory.
	 * @return string[]
	 */
	public function row_meta( $meta, $plugin_file ) {

		if ( plugin_basename( KMPB_FILE ) !== $plugin_file || ! is_array( $meta ) ) {
			return $meta;
		}

		$link = sprintf(
			'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
			esc_url( KMPB_AUTHOR_URL ),
			esc_html( KMPB_AUTHOR )
		);

		$replaced = false;

		foreach ( $meta as $index => $entry ) {

			/*
			 * Match on the author NAME, not on the URL. Author URI and Plugin
			 * URI are the same WhatsApp link here, so matching the URL hit the
			 * "Visit plugin site" entry as well and produced two "By ..." rows.
			 */
			if ( false === strpos( $entry, KMPB_AUTHOR ) ) {
				continue;
			}

			$meta[ $index ] = sprintf(
				/* translators: %s: Plugin author name, linked. */
				esc_html__( 'By %s', 'kirollos-magdy-portfolio-builder' ),
				$link
			);

			$replaced = true;
			break; // Only ever one author row.
		}

		// If WordPress ever changes how that entry is built, add the link rather
		// than silently losing it.
		if ( ! $replaced ) {
			$meta[] = sprintf(
				/* translators: %s: Plugin author name, linked. */
				esc_html__( 'By %s', 'kirollos-magdy-portfolio-builder' ),
				$link
			);
		}

		return $meta;
	}
}
