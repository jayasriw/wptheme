<?php
/**
 * Candidate & employer subscription packages with enforced limits.
 *
 * Packages are WooCommerce products (or hard-coded fallback plans) tagged with:
 *   _jb_plan_type         : 'candidate' | 'employer'
 *   _jb_apply_limit       : int (-1 = unlimited)
 *   _jb_services_limit    : int (-1 = unlimited)
 *   _jb_wishlist_limit    : int (-1 = unlimited)
 *   _jb_follow_limit      : int (-1 = unlimited)
 *   _jb_cv_downloads      : int (-1 = unlimited)  [employer]
 *   _jb_jobs_limit        : int (-1 = unlimited)  [employer]
 *   _jb_featured_jobs     : int (-1 = unlimited)  [employer]
 *   _jb_see_contact_info  : '1' | '0'             [candidate: can see company email/phone]
 *   _jb_plan_duration     : days (0 = forever)
 *
 * When a WC order completes: jbportal_membership_on_order_complete()
 * grants the user the plan's limits as user_meta.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ── User meta keys ────────────────────────────────────────── */
// jb_plan_type, jb_plan_id, jb_plan_expires, jb_apply_limit, jb_apply_count,
// jb_services_limit, jb_services_count, jb_wishlist_limit, jb_follow_limit,
// jb_cv_downloads_limit, jb_cv_downloads_count, jb_jobs_limit, jb_featured_jobs_limit,
// jb_see_contact_info

/* ── Grant plan on WC order complete ───────────────────────── */

add_action( 'woocommerce_order_status_completed', 'jbportal_membership_on_order_complete', 10, 1 );
function jbportal_membership_on_order_complete( $order_id ) {
	if ( ! jbportal_wc_active() ) { return; }
	$order = wc_get_order( $order_id );
	if ( ! $order ) { return; }
	$user_id = $order->get_customer_id();
	if ( ! $user_id ) { return; }

	foreach ( $order->get_items() as $item ) {
		$product_id = $item->get_product_id();
		$plan_type  = get_post_meta( $product_id, '_jb_plan_type', true );
		if ( ! $plan_type ) { continue; }
		jbportal_grant_membership( $user_id, $product_id );
	}
}

function jbportal_grant_membership( $user_id, $product_id ) {
	$duration = (int) get_post_meta( $product_id, '_jb_plan_duration', true );
	$expires  = $duration > 0 ? date( 'Y-m-d', strtotime( "+{$duration} days" ) ) : '';

	update_user_meta( $user_id, 'jb_plan_id', $product_id );
	update_user_meta( $user_id, 'jb_plan_type', get_post_meta( $product_id, '_jb_plan_type', true ) );
	update_user_meta( $user_id, 'jb_plan_expires', $expires );

	$limits = array(
		'jb_apply_limit'           => '_jb_apply_limit',
		'jb_services_limit'        => '_jb_services_limit',
		'jb_wishlist_limit'        => '_jb_wishlist_limit',
		'jb_follow_limit'          => '_jb_follow_limit',
		'jb_cv_downloads_limit'    => '_jb_cv_downloads',
		'jb_jobs_limit'            => '_jb_jobs_limit',
		'jb_featured_jobs_limit'   => '_jb_featured_jobs',
		'jb_see_contact_info'      => '_jb_see_contact_info',
	);
	foreach ( $limits as $user_key => $meta_key ) {
		$val = get_post_meta( $product_id, $meta_key, true );
		if ( $val !== '' ) {
			update_user_meta( $user_id, $user_key, $val );
		}
	}
	// Reset usage counters on new plan.
	update_user_meta( $user_id, 'jb_apply_count', 0 );
	update_user_meta( $user_id, 'jb_cv_downloads_count', 0 );
}

/* ── Check plan expiry on each request ─────────────────────── */

add_action( 'init', 'jbportal_check_plan_expiry' );
function jbportal_check_plan_expiry() {
	if ( ! is_user_logged_in() ) { return; }
	$uid     = get_current_user_id();
	$expires = get_user_meta( $uid, 'jb_plan_expires', true );
	if ( $expires && strtotime( $expires ) < time() ) {
		jbportal_reset_membership( $uid );
	}
}

function jbportal_reset_membership( $user_id ) {
	$free = jbportal_free_plan_limits();
	foreach ( $free as $key => $val ) {
		update_user_meta( $user_id, $key, $val );
	}
	delete_user_meta( $user_id, 'jb_plan_id' );
	delete_user_meta( $user_id, 'jb_plan_expires' );
}

function jbportal_free_plan_limits() {
	return apply_filters( 'jbportal_free_plan_limits', array(
		'jb_apply_limit'        => 10,
		'jb_services_limit'     => 1,
		'jb_wishlist_limit'     => 5,
		'jb_follow_limit'       => 5,
		'jb_cv_downloads_limit' => 5,
		'jb_jobs_limit'         => 1,
		'jb_featured_jobs_limit'=> 0,
		'jb_see_contact_info'   => '0',
	) );
}

/* ── Limit checker ─────────────────────────────────────────── */

function jbportal_membership_limit( $user_id, $type ) {
	$key = "jb_{$type}_limit";
	$val = get_user_meta( $user_id, $key, true );
	if ( $val === '' ) {
		$free = jbportal_free_plan_limits();
		$val  = $free[ $key ] ?? -1;
	}
	return (int) $val;
}

function jbportal_membership_can( $user_id, $type ) {
	$limit = jbportal_membership_limit( $user_id, $type );
	if ( $limit === -1 ) { return true; }
	$used = (int) get_user_meta( $user_id, "jb_{$type}_count", true );
	return $used < $limit;
}

function jbportal_membership_increment( $user_id, $type ) {
	$count = (int) get_user_meta( $user_id, "jb_{$type}_count", true );
	update_user_meta( $user_id, "jb_{$type}_count", $count + 1 );
}

function jbportal_can_see_contact_info( $user_id ) {
	$val = get_user_meta( $user_id, 'jb_see_contact_info', true );
	if ( $val === '' ) { $val = '0'; }
	return '1' === $val || current_user_can( 'manage_options' );
}

/* ── Gate job applications ─────────────────────────────────── */

add_filter( 'jbportal_can_apply', 'jbportal_membership_gate_apply', 10, 2 );
function jbportal_membership_gate_apply( $can, $user_id ) {
	if ( ! $can ) { return $can; }
	if ( ! jbportal_membership_can( $user_id, 'apply' ) ) {
		return false;
	}
	return true;
}

/* ── Gate bookmarks ────────────────────────────────────────── */

add_filter( 'jbportal_can_bookmark', 'jbportal_membership_gate_bookmark', 10, 2 );
function jbportal_membership_gate_bookmark( $can, $user_id ) {
	if ( ! $can ) { return $can; }
	$limit = jbportal_membership_limit( $user_id, 'wishlist' );
	if ( $limit === -1 ) { return true; }
	$saved = count( jbportal_get_user_bookmarks( $user_id ) );
	return $saved < $limit;
}

/* ── WC product meta box for plan settings ─────────────────── */

add_action( 'add_meta_boxes', 'jbportal_membership_product_metabox' );
function jbportal_membership_product_metabox() {
	if ( jbportal_wc_active() ) {
		add_meta_box( 'jbportal_membership', __( 'jbportal Membership Plan', 'jbportal' ), 'jbportal_membership_product_cb', 'product', 'normal' );
	}
}
function jbportal_membership_product_cb( $post ) {
	wp_nonce_field( 'jbportal_membership_product', 'jbportal_membership_nonce' );
	$fields = array(
		'_jb_plan_type'       => array( 'label' => __( 'Plan type', 'jbportal' ),         'type' => 'select', 'options' => array( '' => '— none —', 'candidate' => 'Candidate', 'employer' => 'Employer' ) ),
		'_jb_plan_duration'   => array( 'label' => __( 'Duration (days, 0=forever)', 'jbportal' ), 'type' => 'number' ),
		'_jb_apply_limit'     => array( 'label' => __( 'Apply limit (-1=unlimited)', 'jbportal' ),    'type' => 'number' ),
		'_jb_services_limit'  => array( 'label' => __( 'Services limit (-1=unlimited)', 'jbportal' ), 'type' => 'number' ),
		'_jb_wishlist_limit'  => array( 'label' => __( 'Wishlist limit (-1=unlimited)', 'jbportal' ), 'type' => 'number' ),
		'_jb_follow_limit'    => array( 'label' => __( 'Follow limit (-1=unlimited)', 'jbportal' ),   'type' => 'number' ),
		'_jb_cv_downloads'    => array( 'label' => __( 'CV downloads (-1=unlimited)', 'jbportal' ),   'type' => 'number' ),
		'_jb_jobs_limit'      => array( 'label' => __( 'Jobs limit (-1=unlimited)', 'jbportal' ),     'type' => 'number' ),
		'_jb_featured_jobs'   => array( 'label' => __( 'Featured jobs (-1=unlimited)', 'jbportal' ),  'type' => 'number' ),
		'_jb_see_contact_info'=> array( 'label' => __( 'Can see contact info', 'jbportal' ),          'type' => 'checkbox' ),
	);
	foreach ( $fields as $key => $f ) {
		$val = get_post_meta( $post->ID, $key, true );
		if ( 'select' === $f['type'] ) {
			echo '<p><label>' . esc_html( $f['label'] ) . '<br><select name="' . esc_attr( $key ) . '">';
			foreach ( $f['options'] as $v => $l ) {
				printf( '<option value="%s"%s>%s</option>', esc_attr( $v ), selected( $val, $v, false ), esc_html( $l ) );
			}
			echo '</select></label></p>';
		} elseif ( 'checkbox' === $f['type'] ) {
			echo '<p><label><input type="checkbox" name="' . esc_attr( $key ) . '" value="1"' . checked( $val, '1', false ) . '> ' . esc_html( $f['label'] ) . '</label></p>';
		} else {
			echo '<p><label>' . esc_html( $f['label'] ) . '<br><input type="number" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" style="width:100px"></label></p>';
		}
	}
}
add_action( 'save_post_product', 'jbportal_save_membership_product_meta' );
function jbportal_save_membership_product_meta( $post_id ) {
	if ( ! isset( $_POST['jbportal_membership_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_membership_nonce'] ) ), 'jbportal_membership_product' ) ) { return; }
	$fields = array( '_jb_plan_type', '_jb_plan_duration', '_jb_apply_limit', '_jb_services_limit', '_jb_wishlist_limit', '_jb_follow_limit', '_jb_cv_downloads', '_jb_jobs_limit', '_jb_featured_jobs' );
	foreach ( $fields as $k ) {
		if ( isset( $_POST[ $k ] ) ) { update_post_meta( $post_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) ); }
	}
	update_post_meta( $post_id, '_jb_see_contact_info', isset( $_POST['_jb_see_contact_info'] ) ? '1' : '0' );
}

/* ── Membership admin page ─────────────────────────────────── */

add_action( 'admin_menu', 'jbportal_membership_admin_menu' );
function jbportal_membership_admin_menu() {
	add_submenu_page( 'edit.php?post_type=job_listing', __( 'Membership Plans', 'jbportal' ), __( 'Membership Plans', 'jbportal' ), 'manage_options', 'jbportal-membership', 'jbportal_membership_admin_page' );
}
function jbportal_membership_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Membership Plans', 'jbportal' ); ?></h1>
		<p><?php esc_html_e( 'Create WooCommerce products and set the "jbportal Membership Plan" options on each product to define plan types and limits.', 'jbportal' ); ?></p>
		<?php if ( jbportal_wc_active() ) : ?>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product' ) ); ?>"><?php esc_html_e( '+ Create Membership Product', 'jbportal' ); ?></a>
		<?php else : ?>
			<div class="notice notice-warning"><p><?php esc_html_e( 'WooCommerce is required for paid membership plans.', 'jbportal' ); ?></p></div>
		<?php endif; ?>
	</div>
	<?php
}
