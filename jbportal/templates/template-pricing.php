<?php
/**
 * Template Name: Pricing
 *
 * @package jbportal
 */

get_header(); ?>
<section class="jb-archive-hero">
	<div class="jb-container">
		<h1 class="jb-page-title"><?php esc_html_e( 'Simple, transparent pricing', 'jbportal' ); ?></h1>
		<p class="jb-archive-subtitle"><?php esc_html_e( 'Pick the plan that grows with your hiring needs.', 'jbportal' ); ?></p>
	</div>
</section>
<div class="jb-container jb-layout-1col">
	<?php while ( have_posts() ) { the_post(); the_content(); } ?>
	<?php echo do_shortcode( '[jbportal_pricing]' ); ?>
</div>
<?php get_footer(); ?>
