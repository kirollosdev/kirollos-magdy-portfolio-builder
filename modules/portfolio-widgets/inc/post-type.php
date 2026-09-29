<?php

/**
 * Portfolio post type.
 *
 * Ships with the module so the widgets never depend on the active theme. It
 * only registers when nothing else has claimed the name, so an existing theme
 * or plugin registration still wins.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('pw_register_portfolio_post_type')) {

    function pw_register_portfolio_post_type($post_type = 'portfolios')
    {
        if (post_type_exists($post_type)) {
            return;
        }

        $labels = [
            'name'               => __('Portfolios', 'kirollos-magdy-portfolio-builder'),
            'singular_name'      => __('Portfolio', 'kirollos-magdy-portfolio-builder'),
            'menu_name'          => __('Portfolios', 'kirollos-magdy-portfolio-builder'),
            'add_new'            => __('Add New', 'kirollos-magdy-portfolio-builder'),
            'add_new_item'       => __('Add New Portfolio', 'kirollos-magdy-portfolio-builder'),
            'edit_item'          => __('Edit Portfolio', 'kirollos-magdy-portfolio-builder'),
            'new_item'           => __('New Portfolio', 'kirollos-magdy-portfolio-builder'),
            'view_item'          => __('View Portfolio', 'kirollos-magdy-portfolio-builder'),
            'search_items'       => __('Search Portfolios', 'kirollos-magdy-portfolio-builder'),
            'not_found'          => __('No portfolios found', 'kirollos-magdy-portfolio-builder'),
            'not_found_in_trash' => __('No portfolios found in Trash', 'kirollos-magdy-portfolio-builder'),
            'all_items'          => __('All Portfolios', 'kirollos-magdy-portfolio-builder'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'menu_position'      => 21,
            'menu_icon'          => 'dashicons-portfolio',
            'has_archive'        => true,
            'hierarchical'       => false,
            'rewrite'            => [
                'slug'       => apply_filters('pw_portfolio_slug', 'portfolio'),
                'with_front' => false,
            ],
            'supports'           => [
                'title',
                'editor',
                'thumbnail',
                'excerpt',
                'custom-fields',
                'revisions',
                'page-attributes',
            ],
        ];

        register_post_type($post_type, apply_filters('pw_portfolio_post_type_args', $args));
    }
}
