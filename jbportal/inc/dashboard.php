<?php
/**
 * Employer / candidate dashboard helpers.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the current user's job listings.
 */
function jbportal_get_my_jobs( $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();
	if ( ! $user_id ) {
		return array();
	}
	return get_posts( array(
		'post_type'      => 'job_listing',
		'author'         => $user_id,
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'pending', 'draft' ),
	) );
}

/**
 * Get applications the current user has submitted (logged-in user).
 */
function jbportal_get_my_applications( $user_id = 0 ) {
	$user_id = $user_id ?: get_current_user_id();
	if ( ! $user_id ) {
		return array();
	}
	return get_posts( array(
		'post_type'      => 'job_application',
		'posts_per_page' => -1,
		'meta_key'       => '_application_user_id',
		'meta_value'     => $user_id,
		'post_status'    => 'private',
	) );
}

/**
 * Allow a user to delete a job they own.
 */
function jbportal_handle_dashboard_actions() {
	if ( empty( $_GET['jbportal_action'] ) || ! is_user_logged_in() ) {
		return;
	}
	$action = sanitize_key( wp_unslash( $_GET['jbportal_action'] ) );
	$job_id = isset( $_GET['job_id'] ) ? (int) $_GET['job_id'] : 0;
	$nonce  = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';

	if ( ! $job_id || ! wp_verify_nonce( $nonce, 'jbportal_dashboard_' . $job_id ) ) {
		return;
	}
	$job = get_post( $job_id );
	if ( ! $job || (int) $job->post_author !== get_current_user_id() ) {
		return;
	}

	if ( 'delete_job' === $action ) {
		wp_trash_post( $job_id );
	} elseif ( 'mark_filled' === $action ) {
		update_post_meta( $job_id, '_job_filled', 1 );
	} elseif ( 'mark_open' === $action ) {
		delete_post_meta( $job_id, '_job_filled' );
	}

	wp_safe_redirect( remove_query_arg( array( 'jbportal_action', 'job_id', '_wpnonce' ) ) );
	exit;
}
add_action( 'template_redirect', 'jbportal_handle_dashboard_actions' );

/**
 * Update application status from the employer dashboard.
 */
function jbportal_update_app_status() {
	$app_id = isset( $_POST['app_id'] ) ? (int) $_POST['app_id'] : 0;
	if ( ! $app_id || ! current_user_can( 'edit_post', $app_id ) ) {
		// Allow the job's author to update too.
		$job_id = (int) get_post_meta( $app_id, '_application_job_id', true );
		if ( ! $job_id || (int) get_post_field( 'post_author', $job_id ) !== get_current_user_id() ) {
			wp_die( esc_html__( 'You can\'t do that.', 'jbportal' ) );
		}
	}
	check_admin_referer( 'jbportal_app_status_' . $app_id );

	$status   = sanitize_key( wp_unslash( $_POST['status'] ?? 'new' ) );
	$statuses = jbportal_application_statuses();
	if ( ! isset( $statuses[ $status ] ) ) {
		$status = 'new';
	}
	update_post_meta( $app_id, '_application_status', $status );

	// Notify applicant.
	$applicant_email = get_post_meta( $app_id, '_application_email', true );
	$job_id          = (int) get_post_meta( $app_id, '_application_job_id', true );
	if ( is_email( $applicant_email ) ) {
		wp_mail(
			$applicant_email,
			sprintf( __( '[%1$s] Your application status: %2$s', 'jbportal' ), get_bloginfo( 'name' ), $statuses[ $status ] ),
			sprintf( __( "Your application for \"%1\$s\" is now: %2\$s\n\n%3\$s", 'jbportal' ), get_the_title( $job_id ), $statuses[ $status ], get_permalink( $job_id ) )
		);
	}

	wp_safe_redirect( wp_get_referer() ?: home_url( '/dashboard/?tab=received' ) );
	exit;
}
add_action( 'admin_post_jbportal_update_app_status', 'jbportal_update_app_status' );
