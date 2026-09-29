<?php

namespace KirollosMagdy\PortfolioWidgets\Widgets;

if (! defined('ABSPATH')) {
    exit;
}

use Elementor\Controls_Manager;

class Works_Tabs_Mini extends Base
{
    const SLUG = 'works-tabs-mini';

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
        return __('Works Tabs Mini', 'kirollos-magdy-portfolio-builder');
    }

    public function get_icon()
    {
        return 'eicon-gallery-grid pw-widget-badge';
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

        // Portfolio Service Selection
        $services = get_terms([
            'taxonomy' => 'portfolio_service',
            'hide_empty' => true,
        ]);

        $service_options = [
            'all' => __('All Services', 'kirollos-magdy-portfolio-builder')
        ];

        if (!is_wp_error($services) && !empty($services)) {
            foreach ($services as $service) {
                $service_options[$service->term_id] = $service->name;
            }
        }

        $this->add_control(
            'selected_services',
            [
                'label' => __('Select Portfolio Services', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT2,
                'options' => $service_options,
                'multiple' => true,
                'label_block' => true,
                'default' => ['all'],
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Posts Per Page', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 100,
            ]
        );

        $this->add_control(
            'slides_per_view',
            [
                'label' => __('Slides Per View (desktop)', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 3,
                'min' => 1,
                'max' => 6,
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
                'label' => __('Speed', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 600,
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => __('Autoplay', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'default' => '',
            ]
        );

        $this->add_control(
            'autoplay_delay',
            [
                'label' => __('Autoplay Delay (ms)', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 4000,
                'condition' => [
                    'autoplay' => 'true',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $selected_services = (array) ($settings['selected_services'] ?? ['all']);
        $selected_services = array_values(array_filter(array_map('sanitize_text_field', $selected_services)));

        $args = [
            'post_type' => 'portfolios',
            'posts_per_page' => $settings['posts_per_page'],
            'post_status' => 'publish',
        ];

        $is_all_selected = in_array('all', $selected_services, true);
        if (!$is_all_selected) {
            $service_term_ids = array_values(array_filter(array_map('absint', $selected_services)));

            if (!empty($service_term_ids)) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => 'portfolio_service',
                        'field' => 'term_id',
                        'terms' => $service_term_ids,
                    ],
                ];
            }
        }

        $query = new \WP_Query($args);

        // Slider settings used by JS
        $base_slider_settings = [
            'speed' => (int) $settings['speed'],
            'autoplay' => $settings['autoplay'] === 'true' ? [
                'delay' => (int) $settings['autoplay_delay']
            ] : false,
            'slidesPerView' => (int) $settings['slides_per_view']
        ];
        $base_slider_settings_json = esc_attr(wp_json_encode($base_slider_settings));
        $slider_unique = uniqid('kmpw-portfolio-slider-');

?>

        <div class="kmpw-portfolio-grid-slider-wrapper">
            <!-- Single Slider -->
            <div class="kmpw-portfolio-slider visible" data-slider-id="<?php echo esc_attr($slider_unique); ?>" data-slider-settings="<?php echo $base_slider_settings_json; ?>">
                <div class="swiper-container">
                    <div class="swiper-wrapper">
                        <?php if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post(); ?>
                                <div class="swiper-slide">
                                    <a href="<?php the_permalink(); ?>" class="portfolio-item">
                                        <div class="portfolio-image">
                                            <?php the_post_thumbnail('large'); ?>
                                        </div>

                                        <h3 class="portfolio-title"><?php the_title(); ?></h3>

                                  
                                    </a>
                                </div>
                            <?php endwhile;
                            wp_reset_postdata();
                        else: ?>
                            <div class="swiper-slide">
                                <div class="portfolio-item no-items">
                                    <p><?php esc_html_e('No items', 'kirollos-magdy-portfolio-builder'); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="swiper-pagination"></div>
            </div>
        </div>

<?php
    }
}
