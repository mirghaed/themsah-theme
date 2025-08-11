<?php
/**
 * Single template for Elementor Library items
 * Renders on a clean, full-width canvas without theme header/footer/sidebar
 */
if ( ! defined('ABSPATH') ) exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        body.themsah-elementor-canvas{margin:0;padding:0;background:#fff}
        .elementor-content-area { min-height: 100vh; width: 100%; }
        .elementor-content-fallback { display: none; }
    </style>
</head>
<body <?php body_class('themsah-elementor-canvas'); ?>>
<?php
if ( have_posts() ) :
    echo '<div class="elementor-content-area">';
    while ( have_posts() ) : the_post();
        if ( class_exists('Elementor\\Plugin') ) {
            // Render the saved template content
            echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( get_the_ID() );
        } else {
            the_content();
        }
    endwhile;
    echo '</div>';
else :
    // Fallback content area for Elementor
    echo '<div class="elementor-content-area" style="min-height: 100vh; padding: 20px; text-align: center; color: #666;">
        <p>محتوای المنتور در حال بارگذاری...</p>
        <p>Elementor content is loading...</p>
    </div>';
endif;

// Ensure the_content is always called for Elementor
if ( have_posts() ) {
    echo '<div class="elementor-content-fallback">';
    while ( have_posts() ) {
        the_post();
        the_content();
    }
    rewind_posts();
    echo '</div>';
}
?>
<?php wp_footer(); ?>
</body>
</html>


