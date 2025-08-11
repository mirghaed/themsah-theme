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
    <style>body.themsah-elementor-canvas{margin:0;padding:0;background:#fff}</style>
</head>
<body <?php body_class('themsah-elementor-canvas'); ?>>
<?php
if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        if ( class_exists('Elementor\\Plugin') ) {
            // Render the saved template content
            echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( get_the_ID() );
        } else {
            the_content();
        }
    endwhile;
endif;
?>
<?php wp_footer(); ?>
</body>
</html>


