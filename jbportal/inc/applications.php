<?php
/**
 * Application form handling (front-end submission, job apply).
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle "Apply" form submission on a single job page.
 */
function jbportal_handle_application() {
	if ( empty( $_POST['jbportal_apply_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_apply_nonce'] ) ), 'jbportal_apply' ) ) {
		return;
	}

	$job_id = isset( $_POST['job_id'] ) ? (int) $_POST['job_id'] : 0;
	if ( ! $job_id || 'job_listing' !== get_post_type( $job_id ) ) {
		return;
	}

	$name  = isset( $_POST['applicant_name'] ) ? sanitize_text_field( wp_unslash( $_POST['applicant_name'] ) ) : '';
	$email = isset( $_POST['applicant_email'] ) ? sanitize_email( wp_unslash( $_POST['applicant_email'] ) ) : '';
	$phone = isset( $_POST['applicant_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['applicant_phone'] ) ) : '';
	$cover = isset( $_POST['applicant_cover'] ) ? sanitize_textarea_field( wp_unslash( $_POST['applicant_cover'] ) ) : '';
	$resume_url = isset( $_POST['applicant_resume_url'] ) ? esc_url_raw( wp_unslash( $_POST['applicant_resume_url'] ) ) : '';

	if ( ! $name || ! is_email( $email ) ) {
		set_transient( 'jbportal_apply_err_' . $job_id, __( 'Please provide your name and a valid email.', 'jbportal' ), 60 );
		wp_safe_redirect( get_permalink( $job_id ) . '#apply' );
		exit;
	}

	// Handle uploaded resume if present.
	if ( ! empty( $_FILES['applicant_resume']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$allowed = array( 'pdf', 'doc', 'docx', 'odt', 'rtf' );
		$ext     = strtolower( pathinfo( $_FILES['applicant_resume']['name'], PATHINFO_EXTENSION ) );
		if ( in_array( $ext, $allowed, true ) ) {
			$upload = wp_handle_upload( $_FILES['applicant_resume'], array( 'test_form' => false ) );
			if ( ! empty( $upload['url'] ) ) {
				$resume_url = $upload['url'];
			}
		}
	}

	$app_id = wp_insert_post( array(
		'post_type'   => 'job_application',
		'post_status' => 'private',
		'post_title'  => sprintf( '%s — %s', $name, get_the_title( $job_id ) ),
		'post_content' => $cover,
	) );

	if ( $app_id && ! is_wp_error( $app_id ) ) {
		update_post_meta( $app_id, '_application_job_id', $job_id );
		update_post_meta( $app_id, '_application_name', $name );
		update_post_meta( $app_id, '_application_email', $email );
		update_post_meta( $app_id, '_application_phone', $phone );
		update_post_meta( $app_id, '_application_resume_url', $resume_url );
		update_post_meta( $app_id, '_application_user_id', get_current_user_id() );

		// Notify the job poster / apply email.
		$to = get_post_meta( $job_id, '_job_apply_email', true );
		if ( ! $to ) {
			$author = get_post_field( 'post_author', $job_id );
			$to     = $author ? get_the_author_meta( 'user_email', $author ) : get_option( 'admin_email' );
		}
		$subject = sprintf( __( '[%1$s] New application: %2$s', 'jbportal' ), get_bloginfo( 'name' ), get_the_title( $job_id ) );
		$body    = sprintf(
			"%s: %s\n%s: %s\n%s: %s\n%s: %s\n\n%s\n\n%s:\n%s\n",
			__( 'Name', 'jbportal' ), $name,
			__( 'Email', 'jbportal' ), $email,
			__( 'Phone', 'jbportal' ), $phone,
			__( 'Resume', 'jbportal' ), $resume_url,
			__( 'Cover Letter', 'jbportal' ),
			__( 'Manage applications', 'jbportal' ),
			admin_url( 'edit.php?post_type=job_application' )
		);
		wp_mail( $to, $subject, $body );

		set_transient( 'jbportal_apply_ok_' . $job_id, __( 'Your application has been sent. Good luck!', 'jbportal' ), 60 );
	} else {
		set_transient( 'jbportal_apply_err_' . $job_id, __( 'Could not submit your application. Please try again.', 'jbportal' ), 60 );
	}

	wp_safe_redirect( get_permalink( $job_id ) . '#apply' );
	exit;
}
add_action( 'template_redirect', 'jbportal_handle_application' );

/**
 * Handle front-end "Post a Job" submission.
 */
function jbportal_handle_job_submission() {
	if ( empty( $_POST['jbportal_post_job_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_post_job_nonce'] ) ), 'jbportal_post_job' ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( wp_login_url( wp_get_referer() ) );
		exit;
	}

	$title       = sanitize_text_field( wp_unslash( $_POST['job_title'] ?? '' ) );
	$description = wp_kses_post( wp_unslash( $_POST['job_description'] ?? '' ) );
	$company     = sanitize_text_field( wp_unslash( $_POST['job_company'] ?? '' ) );
	$location    = sanitize_text_field( wp_unslash( $_POST['job_location'] ?? '' ) );
	$salary_min  = (int) ( $_POST['job_salary_min'] ?? 0 );
	$salary_max  = (int) ( $_POST['job_salary_max'] ?? 0 );
	$apply_email = sanitize_email( wp_unslash( $_POST['job_apply_email'] ?? '' ) );
	$apply_url   = esc_url_raw( wp_unslash( $_POST['job_apply_url'] ?? '' ) );
	$type        = sanitize_title( wp_unslash( $_POST['job_type'] ?? '' ) );
	$category    = sanitize_title( wp_unslash( $_POST['job_category'] ?? '' ) );

	if ( ! $title || ! $description ) {
		set_transient( 'jbportal_post_err_' . get_current_user_id(), __( 'A title and description are required.', 'jbportal' ), 60 );
		wp_safe_redirect( wp_get_referer() ?: home_url( '/' ) );
		exit;
	}

	$status  = current_user_can( 'publish_posts' ) ? 'publish' : 'pending';
	$post_id = wp_insert_post( array(
		'post_type'    => 'job_listing',
		'post_title'   => $title,
		'post_content' => $description,
		'post_status'  => $status,
		'post_author'  => get_current_user_id(),
	) );

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		set_transient( 'jbportal_post_err_' . get_current_user_id(), __( 'Could not save your job. Please try again.', 'jbportal' ), 60 );
		wp_safe_redirect( wp_get_referer() ?: home_url( '/' ) );
		exit;
	}

	update_post_meta( $post_id, '_job_company', $company );
	update_post_meta( $post_id, '_job_location', $location );
	update_post_meta( $post_id, '_job_salary_min', $salary_min );
	update_post_meta( $post_id, '_job_salary_max', $salary_max );
	update_post_meta( $post_id, '_job_apply_email', $apply_email );
	update_post_meta( $post_id, '_job_apply_url', $apply_url );
	if ( ! empty( $_POST['job_remote'] ) ) {
		update_post_meta( $post_id, '_job_remote', 1 );
	}

	if ( $type ) {
		wp_set_object_terms( $post_id, $type, 'job_type' );
	}
	if ( $category ) {
		wp_set_object_terms( $post_id, $category, 'job_category' );
	}

	set_transient( 'jbportal_post_ok_' . get_current_user_id(), 'pending' === $status
		? __( 'Thanks! Your job is awaiting moderation.', 'jbportal' )
		: __( 'Your job has been published.', 'jbportal' ), 60 );

	wp_safe_redirect( 'pending' === $status ? home_url( '/' ) : get_permalink( $post_id ) );
	exit;
}
add_action( 'template_redirect', 'jbportal_handle_job_submission' );
