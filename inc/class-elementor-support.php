<?php
if (! defined('ABSPATH')) exit;

class Themsah_Theme_Elementor_Support {
    public function __construct() {
        // Register widgets and category if Elementor is loaded
        add_action('init', array($this,'maybe_register_hooks'), 20);
        // Ensure registration when Elementor finishes booting
        add_action('elementor/init', array($this,'maybe_register_hooks'), 5);
        // Ensure editor always sees a content region
        add_action('wp', array($this,'maybe_force_content_region'));
        // Ensure content area is always available for Elementor
        add_action('wp', array($this,'ensure_elementor_content_area'));
    }

    public function maybe_register_hooks() {
        if ( class_exists('\Elementor\Plugin') ) {
            add_action('elementor/elements/categories_registered', [$this, 'register_category']);
            // Support both new and legacy Elementor hooks for widget registration
            add_action('elementor/widgets/register', [$this, 'register_widgets']);
            add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets']);
            // Register custom fonts with Elementor (so user can choose in typography controls)
            add_action('elementor/fonts/wordpress', [$this, 'register_custom_fonts']);
        }
    }

    /**
     * Ensure the_content exists while Elementor editor/preview is loading
     */
    public function maybe_force_content_region() {
        if ( is_admin() ) return;
        if ( ! class_exists('\\Elementor\\Plugin') ) return;
        $is_editor = isset($_GET['elementor-preview']) || isset($_GET['elementor-iframe']);
        if ( ! $is_editor ) return;
        add_filter('the_content', function($content){
            if ( trim($content) === '' ) {
                return '<div class="elementor-content-area" style="min-height: 100vh; padding: 20px; text-align: center; color: #666;">
                    <p>محتوای المنتور در حال بارگذاری...</p>
                    <p>Elementor content is loading...</p>
                </div>';
            }
            return $content;
        }, 0);
    }

    /**
     * Ensure Elementor always has access to a content area
     */
    public function ensure_elementor_content_area() {
        if ( is_admin() ) return;
        if ( ! class_exists('\\Elementor\\Plugin') ) return;
        
        // Check if we're in an Elementor context
        $is_elementor_context = isset($_GET['elementor-preview']) || 
                                isset($_GET['elementor-iframe']) || 
                                isset($_GET['preview']) || 
                                isset($_GET['preview_id']);
        
        if ( $is_elementor_context ) {
            // Ensure the_content filter is always available
            add_filter('the_content', function($content) {
                // If content is empty, provide a fallback
                if ( trim($content) === '' ) {
                    return '<div class="elementor-content-area" style="min-height: 100vh; padding: 20px; text-align: center; color: #666;">
                        <p>محتوای المنتور در حال بارگذاری...</p>
                        <p>Elementor content is loading...</p>
                    </div>';
                }
                return $content;
            }, 0);
            
            // Add a hidden content wrapper to ensure the_content is called
            add_action('wp_footer', function() {
                if ( have_posts() ) {
                    echo '<div class="elementor-content-fallback" style="display: none;">';
                    while ( have_posts() ) {
                        the_post();
                        the_content();
                    }
                    rewind_posts();
                    echo '</div>';
                }
            }, 1);
            
            // Add debugging information for development
            if ( defined('WP_DEBUG') && WP_DEBUG ) {
                add_action('wp_footer', function() {
                    echo '<!-- Elementor Content Area Debug: Context detected, fallbacks enabled -->';
                }, 999);
            }
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

    public function register_widgets($widgets_manager = null) {
        // Fallback for legacy hook that passes no parameter
        if ( ! $widgets_manager && class_exists('\\Elementor\\Plugin') ) {
            try { $widgets_manager = \Elementor\Plugin::instance()->widgets_manager; } catch ( \Throwable $e ) { $widgets_manager = null; }
        }
        if ( ! $widgets_manager ) { return; }

        $widgets_dir = get_template_directory() . '/widgets';
        // Ensure our base class is loaded now that Elementor core is fully loaded
        $base_file = trailingslashit($widgets_dir) . 'class-elementor-widget-base.php';
        if ( file_exists($base_file) ) {
            require_once $base_file;
        }
        // 1) Legacy flat files: widgets/widget-*.php
        foreach ( glob( $widgets_dir . '/*.php' ) as $file ) {
            if ( basename($file) === 'class-elementor-widget-base.php' ) { continue; }
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
        // Use unified accessor which supports both new and legacy option names
        $opts = Themsah_Theme_Options::get_all_options();
        if ( ! is_array($opts) ) return;

        // New schema: multiple families
        if ( ! empty($opts['custom_fonts_list']) && is_array($opts['custom_fonts_list']) ) {
            foreach ( $opts['custom_fonts_list'] as $fam ) {
                if ( empty($fam['family']) ) continue;
                $fonts_manager->add_font( $fam['family'], 'custom' );
            }
            return;
        }

        // Legacy single-family
        if ( ! empty($opts['custom_fonts']['family']) ) {
            $fonts_manager->add_font( $opts['custom_fonts']['family'], 'custom' );
            return;
        }
    }
}
