<?php
/**
 * Meta boxes for job_listing, company, candidate.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_add_meta_boxes() {
	add_meta_box( 'jbportal_job_details', __( 'Job Details', 'jbportal' ), 'jbportal_job_details_cb', 'job_listing', 'normal', 'high' );
	add_meta_box( 'jbportal_company_details', __( 'Company Details', 'jbportal' ), 'jbportal_company_details_cb', 'company', 'normal', 'high' );
	add_meta_box( 'jbportal_candidate_details', __( 'Candidate Details', 'jbportal' ), 'jbportal_candidate_details_cb', 'candidate', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'jbportal_add_meta_boxes' );

function jbportal_text_field( $post_id, $key, $label, $type = 'text', $placeholder = '' ) {
	$val = get_post_meta( $post_id, $key, true );
	printf(
		'<p><label for="%1$s"><strong>%2$s</strong></label><br><input type="%3$s" id="%1$s" name="%1$s" value="%4$s" placeholder="%5$s" class="widefat"></p>',
		esc_attr( $key ),
		esc_html( $label ),
		esc_attr( $type ),
		esc_attr( $val ),
		esc_attr( $placeholder )
	);
}

function jbportal_checkbox_field( $post_id, $key, $label ) {
	$val = get_post_meta( $post_id, $key, true );
	printf(
		'<p><label><input type="checkbox" name="%1$s" value="1" %3$s> %2$s</label></p>',
		esc_attr( $key ),
		esc_html( $label ),
		checked( $val, 1, false )
	);
}

function jbportal_job_details_cb( $post ) {
	wp_nonce_field( 'jbportal_save_meta', 'jbportal_meta_nonce' );
	jbportal_text_field( $post->ID, '_job_company', __( 'Company Name', 'jbportal' ) );
	jbportal_text_field( $post->ID, '_job_company_id', __( 'Linked Company Post ID (optional)', 'jbportal' ), 'number' );
	jbportal_text_field( $post->ID, '_job_location', __( 'Location', 'jbportal' ), 'text', 'San Francisco, CA' );
	jbportal_text_field( $post->ID, '_job_salary_min', __( 'Salary Min', 'jbportal' ), 'number' );
	jbportal_text_field( $post->ID, '_job_salary_max', __( 'Salary Max', 'jbportal' ), 'number' );
	jbportal_text_field( $post->ID, '_job_salary_currency', __( 'Currency Symbol', 'jbportal' ), 'text', '$' );
	jbportal_text_field( $post->ID, '_job_salary_period', __( 'Salary Period', 'jbportal' ), 'text', 'year' );
	jbportal_text_field( $post->ID, '_job_experience', __( 'Experience (years)', 'jbportal' ), 'text' );
	jbportal_text_field( $post->ID, '_job_apply_email', __( 'Apply Email', 'jbportal' ), 'email' );
	jbportal_text_field( $post->ID, '_job_apply_url', __( 'External Apply URL', 'jbportal' ), 'url' );
	jbportal_text_field( $post->ID, '_job_deadline', __( 'Application Deadline', 'jbportal' ), 'date' );
	jbportal_checkbox_field( $post->ID, '_job_featured', __( 'Featured Job', 'jbportal' ) );
	jbportal_checkbox_field( $post->ID, '_job_urgent', __( 'Urgent Hiring', 'jbportal' ) );
	jbportal_checkbox_field( $post->ID, '_job_remote', __( 'Remote Friendly', 'jbportal' ) );
	jbportal_checkbox_field( $post->ID, '_job_filled', __( 'Position Filled', 'jbportal' ) );
}

function jbportal_company_details_cb( $post ) {
	wp_nonce_field( 'jbportal_save_meta', 'jbportal_meta_nonce' );
	jbportal_text_field( $post->ID, '_company_website', __( 'Website', 'jbportal' ), 'url' );
	jbportal_text_field( $post->ID, '_company_email', __( 'Contact Email', 'jbportal' ), 'email' );
	jbportal_text_field( $post->ID, '_company_phone', __( 'Phone', 'jbportal' ), 'tel' );
	jbportal_text_field( $post->ID, '_company_address', __( 'Address', 'jbportal' ) );
	jbportal_text_field( $post->ID, '_company_size', __( 'Company Size', 'jbportal' ), 'text', '50-200' );
	jbportal_text_field( $post->ID, '_company_founded', __( 'Founded Year', 'jbportal' ), 'number' );
	jbportal_text_field( $post->ID, '_company_twitter', __( 'Twitter / X', 'jbportal' ), 'url' );
	jbportal_text_field( $post->ID, '_company_linkedin', __( 'LinkedIn', 'jbportal' ), 'url' );
	jbportal_text_field( $post->ID, '_company_facebook', __( 'Facebook', 'jbportal' ), 'url' );
	jbportal_checkbox_field( $post->ID, '_company_verified', __( 'Verified Company', 'jbportal' ) );
}

function jbportal_candidate_details_cb( $post ) {
	wp_nonce_field( 'jbportal_save_meta', 'jbportal_meta_nonce' );
	jbportal_text_field( $post->ID, '_candidate_title', __( 'Headline / Title', 'jbportal' ), 'text', 'Senior Frontend Developer' );
	jbportal_text_field( $post->ID, '_candidate_location', __( 'Location', 'jbportal' ) );
	jbportal_text_field( $post->ID, '_candidate_email', __( 'Email', 'jbportal' ), 'email' );
	jbportal_text_field( $post->ID, '_candidate_phone', __( 'Phone', 'jbportal' ), 'tel' );
	jbportal_text_field( $post->ID, '_candidate_experience', __( 'Years of Experience', 'jbportal' ), 'number' );
	jbportal_text_field( $post->ID, '_candidate_expected_salary', __( 'Expected Salary', 'jbportal' ), 'text' );
	jbportal_text_field( $post->ID, '_candidate_resume_url', __( 'Resume URL', 'jbportal' ), 'url' );
	jbportal_text_field( $post->ID, '_candidate_linkedin', __( 'LinkedIn', 'jbportal' ), 'url' );
	jbportal_text_field( $post->ID, '_candidate_website', __( 'Personal Site', 'jbportal' ), 'url' );
	jbportal_checkbox_field( $post->ID, '_candidate_available', __( 'Available For Hire', 'jbportal' ) );
}

function jbportal_save_meta( $post_id ) {
	if ( ! isset( $_POST['jbportal_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_meta_nonce'] ) ), 'jbportal_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$keys = array(
		'_job_company', '_job_company_id', '_job_location', '_job_salary_min', '_job_salary_max',
		'_job_salary_currency', '_job_salary_period', '_job_experience', '_job_apply_email', '_job_apply_url',
		'_job_deadline', '_job_featured', '_job_urgent', '_job_remote', '_job_filled',
		'_company_website', '_company_email', '_company_phone', '_company_address', '_company_size',
		'_company_founded', '_company_twitter', '_company_linkedin', '_company_facebook', '_company_verified',
		'_candidate_title', '_candidate_location', '_candidate_email', '_candidate_phone', '_candidate_experience',
		'_candidate_expected_salary', '_candidate_resume_url', '_candidate_linkedin', '_candidate_website', '_candidate_available',
	);

	foreach ( $keys as $k ) {
		if ( isset( $_POST[ $k ] ) ) {
			$raw = wp_unslash( $_POST[ $k ] );
			if ( in_array( $k, array( '_job_apply_url', '_company_website', '_company_twitter', '_company_linkedin', '_company_facebook', '_candidate_resume_url', '_candidate_linkedin', '_candidate_website' ), true ) ) {
				update_post_meta( $post_id, $k, esc_url_raw( $raw ) );
			} elseif ( in_array( $k, array( '_job_apply_email', '_company_email', '_candidate_email' ), true ) ) {
				update_post_meta( $post_id, $k, sanitize_email( $raw ) );
			} else {
				update_post_meta( $post_id, $k, sanitize_text_field( $raw ) );
			}
		} else {
			delete_post_meta( $post_id, $k );
		}
	}
}
add_action( 'save_post', 'jbportal_save_meta' );
