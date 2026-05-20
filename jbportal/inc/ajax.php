<?php
/**
 * AJAX handlers: bookmarks, applications, load more.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_ajax_toggle_bookmark() {
	check_ajax_referer( 'jbportal_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Please log in to bookmark jobs.', 'jbportal' ) ), 403 );
	}

	$job_id = isset( $_POST['job_id'] ) ? (int) $_POST['job_id'] : 0;
	if ( ! $job_id || 'job_listing' !== get_post_type( $job_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid job.', 'jbportal' ) ), 400 );
	}

	$user_id   = get_current_user_id();
	$bookmarks = jbportal_get_user_bookmarks( $user_id );

	if ( in_array( $job_id, $bookmarks, true ) ) {
		$bookmarks = array_diff( $bookmarks, array( $job_id ) );
		$state     = 'removed';
	} else {
		$bookmarks[] = $job_id;
		$state       = 'added';
	}

	update_user_meta( $user_id, 'jbportal_bookmarks', array_values( array_unique( $bookmarks ) ) );
	wp_send_json_success( array( 'state' => $state ) );
}
add_action( 'wp_ajax_jbportal_toggle_bookmark', 'jbportal_ajax_toggle_bookmark' );

function jbportal_ajax_load_more_jobs() {
	check_ajax_referer( 'jbportal_nonce', 'nonce' );
	$paged = isset( $_POST['paged'] ) ? max( 1, (int) $_POST['paged'] ) : 1;
	$q     = new WP_Query( array(
		'post_type'      => 'job_listing',
		'posts_per_page' => (int) get_theme_mod( 'jbportal_jobs_per_page', 10 ),
		'paged'          => $paged,
	) );
	ob_start();
	if ( $q->have_posts() ) {
		while ( $q->have_posts() ) {
			$q->the_post();
			get_template_part( 'template-parts/content', 'job' );
		}
		wp_reset_postdata();
	}
	wp_send_json_success( array(
		'html'     => ob_get_clean(),
		'has_more' => $paged < $q->max_num_pages,
	) );
}
add_action( 'wp_ajax_jbportal_load_more_jobs', 'jbportal_ajax_load_more_jobs' );
add_action( 'wp_ajax_nopriv_jbportal_load_more_jobs', 'jbportal_ajax_load_more_jobs' );

function jbportal_ajax_newsletter() {
	check_ajax_referer( 'jbportal_nonce', 'nonce' );
	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'jbportal' ) ), 400 );
	}
	$list = get_option( 'jbportal_newsletter', array() );
	if ( ! in_array( $email, $list, true ) ) {
		$list[] = $email;
		update_option( 'jbportal_newsletter', $list );
	}
	/**
	 * Fires when a user signs up via the footer newsletter form.
	 * MailChimp integration listens to this.
	 */
	do_action( 'jbportal_newsletter_signup', $email );
	wp_send_json_success( array( 'message' => __( 'Thanks! We\'ll keep you posted.', 'jbportal' ) ) );
}
add_action( 'wp_ajax_jbportal_newsletter', 'jbportal_ajax_newsletter' );
add_action( 'wp_ajax_nopriv_jbportal_newsletter', 'jbportal_ajax_newsletter' );
