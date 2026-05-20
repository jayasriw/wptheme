<?php
/**
 * 404 page.
 *
 * @package jbportal
 */

get_header(); ?>

<div class="jb-container jb-404">
	<h1>404</h1>
	<h2><?php esc_html_e( 'This page took a coffee break.', 'jbportal' ); ?></h2>
	<p><?php esc_html_e( 'The page you were looking for can\'t be found. Try a search or head back home.', 'jbportal' ); ?></p>
	<?php get_search_form(); ?>
	<a class="jb-btn jb-btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'jbportal' ); ?></a>
</div>

<?php get_footer(); ?>
