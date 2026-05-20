<?php
/**
 * Template Name: Home — Split
 * Two-panel hero: left = job seekers, right = employers.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-hero-split">
	<div class="jb-hero-split-panel jb-hero-split-seeker">
		<div class="jb-hero-split-inner">
			<h2><?php esc_html_e( 'Find your next role', 'jbportal' ); ?></h2>
			<p><?php esc_html_e( 'Browse thousands of open positions updated daily.', 'jbportal' ); ?></p>
			<a class="jb-btn jb-btn-primary jb-btn-lg" href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'Browse Jobs', 'jbportal' ); ?></a>
		</div>
	</div>
	<div class="jb-hero-split-panel jb-hero-split-employer">
		<div class="jb-hero-split-inner">
			<h2><?php esc_html_e( 'Hire great people', 'jbportal' ); ?></h2>
			<p><?php esc_html_e( 'Post a job and start receiving applications today.', 'jbportal' ); ?></p>
			<a class="jb-btn jb-btn-secondary jb-btn-lg" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( 'Post a Job', 'jbportal' ); ?></a>
		</div>
	</div>
</section>

<section class="jb-section jb-split-search-bar">
	<div class="jb-container"><?php get_template_part( 'template-parts/job-search' ); ?></div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'Latest jobs', 'jbportal' ); ?></h2>
			<a class="jb-section-link" href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'See all →', 'jbportal' ); ?></a>
		</header>
		<?php echo do_shortcode( '[jbportal_jobs limit="8"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Browse categories', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_categories limit="12"]' ); ?>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Featured companies', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_companies limit="6"]' ); ?>
	</div>
</section>

<?php get_footer(); ?>
