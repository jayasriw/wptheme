<?php
/**
 * Follow companies / Follow candidates (per-user lists in user_meta).
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_get_follows( $user_id, $type = 'company' ) {
	$key = 'jb_follow_' . $type;
	$ids = get_user_meta( $user_id, $key, true );
	return is_array( $ids ) ? array_map( 'intval', $ids ) : array();
}

function jbportal_is_following( $user_id, $post_id, $type = 'company' ) {
	return in_array( (int) $post_id, jbportal_get_follows( $user_id, $type ), true );
}

function jbportal_ajax_toggle_follow() {
	check_ajax_referer( 'jbportal_nonce', 'nonce' );
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Please log in to follow.', 'jbportal' ) ), 403 );
	}
	$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
	$type    = get_post_type( $post_id );
	if ( ! in_array( $type, array( 'company', 'candidate' ), true ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid target.', 'jbportal' ) ), 400 );
	}
	$user_id = get_current_user_id();
	$key     = 'jb_follow_' . $type;
	$ids     = jbportal_get_follows( $user_id, $type );
	if ( in_array( $post_id, $ids, true ) ) {
		$ids   = array_diff( $ids, array( $post_id ) );
		$state = 'unfollowed';
	} else {
		$ids[] = $post_id;
		$state = 'followed';
	}
	update_user_meta( $user_id, $key, array_values( array_unique( $ids ) ) );
	wp_send_json_success( array( 'state' => $state ) );
}
add_action( 'wp_ajax_jbportal_toggle_follow', 'jbportal_ajax_toggle_follow' );

function jbportal_follow_button( $post_id ) {
	$type    = get_post_type( $post_id );
	$user_id = get_current_user_id();
	$active  = $user_id && jbportal_is_following( $user_id, $post_id, $type );
	$label   = $active ? __( 'Following', 'jbportal' ) : __( 'Follow', 'jbportal' );
	?>
	<button class="jb-btn jb-btn-ghost jb-follow<?php echo $active ? ' is-active' : ''; ?>" data-post-id="<?php echo esc_attr( $post_id ); ?>">
		<span class="jb-follow-icon">★</span> <span class="jb-follow-label"><?php echo esc_html( $label ); ?></span>
	</button>
	<?php
}
