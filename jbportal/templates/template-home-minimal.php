<?php
/**
 * Template Name: Home — Minimal
 * Editorial, content-forward, single column.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-section jb-section-minimal-hero">
	<div class="jb-container" style="max-width:760px;text-align:center">
		<h1 class="jb-hero-title"><?php esc_html_e( 'Less noise. Better jobs.', 'jbportal' ); ?></h1>
		<p class="jb-hero-subtitle"><?php esc_html_e( 'A curated job board for people who care about craft.', 'jbportal' ); ?></p>
		<?php get_template_part( 'template-parts/job-search' ); ?>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container" style="max-width:860px">
		<header class="jb-section-head"><h2><?php esc_html_e( 'This week\'s picks', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_jobs limit="8"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-cta">
	<div class="jb-container jb-cta-inner">
		<div><h2><?php esc_html_e( 'Get the weekly digest', 'jbportal' ); ?></h2><p><?php esc_html_e( 'New, hand-picked roles every Tuesday.', 'jbportal' ); ?></p></div>
		<form class="jb-newsletter" style="max-width:360px">
			<input type="email" name="email" placeholder="you@example.com" required>
			<button class="jb-btn jb-btn-primary"><?php esc_html_e( 'Subscribe', 'jbportal' ); ?></button>
			<span class="jb-newsletter-message"></span>
		</form>
	</div>
</section>

<?php get_footer(); ?>
