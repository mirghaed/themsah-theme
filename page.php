<?php
if ( class_exists('Themsah_Theme_Template_Loader') && Themsah_Theme_Template_Loader::is_elementor_preview_context() ) {
    include get_template_directory() . '/templates/blank-canvas.php';
    return;
}
get_header();
?>
<main class="container">
    <?php if ( ! Themsah_Theme_Template_Loader::maybe_render_single_with_elementor() ) : ?>
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('page'); ?>>
                <h1><?php the_title(); ?></h1>
                <div class="entry-content"><?php the_content(); ?></div>
            </article>
        <?php endwhile; endif; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
?>