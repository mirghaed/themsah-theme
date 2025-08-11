<?php
get_header();
?>
<main class="container single-layout">
    <?php if ( ! Themsah_Theme_Template_Loader::maybe_render_single_with_elementor() ) : ?>
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <?php $pos = Themsah_Theme_Template_Loader::get_single_sidebar_position(); ?>
            <div class="single-grid single-sidebar-<?php echo esc_attr($pos); ?>">
                <?php if ( $pos === 'left' ) : ?>
                    <aside class="single-sidebar">
                        <?php Themsah_Theme_Sidebar_Renderer::render_single_sidebar(); ?>
                    </aside>
                <?php endif; ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class('single-post'); ?>>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="post-thumb"><?php the_post_thumbnail('large'); ?></div>
                    <?php endif; ?>
                    <h1><?php the_title(); ?></h1>
                    <div class="entry-meta"><?php echo esc_html(get_the_date()); ?></div>
                    <div class="entry-content"><?php the_content(); ?></div>
                    <?php comments_template(); ?>
                </article>

                <?php if ( $pos === 'right' ) : ?>
                    <aside class="single-sidebar">
                        <?php Themsah_Theme_Sidebar_Renderer::render_single_sidebar(); ?>
                    </aside>
                <?php endif; ?>
            </div>
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