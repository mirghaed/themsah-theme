<?php
get_header();
?>
<main class="container">
    <?php if ( ! Themsah_Theme_Template_Loader::maybe_render_archive_with_elementor() ) : ?>
        <h1><?php the_archive_title(); ?></h1>
        <?php if ( have_posts() ) : ?>
            <div class="post-list">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'templates/loop', 'post' ); ?>
            <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php else: ?>
            <p><?php esc_html_e('هیچ نوشته‌ای یافت نشد.', 'mytheme'); ?></p>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
?>