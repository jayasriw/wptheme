<?php
/**
 * Saved Searches feature.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register jb_saved_search CPT.
 */
function jbportal_register_saved_search_cpt() {
	register_post_type( 'jb_saved_search', array(
		'labels'       => array(
			'name'          => __( 'Saved Searches', 'jbportal' ),
			'singular_name' => __( 'Saved Search', 'jbportal' ),
		),
		'public'       => false,
		'show_ui'      => false,
		'supports'     => array( 'title', 'author' ),
		'capability_type' => 'post',
		'map_meta_cap' => true,
	) );
}
add_action( 'init', 'jbportal_register_saved_search_cpt' );

/**
 * AJAX: save a search for the current user.
 */
function jbportal_ajax_save_search() {
	check_ajax_referer( 'jbportal_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'You must be logged in to save searches.', 'jbportal' ) ), 403 );
	}

	$keyword  = sanitize_text_field( wp_unslash( $_POST['keyword'] ?? '' ) );
	$location = sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) );
	$category = sanitize_text_field( wp_unslash( $_POST['category'] ?? '' ) );
	$type     = sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) );
	$remote   = ! empty( $_POST['remote'] ) ? '1' : '';

	$params = array(
		'keyword'  => $keyword,
		'location' => $location,
		'category' => $category,
		'type'     => $type,
		'remote'   => $remote,
	);

	$label = array_filter( array( $keyword, $location, $category, $type ) );
	$title = $label ? implode( ' · ', $label ) : __( 'Saved Search', 'jbportal' );

	$post_id = wp_insert_post( array(
		'post_type'   => 'jb_saved_search',
		'post_title'  => sanitize_text_field( $title ),
		'post_status' => 'private',
		'post_author' => get_current_user_id(),
	) );

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Could not save search. Please try again.', 'jbportal' ) ) );
	}

	update_post_meta( $post_id, '_search_params', wp_json_encode( $params ) );

	wp_send_json_success( array(
		'message' => __( 'Search saved!', 'jbportal' ),
		'id'      => $post_id,
	) );
}
add_action( 'wp_ajax_jbportal_save_search', 'jbportal_ajax_save_search' );

/**
 * AJAX: delete a saved search.
 */
function jbportal_ajax_delete_saved_search() {
	check_ajax_referer( 'jbportal_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Not logged in.', 'jbportal' ) ), 403 );
	}

	$post_id = isset( $_POST['search_id'] ) ? (int) $_POST['search_id'] : 0;
	if ( ! $post_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid ID.', 'jbportal' ) ), 400 );
	}

	$post = get_post( $post_id );
	if ( ! $post || 'jb_saved_search' !== $post->post_type || (int) $post->post_author !== get_current_user_id() ) {
		wp_send_json_error( array( 'message' => __( 'You cannot delete this search.', 'jbportal' ) ), 403 );
	}

	wp_delete_post( $post_id, true );
	wp_send_json_success( array( 'message' => __( 'Search deleted.', 'jbportal' ) ) );
}
add_action( 'wp_ajax_jbportal_delete_saved_search', 'jbportal_ajax_delete_saved_search' );
