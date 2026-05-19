<?php
/**
 * Custom post types: job_listing, company, candidate.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_register_post_types() {
	register_post_type( 'job_listing', array(
		'labels' => array(
			'name'               => __( 'Jobs', 'jbportal' ),
			'singular_name'      => __( 'Job', 'jbportal' ),
			'add_new'            => __( 'Add Job', 'jbportal' ),
			'add_new_item'       => __( 'Add New Job', 'jbportal' ),
			'edit_item'          => __( 'Edit Job', 'jbportal' ),
			'new_item'           => __( 'New Job', 'jbportal' ),
			'view_item'          => __( 'View Job', 'jbportal' ),
			'search_items'       => __( 'Search Jobs', 'jbportal' ),
			'not_found'          => __( 'No jobs found', 'jbportal' ),
			'not_found_in_trash' => __( 'No jobs found in trash', 'jbportal' ),
			'menu_name'          => __( 'Jobs', 'jbportal' ),
		),
		'public'        => true,
		'has_archive'   => 'jobs',
		'rewrite'       => array( 'slug' => 'job', 'with_front' => false ),
		'menu_icon'     => 'dashicons-businessperson',
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'custom-fields', 'comments' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'company', array(
		'labels' => array(
			'name'               => __( 'Companies', 'jbportal' ),
			'singular_name'      => __( 'Company', 'jbportal' ),
			'add_new'            => __( 'Add Company', 'jbportal' ),
			'add_new_item'       => __( 'Add New Company', 'jbportal' ),
			'edit_item'          => __( 'Edit Company', 'jbportal' ),
			'menu_name'          => __( 'Companies', 'jbportal' ),
		),
		'public'        => true,
		'has_archive'   => 'companies',
		'rewrite'       => array( 'slug' => 'company', 'with_front' => false ),
		'menu_icon'     => 'dashicons-building',
		'menu_position' => 6,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'candidate', array(
		'labels' => array(
			'name'               => __( 'Candidates', 'jbportal' ),
			'singular_name'      => __( 'Candidate', 'jbportal' ),
			'add_new'            => __( 'Add Candidate', 'jbportal' ),
			'menu_name'          => __( 'Candidates', 'jbportal' ),
		),
		'public'        => true,
		'has_archive'   => 'candidates',
		'rewrite'       => array( 'slug' => 'candidate', 'with_front' => false ),
		'menu_icon'     => 'dashicons-id-alt',
		'menu_position' => 7,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'job_application', array(
		'labels' => array(
			'name'          => __( 'Applications', 'jbportal' ),
			'singular_name' => __( 'Application', 'jbportal' ),
			'menu_name'     => __( 'Applications', 'jbportal' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => 'edit.php?post_type=job_listing',
		'capability_type' => 'post',
		'supports'      => array( 'title', 'editor', 'custom-fields' ),
	) );
}
add_action( 'init', 'jbportal_register_post_types' );

function jbportal_flush_rewrite_on_activation() {
	jbportal_register_post_types();
	jbportal_register_taxonomies();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'jbportal_flush_rewrite_on_activation' );
