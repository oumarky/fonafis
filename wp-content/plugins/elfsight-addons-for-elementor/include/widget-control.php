<?php

if (!defined('ABSPATH')) exit;

use \Elementor\Control_Base_Multiple;

class ElfsightElementorWidgetControl extends Control_Base_Multiple
{
    const CONTROL_TYPE = 'elfsight-elementor-widget-control';

    public function get_type()
    {
        return self::CONTROL_TYPE;
    }

    public function enqueue()
    {
        wp_enqueue_script(
            'elfsight-elementor-widget-control',
            plugins_url('./../build/widget-control.js', __FILE__),
            array('elementor-editor'),
            '1.0.0', true
        );

        wp_enqueue_style(
            'elfsight-iconfont',
            plugins_url('./../iconfont/style.css', __FILE__),
            array(), false
        );
    }

    public function content_template()
    {
        ?>
        <div class="elfsight-control-section" id="create-container">
            <div class="elementor-control-field-description">You need to select the widget you want to embed to your page from the Elfsight catalog</div>

            <div id="create-widget-button-container"></div>
        </div>
        <div class="elfsight-control-section" id="edit-container">
            <div id="edit-widget-button-container"></div>
        </div>
        <?php
    }
}
