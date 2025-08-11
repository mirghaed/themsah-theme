<?php
if (! defined('ABSPATH')) exit;

class Themsah_Theme_Options {
    private $option_name = 'themsah_theme_options';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
        add_action('wp_ajax_themsah_save_options', array($this, 'ajax_save'));
    }

    public function add_admin_menu() {
        add_theme_page(
            __('تنظیمات قالب تمساح', 'themsah-theme'),
            __('تنظیمات قالب', 'themsah-theme'),
            'manage_options',
            'themsah-settings',
            array($this, 'settings_page')
        );
    }

    public function register_settings() {
        register_setting('themsah_theme_options_group', $this->option_name, array($this, 'sanitize'));
    }

    public function admin_assets( $hook ) {
        // Load assets on our settings page; be tolerant of different hooks
        if ( strpos($hook, 'themsah-settings') === false ) return;
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_media();
        wp_enqueue_script('themsah-admin-options', get_template_directory_uri() . '/assets/js/admin-options.js', array('jquery','wp-color-picker'), '1.3.0', true);
        wp_enqueue_style('themsah-admin-options', get_template_directory_uri() . '/assets/css/admin-options.css', array(), '1.3.0');
        wp_localize_script('themsah-admin-options', 'THEMSAH_OPTIONS', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('themsah_options_save'),
            'i18n'     => array(
                'saving' => __('در حال ذخیره...', 'themsah-theme'),
                'saved'  => __('تنظیمات با موفقیت ذخیره شد.', 'themsah-theme'),
                'error'  => __('در ذخیره تنظیمات خطایی رخ داد.', 'themsah-theme'),
            ),
        ));
    }

    public function sanitize($input) {
        $output = array();
        $output['primary_color'] = isset($input['primary_color']) ? sanitize_hex_color($input['primary_color']) : '#2663ff';
        $output['hover_color'] = isset($input['hover_color']) ? sanitize_hex_color($input['hover_color']) : '#1f49c9';
        $output['text_color'] = isset($input['text_color']) ? sanitize_hex_color($input['text_color']) : '#222222';
        $output['text_hover_color'] = isset($input['text_hover_color']) ? sanitize_hex_color($input['text_hover_color']) : '#111111';
        $output['footer_text'] = isset($input['footer_text']) ? wp_kses_post($input['footer_text']) : '';
        $output['header_button_text'] = isset($input['header_button_text']) ? sanitize_text_field($input['header_button_text']) : '';
        $output['header_button_link'] = isset($input['header_button_link']) ? esc_url_raw($input['header_button_link']) : '';
        $output['header_menu'] = isset($input['header_menu']) ? absint($input['header_menu']) : '';
        $output['header_template'] = isset($input['header_template']) ? absint($input['header_template']) : '';
        $output['footer_template'] = isset($input['footer_template']) ? absint($input['footer_template']) : '';
        $output['header_logo_image'] = isset($input['header_logo_image']) ? esc_url_raw($input['header_logo_image']) : '';
        // Single post layout & sidebar
        $output['single_sidebar_position'] = isset($input['single_sidebar_position']) ? sanitize_text_field($input['single_sidebar_position']) : 'right';
        $output['single_sidebar_elementor'] = isset($input['single_sidebar_elementor']) ? absint($input['single_sidebar_elementor']) : '';
        // Archive/Single per post type mapping
        $output['archive_templates'] = array();
        $output['single_templates'] = array();
        if ( isset($input['archive_templates']) && is_array($input['archive_templates']) ) {
            foreach ( $input['archive_templates'] as $pt => $tid ) {
                $output['archive_templates'][sanitize_key($pt)] = absint($tid);
            }
        }
        if ( isset($input['single_templates']) && is_array($input['single_templates']) ) {
            foreach ( $input['single_templates'] as $pt => $tid ) {
                $output['single_templates'][sanitize_key($pt)] = absint($tid);
            }
        }
        // Legacy single-family custom font (kept for backward compatibility)
        $output['custom_fonts'] = array();
        $legacy_family = isset($input['custom_fonts']['family']) ? sanitize_text_field($input['custom_fonts']['family']) : '';
        $output['custom_fonts']['family'] = $legacy_family;
        $output['custom_fonts']['weights'] = array();
        if ( isset($input['custom_fonts']['weights']) && is_array($input['custom_fonts']['weights']) ) {
            foreach ( $input['custom_fonts']['weights'] as $row ) {
                $weight = isset($row['weight']) ? intval($row['weight']) : 400;
                $woff2 = isset($row['woff2']) ? esc_url_raw($row['woff2']) : '';
                $woff  = isset($row['woff']) ? esc_url_raw($row['woff']) : '';
                $ttf   = isset($row['ttf']) ? esc_url_raw($row['ttf']) : '';
                if ( $weight && ($woff2 || $woff || $ttf) ) {
                    $output['custom_fonts']['weights'][] = compact('weight','woff2','woff','ttf');
                }
            }
        }

        // New: Multiple font families with support for variable fonts
        $output['custom_fonts_list'] = array();
        if ( isset($input['custom_fonts_list']) && is_array($input['custom_fonts_list']) ) {
            foreach ( $input['custom_fonts_list'] as $family_row ) {
                $family_name = isset($family_row['family']) ? sanitize_text_field($family_row['family']) : '';
                if ( $family_name === '' ) continue;
                $type = isset($family_row['type']) && $family_row['type'] === 'variable' ? 'variable' : 'static';
                $clean = array(
                    'family' => $family_name,
                    'type'   => $type,
                );
                if ( $type === 'variable' ) {
                    $min = isset($family_row['min']) ? max(100, min(900, intval($family_row['min']))) : 100;
                    $max = isset($family_row['max']) ? max($min, min(900, intval($family_row['max']))) : 900;
                    $vw2 = isset($family_row['woff2']) ? esc_url_raw($family_row['woff2']) : '';
                    $vw  = isset($family_row['woff']) ? esc_url_raw($family_row['woff']) : '';
                    if ( $vw2 || $vw ) {
                        $clean['min']  = $min;
                        $clean['max']  = $max;
                        $clean['woff2'] = $vw2;
                        $clean['woff']  = $vw;
                        $output['custom_fonts_list'][] = $clean;
                    }
                } else {
                    $weights_clean = array();
                    if ( isset($family_row['weights']) && is_array($family_row['weights']) ) {
                        foreach ( $family_row['weights'] as $wrow ) {
                            $weight = isset($wrow['weight']) ? intval($wrow['weight']) : 400;
                            $woff2 = isset($wrow['woff2']) ? esc_url_raw($wrow['woff2']) : '';
                            $woff  = isset($wrow['woff']) ? esc_url_raw($wrow['woff']) : '';
                            if ( $weight && ($woff2 || $woff) ) {
                                $weights_clean[] = array(
                                    'weight' => $weight,
                                    'woff2'  => $woff2,
                                    'woff'   => $woff,
                                );
                            }
                        }
                    }
                    if ( ! empty($weights_clean) ) {
                        $clean['weights'] = $weights_clean;
                        $output['custom_fonts_list'][] = $clean;
                    }
                }
            }
        }
        // If new list is empty but legacy single-family exists, try to migrate into list silently
        if ( empty($output['custom_fonts_list']) && ! empty($output['custom_fonts']['family']) && ! empty($output['custom_fonts']['weights']) ) {
            $migrated_weights = array();
            foreach ( $output['custom_fonts']['weights'] as $wrow ) {
                $migrated_weights[] = array(
                    'weight' => intval($wrow['weight']),
                    'woff2'  => isset($wrow['woff2']) ? $wrow['woff2'] : '',
                    'woff'   => isset($wrow['woff']) ? $wrow['woff'] : '',
                );
            }
            if ( ! empty($migrated_weights) ) {
                $output['custom_fonts_list'][] = array(
                    'family'  => $output['custom_fonts']['family'],
                    'type'    => 'static',
                    'weights' => $migrated_weights,
                );
            }
        }
        return $output;
    }

    public static function get_option( $key, $default = '' ) {
        $opts = self::get_all_options();
        if ( isset($opts[$key]) ) return $opts[$key];
        return $default;
    }

    public function settings_page() {
        if ( ! current_user_can('manage_options') ) return;
        $opts = get_option($this->option_name, array(
            'primary_color' => '#2663ff',
            'hover_color' => '#1f49c9',
            'text_color' => '#222222',
            'text_hover_color' => '#111111',
            'footer_text' => 'تمامی حقوق برای این سایت محفوظ است.',
            'header_button_text' => 'تماس با ما',
            'header_button_link' => '#',
            'header_menu' => '',
            'header_template' => '',
            'footer_template' => '',
            'archive_templates' => array(),
            'single_templates' => array(),
            'custom_fonts' => array('family' => '', 'weights' => array()),
            'custom_fonts_list' => array(),
            'single_sidebar_position' => 'right',
            'single_sidebar_elementor' => '',
        ));
        // Guards for legacy saved options
        if ( empty($opts['custom_fonts']) || ! is_array($opts['custom_fonts']) ) {
            $opts['custom_fonts'] = array('family' => '', 'weights' => array());
        } else {
            if ( ! isset($opts['custom_fonts']['family']) ) $opts['custom_fonts']['family'] = '';
            if ( ! isset($opts['custom_fonts']['weights']) || ! is_array($opts['custom_fonts']['weights']) ) $opts['custom_fonts']['weights'] = array();
        }
        if ( ! isset($opts['custom_fonts_list']) || ! is_array($opts['custom_fonts_list']) ) {
            $opts['custom_fonts_list'] = array();
        }
        if ( ! isset($opts['single_sidebar_position']) ) {
            $opts['single_sidebar_position'] = 'right';
        }
        if ( ! isset($opts['single_sidebar_elementor']) ) {
            $opts['single_sidebar_elementor'] = '';
        }
        if ( ! isset($opts['text_color']) ) {
            $opts['text_color'] = '#222222';
        }
        if ( ! isset($opts['text_hover_color']) ) {
            $opts['text_hover_color'] = '#111111';
        }
        $templates = Themsah_Theme_Elementor_Support::get_elementor_templates();
        $menus = wp_get_nav_menus();
        $public_post_types = get_post_types(array('public' => true), 'objects');
        ?>
        <div class="wrap themsah-admin-wrap">
            <h1><?php esc_html_e('تنظیمات قالب تمساح', 'themsah-theme'); ?></h1>
            <form method="post" action="options.php" id="themsah-options-form">
                <?php settings_fields('themsah_theme_options_group'); ?>
                <?php do_settings_sections('themsah_theme_options_group'); ?>

                <div class="themsah-tabs">
                    <ul class="themsah-tab-nav">
                        <li class="active" data-tab="tab-style"><?php esc_html_e('استایل', 'themsah-theme'); ?></li>
                        <li data-tab="tab-header"><?php esc_html_e('هدر', 'themsah-theme'); ?></li>
                        <li data-tab="tab-elementor"><?php esc_html_e('قالب‌های المنتور', 'themsah-theme'); ?></li>
                        <li data-tab="tab-templates"><?php esc_html_e('قالب‌های صفحات', 'themsah-theme'); ?></li>
                        <li data-tab="tab-fonts"><?php esc_html_e('فونت‌ها', 'themsah-theme'); ?></li>
                    </ul>

                    <div class="themsah-tab-content active" id="tab-style">
                        <table class="form-table">
                            <tr>
                                <th><label for="primary_color"><?php esc_html_e('رنگ اصلی', 'themsah-theme'); ?></label></th>
                                <td>
                                    <input type="text" name="themsah_theme_options[primary_color]" id="primary_color" value="<?php echo esc_attr($opts['primary_color']); ?>" class="themsah-color-field" data-default-color="#2663ff" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="hover_color"><?php esc_html_e('رنگ هاور', 'themsah-theme'); ?></label></th>
                                <td>
                                    <?php $hover = isset($opts['hover_color']) ? $opts['hover_color'] : '#1f49c9'; ?>
                                    <input type="text" name="themsah_theme_options[hover_color]" id="hover_color" value="<?php echo esc_attr($hover); ?>" class="themsah-color-field" data-default-color="#1f49c9" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="text_color"><?php esc_html_e('رنگ متن اصلی', 'themsah-theme'); ?></label></th>
                                <td>
                                    <input type="text" name="themsah_theme_options[text_color]" id="text_color" value="<?php echo esc_attr($opts['text_color']); ?>" class="themsah-color-field" data-default-color="#222222" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="text_hover_color"><?php esc_html_e('رنگ متن در هاور', 'themsah-theme'); ?></label></th>
                                <td>
                                    <input type="text" name="themsah_theme_options[text_hover_color]" id="text_hover_color" value="<?php echo esc_attr($opts['text_hover_color']); ?>" class="themsah-color-field" data-default-color="#111111" />
                                </td>
                            </tr>
                            <tr>
                                <th><label for="footer_text"><?php esc_html_e('متن کپی‌رایت فوتر', 'themsah-theme'); ?></label></th>
                                <td>
                                    <textarea name="themsah_theme_options[footer_text]" id="footer_text" class="large-text" rows="3"><?php echo esc_textarea($opts['footer_text']); ?></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="themsah-tab-content" id="tab-header">
                        <table class="form-table">
                            <tr>
                                <th><label for="header_menu"><?php esc_html_e('منوی هدر', 'themsah-theme'); ?></label></th>
                                <td>
                                    <select name="themsah_theme_options[header_menu]" id="header_menu" class="themsah-select">
                                        <option value=""><?php esc_html_e('-- پیش‌فرض --', 'themsah-theme'); ?></option>
                                        <?php foreach ( $menus as $m ) : ?>
                                            <option value="<?php echo esc_attr($m->term_id); ?>" <?php selected($opts['header_menu'], $m->term_id); ?>><?php echo esc_html($m->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="header_button_text"><?php esc_html_e('متن دکمه هدر', 'themsah-theme'); ?></label></th>
                                <td><input type="text" name="themsah_theme_options[header_button_text]" id="header_button_text" value="<?php echo esc_attr($opts['header_button_text']); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th><label for="header_button_link"><?php esc_html_e('لینک دکمه هدر', 'themsah-theme'); ?></label></th>
                                <td><input type="text" name="themsah_theme_options[header_button_link]" id="header_button_link" value="<?php echo esc_attr($opts['header_button_link']); ?>" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th><label for="header_logo_image"><?php esc_html_e('لوگوی تصویری هدر', 'themsah-theme'); ?></label></th>
                                <td>
                                    <input type="text" name="themsah_theme_options[header_logo_image]" id="header_logo_image" value="<?php echo esc_attr( isset($opts['header_logo_image']) ? $opts['header_logo_image'] : '' ); ?>" class="regular-text themsah-media-url" />
                                    <button class="button themsah-media-upload" data-target="header_logo_image"><?php esc_html_e('انتخاب', 'themsah-theme'); ?></button>
                                    <p class="description"><?php esc_html_e('در صورت انتخاب تصویر، نام سایت به صورت متن نمایش داده نمی‌شود.', 'themsah-theme'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="themsah-tab-content" id="tab-elementor">
                        <table class="form-table">
                            <tr>
                                <th><label for="header_template"><?php esc_html_e('هدر المنتوری', 'themsah-theme'); ?></label></th>
                                <td>
                                    <select name="themsah_theme_options[header_template]" id="header_template" class="themsah-select">
                                        <option value=""><?php esc_html_e('-- استفاده از پیش‌فرض --', 'themsah-theme'); ?></option>
                                        <?php foreach ( $templates as $id => $title ) : ?>
                                            <option value="<?php echo esc_attr($id); ?>" <?php selected($opts['header_template'], $id); ?>><?php echo esc_html($title); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="footer_template"><?php esc_html_e('فوتر المنتوری', 'themsah-theme'); ?></label></th>
                                <td>
                                    <select name="themsah_theme_options[footer_template]" id="footer_template" class="themsah-select">
                                        <option value=""><?php esc_html_e('-- استفاده از پیش‌فرض --', 'themsah-theme'); ?></option>
                                        <?php foreach ( $templates as $id => $title ) : ?>
                                            <option value="<?php echo esc_attr($id); ?>" <?php selected($opts['footer_template'], $id); ?>><?php echo esc_html($title); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="themsah-tab-content" id="tab-templates">
                        <table class="form-table">
                            <?php foreach ( $public_post_types as $pt => $obj ) : if ( in_array($pt, array('attachment','elementor_library')) ) continue; ?>
                            <tr>
                                <th><?php esc_html_e('آرشیو پست تایپ', 'themsah-theme'); ?></th>
                                <td>
                                    <select name="themsah_theme_options[archive_templates][<?php echo esc_attr($pt); ?>]" class="themsah-select">
                                        <option value=""><?php esc_html_e('-- پیش‌فرض قالب --', 'themsah-theme'); ?></option>
                                        <?php foreach ( $templates as $id => $title ) : ?>
                                            <?php $sel = isset($opts['archive_templates'][$pt]) ? (int)$opts['archive_templates'][$pt] : 0; ?>
                                            <option value="<?php echo esc_attr($id); ?>" <?php selected($sel, $id); ?>><?php echo esc_html($title); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('سینگل پست تایپ', 'themsah-theme'); ?></th>
                                <td>
                                    <select name="themsah_theme_options[single_templates][<?php echo esc_attr($pt); ?>]" class="themsah-select">
                                        <option value=""><?php esc_html_e('-- پیش‌فرض قالب --', 'themsah-theme'); ?></option>
                                        <?php foreach ( $templates as $id => $title ) : ?>
                                            <?php $sel = isset($opts['single_templates'][$pt]) ? (int)$opts['single_templates'][$pt] : 0; ?>
                                            <option value="<?php echo esc_attr($id); ?>" <?php selected($sel, $id); ?>><?php echo esc_html($title); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <th><?php esc_html_e('چیدمان سینگل نوشته', 'themsah-theme'); ?></th>
                                <td>
                                    <select name="themsah_theme_options[single_sidebar_position]" class="themsah-select">
                                        <option value="right" <?php selected($opts['single_sidebar_position'],'right'); ?>><?php esc_html_e('سایدبار راست', 'themsah-theme'); ?></option>
                                        <option value="left" <?php selected($opts['single_sidebar_position'],'left'); ?>><?php esc_html_e('سایدبار چپ', 'themsah-theme'); ?></option>
                                        <option value="none" <?php selected($opts['single_sidebar_position'],'none'); ?>><?php esc_html_e('بدون سایدبار', 'themsah-theme'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e('سایدبار المنتوری (اختیاری)', 'themsah-theme'); ?></th>
                                <td>
                                    <select name="themsah_theme_options[single_sidebar_elementor]" class="themsah-select">
                                        <option value=""><?php esc_html_e('-- پیش‌فرض ابزارک‌ها --', 'themsah-theme'); ?></option>
                                        <?php foreach ( $templates as $id => $title ) : ?>
                                            <option value="<?php echo esc_attr($id); ?>" <?php selected($opts['single_sidebar_elementor'], $id); ?>><?php echo esc_html($title); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php esc_html_e('در صورت انتخاب، سایدبار از قالب المنتوری رندر می‌شود و ابزارک‌های پیش‌فرض نمایش داده نمی‌شوند.', 'themsah-theme'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="themsah-tab-content" id="tab-fonts">
                        <p class="description"><?php esc_html_e('می‌توانید چند خانواده فونت اضافه کنید. برای هر خانواده می‌توانید نوع فونت را "ساده" یا "Variable" انتخاب کنید. برای Variable نیازی به افزودن وزن‌های جداگانه نیست.', 'themsah-theme'); ?></p>
                        <div id="themsah-fonts-families">
                            <?php if ( ! empty($opts['custom_fonts_list']) ) : foreach ( $opts['custom_fonts_list'] as $fi => $fam ) : ?>
                            <div class="themsah-font-family" data-index="<?php echo esc_attr($fi); ?>">
                                <div class="family-head">
                                    <strong><?php esc_html_e('خانواده فونت', 'themsah-theme'); ?></strong>
                                    <button class="button button-link-delete themsah-font-family-remove" type="button">&times;</button>
                                </div>
                                <table class="form-table">
                                    <tr>
                                        <th><?php esc_html_e('نام خانواده', 'themsah-theme'); ?></th>
                                        <td><input type="text" class="regular-text" name="themsah_theme_options[custom_fonts_list][<?php echo esc_attr($fi); ?>][family]" value="<?php echo esc_attr( isset($fam['family']) ? $fam['family'] : '' ); ?>" placeholder="مثال: IRANSansX" /></td>
                                    </tr>
                                    <tr>
                                        <th><?php esc_html_e('نوع فونت', 'themsah-theme'); ?></th>
                                        <td>
                                            <?php $type = isset($fam['type']) ? $fam['type'] : 'static'; ?>
                                            <label><input type="radio" name="themsah_theme_options[custom_fonts_list][<?php echo esc_attr($fi); ?>][type]" value="static" <?php checked($type,'static'); ?> class="family-type-radio"> <?php esc_html_e('ساده', 'themsah-theme'); ?></label>
                                            &nbsp; &nbsp;
                                            <label><input type="radio" name="themsah_theme_options[custom_fonts_list][<?php echo esc_attr($fi); ?>][type]" value="variable" <?php checked($type,'variable'); ?> class="family-type-radio"> <?php esc_html_e('Variable', 'themsah-theme'); ?></label>
                                        </td>
                                    </tr>
                                </table>
                                <div class="family-static-fields" style="display: <?php echo ($type==='static' ? 'block':'none'); ?>">
                                    <table class="form-table themsah-fonts-repeater" data-family-index="<?php echo esc_attr($fi); ?>">
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e('وزن', 'themsah-theme'); ?></th>
                                                <th><?php esc_html_e('WOFF2', 'themsah-theme'); ?></th>
                                                <th><?php esc_html_e('WOFF', 'themsah-theme'); ?></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ( ! empty($fam['weights']) && is_array($fam['weights']) ) : foreach ( $fam['weights'] as $wi => $wrow ) : ?>
                                            <tr>
                                                <td>
                                                    <select name="themsah_theme_options[custom_fonts_list][<?php echo esc_attr($fi); ?>][weights][<?php echo esc_attr($wi); ?>][weight]">
                                                        <?php for($w=100;$w<=900;$w+=100): ?>
                                                        <option value="<?php echo $w; ?>" <?php selected( (int)$wrow['weight'], $w ); ?>><?php echo $w; ?></option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="regular-text themsah-media-url" name="themsah_theme_options[custom_fonts_list][<?php echo esc_attr($fi); ?>][weights][<?php echo esc_attr($wi); ?>][woff2]" value="<?php echo esc_url( isset($wrow['woff2']) ? $wrow['woff2'] : '' ); ?>" />
                                                    <button class="button themsah-media-upload" type="button"><?php esc_html_e('انتخاب', 'themsah-theme'); ?></button>
                                                </td>
                                                <td>
                                                    <input type="text" class="regular-text themsah-media-url" name="themsah_theme_options[custom_fonts_list][<?php echo esc_attr($fi); ?>][weights][<?php echo esc_attr($wi); ?>][woff]" value="<?php echo esc_url( isset($wrow['woff']) ? $wrow['woff'] : '' ); ?>" />
                                                    <button class="button themsah-media-upload" type="button"><?php esc_html_e('انتخاب', 'themsah-theme'); ?></button>
                                                </td>
                                                <td><button class="button button-link-delete themsah-font-remove" type="button">&times;</button></td>
                                            </tr>
                                            <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                    <p><button class="button themsah-font-add-weight" type="button" data-family-index="<?php echo esc_attr($fi); ?>">+ <?php esc_html_e('افزودن وزن فونت', 'themsah-theme'); ?></button></p>
                                </div>
                                <div class="family-variable-fields" style="display: <?php echo ($type==='variable' ? 'block':'none'); ?>">
                                    <table class="form-table">
                                        <tr>
                                            <th><?php esc_html_e('بازه وزن', 'themsah-theme'); ?></th>
                                            <td>
                                                <input type="number" min="1" max="1000" step="1" name="themsah_theme_options[custom_fonts_list][<?php echo esc_attr($fi); ?>][min]" value="<?php echo isset($fam['min']) ? intval($fam['min']) : 100; ?>" style="width:100px"> -
                                                <input type="number" min="1" max="1000" step="1" name="themsah_theme_options[custom_fonts_list][<?php echo esc_attr($fi); ?>][max]" value="<?php echo isset($fam['max']) ? intval($fam['max']) : 900; ?>" style="width:100px">
                                                <p class="description"><?php esc_html_e('معمولاً 100 تا 900', 'themsah-theme'); ?></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e('فایل WOFF2', 'themsah-theme'); ?></th>
                                            <td>
                                                <input type="text" class="regular-text themsah-media-url" name="themsah_theme_options[custom_fonts_list][<?php echo esc_attr($fi); ?>][woff2]" value="<?php echo esc_url( isset($fam['woff2']) ? $fam['woff2'] : '' ); ?>" />
                                                <button class="button themsah-media-upload" type="button"><?php esc_html_e('انتخاب', 'themsah-theme'); ?></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e('فایل WOFF (اختیاری)', 'themsah-theme'); ?></th>
                                            <td>
                                                <input type="text" class="regular-text themsah-media-url" name="themsah_theme_options[custom_fonts_list][<?php echo esc_attr($fi); ?>][woff]" value="<?php echo esc_url( isset($fam['woff']) ? $fam['woff'] : '' ); ?>" />
                                                <button class="button themsah-media-upload" type="button"><?php esc_html_e('انتخاب', 'themsah-theme'); ?></button>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                        <p><button class="button button-primary" id="themsah-font-family-add" type="button">+ <?php esc_html_e('افزودن خانواده فونت', 'themsah-theme'); ?></button></p>
                        <hr />
                        <p class="description"><?php esc_html_e('سازگاری با نسخه قدیمی: در صورت نیاز، تنظیمات قدیمی (یک خانواده با وزن‌های مختلف) همچنان ذخیره می‌شود، اما توصیه می‌شود از بخش جدید استفاده کنید.', 'themsah-theme'); ?></p>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function ajax_save() {
        if ( ! current_user_can('manage_options') ) {
            wp_send_json_error(array('message' => __('دسترسی غیرمجاز', 'themsah-theme')));
        }
        check_ajax_referer('themsah_options_save', 'nonce');
        $serialized = isset($_POST['serialized']) ? wp_unslash($_POST['serialized']) : '';
        $parsed = array();
        parse_str($serialized, $parsed);
        $incoming = isset($parsed['themsah_theme_options']) ? $parsed['themsah_theme_options'] : ( isset($parsed['mytheme_options']) ? $parsed['mytheme_options'] : array() );
        $sanitized = $this->sanitize( $incoming );
        update_option( $this->option_name, $sanitized );
        wp_send_json_success(array('message' => __('ذخیره شد', 'themsah-theme')));
    }

    /**
     * Backward compatible access to options by key
     */
    public static function get_all_options() {
        $opts = get_option('themsah_theme_options');
        if ( empty($opts) || ! is_array($opts) ) {
            $opts = get_option('mytheme_options', array());
        }
        return is_array($opts) ? $opts : array();
    }
}
