<?php
/**
 * Template Name: Home — Classic
 * Two-column hero, dense job list.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-hero jb-hero-classic">
	<div class="jb-container jb-hero-inner">
		<div class="jb-hero-copy">
			<h1 class="jb-hero-title"><?php esc_html_e( 'Your next career move starts here', 'jbportal' ); ?></h1>
			<p class="jb-hero-subtitle"><?php esc_html_e( 'Hand-picked openings from top companies across the world.', 'jbportal' ); ?></p>
			<?php get_template_part( 'template-parts/job-search' ); ?>
		</div>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Latest jobs', 'jbportal' ); ?></h2><a class="jb-section-link" href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'See all →', 'jbportal' ); ?></a></header>
		<?php echo do_shortcode( '[jbportal_jobs limit="10"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Browse categories', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_categories limit="12"]' ); ?>
	</div>
</section>

<?php get_footer(); ?>
