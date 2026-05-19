<?php
/**
 * Single post template.
 *
 * @package jbportal
 */

get_header(); ?>

<div class="jb-container jb-layout-2col">
	<div class="jb-content">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'jb-single-post' ); ?>>
				<header class="jb-page-header">
					<h1 class="jb-page-title"><?php the_title(); ?></h1>
					<div class="jb-post-meta">
						<?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?>
					</div>
				</header>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="jb-page-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
				<?php endif; ?>
				<div class="jb-page-content"><?php the_content(); ?></div>
				<footer class="jb-post-footer"><?php the_tags( '<span class="jb-tags">', ' ', '</span>' ); ?></footer>
			</article>
			<?php if ( comments_open() || get_comments_number() ) { comments_template(); } ?>
		<?php endwhile; ?>
	</div>
	<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
