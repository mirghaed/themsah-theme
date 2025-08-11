<?php
/*
Template Name: Themsah Elementor Canvas
Description: Full-blank canvas for Elementor document previews (no header/footer/sidebar)
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
$doc_id = 0;
if ( isset($_GET['elementor-preview']) ) {
    $doc_id = absint($_GET['elementor-preview']);
} elseif ( isset($_GET['preview_id']) ) {
    $doc_id = absint($_GET['preview_id']);
}

if ( $doc_id && class_exists('Elementor\\Plugin') ) {
    echo Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $doc_id );
} else {
    if ( have_posts() ) {
        while ( have_posts() ) { the_post(); the_content(); }
    }
}
?>
<?php wp_footer(); ?>
</body>
</html>


