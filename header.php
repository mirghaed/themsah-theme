<?php if (! defined('ABSPATH')) exit; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class( Themsah_Theme_Template_Loader::is_elementor_preview_context() ? 'themsah-elementor-canvas' : '' ); ?>>
<?php wp_body_open(); ?>
<?php Themsah_Theme_Template_Loader::render_header(); ?>