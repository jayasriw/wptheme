<?php
/**
 * Self-service account actions: deactivate, export, csv.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_handle_account_actions() {
	if ( ! is_user_logged_in() ) {
		return;
	}
	if ( empty( $_POST['jbportal_account_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_account_nonce'] ) ), 'jbportal_account' ) ) {
		return;
	}
	$action = sanitize_key( wp_unslash( $_POST['account_action'] ?? '' ) );
	$uid    = get_current_user_id();

	if ( 'deactivate' === $action ) {
		update_user_meta( $uid, 'jb_deactivated', 1 );
		wp_logout();
		wp_safe_redirect( home_url( '/?deactivated=1' ) );
		exit;
	}
	if ( 'reactivate' === $action ) {
		delete_user_meta( $uid, 'jb_deactivated' );
		wp_safe_redirect( home_url( '/dashboard/' ) );
		exit;
	}
}
add_action( 'template_redirect', 'jbportal_handle_account_actions' );

/**
 * Block deactivated users from logging in.
 */
function jbportal_block_deactivated( $user ) {
	if ( $user instanceof WP_User && get_user_meta( $user->ID, 'jb_deactivated', true ) ) {
		return new WP_Error( 'jb_deactivated', __( 'This account is deactivated. Contact support to reactivate.', 'jbportal' ) );
	}
	return $user;
}
add_filter( 'wp_authenticate_user', 'jbportal_block_deactivated' );

/**
 * Admin: export jobs to CSV.
 */
function jbportal_admin_export_menu() {
	add_submenu_page(
		'edit.php?post_type=job_listing',
		__( 'Export Jobs CSV', 'jbportal' ),
		__( 'Export CSV', 'jbportal' ),
		'manage_options',
		'jbportal-export',
		'jbportal_admin_export_page'
	);
}
add_action( 'admin_menu', 'jbportal_admin_export_menu' );

function jbportal_admin_export_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Export Jobs', 'jbportal' ); ?></h1>
		<p><a class="button button-primary" href="<?php echo esc_url( add_query_arg( array( 'jbportal_export' => 'jobs', '_wpnonce' => wp_create_nonce( 'jbportal_export' ) ), admin_url( 'admin-post.php?action=jbportal_export' ) ) ); ?>"><?php esc_html_e( 'Download Jobs CSV', 'jbportal' ); ?></a></p>
		<p><a class="button" href="<?php echo esc_url( add_query_arg( array( 'jbportal_export' => 'candidates', '_wpnonce' => wp_create_nonce( 'jbportal_export' ) ), admin_url( 'admin-post.php?action=jbportal_export' ) ) ); ?>"><?php esc_html_e( 'Download Candidates CSV', 'jbportal' ); ?></a></p>
	</div>
	<?php
}

function jbportal_admin_export_handler() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die();
	}
	check_admin_referer( 'jbportal_export' );
	$what = isset( $_GET['jbportal_export'] ) ? sanitize_key( $_GET['jbportal_export'] ) : 'jobs';
	$cpt  = 'candidates' === $what ? 'candidate' : 'job_listing';
	$posts = get_posts( array( 'post_type' => $cpt, 'posts_per_page' => -1, 'post_status' => 'any' ) );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=jbportal-' . $what . '-' . gmdate( 'Ymd' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	if ( 'candidate' === $cpt ) {
		fputcsv( $out, array( 'ID', 'Name', 'Title', 'Location', 'Experience', 'Email', 'Resume URL', 'Available' ) );
		foreach ( $posts as $p ) {
			fputcsv( $out, array(
				$p->ID, $p->post_title,
				get_post_meta( $p->ID, '_candidate_title', true ),
				get_post_meta( $p->ID, '_candidate_location', true ),
				get_post_meta( $p->ID, '_candidate_experience', true ),
				get_post_meta( $p->ID, '_candidate_email', true ),
				get_post_meta( $p->ID, '_candidate_resume_url', true ),
				get_post_meta( $p->ID, '_candidate_available', true ),
			) );
		}
	} else {
		fputcsv( $out, array( 'ID', 'Title', 'Company', 'Location', 'Salary Min', 'Salary Max', 'Apply Email', 'Apply URL', 'Featured', 'Status', 'Posted' ) );
		foreach ( $posts as $p ) {
			fputcsv( $out, array(
				$p->ID, $p->post_title,
				get_post_meta( $p->ID, '_job_company', true ),
				get_post_meta( $p->ID, '_job_location', true ),
				get_post_meta( $p->ID, '_job_salary_min', true ),
				get_post_meta( $p->ID, '_job_salary_max', true ),
				get_post_meta( $p->ID, '_job_apply_email', true ),
				get_post_meta( $p->ID, '_job_apply_url', true ),
				get_post_meta( $p->ID, '_job_featured', true ),
				$p->post_status,
				$p->post_date,
			) );
		}
	}
	fclose( $out );
	exit;
}
add_action( 'admin_post_jbportal_export', 'jbportal_admin_export_handler' );
