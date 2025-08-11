<?php
if (! defined('ABSPATH')) exit;

class Themsah_Theme_Template_Loader {
    public function __construct() {}

    public static function get_post_header_template_id( $post_id = 0 ) {
        if ( ! $post_id ) {
            $post_id = get_queried_object_id();
        }
        if ( $post_id ) {
            $meta = get_post_meta( $post_id, '_mytheme_header_template', true );
            if ( $meta ) return $meta;
        }
        $opts = class_exists('Themsah_Theme_Options') ? Themsah_Theme_Options::get_all_options() : array();
        if ( ! empty($opts['header_template']) ) return absint($opts['header_template']);
        return false;
    }

    public static function get_post_footer_template_id( $post_id = 0 ) {
        if ( ! $post_id ) {
            $post_id = get_queried_object_id();
        }
        if ( $post_id ) {
            $meta = get_post_meta( $post_id, '_mytheme_footer_template', true );
            if ( $meta ) return $meta;
        }
        $opts = class_exists('Themsah_Theme_Options') ? Themsah_Theme_Options::get_all_options() : array();
        if ( ! empty($opts['footer_template']) ) return absint($opts['footer_template']);
        return false;
    }

    public static function get_archive_template_id( $post_type = null ) {
        if ( ! $post_type ) {
            $obj = get_queried_object();
            $post_type = isset($obj->name) ? $obj->name : 'post';
        }
        $opts = class_exists('Themsah_Theme_Options') ? Themsah_Theme_Options::get_all_options() : array();
        if ( ! empty($opts['archive_templates']) && ! empty($opts['archive_templates'][$post_type]) ) {
            return absint($opts['archive_templates'][$post_type]);
        }
        return false;
    }

    public static function get_single_template_id( $post_type = null ) {
        if ( ! $post_type ) {
            $post_type = get_post_type() ?: 'post';
        }
        $opts = class_exists('Themsah_Theme_Options') ? Themsah_Theme_Options::get_all_options() : array();
        if ( ! empty($opts['single_templates']) && ! empty($opts['single_templates'][$post_type]) ) {
            return absint($opts['single_templates'][$post_type]);
        }
        return false;
    }

    public static function get_single_sidebar_position() {
        $opts = class_exists('Themsah_Theme_Options') ? Themsah_Theme_Options::get_all_options() : array();
        return isset($opts['single_sidebar_position']) ? $opts['single_sidebar_position'] : 'right';
    }

    public static function get_single_sidebar_elementor_id() {
        $opts = class_exists('Themsah_Theme_Options') ? Themsah_Theme_Options::get_all_options() : array();
        return ! empty($opts['single_sidebar_elementor']) ? absint($opts['single_sidebar_elementor']) : 0;
    }

    public static function render_header() {
        $template_id = self::get_post_header_template_id();
        if ( $template_id && Themsah_Theme_Elementor_Support::render_template( $template_id ) ) {
            return;
        }
        get_template_part('templates/default', 'header');
    }

    public static function render_footer() {
        $template_id = self::get_post_footer_template_id();
        if ( $template_id && Themsah_Theme_Elementor_Support::render_template( $template_id ) ) {
            return;
        }
        get_template_part('templates/default', 'footer');
    }

    public static function maybe_render_archive_with_elementor() {
        $pt = is_post_type_archive() ? get_query_var('post_type') : 'post';
        if ( is_array($pt) ) { $pt = reset($pt); }
        $template_id = self::get_archive_template_id( $pt );
        if ( $template_id && Themsah_Theme_Elementor_Support::render_template( $template_id ) ) {
            // Ensure the_content is available for Elementor
            add_filter('the_content', function($content) {
                // If content is empty, provide a fallback content area for Elementor
                if ( trim($content) === '' ) {
                    return '<div class="elementor-content-area" style="min-height: 100vh;"></div>';
                }
                return $content;
            }, 0);
            
            // Add a filter to ensure the_content is always called
            add_action('wp_footer', function() {
                if ( have_posts() ) {
                    while ( have_posts() ) {
                        the_post();
                        // This ensures Elementor can find the content area
                        echo '<div class="elementor-content-wrapper" style="display: none;">';
                        the_content();
                        echo '</div>';
                    }
                    rewind_posts();
                }
            }, 1);
            
            // Force the_content to be available in the main loop
            add_action('wp', function() {
                if ( have_posts() ) {
                    while ( have_posts() ) {
                        the_post();
                        // Ensure the_content is called at least once
                        $content = get_the_content();
                        if ( empty($content) ) {
                            // Add a fallback content area
                            echo '<div class="elementor-content-area" style="min-height: 100vh; padding: 20px; text-align: center; color: #666;">
                                <p>محتوای المنتور در حال بارگذاری...</p>
                                <p>Elementor content is loading...</p>
                            </div>';
                        }
                    }
                    rewind_posts();
                }
            }, 1);
            
            // Ensure the main content area is available for Elementor
            add_action('wp', function() {
                echo '<div class="elementor-main-content-area" style="min-height: 100vh;">';
                // This ensures Elementor can find the main content area
                if ( have_posts() ) {
                    while ( have_posts() ) {
                        the_post();
                        the_content();
                    }
                    rewind_posts();
                }
                echo '</div>';
            }, 1);
            
            return true;
        }
        return false;
    }

    public static function maybe_render_single_with_elementor() {
        $pt = get_post_type() ?: 'post';
        $template_id = self::get_single_template_id( $pt );
        if ( $template_id && Themsah_Theme_Elementor_Support::render_template( $template_id ) ) {
            // Ensure the_content is available for Elementor
            add_filter('the_content', function($content) {
                // If content is empty, provide a fallback content area for Elementor
                if ( trim($content) === '' ) {
                    return '<div class="elementor-content-area" style="min-height: 100vh;"></div>';
                }
                return $content;
            }, 0);
            
            // Add a filter to ensure the_content is always called
            add_action('wp_footer', function() {
                if ( have_posts() ) {
                    while ( have_posts() ) {
                        the_post();
                        // This ensures Elementor can find the content area
                        echo '<div class="elementor-content-wrapper" style="display: none;">';
                        the_content();
                        echo '</div>';
                    }
                    rewind_posts();
                }
            }, 1);
            
            // Force the_content to be available in the main loop
            add_action('wp', function() {
                if ( have_posts() ) {
                    while ( have_posts() ) {
                        the_post();
                        // Ensure the_content is called at least once
                        $content = get_the_content();
                        if ( empty($content) ) {
                            // Add a fallback content area
                            echo '<div class="elementor-content-area" style="min-height: 100vh; padding: 20px; text-align: center; color: #666;">
                                <p>محتوای المنتور در حال بارگذاری...</p>
                                <p>Elementor content is loading...</p>
                            </div>';
                        }
                    }
                    rewind_posts();
                }
            }, 1);
            
            // Ensure the main content area is available for Elementor
            add_action('wp', function() {
                echo '<div class="elementor-main-content-area" style="min-height: 100vh;">';
                // This ensures Elementor can find the main content area
                if ( have_posts() ) {
                    while ( have_posts() ) {
                        the_post();
                        the_content();
                    }
                    rewind_posts();
                }
                echo '</div>';
            }, 1);
            
            return true;
        }
        return false;
    }

    /**
     * Detect if current request is Elementor editor/preview context
     */
    public static function is_elementor_preview_context() {
        if ( isset($_GET['elementor-preview']) || isset($_GET['elementor-iframe']) ) {
            return true;
        }
        if ( isset($_GET['preview']) || isset($_GET['preview_id']) || isset($_GET['elementor_library']) ) {
            return true;
        }
        if ( function_exists('is_singular') && is_singular('elementor_library') ) {
            return true;
        }
        if ( class_exists('Elementor\\Plugin') ) {
            try {
                if ( \Elementor\Plugin::instance()->editor && \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
                    return true;
                }
            } catch ( \Throwable $e ) {}
        }
        return false;
    }
}

/**
 * Force Elementor Library previews to use a blank canvas template
 */
add_filter('template_include', function($template){
    $blank = get_template_directory() . '/templates/blank-canvas.php';

    // 1) Front-end preview while editing (Elementor loads an iframe): elementor-preview / elementor-iframe
    if ( isset($_GET['elementor-preview']) || isset($_GET['elementor-iframe']) ) {
        if ( file_exists($blank) ) {
            return $blank;
        }
    }

    // 1b) WP preview route used by Elementor for saved templates
    if (
        isset($_GET['preview']) || isset($_GET['preview_id']) || isset($_GET['elementor_library']) || isset($_GET['p']) || isset($_GET['ver']) ||
        ( isset($_GET['post_type']) && $_GET['post_type'] === 'elementor_library' )
    ) {
        if ( file_exists($blank) ) {
            return $blank;
        }
    }

    // 2) Explicit editor mode (extra guard)
    if ( class_exists('Elementor\\Plugin') ) {
        try {
            if ( \Elementor\Plugin::instance()->editor && \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
                // Determine the edited document ID
                $doc_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
                if ( ! $doc_id && isset($_GET['elementor-preview']) ) {
                    $doc_id = absint($_GET['elementor-preview']);
                }
                if ( $doc_id ) {
                    // Prefer a dedicated page template if exists
                    $pages = get_pages(array(
                        'meta_key' => '_wp_page_template',
                        'meta_value' => 'page-templates/themsah-elementor-canvas.php',
                        'number' => 1,
                    ));
                    if ( ! empty($pages) ) {
                        $url = get_permalink($pages[0]->ID);
                        if ( $url ) {
                            wp_safe_redirect( add_query_arg('elementor-preview', $doc_id, $url) );
                            exit;
                        }
                    }
                    if ( file_exists($blank) ) {
                        return $blank;
                    }
                }
            }
        } catch ( \Throwable $e ) {
            // fail silent
        }
    }

    // 3) Normal single view of library items -> let single-elementor_library.php handle it
    return $template;
}, 999);

// Extra guards to force blank template for elementor_library
add_filter('single_template', function($single){
    if ( is_singular('elementor_library') ) {
        $blank = get_template_directory() . '/templates/blank-canvas.php';
        if ( file_exists($blank) ) return $blank;
    }
    return $single;
}, 999);

add_action('template_redirect', function(){
    if ( isset($_GET['elementor-preview']) || isset($_GET['elementor-iframe']) ) {
        $blank = get_template_directory() . '/templates/blank-canvas.php';
        if ( file_exists($blank) ) {
            status_header(200);
            load_template($blank, true);
            exit;
        }
    }
    if (
        isset($_GET['preview']) || isset($_GET['preview_id']) || isset($_GET['elementor_library']) || isset($_GET['p']) || isset($_GET['ver']) ||
        ( isset($_GET['post_type']) && $_GET['post_type'] === 'elementor_library' )
    ) {
        $blank = get_template_directory() . '/templates/blank-canvas.php';
        if ( file_exists($blank) ) {
            status_header(200);
            load_template($blank, true);
            exit;
        }
    }
});

// Force Elementor editor to use a blank-canvas preview URL for saved templates
if ( function_exists('add_filter') ) {
    // Editor preview iframe URL
    add_filter('elementor/document/urls/preview', function($url, $document){
        try {
            $doc_id = is_object($document) && method_exists($document, 'get_main_id') ? intval($document->get_main_id()) : 0;
            if ( $doc_id && get_post_type($doc_id) === 'elementor_library' ) {
                // Prefer a dedicated canvas page if exists
                $pages = get_pages(array(
                    'meta_key' => '_wp_page_template',
                    'meta_value' => 'page-templates/themsah-elementor-canvas.php',
                    'number' => 1,
                ));
                if ( ! empty($pages) ) {
                    $url = add_query_arg('elementor-preview', $doc_id, get_permalink($pages[0]->ID));
                } else {
                    $url = add_query_arg(array(
                        'elementor-preview' => $doc_id,
                        'themsah-canvas'    => '1',
                    ), home_url('/'));
                }
            }
        } catch ( \Throwable $e ) {}
        return $url;
    }, 10, 2);

    // WP core preview link used by Elementor
    add_filter('elementor/document/urls/wp_preview', function($url, $post_id){
        if ( $post_id && get_post_type($post_id) === 'elementor_library' ) {
            $pages = get_pages(array(
                'meta_key' => '_wp_page_template',
                'meta_value' => 'page-templates/themsah-elementor-canvas.php',
                'number' => 1,
            ));
            if ( ! empty($pages) ) {
                $url = add_query_arg('elementor-preview', intval($post_id), get_permalink($pages[0]->ID));
            } else {
                $url = add_query_arg(array(
                    'elementor-preview' => intval($post_id),
                    'themsah-canvas'    => '1',
                ), home_url('/'));
            }
        }
        return $url;
    }, 10, 2);
}

if ( ! class_exists('Themsah_Theme_Sidebar_Renderer') ) {
    class Themsah_Theme_Sidebar_Renderer {
        public static function render_single_sidebar() {
            $tpl_id = Themsah_Theme_Template_Loader::get_single_sidebar_elementor_id();
            if ( $tpl_id && Themsah_Theme_Elementor_Support::render_template($tpl_id) ) {
                return;
            }
            if ( is_active_sidebar('sidebar-1') ) {
                dynamic_sidebar('sidebar-1');
            } else {
                echo '<div class="widget"><p>'. esc_html__('در اینجا ابزارکی اضافه کنید (نمایش > ابزارک‌ها)', 'themsah-theme') .'</p></div>';
            }
        }
    }
}
