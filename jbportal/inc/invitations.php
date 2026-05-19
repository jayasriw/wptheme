<?php
/**
 * Job invitations — employer invites a candidate to a specific job.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_register_invitation_cpt() {
	register_post_type( 'jb_invitation', array(
		'labels'          => array( 'name' => __( 'Invitations', 'jbportal' ), 'singular_name' => __( 'Invitation', 'jbportal' ) ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=job_listing',
		'supports'        => array( 'title', 'editor', 'author' ),
		'capability_type' => 'post',
	) );
}
add_action( 'init', 'jbportal_register_invitation_cpt' );

function jbportal_handle_invite() {
	if ( empty( $_POST['jbportal_invite_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_invite_nonce'] ) ), 'jbportal_invite' ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	$candidate_id = (int) ( $_POST['candidate_post_id'] ?? 0 );
	$job_id       = (int) ( $_POST['invite_job_id'] ?? 0 );
	$message      = sanitize_textarea_field( wp_unslash( $_POST['invite_message'] ?? '' ) );
	if ( ! $candidate_id || ! $job_id ) {
		return;
	}
	$candidate_user = (int) get_post_field( 'post_author', $candidate_id );
	if ( ! $candidate_user ) {
		// fall back to candidate email meta.
		$email = get_post_meta( $candidate_id, '_candidate_email', true );
		if ( ! is_email( $email ) ) {
			return;
		}
	}

	$iid = wp_insert_post( array(
		'post_type'    => 'jb_invitation',
		'post_status'  => 'private',
		'post_title'   => sprintf( __( 'Invitation: %s', 'jbportal' ), get_the_title( $job_id ) ),
		'post_content' => $message,
		'post_author'  => get_current_user_id(),
	) );
	if ( $iid && ! is_wp_error( $iid ) ) {
		update_post_meta( $iid, '_invite_candidate_post', $candidate_id );
		update_post_meta( $iid, '_invite_candidate_user', $candidate_user );
		update_post_meta( $iid, '_invite_job_id', $job_id );
		update_post_meta( $iid, '_invite_status', 'sent' );

		if ( $candidate_user ) {
			$u = get_userdata( $candidate_user );
			if ( $u ) {
				wp_mail(
					$u->user_email,
					sprintf( __( '[%s] You\'ve been invited to apply', 'jbportal' ), get_bloginfo( 'name' ) ),
					sprintf( __( "%1\$s invited you to apply for:\n\n%2\$s\n%3\$s\n\n%4\$s", 'jbportal' ),
						wp_get_current_user()->display_name,
						get_the_title( $job_id ),
						get_permalink( $job_id ),
						$message
					)
				);
			}
		}
		set_transient( 'jbportal_invite_ok_' . get_current_user_id(), __( 'Invitation sent.', 'jbportal' ), 60 );
	}
	wp_safe_redirect( wp_get_referer() ?: home_url( '/dashboard/' ) );
	exit;
}
add_action( 'template_redirect', 'jbportal_handle_invite' );

function jbportal_get_user_invitations( $user_id ) {
	return get_posts( array(
		'post_type'      => 'jb_invitation',
		'posts_per_page' => -1,
		'post_status'    => 'private',
		'meta_query'     => array( array( 'key' => '_invite_candidate_user', 'value' => $user_id ) ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
}
