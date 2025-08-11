<?php
if (! defined('ABSPATH')) exit;

class Themsah_Theme_Widget_Logo extends Themsah_Theme_Elementor_Widget_Base {
    public function get_name() { return 'mytheme-logo'; }
    public function get_title() { return __('لوگو سایت', 'themsah-theme'); }
    public function get_icon() { return 'themsah-icon'; }
    public function get_keywords() { return ['logo','brand','site']; }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('محتوا', 'themsah-theme'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);
        $this->add_control('type', [
            'label' => __('نوع لوگو', 'themsah-theme'),
            'type'  => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'image' => ['title' => __('تصویر','themsah-theme'),'icon'=>'eicon-image'],
                'text'  => ['title' => __('متن','themsah-theme'),'icon'=>'eicon-editor-bold'],
            ],
            'default' => 'image',
            'toggle' => false,
        ]);
        $this->add_control('image', [
            'label' => __('تصویر لوگو', 'themsah-theme'),
            'type'  => \Elementor\Controls_Manager::MEDIA,
            'condition' => ['type' => 'image'],
        ]);
        $this->add_control('fallback_text', [
            'label' => __('متن لوگو', 'themsah-theme'),
            'type'  => \Elementor\Controls_Manager::TEXT,
            'default' => get_bloginfo('name'),
            'condition' => ['type' => 'text'],
        ]);
        $this->end_controls_section();

        $this->start_controls_section('style_section', [
            'label' => __('استایل', 'themsah-theme'),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ]);
        $this->add_responsive_control('align', [
            'label' => __('چیدمان', 'themsah-theme'),
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'left' => [ 'title' => __('چپ','themsah-theme'), 'icon' => 'eicon-h-align-left' ],
                'center' => [ 'title' => __('وسط','themsah-theme'), 'icon' => 'eicon-h-align-center' ],
                'right' => [ 'title' => __('راست','themsah-theme'), 'icon' => 'eicon-h-align-right' ],
            ],
            'selectors' => [ '{{WRAPPER}}' => 'text-align: {{VALUE}};' ],
            'default' => 'left',
        ]);
        $this->add_responsive_control('img_width', [
            'label' => __('عرض تصویر', 'themsah-theme'),
            'type'  => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px','%','vw'],
            'selectors' => [ '{{WRAPPER}} img' => 'width: {{SIZE}}{{UNIT}};' ],
            'condition' => ['type' => 'image'],
        ]);
        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $type = ! empty($s['type']) ? $s['type'] : 'image';
        $home = esc_url(home_url('/'));
        if ( $type === 'image' ) {
            $src = '';
            if ( ! empty($s['image']['url']) ) {
                $src = esc_url($s['image']['url']);
            } elseif ( function_exists('the_custom_logo') && has_custom_logo() ) {
                the_custom_logo();
                return;
            }
            if ( $src ) {
                echo '<a href="'. $home .'"><img src="'. $src .'" alt="'. esc_attr(get_bloginfo('name')) .'" /></a>';
                return;
            }
        }
        $text = ! empty($s['fallback_text']) ? $s['fallback_text'] : get_bloginfo('name');
        printf('<a href="%1$s">%2$s</a>', $home, esc_html($text));
    }
}


