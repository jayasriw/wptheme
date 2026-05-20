<?php
/**
 * Simple direct messaging between employer and candidate.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_register_message_cpt() {
	register_post_type( 'jb_message', array(
		'labels' => array(
			'name'          => __( 'Messages', 'jbportal' ),
			'singular_name' => __( 'Message', 'jbportal' ),
			'menu_name'     => __( 'Messages', 'jbportal' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=job_listing',
		'supports'        => array( 'title', 'editor', 'author' ),
		'capability_type' => 'post',
	) );
}
add_action( 'init', 'jbportal_register_message_cpt' );

function jbportal_handle_send_message() {
	if ( empty( $_POST['jbportal_message_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_message_nonce'] ) ), 'jbportal_send_message' ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}

	$to_user   = (int) ( $_POST['to_user'] ?? 0 );
	$subject   = sanitize_text_field( wp_unslash( $_POST['msg_subject'] ?? '' ) );
	$body      = sanitize_textarea_field( wp_unslash( $_POST['msg_body'] ?? '' ) );
	$thread_id = (int) ( $_POST['thread_id'] ?? 0 );

	if ( ! $to_user || ! $body ) {
		return;
	}

	$mid = wp_insert_post( array(
		'post_type'    => 'jb_message',
		'post_status'  => 'private',
		'post_title'   => $subject ?: __( 'New message', 'jbportal' ),
		'post_content' => $body,
		'post_author'  => get_current_user_id(),
	) );
	if ( $mid && ! is_wp_error( $mid ) ) {
		update_post_meta( $mid, '_msg_from', get_current_user_id() );
		update_post_meta( $mid, '_msg_to', $to_user );
		update_post_meta( $mid, '_msg_thread', $thread_id ?: $mid );
		update_post_meta( $mid, '_msg_read', 0 );

		$to_user_obj = get_userdata( $to_user );
		if ( $to_user_obj ) {
			wp_mail(
				$to_user_obj->user_email,
				sprintf( __( '[%s] New message from %s', 'jbportal' ), get_bloginfo( 'name' ), wp_get_current_user()->display_name ),
				$body . "\n\n" . home_url( '/dashboard/?tab=messages' )
			);
		}
		set_transient( 'jbportal_msg_ok_' . get_current_user_id(), __( 'Message sent.', 'jbportal' ), 60 );
	}
	wp_safe_redirect( wp_get_referer() ?: home_url( '/dashboard/?tab=messages' ) );
	exit;
}
add_action( 'template_redirect', 'jbportal_handle_send_message' );

function jbportal_get_user_messages( $user_id ) {
	return get_posts( array(
		'post_type'      => 'jb_message',
		'posts_per_page' => -1,
		'post_status'    => 'private',
		'meta_query'     => array(
			'relation' => 'OR',
			array( 'key' => '_msg_to', 'value' => $user_id ),
			array( 'key' => '_msg_from', 'value' => $user_id ),
		),
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
}

function jbportal_get_unread_count( $user_id ) {
	$q = new WP_Query( array(
		'post_type'      => 'jb_message',
		'posts_per_page' => -1,
		'post_status'    => 'private',
		'fields'         => 'ids',
		'meta_query'     => array(
			array( 'key' => '_msg_to', 'value' => $user_id ),
			array( 'key' => '_msg_read', 'value' => 0 ),
		),
		'no_found_rows'  => false,
	) );
	return $q->found_posts;
}
