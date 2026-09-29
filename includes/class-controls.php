<?php
/**
 * Registers the "Mouse Effects" and "Floating Effects" sections inside the
 * Advanced tab of every Elementor element: sections, columns, containers
 * and widgets.
 *
 * IMPORTANT ARCHITECTURE NOTE
 * ---------------------------
 * An earlier version of this plugin passed settings to the frontend as
 * data attributes printed from the `elementor/frontend/{type}/before_render`
 * hooks. That does NOT work in the editor preview — `add_render_attribute()`
 * is simply not applied there (elementor/elementor#9623, open since 2019),
 * so nothing appeared to happen while editing.
 *
 * Everything is therefore driven through Elementor's own two mechanisms,
 * both of which live-update in the editor AND render on the frontend:
 *
 *   `prefix_class` -> puts a class on the element wrapper (our JS/CSS hook)
 *   `selectors`    -> writes CSS custom properties onto the wrapper
 *
 * Floating Effects then needs no JS at all (pure CSS keyframes reading those
 * custom properties). Mouse Effects reads the same custom properties from
 * getComputedStyle().
 *
 * Effect types combine what's offered across the ecosystem:
 * - Mouse Track   -> Elementor Pro Motion Effects "Mouse Track"
 * - 3D Tilt       -> Elementor Pro Motion Effects "3D Tilt"
 * - Magnetic Pull -> ElementsKit / PowerPack / XPro "magnetic" effects
 * - Floating      -> Happy Addons / Premium Addons / Master Addons "Floating Effects"
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Element_Base;

class KMPB_Controls {

	public function __construct() {
		add_action( 'elementor/element/after_section_end', array( $this, 'register_controls' ), 10, 3 );
	}

	/**
	 * NOTE: this hook fires for every Controls_Stack in Elementor, not just
	 * widgets/sections/columns/containers — it also fires for the Kit (Site
	 * Settings) and Page/Document settings, which are NOT Element_Base
	 * instances. Do not type-hint the parameter, or Elementor's own Kit
	 * controls trigger a fatal TypeError. Check with instanceof instead.
	 *
	 * The Advanced tab's "Layout" section has a different id per element
	 * type (widgets: `_section_style`, sections/columns: `section_advanced`,
	 * containers: `section_layout`), so it can't be used as a single anchor.
	 * `section_effects` (Elementor's own "Motion Effects" section) is the one
	 * id consistently registered under TAB_ADVANCED on every element type.
	 *
	 * @param mixed  $element
	 * @param string $section_id
	 * @param array  $args
	 */
	public function register_controls( $element, $section_id, $args ) {

		if ( 'section_effects' !== $section_id || ! ( $element instanceof Element_Base ) ) {
			return;
		}

		// This hook runs for every element on every editor/page load, so a single
		// bad control here would take down the whole site with a fatal. Contain
		// any failure to this plugin instead: log it and leave the element alone.
		$this->safely( $element, 'register_mouse_controls' );
		$this->safely( $element, 'register_floating_controls' );
		$this->safely( $element, 'register_cursor_controls' );
		$this->safely( $element, 'register_trail_controls' );
	}

	/**
	 * "Cursor Trail" — the visitor keeps their normal pointer, and particles,
	 * sparkles, a glow or a comet tail follow it, painted on a canvas BEHIND
	 * this element's content.
	 *
	 * Equivalent to the trail animations in Essential Addons (trail particles,
	 * ink trail, frost sparkles, dot comet) and XPro's mouse trail.
	 *
	 * @param Element_Base $element
	 */
	private function register_trail_controls( Element_Base $element ) {

		$element->start_controls_section(
			'mefe_section_trail',
			array(
				'label' => esc_html__( 'Cursor Trail', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			'mefe_trail_enable',
			array(
				'label'        => esc_html__( 'Cursor Trail', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'Off', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => '',
				'prefix_class' => 'mefe-trail-',
				'description'  => esc_html__( 'Keeps the normal cursor and trails an effect behind it, drawn behind this element\'s content.', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$element->add_control(
			'mefe_trail_type',
			array(
				'label'        => esc_html__( 'Trail Type', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'particles',
				'options'      => array(
					'particles' => esc_html__( 'Particles', 'kirollos-magdy-portfolio-builder' ),
					'sparkles'  => esc_html__( 'Sparkles', 'kirollos-magdy-portfolio-builder' ),
					'glow'      => esc_html__( 'Glow Orb', 'kirollos-magdy-portfolio-builder' ),
					'comet'     => esc_html__( 'Comet Tail', 'kirollos-magdy-portfolio-builder' ),
				),
				'prefix_class' => 'mefe-ttype-',
				'condition'    => array(
					'mefe_trail_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_trail_color',
			array(
				'label'     => esc_html__( 'Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#7C3AED',
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-trail-color: {{VALUE}};',
				),
				'condition' => array(
					'mefe_trail_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_trail_color2',
			array(
				'label'       => esc_html__( 'Second Color', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '',
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-trail-color2: {{VALUE}};',
				),
				'description' => esc_html__( 'Optional. Particles and sparkles fade between the two colors.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_trail_enable' => 'yes',
					'mefe_trail_type'   => array( 'particles', 'sparkles' ),
				),
			)
		);

		$element->add_control(
			'mefe_trail_size',
			array(
				'label'     => esc_html__( 'Size (px)', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 400,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 8,
				),
				'condition' => array(
					'mefe_trail_enable' => 'yes',
					'mefe_trail_type'   => array( 'particles', 'sparkles', 'comet' ),
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-trail-size: {{SIZE}};',
				),
			)
		);

		// Separate control so the glow gets a sensible default of its own — an
		// 8px blob would be invisible. Same CSS variable; only the one whose
		// condition is met writes it.
		$element->add_control(
			'mefe_trail_glow_size',
			array(
				'label'     => esc_html__( 'Glow Size (px)', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 40,
						'max'  => 900,
						'step' => 10,
					),
				),
				'default'   => array(
					'size' => 260,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-trail-size: {{SIZE}};',
				),
				'condition' => array(
					'mefe_trail_enable' => 'yes',
					'mefe_trail_type'   => 'glow',
				),
			)
		);

		$element->add_control(
			'mefe_trail_count',
			array(
				'label'       => esc_html__( 'Spawn Rate', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					),
				),
				'default'     => array(
					'size' => 2,
				),
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-trail-count: {{SIZE}};',
				),
				'description' => esc_html__( 'How many pieces spawn per mouse move. Higher is denser and heavier.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_trail_enable' => 'yes',
					'mefe_trail_type'   => array( 'particles', 'sparkles' ),
				),
			)
		);

		$element->add_control(
			'mefe_trail_length',
			array(
				'label'     => esc_html__( 'Tail Length', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 5,
						'max'  => 80,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 25,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-trail-length: {{SIZE}};',
				),
				'condition' => array(
					'mefe_trail_enable' => 'yes',
					'mefe_trail_type'   => 'comet',
				),
			)
		);

		$element->add_control(
			'mefe_trail_life',
			array(
				'label'       => esc_html__( 'Fade Speed', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min'  => 1,
						'max'  => 20,
						'step' => 1,
					),
				),
				'default'     => array(
					'size' => 6,
				),
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-trail-life: {{SIZE}};',
				),
				'description' => esc_html__( 'Higher fades away faster.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_trail_enable' => 'yes',
					'mefe_trail_type'   => array( 'particles', 'sparkles' ),
				),
			)
		);

		$element->add_control(
			'mefe_trail_lag',
			array(
				'label'       => esc_html__( 'Follow Delay', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 95,
						'step' => 5,
					),
				),
				'default'     => array(
					'size' => 85,
				),
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-trail-lag: {{SIZE}};',
				),
				'description' => esc_html__( 'How lazily the glow drifts after the pointer. 0 pins it to the cursor exactly.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_trail_enable' => 'yes',
					'mefe_trail_type'   => 'glow',
				),
			)
		);

		$element->add_control(
			'mefe_trail_drift',
			array(
				'label'       => esc_html__( 'Idle Drift (px)', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 150,
						'step' => 1,
					),
				),
				'default'     => array(
					'size' => 25,
				),
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-trail-drift: {{SIZE}};',
				),
				'description' => esc_html__( 'Keeps the glow wandering gently when the cursor stops moving. 0 makes it sit perfectly still.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_trail_enable' => 'yes',
					'mefe_trail_type'   => 'glow',
				),
			)
		);

		$element->add_control(
			'mefe_trail_drift_speed',
			array(
				'label'     => esc_html__( 'Idle Drift Speed', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 3,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-trail-drift-speed: {{SIZE}};',
				),
				'condition' => array(
					'mefe_trail_enable' => 'yes',
					'mefe_trail_type'   => 'glow',
				),
			)
		);

		$element->add_control(
			'mefe_trail_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0.05,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'default'   => array(
					'size' => 0.7,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-trail-opacity: {{SIZE}};',
				),
				'condition' => array(
					'mefe_trail_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_trail_additive',
			array(
				'label'        => esc_html__( 'Additive Glow', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'No', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'prefix_class' => 'mefe-tadd-',
				'description'  => esc_html__( 'Overlapping pieces brighten each other. Looks best on dark backgrounds.', 'kirollos-magdy-portfolio-builder' ),
				'condition'    => array(
					'mefe_trail_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_trail_clip',
			array(
				'label'        => esc_html__( 'Clip to Element', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'No', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'prefix_class' => 'mefe-tclip-',
				'description'  => esc_html__( 'Hides anything that spills outside the element\'s bounds.', 'kirollos-magdy-portfolio-builder' ),
				'condition'    => array(
					'mefe_trail_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_trail_exclude',
			array(
				'label'       => esc_html__( 'Ignore Pointer Over', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => '.site-header-row-container-inner',
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-trail-exclude: "{{VALUE}}";',
				),
				'description' => esc_html__( 'CSS selector for anything layered on top of this element. While the pointer is over it the trail fades out, so an overlapping header can run its own hover effect. Comma-separate several selectors.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_trail_enable' => 'yes',
				),
			)
		);

		$this->add_device_controls( $element, 'mefe_trail', 'mefe-thide-', 'mefe_trail_enable' );

		$element->end_controls_section();
	}

	/**
	 * Runs one control-registration method, swallowing (and logging) any error.
	 *
	 * If it blows up mid-section the section is left open, and Elementor calls
	 * wp_die() the next time anything tries to open a section — so the open
	 * section is closed before returning.
	 *
	 * @param Element_Base $element
	 * @param string       $method
	 */
	private function safely( Element_Base $element, $method ) {
		try {
			$this->$method( $element );
		} catch ( \Throwable $e ) {
			$this->close_dangling_section( $element );

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log(
					sprintf(
						'Mouse Effects for Elementor: %s() failed for element "%s" — %s in %s:%d',
						$method,
						method_exists( $element, 'get_name' ) ? $element->get_name() : 'unknown',
						$e->getMessage(),
						$e->getFile(),
						$e->getLine()
					)
				);
			}
		}
	}

	/**
	 * @param Element_Base $element
	 */
	private function close_dangling_section( Element_Base $element ) {
		if ( ! method_exists( $element, 'get_current_section' ) ) {
			return;
		}

		try {
			if ( null !== $element->get_current_section() ) {
				$element->end_controls_section();
			}
		} catch ( \Throwable $e ) {
			// Nothing further we can safely do here.
			return;
		}
	}

	/**
	 * "Mouse Cursor" — replaces the pointer with a custom cursor while the
	 * visitor is over this element. Modelled on the custom-cursor extensions in
	 * PowerPack, Premium Addons, Happy Addons, ElementsKit and XPro.
	 *
	 * Supports Dot / Text / Image. Icon and Lottie types are deliberately not
	 * offered: their markup can't be carried in a CSS custom property, and the
	 * only alternative (printing markup from a PHP render hook) is exactly the
	 * approach that silently fails in the Elementor editor.
	 *
	 * @param Element_Base $element
	 */
	private function register_cursor_controls( Element_Base $element ) {

		$element->start_controls_section(
			'mefe_section_cursor',
			array(
				'label' => esc_html__( 'Mouse Cursor', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			'mefe_cursor_enable',
			array(
				'label'        => esc_html__( 'Custom Mouse Cursor', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'Off', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => '',
				'prefix_class' => 'mefe-cursor-',
				'description'  => esc_html__( 'Show a custom cursor while the visitor hovers this element.', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$element->add_control(
			'mefe_cursor_type',
			array(
				'label'        => esc_html__( 'Cursor Type', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'dot',
				'options'      => array(
					'dot'   => esc_html__( 'Dot / Circle', 'kirollos-magdy-portfolio-builder' ),
					'text'  => esc_html__( 'Text', 'kirollos-magdy-portfolio-builder' ),
					'image' => esc_html__( 'Image', 'kirollos-magdy-portfolio-builder' ),
				),
				'prefix_class' => 'mefe-ctype-',
				'condition'    => array(
					'mefe_cursor_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_cursor_text',
			array(
				'label'       => esc_html__( 'Text', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'View', 'kirollos-magdy-portfolio-builder' ),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-cursor-text: "{{VALUE}}";',
				),
				'condition'   => array(
					'mefe_cursor_enable' => 'yes',
					'mefe_cursor_type'   => 'text',
				),
			)
		);

		$element->add_control(
			'mefe_cursor_image',
			array(
				'label'     => esc_html__( 'Image', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::MEDIA,
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-cursor-image: url({{URL}});',
				),
				'condition' => array(
					'mefe_cursor_enable' => 'yes',
					'mefe_cursor_type'   => 'image',
				),
			)
		);

		$element->add_control(
			'mefe_cursor_size',
			array(
				'label'     => esc_html__( 'Size (px)', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 4,
						'max'  => 300,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 40,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-cursor-size: {{SIZE}}px;',
				),
				'condition' => array(
					'mefe_cursor_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_cursor_bg',
			array(
				'label'     => esc_html__( 'Background Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#7C3AED',
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-cursor-bg: {{VALUE}};',
				),
				'condition' => array(
					'mefe_cursor_enable' => 'yes',
					'mefe_cursor_type!'  => 'image',
				),
			)
		);

		$element->add_control(
			'mefe_cursor_color',
			array(
				'label'     => esc_html__( 'Text Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-cursor-color: {{VALUE}};',
				),
				'condition' => array(
					'mefe_cursor_enable' => 'yes',
					'mefe_cursor_type'   => 'text',
				),
			)
		);

		$element->add_control(
			'mefe_cursor_font_size',
			array(
				'label'     => esc_html__( 'Font Size (px)', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 6,
						'max'  => 60,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 14,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-cursor-font-size: {{SIZE}}px;',
				),
				'condition' => array(
					'mefe_cursor_enable' => 'yes',
					'mefe_cursor_type'   => 'text',
				),
			)
		);

		$element->add_control(
			'mefe_cursor_radius',
			array(
				'label'     => esc_html__( 'Border Radius (%)', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'%' => array(
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 50,
					'unit' => '%',
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-cursor-radius: {{SIZE}}%;',
				),
				'condition' => array(
					'mefe_cursor_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_cursor_opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0.1,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'default'   => array(
					'size' => 1,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-cursor-opacity: {{SIZE}};',
				),
				'condition' => array(
					'mefe_cursor_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_cursor_blend',
			array(
				'label'       => esc_html__( 'Blend Mode', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'normal',
				'options'     => array(
					'normal'      => esc_html__( 'Normal', 'kirollos-magdy-portfolio-builder' ),
					'difference'  => esc_html__( 'Difference', 'kirollos-magdy-portfolio-builder' ),
					'exclusion'   => esc_html__( 'Exclusion', 'kirollos-magdy-portfolio-builder' ),
					'multiply'    => esc_html__( 'Multiply', 'kirollos-magdy-portfolio-builder' ),
					'screen'      => esc_html__( 'Screen', 'kirollos-magdy-portfolio-builder' ),
					'overlay'     => esc_html__( 'Overlay', 'kirollos-magdy-portfolio-builder' ),
					'lighten'     => esc_html__( 'Lighten', 'kirollos-magdy-portfolio-builder' ),
					'darken'      => esc_html__( 'Darken', 'kirollos-magdy-portfolio-builder' ),
					'color-dodge' => esc_html__( 'Color Dodge', 'kirollos-magdy-portfolio-builder' ),
					'hue'         => esc_html__( 'Hue', 'kirollos-magdy-portfolio-builder' ),
					'saturation'  => esc_html__( 'Saturation', 'kirollos-magdy-portfolio-builder' ),
					'luminosity'  => esc_html__( 'Luminosity', 'kirollos-magdy-portfolio-builder' ),
				),
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-cursor-blend: {{VALUE}};',
				),
				'description' => esc_html__( '"Difference" is the classic inverted-cursor look over images and dark sections.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_cursor_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_cursor_lag',
			array(
				'label'       => esc_html__( 'Follow Delay (Lazy Move)', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 95,
						'step' => 5,
					),
				),
				'default'     => array(
					'size' => 60,
				),
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-cursor-lag: {{SIZE}};',
				),
				'description' => esc_html__( '0 sticks to the pointer exactly. Higher values trail behind it more softly.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_cursor_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_cursor_hide_native',
			array(
				'label'        => esc_html__( 'Hide Default Cursor', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'No', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'prefix_class' => 'mefe-chide-native-',
				'condition'    => array(
					'mefe_cursor_enable' => 'yes',
				),
			)
		);

		$this->add_device_controls( $element, 'mefe_cursor', 'mefe-chide-', 'mefe_cursor_enable' );

		$element->end_controls_section();
	}

	/**
	 * @param Element_Base $element
	 */
	private function register_mouse_controls( Element_Base $element ) {

		$element->start_controls_section(
			'mefe_section_mouse_effects',
			array(
				'label' => esc_html__( 'Mouse Effects', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			'mefe_enable',
			array(
				'label'        => esc_html__( 'Mouse Effects', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'Off', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => '',
				'prefix_class' => 'mefe-mouse-',
				'description'  => esc_html__( 'Make this element react to the visitor\'s mouse position.', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$element->add_control(
			'mefe_type',
			array(
				'label'        => esc_html__( 'Effect Type', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'track',
				'options'      => array(
					'track'    => esc_html__( 'Mouse Track (Parallax)', 'kirollos-magdy-portfolio-builder' ),
					'tilt'     => esc_html__( '3D Tilt', 'kirollos-magdy-portfolio-builder' ),
					'magnetic' => esc_html__( 'Magnetic Pull', 'kirollos-magdy-portfolio-builder' ),
				),
				'prefix_class' => 'mefe-type-',
				'condition'    => array(
					'mefe_enable' => 'yes',
				),
			)
		);

		// Shared by Mouse Track and 3D Tilt — kept as one control so only a
		// single `mefe-dir-*` class can ever be on the element.
		$element->add_control(
			'mefe_direction',
			array(
				'label'        => esc_html__( 'Direction', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'direct',
				'options'      => array(
					'direct'   => esc_html__( 'Direct', 'kirollos-magdy-portfolio-builder' ),
					'opposite' => esc_html__( 'Opposite', 'kirollos-magdy-portfolio-builder' ),
				),
				'prefix_class' => 'mefe-dir-',
				'condition'    => array(
					'mefe_enable' => 'yes',
					'mefe_type'   => array( 'track', 'tilt' ),
				),
			)
		);

		$element->add_control(
			'mefe_relative',
			array(
				'label'        => esc_html__( 'Effects Relative To', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'viewport',
				'options'      => array(
					'default'  => esc_html__( 'Default (Element)', 'kirollos-magdy-portfolio-builder' ),
					'viewport' => esc_html__( 'Viewport', 'kirollos-magdy-portfolio-builder' ),
					'page'     => esc_html__( 'Entire Page', 'kirollos-magdy-portfolio-builder' ),
				),
				'prefix_class' => 'mefe-rel-',
				'condition'    => array(
					'mefe_enable' => 'yes',
					'mefe_type'   => array( 'track', 'tilt' ),
				),
			)
		);

		/* ---- Mouse Track ---- */

		$element->add_control(
			'mefe_track_speed',
			array(
				'label'     => esc_html__( 'Speed', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 10,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 4,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-track-speed: {{SIZE}};',
				),
				'condition' => array(
					'mefe_enable' => 'yes',
					'mefe_type'   => 'track',
				),
			)
		);

		/* ---- 3D Tilt ---- */

		$element->add_control(
			'mefe_tilt_speed',
			array(
				'label'     => esc_html__( 'Speed', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 20,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 6,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-tilt-speed: {{SIZE}};',
				),
				'condition' => array(
					'mefe_enable' => 'yes',
					'mefe_type'   => 'tilt',
				),
			)
		);

		$element->add_control(
			'mefe_tilt_scale',
			array(
				'label'     => esc_html__( 'Scale on Hover', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 2,
						'step' => 0.01,
					),
				),
				'default'   => array(
					'size' => 1,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-tilt-scale: {{SIZE}};',
				),
				'condition' => array(
					'mefe_enable' => 'yes',
					'mefe_type'   => 'tilt',
				),
			)
		);

		$element->add_control(
			'mefe_tilt_glare',
			array(
				'label'       => esc_html__( 'Glare Opacity', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'default'     => array(
					'size' => 0,
				),
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-tilt-glare: {{SIZE}};',
				),
				'description' => esc_html__( 'Adds a light sheen that follows the cursor across the element. 0 disables it.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_enable' => 'yes',
					'mefe_type'   => 'tilt',
				),
			)
		);

		/* ---- Magnetic Pull ---- */

		$element->add_control(
			'mefe_magnetic_strength',
			array(
				'label'     => esc_html__( 'Strength (%)', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 30,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-mag-strength: {{SIZE}};',
				),
				'condition' => array(
					'mefe_enable' => 'yes',
					'mefe_type'   => 'magnetic',
				),
			)
		);

		$element->add_control(
			'mefe_magnetic_radius',
			array(
				'label'       => esc_html__( 'Trigger Radius (px)', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min'  => 50,
						'max'  => 600,
						'step' => 10,
					),
				),
				'default'     => array(
					'size' => 150,
				),
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-mag-radius: {{SIZE}};',
				),
				'description' => esc_html__( 'Distance from the element within which the pull activates.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_enable' => 'yes',
					'mefe_type'   => 'magnetic',
				),
			)
		);

		$element->add_control(
			'mefe_magnetic_scale_amount',
			array(
				'label'     => esc_html__( 'Scale While Pulled', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 1.3,
						'step' => 0.01,
					),
				),
				'default'   => array(
					'size' => 1,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-mag-scale: {{SIZE}};',
				),
				'condition' => array(
					'mefe_enable' => 'yes',
					'mefe_type'   => 'magnetic',
				),
			)
		);

		$this->add_device_controls( $element, 'mefe_mouse', 'mefe-mhide-', 'mefe_enable' );

		$element->end_controls_section();
	}

	/**
	 * "Floating Effects" — a continuous idle animation that gently drifts,
	 * rotates and/or scales the element. Modelled on the control set shared by
	 * Happy Addons, Premium Addons, Master Addons and XPro: independently
	 * toggleable Translate X / Translate Y / Rotate / Scale, each with its own
	 * distance/amount, plus a shared Duration and Delay.
	 *
	 * Implemented entirely in CSS (see assets/css/mouse-effects.css), so it
	 * animates live in the editor with no JavaScript involved.
	 *
	 * @param Element_Base $element
	 */
	private function register_floating_controls( Element_Base $element ) {

		$element->start_controls_section(
			'mefe_section_floating',
			array(
				'label' => esc_html__( 'Floating Effects', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			'mefe_float_enable',
			array(
				'label'        => esc_html__( 'Floating Effects', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'Off', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => '',
				'prefix_class' => 'mefe-float-',
				'description'  => esc_html__( 'Gently and continuously animate this element in place — great for floating images and shapes.', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		/* ---- Translate Y (the classic "floating image" effect) ---- */

		$element->add_control(
			'mefe_float_y_enable',
			array(
				'label'        => esc_html__( 'Float Vertically', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'No', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
				'condition'    => array(
					'mefe_float_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_float_y_distance',
			array(
				'label'     => esc_html__( 'Vertical Distance (px)', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 200,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 15,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-fy: {{SIZE}}px;',
				),
				'condition' => array(
					'mefe_float_enable'   => 'yes',
					'mefe_float_y_enable' => 'yes',
				),
			)
		);

		/* ---- Translate X ---- */

		$element->add_control(
			'mefe_float_x_enable',
			array(
				'label'        => esc_html__( 'Float Horizontally', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'No', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
				'condition'    => array(
					'mefe_float_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_float_x_distance',
			array(
				'label'     => esc_html__( 'Horizontal Distance (px)', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 200,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 15,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-fx: {{SIZE}}px;',
				),
				'condition' => array(
					'mefe_float_enable'   => 'yes',
					'mefe_float_x_enable' => 'yes',
				),
			)
		);

		/* ---- Rotate ---- */

		$element->add_control(
			'mefe_float_rotate_enable',
			array(
				'label'        => esc_html__( 'Rotate', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'No', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
				'condition'    => array(
					'mefe_float_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_float_rotate_angle',
			array(
				'label'     => esc_html__( 'Rotate Angle (deg)', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 45,
						'step' => 1,
					),
				),
				'default'   => array(
					'size' => 5,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-frot: {{SIZE}}deg;',
				),
				'condition' => array(
					'mefe_float_enable'        => 'yes',
					'mefe_float_rotate_enable' => 'yes',
				),
			)
		);

		/* ---- Scale ---- */

		$element->add_control(
			'mefe_float_scale_enable',
			array(
				'label'        => esc_html__( 'Scale', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'No', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
				'condition'    => array(
					'mefe_float_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_float_scale_amount',
			array(
				'label'     => esc_html__( 'Scale Amount', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 1,
						'max'  => 1.5,
						'step' => 0.01,
					),
				),
				'default'   => array(
					'size' => 1.05,
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--mefe-fscale-to: {{SIZE}};',
				),
				'condition' => array(
					'mefe_float_enable'       => 'yes',
					'mefe_float_scale_enable' => 'yes',
				),
			)
		);

		/* ---- Timing ---- */

		$element->add_control(
			'mefe_float_duration',
			array(
				'label'       => esc_html__( 'Duration (ms)', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min'  => 500,
						'max'  => 10000,
						'step' => 100,
					),
				),
				'default'     => array(
					'size' => 3000,
				),
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-fdur: {{SIZE}}ms;',
				),
				'separator'   => 'before',
				'description' => esc_html__( 'Time for one full float cycle. Higher is slower and gentler.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_float_enable' => 'yes',
				),
			)
		);

		$element->add_control(
			'mefe_float_delay',
			array(
				'label'       => esc_html__( 'Delay (ms)', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 5000,
						'step' => 100,
					),
				),
				'default'     => array(
					'size' => 0,
				),
				'selectors'   => array(
					'{{WRAPPER}}' => '--mefe-fdelay: {{SIZE}}ms;',
				),
				'description' => esc_html__( 'Offsets the start of the cycle. Give sibling elements different delays so they do not float in unison.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array(
					'mefe_float_enable' => 'yes',
				),
			)
		);

		$this->add_device_controls( $element, 'mefe_float', 'mefe-fhide-', 'mefe_float_enable' );

		$element->end_controls_section();
	}

	/**
	 * Per-device "disable" switches.
	 *
	 * A multi-select "Apply Effects On" can't drive `prefix_class` (it would
	 * emit one mangled class for the whole array), so each device gets its own
	 * switcher. Phrased as "Disable on X" because a switcher that's OFF emits
	 * no class at all — meaning the CSS/JS can only key off the enabled state.
	 *
	 * @param Element_Base $element
	 * @param string       $prefix       Control id prefix.
	 * @param string       $class_prefix CSS class prefix.
	 * @param string       $condition    Control id this group depends on.
	 */
	private function add_device_controls( Element_Base $element, $prefix, $class_prefix, $condition ) {

		$devices = array(
			'desktop' => esc_html__( 'Disable on Desktop', 'kirollos-magdy-portfolio-builder' ),
			'tablet'  => esc_html__( 'Disable on Tablet', 'kirollos-magdy-portfolio-builder' ),
			'mobile'  => esc_html__( 'Disable on Mobile', 'kirollos-magdy-portfolio-builder' ),
		);

		$first = true;

		foreach ( $devices as $device => $label ) {
			$args = array(
				'label'        => $label,
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'No', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => '',
				'prefix_class' => $class_prefix . $device . '-',
				'condition'    => array(
					$condition => 'yes',
				),
			);

			if ( $first ) {
				$args['separator'] = 'before';
				$first             = false;
			}

			$element->add_control( $prefix . '_hide_' . $device, $args );
		}
	}
}
