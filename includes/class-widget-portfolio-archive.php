<?php
/**
 * Portfolio Archive widget.
 *
 * The main portfolio page: every project in a grid, with a sidebar that filters
 * by Industry and Service. Filtering happens in the browser against the term
 * ids printed on each card, so switching is instant and needs no request. The
 * trade-off is that it filters what the widget rendered, so leave "How Many"
 * at -1 on the archive page itself.
 *
 * Each card carries the four things asked for: the project logo, its name, a
 * link to the live site, and a call to action through to the project page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

class KMPB_Widget_Portfolio_Archive extends Widget_Base {

	public function get_name() {
		return 'kmpb-portfolio-archive';
	}

	public function get_title() {
		return esc_html__( 'Portfolio Archive', 'kirollos-magdy-portfolio-builder' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
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
		return array( 'portfolio', 'archive', 'projects', 'filter', 'grid', 'works', 'kirollos' );
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

		/* ---------------------------------------------------- query */

		$this->start_controls_section(
			'section_query',
			array( 'label' => esc_html__( 'Projects', 'kirollos-magdy-portfolio-builder' ) )
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'       => esc_html__( 'How Many', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => -1,
				'min'         => -1,
				'description' => esc_html__( 'Use -1 for every project. Filtering only searches what is rendered, so keep this at -1 on the portfolio page.', 'kirollos-magdy-portfolio-builder' ),
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

		$this->add_control(
			'order',
			array(
				'label'     => esc_html__( 'Order', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'DESC',
				'options'   => array(
					'DESC' => esc_html__( 'Descending', 'kirollos-magdy-portfolio-builder' ),
					'ASC'  => esc_html__( 'Ascending', 'kirollos-magdy-portfolio-builder' ),
				),
				'condition' => array( 'orderby!' => 'rand' ),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------- sidebar */

		$this->start_controls_section(
			'section_filters',
			array( 'label' => esc_html__( 'Filter Sidebar', 'kirollos-magdy-portfolio-builder' ) )
		);

		$this->add_control(
			'show_sidebar',
			array(
				'label'        => esc_html__( 'Show Sidebar', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'sidebar_position',
			array(
				'label'     => esc_html__( 'Position', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => array(
					'left'  => esc_html__( 'Left', 'kirollos-magdy-portfolio-builder' ),
					'right' => esc_html__( 'Right', 'kirollos-magdy-portfolio-builder' ),
				),
				'condition' => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_responsive_control(
			'sidebar_width',
			array(
				'label'     => esc_html__( 'Sidebar Width', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 160, 'max' => 480, 'step' => 10 ) ),
				'default'   => array( 'size' => 330 ),
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-side: {{SIZE}}px;' ),
				'condition' => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_control(
			'show_industry',
			array(
				'label'        => esc_html__( 'Industry Filter', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_control(
			'industry_label',
			array(
				'label'     => esc_html__( 'Industry Heading', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Industry', 'kirollos-magdy-portfolio-builder' ),
				'condition' => array( 'show_sidebar' => 'yes', 'show_industry' => 'yes' ),
			)
		);

		$this->add_control(
			'show_service',
			array(
				'label'        => esc_html__( 'Service Filter', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_control(
			'service_label',
			array(
				'label'     => esc_html__( 'Service Heading', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Service', 'kirollos-magdy-portfolio-builder' ),
				'condition' => array( 'show_sidebar' => 'yes', 'show_service' => 'yes' ),
			)
		);

		$this->add_control(
			'filter_columns',
			array(
				'label'     => esc_html__( 'Filter Columns', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '2',
				'options'   => array(
					'1' => esc_html__( 'One under the other', 'kirollos-magdy-portfolio-builder' ),
					'2' => esc_html__( 'Two side by side', 'kirollos-magdy-portfolio-builder' ),
				),
				'selectors' => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-filter-cols: {{VALUE}};' ),
				'condition' => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_control(
			'offcanvas',
			array(
				'label'        => esc_html__( 'Slide-in Filters on Small Screens', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'The sidebar becomes a panel that slides in from a floating filter button, instead of sitting above the grid and pushing it down the page.', 'kirollos-magdy-portfolio-builder' ),
				'condition'    => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_control(
			'offcanvas_below',
			array(
				'label'     => esc_html__( 'Slide In Below', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'tablet',
				'options'   => array(
					'tablet' => esc_html__( 'Tablet and phone (1024px)', 'kirollos-magdy-portfolio-builder' ),
					'mobile' => esc_html__( 'Phone only (767px)', 'kirollos-magdy-portfolio-builder' ),
				),
				'condition' => array( 'show_sidebar' => 'yes', 'offcanvas' => 'yes' ),
			)
		);

		$this->add_control(
			'offcanvas_text',
			array(
				'label'     => esc_html__( 'Filter Button Text', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Filters', 'kirollos-magdy-portfolio-builder' ),
				'condition' => array( 'show_sidebar' => 'yes', 'offcanvas' => 'yes' ),
			)
		);

		$this->add_control(
			'offcanvas_apply',
			array(
				'label'     => esc_html__( 'Apply Button Text', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Show results', 'kirollos-magdy-portfolio-builder' ),
				'condition' => array( 'show_sidebar' => 'yes', 'offcanvas' => 'yes' ),
			)
		);

		$this->add_control(
			'offcanvas_side',
			array(
				'label'     => esc_html__( 'Button Edge', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => array(
					'left'  => esc_html__( 'Left edge', 'kirollos-magdy-portfolio-builder' ),
					'right' => esc_html__( 'Right edge', 'kirollos-magdy-portfolio-builder' ),
				),
				'condition' => array( 'show_sidebar' => 'yes', 'offcanvas' => 'yes' ),
			)
		);

		$this->add_control(
			'offcanvas_label',
			array(
				'label'        => esc_html__( 'Show Button Text', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Off by default: the tab is the icon alone. The text stays in the markup either way, for screen readers.', 'kirollos-magdy-portfolio-builder' ),
				'condition'    => array( 'show_sidebar' => 'yes', 'offcanvas' => 'yes' ),
			)
		);

		$this->add_control(
			'offcanvas_top',
			array(
				'label'       => esc_html__( 'Button Distance From Top', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( '%', 'px' ),
				'range'       => array(
					'%'  => array( 'min' => 5, 'max' => 85, 'step' => 1 ),
					'px' => array( 'min' => 60, 'max' => 900, 'step' => 5 ),
				),
				'default'     => array(
					'unit' => '%',
					'size' => 30,
				),
				'description' => esc_html__( 'Measured down the screen, so the tab sits where a thumb reaches rather than under a sticky bottom bar.', 'kirollos-magdy-portfolio-builder' ),
				'selectors'   => array( '{{WRAPPER}} .kmpb-pa' => '--kmpb-pa-fab-top: {{SIZE}}{{UNIT}};' ),
				'condition'   => array( 'show_sidebar' => 'yes', 'offcanvas' => 'yes' ),
			)
		);

		$this->add_control(
			'show_counts',
			array(
				'label'        => esc_html__( 'Show Counts', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->add_control(
			'reset_text',
			array(
				'label'     => esc_html__( 'Reset Button Text', 'kirollos-magdy-portfolio-builder' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Clear filters', 'kirollos-magdy-portfolio-builder' ),
				'condition' => array( 'show_sidebar' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* ---------------------------------------------------- card */

		$this->start_controls_section(
			'section_card',
			array( 'label' => esc_html__( 'Cards', 'kirollos-magdy-portfolio-builder' ) )
		);

		$this->add_control(
			'layout',
			array(
				'label'   => esc_html__( 'Layout', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid' => esc_html__( 'Grid', 'kirollos-magdy-portfolio-builder' ),
					'list' => esc_html__( 'List', 'kirollos-magdy-portfolio-builder' ),
				),
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'Columns', 'kirollos-magdy-portfolio-builder' ),
				'type'           => Controls_Manager::SLIDER,
				'range'          => array( 'px' => array( 'min' => 1, 'max' => 4, 'step' => 1 ) ),
				'default'        => array( 'size' => 2 ),
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
				'label'       => esc_html__( 'Gallery Photos', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 4,
				'min'         => 0,
				'max'         => 4,
				'description' => esc_html__( 'Taken from the Project Gallery box on each project, in order. Four is the limit a project can hold.', 'kirollos-magdy-portfolio-builder' ),
			)
		);

		$this->add_control(
			'shot_fit',
			array(
				'label'       => esc_html__( 'Photo Fit', 'kirollos-magdy-portfolio-builder' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'full',
				'options'     => array(
					'full'  => esc_html__( 'Show the whole image', 'kirollos-magdy-portfolio-builder' ),
					'cover' => esc_html__( 'Crop to a shape', 'kirollos-magdy-portfolio-builder' ),
				),
				'description' => esc_html__( 'A full-page screenshot is cut off by cropping, so the whole image is shown by default and the card grows to fit it.', 'kirollos-magdy-portfolio-builder' ),
				'condition'   => array( 'gallery_count!' => 0 ),
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
			'show_date',
			array(
				'label'        => esc_html__( 'Show Work Date', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Set on each project, shown on the far side of the button.', 'kirollos-magdy-portfolio-builder' ),
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
			'show_excerpt',
			array(
				'label'        => esc_html__( 'Show Excerpt', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
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
			'show_head_link',
			array(
				'label'        => esc_html__( 'Live Link Beside Logo', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'A chip on the far side of the logo strip, opposite the logo.', 'kirollos-magdy-portfolio-builder' ),
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
			'show_link',
			array(
				'label'        => esc_html__( 'Live Link Above Button', 'kirollos-magdy-portfolio-builder' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
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
				'description'  => esc_html__( 'Opens the client\'s site in a new tab. Only appears on projects that have a live site address.', 'kirollos-magdy-portfolio-builder' ),
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
				'description'  => esc_html__( 'Off: the images are pictures, not doors to the project page.', 'kirollos-magdy-portfolio-builder' ),
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

		$this->add_control(
			'empty_text',
			array(
				'label'   => esc_html__( 'No Results Text', 'kirollos-magdy-portfolio-builder' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'No projects match those filters.', 'kirollos-magdy-portfolio-builder' ),
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
		$uid      = $this->get_id();

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
			! empty( $settings['order'] ) ? $settings['order'] : 'DESC'
		);

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p style="opacity:.6">' . esc_html__( 'No published projects yet.', 'kirollos-magdy-portfolio-builder' ) . '</p>';
			}
			return;
		}

		$show_sidebar = ! empty( $settings['show_sidebar'] );
		$industries   = ( $show_sidebar && ! empty( $settings['show_industry'] ) ) ? $this->terms( 'portfolio_industry' ) : array();
		$services     = ( $show_sidebar && ! empty( $settings['show_service'] ) ) ? $this->terms( 'portfolio_service' ) : array();

		$layout  = ( isset( $settings['layout'] ) && 'list' === $settings['layout'] ) ? 'list' : 'grid';
		$classes = 'kmpb-pa kmpb-pa--' . $layout;

		$has_side  = ( $show_sidebar && ( $industries || $services ) );
		$offcanvas = ( $has_side && ! empty( $settings['offcanvas'] ) );

		if ( $has_side ) {
			$classes .= ' kmpb-pa--has-side kmpb-pa--' . ( 'right' === $settings['sidebar_position'] ? 'right' : 'left' );
		}

		if ( $offcanvas ) {
			$classes .= ' kmpb-pa--oc kmpb-pa--oc-' . ( 'mobile' === $settings['offcanvas_below'] ? 'mobile' : 'tablet' );
			$classes .= ' kmpb-pa--fab-' . ( 'right' === $settings['offcanvas_side'] ? 'right' : 'left' );

			if ( ! empty( $settings['offcanvas_label'] ) ) {
				$classes .= ' kmpb-pa--fab-text';
			}
		}
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">

			<?php if ( $offcanvas ) : ?>
				<button type="button" class="kmpb-pa__fab" aria-expanded="false" aria-controls="kmpb-pa-side-<?php echo esc_attr( $uid ); ?>">
					<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
						stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
						<path d="M3 5h18l-7 8v6l-4 2v-8z" />
					</svg>
					<span class="kmpb-pa__fab-text"><?php echo esc_html( $settings['offcanvas_text'] ); ?></span>
					<span class="kmpb-pa__fab-count" hidden>0</span>
				</button>

				<div class="kmpb-pa__scrim" hidden></div>
			<?php endif; ?>

			<?php if ( $industries || $services ) : ?>
				<aside class="kmpb-pa__side" id="kmpb-pa-side-<?php echo esc_attr( $uid ); ?>">

					<?php if ( $offcanvas ) : ?>
						<div class="kmpb-pa__side-head">
							<strong><?php echo esc_html( $settings['offcanvas_text'] ); ?></strong>
							<button type="button" class="kmpb-pa__close" aria-label="<?php esc_attr_e( 'Close', 'kirollos-magdy-portfolio-builder' ); ?>">&times;</button>
						</div>
					<?php endif; ?>

					<div class="kmpb-pa__groups">
						<?php
						$this->render_filter_group( esc_html( $settings['industry_label'] ), 'industry', $industries, $uid, ! empty( $settings['show_counts'] ) );
						$this->render_filter_group( esc_html( $settings['service_label'] ), 'service', $services, $uid, ! empty( $settings['show_counts'] ) );
						?>
					</div>
					<button type="button" class="kmpb-pa__reset" hidden>
						<?php echo esc_html( $settings['reset_text'] ); ?>
					</button>

					<?php if ( $offcanvas ) : ?>
						<button type="button" class="kmpb-pa__apply">
							<?php echo esc_html( $settings['offcanvas_apply'] ); ?>
						</button>
					<?php endif; ?>
				</aside>
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
	 * @param string $label
	 * @param string $key    industry|service
	 * @param array  $terms
	 * @param string $uid
	 * @param bool   $counts
	 */
	private function render_filter_group( $label, $key, $terms, $uid, $counts ) {

		if ( ! $terms ) {
			return;
		}
		?>
		<div class="kmpb-pa__group">
			<h3 class="kmpb-pa__group-title"><?php echo esc_html( $label ); ?></h3>
			<ul class="kmpb-pa__list">
				<?php foreach ( $terms as $term ) : ?>
					<li>
						<label class="kmpb-pa__check">
							<input type="checkbox" data-kmpb-filter="<?php echo esc_attr( $key ); ?>"
								value="<?php echo esc_attr( (string) $term->term_id ); ?>">
							<span><?php echo esc_html( $term->name ); ?></span>
							<?php if ( $counts ) : ?>
								<em><?php echo esc_html( (string) $term->count ); ?></em>
							<?php endif; ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * @param array $settings
	 */
	private function render_card( $settings ) {
		KMPB_Portfolio_Card::render( $settings );
	}
}
