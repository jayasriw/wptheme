<?php
/**
 * In-app Notification Center.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register jb_notification CPT.
 */
function jbportal_register_notification_cpt() {
	register_post_type( 'jb_notification', array(
		'labels'       => array(
			'name'          => __( 'Notifications', 'jbportal' ),
			'singular_name' => __( 'Notification', 'jbportal' ),
		),
		'public'       => false,
		'show_ui'      => false,
		'supports'     => array( 'title', 'author', 'editor' ),
		'capability_type' => 'post',
		'map_meta_cap' => true,
	) );
}
add_action( 'init', 'jbportal_register_notification_cpt' );

/**
 * Insert a notification for a user.
 *
 * @param int    $user_id User ID.
 * @param string $message Notification message.
 * @param string $type    Notification type: info, success, warning, error.
 * @param string $link    Optional URL.
 * @return int|false Post ID on success, false on failure.
 */
function jbportal_add_notification( $user_id, $message, $type = 'info', $link = '' ) {
	$user_id = (int) $user_id;
	if ( ! $user_id || ! $message ) {
		return false;
	}

	$notif_id = wp_insert_post( array(
		'post_type'    => 'jb_notification',
		'post_title'   => sanitize_text_field( $message ),
		'post_content' => sanitize_text_field( $message ),
		'post_status'  => 'private',
		'post_author'  => $user_id,
	) );

	if ( $notif_id && ! is_wp_error( $notif_id ) ) {
		update_post_meta( $notif_id, '_notif_type', sanitize_key( $type ) );
		update_post_meta( $notif_id, '_notif_link', esc_url_raw( $link ) );
		update_post_meta( $notif_id, '_notif_read', '0' );
		return $notif_id;
	}

	return false;
}

/**
 * Get notifications for a user.
 *
 * @param int $user_id User ID.
 * @param int $limit   Max number of notifications to retrieve.
 * @return WP_Post[]
 */
function jbportal_get_notifications( $user_id, $limit = 20 ) {
	$user_id = (int) $user_id;
	if ( ! $user_id ) {
		return array();
	}
	return get_posts( array(
		'post_type'      => 'jb_notification',
		'posts_per_page' => $limit,
		'author'         => $user_id,
		'post_status'    => 'private',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	) );
}

/**
 * Mark all notifications as read for a user.
 *
 * @param int $user_id User ID.
 */
function jbportal_mark_notifications_read( $user_id ) {
	$user_id = (int) $user_id;
	if ( ! $user_id ) {
		return;
	}
	$notifs = get_posts( array(
		'post_type'      => 'jb_notification',
		'posts_per_page' => -1,
		'author'         => $user_id,
		'post_status'    => 'private',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => '_notif_read',
				'value'   => '1',
				'compare' => '!=',
			),
		),
	) );
	foreach ( $notifs as $n ) {
		update_post_meta( $n->ID, '_notif_read', '1' );
	}
}

/**
 * Get unread notification count for a user.
 *
 * @param int $user_id User ID.
 * @return int
 */
function jbportal_get_unread_notifications_count( $user_id ) {
	$user_id = (int) $user_id;
	if ( ! $user_id ) {
		return 0;
	}
	$posts = get_posts( array(
		'post_type'      => 'jb_notification',
		'posts_per_page' => -1,
		'author'         => $user_id,
		'post_status'    => 'private',
		'no_found_rows'  => true,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'     => '_notif_read',
				'value'   => '1',
				'compare' => '!=',
			),
		),
	) );
	return count( $posts );
}

/**
 * Hook: notify employer when an application is submitted.
 */
function jbportal_notif_on_application( $app_id ) {
	$job_id    = (int) get_post_meta( $app_id, '_application_job_id', true );
	$job_title = $job_id ? get_the_title( $job_id ) : __( 'a job', 'jbportal' );
	if ( $job_id ) {
		$employer_id = (int) get_post_field( 'post_author', $job_id );
		if ( $employer_id ) {
			jbportal_add_notification(
				$employer_id,
				/* translators: %s: job title */
				sprintf( __( 'New application for %s', 'jbportal' ), $job_title ),
				'info',
				admin_url( 'edit.php?post_type=job_application' )
			);
		}
	}
}
add_action( 'jbportal_application_submitted', 'jbportal_notif_on_application' );

/**
 * Hook: notify seller when a service order is created.
 */
function jbportal_notif_on_service_order( $order_id ) {
	$service_id = (int) get_post_meta( $order_id, '_order_service', true );
	$seller_id  = (int) get_post_meta( $order_id, '_order_seller', true );
	$svc_title  = $service_id ? get_the_title( $service_id ) : __( 'a service', 'jbportal' );
	if ( $seller_id ) {
		jbportal_add_notification(
			$seller_id,
			/* translators: %s: service title */
			sprintf( __( 'New order for %s', 'jbportal' ), $svc_title ),
			'success'
		);
	}
}
add_action( 'jbportal_service_order_created', 'jbportal_notif_on_service_order' );

/**
 * Hook: notify user when withdrawal is approved.
 */
function jbportal_notif_on_withdrawal_approved( $user_id ) {
	jbportal_add_notification(
		(int) $user_id,
		__( 'Your withdrawal was approved', 'jbportal' ),
		'success'
	);
}
add_action( 'jbportal_withdrawal_approved', 'jbportal_notif_on_withdrawal_approved' );

/**
 * AJAX: mark notifications read.
 */
function jbportal_ajax_mark_notifications_read() {
	check_ajax_referer( 'jbportal_nonce', 'nonce' );
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Not logged in.', 'jbportal' ) ), 403 );
	}
	jbportal_mark_notifications_read( get_current_user_id() );
	wp_send_json_success( array( 'message' => __( 'Notifications marked as read.', 'jbportal' ) ) );
}
add_action( 'wp_ajax_jbportal_mark_notifications_read', 'jbportal_ajax_mark_notifications_read' );
