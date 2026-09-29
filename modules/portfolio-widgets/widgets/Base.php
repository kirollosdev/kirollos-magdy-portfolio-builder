<?php

namespace KirollosMagdy\PortfolioWidgets\Widgets;

use Elementor\Widget_Base;
use KirollosMagdy\PortfolioWidgets\Module;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Shared identity for every widget in this module.
 *
 * Each widget declares `const SLUG` and inherits its Elementor name, panel
 * category, and script handle from here, so the host plugin can change the
 * prefix in one place at init() time instead of editing six files.
 */
abstract class Base extends Widget_Base
{
    const SLUG = '';

    public function get_name()
    {
        return Module::instance()->widget_name(static::SLUG);
    }

    public function get_categories()
    {
        return [Module::instance()->category_slug()];
    }

    /**
     * Registered script handle for this widget's own JS file.
     */
    protected function handle($slug = null)
    {
        return Module::instance()->handle($slug ?: static::SLUG);
    }
}
