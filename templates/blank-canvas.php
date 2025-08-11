<?php
/**
 * Blank canvas template for Elementor Library previews
 * Ensures Elementor editor opens on a clean, full-width page without theme header/footer
 */
if (! defined('ABSPATH')) exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        /* Keep the canvas clean and full width */
        body.themsah-elementor-canvas { margin:0; padding:0; background:#fff; }
    </style>
</head>
<body <?php body_class('themsah-elementor-canvas'); ?>>
<!-- THEMSAH_CANVAS_ACTIVE -->
<?php
// Prefer rendering the requested Elementor document explicitly
$doc_id = 0;
if ( isset($_GET['elementor-preview']) ) {
    $doc_id = absint($_GET['elementor-preview']);
} elseif ( isset($_GET['preview_id']) ) {
    $doc_id = absint($_GET['preview_id']);
}

if ( $doc_id && class_exists('Elementor\\Plugin') ) {
    echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $doc_id );
} else {
    if ( have_posts() ) {
        while ( have_posts() ) {
            the_post();
            the_content();
        }
    }
}
?>
<?php wp_footer(); ?>
</body>
</html>


