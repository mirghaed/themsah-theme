<?php
if (! defined('ABSPATH')) {
    exit;
}


define('THEMSAH_THEME_DIR', get_template_directory());
define('THEMSAH_THEME_URI', get_template_directory_uri());

// Core classes
require_once THEMSAH_THEME_DIR . '/inc/class-theme-setup.php';
require_once THEMSAH_THEME_DIR . '/inc/class-assets.php';
require_once THEMSAH_THEME_DIR . '/inc/class-elementor-support.php';
require_once THEMSAH_THEME_DIR . '/inc/class-template-loader.php';
require_once THEMSAH_THEME_DIR . '/inc/class-theme-options.php';
require_once THEMSAH_THEME_DIR . '/inc/class-meta-boxes.php';

// Instantiate
new Themsah_Theme_Setup();
new Themsah_Theme_Assets();
new Themsah_Theme_Elementor_Support();
new Themsah_Theme_Template_Loader();
new Themsah_Theme_Options();
new Themsah_Theme_Meta_Boxes();

// Admin notice for Elementor if missing
add_action('admin_notices', function(){
    if ( ! current_user_can('install_plugins') ) return;
    if ( class_exists('\Elementor\Plugin') ) return;

    $plugin_slug = 'elementor/elementor.php';
    if ( file_exists(WP_PLUGIN_DIR . '/elementor/elementor.php') ) {
        $activate_url = wp_nonce_url( admin_url('plugins.php?action=activate&plugin=' . urlencode($plugin_slug)), 'activate-plugin_' . $plugin_slug );
        $cta = '<a class="button button-primary" href="'. esc_url($activate_url) .'">'. esc_html__('فعال‌سازی المنتور', 'mytheme') .'</a>';
    } else {
        $install_url = wp_nonce_url( admin_url('update.php?action=install-plugin&plugin=elementor'), 'install-plugin_elementor' );
        $cta = '<a class="button button-primary" href="'. esc_url($install_url) .'">'. esc_html__('نصب المنتور', 'mytheme') .'</a>';
    }

    echo '<div class="notice notice-warning"><p>'. esc_html__('قالب تمساح برای عملکرد کامل به افزونه Elementor نیاز دارد.', 'themsah-theme') .' '. $cta .'</p></div>';
});

// Force blank canvas for Elementor library previews as early as possible
if ( ! function_exists('themsah_is_elementor_preview_request') ) {
    function themsah_is_elementor_preview_request(): bool {
        return (
            isset($_GET['elementor-preview']) || isset($_GET['elementor-iframe']) ||
            isset($_GET['preview']) || isset($_GET['preview_id']) || isset($_GET['p']) || isset($_GET['elementor_library']) || isset($_GET['ver']) ||
            ( isset($_GET['post_type']) && $_GET['post_type'] === 'elementor_library' )
        );
    }
}

if ( ! function_exists('themsah_maybe_load_canvas_and_exit') ) {
    function themsah_maybe_load_canvas_and_exit() {
        if ( themsah_is_elementor_preview_request() ) {
            $blank = get_template_directory() . '/templates/blank-canvas.php';
            if ( file_exists($blank) ) {
                status_header(200);
                load_template($blank, true);
                exit;
            }
        }
    }
}

// Super-early guards
add_action('parse_request', function(){ themsah_maybe_load_canvas_and_exit(); }, 0);
add_action('wp', function(){ themsah_maybe_load_canvas_and_exit(); }, 0);
add_action('template_redirect', function(){ themsah_maybe_load_canvas_and_exit(); }, 0);
add_filter('redirect_canonical', function($redirect_url){
    if (
        isset($_GET['elementor-preview']) || isset($_GET['elementor-iframe']) ||
        isset($_GET['preview']) || isset($_GET['preview_id']) || isset($_GET['elementor_library']) || isset($_GET['p']) || isset($_GET['ver']) ||
        ( isset($_GET['post_type']) && $_GET['post_type'] === 'elementor_library' )
    ) {
        return false; // prevent canonical redirects from dropping our params
    }
    return $redirect_url;
}, 1, 1);

add_filter('template_include', function($template){
    $blank = get_template_directory() . '/templates/blank-canvas.php';
    if ( file_exists($blank) && themsah_is_elementor_preview_request() ) {
        return $blank;
    }
    return $template;
}, 1);

// Add Theme Settings to Admin Bar
add_action('admin_bar_menu', function($wp_admin_bar){
    if ( ! current_user_can('manage_options') ) return;
    // Add a top-level node near the left
    $wp_admin_bar->add_node(array(
        'id' => 'themsah-theme-settings',
        'title' => __('تنظیمات قالب تمساح', 'themsah-theme'),
        'href' => admin_url('themes.php?page=themsah-settings'),
        'meta' => array('class' => 'themsah-theme-settings')
    ));
}, 200);
