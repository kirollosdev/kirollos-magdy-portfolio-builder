<?php
/**
 * Animated Heading widget.
 *
 * A free-Elementor equivalent of Pro's Animated Headline, with both of its
 * modes:
 *
 *   Highlighted - an SVG shape is drawn around or under one word, the stroke
 *                 animating on as it scrolls into view.
 *   Rotating    - a list of words cycles through, with a choice of animation
 *                 including a real typing effect.
 *
 * The shapes are authored as stretchable paths (preserveAspectRatio="none")
 * so one path fits any word length, which is how the hand-drawn look survives
 * responsive text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

class KMPB_Widget_Animated_Heading extends Widget_Base {

	public function get_name() {
		return 'kmpb-animated-heading';
	}

	public function get_title() {
		return esc_html__( 'Animated Heading', 'kirollos-magdy-portfolio-builder' );
	}

	public function get_icon() {
		return 'eicon-animated-headline';
	}

	public function get_categories() {
		return array( KMPB_Widgets::CATEGORY, 'general' );
	}

	public function get_keywords() {
		return array( 'headline', 'heading', 'animated', 'typing', 'rotate', 'highlight', 'kirollos' );
	}

	public function get_style_depends() {
		return array( 'kmpb-animated-heading' );
	}

	public function get_script_depends() {
		return array( 'kmpb-animated-heading' );
	}

	/**
	 * Stretchable SVG paths, one entry per shape.
	 *
	 * @return array
	 */
	public static function shapes() {
		return array(
			// wrap    - overlays the whole word
			// under   - sits below the baseline
			// through - crosses the middle of the word
			'circle'           => array(
				'placement' => 'wrap',
				'box'       => '0 0 500 150',
				'paths'     => array( 'M470,62 C470,27 370,10 250,10 C120,10 25,34 22,74 C19,114 120,144 255,144 C390,144 482,114 478,74 C476,52 450,37 420,28' ),
			),
			'curly'            => array(
				'placement' => 'under',
				'box'       => '0 0 500 40',
				'paths'     => array( 'M8,25 Q40,6 72,25 T136,25 T200,25 T264,25 T328,25 T392,25 T456,25 T492,25' ),
			),
			'underline'        => array(
				'placement' => 'under',
				'box'       => '0 0 500 40',
				'paths'     => array( 'M10,28 C120,12 300,10 490,22' ),
			),
			'double'           => array(
				'placement' => 'under',
				'box'       => '0 0 500 40',
				'paths'     => array(
					'M10,14 C120,3 300,2 490,11',
					'M16,34 C130,24 310,22 484,31',
				),
			),
			'underline_zigzag' => array(
				'placement' => 'under',
				'box'       => '0 0 500 40',
				'paths'     => array( 'M8,30 L48,12 L88,30 L128,12 L168,30 L208,12 L248,30 L288,12 L328,30 L368,12 L408,30 L448,12 L488,30' ),
			),
			'diagonal'         => array(
				'placement' => 'wrap',
				'box'       => '0 0 500 150',
				'paths'     => array( 'M22,132 C160,100 340,52 478,18' ),
			),
			'strikethrough'    => array(
				'placement' => 'through',
				'box'       => '0 0 500 20',
				'paths'     => array( 'M8,11 C120,4 300,3 492,9' ),
			),
			'x'                => array(
				'placement' => 'wrap',
				'box'       => '0 0 500 150',
				'paths'     => array(
					'M30,20 C160,55 340,95 470,130',
					'M470,20 C340,55 160,95 30,130',
				),
			),
		);
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_content',
			array( 'label' => esc_html__( 'Animated Heading', 'kirollos-magdy-portfolio-builder' ) )
		);

		$this->add_control(
			'headline_type',
			array(
				'label'   => esc_html__( 'Type', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'rotate',
				'options' => array(
					'highlight' => array(
						'title' => esc_html__( 'Highlighted', 'kirollos-magdy-portfolio-builder' ),
						'icon'  => 'eicon-t-letter',
					),
					'rotate'    => array(
						'title' => esc_html__( 'Rotating', 'kirollos-magdy-portfolio-builder' ),
						'icon'  => 'eicon-animation',
					),
				),
				'toggle'  => false,
			)
		);

		$this->add_control(
			'before_text',
			array(
				'label'       => esc_html__( 'Before Text', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'I can build your', 'kirollos-magdy-portfolio-builder' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'highlighted_text',
			array(
				'label'       => esc_html__( 'Highlighted Text', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'WordPress', 'kirollos-magdy-portfolio-builder' ),
				'label_block' => true,
				'condition'   => array( 'headline_type' => 'highlight' ),
			)
		);

		$this->add_control(
			'rotating_text',
			array(
				'label'       => esc_html__( 'Rotating Text', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => "WordPress\nWooCommerce\nTour Reservation\nShopify",
				'description' => esc_html__( 'One word or phrase per line.', 'kirollos-magdy-portfolio-builder' ),
				'rows'        => 5,
				'condition'   => array( 'headline_type' => 'rotate' ),
			)
		);

		$this->add_control(
			'after_text',
			array(
				'label'       => esc_html__( 'After Text', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'website.', 'kirollos-magdy-portfolio-builder' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'shape',
			array(
				'label'     => esc_html__( 'Shape', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'circle',
				'options'   => array(
					'circle'           => esc_html__( 'Circle', 'kirollos-magdy-portfolio-builder' ),
					'curly'            => esc_html__( 'Curly', 'kirollos-magdy-portfolio-builder' ),
					'underline'        => esc_html__( 'Underline', 'kirollos-magdy-portfolio-builder' ),
					'double'           => esc_html__( 'Double Underline', 'kirollos-magdy-portfolio-builder' ),
					'underline_zigzag' => esc_html__( 'Zigzag', 'kirollos-magdy-portfolio-builder' ),
					'diagonal'         => esc_html__( 'Diagonal', 'kirollos-magdy-portfolio-builder' ),
					'strikethrough'    => esc_html__( 'Strikethrough', 'kirollos-magdy-portfolio-builder' ),
					'x'                => esc_html__( 'X', 'kirollos-magdy-portfolio-builder' ),
				),
				'condition' => array( 'headline_type' => 'highlight' ),
			)
		);

		$this->add_control(
			'animation',
			array(
				'label'     => esc_html__( 'Animation', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'typing',
				'options'   => array(
					'typing'     => esc_html__( 'Typing', 'kirollos-magdy-portfolio-builder' ),
					'fade'       => esc_html__( 'Fade In', 'kirollos-magdy-portfolio-builder' ),
					'slide_up'   => esc_html__( 'Slide Up', 'kirollos-magdy-portfolio-builder' ),
					'slide_down' => esc_html__( 'Slide Down', 'kirollos-magdy-portfolio-builder' ),
					'drop_in'    => esc_html__( 'Drop In', 'kirollos-magdy-portfolio-builder' ),
					'flip'       => esc_html__( 'Flip', 'kirollos-magdy-portfolio-builder' ),
					'clip'       => esc_html__( 'Clip', 'kirollos-magdy-portfolio-builder' ),
				),
				'condition' => array( 'headline_type' => 'rotate' ),
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'       => esc_html__( 'Hold Time (ms)', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array( 'px' => array( 'min' => 500, 'max' => 8000, 'step' => 100 ) ),
				'default'     => array( 'size' => 2200 ),
				'description' => esc_html__( 'How long each word stays before the next one.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array( 'headline_type' => 'rotate' ),
			)
		);

		$this->add_control(
			'type_speed',
			array(
				'label'     => esc_html__( 'Typing Speed (ms per letter)', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 20, 'max' => 300, 'step' => 5 ) ),
				'default'   => array( 'size' => 90 ),
				'condition' => array(
					'headline_type' => 'rotate',
					'animation'     => 'typing',
				),
			)
		);

		$this->add_control(
			'loop',
			array(
				'label'        => esc_html__( 'Loop', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'headline_type' => 'rotate' ),
			)
		);

		$this->add_control(
			'html_tag',
			array(
				'label'     => esc_html__( 'HTML Tag', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h2',
				'options'   => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'kirollos-magdy-portfolio-builder' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'kirollos-magdy-portfolio-builder' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'kirollos-magdy-portfolio-builder' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'left',
				'selectors' => array( '{{WRAPPER}} .kmpb-ah' => 'text-align: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_text',
			array(
				'label' => esc_html__( 'Heading', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmpb-ah' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .kmpb-ah',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_focus',
			array(
				'label' => esc_html__( 'Animated Text', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'focus_color',
			array(
				'label'     => esc_html__( 'Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#9B00FF',
				'selectors' => array( '{{WRAPPER}} .kmpb-ah__focus' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'focus_typography',
				'selector' => '{{WRAPPER}} .kmpb-ah__focus',
			)
		);

		$this->add_control(
			'cursor_color',
			array(
				'label'     => esc_html__( 'Typing Cursor Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .kmpb-ah__cursor' => 'background-color: {{VALUE}};' ),
				'condition' => array(
					'headline_type' => 'rotate',
					'animation'     => 'typing',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_shape',
			array(
				'label'     => esc_html__( 'Shape', 'kirollos-magdy-portfolio-builder' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'headline_type' => 'highlight' ),
			)
		);

		$this->add_control(
			'shape_color',
			array(
				'label'     => esc_html__( 'Shape Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#9B00FF',
				'selectors' => array( '{{WRAPPER}} .kmpb-ah__shape path' => 'stroke: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'stroke_width',
			array(
				'label'     => esc_html__( 'Stroke Width', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 1, 'max' => 30, 'step' => 1 ) ),
				'default'   => array( 'size' => 8 ),
				'selectors' => array( '{{WRAPPER}} .kmpb-ah__shape path' => 'stroke-width: {{SIZE}};' ),
			)
		);

		$this->add_control(
			'draw_duration',
			array(
				'label'     => esc_html__( 'Draw Duration (ms)', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 200, 'max' => 5000, 'step' => 100 ) ),
				'default'   => array( 'size' => 1200 ),
				'selectors' => array( '{{WRAPPER}} .kmpb-ah__shape path' => 'animation-duration: {{SIZE}}ms;' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * The SVG for the chosen shape.
	 *
	 * @param string $shape
	 * @return string
	 */
	private function shape_svg( $shape ) {

		$shapes = self::shapes();

		if ( ! isset( $shapes[ $shape ] ) ) {
			return '';
		}

		$placement = isset( $shapes[ $shape ]['placement'] ) ? $shapes[ $shape ]['placement'] : 'wrap';

		$svg = '<svg class="kmpb-ah__shape kmpb-ah__shape--' . esc_attr( $placement ) . '"'
			. ' viewBox="' . esc_attr( $shapes[ $shape ]['box'] ) . '"'
			. ' preserveAspectRatio="none" aria-hidden="true" focusable="false">';

		foreach ( $shapes[ $shape ]['paths'] as $index => $path ) {
			// Offsetting each path makes a two-stroke shape draw in sequence.
			$svg .= '<path d="' . esc_attr( $path ) . '" style="--kmpb-ah-delay:' . ( (int) $index * 220 ) . 'ms"/>';
		}

		return $svg . '</svg>';
	}

	protected function render() {

		$settings = $this->get_settings_for_display();
		$type     = ! empty( $settings['headline_type'] ) ? $settings['headline_type'] : 'rotate';
		$tag      = ! empty( $settings['html_tag'] ) ? $settings['html_tag'] : 'h2';

		// Only tags offered by the control are allowed through.
		if ( ! in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' ), true ) ) {
			$tag = 'h2';
		}

		$config = array(
			'type'      => $type,
			'animation' => ! empty( $settings['animation'] ) ? $settings['animation'] : 'typing',
			'speed'     => isset( $settings['speed']['size'] ) ? (int) $settings['speed']['size'] : 2200,
			'typeSpeed' => isset( $settings['type_speed']['size'] ) ? (int) $settings['type_speed']['size'] : 90,
			'loop'      => ! empty( $settings['loop'] ),
		);

		$classes = 'kmpb-ah kmpb-ah--' . $type;
		if ( 'rotate' === $type ) {
			$classes .= ' kmpb-ah--' . $config['animation'];
		}

		echo '<' . tag_escape( $tag ) . ' class="' . esc_attr( $classes ) . '"';
		echo ' data-kmpb-ah="' . esc_attr( wp_json_encode( $config ) ) . '">';

		if ( '' !== trim( (string) $settings['before_text'] ) ) {
			echo '<span class="kmpb-ah__before">' . esc_html( $settings['before_text'] ) . ' </span>';
		}

		if ( 'highlight' === $type ) {

			echo '<span class="kmpb-ah__focus kmpb-ah__focus--highlight">';
			echo esc_html( $settings['highlighted_text'] );
			// Built from the allow-listed shape map above, escaped inside.
			echo $this->shape_svg( $settings['shape'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</span>';

		} else {

			$words = preg_split( '/\r\n|\r|\n/', (string) $settings['rotating_text'] );
			$words = array_values( array_filter( array_map( 'trim', (array) $words ), 'strlen' ) );

			if ( ! $words ) {
				$words = array( '' );
			}

			echo '<span class="kmpb-ah__focus kmpb-ah__focus--rotate">';

			// Every word is printed. JS shows one at a time; with JS disabled
			// they all stay readable rather than the heading rendering empty.
			foreach ( $words as $index => $word ) {
				printf(
					'<span class="kmpb-ah__word%1$s">%2$s</span>',
					0 === $index ? ' is-active' : '',
					esc_html( $word )
				);
			}

			if ( 'typing' === $config['animation'] ) {
				echo '<span class="kmpb-ah__cursor" aria-hidden="true"></span>';
			}

			echo '</span>';
		}

		if ( '' !== trim( (string) $settings['after_text'] ) ) {
			echo '<span class="kmpb-ah__after"> ' . esc_html( $settings['after_text'] ) . '</span>';
		}

		echo '</' . tag_escape( $tag ) . '>';
	}
}
