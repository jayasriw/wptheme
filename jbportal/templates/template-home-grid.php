<?php
/**
 * Template Name: Home — Grid
 * Cards-first layout with featured companies prominent.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-section jb-hero-flat">
	<div class="jb-container">
		<h1 class="jb-hero-title" style="text-align:center"><?php esc_html_e( 'Discover great places to work.', 'jbportal' ); ?></h1>
		<?php get_template_part( 'template-parts/job-search' ); ?>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Top companies hiring', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_companies limit="12"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Featured jobs', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_jobs limit="9" featured="1"]' ); ?>
	</div>
</section>

<?php get_footer(); ?>
