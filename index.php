<?php
if ( class_exists('Themsah_Theme_Template_Loader') && Themsah_Theme_Template_Loader::is_elementor_preview_context() ) {
    include get_template_directory() . '/templates/blank-canvas.php';
    return;
}
get_header();
?>
<main class="container">
    <?php if ( is_home() || is_archive() ) : ?>
        <?php if ( ! Themsah_Theme_Template_Loader::maybe_render_archive_with_elementor() ) : ?>
            <?php if ( have_posts() ) : ?>
                <div class="post-list" role="list">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'templates/loop', 'post' ); ?>
                <?php endwhile; ?>
                </div>
                <div class="pagination"><?php the_posts_pagination(); ?></div>
            <?php else: ?>
                <p><?php esc_html_e('هیچ نوشته‌ای یافت نشد.', 'mytheme'); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <?php if ( ! Themsah_Theme_Template_Loader::maybe_render_single_with_elementor() ) : ?>
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('single-post'); ?>>
                    <h1><?php the_title(); ?></h1>
                    <div class="entry-content"><?php the_content(); ?></div>
                </article>
            <?php endwhile; endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
?>