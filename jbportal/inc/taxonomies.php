<?php
/**
 * Taxonomies: job_category, job_type, job_location, job_tag, industry, skill.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_register_taxonomies() {
	register_taxonomy( 'job_category', array( 'job_listing' ), array(
		'labels'       => array(
			'name'          => __( 'Job Categories', 'jbportal' ),
			'singular_name' => __( 'Job Category', 'jbportal' ),
			'menu_name'     => __( 'Categories', 'jbportal' ),
		),
		'hierarchical' => true,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'job-category' ),
	) );

	register_taxonomy( 'job_type', array( 'job_listing' ), array(
		'labels'       => array(
			'name'          => __( 'Job Types', 'jbportal' ),
			'singular_name' => __( 'Job Type', 'jbportal' ),
		),
		'hierarchical' => false,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'job-type' ),
	) );

	register_taxonomy( 'job_location', array( 'job_listing', 'company' ), array(
		'labels'       => array(
			'name'          => __( 'Locations', 'jbportal' ),
			'singular_name' => __( 'Location', 'jbportal' ),
		),
		'hierarchical' => true,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'job-location' ),
	) );

	register_taxonomy( 'job_tag', array( 'job_listing' ), array(
		'labels'       => array(
			'name'          => __( 'Job Tags', 'jbportal' ),
			'singular_name' => __( 'Job Tag', 'jbportal' ),
		),
		'hierarchical' => false,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'job-tag' ),
	) );

	register_taxonomy( 'industry', array( 'company' ), array(
		'labels'       => array(
			'name'          => __( 'Industries', 'jbportal' ),
			'singular_name' => __( 'Industry', 'jbportal' ),
		),
		'hierarchical' => true,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'industry' ),
	) );

	register_taxonomy( 'skill', array( 'candidate', 'job_listing' ), array(
		'labels'       => array(
			'name'          => __( 'Skills', 'jbportal' ),
			'singular_name' => __( 'Skill', 'jbportal' ),
		),
		'hierarchical' => false,
		'public'       => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => 'skill' ),
	) );
}
add_action( 'init', 'jbportal_register_taxonomies' );

function jbportal_seed_default_terms() {
	if ( get_option( 'jbportal_terms_seeded' ) ) {
		return;
	}
	$types = array( 'Full Time', 'Part Time', 'Freelance', 'Contract', 'Internship', 'Temporary', 'Remote' );
	foreach ( $types as $t ) {
		if ( ! term_exists( $t, 'job_type' ) ) {
			wp_insert_term( $t, 'job_type' );
		}
	}
	$cats = array( 'Engineering', 'Design', 'Marketing', 'Sales', 'Customer Service', 'Finance', 'Healthcare', 'Education' );
	foreach ( $cats as $c ) {
		if ( ! term_exists( $c, 'job_category' ) ) {
			wp_insert_term( $c, 'job_category' );
		}
	}
	update_option( 'jbportal_terms_seeded', 1 );
}
add_action( 'after_switch_theme', 'jbportal_seed_default_terms' );
