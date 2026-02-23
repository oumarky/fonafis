<?php
/**
 * Plugin Name: Elfsight Addons For Elementor
 * Description: Elfsight Addons includes 50+ widgets for Elementor Page Builder.
 * Plugin URI:  https://elfsight.com/elementor-widgets/?utm_source=portals&utm_medium=wordpress-org&utm_campaign=elfsight-elementor-addons&utm_term=list&utm_content=plugin-site
 * Version:     1.2.0
 * Author:      Elfsight
 * Author URI:  https://elfsight.com/?utm_source=portals&utm_medium=wordpress-org&utm_campaign=elfsight-elementor-addons&utm_content=plugins-list
 * Text Domain: elfsight-elementor-extension
 */

if (!defined('ABSPATH')) exit;

use \Elementor\Plugin;

final class ElfsightElementorExtension
{
    /**
     * Plugin Version
     * @var string
     */
    const VERSION = '1.2.0';

    /**
     * Instance
     * @var ElfsightElementorExtension
     */
    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        add_action('init', array($this, 'loadTextDomain'));
        add_action('plugins_loaded', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueuePublicAssets'));
    }

    public function loadTextDomain()
    {
        load_plugin_textdomain('elfsight-elementor-extension');
    }

    public function init()
    {
        add_action('elementor/widgets/widgets_registered', array($this, 'initWidgets'));
        add_action('elementor/controls/controls_registered', array($this, 'initControls'));
    }

    public function initWidgets()
    {
        require_once(__DIR__ . '/include/widget.php');

        Plugin::instance()->widgets_manager->register_widget_type(new ElfsightElementorWidget());
    }

    public function initControls()
    {
        require_once(__DIR__ . '/include/widget-control.php');

        Plugin::instance()->controls_manager->register_control('elfsight-elementor-widget-control', new ElfsightElementorWidgetControl());
    }

    /**
     * Enqueue a Elfsight platform script on frontend
     */
    public function enqueuePublicAssets()
    {
        wp_enqueue_script(
            'elfsight-platform',
            'https://static.elfsight.com/platform/platform.js',
            array(), false, true
        );

        wp_enqueue_style(
            'elfsight-iconfont',
            plugins_url('iconfont/style.css', __FILE__),
            array(), false
        );
    }
}

ElfsightElementorExtension::instance();
