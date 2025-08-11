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

    public function save( $post_id ) {
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if ( ! isset($_POST['mytheme_page_templates_nonce']) || ! wp_verify_nonce($_POST['mytheme_page_templates_nonce'], 'mytheme_page_templates_nonce') ) return;
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
}
