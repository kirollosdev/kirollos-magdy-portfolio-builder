<?php

namespace KirollosMagdy\PortfolioWidgets\Widgets;


use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Css_Filter;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\repeater;
use Elementor\Frontend;
use Elementor\Icons_Manager;
use Elementor\Core\Schemes;
use Elementor\Group_Control_Image_Size;
use Elementor\Scheme_Base;
use Elementor\Group_Control_Text_Shadow;


if (!defined('ABSPATH')) exit; // Exit if accessed directly



/**
 * @since 1.1.0
 */
class Dynamic_Post_Meta extends Base
{
    const SLUG = 'dynamic-post-meta';

    public function get_script_depends()
    {
        return ['swiper', $this->handle()];
    }

    public function get_style_depends()
    {
        return ['swiper'];
    }


    /**
     * Get widget name.
     *
     * Retrieve icon list widget name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */
    /**
     * Get widget title.
     *
     * Retrieve icon list widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title()
    {
        return esc_html__('Dynamic Post Meta', 'kirollos-magdy-portfolio-builder');
    }

    /**
     * Get widget icon.
     *
     * Retrieve icon list widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon()
    {
        return 'eicon-document-file pw-widget-badge';
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the widget belongs to.
     *
     * @since 2.1.0
     * @access public
     *
     * @return array Widget keywords.
     */
    public function get_keywords()
    {
        return ['posts', 'blogs', 'portfolio'];
    }

    /**
     * Retrieve the list of categories the widget belongs to.
     *
     * Used to determine where to display the widget in the editor.
     *
     * Note that currently Elementor supports only one category.
     * When multiple categories passed, Elementor uses the first one.
     *
     * @since 1.0.0
     *
     * @access public
     *
     * @return array Widget categories.
     */
    /**
     * Retrieve the list of scripts the widget depended on.
     *
     * Used to set styles dependencies required to run the widget.
     *
     * @since 1.0.0
     *
     * @access public
     *
     * @return array Widget styles dependencies.
     */
    /**
     * Retrieve the list of scripts the widget depended on.
     *
     * Used to set scripts dependencies required to run the widget.
     *
     * @since 1.0.0
     *
     * @access public
     *
     * @return array Widget scripts dependencies.
     */
    private function is_editor_mode()
    {
        return \Elementor\Plugin::$instance->editor->is_edit_mode();
    }
    /**
     * Register icon list widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 3.1.0
     * @access protected
     */

    protected function register_controls()
    {

        $this->start_controls_section(
            'kmpw_section_post__filters',
            [
                'label' => __('Query', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'data_type',
            [
                'label' => __('Data Type', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'title' => 'Title',
                    'date' => 'Date',
                    'time' => 'Time',
                    'excerpt' => 'Excerpt',
                    'author' => 'Author',
                    'author-bio' => 'Author Bio',
                    'avatar' => 'Avatar',
                    'role' => 'Role',
                    'categories' => 'Categories',
                    'tags' => 'Tags',
                    'comments' => 'Comments',
                    'count' => 'Post Count',
                    'port-sec' => 'image-sec (portfolio)',
                    'woo-price' => 'Price (Woocommerce)',
                    'woo-reviews' => 'Reviews (Woocommerce)',
                ],
                'default' => 'title',

            ]
        );
        $this->add_control(
            'count_prefix_zero',
            [
                'label' => esc_html__('Add Prefix Zero', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'data_type' => 'count',
                ],
                'description' => esc_html__('Add leading zero for numbers 1-9 (e.g., 01, 02, 03)', 'kirollos-magdy-portfolio-builder'),
            ]
        );
        $this->add_control(
            'image_size',
            [
                'label' => esc_html__('Image Size', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'default' => 'thumbnail',
                'options' => [
                    'thumbnail' => esc_html__('Thumbnail (150x150)', 'kirollos-magdy-portfolio-builder'),
                    'medium' => esc_html__('Medium', 'kirollos-magdy-portfolio-builder'),
                    'medium_large' => esc_html__('Medium Large', 'kirollos-magdy-portfolio-builder'),
                    'large' => esc_html__('Large', 'kirollos-magdy-portfolio-builder'),
                    'full' => esc_html__('Full/Original Size', 'kirollos-magdy-portfolio-builder'),
                ],
                'condition' => [
                    'data_type' => 'port-sec',
                ],
            ]
        );
        $this->add_control(
            'title_limit',
            [
                'label' => __('Title Max Lines', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'condition' => [
                    'data_type' => 'title',
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text' => 'display: -webkit-box;-webkit-line-clamp: {{VALUE}};-webkit-box-orient: vertical;overflow: hidden;',
                ],
            ]
        );

        $this->add_control(
            'woo_reviews_text',
            [
                'label' => esc_html__('Review Text', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Reviews', 'kirollos-magdy-portfolio-builder'),
                'condition' => [
                    'data_type' => ['woo-reviews'],
                ],
            ]
        );
        $this->add_control(
            'woo_no_reviews_text',
            [
                'label' => esc_html__('No Reviews Text', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('No Reviews', 'kirollos-magdy-portfolio-builder'),
                'condition' => [
                    'data_type' => ['woo-reviews'],
                ],
            ]
        );

        $this->add_control(
            'meta_separator',
            [
                'label' => esc_html__('Categories/tags Separator', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::TEXT,
                'default' => ' / ',
                'condition' => [
                    'data_type' => ['categories', 'tags'],
                ],
            ]
        );

        $this->add_control(
            'separator_wrap_in_span',
            [
                'label' => esc_html__('Wrap Separator in &lt;span&gt;', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'condition' => [
                    'data_type' => ['categories', 'tags'],
                ],
            ]
        );

        $this->add_control(
            'date_format',
            [
                'label' => esc_html__('Date Format', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => 'Default',
                    '0' => _x('March 6, 2018 (F j, Y)', 'Date Format', 'kirollos-magdy-portfolio-builder'),
                    '1' => '2018-03-06 (Y-m-d)',
                    '2' => '03/06/2018 (m/d/Y)',
                    '3' => '06/03/2018 (d/m/Y)',
                    'custom' => esc_html__('Custom', 'kirollos-magdy-portfolio-builder'),
                ],
                'condition' => [
                    'data_type' => 'date',
                ],
            ]
        );

        $this->add_control(
            'custom_date_format',
            [
                'label' => esc_html__('Custom Date Format', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::TEXT,
                'default' => 'F j, Y',
                'condition' => [
                    'data_type' => 'date',
                    'date_format' => 'custom',
                ],
                'description' => sprintf(
                    /* translators: %s: Allowed data letters (see: http://php.net/manual/en/function.date.php). */
                    __('Use the letters: %s', 'kirollos-magdy-portfolio-builder'),
                    'l D d j S F m M n Y y'
                ),
            ]
        );

        $this->add_control(
            'use_days_ago',
            [
                'label' => esc_html__('Use Days Ago', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'data_type' => 'date',
                ],
            ]
        );

        $this->add_control(
            'custom_days_ago_text',
            [
                'label' => esc_html__('Days Ago Text', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Days ago', 'kirollos-magdy-portfolio-builder'),
                'placeholder' => esc_html__('Enter custom text', 'kirollos-magdy-portfolio-builder'),
                'condition' => [
                    'data_type' => 'date',
                    'use_days_ago' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'days_ago_limit',
            [
                'label' => esc_html__('Days Ago Limit', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 7,
                'min' => 1,
                'max' => 365,
                'step' => 1,
                'condition' => [
                    'data_type' => 'date',
                    'use_days_ago' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'time_format',
            [
                'label' => esc_html__('Time Format', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => 'Default',
                    '0' => '3:31 pm (g:i a)',
                    '1' => '3:31 PM (g:i A)',
                    '2' => '15:31 (H:i)',
                    'custom' => esc_html__('Custom', 'kirollos-magdy-portfolio-builder'),
                ],
                'condition' => [
                    'data_type' => 'time',
                ],
            ]
        );

        $this->add_control(
            'custom_time_format',
            [
                'label' => esc_html__('Custom Time Format', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::TEXT,
                'default' => 'g:i a',
                'placeholder' => 'g:i a',
                'condition' => [
                    'data_type' => 'time',
                    'time_format' => 'custom',
                ],
                'description' => sprintf(
                    /* translators: %s: Allowed time letters (see: http://php.net/manual/en/function.time.php). */
                    __('Use the letters: %s', 'kirollos-magdy-portfolio-builder'),
                    'g G H i a A'
                ),
            ]
        );

        $this->add_control(
            'use_hours_ago',
            [
                'label' => esc_html__('Use Hours Ago', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'data_type' => 'time',
                ],
            ]
        );
        $this->add_control(
            'custom_hours_ago_text',
            [
                'label' => esc_html__('Hours Ago Text', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('hours ago', 'kirollos-magdy-portfolio-builder'),
                'placeholder' => esc_html__('Enter custom text', 'kirollos-magdy-portfolio-builder'),
                'condition' => [
                    'use_hours_ago' => 'yes',
                    'data_type' => 'time',
                ],
            ]
        );

        $this->add_control(
            'hours_ago_limit',
            [
                'label' => esc_html__('Hours Ago Limit', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => 24,
                'min' => 1,
                'max' => 96,
                'step' => 1,
                'condition' => [
                    'use_hours_ago' => 'yes',
                    'data_type' => 'time',
                ],
            ]
        );

        $this->add_control(
            'excerpt',
            [
                'label' => __('Blog Excerpt Length', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::NUMBER,
                'default' => '150',
                'min' => 10,
                'condition' => [
                    'data_type' => 'excerpt',
                ],
            ]
        );

        $this->add_control(
            'excerpt_after',
            [
                'label' => __('After Excerpt text/symbol', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'data_type' => 'excerpt',
                ],
                'default' => '...',
            ]
        );

        $this->add_control(
            'comments_custom_strings',
            [
                'label' => esc_html__('Custom Format', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'default' => false,
                'condition' => [
                    'data_type' => 'comments',
                ],
            ]
        );

        $this->add_control(
            'string_no_comments',
            [
                'label' => esc_html__('No Comments', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__('No Comments', 'kirollos-magdy-portfolio-builder'),
                'condition' => [
                    'comments_custom_strings' => 'yes',
                    'data_type' => 'comments',
                ],
            ]
        );

        $this->add_control(
            'string_one_comment',
            [
                'label' => esc_html__('One Comment', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__('One Comment', 'kirollos-magdy-portfolio-builder'),
                'condition' => [
                    'comments_custom_strings' => 'yes',
                    'data_type' => 'comments',
                ],
            ]
        );

        $this->add_control(
            'string_comments',
            [
                'label' => esc_html__('Comments', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__('%s Comments', 'kirollos-magdy-portfolio-builder'),
                'condition' => [
                    'comments_custom_strings' => 'yes',
                    'data_type' => 'comments',
                ],
            ]
        );

        $this->add_control(
            'header_size',
            [
                'label' => esc_html__('HTML Tag', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ],
                'default' => 'h2',
            ]
        );

        $this->add_responsive_control(
            'align',
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
                    'justify' => [
                        'title' => esc_html__('Justified', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'link',
            [
                'label' => esc_html__('Link', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'floating_title',
            [
                'label' => esc_html__('Enable Floating Title', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'data_type' => 'title',
                ],
            ]
        );

        $this->add_control(
            'show_editor_placeholder',
            [
                'label' => esc_html__('Show Placeholder in Editor', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'kirollos-magdy-portfolio-builder'),
                'label_off' => esc_html__('No', 'kirollos-magdy-portfolio-builder'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => ['data_type' => ['categories', 'tags']],
                'description' => esc_html__('Show sample content in Elementor editor when no data is available', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_title_style',
            [
                'label' => esc_html__('Text', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_wrap',
            [
                'label' => esc_html__('Text Wrap', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => esc_html__('Default', 'kirollos-magdy-portfolio-builder'),
                    'wrap' => esc_html__('Wrap', 'kirollos-magdy-portfolio-builder'),
                    'nowrap' => esc_html__('No Wrap', 'kirollos-magdy-portfolio-builder'),
                    'balance' => esc_html__('Balance', 'kirollos-magdy-portfolio-builder'),
                    'pretty' => esc_html__('Pretty', 'kirollos-magdy-portfolio-builder'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text' => 'text-wrap: {{VALUE}};'
                ]
            ]
        );

        $this->add_responsive_control(
            'text_min_height',
            [
                'label' => esc_html__( 'Heading Min Height', 'kirollos-magdy-portfolio-builder' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'unit' => 'px',
                ],
                'size_units' => [ 'px', 'vh', '%', 'vw', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 500,
                    ],
                    'vh' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text' => 'min-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('heading_color');
        $this->start_controls_tab(
            'text_normal',
            [
                'label' => esc_html__('Normal', 'kirollos-magdy-portfolio-builder'),
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-text',
            ]
        );
        $this->add_control(
            'title_color',
            [
                'label' => esc_html__('Text Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text a' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'text_stroke',
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-text',
            ]
        );

        $this->add_control(
            'heading_opacity',
            [
                'label' => esc_html__('Opacity', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'heading_padding',
            [
                'label' => esc_html__('Padding', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'heading_border_radius',
            [
                'label' => esc_html__('Border Radius', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'heading_background',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-text',
                'exclude' => ['image']
            ]
        );

        $this->add_control(
            'heading_blur_method',
            [
                'label' => esc_html__('Heading Blur Method', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'backdrop-filter' => 'backdrop-filter',
                    'filter' => 'filter',
                ],
                'default' => 'backdrop-filter',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text' => '{{VALUE}}: blur({{heading_blur.SIZE}}px);',
                ],
            ]
        );

        $this->add_control(
            'heading_blur',
            [
                'label' => esc_html__('Blur', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 250,
                    ],
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'text_border',
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-text',
            ]
        );
        $this->add_control(
            'text_dark_mode_heading',
            [
                'label' => esc_html__('Dark Mode', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'title_color_dark_mode',
            [
                'label' => esc_html__('Title Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta-text' => 'color: {{VALUE}};',
                    '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta-text' => 'color: {{VALUE}};',
                    '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta-text a' => 'color: {{VALUE}};',
                    '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta-text a' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
            'text_hover',
            [
                'label' => esc_html__('Hover', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'title_style_overlay_selector',
            [
                'label' => esc_html__('Choose Selector', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'image',
                'options' => [
                    'image' => esc_html__('Image', 'kirollos-magdy-portfolio-builder'),
                    'container'  => esc_html__('Parent Container', 'kirollos-magdy-portfolio-builder'),
                    'parent-container'  => esc_html__('Parent of Parent Container', 'kirollos-magdy-portfolio-builder'),
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'hover_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-text:hover, .e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text, .e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-parent-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text',
            ]
        );
        $this->add_control(
            'hover_title_color',
            [
                'label' => esc_html__('Hover Text Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text a:hover' => 'color: {{VALUE}};',
                    '.e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text' => 'color: {{VALUE}};',
                    '.e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text a' => 'color: {{VALUE}};',
                    '.e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-parent-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text' => 'color: {{VALUE}};',
                    '.e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-parent-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text a' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'text_stroke_hover',
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-text:hover, .e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text, .e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-parent-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text',
            ]
        );
        $this->add_control(
            'heading_opacity_hover',
            [
                'label' => esc_html__('Opacity', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text:hover' => 'opacity: {{SIZE}};',
                    '.e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text' => 'opacity: {{SIZE}};',
                    '.e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-parent-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text' => 'opacity: {{SIZE}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'text_border_hover',
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-text:hover,.e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text,.e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-parent-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text',
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'text_background_hover',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-text:hover,.e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text,.e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-parent-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text',
                'exclude' => ['image']
            ]
        );
        $this->add_control(
            'text_dark_mode_heading_hover',
            [
                'label' => esc_html__('Dark Mode', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'title_color_dark_mode_hover',
            [
                'label' => esc_html__('Title Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta-text:hover' => 'color: {{VALUE}};',
                    '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta-text:hover' => 'color: {{VALUE}};',
                    '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode .e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text' => 'color: {{VALUE}};',
                    '} body.kmpw-dark-mode .e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text' => 'color: {{VALUE}};',
                    '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode .e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-parent-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text' => 'color: {{VALUE}};',
                    '} body.kmpw-dark-mode .e-con:hover .elementor-element-{{ID}}>.elementor-widget-container>.kmpw-dynamic-post-meta.selector-type-parent-container.kmpw-dynamic-post-meta-container-active .kmpw-dynamic-post-meta-text' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'separator_border2',
            [
                'type' => Controls_Manager::DIVIDER,
                'style' => 'thick',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-text',
            ]
        );

        $this->add_control(
            'blend_mode',
            [
                'label' => esc_html__('Blend Mode', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__('Normal', 'kirollos-magdy-portfolio-builder'),
                    'multiply' => esc_html__('Multiply', 'kirollos-magdy-portfolio-builder'),
                    'screen' => esc_html__('Screen', 'kirollos-magdy-portfolio-builder'),
                    'overlay' => esc_html__('Overlay', 'kirollos-magdy-portfolio-builder'),
                    'darken' => esc_html__('Darken', 'kirollos-magdy-portfolio-builder'),
                    'lighten' => esc_html__('Lighten', 'kirollos-magdy-portfolio-builder'),
                    'color-dodge' => esc_html__('Color Dodge', 'kirollos-magdy-portfolio-builder'),
                    'saturation' => esc_html__('Saturation', 'kirollos-magdy-portfolio-builder'),
                    'color' => esc_html__('Color', 'kirollos-magdy-portfolio-builder'),
                    'difference' => esc_html__('Difference', 'kirollos-magdy-portfolio-builder'),
                    'exclusion' => esc_html__('Exclusion', 'kirollos-magdy-portfolio-builder'),
                    'hue' => esc_html__('Hue', 'kirollos-magdy-portfolio-builder'),
                    'luminosity' => esc_html__('Luminosity', 'kirollos-magdy-portfolio-builder'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-text' => 'mix-blend-mode: {{VALUE}}',
                ],
                'separator' => 'none',
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_image',
            [
                'label' => esc_html__('Image', 'kirollos-magdy-portfolio-builder'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'data_type' => ['avatar', 'port-sec'],
                ]
            ]
        );

        $this->add_responsive_control(
            'width',
            [
                'label' => esc_html__('Width', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => '%',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'space',
            [
                'label' => esc_html__('Max Width', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'unit' => '%',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-img' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'height',
            [
                'label' => esc_html__('Height', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vh', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 500,
                    ],
                    'vh' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'object-fit',
            [
                'label' => esc_html__('Object Fit', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'condition' => [
                    'height[size]!' => '',
                ],
                'options' => [
                    '' => esc_html__('Default', 'kirollos-magdy-portfolio-builder'),
                    'fill' => esc_html__('Fill', 'kirollos-magdy-portfolio-builder'),
                    'cover' => esc_html__('Cover', 'kirollos-magdy-portfolio-builder'),
                    'contain' => esc_html__('Contain', 'kirollos-magdy-portfolio-builder'),
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-img' => 'object-fit: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'object-position',
            [
                'label' => esc_html__('Object Position', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'center center' => esc_html__('Center Center', 'kirollos-magdy-portfolio-builder'),
                    'center left' => esc_html__('Center Left', 'kirollos-magdy-portfolio-builder'),
                    'center right' => esc_html__('Center Right', 'kirollos-magdy-portfolio-builder'),
                    'top center' => esc_html__('Top Center', 'kirollos-magdy-portfolio-builder'),
                    'top left' => esc_html__('Top Left', 'kirollos-magdy-portfolio-builder'),
                    'top right' => esc_html__('Top Right', 'kirollos-magdy-portfolio-builder'),
                    'bottom center' => esc_html__('Bottom Center', 'kirollos-magdy-portfolio-builder'),
                    'bottom left' => esc_html__('Bottom Left', 'kirollos-magdy-portfolio-builder'),
                    'bottom right' => esc_html__('Bottom Right', 'kirollos-magdy-portfolio-builder'),
                ],
                'default' => 'center center',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-img' => 'object-position: {{VALUE}};',
                ],
                'condition' => [
                    'object-fit' => 'cover',
                ],
            ]
        );

        $this->add_control(
            'separator_panel_style',
            [
                'type' => Controls_Manager::DIVIDER,
                'style' => 'thick',
            ]
        );

        $this->start_controls_tabs('image_effects');

        $this->start_controls_tab(
            'image_normal',
            [
                'label' => esc_html__('Normal', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'opacity',
            [
                'label' => esc_html__('Opacity', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-img' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Css_Filter::get_type(),
            [
                'name' => 'css_filters',
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-img',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'image_hover',
            [
                'label' => esc_html__('Hover', 'kirollos-magdy-portfolio-builder'),
            ]
        );

        $this->add_control(
            'opacity_hover',
            [
                'label' => esc_html__('Opacity', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}:hover .kmpw-dynamic-post-meta-img' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Css_Filter::get_type(),
            [
                'name' => 'css_filters_hover',
                'selector' => '{{WRAPPER}}:hover .kmpw-dynamic-post-meta-img',
            ]
        );

        $this->add_control(
            'background_hover_transition',
            [
                'label' => esc_html__('Transition Duration', 'kirollos-magdy-portfolio-builder') . ' (s)',
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-img' => 'transition-duration: {{SIZE}}s',
                ],
            ]
        );

        $this->add_control(
            'hover_animation',
            [
                'label' => esc_html__('Hover Animation', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-img',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'image_border_radius',
            [
                'label' => esc_html__('Border Radius', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow',
                'exclude' => [
                    'box_shadow_position',
                ],
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta-img',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'price_reviews_style',
            [
                'label' => esc_html__('Price & Reviews Style', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'data_type' => ['woo-reviews', 'woo-price'],
                ],
            ]
        );

        $this->add_control(
            'price_options',
            [
                'label' => esc_html__('Price Options', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sale_price_typography',
                'label' => esc_html__('Sale Price Typography', 'kirollos-magdy-portfolio-builder'),
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta .woo-price .sale-price',
            ]
        );
        $this->add_control(
            'sale_price_color',
            [
                'label' => esc_html__('Sale Price Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta .woo-price .sale-price' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'sale_price_margin',
            [
                'label' => esc_html__('Sale Price Margin', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta .woo-price .sale-price' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'old_price_typography',
                'label' => esc_html__('Old Price Typography', 'kirollos-magdy-portfolio-builder'),
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta .woo-price  .old-price,{{WRAPPER}} .kmpw-dynamic-post-meta .woo-price .old-price > *,{{WRAPPER}} .kmpw-dynamic-post-meta .woo-price  .old-price .woocommerce-Price-currencySymbol',
            ]
        );
        $this->add_control(
            'old_price_color',
            [
                'label' => esc_html__('Old Price Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta .woo-price .old-price' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'old_price_margin',
            [
                'label' => esc_html__('Old Price Margin', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta .woo-price .old-price' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'rate_heading',
            [
                'label' => esc_html__('Rate Options', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before'
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'rate_text_typography',
                'label' => esc_html__('Rate Text Typography', 'kirollos-magdy-portfolio-builder'),
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta .woo-reviews .txt',
            ]
        );
        $this->add_control(
            'rate_text_color',
            [
                'label' => esc_html__('Rate Text Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta .woo-reviews .txt' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'stars_rate_icon_size',
            [
                'label' => esc_html__('Star Icon Size', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'custom', 'rem', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 300,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta .woo-reviews .stars svg' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'stars_rate_icon_color',
            [
                'label' => esc_html__('Star Icon Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta .woo-reviews .stars svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'tags_categories_style',
            [
                'label' => esc_html__('Tags & Categories Style', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'data_type' => ['categories', 'tags'],
                ],
            ]
        );
        $this->add_control(
            'tags_categories_display',
            [
                'label' => esc_html__('Tags & Categories Display Type', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => esc_html__('Default', 'kirollos-magdy-portfolio-builder'),
                    'block' => esc_html__('Block', 'kirollos-magdy-portfolio-builder'),
                    'inline-block' => esc_html__('Inline Block', 'kirollos-magdy-portfolio-builder'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a' => 'display: {{VALUE}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'display: {{VALUE}};'
                ]
            ]
        );
        $this->add_responsive_control(
            'tags_categories_padding',
            [
                'label' => esc_html__('Tags & Categories Padding', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tags_categories_margin',
            [
                'label' => esc_html__('Tags & Categories Margin', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'tags_categories_container_display',
            [
                'label' => esc_html__('Tags & Categories Container Display', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => esc_html__('Default', 'kirollos-magdy-portfolio-builder'),
                    'block' => esc_html__('Block', 'kirollos-magdy-portfolio-builder'),
                    'inline-block' => esc_html__('Inline Block', 'kirollos-magdy-portfolio-builder'),
                    'flex' => esc_html__('Flex', 'kirollos-magdy-portfolio-builder'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags .kmpw-dynamic-post-meta-text' => 'display: {{VALUE}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories .kmpw-dynamic-post-meta-text' => 'display: {{VALUE}};',
                ]
            ]
        );

        $this->add_responsive_control(
            'tags_categories_container_flex_direction',
            [
                'label' => esc_html__('Flex Direction', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'before' => [
                        'title' => esc_html__('Before', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'after' => [
                        'title' => esc_html__('After', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                    'start' => [
                        'title' => esc_html__('Start', 'kirollos-magdy-portfolio-builder'),
                        'icon' => "eicon-h-align-right",
                    ],
                    'end' => [
                        'title' => esc_html__('End', 'kirollos-magdy-portfolio-builder'),
                        'icon' => "eicon-h-align-left",
                    ],
                ],
                'selectors_dictionary' => [
                    'before' => 'flex-direction: column;',
                    'after' => 'flex-direction: column-reverse;',
                    'start' => 'flex-direction: row;',
                    'end' => 'flex-direction: row-reverse;',
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags .kmpw-dynamic-post-meta-text' => '{{VALUE}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories .kmpw-dynamic-post-meta-text' => '{{VALUE}};',
                ],
                'condition' => ['tags_categories_container_display' => ['flex']],
            ]
        );

        $this->add_responsive_control(
            'tags_categories_container_justify_content',
            [
                'label' => esc_html__('Justify Content', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::CHOOSE,
                'label_block' => true,
                'default' => '',
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__('Start', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-justify-start-h',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-justify-center-h',
                    ],
                    'flex-end' => [
                        'title' => esc_html__('End', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-justify-end-h',
                    ],
                    'space-between' => [
                        'title' => esc_html__('Space Between', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-justify-space-between-h',
                    ],
                    'space-around' => [
                        'title' => esc_html__('Space Around', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-justify-space-around-h',
                    ],
                    'space-evenly' => [
                        'title' => esc_html__('Space Evenly', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-justify-space-evenly-h',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags .kmpw-dynamic-post-meta-text' => 'justify-content: {{VALUE}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories .kmpw-dynamic-post-meta-text' => 'justify-content: {{VALUE}};',
                ],
                'condition' => ['tags_categories_container_display' => 'flex'],
                'responsive' => true,
            ]
        );

        $this->add_responsive_control(
            'tags_categories_container_align_items',
            [
                'label' => esc_html__('Align Items', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::CHOOSE,
                'default' => '',
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__('Start', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-align-start-v',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-align-center-v',
                    ],
                    'flex-end' => [
                        'title' => esc_html__('End', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-align-end-v',
                    ],
                    'stretch' => [
                        'title' => esc_html__('Stretch', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-align-stretch-v',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags .kmpw-dynamic-post-meta-text' => 'align-items: {{VALUE}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories .kmpw-dynamic-post-meta-text' => 'align-items: {{VALUE}};',
                ],
                'condition' => ['tags_categories_container_display' => 'flex'],
                'responsive' => true,
            ]
        );

        $this->add_responsive_control(
            'tags_categories_container_flex_wrap',
            [
                'label' => esc_html__('Wrap', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'nowrap' => [
                        'title' => esc_html__('No Wrap', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-nowrap',
                    ],
                    'wrap' => [
                        'title' => esc_html__('Wrap', 'kirollos-magdy-portfolio-builder'),
                        'icon' => 'eicon-flex eicon-wrap',
                    ],
                ],
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags .kmpw-dynamic-post-meta-text' => 'flex-wrap: {{VALUE}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories .kmpw-dynamic-post-meta-text' => 'flex-wrap: {{VALUE}};',
                ],
                'condition' => ['tags_categories_container_display' => ['flex']],
            ]
        );

        $this->add_responsive_control(
            'tags_categories_border_radius',
            [
                'label' => esc_html__('Border Radius', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'tags_categories_blur_method',
            [
                'label' => esc_html__('Blur Method', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'backdrop-filter' => 'backdrop-filter',
                    'filter' => 'filter',
                ],
                'default' => 'backdrop-filter',
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a' => '{{VALUE}}: blur({{tags_categories_blur_value.SIZE}}px);',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => '{{VALUE}}: blur({{tags_categories_blur_value.SIZE}}px);',
                ],
            ]
        );
        $this->add_control(
            'tags_categories_blur_value',
            [
                'label' => esc_html__('Blur', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 250,
                    ],
                ],
            ]
        );
        $this->start_controls_tabs(
            'categories_tags_tabs',
        );
        $this->start_controls_tab(
            'categories_tags_normal_tab',
            [
                'label'   => esc_html__('Normal', 'kirollos-magdy-portfolio-builder'),
            ]
        );
        $this->add_control(
            'category_tag_color',
            [
                'label' => esc_html__('Text Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'tags_categories_background',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a',
                'exclude' => ['image']
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tags_categories_border',
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a',
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'categories_tags_dark_mode_heading',
            [
                'label' => esc_html__('Dark Mode', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'categories_tags_color_dark_mode',
            [
                'label' => esc_html__('Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a' => 'color: {{VALUE}};',
                    '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a' => 'color: {{VALUE}};',
                    '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'color: {{VALUE}};',
                    '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'categories_tags_border_color_dark_mode',
            [
                'label' => esc_html__('Border Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a' => 'border-color: {{VALUE}};',
                    '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a' => 'border-color: {{VALUE}};',
                    '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'border-color: {{VALUE}};',
                    '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'categories_tags_background_dark_mode',
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a',
                'types' => ['classic', 'gradient'],
                'fields_options' => [
                    'color' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-color: {{VALUE}};',
                            '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-color: {{VALUE}};',
                        ],
                    ],
                    'gradient_angle' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-color: transparent; background-image: linear-gradient({{SIZE}}{{UNIT}}, {{color.VALUE}} {{color_stop.SIZE}}{{color_stop.UNIT}}, {{color_b.VALUE}} {{color_b_stop.SIZE}}{{color_b_stop.UNIT}})',
                            '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-color: transparent; background-image: linear-gradient({{SIZE}}{{UNIT}}, {{color.VALUE}} {{color_stop.SIZE}}{{color_stop.UNIT}}, {{color_b.VALUE}} {{color_b_stop.SIZE}}{{color_b_stop.UNIT}})',
                        ],
                    ],
                    'gradient_position' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-color: transparent; background-image: radial-gradient(at {{VALUE}}, {{color.VALUE}} {{color_stop.SIZE}}{{color_stop.UNIT}}, {{color_b.VALUE}} {{color_b_stop.SIZE}}{{color_b_stop.UNIT}})',
                            '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-color: transparent; background-image: radial-gradient(at {{VALUE}}, {{color.VALUE}} {{color_stop.SIZE}}{{color_stop.UNIT}}, {{color_b.VALUE}} {{color_b_stop.SIZE}}{{color_b_stop.UNIT}})',
                        ],
                    ],
                    'image' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-image: url("{{URL}}");',
                            '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-image: url("{{URL}}");',
                        ],
                    ],
                    'position' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-position: {{VALUE}};',
                            '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-position: {{VALUE}};',
                        ],
                    ],
                    'xpos' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-position: {{SIZE}}{{UNIT}} {{ypos.SIZE}}{{ypos.UNIT}}',
                            '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-position: {{SIZE}}{{UNIT}} {{ypos.SIZE}}{{ypos.UNIT}}',
                        ],
                    ],
                    'ypos' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-position: {{xpos.SIZE}}{{xpos.UNIT}} {{SIZE}}{{UNIT}}',
                            '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-position: {{xpos.SIZE}}{{xpos.UNIT}} {{SIZE}}{{UNIT}}',
                        ],
                    ],
                    'attachment' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode (desktop+){{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode (desktop+){{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-attachment: {{VALUE}};',
                            '} body.kmpw-dark-mode (desktop+){{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode (desktop+){{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-attachment: {{VALUE}};',
                        ],
                    ],
                    'repeat' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-repeat: {{VALUE}};',
                            '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-repeat: {{VALUE}};',
                        ],
                    ],
                    'size' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-size: {{VALUE}};',
                            '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-size: {{VALUE}};',
                        ],
                    ],
                    'bg_width' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-size: {{SIZE}}{{UNIT}} auto',
                            '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background-size: {{SIZE}}{{UNIT}} auto',
                        ],
                    ],
                    'video_fallback' => [
                        'selectors' => [
                            '@media (prefers-color-scheme: dark){ body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-auto-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background: url("{{URL}}") 50% 50%; background-size: cover;',
                            '} body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a, body.kmpw-dark-mode {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a' => 'background: url("{{URL}}") 50% 50%; background-size: cover;',
                        ],
                    ],
                ]
            ]
        );
        $this->end_controls_tab();
        $this->start_controls_tab(
            'categories_tags_hover_tab',
            [
                'label'   => esc_html__('Hover', 'kirollos-magdy-portfolio-builder'),
            ]
        );
        $this->add_control(
            'category_tag_color_hover',
            [
                'label' => esc_html__('Text Color', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'tags_categories_background_hover',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a:hover, {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a:hover',
                'exclude' => ['image']
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'tags_categories_border_hover',
                'selector' => '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags a:hover, {{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories a:hover',
                'separator' => 'before',
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_control(
            'categories_tags_separator_heading',
            [
                'label' => esc_html__('Separator Wrapper Controls', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'condition' => ['separator_wrap_in_span' => 'yes'],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'categories_tags_separator_margin',
            [
                'label' => esc_html__('Margin', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'condition' => ['separator_wrap_in_span' => 'yes'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags .separator' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories .separator' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'categories_tags_separator_margin_opacity',
            [
                'label' => esc_html__('Opacity', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'condition' => ['separator_wrap_in_span' => 'yes'],
                'selectors' => [
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-tags .separator' => 'opacity: {{SIZE}};',
                    '{{WRAPPER}} .kmpw-dynamic-post-meta.kmpw-meta-type-categories .separator' => 'opacity: {{SIZE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'animation_options_section',
            [
                'label' => esc_html__('Animation Options', 'kirollos-magdy-portfolio-builder'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'animation_options',
            [
                'label' => esc_html__('Animation Options', 'kirollos-magdy-portfolio-builder'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => esc_html__('Default', 'kirollos-magdy-portfolio-builder'),
                    'swiper-parallax' => esc_html__('Swiper Parallax', 'kirollos-magdy-portfolio-builder'),
                ],
            ]
        );

        $this->add_control(
            'parallax_offset',
            [
                'label' => esc_html__('Parallax Offset', 'kirollos-magdy-portfolio-builder'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => -2000,
                'min' => -5000,
                'max' => 5000,
                'step' => 10,
                'condition' => [
                    'animation_options' => 'swiper-parallax',
                ]
            ]
        );



        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings(); ?>

        <div class="kmpw-dynamic-post-meta kmpw-meta-type-<?php echo $settings['data_type'] . ' ' . 'selector-type-' . $settings['title_style_overlay_selector'] ?>" <?php if ($settings['animation_options'] == 'swiper-parallax') echo 'data-swiper-parallax="' . esc_attr($settings['parallax_offset']) . '"'; ?>>

            <?php

            switch ($settings['data_type']) {
                case 'title':

                    $link_start = '';
                    $link_end = '';

                    if ($settings['link'] === 'yes') : $link_start = '<a href="' . esc_url(get_the_permalink()) . '">';
                        $link_end = '</a>';
                    endif;

            ?>
                    <<?= Utils::validate_html_tag($settings['header_size']) ?> class="kmpw-dynamic-post-meta-text<?php if ($settings['floating_title'] === 'yes') echo esc_attr(' floating-title'); ?>"><?= $link_start ?><?php the_title(); ?><?= $link_end ?></<?= Utils::validate_html_tag($settings['header_size']) ?>>
                <?php
                    break;
                case 'date':
                    $custom_date_format = empty($settings['custom_date_format']) ? 'F j, Y' : $settings['custom_date_format'];

                    $format_options = [
                        'default' => 'F j, Y',
                        '0' => 'F j, Y',
                        '1' => 'Y-m-d',
                        '2' => 'm/d/Y',
                        '3' => 'd/m/Y',
                        'custom' => $custom_date_format,
                    ];

                    $post_date = get_the_time('U');
                    $current_date = current_time('timestamp');
                    $days_diff = floor(($current_date - $post_date) / DAY_IN_SECONDS);

                    if ('yes' === $settings['use_days_ago'] && $days_diff <= $settings['days_ago_limit']) {
                        $title = sprintf(esc_html__('%s %s', 'kirollos-magdy-portfolio-builder'), $days_diff, $settings['custom_days_ago_text']);
                    } else {
                        $title = get_the_time($format_options[$settings['date_format']]);
                    }

                    $this->add_render_attribute('title', 'class', 'kmpw-dynamic-post-meta-text');

                    $link_start = '';
                    $link_end = '';

                    if ($settings['link'] == 'yes') :
                        $link_start = '<a href="' . esc_url(get_day_link(get_post_time('Y'), get_post_time('m'), get_post_time('j'))) . '">';
                        $link_end = '</a>';
                    endif;

                    $title_html = sprintf('<%1$s %2$s>' . $link_start . '%3$s' . $link_end . '</%1$s>', Utils::validate_html_tag($settings['header_size']), $this->get_render_attribute_string('title'), $title);

                    echo $title_html;

                    break;
                case 'time':
                    $custom_time_format = empty($settings['custom_time_format']) ? 'g:i a' : $settings['custom_time_format'];

                    $format_options = [
                        'default' => 'g:i a',
                        '0' => 'g:i a',
                        '1' => 'g:i A',
                        '2' => 'H:i',
                        'custom' => $custom_time_format,
                    ];

                    $post_time = get_the_time('U');
                    $current_time = current_time('timestamp');
                    $hours_diff = floor(($current_time - $post_time) / HOUR_IN_SECONDS);

                    if ('yes' === $settings['use_hours_ago'] && $hours_diff <= $settings['hours_ago_limit']) {
                        $title = sprintf(esc_html__('%s %s', 'kirollos-magdy-portfolio-builder'), $hours_diff, $settings['custom_hours_ago_text']);
                    } else {
                        $title = get_the_time($format_options[$settings['time_format']]);
                    }

                    $this->add_render_attribute('title', 'class', 'kmpw-dynamic-post-meta-text');

                    $link_start = '';
                    $link_end = '';

                    if ($settings['link'] == 'yes') :
                        $link_start = '<a href="' . esc_url(get_day_link(get_post_time('Y'), get_post_time('m'), get_post_time('j'))) . '">';
                        $link_end = '</a>';
                    endif;

                    $title_html = sprintf('<%1$s %2$s>' . $link_start . '%3$s' . $link_end . '</%1$s>', Utils::validate_html_tag($settings['header_size']), $this->get_render_attribute_string('title'), $title);

                    echo $title_html;

                    break;

                case 'excerpt':

                    $excerpt = get_the_excerpt();
                    $excerpt = substr($excerpt, 0, $settings['excerpt']);

                    $this->add_render_attribute('title', 'class', 'kmpw-dynamic-post-meta-text');

                    $title = $excerpt . $settings['excerpt_after'];

                    $title_html = sprintf('<%1$s %2$s>%3$s</%1$s>', Utils::validate_html_tag($settings['header_size']), $this->get_render_attribute_string('title'), $title);

                    // PHPCS - the variable $title_html holds safe data.
                    echo $title_html;

                    break;
                case 'comments':
                    if (comments_open()) {
                        $default_strings = [
                            'string_no_comments' => esc_html__('No Comments', 'kirollos-magdy-portfolio-builder'),
                            'string_one_comment' => esc_html__('%s Comment', 'kirollos-magdy-portfolio-builder'),
                            'string_comments' => esc_html__('%s Comments', 'kirollos-magdy-portfolio-builder'),
                        ];

                        if ('yes' === $settings['comments_custom_strings']) {
                            if (!empty($settings['string_no_comments'])) {
                                $default_strings['string_no_comments'] = $settings['string_no_comments'];
                            }

                            if (!empty($settings['string_one_comment'])) {
                                $default_strings['string_one_comment'] = $settings['string_one_comment'];
                            }

                            if (!empty($settings['string_comments'])) {
                                $default_strings['string_comments'] = $settings['string_comments'];
                            }
                        }

                        $num_comments = (int) get_comments_number();
                        $title = '';

                        if (0 === $num_comments) {
                            $title = $default_strings['string_no_comments'];
                        } elseif (1 === $num_comments) {
                            // Handle single comment case properly for RTL
                            $title = sprintf($default_strings['string_one_comment'], $num_comments);
                        } else {
                            // Handle multiple comments case properly for RTL
                            $title = sprintf($default_strings['string_comments'], $num_comments);
                        }

                        // Add RTL direction attribute if the site is RTL
                        $rtl_attr = '';
                        if (is_rtl()) {
                            $rtl_attr = ' dir="rtl"';
                        }

                        $this->add_render_attribute('title', 'class', 'kmpw-dynamic-post-meta-text');

                        // Add RTL support attributes
                        if (is_rtl()) {
                            $this->add_render_attribute('title', 'dir', 'rtl');
                        }

                        $link_start = '';
                        $link_end = '';

                        if ($settings['link'] == 'yes') :
                            $link_start = '<a href="' . esc_url(get_comments_link()) . '">';
                            $link_end = '</a>';
                        endif;

                        $title_html = sprintf(
                            '<%1$s %2$s>%3$s%4$s%5$s</%1$s>',
                            Utils::validate_html_tag($settings['header_size']),
                            $this->get_render_attribute_string('title'),
                            $link_start,
                            esc_html($title),
                            $link_end
                        );

                        echo $title_html;
                    }
                    break;
                case 'avatar':

                    global $post;
                    // Check if $post exists and has post_author property
                    if (!$post || !isset($post->post_author)) {
                        echo '<img class="kmpw-dynamic-post-meta-img" src="' . esc_url(\Elementor\Utils::get_placeholder_image_src()) . '" alt="Author Avatar">';
                        break;
                    }

                    $author_id = $post->post_author;

                    $link_start = '';
                    $link_end = '';

                    if ($settings['link'] == 'yes') : $link_start = '<a href="' . esc_url(get_author_posts_url($author_id)) . '">';
                        $link_end = '</a>';
                    endif;

                    $author_name = get_the_author_meta('display_name'); // Retrieve author name for alt text

                    echo $link_start . "<img class='kmpw-dynamic-post-meta-img' src='" . get_avatar_url(get_the_author_meta('ID'), 96) . "' alt='" . esc_attr($author_name) . "'>" . $link_end;
                    break;
                case 'author':
                    global $post;

                    // Check if $post exists and has post_author property
                    if (!$post || !isset($post->post_author)) {
                        $title = esc_html__('Author Name', 'kirollos-magdy-portfolio-builder');
                    } else {
                        $author_id = $post->post_author;
                        $title = get_the_author_meta('display_name', $author_id);
                    }

                    $this->add_render_attribute('title', 'class', 'kmpw-dynamic-post-meta-text');

                    $link_start = '';
                    $link_end = '';

                    if ($settings['link'] == 'yes' && $post && isset($post->post_author)) :
                        $link_start = '<a href="' . esc_url(get_author_posts_url($post->post_author)) . '">';
                        $link_end = '</a>';
                    endif;

                    $title_html = sprintf('<%1$s %2$s>' . $link_start . '%3$s' . $link_end . '</%1$s>', Utils::validate_html_tag($settings['header_size']), $this->get_render_attribute_string('title'), $title);

                    echo $title_html;
                    break;
                case 'author-bio':

                    global $post;

                    // Check if $post exists and has post_author property
                    if (!$post || !isset($post->post_author)) {
                        $title = esc_html__('Author bio description', 'kirollos-magdy-portfolio-builder');
                        $author_id = null;
                    } else {
                        $author_id = $post->post_author;
                        $title = get_the_author_meta('description', $author_id);

                        // Fallback if no bio is set
                        if (empty($title)) {
                            $title = esc_html__('No bio available', 'kirollos-magdy-portfolio-builder');
                        }
                    }

                    $this->add_render_attribute('title', 'class', 'kmpw-dynamic-post-meta-text');

                    $link_start = '';
                    $link_end = '';

                    if ($settings['link'] == 'yes' && !empty($author_id)) {
                        $link_start = '<a href="' . esc_url(get_author_posts_url($author_id)) . '">';
                        $link_end = '</a>';
                    }

                    $title_html = sprintf(
                        '<%1$s %2$s>%3$s%4$s%5$s</%1$s>',
                        Utils::validate_html_tag($settings['header_size']),
                        $this->get_render_attribute_string('title'),
                        $link_start,
                        $title,
                        $link_end
                    );

                    echo $title_html;
                    break;
                case 'categories':
                    global $post;
                    $category = false;
                    $taxonomy_names = get_post_taxonomies();
                    if ($taxonomy_names) {
                        $category = get_the_terms($post->ID, $taxonomy_names[0]);
                    }

                    // Show placeholder in editor if no categories and placeholder is enabled
                    if ($this->is_editor_mode() && $settings['show_editor_placeholder'] === 'yes' && (!$category || is_wp_error($category))) {
                        $placeholder_categories = [
                            (object) ['name' => 'Design', 'term_id' => 1],
                            (object) ['name' => 'Technology', 'term_id' => 2],
                            (object) ['name' => 'Web Development', 'term_id' => 3]
                        ];
                        $category = $placeholder_categories;
                    }
                    ?>
                    <<?= Utils::validate_html_tag($settings['header_size']) ?> class="kmpw-dynamic-post-meta-text">
                    <?php
                    $cat_counter = 0;
                    if ($category && !is_wp_error($category)) {
                        foreach ($category as $cat) {
                            if ($cat_counter >= 1) {
                                echo $settings['separator_wrap_in_span'] === 'yes'
                                    ? '<span class="separator">' . $settings['meta_separator'] . '</span>'
                                    : $settings['meta_separator'];
                            }

                            // Check if links should be enabled
                            if ($settings['link'] === 'yes') {
                                // Use placeholder link in editor mode
                                if ($this->is_editor_mode() && $settings['show_editor_placeholder'] === 'yes' && !isset($cat->slug)) {
                                    echo '<a href="#" onclick="return false;">' . $cat->name . '</a>';
                                } else {
                                    echo '<a href="' . esc_url(get_term_link($cat)) . '">' . $cat->name . '</a>';
                                }
                            } else {
                                // No link, just display the category name
                                echo $cat->name;
                            }
                            $cat_counter++;
                        }
                    }
                    ?>
                    </<?= Utils::validate_html_tag($settings['header_size']) ?>>
                    <?php
                    break;
                case 'tags':
                    global $post;
                    $tags = false;
                    $taxonomy_names = get_post_taxonomies();
                    if ($taxonomy_names && isset($taxonomy_names[1])) {
                        $tags = get_the_terms($post->ID, $taxonomy_names[1]);
                    }

                    // Show placeholder in editor if no tags and placeholder is enabled
                    if ($this->is_editor_mode() && $settings['show_editor_placeholder'] === 'yes' && (!$tags || is_wp_error($tags))) {
                        $placeholder_tags = [
                            (object) ['name' => 'WordPress', 'term_id' => 1],
                            (object) ['name' => 'Elementor', 'term_id' => 2],
                            (object) ['name' => 'PHP', 'term_id' => 3]
                        ];
                        $tags = $placeholder_tags;
                    }
                    ?>
                    <<?= Utils::validate_html_tag($settings['header_size']) ?> class="kmpw-dynamic-post-meta-text">
                    <?php
                    $tag_counter = 0;
                    if ($tags && !is_wp_error($tags)) {
                        foreach ($tags as $tag) {
                            if ($tag_counter >= 1) {
                                echo $settings['separator_wrap_in_span'] === 'yes'
                                    ? '<span class="separator">' . $settings['meta_separator'] . '</span>'
                                    : $settings['meta_separator'];
                            }

                            // Check if links should be enabled
                            if ($settings['link'] === 'yes') {
                                // Use placeholder link in editor mode
                                if ($this->is_editor_mode() && $settings['show_editor_placeholder'] === 'yes' && !isset($tag->slug)) {
                                    echo '<a href="#" onclick="return false;">' . $tag->name . '</a>';
                                } else {
                                    echo '<a href="' . esc_url(get_term_link($tag)) . '">' . $tag->name . '</a>';
                                }
                            } else {
                                // No link, just display the tag name
                                echo $tag->name;
                            }
                            $tag_counter++;
                        }
                    }
                    ?>
                    </<?= Utils::validate_html_tag($settings['header_size']) ?>>
                    <?php
                    break;
                case 'woo-price':
            ?>

                <<?= Utils::validate_html_tag($settings['header_size']) ?> class="kmpw-dynamic-post-meta-text woo-price">
                    <?php
                    global $product;
                    if (isset($product)) {
                        if ($product->is_type('variable')) {
                            echo $product->get_price_html();
                        } else {
                            $regular_price = get_post_meta(get_the_ID(), '_regular_price', true);
                            $sale_price = get_post_meta(get_the_ID(), '_sale_price', true);
                            if ($product->is_on_sale()) {
                    ?>
                                <span class="sale-price"><?php echo wc_price($sale_price); ?></span><span class="old-price"><?php echo wc_price($regular_price); ?></span>
                    <?php
                            } else {
                                echo wc_price($regular_price);
                            }
                        };
                    };
                    ?>
                </<?= Utils::validate_html_tag($settings['header_size']) ?>>

            <?php
                break;
                case 'port-sec':
                    $secondary_image_id = get_post_meta(get_the_ID(), '_secondary_featured_image', true);

                    $image_size = !empty($settings['image_size']) ? $settings['image_size'] : 'thumbnail';

                    if ($secondary_image_id) {
                        echo wp_get_attachment_image($secondary_image_id, $image_size, false, ['class' => 'kmpw-dynamic-post-meta-img']);
                    } else {
                        $placeholder_url = \Elementor\Utils::get_placeholder_image_src();
                        echo '<img class="kmpw-dynamic-post-meta-img" src="' . esc_url($placeholder_url) . '" alt="Placeholder Image" />';
                    }

                    break;
                case 'woo-reviews': ?>
                    <div class="woo-reviews">
                        <?php
                        global $product;
                        if (isset($product)):
                            if ($product->get_average_rating()) : ?>
                                <div class="stars">
                                    <?php
                                    $rating = $product->get_average_rating();
                                    $stars = floor($rating);
                                    for ($i = 0; $i < $stars; $i++) {
                                    ?>
                                        <svg aria-hidden="true" class="e-font-icon-svg e-fas-star" viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path>
                                        </svg>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <span class="txt"> (<?php echo $product->get_rating_count() . ' ' . $settings['woo_reviews_text']; ?>) </span>
                            <?php else : ?>
                                <span class="txt"><?= $settings['woo_no_reviews_text']; ?></span>
                        <?php endif;
                        endif; ?>
                    </div>
            <?php

                    break;
                case 'role':

                    global $post;

                    // Check if $post exists and has post_author property
                    if (!$post || !isset($post->post_author)) {
                        $role_names = esc_html__('User Role', 'kirollos-magdy-portfolio-builder');
                    } else {
                        $author_id = $post->post_author;
                        $author_data = get_userdata($author_id);

                        if (!empty($author_data->roles)) {
                            $roles = $author_data->roles;
                            $role_names = implode(', ', array_map('ucfirst', $roles));
                        } else {
                            $role_names = esc_html__('No role assigned', 'kirollos-magdy-portfolio-builder');
                        }
                    }

                    $this->add_render_attribute('title', 'class', 'kmpw-dynamic-post-meta-text');

                    if (!empty($author_data->roles)) {
                        // Retrieve roles assigned to the post author
                        $roles = $author_data->roles;
                        // Display roles as a comma-separated string
                        $role_names = implode(', ', array_map('ucfirst', $roles));

                        $link_start = '';
                        $link_end = '';

                        if ($settings['link'] == 'yes') :
                            $link_start = '<a href="' . esc_url(get_author_posts_url($author_id)) . '">';
                            $link_end = '</a>';
                        endif;

                        // Output the role using header size
                        $title_html = sprintf('<%1$s %2$s>' . $link_start . '%3$s' . $link_end . '</%1$s>', Utils::validate_html_tag($settings['header_size']), $this->get_render_attribute_string('title'), esc_html($role_names));

                        // PHPCS - the variable $title_html holds safe data.
                        echo $title_html;
                    } else {
                        // Fallback if the author has no role
                        $fallback_role = __('No role assigned', 'kirollos-magdy-portfolio-builder');
                        $title_html = sprintf('<%1$s %2$s>%3$s</%1$s>', Utils::validate_html_tag($settings['header_size']), $this->get_render_attribute_string('title'), esc_html($fallback_role));

                        echo $title_html;
                    }

                    break;
                case 'count':
                    static $post_counter = 0;
                    $post_counter++;

                    if ($post_counter < 10) {
                        $count_text = ($settings['count_prefix_zero'] === 'yes')
                            ? sprintf('%02d', $post_counter)
                            : $post_counter;
                    }

                    $this->add_render_attribute('title', 'class', 'kmpw-dynamic-post-meta-text');

                    $link_start = '';
                    $link_end = '';

                    if ($settings['link'] === 'yes') {
                        $link_start = '<a href="' . esc_url(get_the_permalink()) . '">';
                        $link_end = '</a>';
                    }

                    $title_html = sprintf(
                        '<%1$s %2$s>%3$s%4$s%5$s</%1$s>',
                        Utils::validate_html_tag($settings['header_size']),
                        $this->get_render_attribute_string('title'),
                        $link_start,
                        esc_html($count_text),
                        $link_end
                    );

                    echo $title_html;
                    break;
            }

            ?>

        </div>

<?php
    }
}
