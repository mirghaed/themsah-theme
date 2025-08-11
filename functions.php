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

// Invalidate OPcache for all theme PHP files in admin (prevents stale code during development)
add_action('admin_init', function(){
    if ( ! function_exists('opcache_invalidate') ) return;
    try {
        $dir = new RecursiveDirectoryIterator(THEMSAH_THEME_DIR, FilesystemIterator::SKIP_DOTS);
        foreach ( new RecursiveIteratorIterator($dir) as $file ) {
            $path = (string) $file;
            if ( substr($path, -4) === '.php' ) {
                @opcache_invalidate($path, true);
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
}, 1);

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

// Remove custom canvas routing entirely (revert to Elementor defaults)

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

// Hide our internal canvas page template from Page Attributes to avoid beginners selecting it
add_filter('theme_page_templates', function($templates){
    foreach ($templates as $file => $label) {
        if ( strpos($file, 'themsah-elementor-canvas.php') !== false ) {
            unset($templates[$file]);
        }
    }
    return $templates;
});

// When editing a normal Page with Elementor, if the page template was set to our internal canvas,
// fallback to the standard page template so the_content exists and editor can load
add_filter('page_template', function($template){
    $is_elementor_admin_edit = is_admin() && isset($_GET['action']) && $_GET['action'] === 'elementor';
    if ( ! $is_elementor_admin_edit ) return $template;
    // Resolve selected template file
    $selected = get_page_template_slug(get_queried_object_id());
    if ( $selected && strpos($selected, 'themsah-elementor-canvas.php') !== false ) {
        $fallback = locate_template('page.php');
        if ( $fallback ) return $fallback;
    }
    return $template;
}, 20);
