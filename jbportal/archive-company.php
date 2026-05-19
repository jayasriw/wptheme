<?php
/**
 * Companies archive.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-archive-hero">
	<div class="jb-container">
		<h1 class="jb-page-title"><?php esc_html_e( 'Companies hiring on jbportal', 'jbportal' ); ?></h1>
		<p class="jb-archive-subtitle"><?php esc_html_e( 'Explore companies and find your next great workplace.', 'jbportal' ); ?></p>
	</div>
</section>

<div class="jb-container">
	<?php if ( have_posts() ) : ?>
		<div class="jb-companies-grid">
			<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/content', 'company' ); endwhile; ?>
		</div>
		<?php the_posts_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No companies found.', 'jbportal' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
