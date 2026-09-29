<?php
namespace KirollosMagdy\LatestArticles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

if ( ! class_exists( 'Elementor\Widget_Base' ) ) {
	return;
}

class Widget_Latest_Articles extends Widget_Base {

	public function get_name() {
		return 'kmpb-latest-articles';
	}

	public function get_title() {
		return esc_html__( 'Latest Articles', 'kirollos-magdy-portfolio-builder' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return array( 'general' );
	}

	public function get_keywords() {
		return array( 'latest', 'articles', 'posts', 'blog', 'grid', 'categories' );
	}

	public function get_style_depends() {
		return array( 'kmpb-latest-articles' );
	}

	public function get_script_depends() {
		return array( 'kmpb-latest-articles' );
	}

	private function get_category_options() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);

		$options = array();
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ (string) $term->term_id ] = $term->name;
			}
		}
		return $options;
	}

	private function default_categories() {
		$options = $this->get_category_options();
		return array_slice( array_keys( $options ), 0, 4 );
	}

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			array(
				'label' => esc_html__( 'Content', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'heading',
			array(
				'label'       => esc_html__( 'Heading', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Latest Articles', 'kirollos-magdy-portfolio-builder' ),
				'placeholder' => esc_html__( 'Latest Articles', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$this->add_control(
			'categories',
			array(
				'label'       => esc_html__( 'Categories in Navigation', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $this->get_category_options(),
				'default'     => $this->default_categories(),
				'label_block' => true,
				'description' => esc_html__( 'Choose the categories shown in the top navigation and used to filter the articles.', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$this->add_control(
			'show_all',
			array(
				'label'        => esc_html__( 'Show All Filter', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'kirollos-magdy-portfolio-builder' ),
				'label_off'    => esc_html__( 'Hide', 'kirollos-magdy-portfolio-builder' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'Columns', 'kirollos-magdy-portfolio-builder' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'selectors'      => array(
					'{{WRAPPER}} .kmla-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->add_control(
			'initial_visible',
			array(
				'label'   => esc_html__( 'Articles Initially Visible', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 100,
			)
		);

		$this->add_control(
			'load_count',
			array(
				'label'   => esc_html__( 'Articles Per Load More', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 3,
				'min'     => 1,
				'max'     => 50,
			)
		);

		$this->add_control(
			'excerpt_words',
			array(
				'label'   => esc_html__( 'Excerpt Words', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 18,
				'min'     => 0,
				'max'     => 80,
			)
		);

		$this->add_control(
			'show_excerpt',
			array(
				'label'        => esc_html__( 'Show Excerpt', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_date',
			array(
				'label'        => esc_html__( 'Show Date', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'load_more_text',
			array(
				'label'   => esc_html__( 'Load More Text', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Load more', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_header',
			array(
				'label' => esc_html__( 'Header & Navigation', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'heading_color',
			array(
				'label'     => esc_html__( 'Heading Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-heading' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'heading_typography',
				'selector' => '{{WRAPPER}} .kmla-heading',
			)
		);

		$this->add_responsive_control(
			'header_gap',
			array(
				'label'      => esc_html__( 'Header Gap', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmla-header' => 'column-gap: {{SIZE}}{{UNIT}}; row-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'nav_gap',
			array(
				'label'      => esc_html__( 'Navigation Gap', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 8 ),
				'selectors'  => array( '{{WRAPPER}} .kmla-nav' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'nav_color',
			array(
				'label'     => esc_html__( 'Non-Active Navigation Text Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-nav-link' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'nav_active_color',
			array(
				'label'     => esc_html__( 'Active Navigation Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-nav-link.is-active' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'nav_border_color',
			array(
				'label'     => esc_html__( 'Navigation Border Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-nav-link' => 'border-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'nav_active_background',
			array(
				'label'     => esc_html__( 'Active Navigation Background', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-nav-link.is-active' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'nav_hover_color',
			array(
				'label'     => esc_html__( 'Hover Text Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-nav-link:not(.is-active):hover' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'nav_hover_background',
			array(
				'label'     => esc_html__( 'Hover Background', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-nav-link:not(.is-active):hover' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'nav_hover_border',
			array(
				'label'     => esc_html__( 'Hover Border Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-nav-link:not(.is-active):hover' => 'border-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'nav_typography',
				'selector' => '{{WRAPPER}} .kmla-nav-link',
			)
		);

		$this->add_responsive_control(
			'nav_padding',
			array(
				'label'      => esc_html__( 'Navigation Padding', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .kmla-nav-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'nav_radius',
			array(
				'label'      => esc_html__( 'Navigation Radius', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmla-nav-link' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_cards',
			array(
				'label' => esc_html__( 'Cards', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'grid_gap',
			array(
				'label'      => esc_html__( 'Card Gap', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 24 ),
				'selectors'  => array( '{{WRAPPER}} .kmla-grid' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'card_background',
				'selector' => '{{WRAPPER}} .kmla-card',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .kmla-card',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .kmla-card',
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => esc_html__( 'Card Radius', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( '{{WRAPPER}} .kmla-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => esc_html__( 'Card Padding', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .kmla-card-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		// The image always fills its frame (object-fit: cover), so the ratio decides the shape.
		// 1200 / 630 matches the blog covers exactly, so nothing is cropped by default.
		$this->add_responsive_control(
			'image_ratio',
			array(
				'label'   => esc_html__( 'Image Aspect Ratio', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'1200 / 630' => esc_html__( 'Blog cover (1.91:1)', 'kirollos-magdy-portfolio-builder' ),
					'16 / 9'     => '16:9',
					'3 / 2'      => '3:2',
					'4 / 3'      => '4:3',
					'1 / 1'      => '1:1',
				),
				'default'   => '1200 / 630',
				'selectors' => array( '{{WRAPPER}} .kmla-image-wrap' => 'aspect-ratio: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'image_radius',
			array(
				'label'      => esc_html__( 'Image Radius', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( '{{WRAPPER}} .kmla-image, {{WRAPPER}} .kmla-image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_content',
			array(
				'label' => esc_html__( 'Card Content', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$style_groups = array(
			array( 'category', 'Category', '.kmla-category' ),
			array( 'title', 'Title', '.kmla-title' ),
			array( 'excerpt', 'Excerpt', '.kmla-excerpt' ),
			array( 'meta', 'Meta', '.kmla-meta' ),
		);

		foreach ( $style_groups as $group ) {
			$key = $group[0];
			$label = $group[1];
			$selector = $group[2];

			$this->add_control(
				$key . '_color',
				array(
					'label'     => $label . ' Color',
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( '{{WRAPPER}} ' . $selector => 'color: {{VALUE}};' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => $key . '_typography',
					'selector' => '{{WRAPPER}} ' . $selector,
				)
			);

			$this->add_responsive_control(
				$key . '_margin',
				array(
					'label'      => $label . ' Margin',
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', 'rem' ),
					'selectors'  => array( '{{WRAPPER}} ' . $selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				)
			);
		}

		$this->add_control(
			'category_background',
			array(
				'label'     => esc_html__( 'Category Background', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-category' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'category_radius',
			array(
				'label'      => esc_html__( 'Category Radius', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 50 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmla-category' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'style_button',
			array(
				'label' => esc_html__( 'Load More Button', 'kirollos-magdy-portfolio-builder' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'button_color',
			array(
				'label'     => esc_html__( 'Text Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-load-more' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'button_background',
			array(
				'label'     => esc_html__( 'Background', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-load-more' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'button_hover_color',
			array(
				'label'     => esc_html__( 'Hover Text Color', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-load-more:hover' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'button_hover_background',
			array(
				'label'     => esc_html__( 'Hover Background', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .kmla-load-more:hover' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .kmla-load-more',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .kmla-load-more',
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .kmla-load-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'button_radius',
			array(
				'label'      => esc_html__( 'Radius', 'kirollos-magdy-portfolio-builder' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'selectors'  => array( '{{WRAPPER}} .kmla-load-more' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();
	}

	private function excerpt( $post_id, $words ) {
		$text = get_the_excerpt( $post_id );
		if ( ! $text ) {
			$text = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
		}
		$text = wp_strip_all_tags( $text );
		return wp_trim_words( $text, max( 0, (int) $words ) );
	}

	private function card( $post_id, $selected_terms, $show_excerpt, $show_date, $excerpt_words ) {
		$post_terms = wp_get_post_categories( $post_id );
		$matching = array_intersect( array_map( 'intval', $selected_terms ), array_map( 'intval', $post_terms ) );
		if ( empty( $matching ) ) {
			return '';
		}

		$primary = reset( $matching );
		$term = get_term( $primary, 'category' );
		$term_name = ( $term && ! is_wp_error( $term ) ) ? $term->name : '';
		$term_ids = implode( ',', array_map( 'intval', $post_terms ) );

		ob_start();
		?>
		<article class="kmla-card" data-categories="<?php echo esc_attr( $term_ids ); ?>">
			<a class="kmla-card-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
				<div class="kmla-image-wrap">
					<?php if ( has_post_thumbnail( $post_id ) ) : ?>
						<?php echo get_the_post_thumbnail(
							$post_id,
							'large',
							array(
								'class'    => 'kmla-image',
								'loading'  => 'lazy',
								'decoding' => 'async',
								'sizes'    => '(max-width: 767px) 100vw, (max-width: 1024px) 50vw, 400px',
							)
						); ?>
					<?php else : ?>
						<div class="kmla-placeholder" aria-hidden="true"></div>
					<?php endif; ?>
				</div>
				<div class="kmla-card-body">
					<?php if ( $term_name ) : ?>
						<span class="kmla-category"><?php echo esc_html( $term_name ); ?></span>
					<?php endif; ?>
					<h3 class="kmla-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
					<?php if ( $show_excerpt ) : ?>
						<p class="kmla-excerpt"><?php echo esc_html( $this->excerpt( $post_id, $excerpt_words ) ); ?></p>
					<?php endif; ?>
					<?php if ( $show_date ) : ?>
						<div class="kmla-meta"><span><?php echo esc_html( get_the_date( '', $post_id ) ); ?></span></div>
					<?php endif; ?>
				</div>
			</a>
		</article>
		<?php
		return ob_get_clean();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$selected = ! empty( $settings['categories'] ) ? array_map( 'intval', (array) $settings['categories'] ) : array();

		$heading = isset( $settings['heading'] ) ? $settings['heading'] : 'Latest Articles';
		$initial = max( 1, (int) ( $settings['initial_visible'] ?? 6 ) );
		$load_count = max( 1, (int) ( $settings['load_count'] ?? 3 ) );
		$excerpt_words = max( 0, (int) ( $settings['excerpt_words'] ?? 18 ) );

		if ( empty( $selected ) ) {
			$terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true ) );
			if ( ! is_wp_error( $terms ) ) {
				$selected = array_map( 'intval', wp_list_pluck( array_slice( $terms, 0, 4 ), 'term_id' ) );
			}
		}

		$query_args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 100,
			'ignore_sticky_posts' => true,
		);

		if ( ! empty( $selected ) ) {
			$query_args['category__in'] = $selected;
		}

		$posts = get_posts( $query_args );
		$widget_id = 'kmla-' . $this->get_id();
		?>
		<section id="<?php echo esc_attr( $widget_id ); ?>" class="kmla" data-initial="<?php echo esc_attr( $initial ); ?>" data-load="<?php echo esc_attr( $load_count ); ?>">
			<div class="kmla-header">
				<?php if ( $heading !== '' ) : ?>
					<h2 class="kmla-heading"><?php echo esc_html( $heading ); ?></h2>
				<?php endif; ?>

				<nav class="kmla-nav" aria-label="<?php esc_attr_e( 'Article categories', 'kirollos-magdy-portfolio-builder' ); ?>">
					<?php if ( 'yes' === ( $settings['show_all'] ?? 'yes' ) ) : ?>
						<button type="button" class="kmla-nav-link is-active" data-filter="all"><?php esc_html_e( 'All', 'kirollos-magdy-portfolio-builder' ); ?></button>
					<?php endif; ?>
					<?php foreach ( $selected as $term_id ) :
						$term = get_term( $term_id, 'category' );
						if ( ! $term || is_wp_error( $term ) ) continue;
						?>
						<button type="button" class="kmla-nav-link" data-filter="<?php echo esc_attr( $term_id ); ?>"><?php echo esc_html( $term->name ); ?></button>
					<?php endforeach; ?>
				</nav>
			</div>

			<div class="kmla-grid">
				<?php
				$rendered = 0;
				foreach ( $posts as $post ) {
					$html = $this->card(
						$post->ID,
						$selected,
						'yes' === ( $settings['show_excerpt'] ?? 'yes' ),
						'yes' === ( $settings['show_date'] ?? 'yes' ),
						$excerpt_words
					);
					if ( $html ) {
						$rendered++;
						echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
				?>
			</div>

			<?php if ( $rendered > $initial ) : ?>
				<div class="kmla-load-wrap">
					<button type="button" class="kmla-load-more"><?php echo esc_html( $settings['load_more_text'] ?? 'Load more' ); ?></button>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}
}
