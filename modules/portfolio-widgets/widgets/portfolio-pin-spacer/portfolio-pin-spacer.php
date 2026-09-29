<?php

namespace KirollosMagdy\PortfolioWidgets\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

if (!defined('ABSPATH')) exit;

class Portfolio_Pin_Spacer extends Base
{
    const SLUG = 'portfolio-pin-spacer';

    public function get_script_depends()
    {
        return ['gsap', 'ScrollTrigger', $this->handle()];
    }

    public function get_title()
    {
        return esc_html__('Portfolio Pin Spacer', 'kirollos-magdy-portfolio-builder');
    }

    public function get_icon()
    {
        return 'eicon-gallery-grid pw-widget-badge';
    }

    /**
     * Portfolio posts for manual Select2 (id => title).
     *
     * @return array<int, string>
     */
    protected function get_portfolio_posts_select_options()
    {
        if (! post_type_exists('portfolios')) {
            return [];
        }

        $posts = get_posts(array(
            'post_type'      => 'portfolios',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
            'fields'         => 'ids',
        ));

        $options = array();
        foreach ($posts as $post_id) {
            $options[(int) $post_id] = get_the_title($post_id);
        }

        return $options;
    }

    /**
     * @param mixed $raw From Elementor SELECT2 (array or comma-separated string).
     * @return int[]
     */
    protected function parse_manual_portfolio_ids($raw)
    {
        if (empty($raw)) {
            return array();
        }
        if (is_array($raw)) {
            return array_values(array_filter(array_map('absint', $raw)));
        }
        if (is_string($raw)) {
            return array_values(array_filter(array_map('absint', explode(',', $raw))));
        }

        return array();
    }

    protected function register_controls()
    {
        // Content Section
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Content Settings', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'       => esc_html__('Number of Posts', 'kirollos-magdy-portfolio-builder'),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 3,
                'min'         => 1,
                'max'         => 10,
                'description' => esc_html__('Used only when “Specific portfolios” below is empty (latest posts by date).', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'manual_portfolio_posts',
            [
                'label'       => esc_html__('Specific portfolios', 'kirollos-magdy-portfolio-builder'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_portfolio_posts_select_options(),
                'multiple'    => true,
                'label_block' => true,
                'description' => esc_html__('Pick posts by name. Order here is the order on the page. Leave empty to use “Number of Posts” instead.', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'show_categories',
            [
                'label' => esc_html__('Show Categories', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => esc_html__('Show Title', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_divider',
            [
                'label' => esc_html__('Show Divider', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Wrapper Style Section
        $this->start_controls_section(
            'section_wrapper_style',
            [
                'label' => esc_html__('Wrapper Style', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'wrapper_padding',
            [
                'label' => esc_html__('Wrapper Padding', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'wrapper_background',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .portfolio-wrapper',
            ]
        );

        $this->add_control(
            'content_position',
            [
                'label' => esc_html__('Content Position', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'default' => 'absolute',
                'options' => [
                    'relative' => esc_html__('Relative', 'kirollos-magdy-portfolio-builder'),
                    'absolute' => esc_html__('Absolute', 'kirollos-magdy-portfolio-builder'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-wrapper' => 'position: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_bottom',
            [
                'label' => esc_html__('Top Position', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-wrapper' => 'top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'content_position' => 'absolute',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_left',
            [
                'label' => esc_html__('Left Position', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-wrapper' => 'left: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'content_position' => 'absolute',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_width',
            [
                'label' => esc_html__('Content Width', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 2000,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-wrapper' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Card Style Section
        $this->start_controls_section(
            'section_card_style',
            [
                'label' => esc_html__('Card Style', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'card_padding',
            [
                'label' => esc_html__('Padding', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .tc-card-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'card_margin',
            [
                'label' => esc_html__('Margin', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .tc-card-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'card_background',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .tc-card-item',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'selector' => '{{WRAPPER}} .tc-card-item',
            ]
        );

        $this->add_responsive_control(
            'card_border_radius',
            [
                'label' => esc_html__('Border Radius', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .tc-card-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .tc-card-item',
            ]
        );

        $this->end_controls_section();

        // Image Style Section
        $this->start_controls_section(
            'section_image_style',
            [
                'label' => esc_html__('Image Style', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label' => esc_html__('Height', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh', '%'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                    ],
                    'vh' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'vh',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .tc-card-item .card-image' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .tc-card-item .card-image img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_object_fit',
            [
                'label' => esc_html__('Object Fit', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'cover' => esc_html__('Cover', 'kirollos-magdy-portfolio-builder'),
                    'contain' => esc_html__('Contain', 'kirollos-magdy-portfolio-builder'),
                    'fill' => esc_html__('Fill', 'kirollos-magdy-portfolio-builder'),
                ],
                'default' => 'cover',
                'selectors' => [
                    '{{WRAPPER}} .tc-card-item .card-image img' => 'object-fit: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_border_radius',
            [
                'label' => esc_html__('Border Radius', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .tc-card-item .card-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Categories Style Section
        $this->start_controls_section(
            'section_categories_style',
            [
                'label' => esc_html__('Categories Style', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_categories' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'categories_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .portfolio-categories',
            ]
        );

        $this->add_control(
            'categories_color',
            [
                'label' => esc_html__('Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFF',
                'selectors' => [
                    '{{WRAPPER}} .portfolio-categories' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'categories_gap',
            [
                'label' => esc_html__('Gap Between Items', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-categories' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'categories_margin',
            [
                'label' => esc_html__('Margin', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-categories' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Divider Style Section
        $this->start_controls_section(
            'section_divider_style',
            [
                'label' => esc_html__('Divider Style', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_divider' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'divider_width',
            [
                'label' => esc_html__('Width', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 229,
                ],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-divider' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'divider_height',
            [
                'label' => esc_html__('Height', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 2,
                ],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-divider' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'divider_color',
            [
                'label' => esc_html__('Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .portfolio-divider' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Title Style Section
        $this->start_controls_section(
            'section_title_style',
            [
                'label' => esc_html__('Title Style', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .portfolio-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFF',
                'selectors' => [
                    '{{WRAPPER}} .portfolio-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label' => esc_html__('Margin', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Content Flex Settings
        $this->start_controls_section(
            'section_content_flex',
            [
                'label' => esc_html__('Content Layout', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'content_gap',
            [
                'label' => esc_html__('Gap', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 32,
                ],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-content' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_max_width',
            [
                'label' => esc_html__('Max Width', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 2000,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1200,
                ],
                'selectors' => [
                    '{{WRAPPER}} .portfolio-content' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_justify',
            [
                'label' => esc_html__('Justify Content', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__('Start', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-justify-start-h',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-justify-center-h',
                    ],
                    'flex-end' => [
                        'title' => esc_html__('End', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-justify-end-h',
                    ],
                ],
                'default' => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .portfolio-content' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_align',
            [
                'label' => esc_html__('Align Items', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__('Start', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-align-start-v',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-align-center-v',
                    ],
                    'flex-end' => [
                        'title' => esc_html__('End', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-align-end-v',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .portfolio-content' => 'align-items: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Animation Settings
        $this->start_controls_section(
            'section_animation_settings',
            [
                'label' => esc_html__('Animation Settings', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'enable_animation',
            [
                'label' => esc_html__('Enable Pin Animation', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'scale_ratio',
            [
                'label' => esc_html__('Scale Ratio', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 0.1,
                        'step' => 0.001,
                    ],
                ],
                'default' => [
                    'size' => 0.025,
                ],
                'condition' => [
                    'enable_animation' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $manual_ids = $this->parse_manual_portfolio_ids($settings['manual_portfolio_posts'] ?? array());

        $args = array(
            'post_type'      => 'portfolios',
            'post_status'    => 'publish',
        );

        if (! empty($manual_ids)) {
            $args['post__in']       = $manual_ids;
            $args['orderby']        = 'post__in';
            $args['posts_per_page'] = count($manual_ids);
        } else {
            $args['posts_per_page'] = (int) $settings['posts_per_page'];
            $args['orderby']        = 'date';
            $args['order']          = 'DESC';
        }

        $query = new \WP_Query($args);

        if ($query->have_posts()) :
            $animation_class = $settings['enable_animation'] === 'yes' ? 'tc-cards-animation' : '';
?>
            <div class="kmpw-portfolio-pin-spacer <?php echo esc_attr($animation_class); ?>" data-scale-ratio="<?php echo esc_attr($settings['scale_ratio']['size']); ?>">
                <?php while ($query->have_posts()) : $query->the_post();
                    $terms = get_the_terms(get_the_ID(), 'portfolio_category');
                    $categories_html = '';
                    if ($terms && !is_wp_error($terms)) {
                        $total_terms = count($terms);
                        $i = 0;
                        foreach ($terms as $term) {
                            $i++;
                            // الفاصلة بعد كل كاتيجوري ما عدا الأخيرة
                            $separator = ($i < $total_terms) ? ',' : '';
                            $categories_html .= '<span class="portfolio-category-item">' . esc_html($term->name) . $separator . '</span>';
                        }
                    }

                    $permalink = get_permalink();
                ?>
                    <a href="<?php echo esc_url($permalink); ?>" class="tc-card-item-link">
                        <div class="tc-card-item">
                            <div class="tc-card-overlay"></div>
                            <div class="card-image">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('full'); ?>
                                <?php endif; ?>
                            </div>

                            <div class="portfolio-wrapper kmpw-pin-spacer-text-desktop-only">
                                <div class="portfolio-content">
                                    <?php if ($settings['show_categories'] === 'yes' && !empty($categories_html)) : ?>
                                        <h3 class="portfolio-categories"><?php echo $categories_html; ?></h3>
                                    <?php endif; ?>

                                    <?php if ($settings['show_divider'] === 'yes') : ?>
                                        <div class="portfolio-divider"></div>
                                    <?php endif; ?>

                                    <?php if ($settings['show_title'] === 'yes') : ?>
                                        <h3 class="portfolio-title"><?php the_title(); ?></h3>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
<?php
            wp_reset_postdata();
        endif;
    }
}
