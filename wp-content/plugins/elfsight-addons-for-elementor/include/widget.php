<?php

if (!defined('ABSPATH')) exit;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;

class ElfsightElementorWidget extends Widget_Base
{
    const CONTROL_ID = 'elfsight_widget';
    const CONTROL_TYPE = 'elfsight-elementor-widget-control';
    const SECTION_ID = 'elfsight_widget_content_section';
    const ID_ENTRY = 'id';

    public function get_name()
    {
        return 'elfsight-elementor-widget';
    }

    public function get_title()
    {
        return __('Elfsight Widgets', 'elfsight-elementor-extension');
    }

    public function get_icon()
    {
        return 'icon-elfsight-glyph';
    }

    public function get_keywords()
    {
        require_once(__DIR__ . '/widget-keywords.php');

        return $keywords;
    }

    public function get_categories()
    {
        return array('general');
    }

    protected function _register_controls()
    {
        $this->start_controls_section(
            self::SECTION_ID,
            array(
                'label' => __('Widget', 'elfsight-elementor-extension'),
                'tab' => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            self::CONTROL_ID,
            array(
                'type' => self::CONTROL_TYPE
            )
        );

        $this->end_controls_section();
    }

    protected function get_elfsight_widget_id()
    {
        $settings = $this->get_settings_for_display();

        return isset($settings[self::CONTROL_ID][self::ID_ENTRY]) && !empty($settings[self::CONTROL_ID][self::ID_ENTRY])
            ? $settings[self::CONTROL_ID][self::ID_ENTRY] : null;
    }

    protected function render()
    {
        $widget_id = $this->get_elfsight_widget_id();

        if ($widget_id) {
            echo "<div class=\"elfsight-app-{$widget_id}\"></div>";
        }
    }
}
