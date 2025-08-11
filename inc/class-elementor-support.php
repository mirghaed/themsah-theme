<?php
if (! defined('ABSPATH')) exit;

class Themsah_Theme_Elementor_Support {
    public function __construct() {
        // Register widgets and category if Elementor is loaded
        add_action('init', array($this,'maybe_register_hooks'), 20);
    }

    public function maybe_register_hooks() {
        if ( class_exists('\Elementor\Plugin') ) {
            add_action('elementor/elements/categories_registered', [$this, 'register_category']);
            add_action('elementor/widgets/register', [$this, 'register_widgets']);
            // Register custom fonts with Elementor (so user can choose in typography controls)
            add_action('elementor/fonts/wordpress', [$this, 'register_custom_fonts']);
        }
    }

    public function register_category($elements_manager) {
        $elements_manager->add_category(
            'mytheme-widgets',
            [
                'title' => __('ویجت‌های قالب تمساح', 'themsah-theme'),
                'icon' => 'fa fa-plug',
            ]
        );
    }

    public function register_widgets($widgets_manager) {
        $widgets_dir = get_template_directory() . '/widgets';
        // 1) Legacy flat files: widgets/widget-*.php
        foreach ( glob( $widgets_dir . '/*.php' ) as $file ) {
            require_once $file;
            $base = basename($file, '.php');
            $slug = preg_replace('/^widget-/', '', $base);
            $parts = explode('-', $slug);
            $class = 'Themsah_Theme_Widget_' . implode('_', array_map('ucfirst', $parts));
            if ( class_exists( $class ) ) {
                try {
                    $widgets_manager->register( new $class() );
                } catch ( Exception $e ) {
                    // ignore
                }
            }
        }

        // 2) New structured folders: widgets/<slug>/widget.php
        foreach ( glob( $widgets_dir . '/*', GLOB_ONLYDIR ) as $dir ) {
            $slug = basename($dir);
            $file = trailingslashit($dir) . 'widget.php';
            if ( file_exists($file) ) {
                require_once $file;
                $parts = explode('-', $slug);
                $class = 'Themsah_Theme_Widget_' . implode('_', array_map('ucfirst', $parts));
                if ( class_exists( $class ) ) {
                    try {
                        $widgets_manager->register( new $class() );
                    } catch ( Exception $e ) {
                        // ignore
                    }
                }
            }
        }
    }

    public static function get_elementor_templates() {
        $templates = get_posts(array(
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
            'post_status' => array('publish','draft','private'),
            'orderby' => 'title',
            'order' => 'ASC',
            'suppress_filters' => false,
            'no_found_rows' => true,
        ));
        $list = array();
        if ( $templates ) {
            foreach ( $templates as $t ) {
                $list[ $t->ID ] = $t->post_title;
            }
        }
        return $list;
    }

    public static function render_template( $template_id ) {
        if ( ! $template_id ) return false;
        if ( ! class_exists('\\Elementor\\Plugin') ) return false;

        // Avoid recursion when editing elementor_library items
        if ( is_singular('elementor_library') ) {
            return false;
        }

        try {
            echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
            return true;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Expose custom fonts (uploaded in theme options) to Elementor font list
     */
    public function register_custom_fonts( $fonts_manager ) {
        $opts = get_option('mytheme_options', array());
        if ( empty($opts['custom_fonts']) || ! is_array($opts['custom_fonts']) ) return;
        // New schema: ['family' => 'Name', 'weights' => [ {weight, woff2, woff, ttf}, ... ]]
        if ( ! empty($opts['custom_fonts']['family']) ) {
            $fonts_manager->add_font( $opts['custom_fonts']['family'], 'custom' );
            return;
        }
        // Backward compatibility: array of rows with 'name'
        $families = array();
        foreach ( $opts['custom_fonts'] as $font ) {
            if ( empty($font['name']) ) continue;
            $families[] = $font['name'];
        }
        $families = array_unique($families);
        foreach ( $families as $family ) {
            $fonts_manager->add_font( $family, 'custom' );
        }
    }
}
