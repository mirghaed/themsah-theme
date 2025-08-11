<?php
if (! defined('ABSPATH')) exit;
// Define base only when Elementor core is loaded. Use eval to avoid parse-time dependency on Elementor classes.
if ( class_exists('\\Elementor\\Widget_Base') && ! class_exists('Themsah_Theme_Elementor_Widget_Base') ) {
    $themsah_widget_base_code = <<<'PHP'
abstract class Themsah_Theme_Elementor_Widget_Base extends \Elementor\Widget_Base {
    public function get_categories() {
        return array('mytheme-widgets');
    }

    public function get_icon() {
        return 'themsah-icon';
    }

    public function get_style_depends() {
        $name = $this->get_name();
        // expected format: mytheme-<slug>
        $slug = preg_replace('/^mytheme-/', '', $name);
        $handle = 'themsah-widget-' . $slug;
        $css_rel = '/widgets/' . $slug . '/assets/style.css';
        $css_abs = get_template_directory() . $css_rel;
        if ( file_exists( $css_abs ) ) {
            $css_uri = get_template_directory_uri() . $css_rel;
            if ( ! wp_style_is( $handle, 'registered' ) ) {
                wp_register_style( $handle, $css_uri, array(), filemtime( $css_abs ) );
            }
            return array( $handle );
        }
        return array();
    }
}
PHP;
    eval($themsah_widget_base_code);
}
