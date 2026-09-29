<?php

/**
 * Service + Industry taxonomies, used by Portfolio Filter List and
 * Works Tabs Mini.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('pw_register_portfolio_taxonomies')) {

    function pw_register_portfolio_taxonomies($post_type = 'portfolios')
    {
        if (! post_type_exists($post_type)) {
            return;
        }

        $service_labels = [
            'name'              => __('Portfolio Services', 'kirollos-magdy-portfolio-builder'),
            'singular_name'     => __('Portfolio Service', 'kirollos-magdy-portfolio-builder'),
            'search_items'      => __('Search Services', 'kirollos-magdy-portfolio-builder'),
            'all_items'         => __('All Services', 'kirollos-magdy-portfolio-builder'),
            'parent_item'       => __('Parent Service', 'kirollos-magdy-portfolio-builder'),
            'parent_item_colon' => __('Parent Service:', 'kirollos-magdy-portfolio-builder'),
            'edit_item'         => __('Edit Service', 'kirollos-magdy-portfolio-builder'),
            'update_item'       => __('Update Service', 'kirollos-magdy-portfolio-builder'),
            'add_new_item'      => __('Add New Service', 'kirollos-magdy-portfolio-builder'),
            'new_item_name'     => __('New Service Name', 'kirollos-magdy-portfolio-builder'),
            'menu_name'         => __('Services', 'kirollos-magdy-portfolio-builder'),
        ];

        $industry_labels = [
            'name'              => __('Portfolio Industries', 'kirollos-magdy-portfolio-builder'),
            'singular_name'     => __('Portfolio Industry', 'kirollos-magdy-portfolio-builder'),
            'search_items'      => __('Search Industries', 'kirollos-magdy-portfolio-builder'),
            'all_items'         => __('All Industries', 'kirollos-magdy-portfolio-builder'),
            'parent_item'       => __('Parent Industry', 'kirollos-magdy-portfolio-builder'),
            'parent_item_colon' => __('Parent Industry:', 'kirollos-magdy-portfolio-builder'),
            'edit_item'         => __('Edit Industry', 'kirollos-magdy-portfolio-builder'),
            'update_item'       => __('Update Industry', 'kirollos-magdy-portfolio-builder'),
            'add_new_item'      => __('Add New Industry', 'kirollos-magdy-portfolio-builder'),
            'new_item_name'     => __('New Industry Name', 'kirollos-magdy-portfolio-builder'),
            'menu_name'         => __('Industries', 'kirollos-magdy-portfolio-builder'),
        ];


        // portfolio_category. Portfolio Slider builds its filter buttons and
        // tax_query from this, and Pin Spacer / Horizontal Slider print its term
        // names on each card, so it has to exist for those three widgets to show
        // anything on a clean install.
        $category_labels = [
            'name'              => __('Portfolio Categories', 'kirollos-magdy-portfolio-builder'),
            'singular_name'     => __('Portfolio Category', 'kirollos-magdy-portfolio-builder'),
            'search_items'      => __('Search Categories', 'kirollos-magdy-portfolio-builder'),
            'all_items'         => __('All Categories', 'kirollos-magdy-portfolio-builder'),
            'parent_item'       => __('Parent Category', 'kirollos-magdy-portfolio-builder'),
            'parent_item_colon' => __('Parent Category:', 'kirollos-magdy-portfolio-builder'),
            'edit_item'         => __('Edit Category', 'kirollos-magdy-portfolio-builder'),
            'update_item'       => __('Update Category', 'kirollos-magdy-portfolio-builder'),
            'add_new_item'      => __('Add New Category', 'kirollos-magdy-portfolio-builder'),
            'new_item_name'     => __('New Category Name', 'kirollos-magdy-portfolio-builder'),
            'menu_name'         => __('Categories', 'kirollos-magdy-portfolio-builder'),
        ];

        $args = [
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'portfolio-service'],
        ];

        $args['labels']  = $category_labels;
        $args['rewrite'] = ['slug' => 'portfolio-category'];

        if (! taxonomy_exists('portfolio_category')) {
            register_taxonomy('portfolio_category', [$post_type], $args);
        }

        $args['labels']  = $service_labels;
        $args['rewrite'] = ['slug' => 'portfolio-service'];

        if (! taxonomy_exists('portfolio_service')) {
            register_taxonomy('portfolio_service', [$post_type], $args);
        }

        $args['labels']  = $industry_labels;
        $args['rewrite'] = ['slug' => 'portfolio-industry'];

        if (! taxonomy_exists('portfolio_industry')) {
            register_taxonomy('portfolio_industry', [$post_type], $args);
        }
    }
}
