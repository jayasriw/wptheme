<?php
/**
 * Scheduled tasks: auto-expire jobs, send job alerts.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_schedule_events() {
	if ( ! wp_next_scheduled( 'jbportal_daily_expire_jobs' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'jbportal_daily_expire_jobs' );
	}
	if ( ! wp_next_scheduled( 'jbportal_daily_job_alerts' ) ) {
		wp_schedule_event( strtotime( 'tomorrow 8:00' ), 'daily', 'jbportal_daily_job_alerts' );
	}
}
add_action( 'after_switch_theme', 'jbportal_schedule_events' );
add_action( 'init', 'jbportal_schedule_events' );

function jbportal_clear_scheduled_events() {
	wp_clear_scheduled_hook( 'jbportal_daily_expire_jobs' );
	wp_clear_scheduled_hook( 'jbportal_daily_job_alerts' );
}
add_action( 'switch_theme', 'jbportal_clear_scheduled_events' );

/**
 * Expire jobs whose deadline has passed and clean up old applications.
 */
function jbportal_expire_jobs() {
	$today = current_time( 'Y-m-d' );

	$q = new WP_Query( array(
		'post_type'      => 'job_listing',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'     => '_job_deadline',
				'value'   => $today,
				'compare' => '<',
				'type'    => 'DATE',
			),
		),
	) );

	foreach ( $q->posts as $id ) {
		wp_update_post( array( 'ID' => $id, 'post_status' => 'expired' ) );
	}

	// Also expire jobs older than the configured window (default 60 days).
	$days = (int) apply_filters( 'jbportal_auto_expire_days', 60 );
	if ( $days > 0 ) {
		$old = new WP_Query( array(
			'post_type'      => 'job_listing',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'date_query'     => array( array( 'column' => 'post_date', 'before' => $days . ' days ago' ) ),
		) );
		foreach ( $old->posts as $id ) {
			if ( ! get_post_meta( $id, '_job_no_expire', true ) ) {
				wp_update_post( array( 'ID' => $id, 'post_status' => 'expired' ) );
			}
		}
	}
}
add_action( 'jbportal_daily_expire_jobs', 'jbportal_expire_jobs' );

function jbportal_register_expired_status() {
	register_post_status( 'expired', array(
		'label'                     => _x( 'Expired', 'post status', 'jbportal' ),
		'public'                    => false,
		'protected'                 => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'jbportal' ),
	) );
}
add_action( 'init', 'jbportal_register_expired_status' );
