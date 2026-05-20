<?php
/**
 * Lightweight post-view counter for jobs, companies and candidates.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_track_views() {
	if ( ! is_singular( array( 'job_listing', 'company', 'candidate' ) ) ) {
		return;
	}
	if ( is_admin() || is_preview() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return;
	}
	// Skip authors viewing their own posts and admins.
	global $post;
	if ( ! $post ) { return; }
	if ( is_user_logged_in() && (int) $post->post_author === get_current_user_id() ) {
		return;
	}
	$views = (int) get_post_meta( $post->ID, '_jb_views', true );
	update_post_meta( $post->ID, '_jb_views', $views + 1 );
	do_action( 'jbportal_view_tracked', $post->ID );
}
add_action( 'wp', 'jbportal_track_views' );

function jbportal_get_views( $post_id = null ) {
	return (int) get_post_meta( $post_id ?: get_the_ID(), '_jb_views', true );
}
