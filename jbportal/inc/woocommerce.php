<?php
/**
 * WooCommerce integration: paid job posting + featured-listing upgrades.
 *
 * If WooCommerce is active, employers must pay for a Job Package before
 * their submitted jobs are published. Each package has:
 *   - meta `_jb_jobs_allowed`  : number of jobs the buyer can post
 *   - meta `_jb_jobs_duration` : days the job stays live
 *   - meta `_jb_featured`      : 1/0 if these jobs should be marked featured
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_wc_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * Register the job_package product type (loosely; we use simple products with our meta).
 */
function jbportal_wc_product_meta_box( $product ) {
	global $post;
	echo '<div class="options_group">';
	woocommerce_wp_text_input( array(
		'id'                => '_jb_jobs_allowed',
		'label'             => __( 'Jobs allowed', 'jbportal' ),
		'type'              => 'number',
		'custom_attributes' => array( 'min' => '0' ),
		'description'       => __( 'How many job listings does this package allow? Leave 0 for upgrade-only products (e.g. "feature a job").', 'jbportal' ),
	) );
	woocommerce_wp_text_input( array(
		'id'                => '_jb_jobs_duration',
		'label'             => __( 'Listing duration (days)', 'jbportal' ),
		'type'              => 'number',
		'custom_attributes' => array( 'min' => '0' ),
	) );
	woocommerce_wp_checkbox( array(
		'id'    => '_jb_featured',
		'label' => __( 'Mark jobs as featured', 'jbportal' ),
	) );
	echo '</div>';
}

function jbportal_wc_save_product_meta( $post_id ) {
	if ( ! jbportal_wc_active() ) {
		return;
	}
	foreach ( array( '_jb_jobs_allowed', '_jb_jobs_duration', '_jb_featured' ) as $key ) {
		$val = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		update_post_meta( $post_id, $key, $val );
	}
}

if ( jbportal_wc_active() ) {
	add_action( 'woocommerce_product_options_general_product_data', 'jbportal_wc_product_meta_box' );
	add_action( 'woocommerce_process_product_meta', 'jbportal_wc_save_product_meta' );

	/**
	 * On order completion, credit jobs to the buyer.
	 */
	add_action( 'woocommerce_order_status_completed', 'jbportal_wc_credit_jobs' );
	add_action( 'woocommerce_order_status_processing', 'jbportal_wc_credit_jobs' );
}

function jbportal_wc_credit_jobs( $order_id ) {
	$order   = wc_get_order( $order_id );
	$user_id = $order ? $order->get_user_id() : 0;
	if ( ! $user_id ) {
		return;
	}
	if ( get_post_meta( $order_id, '_jb_credited', true ) ) {
		return;
	}
	foreach ( $order->get_items() as $item ) {
		$product_id = $item->get_product_id();
		$allowed    = (int) get_post_meta( $product_id, '_jb_jobs_allowed', true );
		$duration   = (int) get_post_meta( $product_id, '_jb_jobs_duration', true );
		$featured   = (int) get_post_meta( $product_id, '_jb_featured', true );
		$qty        = (int) $item->get_quantity();

		$credits = (int) get_user_meta( $user_id, 'jb_job_credits', true );
		$credits = max( 0, $credits + $allowed * $qty );
		update_user_meta( $user_id, 'jb_job_credits', $credits );

		if ( $duration ) { update_user_meta( $user_id, 'jb_job_duration', $duration ); }
		if ( $featured ) { update_user_meta( $user_id, 'jb_job_featured', 1 ); }
	}
	update_post_meta( $order_id, '_jb_credited', 1 );
}

/**
 * Hook into job submission: deduct a credit and apply featured flag.
 */
function jbportal_wc_apply_credit_on_job( $job_id, $user_id ) {
	if ( ! jbportal_wc_active() ) {
		return;
	}
	$credits = (int) get_user_meta( $user_id, 'jb_job_credits', true );
	if ( $credits <= 0 ) {
		// Force pending review when out of credits.
		wp_update_post( array( 'ID' => $job_id, 'post_status' => 'pending' ) );
		return;
	}
	update_user_meta( $user_id, 'jb_job_credits', $credits - 1 );

	if ( (int) get_user_meta( $user_id, 'jb_job_featured', true ) ) {
		update_post_meta( $job_id, '_job_featured', 1 );
	}
	$duration = (int) get_user_meta( $user_id, 'jb_job_duration', true );
	if ( $duration ) {
		update_post_meta( $job_id, '_job_expires', gmdate( 'Y-m-d', time() + $duration * DAY_IN_SECONDS ) );
	}
}
add_action( 'jbportal_after_job_submission', 'jbportal_wc_apply_credit_on_job', 10, 2 );
