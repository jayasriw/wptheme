<?php
/**
 * Search results.
 *
 * @package jbportal
 */

get_header(); ?>

<div class="jb-container jb-layout-2col">
	<div class="jb-content">
		<header class="jb-page-header">
			<h1 class="jb-page-title"><?php printf( esc_html__( 'Search results for: %s', 'jbportal' ), '<span>' . esc_html( get_search_query() ) . '</span>' ); ?></h1>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="jb-post-list">
				<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/content', get_post_type() ); endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No results found. Try a different search.', 'jbportal' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
