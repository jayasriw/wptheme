<?php
/**
 * Custom roles: jb_employer, jb_candidate.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_register_roles() {
	if ( ! get_role( 'jb_employer' ) ) {
		add_role( 'jb_employer', __( 'Employer', 'jbportal' ), array(
			'read'                   => true,
			'edit_posts'             => false,
			'publish_jb_jobs'        => true,
			'edit_jb_jobs'           => true,
			'delete_jb_jobs'         => true,
			'upload_files'           => true,
			'jb_post_jobs'           => true,
			'jb_manage_applications' => true,
		) );
	}
	if ( ! get_role( 'jb_candidate' ) ) {
		add_role( 'jb_candidate', __( 'Candidate', 'jbportal' ), array(
			'read'                  => true,
			'upload_files'          => true,
			'jb_apply_jobs'         => true,
			'jb_edit_own_profile'   => true,
		) );
	}

	// Grant admin all jb caps.
	$admin = get_role( 'administrator' );
	if ( $admin ) {
		foreach ( array( 'jb_post_jobs', 'jb_manage_applications', 'jb_apply_jobs', 'jb_edit_own_profile', 'publish_jb_jobs', 'edit_jb_jobs', 'delete_jb_jobs' ) as $cap ) {
			$admin->add_cap( $cap );
		}
	}
}
add_action( 'after_switch_theme', 'jbportal_register_roles' );

/**
 * Add a "Register as" picker to the WP register form.
 */
function jbportal_register_form() {
	$role = isset( $_GET['role'] ) ? sanitize_key( $_GET['role'] ) : '';
	?>
	<p>
		<label><input type="radio" name="jb_role" value="jb_candidate" <?php checked( 'jb_candidate' === $role || ! $role ); ?>> <?php esc_html_e( 'I\'m looking for a job', 'jbportal' ); ?></label><br>
		<label><input type="radio" name="jb_role" value="jb_employer" <?php checked( 'jb_employer' === $role ); ?>> <?php esc_html_e( 'I want to hire', 'jbportal' ); ?></label>
	</p>
	<?php
}
add_action( 'register_form', 'jbportal_register_form' );

function jbportal_register_user( $user_id ) {
	if ( empty( $_POST['jb_role'] ) ) {
		return;
	}
	$role = sanitize_key( wp_unslash( $_POST['jb_role'] ) );
	if ( in_array( $role, array( 'jb_employer', 'jb_candidate' ), true ) ) {
		$user = new WP_User( $user_id );
		$user->set_role( $role );
	}
}
add_action( 'user_register', 'jbportal_register_user' );

/**
 * Helpers.
 */
function jbportal_user_is_employer( $user_id = 0 ) {
	$user = $user_id ? get_user_by( 'id', $user_id ) : wp_get_current_user();
	return $user && ( in_array( 'jb_employer', (array) $user->roles, true ) || user_can( $user, 'jb_post_jobs' ) );
}
function jbportal_user_is_candidate( $user_id = 0 ) {
	$user = $user_id ? get_user_by( 'id', $user_id ) : wp_get_current_user();
	return $user && ( in_array( 'jb_candidate', (array) $user->roles, true ) || user_can( $user, 'jb_apply_jobs' ) );
}
