<?php
if (! defined('ABSPATH')) exit;

class Themsah_Theme_Setup {
    public function __construct() {
        add_action('after_setup_theme', [$this, 'setup']);
        add_action('init', [$this, 'register_menus']);
        add_action('widgets_init', [$this, 'widgets_init']);
    }

    public function setup() {
        load_theme_textdomain('themsah-theme', get_template_directory() . '/languages');
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('automatic-feed-links');
        add_theme_support('html5', array('search-form','comment-form','comment-list','gallery','caption'));
        add_theme_support('custom-logo');
    }

    public function register_menus() {
        register_nav_menus(array(
            'main-menu' => __('منوی اصلی', 'themsah-theme'),
            'footer-menu' => __('منوی فوتر', 'themsah-theme'),
            'sidebar-1' => __('سایدبار پیش‌فرض', 'themsah-theme'),
        ));
    }

    public function widgets_init() {
        register_sidebar(array(
            'name' => __('ابزارک‌های فوتر', 'themsah-theme'),
            'id' => 'footer-widgets',
            'description' => __('ناحیه ابزارک در فوتر', 'themsah-theme'),
            'before_widget' => '<div class="footer-widget">',
            'after_widget' => '</div>',
            'before_title' => '<h4>',
            'after_title' => '</h4>',
        ));
        register_sidebar(array(
            'name' => __('سایدبار نوشته‌ها', 'themsah-theme'),
            'id' => 'sidebar-1',
            'description' => __('سایدبار پیش‌فرض برای صفحات سینگل نوشته', 'themsah-theme'),
            'before_widget' => '<section class="widget">',
            'after_widget' => '</section>',
            'before_title' => '<h4>',
            'after_title' => '</h4>',
        ));
    }
}
