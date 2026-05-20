<?php
/**
 * Template Name: Home — Dark
 * Dark-mode-first homepage with glowing accent elements.
 *
 * @package jbportal
 */

get_header(); ?>

<div data-jb-theme="dark">

<section class="jb-hero jb-hero-dark-mode">
	<div class="jb-container jb-hero-inner">
		<div class="jb-hero-copy">
			<h1 class="jb-hero-title"><?php esc_html_e( 'Where great careers begin.', 'jbportal' ); ?></h1>
			<p class="jb-hero-subtitle"><?php esc_html_e( 'Thousands of hand-curated roles. Zero noise.', 'jbportal' ); ?></p>
			<?php get_template_part( 'template-parts/job-search' ); ?>
		</div>
	</div>
</section>

<section class="jb-section jb-section-stats jb-section-dark">
	<div class="jb-container"><?php echo do_shortcode( '[jbportal_stats]' ); ?></div>
</section>

<section class="jb-section jb-section-dark">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'Featured openings', 'jbportal' ); ?></h2>
			<a class="jb-section-link" href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'All jobs →', 'jbportal' ); ?></a>
		</header>
		<?php echo do_shortcode( '[jbportal_jobs limit="6" featured="1"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-alt jb-section-dark">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Top categories', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_categories limit="10"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-dark">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Hiring now', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_companies limit="8"]' ); ?>
	</div>
</section>

</div>

<?php get_footer(); ?>
