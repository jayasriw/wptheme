<?php
/**
 * Company Claim Flow.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the jb_claim CPT.
 */
function jbportal_register_claim_cpt() {
	register_post_type( 'jb_claim', array(
		'labels'       => array(
			'name'          => __( 'Company Claims', 'jbportal' ),
			'singular_name' => __( 'Company Claim', 'jbportal' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => 'edit.php?post_type=job_listing',
		'supports'     => array( 'title', 'author' ),
		'capability_type' => 'post',
		'map_meta_cap' => true,
	) );
}
add_action( 'init', 'jbportal_register_claim_cpt' );

/**
 * Display claim form on single company pages when query param is present.
 */
function jbportal_company_claim_output() {
	if ( ! is_singular( 'company' ) ) {
		return;
	}
	if ( empty( $_GET['jbportal_claim'] ) || empty( $_GET['company'] ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	$company_id = (int) $_GET['company'];
	if ( 'company' !== get_post_type( $company_id ) ) {
		return;
	}

	$claim_ok  = get_transient( 'jbportal_claim_ok_' . get_current_user_id() );
	$claim_err = get_transient( 'jbportal_claim_err_' . get_current_user_id() );
	if ( $claim_ok ) { delete_transient( 'jbportal_claim_ok_' . get_current_user_id() ); }
	if ( $claim_err ) { delete_transient( 'jbportal_claim_err_' . get_current_user_id() ); }
	?>
	<div class="jb-container" style="margin-top:2rem">
		<div class="jb-card" style="max-width:600px;margin:0 auto">
			<h2><?php esc_html_e( 'Claim this company', 'jbportal' ); ?></h2>
			<?php if ( $claim_ok ) : ?>
				<div class="jb-notice jb-notice-success"><?php echo esc_html( $claim_ok ); ?></div>
			<?php endif; ?>
			<?php if ( $claim_err ) : ?>
				<div class="jb-notice jb-notice-error"><?php echo esc_html( $claim_err ); ?></div>
			<?php endif; ?>
			<p><?php esc_html_e( 'Complete the form below to submit a claim for this company. Our team will review your request.', 'jbportal' ); ?></p>
			<form class="jb-form" method="post">
				<?php wp_nonce_field( 'jbportal_company_claim_' . $company_id, 'jbportal_claim_nonce' ); ?>
				<input type="hidden" name="jbportal_claim_submit" value="1">
				<input type="hidden" name="claim_company_id" value="<?php echo esc_attr( $company_id ); ?>">
				<label><?php esc_html_e( 'Your Name', 'jbportal' ); ?><input type="text" name="claim_name" required></label>
				<label><?php esc_html_e( 'Your Email', 'jbportal' ); ?><input type="email" name="claim_email" required></label>
				<label><?php esc_html_e( 'Proof of ownership (describe your role, provide website/LinkedIn)', 'jbportal' ); ?><textarea name="claim_proof" rows="5" required></textarea></label>
				<button type="submit" class="jb-btn jb-btn-primary"><?php esc_html_e( 'Submit Claim', 'jbportal' ); ?></button>
			</form>
		</div>
	</div>
	<?php
}
add_action( 'loop_end', 'jbportal_company_claim_output' );

/**
 * Handle claim form submission.
 */
function jbportal_handle_company_claim() {
	if ( empty( $_POST['jbportal_claim_submit'] ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}

	$company_id = isset( $_POST['claim_company_id'] ) ? (int) $_POST['claim_company_id'] : 0;
	if ( ! $company_id || 'company' !== get_post_type( $company_id ) ) {
		return;
	}

	if ( empty( $_POST['jbportal_claim_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_claim_nonce'] ) ), 'jbportal_company_claim_' . $company_id ) ) {
		wp_die( esc_html__( 'Security check failed.', 'jbportal' ) );
	}

	$uid   = get_current_user_id();
	$name  = sanitize_text_field( wp_unslash( $_POST['claim_name'] ?? '' ) );
	$email = sanitize_email( wp_unslash( $_POST['claim_email'] ?? '' ) );
	$proof = sanitize_textarea_field( wp_unslash( $_POST['claim_proof'] ?? '' ) );

	if ( ! $name || ! is_email( $email ) || ! $proof ) {
		set_transient( 'jbportal_claim_err_' . $uid, __( 'Please fill in all fields with valid data.', 'jbportal' ), 60 );
		wp_safe_redirect( add_query_arg( array( 'jbportal_claim' => '1', 'company' => $company_id ), get_permalink( $company_id ) ) );
		exit;
	}

	$claim_id = wp_insert_post( array(
		'post_type'    => 'jb_claim',
		'post_title'   => sprintf( 'Claim: %s by %s', get_the_title( $company_id ), $name ),
		'post_status'  => 'pending',
		'post_author'  => $uid,
		'post_content' => $proof,
	) );

	if ( $claim_id && ! is_wp_error( $claim_id ) ) {
		update_post_meta( $claim_id, '_claim_company_id', $company_id );
		update_post_meta( $claim_id, '_claim_name', $name );
		update_post_meta( $claim_id, '_claim_email', $email );
		// Notify admin.
		wp_mail(
			get_option( 'admin_email' ),
			sprintf( __( '[%s] New company claim submitted', 'jbportal' ), get_bloginfo( 'name' ) ),
			sprintf(
				"%s: %s\n%s: %s\n%s: %s\n\n%s:\n%s\n\n%s",
				__( 'Company', 'jbportal' ), get_the_title( $company_id ),
				__( 'Name', 'jbportal' ), $name,
				__( 'Email', 'jbportal' ), $email,
				__( 'Proof', 'jbportal' ), $proof,
				admin_url( 'edit.php?post_type=jb_claim' )
			)
		);
		set_transient( 'jbportal_claim_ok_' . $uid, __( 'Your claim has been submitted. We\'ll review it shortly.', 'jbportal' ), 60 );
	} else {
		set_transient( 'jbportal_claim_err_' . $uid, __( 'Could not submit your claim. Please try again.', 'jbportal' ), 60 );
	}

	wp_safe_redirect( add_query_arg( array( 'jbportal_claim' => '1', 'company' => $company_id ), get_permalink( $company_id ) ) );
	exit;
}
add_action( 'template_redirect', 'jbportal_handle_company_claim' );

/**
 * Admin: Company Claims listing page with Approve / Reject actions.
 */
function jbportal_claims_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=job_listing',
		__( 'Company Claims', 'jbportal' ),
		__( 'Company Claims', 'jbportal' ),
		'manage_options',
		'jbportal-company-claims',
		'jbportal_claims_admin_page'
	);
}
add_action( 'admin_menu', 'jbportal_claims_admin_menu' );

function jbportal_claims_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Access denied.', 'jbportal' ) );
	}

	// Handle approve/reject actions.
	if ( isset( $_GET['jbportal_claim_action'], $_GET['claim_id'], $_GET['_wpnonce'] ) ) {
		$action   = sanitize_key( $_GET['jbportal_claim_action'] );
		$claim_id = (int) $_GET['claim_id'];
		if ( wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'jbportal_claim_action_' . $claim_id ) ) {
			if ( 'approve' === $action ) {
				$company_id = (int) get_post_meta( $claim_id, '_claim_company_id', true );
				if ( $company_id ) {
					update_post_meta( $company_id, '_company_verified', '1' );
				}
				wp_update_post( array( 'ID' => $claim_id, 'post_status' => 'publish' ) );
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Claim approved and company verified.', 'jbportal' ) . '</p></div>';
			} elseif ( 'reject' === $action ) {
				wp_update_post( array( 'ID' => $claim_id, 'post_status' => 'trash' ) );
				echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'Claim rejected.', 'jbportal' ) . '</p></div>';
			}
		} else {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Security check failed.', 'jbportal' ) . '</p></div>';
		}
	}

	$claims = get_posts( array(
		'post_type'      => 'jb_claim',
		'post_status'    => 'pending',
		'posts_per_page' => -1,
	) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Company Claims', 'jbportal' ); ?></h1>
		<?php if ( $claims ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Company', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Claimant Name', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Email', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Proof', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Submitted', 'jbportal' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'jbportal' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $claims as $claim ) :
					$company_id  = (int) get_post_meta( $claim->ID, '_claim_company_id', true );
					$claim_name  = get_post_meta( $claim->ID, '_claim_name', true );
					$claim_email = get_post_meta( $claim->ID, '_claim_email', true );
					$approve_url = wp_nonce_url( add_query_arg( array( 'page' => 'jbportal-company-claims', 'jbportal_claim_action' => 'approve', 'claim_id' => $claim->ID ), admin_url( 'edit.php?post_type=job_listing' ) ), 'jbportal_claim_action_' . $claim->ID );
					$reject_url  = wp_nonce_url( add_query_arg( array( 'page' => 'jbportal-company-claims', 'jbportal_claim_action' => 'reject', 'claim_id' => $claim->ID ), admin_url( 'edit.php?post_type=job_listing' ) ), 'jbportal_claim_action_' . $claim->ID );
					?>
					<tr>
						<td><?php echo $company_id ? '<a href="' . esc_url( get_permalink( $company_id ) ) . '" target="_blank">' . esc_html( get_the_title( $company_id ) ) . '</a>' : '—'; ?></td>
						<td><?php echo esc_html( $claim_name ); ?></td>
						<td><a href="mailto:<?php echo esc_attr( $claim_email ); ?>"><?php echo esc_html( $claim_email ); ?></a></td>
						<td><?php echo esc_html( wp_trim_words( $claim->post_content, 20 ) ); ?></td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $claim->post_date ) ); ?></td>
						<td>
							<a href="<?php echo esc_url( $approve_url ); ?>" class="button button-primary" onclick="return confirm('<?php esc_attr_e( 'Approve this claim and verify the company?', 'jbportal' ); ?>')"><?php esc_html_e( 'Approve', 'jbportal' ); ?></a>
							<a href="<?php echo esc_url( $reject_url ); ?>" class="button" onclick="return confirm('<?php esc_attr_e( 'Reject and trash this claim?', 'jbportal' ); ?>')"><?php esc_html_e( 'Reject', 'jbportal' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p><?php esc_html_e( 'No pending company claims.', 'jbportal' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}
