<?php
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
    <?php else : ?>
        <!-- Elementor content area fallback -->
        <div class="elementor-content-area">
            <?php 
            // Ensure the_content is called even when Elementor template is rendered
            if ( have_posts() ) : while ( have_posts() ) : the_post(); 
                the_content(); 
            endwhile; endif; 
            ?>
        </div>
    <?php endif; ?>
</main>
<?php
get_footer();
?>