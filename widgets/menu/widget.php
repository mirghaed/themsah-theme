<?php
if (! defined('ABSPATH')) exit;
use \Elementor\Controls_Manager;

class Themsah_Theme_Widget_Menu extends Themsah_Theme_Elementor_Widget_Base {
    public function get_name() { return 'mytheme-menu'; }
    public function get_title() { return __('منوی سایت', 'themsah-theme'); }
    public function get_icon() { return 'themsah-icon'; }
    public function get_keywords() { return ['menu','navigation']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('محتوا', 'themsah-theme'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $menus = wp_get_nav_menus();
        $choices = array();
        if ( $menus ) {
            foreach ( $menus as $m ) { $choices[ $m->term_id ] = $m->name; }
        }
        $this->add_control('menu_select', [
            'label' => __('انتخاب منو', 'themsah-theme'),
            'type' => Controls_Manager::SELECT,
            'options' => $choices,
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_section', [
            'label' => __('استایل', 'themsah-theme'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);
        $this->add_control('link_color', [
            'label' => __('رنگ لینک', 'themsah-theme'),
            'type'  => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} a' => 'color: {{VALUE}};' ],
        ]);
        $this->add_control('link_hover_color', [
            'label' => __('رنگ هاور لینک', 'themsah-theme'),
            'type'  => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} a:hover' => 'color: {{VALUE}};' ],
        ]);
        $this->add_responsive_control('spacing', [
            'label' => __('فاصله آیتم‌ها', 'themsah-theme'),
            'type' => Controls_Manager::SLIDER,
            'size_units' => ['px','em','rem'],
            'selectors' => [ '{{WRAPPER}} .menu, {{WRAPPER}} ul' => 'gap: {{SIZE}}{{UNIT}}; display:flex; flex-wrap:wrap;' ],
        ]);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $menu = ! empty($settings['menu_select']) ? intval($settings['menu_select']) : '';
        if ( $menu ) { wp_nav_menu(['menu' => $menu, 'container' => false]); }
        else { wp_nav_menu(['theme_location' => 'main-menu', 'container' => false]); }
    }
}


