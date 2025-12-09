<?php get_header(); ?>
<main>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <p><?php the_excerpt(); ?></p>
    <?php endwhile; else : ?>
        <p><?php _e('No posts found', 'osmb'); ?></p>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
