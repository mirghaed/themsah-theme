<?php if (! defined('ABSPATH')) exit; ?>
<article class="post-card" role="listitem">
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="post-thumb"><?php the_post_thumbnail('medium'); ?></div>
    <?php endif; ?>
    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <div class="excerpt"><?php echo wp_trim_words( get_the_excerpt(), 25 ); ?></div>
    <a href="<?php the_permalink(); ?>"><?php esc_html_e('ادامه مطلب', 'mytheme'); ?></a>
</article>
