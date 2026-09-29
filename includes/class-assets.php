<?php
/**
 * Registers and enqueues the frontend CSS/JS.
 *
 * The CSS carries the Floating Effects keyframes, so it must load in the
 * editor preview as well — otherwise the effect looks broken while editing.
 * `elementor/preview/enqueue_styles` covers that case explicitly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KMPB_Assets {

	public function __construct() {
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_styles' ) );
		add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Editor preview iframe.
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_styles' ) );
		add_action( 'elementor/preview/enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Editor panel itself (outside the preview iframe) — panel branding.
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );
	}

	public function enqueue_editor_styles() {
		wp_enqueue_style(
			'kmpb-editor',
			KMPB_URL . 'assets/css/editor.css',
			array(),
			KMPB_VERSION
		);
	}

	public function enqueue_styles() {
		wp_enqueue_style(
			'kmpb-effects',
			KMPB_URL . 'assets/css/mouse-effects.css',
			array(),
			KMPB_VERSION
		);
	}

	public function enqueue_scripts() {
		wp_enqueue_script(
			'kmpb-effects',
			KMPB_URL . 'assets/js/mouse-effects.js',
			array(),
			KMPB_VERSION,
			true
		);
	}
}
