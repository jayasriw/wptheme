<?php
/**
 * Default page template.
 *
 * @package jbportal
 */

get_header(); ?>

<div class="jb-container jb-layout-1col">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'jb-page' ); ?>>
			<header class="jb-page-header">
				<h1 class="jb-page-title"><?php the_title(); ?></h1>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="jb-page-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>
			<div class="jb-page-content"><?php the_content(); wp_link_pages(); ?></div>
		</article>
		<?php if ( comments_open() || get_comments_number() ) { comments_template(); } ?>
	<?php endwhile; ?>
</div>

<?php get_footer(); ?>
