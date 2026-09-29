<?php

namespace KirollosMagdy\PortfolioWidgets\Widgets;

if (! defined('ABSPATH')) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;

class Works_Slider extends Base
{
    const SLUG = 'works-slider';

    public function get_script_depends()
    {
        return ['swiper', $this->handle()];
    }

    public function get_title()
    {
        return esc_html__('Works Slider', 'kirollos-magdy-portfolio-builder');
    }

    public function get_icon()
    {
        return 'eicon-post-slider pw-widget-badge';
    }

    protected function register_controls()
    {
        // Query Section
        $this->start_controls_section(
            'query_section',
            [
                'label' => __('Query Settings', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => esc_html__('Posts Per Page', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1,
                'max' => 100,
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

        // Slider Settings
        $this->start_controls_section(
            'slider_settings',
            [
                'label' => __('Slider Settings', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'speed',
            [
                'label' => esc_html__('Speed', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 500,
                'min' => 100,
                'max' => 10000,
                'step' => 100,
            ]
        );

        $this->add_control(
            'effect',
            [
                'label' => esc_html__('Effect', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'default' => 'slide',
                'options' => [
                    'slide' => esc_html__('Slide', 'kirollos-magdy-portfolio-builder'),
                    'fade' => esc_html__('Fade', 'kirollos-magdy-portfolio-builder'),
                    'cube' => esc_html__('Cube', 'kirollos-magdy-portfolio-builder'),
                    'coverflow' => esc_html__('Coverflow', 'kirollos-magdy-portfolio-builder'),
                    'flip' => esc_html__('Flip', 'kirollos-magdy-portfolio-builder'),
                ],
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => esc_html__('Autoplay', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('On', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('Off', 'kirollos-magdy-portfolio-builder'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->add_control(
            'autoplay_delay',
            [
                'label' => esc_html__('Autoplay Delay', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 5000,
                'condition' => [
                    'autoplay' => 'true',
                ]
            ]
        );

        $this->add_control(
            'arrows',
            [
                'label' => esc_html__('Show Arrows', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->add_control(
            'pagination',
            [
                'label' => esc_html__('Show Pagination', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->end_controls_section();

        // Content Settings
        $this->start_controls_section(
            'content_settings',
            [
                'label' => __('Content Settings', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => esc_html__('Show Title', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_excerpt',
            [
                'label' => esc_html__('Show Excerpt', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label' => esc_html__('Excerpt Length', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 20,
                'condition' => [
                    'show_excerpt' => 'yes',
                ]
            ]
        );

        $this->add_control(
            'read_more_icon',
            [
                'label' => esc_html__('Read More Icon', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-arrow-right',
                    'library' => 'solid',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Slide
        $this->start_controls_section(
            'slide_style',
            [
                'label' => esc_html__('Slide Style', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'slide_height',
            [
                'label' => esc_html__('Slide Height', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh', '%'],
                'range' => [
                    'px' => [
                        'min' => 300,
                        'max' => 1000,
                    ],
                    'vh' => [
                        'min' => 50,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'vh',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .swiper-slide' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'overlay_color',
            [
                'label' => esc_html__('Overlay Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => 'rgba(0,0,0,0.5)',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .slide-overlay' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Content
        $this->start_controls_section(
            'content_style',
            [
                'label' => esc_html__('Content Style', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_alignment',
            [
                'label' => esc_html__('Alignment', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .slide-content' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => esc_html__('Padding', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => 50,
                    'right' => 50,
                    'bottom' => 50,
                    'left' => 50,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .slide-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Title
        $this->add_control(
            'title_heading',
            [
                'label' => esc_html__('Title', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .kmpw-works-slider .slide-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .slide-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_spacing',
            [
                'label' => esc_html__('Spacing', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .slide-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Description
        $this->add_control(
            'description_heading',
            [
                'label' => esc_html__('Description', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .kmpw-works-slider .slide-description',
            ]
        );

        $this->add_control(
            'description_color',
            [
                'label' => esc_html__('Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .slide-description' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'description_spacing',
            [
                'label' => esc_html__('Spacing', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .slide-description' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Read More Icon
        $this->start_controls_section(
            'icon_style',
            [
                'label' => esc_html__('Read More Icon Style', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => esc_html__('Size', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 40,
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .read-more-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .kmpw-works-slider .read-more-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('icon_tabs');

        $this->start_controls_tab(
            'icon_normal',
            [
                'label' => esc_html__('Normal', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => esc_html__('Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .read-more-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .kmpw-works-slider .read-more-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'icon_background',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .kmpw-works-slider .read-more-icon',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'icon_hover',
            [
                'label' => esc_html__('Hover', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'icon_color_hover',
            [
                'label' => esc_html__('Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .read-more-icon:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .kmpw-works-slider .read-more-icon:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'icon_background_hover',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .kmpw-works-slider .read-more-icon:hover',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'icon_padding',
            [
                'label' => esc_html__('Padding', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .read-more-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'icon_border',
                'selector' => '{{WRAPPER}} .kmpw-works-slider .read-more-icon',
            ]
        );

        $this->add_responsive_control(
            'icon_border_radius',
            [
                'label' => esc_html__('Border Radius', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-works-slider .read-more-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        // Query Posts
        $args = [
            'post_type' => 'portfolios',
            'posts_per_page' => $settings['posts_per_page'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'post_status' => 'publish',
        ];

        $query = new \WP_Query($args);

        if (!$query->have_posts()) {
            echo '<p>' . esc_html__('No posts found.', 'kirollos-magdy-portfolio-builder') . '</p>';
            return;
        }

        $slider_settings = [
            'speed' => $settings['speed'],
            'effect' => $settings['effect'],
            'autoplay' => $settings['autoplay'] === 'true' ? [
                'delay' => $settings['autoplay_delay'],
            ] : false,
        ];

        $slider_id = uniqid('kmpw-works-slider-');
?>

        <div id="<?php echo esc_attr($slider_id); ?>" class="kmpw-works-slider" data-slider-settings='<?php echo esc_attr(json_encode($slider_settings)); ?>'>
            <div class="swiper-container">
                <div class="swiper-wrapper">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <div class="swiper-slide" style="background-image: url('<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>');">
                            <div class="slide-overlay"></div>
                            <div class="slide-content">
                                <h2 class="slide-title"><?php the_title(); ?></h2>

                                <p class="slide-description">
                                    <?php echo wp_trim_words(get_the_excerpt(), $settings['excerpt_length'], '...'); ?>
                                </p>

                                <a href="<?php the_permalink(); ?>" class="read-more-link">
                                    <span class="read-more-text"><?php echo esc_html__('Read More', 'kirollos-magdy-portfolio-builder'); ?></span>
                                    <span class="read-more-icon">
                                        <?php Icons_Manager::render_icon($settings['read_more_icon'], ['aria-hidden' => 'true']); ?>
                                    </span>
                                </a>
                            </div>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>
            </div>
        </div>

<?php
    }
}
