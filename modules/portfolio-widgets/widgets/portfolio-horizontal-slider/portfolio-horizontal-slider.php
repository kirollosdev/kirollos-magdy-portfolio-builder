<?php

namespace KirollosMagdy\PortfolioWidgets\Widgets;

if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class Portfolio_Horizontal_Slider extends Base
{
    const SLUG = 'portfolio-horizontal-slider';

    public function get_script_depends()
    {
        return ['swiper', $this->handle()];
    }

    public function get_style_depends()
    {
        return ['swiper'];
    }


    public function get_title()
    {
        return esc_html__('Portfolio Horizontal Slider', 'kirollos-magdy-portfolio-builder');
    }

    public function get_icon()
    {
        return 'eicon-gallery-justified pw-widget-badge';
    }

    public function get_keywords()
    {
        return ['portfolio', 'grid', 'horizontal', 'gallery', 'swiper'];
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
            'posts_per_page',
            [
                'label' => esc_html__('Number of Items', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 9,
                'min' => 1,
                'max' => 50,
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
            'show_category',
            [
                'label' => esc_html__('Show Category', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Swiper Settings Section
        $this->start_controls_section(
            'swiper_section',
            [
                'label' => esc_html__('Swiper Settings', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'coverflow_rotate',
            [
                'label' => esc_html__('Rotation Angle', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'size' => 50,
                ],
            ]
        );

        $this->add_control(
            'coverflow_depth',
            [
                'label' => esc_html__('Depth', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'size' => 100,
                ],
            ]
        );

        $this->add_control(
            'coverflow_modifier',
            [
                'label' => esc_html__('Modifier', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 5,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 1,
                ],
            ]
        );

        $this->add_control(
            'show_pagination',
            [
                'label' => esc_html__('Show Pagination', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_navigation',
            [
                'label' => esc_html__('Show Navigation', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => esc_html__('Autoplay', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'default' => 'no',
            ]
        );

        $this->add_control(
            'autoplay_delay',
            [
                'label' => esc_html__('Autoplay Delay (ms)', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 3000,
                'condition' => [
                    'autoplay' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Layout Section
        $this->start_controls_section(
            'layout_section',
            [
                'label' => esc_html__('Layout Settings', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'item_width',
            [
                'label' => esc_html__('Item Width', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 800,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 300,
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-slide' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'item_height',
            [
                'label' => esc_html__('Item Height', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 800,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 300,
                ],
                'selectors' => [
                    '{{WRAPPER}} .swiper-slide' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Items
        $this->start_controls_section(
            'item_style_section',
            [
                'label' => esc_html__('Portfolio Items', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'item_border_radius',
            [
                'label' => esc_html__('Border Radius', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-portfolio-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .kmpw-portfolio-item::before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'overlay_color',
            [
                'label' => esc_html__('Overlay Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => 'rgba(0, 0, 0, 0.3)',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-portfolio-item::before' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'overlay_hover_color',
            [
                'label' => esc_html__('Overlay Hover Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => 'rgba(0, 0, 0, 0.6)',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-portfolio-item:hover::before' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Title
        $this->start_controls_section(
            'title_style_section',
            [
                'label' => esc_html__('Title', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .portfolio-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Title Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFF',
                'selectors' => [
                    '{{WRAPPER}} .portfolio-title' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Category
        $this->start_controls_section(
            'category_style_section',
            [
                'label' => esc_html__('Category', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'category_typography',
                'selector' => '{{WRAPPER}} .portfolio-categories',
            ]
        );

        $this->add_control(
            'category_color',
            [
                'label' => esc_html__('Category Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFF',
                'selectors' => [
                    '{{WRAPPER}} .portfolio-categories' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Navigation
        $this->start_controls_section(
            'navigation_style_section',
            [
                'label' => esc_html__('Navigation', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'nav_color',
            [
                'label' => esc_html__('Navigation Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFF',
                'selectors' => [
                    '{{WRAPPER}} .swiper-button-next, {{WRAPPER}} .swiper-button-prev' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'nav_bg_color',
            [
                'label' => esc_html__('Navigation Background', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => 'rgba(0, 0, 0, 0.5)',
                'selectors' => [
                    '{{WRAPPER}} .swiper-button-next, {{WRAPPER}} .swiper-button-prev' => 'background: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Pagination
        $this->start_controls_section(
            'pagination_style_section',
            [
                'label' => esc_html__('Pagination', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_pagination' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'pagination_color',
            [
                'label' => esc_html__('Pagination Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFF',
                'selectors' => [
                    '{{WRAPPER}} .swiper-pagination-bullet' => 'background: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $widget_id = $this->get_id();

        $query = new \WP_Query([
            'post_type' => 'portfolios',
            'posts_per_page' => $settings['posts_per_page'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
        ]);

        if (!$query->have_posts()) {
            echo '<div class="kmpw-no-portfolios"><p>' . esc_html__('No portfolios found.', 'kirollos-magdy-portfolio-builder') . '</p></div>';
            return;
        }

        // تمرير الإعدادات إلى JavaScript
        $swiper_settings = [
            'rotate' => $settings['coverflow_rotate']['size'],
            'depth' => $settings['coverflow_depth']['size'],
            'modifier' => $settings['coverflow_modifier']['size'],
            'show_pagination' => $settings['show_pagination'] === 'yes',
            'show_navigation' => $settings['show_navigation'] === 'yes',
            'autoplay' => $settings['autoplay'] === 'yes',
            'autoplay_delay' => $settings['autoplay_delay'],
        ];
?>
        <div class="kmpw-portfolio-swiper-wrapper" data-swiper-settings='<?php echo esc_attr(json_encode($swiper_settings)); ?>'>
            <div class="swiper kmpw-portfolio-swiper-<?php echo esc_attr($widget_id); ?>">
                <div class="swiper-wrapper">
                    <?php while ($query->have_posts()) :
                        $query->the_post();
                        $image = get_the_post_thumbnail_url(get_the_ID(), 'large');
                        $terms = wp_get_post_terms(get_the_ID(), 'portfolio_category', ['fields' => 'names']);
                        $categories_html = '';
                        if (!empty($terms) && $settings['show_category'] === 'yes') {
                            $categories_html = implode(', ', $terms);
                        }
                    ?>
                        <div class="swiper-slide">
                            <a href="<?php the_permalink(); ?>"
                                class="kmpw-portfolio-item"
                                style="background-image: url('<?php echo esc_url($image); ?>');">
                                <div class="portfolio-overlay-content">
                                    <?php if ($settings['show_category'] === 'yes' && $categories_html) : ?>
                                        <div class="portfolio-categories"><?php echo esc_html($categories_html); ?></div>
                                    <?php endif; ?>

                                    <?php if ($settings['show_title'] === 'yes') : ?>
                                        <h3 class="portfolio-title"><?php the_title(); ?></h3>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>

                <?php if ($settings['show_navigation'] === 'yes') : ?>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
}
