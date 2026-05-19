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

/* ── CSV Import ──────────────────────────────────────────────── */

add_action( 'admin_menu', 'jbportal_admin_import_menu' );
function jbportal_admin_import_menu() {
	add_submenu_page(
		'edit.php?post_type=job_listing',
		__( 'Import CSV', 'jbportal' ),
		__( 'Import CSV', 'jbportal' ),
		'manage_options',
		'jbportal-import',
		'jbportal_admin_import_page'
	);
}

function jbportal_admin_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$result = get_transient( 'jbportal_import_result_' . get_current_user_id() );
	if ( $result ) {
		delete_transient( 'jbportal_import_result_' . get_current_user_id() );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import from CSV', 'jbportal' ); ?></h1>

		<?php if ( $result ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $result ); ?></p></div>
		<?php endif; ?>

		<p><?php esc_html_e( 'Upload a CSV file to bulk-import jobs or candidates. The first row must be a header row.', 'jbportal' ); ?></p>

		<h2><?php esc_html_e( 'Import Jobs', 'jbportal' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Required columns: Title, Company, Location. Optional: Salary Min, Salary Max, Apply Email, Apply URL, Featured (1/0), Status (publish/draft).', 'jbportal' ); ?></p>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="jbportal_import">
			<input type="hidden" name="import_type" value="jobs">
			<?php wp_nonce_field( 'jbportal_import' ); ?>
			<input type="file" name="csv_file" accept=".csv" required>
			<?php submit_button( __( 'Import Jobs', 'jbportal' ) ); ?>
		</form>

		<h2><?php esc_html_e( 'Import Candidates', 'jbportal' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Required columns: Name. Optional: Title, Location, Experience, Email, Resume URL, Available (1/0).', 'jbportal' ); ?></p>
		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="jbportal_import">
			<input type="hidden" name="import_type" value="candidates">
			<?php wp_nonce_field( 'jbportal_import' ); ?>
			<input type="file" name="csv_file" accept=".csv" required>
			<?php submit_button( __( 'Import Candidates', 'jbportal' ) ); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_post_jbportal_import', 'jbportal_admin_import_handler' );
function jbportal_admin_import_handler() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die();
	}
	check_admin_referer( 'jbportal_import' );

	$type = isset( $_POST['import_type'] ) ? sanitize_key( $_POST['import_type'] ) : 'jobs';

	if ( empty( $_FILES['csv_file']['tmp_name'] ) ) {
		wp_safe_redirect( add_query_arg( 'page', 'jbportal-import', admin_url( 'edit.php?post_type=job_listing' ) ) );
		exit;
	}

	$file    = $_FILES['csv_file']['tmp_name']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$handle  = fopen( $file, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$headers = fgetcsv( $handle );

	if ( ! $headers ) {
		wp_safe_redirect( add_query_arg( 'page', 'jbportal-import', admin_url( 'edit.php?post_type=job_listing' ) ) );
		exit;
	}

	$headers = array_map( 'trim', $headers );
	$col     = array_flip( array_map( 'strtolower', $headers ) );
	$count   = 0;

	if ( 'candidates' === $type ) {
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$data = array_map( 'trim', $row );
			$name = $data[ $col['name'] ?? 0 ] ?? '';
			if ( ! $name ) { continue; }
			$pid = wp_insert_post( array(
				'post_type'   => 'candidate',
				'post_status' => 'publish',
				'post_title'  => $name,
			) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				$map = array(
					'title'      => '_candidate_title',
					'location'   => '_candidate_location',
					'experience' => '_candidate_experience',
					'email'      => '_candidate_email',
					'resume url' => '_candidate_resume_url',
					'available'  => '_candidate_available',
				);
				foreach ( $map as $header_key => $meta_key ) {
					if ( isset( $col[ $header_key ] ) && isset( $data[ $col[ $header_key ] ] ) ) {
						update_post_meta( $pid, $meta_key, sanitize_text_field( $data[ $col[ $header_key ] ] ) );
					}
				}
				$count++;
			}
		}
	} else {
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$data  = array_map( 'trim', $row );
			$title = $data[ $col['title'] ?? 0 ] ?? '';
			if ( ! $title ) { continue; }
			$status = isset( $col['status'] ) && isset( $data[ $col['status'] ] ) ? sanitize_key( $data[ $col['status'] ] ) : 'publish';
			$pid = wp_insert_post( array(
				'post_type'   => 'job_listing',
				'post_status' => in_array( $status, array( 'publish', 'draft', 'pending' ), true ) ? $status : 'publish',
				'post_title'  => $title,
			) );
			if ( $pid && ! is_wp_error( $pid ) ) {
				$map = array(
					'company'     => '_job_company',
					'location'    => '_job_location',
					'salary min'  => '_job_salary_min',
					'salary max'  => '_job_salary_max',
					'apply email' => '_job_apply_email',
					'apply url'   => '_job_apply_url',
					'featured'    => '_job_featured',
				);
				foreach ( $map as $header_key => $meta_key ) {
					if ( isset( $col[ $header_key ] ) && isset( $data[ $col[ $header_key ] ] ) ) {
						update_post_meta( $pid, $meta_key, sanitize_text_field( $data[ $col[ $header_key ] ] ) );
					}
				}
				$count++;
			}
		}
	}

	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	set_transient(
		'jbportal_import_result_' . get_current_user_id(),
		sprintf( __( 'Import complete. %d records created.', 'jbportal' ), $count ),
		60
	);
	wp_safe_redirect( add_query_arg( 'page', 'jbportal-import', admin_url( 'edit.php?post_type=job_listing' ) ) );
	exit;
}
