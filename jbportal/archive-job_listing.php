<?php
/**
 * Jobs archive.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-archive-hero">
	<div class="jb-container">
		<h1 class="jb-page-title">
			<?php
			if ( is_tax() ) {
				single_term_title();
			} else {
				esc_html_e( 'Browse Jobs', 'jbportal' );
			}
			?>
		</h1>
		<p class="jb-archive-subtitle">
			<?php
			global $wp_query;
			printf( esc_html__( '%d open positions found', 'jbportal' ), (int) $wp_query->found_posts );
			?>
		</p>
		<?php get_template_part( 'template-parts/job-search' ); ?>
	</div>
</section>

<div class="jb-container jb-layout-2col">
	<aside class="jb-sidebar jb-jobs-filters">
		<div class="jb-card">
			<h3><?php esc_html_e( 'Refine results', 'jbportal' ); ?></h3>
			<?php jbportal_render_job_filter_form(); ?>
		</div>
		<?php if ( is_active_sidebar( 'sidebar-jobs' ) ) { dynamic_sidebar( 'sidebar-jobs' ); } ?>
	</aside>

	<div class="jb-content">
		<?php if ( have_posts() ) : ?>
			<div class="jb-jobs-list">
				<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/content', 'job' ); endwhile; ?>
			</div>
			<?php the_posts_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
		<?php else : ?>
			<div class="jb-empty">
				<h2><?php esc_html_e( 'No jobs match your search.', 'jbportal' ); ?></h2>
				<p><?php esc_html_e( 'Try clearing your filters or broadening your keywords.', 'jbportal' ); ?></p>
				<a class="jb-btn jb-btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'Clear filters', 'jbportal' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
