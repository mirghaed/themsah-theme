<?php
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
        <?php else : ?>
            <!-- Elementor archive content area fallback -->
            <div class="elementor-content-area">
                <?php 
                // Ensure the_content is called even when Elementor template is rendered
                if ( have_posts() ) : while ( have_posts() ) : the_post(); 
                    the_content(); 
                endwhile; endif; 
                ?>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <?php if ( ! Themsah_Theme_Template_Loader::maybe_render_single_with_elementor() ) : ?>
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('single-post'); ?>>
                    <h1><?php the_title(); ?></h1>
                    <div class="entry-content"><?php the_content(); ?></div>
                </article>
            <?php endwhile; endif; ?>
        <?php else : ?>
            <!-- Elementor single content area fallback -->
            <div class="elementor-content-area">
                <?php 
                // Ensure the_content is called even when Elementor template is rendered
                if ( have_posts() ) : while ( have_posts() ) : the_post(); 
                    the_content(); 
                endwhile; endif; 
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php
get_footer();
?>