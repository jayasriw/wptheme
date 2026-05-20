<?php
/**
 * Main template file.
 *
 * @package jbportal
 */

get_header(); ?>

<div class="jb-container jb-layout-2col">
	<div class="jb-content">
		<?php if ( have_posts() ) : ?>
			<header class="jb-page-header">
				<h1 class="jb-page-title">
					<?php
					if ( is_home() && ! is_front_page() ) {
						single_post_title();
					} else {
						esc_html_e( 'Latest from the Blog', 'jbportal' );
					}
					?>
				</h1>
			</header>

			<div class="jb-post-list">
				<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/content', get_post_type() ); endwhile; ?>
			</div>

			<?php
			the_posts_pagination( array(
				'prev_text' => '←',
				'next_text' => '→',
			) );
			?>
		<?php else : ?>
			<p><?php esc_html_e( 'No posts found.', 'jbportal' ); ?></p>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
