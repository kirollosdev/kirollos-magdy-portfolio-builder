<?php
/**
 * Uninstall handler.
 *
 * The plugin stores no options, no tables and no post meta of its own — every
 * setting lives inside Elementor's own page data — so there is nothing to
 * remove here. The file exists so WordPress does not fall back to looking for
 * a deactivation hook to clean up.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
