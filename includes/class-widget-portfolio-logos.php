<?php
/**
 * Portfolio Logo Wall widget.
 *
 * A compact companion to the archive: every client logo in a tight grid, with
 * the name, live link and call to action revealed on the card itself. Filtering
 * here is a row of pills across the top rather than a sidebar, so it suits the
 * narrower strips of the portfolio page.
 *
 * It shares the archive stylesheet and script, so both widgets filter the same
 * way and one asset pair covers them.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

class KMPB_Widget_Portfolio_Logos extends Widget_Base {

	public function get_name() {
		return 'kmpb-portfolio-logos';
	}

	public function get_title() {
		return esc_html__( 'Portfolio Logo Wall', 'kirollos-magdy-portfolio-builder' );
	}

	public function get_icon() {
		return 'eicon-logo';
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
		return array( 'portfolio', 'logos', 'clients', 'wall', 'projects', 'kirollos' );
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

	/**
	 * @param string $taxonomy
	 * @return array
	 */
	private function terms( $taxonomy ) {

		if ( ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);

		return is_wp_error( $terms ) ? array() : $terms;
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_query',
			array( 'label' => esc_html__( 'Projects', 'kirollos-magdy-portfolio-builder' ) )
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'   => esc_html__( 'How Many', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => -1,
				'min'     => -1,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => esc_html__( 'Order By', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'work_date',
				'options' => array(
					'work_date'  => esc_html__( 'Work Date', 'kirollos-magdy-portfolio-builder' ),
					'date'       => esc_html__( 'Date Published', 'kirollos-magdy-portfolio-builder' ),
					'title'      => esc_html__( 'Title', 'kirollos-magdy-portfolio-builder' ),
					'menu_order' => esc_html__( 'Manual Order', 'kirollos-magdy-portfolio-builder' ),
					'rand'       => esc_html__( 'Random', 'kirollos-magdy-portfolio-builder' ),
				),
				'description' => esc_html__( 'Work Date is the month set on each project. Date Published is when the project was added to the site, which is usually the day you imported it.', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_filters',
			array( 'label' => esc_html__( 'Filter Pills', 'kirollos-magdy-portfolio-builder' ) )
		);

		$this->add_control(
			'show_pills',
			array(
				'label'        => esc_html__( 'Show Pills', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'pill_source',
			array(
				'label'     => esc_html__( 'Filter By', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'industry',
				'options'   => array(
					'industry' => esc_html__( 'Industry', 'kirollos-magdy-portfolio-builder' ),
					'service'  => esc_html__( 'Service', 'kirollos-magdy-portfolio-builder' ),
				),
				'condition' => array( 'show_pills' => 'yes' ),
			)
		);

		$this->add_control(
			'all_text',
			array(
				'label'     => esc_html__( '"All" Pill Text', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'All', 'kirollos-magdy-portfolio-builder' ),
				'condition' => array( 'show_pills' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_card',
			array( 'label' => esc_html__( 'Cards', 'kirollos-magdy-portfolio-builder' ) )
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'Columns', 'kirollos-magdy-portfolio-builder' ),
				'type'           => Controls_Manager::SLIDER,
				'range'          => array( 'px' => array( 'min' => 2, 'max' => 6, 'step' => 1 ) ),
				'default'        => array( 'size' => 4 ),
				'tablet_default' => array( 'size' => 3 ),
				'mobile_default' => array( 'size' => 2 ),
				'selectors'      => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-cols: {{SIZE}};' ),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'     => esc_html__( 'Gap', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 48, 'step' => 2 ) ),
				'default'   => array( 'size' => 16 ),
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-gap: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'logo_height',
			array(
				'label'     => esc_html__( 'Logo Height', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 40, 'max' => 180, 'step' => 5 ) ),
				'default'   => array( 'size' => 90 ),
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-logo: {{SIZE}}px;' ),
			)
		);

		$this->add_control(
			'grayscale',
			array(
				'label'        => esc_html__( 'Grey Until Hover', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'show_link',
			array(
				'label'        => esc_html__( 'Show Live Link', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'   => esc_html__( 'Button Text', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'View Project', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$this->add_control(
			'empty_text',
			array(
				'label'   => esc_html__( 'No Results Text', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'No projects match that filter.', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$this->end_controls_section();

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
	 * Applies the chosen ordering to the query arguments.
	 *
	 * Work Date sorts on the work_date meta, which is a Y-m string, so plain
	 * string ordering is already chronological and needs no casting.
	 *
	 * A project with no work date would drop out of the results entirely if the
	 * meta were required, so the query asks for "has one OR has none" and sorts
	 * on the clause that has one. Undated projects therefore land at the end
	 * newest-first, and fall back to their published date among themselves.
	 *
	 * @param array  $args
	 * @param string $orderby
	 * @param string $order
	 * @return array
	 */
	private function apply_order( $args, $orderby, $order ) {

		if ( 'rand' === $orderby ) {
			$args['orderby'] = 'rand';
			return $args;
		}

		$order = ( 'ASC' === strtoupper( $order ) ) ? 'ASC' : 'DESC';

		if ( 'work_date' !== $orderby ) {
			$args['orderby'] = $orderby;
			$args['order']   = $order;
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
			'dated' => $order,
			'date'  => 'DESC',
		);

		return $args;
	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$ppp = isset( $settings['posts_per_page'] ) ? (int) $settings['posts_per_page'] : -1;
		if ( 0 === $ppp ) {
			$ppp = -1;
		}

		$args = array(
			'post_type'      => $this->post_type(),
			'post_status'    => 'publish',
			'posts_per_page' => $ppp,
			'no_found_rows'  => true,
		);

		$args = $this->apply_order(
			$args,
			! empty( $settings['orderby'] ) ? $settings['orderby'] : 'work_date',
			'DESC'
		);

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p style="opacity:.6">' . esc_html__( 'No published projects yet.', 'kirollos-magdy-portfolio-builder' ) . '</p>';
			}
			return;
		}

		$key      = ( 'service' === $settings['pill_source'] ) ? 'service' : 'industry';
		$taxonomy = ( 'service' === $key ) ? 'portfolio_service' : 'portfolio_industry';
		$pills    = ! empty( $settings['show_pills'] ) ? $this->terms( $taxonomy ) : array();

		$classes = 'kmpb-pa kmpb-pa--wall';
		if ( ! empty( $settings['grayscale'] ) ) {
			$classes .= ' kmpb-pa--grey';
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">

			<?php if ( $pills ) : ?>
				<div class="kmpb-pa__pills">
					<button type="button" class="kmpb-pa__pill is-active" data-kmpb-pill="<?php echo esc_attr( $key ); ?>" value="">
						<?php echo esc_html( $settings['all_text'] ); ?>
					</button>
					<?php foreach ( $pills as $term ) : ?>
						<button type="button" class="kmpb-pa__pill" data-kmpb-pill="<?php echo esc_attr( $key ); ?>"
							value="<?php echo esc_attr( (string) $term->term_id ); ?>">
							<?php echo esc_html( $term->name ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="kmpb-pa__main">
				<div class="kmpb-pa__grid">
					<?php
					while ( $query->have_posts() ) {
						$query->the_post();
						$this->render_card( $settings );
					}
					?>
				</div>
				<p class="kmpb-pa__empty" hidden><?php echo esc_html( $settings['empty_text'] ); ?></p>
			</div>
		</div>
		<?php
		wp_reset_postdata();
	}

	/**
	 * @param array $settings
	 */
	private function render_card( $settings ) {

		$post_id = get_the_ID();
		$link    = get_permalink( $post_id );
		$live    = get_post_meta( $post_id, 'project_url', true );

		$industry_ids = wp_get_post_terms( $post_id, 'portfolio_industry', array( 'fields' => 'ids' ) );
		$service_ids  = wp_get_post_terms( $post_id, 'portfolio_service', array( 'fields' => 'ids' ) );
		$industry_ids = is_wp_error( $industry_ids ) ? array() : $industry_ids;
		$service_ids  = is_wp_error( $service_ids ) ? array() : $service_ids;

		$logo_id = class_exists( 'KMPB_Portfolio_Meta' ) ? KMPB_Portfolio_Meta::logo_id( $post_id ) : get_post_thumbnail_id( $post_id );
		?>
		<article class="kmpb-pa__card kmpb-pa__card--wall"
			data-industry="<?php echo esc_attr( implode( ' ', array_map( 'strval', $industry_ids ) ) ); ?>"
			data-service="<?php echo esc_attr( implode( ' ', array_map( 'strval', $service_ids ) ) ); ?>">

			<a class="kmpb-pa__logo" href="<?php echo esc_url( $link ); ?>">
				<?php
				if ( $logo_id ) {
					echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'alt' => get_the_title( $post_id ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</a>

			<div class="kmpb-pa__body">
				<h3 class="kmpb-pa__name">
					<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
				</h3>

				<?php if ( ! empty( $settings['show_link'] ) && $live ) : ?>
					<a class="kmpb-pa__url" href="<?php echo esc_url( $live ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( preg_replace( '#^https?://(www\.)?#i', '', untrailingslashit( $live ) ) ); ?>
					</a>
				<?php endif; ?>

				<a class="kmpb-pa__cta" href="<?php echo esc_url( $link ); ?>">
					<?php echo esc_html( $settings['cta_text'] ); ?>
				</a>
			</div>
		</article>
		<?php
	}
}
