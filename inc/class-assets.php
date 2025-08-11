<?php
if (! defined('ABSPATH')) exit;

class Themsah_Theme_Assets {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this,'front_scripts']);
        add_action('wp_head', [$this, 'inline_styles']);
        add_action('elementor/editor/after_enqueue_styles', [$this,'register_elementor_icon']);
        add_action('admin_enqueue_scripts', [$this,'register_elementor_icon']);
        add_action('admin_enqueue_scripts', [$this,'admin_styles']);
    }

    public function front_scripts() {
        $css_path = get_template_directory() . '/assets/css/main.css';
        wp_enqueue_style('themsah-style', get_template_directory_uri() . '/assets/css/main.css', array(), file_exists($css_path) ? filemtime($css_path) : '1.3.0' );
        wp_enqueue_script('themsah-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.2', true);

        // Load default Vazirmatn font from CDN when no custom font is configured (legacy or new schema)
        $custom_fonts      = Themsah_Theme_Options::get_option('custom_fonts', array());
        $custom_fonts_list = Themsah_Theme_Options::get_option('custom_fonts_list', array());
        $use_custom_fonts_legacy = ! empty($custom_fonts['family']) && ! empty($custom_fonts['weights']) && is_array($custom_fonts['weights']);
        $use_custom_fonts_new    = is_array($custom_fonts_list) && ! empty($custom_fonts_list);
        if ( ! $use_custom_fonts_legacy && ! $use_custom_fonts_new ) {
            wp_enqueue_style('themsah-vazirmatn', 'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css', array(), 'v33.003');
        }
        // basic layout CSS for single with sidebar
        $inline = '.single-grid{display:grid;grid-template-columns:1fr;gap:28px}.single-grid.single-sidebar-left{grid-template-columns:300px 1fr}.single-grid.single-sidebar-right{grid-template-columns:1fr 300px}.single-sidebar{position:sticky;top:20px;align-self:start;height:fit-content}.single-post .post-thumb{margin-bottom:16px}.single-post .post-thumb img{width:100%;height:550px;object-fit:cover;border-radius:10px}.single-post .entry-meta{color:#6b7280;margin-bottom:12px}.single-grid article{background:#fff;border:1px solid #eef3fb;padding:20px;border-radius:10px}.single-grid .single-sidebar .widget{background:#fff;border:1px solid #eef3fb;padding:16px;border-radius:10px;margin-bottom:16px}.comments-area,.comment-respond{background:#fff;border:1px solid #eef3fb;padding:16px;border-radius:10px;margin-top:24px}.comment-list{list-style:none;margin:0;padding:0}.comment-list .comment{border-bottom:1px solid #eef3fb;padding:12px 0}.comment-list .children{list-style:none;margin:12px 0 0 16px;padding:0;border-left:2px solid #eef3fb}.comment-meta .comment-author{font-weight:600}.comment-reply-link,.submit{display:inline-block;background:var(--mytheme-primary);color:#fff;border:none;border-radius:8px;padding:8px 14px;text-decoration:none;cursor:pointer;font-family:inherit}.comment-reply-link:hover,.submit:hover{opacity:.9}.comment-respond input[type="text"],.comment-respond input[type="email"],.comment-respond textarea{width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:10px;line-height:1.6;box-shadow:0 1px 2px rgba(16,24,40,.04);background:#fff}.comment-respond textarea{min-height:160px}';
        wp_add_inline_style('themsah-style', $inline);
    }

    public function inline_styles() {
        $primary = Themsah_Theme_Options::get_option('primary_color', '#2663ff');
        $hover = Themsah_Theme_Options::get_option('hover_color', '#1f49c9');
        $text = Themsah_Theme_Options::get_option('text_color', '#222222');
        $text_hover = Themsah_Theme_Options::get_option('text_hover_color', '#111111');
        $custom_fonts      = Themsah_Theme_Options::get_option('custom_fonts', array());
        $custom_fonts_list = Themsah_Theme_Options::get_option('custom_fonts_list', array());
        $use_custom_fonts_legacy = ! empty($custom_fonts['family']) && ! empty($custom_fonts['weights']) && is_array($custom_fonts['weights']);
        $use_custom_fonts_new    = is_array($custom_fonts_list) && ! empty($custom_fonts_list);

        $css  = ":root{--mytheme-primary:{$primary};--mytheme-hover:{$hover};--mytheme-text:{$text};--mytheme-text-hover:{$text_hover}} body{color:var(--mytheme-text)} .mytheme-btn{background:var(--mytheme-primary);} a:hover{color:var(--mytheme-hover)} .mytheme-btn:hover{background:var(--mytheme-hover)} a{color:var(--mytheme-text)} a:hover{color:var(--mytheme-text-hover)}";

        if ( $use_custom_fonts_legacy || $use_custom_fonts_new ) {
            $families_printed = array();
            // New schema: multiple families
            if ( $use_custom_fonts_new ) {
                foreach ( $custom_fonts_list as $family_conf ) {
                    $family = isset($family_conf['family']) ? trim($family_conf['family']) : '';
                    if ( $family === '' ) continue;
                    $type = isset($family_conf['type']) ? $family_conf['type'] : 'static';
                    if ( $type === 'variable' ) {
                        $min = isset($family_conf['min']) ? intval($family_conf['min']) : 100;
                        $max = isset($family_conf['max']) ? intval($family_conf['max']) : 900;
                        $woff2 = isset($family_conf['woff2']) ? esc_url($family_conf['woff2']) : '';
                        $woff  = isset($family_conf['woff']) ? esc_url($family_conf['woff']) : '';
                        if ( $woff2 || $woff ) {
                            $src_parts = array();
                            if ( $woff2 ) $src_parts[] = "url('{$woff2}') format('woff2')";
                            if ( $woff )  $src_parts[] = "url('{$woff}') format('woff')";
                            $src = implode(', ', $src_parts);
                            $css .= "@font-face{font-family:'" . esc_attr($family) . "';font-display:swap;src:".$src.";font-weight:".intval($min)." ".intval($max).";font-style:normal;}";
                            $families_printed[$family] = true;
                        }
                    } else {
                        if ( ! empty($family_conf['weights']) && is_array($family_conf['weights']) ) {
                            foreach ( $family_conf['weights'] as $wrow ) {
                                $weight = isset($wrow['weight']) ? intval($wrow['weight']) : 400;
                                $woff2  = isset($wrow['woff2']) ? esc_url($wrow['woff2']) : '';
                                $woff   = isset($wrow['woff']) ? esc_url($wrow['woff']) : '';
                                if ( ! $woff2 && ! $woff ) continue;
                                $src_parts = array();
                                if ( $woff2 ) $src_parts[] = "url('{$woff2}') format('woff2')";
                                if ( $woff )  $src_parts[] = "url('{$woff}') format('woff')";
                                $src = implode(', ', $src_parts);
                                $css .= "@font-face{font-family:'" . esc_attr($family) . "';font-display:swap;src:".$src.";font-weight:".$weight.";font-style:normal;}";
                                $families_printed[$family] = true;
                            }
                        }
                    }
                }
            }
            // Legacy single family (still supported)
            if ( $use_custom_fonts_legacy ) {
                $family = trim($custom_fonts['family']);
                foreach ( $custom_fonts['weights'] as $font ) {
                    $weight = isset($font['weight']) ? intval($font['weight']) : 400;
                    $woff2  = isset($font['woff2']) ? esc_url($font['woff2']) : '';
                    $woff   = isset($font['woff']) ? esc_url($font['woff']) : '';
                    $ttf    = isset($font['ttf']) ? esc_url($font['ttf']) : '';
                    if ( ! $family || ( ! $woff2 && ! $woff && ! $ttf ) ) {
                        continue;
                    }
                    $src_parts = array();
                    if ( $woff2 ) $src_parts[] = "url('{$woff2}') format('woff2')";
                    if ( $woff )  $src_parts[] = "url('{$woff}') format('woff')";
                    if ( $ttf )   $src_parts[] = "url('{$ttf}') format('truetype')";
                    $src = implode(', ', $src_parts);
                    $css .= "@font-face{font-family:'" . esc_attr($family) . "';font-display:swap;src:".$src.";font-weight:".$weight.";font-style:normal;}";
                    $families_printed[ $family ] = true;
                }
            }
            if ( ! empty($families_printed) ) {
                $families = array_keys($families_printed);
                $css .= 'body{font-family:"' . esc_attr(implode('","', $families)) . '",system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif;}';
            }
        } else {
            // Default to Vazirmatn when custom font is not set
            $css .= 'body{font-family:"Vazirmatn",system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif;}';
        }

        echo '<style>' . $css . '</style>';
    }

    public function register_elementor_icon() {
        $svg_url = get_template_directory_uri() . '/assets/images/themsah-icon.svg';
        $css = 
            ".elementor-panel .elementor-element .icon.themsah-icon {".
            "background:url('{$svg_url}') no-repeat center/contain; width:16px; height:16px; display:inline-block;".
            "} .themsah-icon:before{content:'';}".
            "body.themsah-elementor-canvas .site,body.themsah-elementor-canvas #page,body.themsah-elementor-canvas .wrap{max-width:none;margin:0;padding:0}";
        // Try to attach to Elementor editor styles; fallback register our own handle
        if ( wp_style_is('elementor-editor','enqueued') || wp_style_is('elementor-editor','registered') ) {
            wp_add_inline_style('elementor-editor', $css);
        } elseif ( is_admin() && ( wp_style_is('wp-admin','enqueued') || wp_style_is('wp-admin','registered') ) ) {
            wp_add_inline_style('wp-admin', $css);
        } else {
            if ( ! wp_style_is('themsah-editor-inline','registered') ) {
                wp_register_style('themsah-editor-inline', false);
                wp_enqueue_style('themsah-editor-inline');
            }
            wp_add_inline_style('themsah-editor-inline', $css);
        }
    }

    public function admin_styles() {
        // Apply Vazirmatn in admin when no custom font is set
        $custom_fonts      = Themsah_Theme_Options::get_option('custom_fonts', array());
        $custom_fonts_list = Themsah_Theme_Options::get_option('custom_fonts_list', array());
        $use_custom_fonts_legacy = ! empty($custom_fonts['family']) && ! empty($custom_fonts['weights']) && is_array($custom_fonts['weights']);
        $use_custom_fonts_new    = is_array($custom_fonts_list) && ! empty($custom_fonts_list);
        if ( ! $use_custom_fonts_legacy && ! $use_custom_fonts_new ) {
            wp_enqueue_style('themsah-vazirmatn-admin', 'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css', array(), 'v33.003');
            if ( ! wp_style_is('themsah-admin-base','registered') ) {
                wp_register_style('themsah-admin-base', false);
            }
            wp_enqueue_style('themsah-admin-base');
            wp_add_inline_style('themsah-admin-base', 'body, .wrap, .wp-core-ui, input, select, textarea{font-family:"Vazirmatn", -apple-system, Segoe UI, Roboto, Arial, sans-serif;}');
        }
    }
}
