<?php
if (! defined('ABSPATH')) exit;

class Themsah_Theme_Meta_Boxes {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add'));
        add_action('save_post', array($this, 'save'));
    }

    public function add() {
        $post_types = get_post_types(array('public' => true), 'names');
        foreach ( $post_types as $pt ) {
            add_meta_box('mytheme_page_templates', __('تنظیمات قالب صفحه','themsah-theme'), array($this, 'render'), $pt, 'normal', 'default');
        }
        // Project meta box
        add_meta_box('themsah_project_meta', __('اطلاعات پروژه','themsah-theme'), array($this,'render_project_meta'), 'themsah_project', 'normal', 'high');
    }

    public function render( $post ) {
        wp_nonce_field('mytheme_page_templates_nonce', 'mytheme_page_templates_nonce');
        $header_id = get_post_meta($post->ID, '_mytheme_header_template', true);
        $footer_id = get_post_meta($post->ID, '_mytheme_footer_template', true);
        $templates = Themsah_Theme_Elementor_Support::get_elementor_templates();
        ?>
        <p>
            <label for="mytheme_header_template"><?php esc_html_e('هدر:', 'themsah-theme'); ?></label>
            <select name="mytheme_header_template" id="mytheme_header_template" style="width:100%;">
                <option value=""><?php esc_html_e('استفاده از پیش‌فرض', 'themsah-theme'); ?></option>
                <?php foreach ( $templates as $id => $title ) : ?>
                    <option value="<?php echo esc_attr($id); ?>" <?php selected($header_id, $id); ?>><?php echo esc_html($title); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="mytheme_footer_template"><?php esc_html_e('فوتر:', 'themsah-theme'); ?></label>
            <select name="mytheme_footer_template" id="mytheme_footer_template" style="width:100%;">
                <option value=""><?php esc_html_e('استفاده از پیش‌فرض', 'themsah-theme'); ?></option>
                <?php foreach ( $templates as $id => $title ) : ?>
                    <option value="<?php echo esc_attr($id); ?>" <?php selected($footer_id, $id); ?>><?php echo esc_html($title); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public function render_project_meta( $post ) {
        wp_nonce_field('themsah_project_meta_nonce', 'themsah_project_meta_nonce');
        $name = get_post_meta($post->ID, '_themsah_project_name', true);
        $type = get_post_meta($post->ID, '_themsah_project_type', true);
        $gallery = get_post_meta($post->ID, '_themsah_project_gallery', true);
        $url = get_post_meta($post->ID, '_themsah_project_url', true);
        ?>
        <table class="form-table">
            <tr>
                <th><label for="themsah_project_name"><?php esc_html_e('نام پروژه', 'themsah-theme'); ?></label></th>
                <td><input type="text" id="themsah_project_name" name="themsah_project_name" class="regular-text" value="<?php echo esc_attr($name); ?>" /></td>
            </tr>
            <tr>
                <th><label for="themsah_project_type"><?php esc_html_e('نوع پروژه', 'themsah-theme'); ?></label></th>
                <td><input type="text" id="themsah_project_type" name="themsah_project_type" class="regular-text" value="<?php echo esc_attr($type); ?>" /></td>
            </tr>
            <tr>
                <th><label for="themsah_project_gallery"><?php esc_html_e('گالری عکس پروژه', 'themsah-theme'); ?></label></th>
                <td>
                    <input type="text" id="themsah_project_gallery" name="themsah_project_gallery" class="regular-text" value="<?php echo esc_attr($gallery); ?>" />
                    <button class="button themsah-media-upload" data-target="themsah_project_gallery" data-multiple="true" type="button"><?php esc_html_e('انتخاب تصاویر', 'themsah-theme'); ?></button>
                    <p class="description"><?php esc_html_e('می‌توانید چند تصویر انتخاب کنید؛ آدرس‌ها با ویرگول ذخیره می‌شوند.', 'themsah-theme'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="themsah_project_video"><?php esc_html_e('ویدئو پروژه', 'themsah-theme'); ?></label></th>
                <td>
                    <input type="text" id="themsah_project_video" name="themsah_project_video" class="regular-text" value="<?php echo esc_attr( get_post_meta($post->ID, '_themsah_project_video', true) ); ?>" />
                    <button class="button themsah-media-upload" data-target="themsah_project_video" data-library="video" type="button"><?php esc_html_e('انتخاب ویدئو', 'themsah-theme'); ?></button>
                </td>
            </tr>
            <tr>
                <th><label for="themsah_project_url"><?php esc_html_e('آدرس/لینک پروژه', 'themsah-theme'); ?></label></th>
                <td><input type="url" id="themsah_project_url" name="themsah_project_url" class="regular-text" value="<?php echo esc_attr($url); ?>" /></td>
            </tr>
        </table>
        <?php
    }

    public function save( $post_id ) {
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if ( isset($_POST['mytheme_page_templates_nonce']) && wp_verify_nonce($_POST['mytheme_page_templates_nonce'], 'mytheme_page_templates_nonce') ) {
            if ( isset($_POST['mytheme_header_template']) ) {
                update_post_meta( $post_id, '_mytheme_header_template', sanitize_text_field( $_POST['mytheme_header_template'] ) );
            } else {
                delete_post_meta( $post_id, '_mytheme_header_template' );
            }
            if ( isset($_POST['mytheme_footer_template']) ) {
                update_post_meta( $post_id, '_mytheme_footer_template', sanitize_text_field( $_POST['mytheme_footer_template'] ) );
            } else {
                delete_post_meta( $post_id, '_mytheme_footer_template' );
            }
        }
        if ( isset($_POST['themsah_project_meta_nonce']) && wp_verify_nonce($_POST['themsah_project_meta_nonce'], 'themsah_project_meta_nonce') ) {
            if ( isset($_POST['themsah_project_name']) ) update_post_meta($post_id, '_themsah_project_name', sanitize_text_field($_POST['themsah_project_name']));
            if ( isset($_POST['themsah_project_type']) ) update_post_meta($post_id, '_themsah_project_type', sanitize_text_field($_POST['themsah_project_type']));
            if ( isset($_POST['themsah_project_gallery']) ) update_post_meta($post_id, '_themsah_project_gallery', wp_kses_post($_POST['themsah_project_gallery']));
            if ( isset($_POST['themsah_project_url']) ) update_post_meta($post_id, '_themsah_project_url', esc_url_raw($_POST['themsah_project_url']));
            if ( isset($_POST['themsah_project_video']) ) update_post_meta($post_id, '_themsah_project_video', esc_url_raw($_POST['themsah_project_video']));
        }
    }
}
