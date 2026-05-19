<?php
/**
 * Online meetings / interviews (Zoom / Google Meet / etc.) tied to applications or candidates.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_register_meeting_cpt() {
	register_post_type( 'jb_meeting', array(
		'labels' => array(
			'name'          => __( 'Meetings', 'jbportal' ),
			'singular_name' => __( 'Meeting', 'jbportal' ),
			'menu_name'     => __( 'Meetings', 'jbportal' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=job_listing',
		'supports'        => array( 'title', 'editor', 'author' ),
		'capability_type' => 'post',
	) );
}
add_action( 'init', 'jbportal_register_meeting_cpt' );

function jbportal_handle_create_meeting() {
	if ( empty( $_POST['jbportal_meeting_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_meeting_nonce'] ) ), 'jbportal_create_meeting' ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	$candidate_user = (int) ( $_POST['meeting_with'] ?? 0 );
	$datetime       = sanitize_text_field( wp_unslash( $_POST['meeting_when'] ?? '' ) );
	$url            = esc_url_raw( wp_unslash( $_POST['meeting_url'] ?? '' ) );
	$subject        = sanitize_text_field( wp_unslash( $_POST['meeting_subject'] ?? '' ) );
	$notes          = sanitize_textarea_field( wp_unslash( $_POST['meeting_notes'] ?? '' ) );

	if ( ! $candidate_user || ! $datetime ) {
		return;
	}

	$mid = wp_insert_post( array(
		'post_type'    => 'jb_meeting',
		'post_status'  => 'private',
		'post_title'   => $subject ?: __( 'Interview', 'jbportal' ),
		'post_content' => $notes,
		'post_author'  => get_current_user_id(),
	) );
	if ( $mid && ! is_wp_error( $mid ) ) {
		update_post_meta( $mid, '_meeting_with', $candidate_user );
		update_post_meta( $mid, '_meeting_when', $datetime );
		update_post_meta( $mid, '_meeting_url', $url );
		update_post_meta( $mid, '_meeting_organizer', get_current_user_id() );

		$user = get_userdata( $candidate_user );
		if ( $user ) {
			wp_mail(
				$user->user_email,
				sprintf( __( '[%s] Interview invitation', 'jbportal' ), get_bloginfo( 'name' ) ),
				sprintf( __( "You have a new interview scheduled:\n\nWhen: %1\$s\nLink: %2\$s\nSubject: %3\$s\n\n%4\$s", 'jbportal' ), $datetime, $url, $subject, $notes )
			);
		}
		set_transient( 'jbportal_meeting_ok_' . get_current_user_id(), __( 'Meeting scheduled and invitation sent.', 'jbportal' ), 60 );
	}
	wp_safe_redirect( wp_get_referer() ?: home_url( '/dashboard/?tab=meetings' ) );
	exit;
}
add_action( 'template_redirect', 'jbportal_handle_create_meeting' );

function jbportal_get_user_meetings( $user_id ) {
	return get_posts( array(
		'post_type'      => 'jb_meeting',
		'posts_per_page' => -1,
		'post_status'    => 'private',
		'meta_query'     => array(
			'relation' => 'OR',
			array( 'key' => '_meeting_with', 'value' => $user_id ),
			array( 'key' => '_meeting_organizer', 'value' => $user_id ),
		),
		'orderby'        => 'meta_value',
		'meta_key'       => '_meeting_when',
		'order'          => 'ASC',
	) );
}
