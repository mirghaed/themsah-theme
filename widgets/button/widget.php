<?php
if (! defined('ABSPATH')) exit;
use \Elementor\Controls_Manager;

class Themsah_Theme_Widget_Button extends Themsah_Theme_Elementor_Widget_Base {
    public function get_name() { return 'mytheme-button'; }
    public function get_title() { return __('دکمه سفارشی', 'themsah-theme'); }
    public function get_icon() { return 'themsah-icon'; }
    public function get_keywords() { return ['button','cta']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('محتوا', 'themsah-theme'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control('btn_text', [
            'label' => __('متن دکمه', 'themsah-theme'),
            'type' => Controls_Manager::TEXT,
            'default' => 'کلیک کنید',
        ]);

        $this->add_control('btn_link', [
            'label' => __('لینک دکمه', 'themsah-theme'),
            'type' => Controls_Manager::URL,
            'placeholder' => 'https://',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_section', [
            'label' => __('استایل', 'themsah-theme'),
            'tab'   => Controls_Manager::TAB_STYLE,
        ]);
        $this->add_control('text_color', [
            'label' => __('رنگ متن', 'themsah-theme'),
            'type'  => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .mytheme-btn' => 'color: {{VALUE}};' ],
        ]);
        $this->add_control('bg_color', [
            'label' => __('رنگ پس‌زمینه', 'themsah-theme'),
            'type'  => Controls_Manager::COLOR,
            'selectors' => [ '{{WRAPPER}} .mytheme-btn' => 'background-color: {{VALUE}};' ],
        ]);
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name' => 'typography',
            'selector' => '{{WRAPPER}} .mytheme-btn',
        ]);
        $this->add_group_control( \Elementor\Group_Control_Border::get_type(), [
            'name' => 'border',
            'selector' => '{{WRAPPER}} .mytheme-btn',
        ]);
        $this->add_responsive_control('padding', [
            'label' => __('پدینگ', 'themsah-theme'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px','em','rem'],
            'selectors' => [ '{{WRAPPER}} .mytheme-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
        ]);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $url = '#';
        if ( ! empty($settings['btn_link']['url']) ) {
            $url = esc_url($settings['btn_link']['url']);
        }
        printf('<a class="mytheme-btn" href="%1$s">%2$s</a>', $url, esc_html($settings['btn_text']));
    }
}


