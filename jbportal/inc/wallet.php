<?php
/**
 * Wallet system — pending earnings, withdrawals, admin approval.
 *
 * Balance flow:
 *   service order paid → seller pending balance increases (minus commission)
 *   seller requests withdrawal → jb_withdrawal CPT created (status: pending)
 *   admin approves → balance decreases, withdrawal marked paid
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ── CPT ───────────────────────────────────────────────────── */

add_action( 'init', 'jbportal_register_withdrawal_cpt' );
function jbportal_register_withdrawal_cpt() {
	register_post_type( 'jb_withdrawal', array(
		'labels'          => array( 'name' => __( 'Withdrawals', 'jbportal' ), 'singular_name' => __( 'Withdrawal', 'jbportal' ) ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=job_listing',
		'supports'        => array( 'title', 'author' ),
		'capability_type' => 'post',
	) );
}

/* ── Wallet helpers ────────────────────────────────────────── */

function jbportal_wallet_balance( $user_id ) {
	return (float) get_user_meta( $user_id, 'jb_wallet_balance', true );
}

function jbportal_wallet_pending( $user_id ) {
	return (float) get_user_meta( $user_id, 'jb_wallet_pending', true );
}

function jbportal_wallet_add_pending( $user_id, $amount ) {
	$current = jbportal_wallet_pending( $user_id );
	update_user_meta( $user_id, 'jb_wallet_pending', $current + (float) $amount );
}

function jbportal_wallet_confirm_pending( $user_id, $amount ) {
	$pending = jbportal_wallet_pending( $user_id );
	$move    = min( $pending, (float) $amount );
	update_user_meta( $user_id, 'jb_wallet_pending', $pending - $move );
	$balance = jbportal_wallet_balance( $user_id );
	update_user_meta( $user_id, 'jb_wallet_balance', $balance + $move );
}

/* ── Credit wallet when a service order is completed ────────── */

add_action( 'jbportal_service_order_completed', 'jbportal_wallet_credit_seller', 10, 1 );
function jbportal_wallet_credit_seller( $order_id ) {
	$seller_id  = (int) get_post_meta( $order_id, '_order_seller', true );
	$earn       = (float) get_post_meta( $order_id, '_order_seller_earn', true );
	if ( $seller_id && $earn > 0 ) {
		jbportal_wallet_add_pending( $seller_id, $earn );
		update_post_meta( $order_id, '_order_status', 'completed' );
	}
}

/* ── Admin: mark order complete ─────────────────────────────── */

add_action( 'admin_post_jbportal_complete_service_order', 'jbportal_admin_complete_order' );
function jbportal_admin_complete_order() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die(); }
	$order_id = (int) ( $_GET['order_id'] ?? 0 );
	check_admin_referer( 'jbportal_complete_order_' . $order_id );
	do_action( 'jbportal_service_order_completed', $order_id );
	wp_safe_redirect( admin_url( 'edit.php?post_type=jb_service_order' ) );
	exit;
}

/* ── Front-end: request withdrawal ─────────────────────────── */

add_action( 'template_redirect', 'jbportal_handle_withdrawal_request' );
function jbportal_handle_withdrawal_request() {
	if ( empty( $_POST['jbportal_withdrawal_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_withdrawal_nonce'] ) ), 'jbportal_withdrawal' ) ) { return; }
	if ( ! is_user_logged_in() ) { return; }

	$uid    = get_current_user_id();
	$amount = (float) ( $_POST['withdrawal_amount'] ?? 0 );
	$method = sanitize_text_field( wp_unslash( $_POST['withdrawal_method'] ?? '' ) );
	$detail = sanitize_text_field( wp_unslash( $_POST['withdrawal_detail'] ?? '' ) );

	if ( $amount <= 0 ) {
		set_transient( 'jbportal_wallet_err_' . $uid, __( 'Enter a valid amount.', 'jbportal' ), 60 );
		wp_safe_redirect( add_query_arg( 'tab', 'wallet', home_url( '/dashboard/' ) ) );
		exit;
	}

	$available = jbportal_wallet_balance( $uid );
	if ( $amount > $available ) {
		set_transient( 'jbportal_wallet_err_' . $uid, __( 'Withdrawal amount exceeds available balance.', 'jbportal' ), 60 );
		wp_safe_redirect( add_query_arg( 'tab', 'wallet', home_url( '/dashboard/' ) ) );
		exit;
	}

	$wid = wp_insert_post( array(
		'post_type'   => 'jb_withdrawal',
		'post_status' => 'private',
		'post_title'  => sprintf( __( 'Withdrawal — $%.2f', 'jbportal' ), $amount ),
		'post_author' => $uid,
	) );
	if ( $wid && ! is_wp_error( $wid ) ) {
		update_post_meta( $wid, '_withdrawal_amount', $amount );
		update_post_meta( $wid, '_withdrawal_method', $method );
		update_post_meta( $wid, '_withdrawal_detail', $detail );
		update_post_meta( $wid, '_withdrawal_status', 'pending' );
		// Freeze the funds.
		$bal = jbportal_wallet_balance( $uid );
		update_user_meta( $uid, 'jb_wallet_balance', $bal - $amount );
		set_transient( 'jbportal_wallet_ok_' . $uid, __( 'Withdrawal request submitted. We will process it within 3 business days.', 'jbportal' ), 60 );
	}
	wp_safe_redirect( add_query_arg( 'tab', 'wallet', home_url( '/dashboard/' ) ) );
	exit;
}

/* ── Admin: approve / reject withdrawal ─────────────────────── */

add_action( 'admin_post_jbportal_process_withdrawal', 'jbportal_admin_process_withdrawal' );
function jbportal_admin_process_withdrawal() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die(); }
	$wid    = (int) ( $_GET['wid'] ?? 0 );
	$action = sanitize_key( $_GET['action_type'] ?? '' );
	check_admin_referer( 'jbportal_withdrawal_' . $wid );

	if ( 'approve' === $action ) {
		update_post_meta( $wid, '_withdrawal_status', 'paid' );
		$uid = (int) get_post_field( 'post_author', $wid );
		// Notify user.
		$user = get_userdata( $uid );
		if ( $user ) {
			wp_mail( $user->user_email, sprintf( __( '[%s] Withdrawal approved', 'jbportal' ), get_bloginfo( 'name' ) ), __( 'Your withdrawal request has been approved and will be transferred shortly.', 'jbportal' ) );
		}
	} elseif ( 'reject' === $action ) {
		update_post_meta( $wid, '_withdrawal_status', 'rejected' );
		// Refund balance.
		$uid    = (int) get_post_field( 'post_author', $wid );
		$amount = (float) get_post_meta( $wid, '_withdrawal_amount', true );
		$bal    = jbportal_wallet_balance( $uid );
		update_user_meta( $uid, 'jb_wallet_balance', $bal + $amount );
	}
	wp_safe_redirect( admin_url( 'edit.php?post_type=jb_withdrawal' ) );
	exit;
}

/* ── Admin: withdrawals submenu ─────────────────────────────── */

add_action( 'admin_menu', 'jbportal_wallet_admin_menu' );
function jbportal_wallet_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=job_listing',
		__( 'Withdrawals', 'jbportal' ),
		__( 'Withdrawals', 'jbportal' ),
		'manage_options',
		'jbportal-withdrawals',
		'jbportal_withdrawals_admin_page'
	);
}
function jbportal_withdrawals_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$withdrawals = get_posts( array( 'post_type' => 'jb_withdrawal', 'posts_per_page' => -1, 'post_status' => 'private', 'orderby' => 'date', 'order' => 'DESC' ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Withdrawal Requests', 'jbportal' ); ?></h1>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'User', 'jbportal' ); ?></th><th><?php esc_html_e( 'Amount', 'jbportal' ); ?></th><th><?php esc_html_e( 'Method', 'jbportal' ); ?></th><th><?php esc_html_e( 'Detail', 'jbportal' ); ?></th><th><?php esc_html_e( 'Status', 'jbportal' ); ?></th><th><?php esc_html_e( 'Date', 'jbportal' ); ?></th><th></th></tr></thead>
			<tbody>
			<?php foreach ( $withdrawals as $w ) :
				$uid    = (int) get_post_field( 'post_author', $w->ID );
				$user   = get_userdata( $uid );
				$amount = (float) get_post_meta( $w->ID, '_withdrawal_amount', true );
				$method = get_post_meta( $w->ID, '_withdrawal_method', true );
				$detail = get_post_meta( $w->ID, '_withdrawal_detail', true );
				$status = get_post_meta( $w->ID, '_withdrawal_status', true );
				$app_url = wp_nonce_url( admin_url( 'admin-post.php?action=jbportal_process_withdrawal&wid=' . $w->ID . '&action_type=approve' ), 'jbportal_withdrawal_' . $w->ID );
				$rej_url = wp_nonce_url( admin_url( 'admin-post.php?action=jbportal_process_withdrawal&wid=' . $w->ID . '&action_type=reject' ), 'jbportal_withdrawal_' . $w->ID );
				?>
				<tr>
					<td><?php echo esc_html( $user ? $user->display_name : '—' ); ?></td>
					<td>$<?php echo esc_html( number_format( $amount, 2 ) ); ?></td>
					<td><?php echo esc_html( $method ); ?></td>
					<td><?php echo esc_html( $detail ); ?></td>
					<td><?php echo esc_html( $status ); ?></td>
					<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $w->post_date ) ); ?></td>
					<td>
						<?php if ( 'pending' === $status ) : ?>
							<a href="<?php echo esc_url( $app_url ); ?>" class="button button-primary"><?php esc_html_e( 'Approve', 'jbportal' ); ?></a>
							<a href="<?php echo esc_url( $rej_url ); ?>" class="button"><?php esc_html_e( 'Reject', 'jbportal' ); ?></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
