<?php
/**
 * Template Name: Home — Corporate
 * Heavy hero, stats, multiple CTAs.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-hero jb-hero-corporate">
	<div class="jb-container jb-hero-inner">
		<div class="jb-hero-copy">
			<span class="jb-eyebrow"><?php esc_html_e( 'Trusted by 12,000+ companies', 'jbportal' ); ?></span>
			<h1 class="jb-hero-title"><?php esc_html_e( 'Hire faster. Hire better.', 'jbportal' ); ?></h1>
			<p class="jb-hero-subtitle"><?php esc_html_e( 'The hiring platform for modern teams.', 'jbportal' ); ?></p>
			<?php get_template_part( 'template-parts/job-search' ); ?>
		</div>
	</div>
</section>

<section class="jb-section jb-section-stats">
	<div class="jb-container"><?php echo do_shortcode( '[jbportal_stats]' ); ?></div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Featured Opportunities', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_jobs limit="6" featured="1"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Browse by category', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_categories limit="8"]' ); ?>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'What people say', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_testimonials]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-cta">
	<div class="jb-container jb-cta-inner">
		<div><h2><?php esc_html_e( 'Ready to grow your team?', 'jbportal' ); ?></h2><p><?php esc_html_e( 'Post a job in under 5 minutes.', 'jbportal' ); ?></p></div>
		<div class="jb-cta-actions">
			<a class="jb-btn jb-btn-primary jb-btn-lg" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( 'Post a Job', 'jbportal' ); ?></a>
			<a class="jb-btn jb-btn-ghost jb-btn-lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'See Pricing', 'jbportal' ); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
