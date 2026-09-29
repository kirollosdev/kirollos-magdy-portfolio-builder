<?php

/**
 * Portfolio Widgets module.
 *
 * Drop-in module, not a standalone plugin. The host plugin owns the file
 * header, the constants, and the text domain. This file deliberately declares
 * no global constants and no global functions, so it can be merged into any
 * plugin without collisions.
 *
 * Usage from the host plugin's main file:
 *
 *     require_once __DIR__ . '/modules/portfolio-widgets/module.php';
 *
 *     add_action('plugins_loaded', function () {
 *         \KirollosMagdy\PortfolioWidgets\Module::init([
 *             'path'    => __DIR__ . '/modules/portfolio-widgets/',
 *             'url'     => plugin_dir_url(__FILE__) . 'modules/portfolio-widgets/',
 *             'version' => MY_PLUGIN_VERSION,
 *         ]);
 *     });
 *
 * See MERGE.md for the full option list.
 */

namespace KirollosMagdy\PortfolioWidgets;

if (! defined('ABSPATH')) {
    exit;
}

class Module
{
    /**
     * slug => class name, relative to this namespace's Widgets sub-namespace.
     */
    const WIDGETS = [
        'portfolio-pin-spacer'        => 'Portfolio_Pin_Spacer',
        'portfolio-slider'            => 'Portfolio_Slider',
        'portfolio-filter-list'       => 'Portfolio_Filter_List',
        'portfolio-horizontal-slider' => 'Portfolio_Horizontal_Slider',
        'works-slider'                => 'Works_Slider',
        'works-tabs-mini'             => 'Works_Tabs_Mini',
        'dynamic-post-meta'           => 'Dynamic_Post_Meta',
        'post-image'                  => 'Post_Image',
        'post-title'                  => 'Post_Title',
    ];


    /** @var Module|null */
    private static $instance = null;

    /** @var array */
    private $config = [];

    private function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $config {
     *   @type string $path            Absolute path to this module folder, trailing slash. Required.
     *   @type string $url             Public URL to this module folder, trailing slash. Required.
     *   @type string $version         Fallback asset version. Default '1.0.0'.
     *   @type string $widget_prefix   Prefix for Elementor widget names. Default 'kmpb-'.
     *                                 WARNING: baked into saved pages. Decide before you build.
     *   @type string $handle_prefix   Prefix for wp_register_script/style handles. Default 'kmpb-'.
     *   @type string $category_slug   Elementor panel category. Default 'kirollos-portfolio'.
     *   @type string $category_title  Panel category label. Default 'Portfolio Builder'.
     *   @type string $post_type       Post type the widgets query. Default 'portfolios'.
     *   @type bool   $register_post_type  Register $post_type if nothing else has. Default true.
     *   @type bool   $register_taxonomies Register service/industry taxonomies. Default true.
     *   @type bool   $register_gsap       Register gsap/ScrollTrigger from cdnjs. Default true.
     * }
     */
    public static function init(array $config = [])
    {
        if (null !== self::$instance) {
            return self::$instance;
        }

        $config = array_merge([
            'path'                => '',
            'url'                 => '',
            'version'             => '1.0.0',
            'widget_prefix'       => 'kmpb-',
            'handle_prefix'       => 'kmpb-',
            'category_slug'       => 'kirollos-portfolio',
            'category_title'      => 'Portfolio Builder',
            'post_type'           => 'portfolios',
            'register_post_type'  => true,
            'register_taxonomies' => true,
            'register_gsap'       => true,
            'register_secondary_image' => true,
            // CPT UI hooks post types on init:10 and taxonomies on init:9, and
            // most themes use the default priority 10. Registering after that
            // lets the existence guards actually see them and stand down.
            'post_type_priority'  => 11,
            'taxonomy_priority'   => 12,
        ], $config);

        if ('' === $config['path'] || '' === $config['url']) {
            return null;
        }

        $config['path'] = trailingslashit($config['path']);
        $config['url']  = trailingslashit($config['url']);

        self::$instance = new self($config);
        self::$instance->hooks();

        return self::$instance;
    }

    public static function instance()
    {
        return self::$instance;
    }

    private function hooks()
    {
        if ($this->config['register_post_type']) {
            require_once $this->config['path'] . 'inc/post-type.php';
            add_action('init', [$this, 'register_post_type'], (int) $this->config['post_type_priority']);
        }

        if ($this->config['register_taxonomies']) {
            require_once $this->config['path'] . 'inc/taxonomies.php';
            add_action('init', [$this, 'register_taxonomies'], (int) $this->config['taxonomy_priority']);
        }

        if ($this->config['register_secondary_image']) {
            require_once $this->config['path'] . 'inc/secondary-image.php';
            add_action('init', [$this, 'register_secondary_image'], (int) $this->config['taxonomy_priority']);
            add_action('add_meta_boxes', [$this, 'add_secondary_image_meta_box']);
            add_action('save_post', 'pw_save_secondary_image_meta');
        }

        add_action('elementor/elements/categories_registered', [$this, 'register_category']);
        add_action('elementor/frontend/after_register_scripts', [$this, 'register_scripts']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_styles']);
        add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_badge_style']);
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    /* ---------------------------------------------------------------- config */

    public function get($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    public function post_type()
    {
        return $this->config['post_type'];
    }

    public function category_slug()
    {
        return $this->config['category_slug'];
    }

    public function widget_name($slug)
    {
        return $this->config['widget_prefix'] . $slug;
    }

    public function handle($slug)
    {
        return $this->config['handle_prefix'] . $slug;
    }

    public function url($relative)
    {
        return $this->config['url'] . ltrim($relative, '/');
    }

    /**
     * filemtime cache busting, falling back to the host plugin's version.
     */
    private function asset_version($relative)
    {
        $file = $this->config['path'] . ltrim($relative, '/');

        return file_exists($file) ? (string) filemtime($file) : $this->config['version'];
    }

    /* ------------------------------------------------------------ content types */

    public function register_post_type()
    {
        pw_register_portfolio_post_type($this->post_type());
    }

    public function register_taxonomies()
    {
        pw_register_portfolio_taxonomies($this->post_type());
    }

    public function register_secondary_image()
    {
        pw_register_secondary_image_meta($this->post_type());
    }

    public function add_secondary_image_meta_box()
    {
        pw_add_secondary_image_meta_box($this->post_type());
    }

    /* ------------------------------------------------------------------ assets */

    public function register_category($elements_manager)
    {
        $elements_manager->add_category(
            $this->category_slug(),
            [
                'title' => $this->config['category_title'],
                'icon'  => 'fa fa-plug',
            ]
        );
    }

    /**
     * Scripts are registered, not enqueued. Elementor pulls each one in through
     * get_script_depends() only on pages that actually place the widget.
     */
    public function register_scripts()
    {
        if ($this->config['register_gsap']) {
            // Portfolio Pin Spacer and Portfolio Slider both need these.
            // wp_register_script is a no-op if the theme already owns the handle.
            wp_register_script(
                'gsap',
                'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js',
                [],
                '3.12.5',
                true
            );

            wp_register_script(
                'ScrollTrigger',
                'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js',
                ['gsap'],
                '3.12.5',
                true
            );
        }

        // WOW.js, used by Post Image's hover animation option.
        wp_register_script(
            'wow.min',
            $this->url('assets/js/wow.min.js'),
            [],
            '1.1.3',
            true
        );

        foreach (array_keys(self::WIDGETS) as $slug) {
            $relative = 'assets/js/' . $slug . '.js';

            if (! file_exists($this->config['path'] . $relative)) {
                continue; // widgets with no JS of their own (post-title, dynamic-post-meta)
            }

            wp_register_script(
                $this->handle($slug),
                $this->url($relative),
                ['jquery'],
                $this->asset_version($relative),
                true
            );
        }
    }

    public function enqueue_styles()
    {
        $relative = 'assets/css/' . (is_rtl() ? 'style-rtl.css' : 'style.css');

        wp_enqueue_style(
            $this->handle('portfolio'),
            $this->url($relative),
            [],
            $this->asset_version($relative),
            'all'
        );
    }

    public function enqueue_badge_style()
    {
        $relative = 'assets/css/editor-badge.css';

        wp_enqueue_style(
            $this->handle('editor-badge'),
            $this->url($relative),
            [],
            $this->asset_version($relative)
        );
    }

    /* ----------------------------------------------------------------- widgets */

    public function register_widgets($widgets_manager)
    {
        require_once $this->config['path'] . 'widgets/Base.php';

        foreach (self::WIDGETS as $slug => $class) {
            $file = $this->config['path'] . 'widgets/' . $slug . '/' . $slug . '.php';

            if (! file_exists($file)) {
                continue;
            }

            require_once $file;

            $fqcn = __NAMESPACE__ . '\\Widgets\\' . $class;

            if (! class_exists($fqcn) || $this->is_registered($widgets_manager, $this->widget_name($slug))) {
                continue;
            }

            $widgets_manager->register(new $fqcn());
        }
    }

    private function is_registered($widgets_manager, $name)
    {
        if (! method_exists($widgets_manager, 'get_widget_types')) {
            return false;
        }

        return null !== $widgets_manager->get_widget_types($name);
    }
}
