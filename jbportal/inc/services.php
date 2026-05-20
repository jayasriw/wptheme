<?php
/**
 * Freelance services — candidates list services, employers purchase them.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ── CPT ───────────────────────────────────────────────────── */

add_action( 'init', 'jbportal_register_service_cpt' );
function jbportal_register_service_cpt() {
	register_post_type( 'jb_service', array(
		'labels' => array(
			'name'          => __( 'Services', 'jbportal' ),
			'singular_name' => __( 'Service', 'jbportal' ),
			'menu_name'     => __( 'Services', 'jbportal' ),
			'add_new_item'  => __( 'Add New Service', 'jbportal' ),
		),
		'public'        => true,
		'has_archive'   => 'services',
		'rewrite'       => array( 'slug' => 'service', 'with_front' => false ),
		'menu_icon'     => 'dashicons-cart',
		'menu_position' => 8,
		'show_in_menu'  => 'edit.php?post_type=job_listing',
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
		'show_in_rest'  => true,
	) );
}

/* ── Meta box ──────────────────────────────────────────────── */

add_action( 'add_meta_boxes', 'jbportal_service_meta_box' );
function jbportal_service_meta_box() {
	add_meta_box( 'jbportal_service_details', __( 'Service Details', 'jbportal' ), 'jbportal_service_meta_cb', 'jb_service', 'normal', 'high' );
}
function jbportal_service_meta_cb( $post ) {
	wp_nonce_field( 'jbportal_service_save', 'jbportal_service_nonce' );
	$fields = array(
		'_service_price'         => __( 'Price ($)', 'jbportal' ),
		'_service_delivery_days' => __( 'Delivery (days)', 'jbportal' ),
		'_service_revisions'     => __( 'Revisions', 'jbportal' ),
	);
	foreach ( $fields as $key => $label ) {
		$val = get_post_meta( $post->ID, $key, true );
		printf( '<label style="display:block;margin-bottom:8px"><strong>%s</strong><br><input type="text" name="%s" value="%s" style="width:100%%"></label>', esc_html( $label ), esc_attr( $key ), esc_attr( $val ) );
	}
	$featured = get_post_meta( $post->ID, '_service_featured', true );
	echo '<label><input type="checkbox" name="_service_featured" value="1"' . checked( $featured, '1', false ) . '> ' . esc_html__( 'Featured service', 'jbportal' ) . '</label>';
}
add_action( 'save_post_jb_service', 'jbportal_save_service_meta' );
function jbportal_save_service_meta( $post_id ) {
	if ( ! isset( $_POST['jbportal_service_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_service_nonce'] ) ), 'jbportal_service_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	foreach ( array( '_service_price', '_service_delivery_days', '_service_revisions' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
	update_post_meta( $post_id, '_service_featured', isset( $_POST['_service_featured'] ) ? '1' : '0' );
}

/* ── Front-end create / edit / delete ──────────────────────── */

add_action( 'template_redirect', 'jbportal_handle_service_submit' );
function jbportal_handle_service_submit() {
	if ( empty( $_POST['jbportal_service_nonce_fe'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_service_nonce_fe'] ) ), 'jbportal_service_fe' ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	$uid   = get_current_user_id();
	$sid   = (int) ( $_POST['service_id'] ?? 0 );
	$title = sanitize_text_field( wp_unslash( $_POST['service_title'] ?? '' ) );
	$desc  = sanitize_textarea_field( wp_unslash( $_POST['service_description'] ?? '' ) );
	$price = (float) ( $_POST['service_price'] ?? 0 );
	$days  = (int) ( $_POST['service_delivery_days'] ?? 1 );
	$revs  = (int) ( $_POST['service_revisions'] ?? 0 );

	// Check membership service limit.
	if ( ! $sid ) {
		$limit = jbportal_membership_limit( $uid, 'services' );
		if ( $limit !== -1 ) {
			$used = (int) get_user_meta( $uid, 'jb_services_count', true );
			if ( $used >= $limit ) {
				set_transient( 'jbportal_service_err_' . $uid, __( 'You have reached your service listing limit. Upgrade your plan to add more.', 'jbportal' ), 60 );
				wp_safe_redirect( add_query_arg( 'tab', 'services', wp_get_referer() ?: home_url( '/dashboard/' ) ) );
				exit;
			}
		}
	}

	if ( $sid ) {
		// Update: verify ownership.
		if ( (int) get_post_field( 'post_author', $sid ) !== $uid ) {
			wp_safe_redirect( add_query_arg( 'tab', 'services', home_url( '/dashboard/' ) ) );
			exit;
		}
		wp_update_post( array( 'ID' => $sid, 'post_title' => $title, 'post_content' => $desc ) );
	} else {
		$sid = wp_insert_post( array(
			'post_type'    => 'jb_service',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => $desc,
			'post_author'  => $uid,
		) );
		if ( $sid && ! is_wp_error( $sid ) ) {
			$used = (int) get_user_meta( $uid, 'jb_services_count', true );
			update_user_meta( $uid, 'jb_services_count', $used + 1 );
		}
	}
	if ( $sid && ! is_wp_error( $sid ) ) {
		update_post_meta( $sid, '_service_price', $price );
		update_post_meta( $sid, '_service_delivery_days', $days );
		update_post_meta( $sid, '_service_revisions', $revs );
		set_transient( 'jbportal_service_ok_' . $uid, __( 'Service saved.', 'jbportal' ), 60 );
	}
	wp_safe_redirect( add_query_arg( 'tab', 'services', home_url( '/dashboard/' ) ) );
	exit;
}

add_action( 'template_redirect', 'jbportal_handle_service_delete' );
function jbportal_handle_service_delete() {
	if ( empty( $_GET['jbportal_delete_service'] ) || empty( $_GET['_wpnonce'] ) ) {
		return;
	}
	$sid = (int) $_GET['jbportal_delete_service'];
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'jbportal_delete_service_' . $sid ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	$uid = get_current_user_id();
	if ( (int) get_post_field( 'post_author', $sid ) !== $uid ) {
		wp_safe_redirect( home_url( '/dashboard/?tab=services' ) );
		exit;
	}
	wp_delete_post( $sid, true );
	$used = max( 0, (int) get_user_meta( $uid, 'jb_services_count', true ) - 1 );
	update_user_meta( $uid, 'jb_services_count', $used );
	wp_safe_redirect( add_query_arg( 'tab', 'services', home_url( '/dashboard/' ) ) );
	exit;
}

/* ── Purchase a service (simplified order) ──────────────────── */

add_action( 'template_redirect', 'jbportal_handle_service_purchase' );
function jbportal_handle_service_purchase() {
	if ( empty( $_POST['jbportal_buy_service_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_buy_service_nonce'] ) ), 'jbportal_buy_service' ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( wp_login_url( wp_get_referer() ) );
		exit;
	}
	$service_id  = (int) ( $_POST['service_id'] ?? 0 );
	$buyer_id    = get_current_user_id();
	$message     = sanitize_textarea_field( wp_unslash( $_POST['buyer_message'] ?? '' ) );

	if ( ! $service_id || 'jb_service' !== get_post_type( $service_id ) ) {
		wp_safe_redirect( wp_get_referer() );
		exit;
	}

	// If WooCommerce is active redirect to WC checkout with service as cart item.
	if ( jbportal_wc_active() ) {
		$product_id = get_option( 'jbportal_service_wc_product' );
		if ( $product_id ) {
			WC()->cart->empty_cart();
			WC()->cart->add_to_cart( $product_id, 1, 0, array(), array( 'jb_service_id' => $service_id, 'jb_buyer_msg' => $message ) );
			wp_safe_redirect( wc_get_checkout_url() );
			exit;
		}
	}

	// Fallback: create a pending order post.
	$order_id = wp_insert_post( array(
		'post_type'    => 'jb_service_order',
		'post_status'  => 'private',
		'post_title'   => sprintf( __( 'Order: %s', 'jbportal' ), get_the_title( $service_id ) ),
		'post_author'  => $buyer_id,
	) );
	if ( $order_id && ! is_wp_error( $order_id ) ) {
		$seller_id   = (int) get_post_field( 'post_author', $service_id );
		$price       = (float) get_post_meta( $service_id, '_service_price', true );
		$commission  = (float) get_option( 'jbportal_commission_rate', 10 ) / 100;
		$seller_earn = $price * ( 1 - $commission );

		update_post_meta( $order_id, '_order_service', $service_id );
		update_post_meta( $order_id, '_order_buyer', $buyer_id );
		update_post_meta( $order_id, '_order_seller', $seller_id );
		update_post_meta( $order_id, '_order_price', $price );
		update_post_meta( $order_id, '_order_seller_earn', $seller_earn );
		update_post_meta( $order_id, '_order_message', $message );
		update_post_meta( $order_id, '_order_status', 'pending_payment' );

		// Notify seller.
		$seller = get_userdata( $seller_id );
		if ( $seller ) {
			wp_mail(
				$seller->user_email,
				sprintf( __( '[%s] New service order', 'jbportal' ), get_bloginfo( 'name' ) ),
				sprintf( __( "You received a new order for: %s\nBuyer message: %s\n\nLog in to view your dashboard.", 'jbportal' ), get_the_title( $service_id ), $message )
			);
		}
		set_transient( 'jbportal_service_buy_ok_' . $buyer_id, __( 'Order placed! The freelancer will be in touch.', 'jbportal' ), 60 );
	}
	wp_safe_redirect( get_permalink( $service_id ) );
	exit;
}

/* ── Register service order CPT ────────────────────────────── */

add_action( 'init', 'jbportal_register_service_order_cpt' );
function jbportal_register_service_order_cpt() {
	register_post_type( 'jb_service_order', array(
		'labels'          => array( 'name' => __( 'Service Orders', 'jbportal' ), 'singular_name' => __( 'Service Order', 'jbportal' ) ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=job_listing',
		'supports'        => array( 'title', 'author' ),
		'capability_type' => 'post',
	) );
}

/* ── Helpers ───────────────────────────────────────────────── */

function jbportal_get_user_services( $user_id ) {
	return get_posts( array(
		'post_type'      => 'jb_service',
		'posts_per_page' => -1,
		'author'         => $user_id,
		'post_status'    => array( 'publish', 'draft' ),
	) );
}

function jbportal_get_user_service_orders( $user_id, $role = 'seller' ) {
	$key = 'seller' === $role ? '_order_seller' : '_order_buyer';
	return get_posts( array(
		'post_type'      => 'jb_service_order',
		'posts_per_page' => -1,
		'post_status'    => 'private',
		'meta_query'     => array( array( 'key' => $key, 'value' => $user_id ) ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
}

/* ── Admin: service settings + commission ───────────────────── */

add_action( 'admin_menu', 'jbportal_service_admin_menu' );
function jbportal_service_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=job_listing',
		__( 'Service Settings', 'jbportal' ),
		__( 'Service Settings', 'jbportal' ),
		'manage_options',
		'jbportal-service-settings',
		'jbportal_service_settings_page'
	);
}
function jbportal_service_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	if ( ! empty( $_POST['jbportal_service_settings_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_service_settings_nonce'] ) ), 'jbportal_service_settings' ) ) {
		update_option( 'jbportal_commission_rate', (float) ( $_POST['commission_rate'] ?? 10 ) );
		update_option( 'jbportal_services_enabled', isset( $_POST['services_enabled'] ) ? '1' : '0' );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'jbportal' ) . '</p></div>';
	}
	$rate    = get_option( 'jbportal_commission_rate', 10 );
	$enabled = get_option( 'jbportal_services_enabled', '1' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Service & Freelance Settings', 'jbportal' ); ?></h1>
		<form method="post">
			<?php wp_nonce_field( 'jbportal_service_settings', 'jbportal_service_settings_nonce' ); ?>
			<table class="form-table">
				<tr><th><?php esc_html_e( 'Enable Freelance Services', 'jbportal' ); ?></th><td><input type="checkbox" name="services_enabled" value="1" <?php checked( $enabled, '1' ); ?>></td></tr>
				<tr><th><?php esc_html_e( 'Admin Commission Rate (%)', 'jbportal' ); ?></th><td><input type="number" name="commission_rate" value="<?php echo esc_attr( $rate ); ?>" min="0" max="100" step="0.01" style="width:80px"></td></tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/* ── Shortcode ─────────────────────────────────────────────── */

add_shortcode( 'jbportal_services', 'jbportal_shortcode_services' );
function jbportal_shortcode_services( $atts ) {
	$atts = shortcode_atts( array( 'limit' => 8, 'featured' => 0 ), $atts );
	$args = array( 'post_type' => 'jb_service', 'posts_per_page' => (int) $atts['limit'], 'post_status' => 'publish' );
	if ( $atts['featured'] ) {
		$args['meta_query'] = array( array( 'key' => '_service_featured', 'value' => '1' ) );
	}
	$q = new WP_Query( $args );
	ob_start();
	if ( $q->have_posts() ) {
		echo '<div class="jb-services-grid">';
		while ( $q->have_posts() ) { $q->the_post(); get_template_part( 'template-parts/content', 'service' ); }
		echo '</div>';
		wp_reset_postdata();
	}
	return ob_get_clean();
}
