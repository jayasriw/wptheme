<?php
/**
 * Gutenberg block patterns and a few dynamic blocks.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_register_block_patterns() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}
	register_block_pattern_category( 'jbportal', array( 'label' => __( 'jbportal', 'jbportal' ) ) );

	register_block_pattern( 'jbportal/hero-search', array(
		'title'      => __( 'Hero with job search', 'jbportal' ),
		'categories' => array( 'jbportal' ),
		'content'    => '<!-- wp:shortcode -->[jbportal_job_search]<!-- /wp:shortcode -->',
	) );
	register_block_pattern( 'jbportal/featured-jobs', array(
		'title'      => __( 'Featured jobs grid', 'jbportal' ),
		'categories' => array( 'jbportal' ),
		'content'    => '<!-- wp:shortcode -->[jbportal_jobs limit="6" featured="1"]<!-- /wp:shortcode -->',
	) );
	register_block_pattern( 'jbportal/categories', array(
		'title'      => __( 'Browse by category', 'jbportal' ),
		'categories' => array( 'jbportal' ),
		'content'    => '<!-- wp:shortcode -->[jbportal_categories limit="8"]<!-- /wp:shortcode -->',
	) );
	register_block_pattern( 'jbportal/pricing', array(
		'title'      => __( 'Pricing plans', 'jbportal' ),
		'categories' => array( 'jbportal' ),
		'content'    => '<!-- wp:shortcode -->[jbportal_pricing]<!-- /wp:shortcode -->',
	) );
	register_block_pattern( 'jbportal/stats', array(
		'title'      => __( 'Site stats', 'jbportal' ),
		'categories' => array( 'jbportal' ),
		'content'    => '<!-- wp:shortcode -->[jbportal_stats]<!-- /wp:shortcode -->',
	) );
	register_block_pattern( 'jbportal/testimonials', array(
		'title'      => __( 'Testimonials', 'jbportal' ),
		'categories' => array( 'jbportal' ),
		'content'    => '<!-- wp:shortcode -->[jbportal_testimonials]<!-- /wp:shortcode -->',
	) );
	register_block_pattern( 'jbportal/cta', array(
		'title'      => __( 'Call-to-action', 'jbportal' ),
		'categories' => array( 'jbportal' ),
		'content'    => '<!-- wp:group {"className":"jb-section jb-section-cta"} --><div class="wp-block-group jb-section jb-section-cta"><div class="jb-container jb-cta-inner"><div><h2>' . esc_html__( 'Ready to hire?', 'jbportal' ) . '</h2><p>' . esc_html__( 'Post your first job in minutes.', 'jbportal' ) . '</p></div><div class="jb-cta-actions"><a class="jb-btn jb-btn-primary jb-btn-lg" href="/post-a-job/">' . esc_html__( 'Post a Job', 'jbportal' ) . '</a></div></div></div><!-- /wp:group -->',
	) );
}
add_action( 'init', 'jbportal_register_block_patterns' );

/**
 * Register a couple of simple dynamic blocks.
 */
function jbportal_register_blocks() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	register_block_type( 'jbportal/jobs', array(
		'attributes'      => array(
			'limit'    => array( 'type' => 'number', 'default' => 6 ),
			'featured' => array( 'type' => 'boolean', 'default' => false ),
		),
		'render_callback' => function( $attrs ) {
			return do_shortcode( sprintf( '[jbportal_jobs limit="%d" featured="%d"]', (int) ( $attrs['limit'] ?? 6 ), ! empty( $attrs['featured'] ) ? 1 : 0 ) );
		},
	) );

	register_block_type( 'jbportal/stats', array(
		'render_callback' => function() { return do_shortcode( '[jbportal_stats]' ); },
	) );

	register_block_type( 'jbportal/categories', array(
		'attributes'      => array( 'limit' => array( 'type' => 'number', 'default' => 8 ) ),
		'render_callback' => function( $attrs ) {
			return do_shortcode( sprintf( '[jbportal_categories limit="%d"]', (int) ( $attrs['limit'] ?? 8 ) ) );
		},
	) );
}
add_action( 'init', 'jbportal_register_blocks' );
