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
        .elementor-content-area { min-height: 100vh; width: 100%; }
        .elementor-content-fallback { display: none; }
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
    echo '<div class="elementor-content-area">';
    echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $doc_id );
    echo '</div>';
} else {
    if ( have_posts() ) {
        echo '<div class="elementor-content-area">';
        while ( have_posts() ) {
            the_post();
            the_content();
        }
        echo '</div>';
    } else {
        // Fallback content area for Elementor
        echo '<div class="elementor-content-area" style="min-height: 100vh; padding: 20px; text-align: center; color: #666;">
            <p>محتوای المنتور در حال بارگذاری...</p>
            <p>Elementor content is loading...</p>
        </div>';
    }
}

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


