<?php

namespace KirollosMagdy\PortfolioWidgets\Widgets;

use Elementor\Controls_Manager;

if (! defined('ABSPATH')) {
    exit;
}

class Portfolio_Filter_List extends Base
{
    const SLUG = 'portfolio-filter-list';

    public function get_script_depends()
    {
        return [$this->handle()];
    }

    public function get_title()
    {
        return esc_html__('Portfolio Filter List', 'kirollos-magdy-portfolio-builder');
    }

    public function get_icon()
    {
        return 'eicon-filter pw-widget-badge';
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Content', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'   => esc_html__('Number of Posts', 'kirollos-magdy-portfolio-builder'),
                'type'    => Controls_Manager::NUMBER,
                'default' => -1,
                'min'     => -1,
                'description' => esc_html__('Use -1 to show all portfolio posts.', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'filter_service_label',
            [
                'label'       => esc_html__('Service filter label', 'kirollos-magdy-portfolio-builder'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Filter By Service', 'kirollos-magdy-portfolio-builder'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'filter_industry_label',
            [
                'label'       => esc_html__('Industry filter label', 'kirollos-magdy-portfolio-builder'),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__('Filter By Industry', 'kirollos-magdy-portfolio-builder'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label'     => esc_html__('Show Title', 'kirollos-magdy-portfolio-builder'),
                'type'      => Controls_Manager::SWITCHER,
                'label_on'  => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'default'   => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_layout_style',
            [
                'label' => esc_html__('Layout', 'kirollos-magdy-portfolio-builder'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'cards_gap',
            [
                'label'      => esc_html__('Space Between Cards', 'kirollos-magdy-portfolio-builder'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 80,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 24,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .kmpw-portfolio-filter-list' => '--kmpw-pfl-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'toolbar_gap_bottom',
            [
                'label'      => esc_html__('Space Below Filters', 'kirollos-magdy-portfolio-builder'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 80,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 28,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .kmpw-pfl-toolbar' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * @return \WP_Term[]|array|\WP_Error
     */
    private function get_terms_safe($taxonomy)
    {
        if (! taxonomy_exists($taxonomy)) {
            return [];
        }

        $terms = get_terms(array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ));

        return is_wp_error($terms) ? [] : $terms;
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $uid      = $this->get_id();

        $ppp = (int) $settings['posts_per_page'];
        if ($ppp === 0) {
            $ppp = -1;
        }

        $args = array(
            'post_type'      => 'portfolios',
            'posts_per_page' => $ppp,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish',
        );

        $query = new \WP_Query($args);

        $service_terms  = $this->get_terms_safe('portfolio_service');
        $industry_terms = $this->get_terms_safe('portfolio_industry');

        ?>
        <div class="kmpw-portfolio-filter-list">
            <div class="kmpw-pfl-toolbar">
                <div class="kmpw-pfl-field">
                    <label for="kmpw-pfl-service-<?php echo esc_attr($uid); ?>">
                        <?php echo esc_html($settings['filter_service_label']); ?>
                    </label>
                    <select
                        id="kmpw-pfl-service-<?php echo esc_attr($uid); ?>"
                        class="kmpw-pfl-select"
                        data-kmpw-pfl-filter="service"
                        aria-label="<?php echo esc_attr($settings['filter_service_label']); ?>"
                    >
                        <option value=""><?php echo esc_html__('All', 'kirollos-magdy-portfolio-builder'); ?></option>
                        <?php foreach ($service_terms as $term) : ?>
                            <option value="<?php echo esc_attr((string) $term->term_id); ?>">
                                <?php echo esc_html($term->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="kmpw-pfl-field">
                    <label for="kmpw-pfl-industry-<?php echo esc_attr($uid); ?>">
                        <?php echo esc_html($settings['filter_industry_label']); ?>
                    </label>
                    <select
                        id="kmpw-pfl-industry-<?php echo esc_attr($uid); ?>"
                        class="kmpw-pfl-select"
                        data-kmpw-pfl-filter="industry"
                        aria-label="<?php echo esc_attr($settings['filter_industry_label']); ?>"
                    >
                        <option value=""><?php echo esc_html__('All', 'kirollos-magdy-portfolio-builder'); ?></option>
                        <?php foreach ($industry_terms as $term) : ?>
                            <option value="<?php echo esc_attr((string) $term->term_id); ?>">
                                <?php echo esc_html($term->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if (! $query->have_posts()) : ?>
                <p class="kmpw-pfl-no-posts">
                    <?php echo esc_html__('No portfolios found.', 'kirollos-magdy-portfolio-builder'); ?>
                </p>
            <?php else : ?>
                <div class="kmpw-pfl-list">
                    <?php
                    while ($query->have_posts()) :
                        $query->the_post();
                        $post_id = get_the_ID();

                        $service_ids  = wp_get_post_terms($post_id, 'portfolio_service', array('fields' => 'ids'));
                        $industry_ids = wp_get_post_terms($post_id, 'portfolio_industry', array('fields' => 'ids'));
                        if (is_wp_error($service_ids)) {
                            $service_ids = [];
                        }
                        if (is_wp_error($industry_ids)) {
                            $industry_ids = [];
                        }

                        $services_attr  = esc_attr(implode(',', array_map('strval', $service_ids)));
                        $industries_attr = esc_attr(implode(',', array_map('strval', $industry_ids)));

                        $permalink = get_permalink();
                        $raw_excerpt = wp_strip_all_tags((string) get_the_excerpt());
                        $excerpt_desktop = wp_trim_words($raw_excerpt, 30, '...');
                        $excerpt_mobile  = wp_trim_words($raw_excerpt, 15, '...');
                        ?>
                        <a
                            href="<?php echo esc_url($permalink); ?>"
                            class="tc-card-item-link"
                            data-kmpw-pfl-card
                            data-kmpw-services="<?php echo $services_attr; ?>"
                            data-kmpw-industries="<?php echo $industries_attr; ?>"
                        >
                            <div class="tc-card-item">
                                <div class="card-image">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('full'); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="tc-card-overlay" aria-hidden="true"></div>
                                <div class="portfolio-wrapper">
                                    <div class="portfolio-content">
                                        <?php if ($settings['show_title'] === 'yes') : ?>
                                            <h3 class="portfolio-title"><?php the_title(); ?></h3>
                                        <?php endif; ?>

                                        <?php if ($excerpt_desktop !== '') : ?>
                                            <?php if ($excerpt_mobile === $excerpt_desktop) : ?>
                                                <p class="portfolio-excerpt"><?php echo esc_html($excerpt_desktop); ?></p>
                                            <?php else : ?>
                                                <p class="portfolio-excerpt portfolio-excerpt--desktop"><?php echo esc_html($excerpt_desktop); ?></p>
                                                <p class="portfolio-excerpt portfolio-excerpt--mobile"><?php echo esc_html($excerpt_mobile); ?></p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php
                    endwhile;
                    ?>
                </div>
                <p class="kmpw-pfl-empty" hidden>
                    <?php echo esc_html__('No projects match these filters.', 'kirollos-magdy-portfolio-builder'); ?>
                </p>
                <?php
                wp_reset_postdata();
            endif;
            ?>
        </div>
        <?php
    }
}
