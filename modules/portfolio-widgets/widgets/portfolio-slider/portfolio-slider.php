<?php

namespace KirollosMagdy\PortfolioWidgets\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

class Portfolio_Slider extends Base
{
    const SLUG = 'portfolio-slider';

    public function get_script_depends()
    {
        return ['gsap', 'ScrollTrigger', $this->handle()];
    }


    public function get_title()
    {
        return esc_html__('Portfolio Slider', 'kirollos-magdy-portfolio-builder');
    }

    public function get_icon()
    {
        return 'eicon-gallery-grid pw-widget-badge';
    }

    public function get_keywords()
    {
        return ['portfolio', 'gsap', 'scroll', 'animation'];
    }

    protected function register_controls()
    {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Portfolio Settings', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'posts_per_category',
            [
                'label' => esc_html__('Posts Per Category', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 3,
                'min' => 1,
                'max' => 10,
            ]
        );

        $this->add_control(
            'show_all_filter',
            [
                'label' => esc_html__('Show "All" Filter', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => esc_html__('Order By', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => esc_html__('Date', 'kirollos-magdy-portfolio-builder'),
                    'title' => esc_html__('Title', 'kirollos-magdy-portfolio-builder'),
                    'rand' => esc_html__('Random', 'kirollos-magdy-portfolio-builder'),
                    'menu_order' => esc_html__('Menu Order', 'kirollos-magdy-portfolio-builder'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => esc_html__('Order', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'ASC' => esc_html__('Ascending', 'kirollos-magdy-portfolio-builder'),
                    'DESC' => esc_html__('Descending', 'kirollos-magdy-portfolio-builder'),
                ],
            ]
        );

        $this->end_controls_section();

        // Filter Style Section
        $this->start_controls_section(
            'filter_style_section',
            [
                'label' => esc_html__('Filter Buttons', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'filter_typography',
                'selector' => '{{WRAPPER}} .kmpw-portfolio-filter-btn',
            ]
        );

        $this->add_control(
            'filter_color',
            [
                'label' => esc_html__('Text Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-portfolio-filter-btn' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'filter_active_color',
            [
                'label' => esc_html__('Active Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-portfolio-filter-btn.active' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        // Portfolio Card Style
        $this->start_controls_section(
            'card_style_section',
            [
                'label' => esc_html__('Portfolio Cards', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'card_title_typography',
                'selector' => '{{WRAPPER}} .kmpw-portfolio-card-title',
            ]
        );

        $this->add_control(
            'card_border_radius',
            [
                'label' => esc_html__('Border Radius', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-portfolio-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $posts_per_category = $settings['posts_per_category'];
        $show_all = $settings['show_all_filter'];
        $orderby = isset($settings['orderby']) ? $settings['orderby'] : 'date';
        $order = isset($settings['order']) ? $settings['order'] : 'DESC';
        $widget_id = $this->get_id();

        // Get portfolio categories
        $categories = get_terms([
            'taxonomy' => 'portfolio_category',
            'hide_empty' => true,
        ]);

        if (is_wp_error($categories) || empty($categories)) {
            echo '<div class="kmpw-no-portfolios">';
            echo '<p>' . esc_html__('No portfolio categories found. Please add categories first.', 'kirollos-magdy-portfolio-builder') . '</p>';
            echo '</div>';
            return;
        }

?>
        <div class="kmpw-portfolio-slider-wrapper" data-widget-id="<?php echo esc_attr($widget_id); ?>">

            <!-- Filter Buttons -->
            <div class="kmpw-portfolio-filters">
                <?php if ($show_all === 'yes') : ?>
                    <button class="kmpw-portfolio-filter-btn active" data-category="all">
                        <?php echo esc_html__('All', 'kirollos-magdy-portfolio-builder'); ?>
                    </button>
                <?php endif; ?>

                <?php foreach ($categories as $index => $category) : ?>
                    <button class="kmpw-portfolio-filter-btn"
                        data-category="<?php echo esc_attr($category->term_id); ?>"
                        style="transition-delay: <?php echo ($index * 0.1); ?>s">
                        <?php echo esc_html($category->name); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Portfolio Container with Pin Spacer -->
            <div class="kmpw-portfolio-slider-container">
                <div class="kmpw-portfolio-container">

                    <?php
                    // Get all portfolio posts from all categories
                    $all_posts = [];

                    foreach ($categories as $category) {
                        $args = [
                            'post_type' => 'portfolios',
                            'posts_per_page' => $posts_per_category,
                            'orderby' => $orderby,
                            'order' => $order,
                            'tax_query' => [
                                [
                                    'taxonomy' => 'portfolio_category',
                                    'field' => 'term_id',
                                    'terms' => $category->term_id,
                                ],
                            ],
                        ];

                        $query = new \WP_Query($args);

                        if ($query->have_posts()) {
                            while ($query->have_posts()) {
                                $query->the_post();
                                $post_id = get_the_ID();

                                // Get all category IDs for this post
                                $post_categories = wp_get_post_terms($post_id, 'portfolio_category', ['fields' => 'ids']);

                                $all_posts[] = [
                                    'id' => $post_id,
                                    'categories' => $post_categories,
                                    'title' => get_the_title(),
                                    'image' => get_the_post_thumbnail_url($post_id, 'large'),
                                    'link' => get_permalink(),
                                    'excerpt' => get_the_excerpt(),
                                ];
                            }
                            wp_reset_postdata();
                        }
                    }

                    // Remove duplicates
                    $unique_posts = [];
                    $seen_ids = [];
                    foreach ($all_posts as $post) {
                        if (!in_array($post['id'], $seen_ids)) {
                            $unique_posts[] = $post;
                            $seen_ids[] = $post['id'];
                        }
                    }
                    $all_posts = $unique_posts;

                    // Display posts
                    if (!empty($all_posts)) :
                        foreach ($all_posts as $index => $post) :
                            // Get all categories for this post
                            $terms = wp_get_post_terms($post['id'], 'portfolio_category', ['fields' => 'names']);

                            // Build categories HTML
                            $categories_html = '';
                            if (!empty($terms)) {
                                $total = count($terms);
                                foreach ($terms as $key => $term) {
                                    $categories_html .= '<span class="portfolio-category-item">' . esc_html($term);
                                    if ($key < $total - 1) {
                                        $categories_html .= ' ,';
                                    }
                                    $categories_html .= '</span>';
                                }
                            } else {
                                $categories_html = '<span class="portfolio-category-item">' . esc_html__('Uncategorized', 'kirollos-magdy-portfolio-builder') . '</span>';
                            }

                            // Create data-categories attribute with all category IDs
                            $data_categories = implode(',', $post['categories']);
                    ?>
                            <a href="<?php echo esc_url($post['link']); ?>"
                                class="kmpw-portfolio-item"
                                style="background-image: url('<?php echo esc_url($post['image']); ?>');"
                                data-categories="<?php echo esc_attr($data_categories); ?>">

                                <div class="portfolio-wrapper">
                                    <div class="portfolio-content">
                                        <h6 class="portfolio-categories"><?php echo $categories_html; ?></h6>
                                        <div class="portfolio-divider"></div>
                                        <h3 class="portfolio-title"><?php echo esc_html($post['title']); ?></h3>
                                    </div>
                                </div>

                            </a>
                        <?php
                        endforeach;
                    else :
                        ?>
                        <div class="kmpw-no-portfolios">
                            <p><?php echo esc_html__('No portfolios found.', 'kirollos-magdy-portfolio-builder'); ?></p>
                        </div>
                    <?php
                    endif; ?>

                </div>
            </div>
        </div>
<?php
    }
}
