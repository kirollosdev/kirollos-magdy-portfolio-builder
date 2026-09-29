<?php
/**
 * Latest Projects widget.
 *
 * The portfolio cards, newest first, with nothing around them: no heading, no
 * link, no filter sidebar. It is meant to sit under a heading built in
 * Elementor, so the heading stays where the rest of the page's headings are
 * edited rather than being trapped in a widget's settings.
 *
 * The card itself is KMPB_Portfolio_Card, the same class the Portfolio Archive
 * draws, so the two are identical by construction and stay that way.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

class KMPB_Widget_Latest_Projects extends Widget_Base {

	public function get_name() {
		return 'kmpb-latest-projects';
	}

	public function get_title() {
		return esc_html__( 'Latest Projects', 'kirollos-magdy-portfolio-builder' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		$category = 'kirollos-portfolio';

		if ( class_exists( '\KirollosMagdy\PortfolioWidgets\Module' ) ) {
			$module = \KirollosMagdy\PortfolioWidgets\Module::instance();
			if ( $module && method_exists( $module, 'category_slug' ) ) {
				$category = $module->category_slug();
			}
		}

		return array( $category, 'general' );
	}

	public function get_keywords() {
		return array( 'portfolio', 'latest', 'recent', 'projects', 'work', 'home', 'kirollos' );
	}

	public function get_style_depends() {
		return array( 'kmpb-portfolio-archive' );
	}

	public function get_script_depends() {
		return array( 'kmpb-portfolio-archive' );
	}

	private function post_type() {
		return class_exists( 'KMPB_Portfolio_Meta' ) ? KMPB_Portfolio_Meta::post_type() : 'portfolios';
	}

	protected function register_controls() {

		/* ---------------------------------------------------- query */

		$this->start_controls_section(
			'section_query',
			array( 'label' => esc_html__( 'Projects', 'kirollos-magdy-portfolio-builder' ) )
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'   => esc_html__( 'How Many', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 3,
				'min'     => 1,
				'max'     => 12,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'       => esc_html__( 'Order By', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'work_date',
				'options'     => array(
					'work_date'  => esc_html__( 'Work Date', 'kirollos-magdy-portfolio-builder' ),
					'date'       => esc_html__( 'Date Published', 'kirollos-magdy-portfolio-builder' ),
					'menu_order' => esc_html__( 'Manual Order', 'kirollos-magdy-portfolio-builder' ),
					'rand'       => esc_html__( 'Random', 'kirollos-magdy-portfolio-builder' ),
				),
				'description' => esc_html__( 'Work Date is the month set on each project, which is what makes this strip actually show the latest work.', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------- cards */

		$this->start_controls_section(
			'section_card',
			array( 'label' => esc_html__( 'Cards', 'kirollos-magdy-portfolio-builder' ) )
		);

		$this->add_control(
			'display',
			array(
				'label'   => esc_html__( 'Display', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'slider',
				'options' => array(
					'slider' => esc_html__( 'Slider', 'kirollos-magdy-portfolio-builder' ),
					'grid'   => esc_html__( 'Grid', 'kirollos-magdy-portfolio-builder' ),
				),
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'     => esc_html__( 'Card Layout', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'grid',
				'options'   => array(
					'grid' => esc_html__( 'Stacked', 'kirollos-magdy-portfolio-builder' ),
					'list' => esc_html__( 'Side by side', 'kirollos-magdy-portfolio-builder' ),
				),
				'condition' => array( 'display' => 'grid' ),
			)
		);

		$this->add_control(
			'show_arrows',
			array(
				'label'        => esc_html__( 'Arrows', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'display' => 'slider' ),
			)
		);

		$this->add_control(
			'show_dots',
			array(
				'label'        => esc_html__( 'Dots', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'display' => 'slider' ),
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => esc_html__( 'Move On Its Own', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Stops as soon as someone hovers, focuses or swipes it, and never starts for a visitor who has asked for reduced motion.', 'kirollos-magdy-portfolio-builder' ),
				'condition'    => array( 'display' => 'slider' ),
			)
		);

		$this->add_control(
			'autoplay_delay',
			array(
				'label'      => esc_html__( 'Seconds Between Moves', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 2, 'max' => 12, 'step' => 1 ) ),
				'default'    => array( 'size' => 5 ),
				'condition'  => array( 'display' => 'slider', 'autoplay' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'Columns / Cards In View', 'kirollos-magdy-portfolio-builder' ),
				'type'           => Controls_Manager::SLIDER,
				'range'          => array( 'px' => array( 'min' => 1, 'max' => 4, 'step' => 1 ) ),
				'default'        => array( 'size' => 3 ),
				'tablet_default' => array( 'size' => 2 ),
				'mobile_default' => array( 'size' => 1 ),
				'selectors'      => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-cols: {{SIZE}};' ),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'     => esc_html__( 'Gap', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 60, 'step' => 2 ) ),
				'default'   => array( 'size' => 22 ),
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-gap: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'logo_height',
			array(
				'label'     => esc_html__( 'Logo Size', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 20, 'max' => 120, 'step' => 2 ) ),
				'default'   => array( 'size' => 40 ),
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-logo: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'gallery_count',
			array(
				'label'   => esc_html__( 'Gallery Photos', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 4,
				'min'     => 0,
				'max'     => 4,
			)
		);

		$this->add_control(
			'shot_fit',
			array(
				'label'     => esc_html__( 'Photo Fit', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'full',
				'options'   => array(
					'full'  => esc_html__( 'Show the whole image', 'kirollos-magdy-portfolio-builder' ),
					'cover' => esc_html__( 'Crop to a shape', 'kirollos-magdy-portfolio-builder' ),
				),
				'condition' => array( 'gallery_count!' => 0 ),
			)
		);

		$this->add_control(
			'shot_ratio',
			array(
				'label'     => esc_html__( 'Photo Shape', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '4 / 3',
				'options'   => array(
					'16 / 9' => esc_html__( 'Wide', 'kirollos-magdy-portfolio-builder' ),
					'4 / 3'  => esc_html__( 'Standard', 'kirollos-magdy-portfolio-builder' ),
					'1 / 1'  => esc_html__( 'Square', 'kirollos-magdy-portfolio-builder' ),
					'3 / 4'  => esc_html__( 'Tall', 'kirollos-magdy-portfolio-builder' ),
				),
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-shot-ratio: {{VALUE}};' ),
				'condition' => array( 'gallery_count!' => 0, 'shot_fit' => 'cover' ),
			)
		);

		$this->add_control(
			'show_head_link',
			array(
				'label'        => esc_html__( 'Live Link Beside Logo', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'head_link_text',
			array(
				'label'       => esc_html__( 'Chip Text', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'Leave empty for the domain', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array( 'show_head_link' => 'yes' ),
			)
		);

		$this->add_control(
			'show_terms',
			array(
				'label'        => esc_html__( 'Show Industry / Service', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_excerpt',
			array(
				'label'        => esc_html__( 'Show Excerpt', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_link',
			array(
				'label'        => esc_html__( 'Live Link Above Button', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_date',
			array(
				'label'        => esc_html__( 'Show Work Date', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'date_format',
			array(
				'label'     => esc_html__( 'Date Format', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'F Y',
				'options'   => array(
					'F Y' => esc_html__( 'August 2026', 'kirollos-magdy-portfolio-builder' ),
					'M Y' => esc_html__( 'Aug 2026', 'kirollos-magdy-portfolio-builder' ),
					'm/Y' => esc_html__( '08/2026', 'kirollos-magdy-portfolio-builder' ),
					'Y'   => esc_html__( '2026', 'kirollos-magdy-portfolio-builder' ),
				),
				'condition' => array( 'show_date' => 'yes' ),
			)
		);

		$this->add_control(
			'show_cta',
			array(
				'label'        => esc_html__( 'Show Project Button', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Off while the single project pages are not ready.', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'     => esc_html__( 'Button Text', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'View Project', 'kirollos-magdy-portfolio-builder' ),
				'condition' => array( 'show_cta' => 'yes' ),
			)
		);

		$this->add_control(
			'show_live_cta',
			array(
				'label'        => esc_html__( 'Show Live Website Button', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'live_cta_text',
			array(
				'label'     => esc_html__( 'Live Website Button Text', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Visit Website', 'kirollos-magdy-portfolio-builder' ),
				'condition' => array( 'show_live_cta' => 'yes' ),
			)
		);

		$this->add_control(
			'live_cta_style',
			array(
				'label'     => esc_html__( 'Live Website Button Style', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'solid',
				'options'   => array(
					'solid'   => esc_html__( 'Filled', 'kirollos-magdy-portfolio-builder' ),
					'outline' => esc_html__( 'Outlined', 'kirollos-magdy-portfolio-builder' ),
				),
				'condition' => array( 'show_live_cta' => 'yes' ),
			)
		);

		$this->add_control(
			'link_media',
			array(
				'label'        => esc_html__( 'Link the Photos and Logo', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'link_title',
			array(
				'label'        => esc_html__( 'Link the Project Name', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------- style */

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Style', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'accent',
			array(
				'label'     => esc_html__( 'Accent', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#9B00FF',
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-accent: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => esc_html__( 'Card Background', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-card: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'border_color',
			array(
				'label'     => esc_html__( 'Border', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-border: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Text', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-text: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'muted_color',
			array(
				'label'     => esc_html__( 'Muted Text', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-muted: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'radius',
			array(
				'label'     => esc_html__( 'Corner Radius', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 36 ) ),
				'default'   => array( 'size' => 14 ),
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-radius: {{SIZE}}px;' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .kmpb-pa__name',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Same ordering rules as the archive: a project with no work date still
	 * appears, it just falls to the end.
	 *
	 * @param array  $args
	 * @param string $orderby
	 * @return array
	 */
	private function apply_order( $args, $orderby ) {

		if ( 'rand' === $orderby ) {
			$args['orderby'] = 'rand';
			return $args;
		}

		if ( 'work_date' !== $orderby ) {
			$args['orderby'] = $orderby;
			$args['order']   = 'DESC';
			return $args;
		}

		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'relation' => 'OR',
			'dated'    => array(
				'key'     => 'work_date',
				'compare' => 'EXISTS',
			),
			'undated'  => array(
				'key'     => 'work_date',
				'compare' => 'NOT EXISTS',
			),
		);

		$args['orderby'] = array(
			'dated' => 'DESC',
			'date'  => 'DESC',
		);

		return $args;
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$args = array(
			'post_type'      => $this->post_type(),
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, (int) $settings['posts_per_page'] ),
			'no_found_rows'  => true,
		);

		$args = $this->apply_order( $args, ! empty( $settings['orderby'] ) ? $settings['orderby'] : 'work_date' );

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p style="opacity:.6">' . esc_html__( 'No published projects yet.', 'kirollos-magdy-portfolio-builder' ) . '</p>';
			}
			return;
		}

		$slider = ( ! isset( $settings['display'] ) || 'grid' !== $settings['display'] );
		$layout = ( ! $slider && 'list' === $settings['layout'] ) ? 'list' : 'grid';

		$classes = 'kmpb-pa kmpb-pa--' . $layout;

		if ( $slider ) {
			$classes .= ' kmpb-pa--slider';
		}

		$delay = isset( $settings['autoplay_delay']['size'] ) ? (int) $settings['autoplay_delay']['size'] : 5;
		?>
		<div class="<?php echo esc_attr( $classes ); ?>"
			<?php if ( $slider && ! empty( $settings['autoplay'] ) ) : ?>
				data-kmpb-autoplay="<?php echo esc_attr( (string) max( 2, $delay ) ); ?>"
			<?php endif; ?>>

			<div class="kmpb-pa__grid">
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					KMPB_Portfolio_Card::render( $settings );
				}
				?>
			</div>

			<?php if ( $slider && ( ! empty( $settings['show_arrows'] ) || ! empty( $settings['show_dots'] ) ) ) : ?>
				<div class="kmpb-pa__nav">

					<?php if ( ! empty( $settings['show_arrows'] ) ) : ?>
						<button type="button" class="kmpb-pa__arrow kmpb-pa__arrow--prev"
							aria-label="<?php esc_attr_e( 'Previous projects', 'kirollos-magdy-portfolio-builder' ); ?>">
							<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
								stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
								<path d="m14 6-6 6 6 6" />
							</svg>
						</button>
					<?php endif; ?>

					<?php if ( ! empty( $settings['show_dots'] ) ) : ?>
						<div class="kmpb-pa__dots" role="tablist"></div>
					<?php endif; ?>

					<?php if ( ! empty( $settings['show_arrows'] ) ) : ?>
						<button type="button" class="kmpb-pa__arrow kmpb-pa__arrow--next"
							aria-label="<?php esc_attr_e( 'More projects', 'kirollos-magdy-portfolio-builder' ); ?>">
							<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
								stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
								<path d="m10 6 6 6-6 6" />
							</svg>
						</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		wp_reset_postdata();
	}
}
