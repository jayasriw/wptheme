<?php
/**
 * Job alerts: users save a search and get a daily email digest of new matches.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_register_alert_cpt() {
	register_post_type( 'job_alert', array(
		'labels' => array(
			'name'          => __( 'Job Alerts', 'jbportal' ),
			'singular_name' => __( 'Job Alert', 'jbportal' ),
			'menu_name'     => __( 'Job Alerts', 'jbportal' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=job_listing',
		'supports'        => array( 'title', 'author' ),
		'capability_type' => 'post',
	) );
}
add_action( 'init', 'jbportal_register_alert_cpt' );

/**
 * Handle alert creation from a form.
 */
function jbportal_handle_save_alert() {
	if ( empty( $_POST['jbportal_alert_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_alert_nonce'] ) ), 'jbportal_save_alert' ) ) {
		return;
	}

	$email    = isset( $_POST['alert_email'] ) ? sanitize_email( wp_unslash( $_POST['alert_email'] ) ) : '';
	$keyword  = sanitize_text_field( wp_unslash( $_POST['alert_keyword'] ?? '' ) );
	$location = sanitize_text_field( wp_unslash( $_POST['alert_location'] ?? '' ) );
	$category = sanitize_title( wp_unslash( $_POST['alert_category'] ?? '' ) );
	$type     = sanitize_title( wp_unslash( $_POST['alert_type'] ?? '' ) );

	if ( ! is_email( $email ) ) {
		set_transient( 'jbportal_alert_err', __( 'Please enter a valid email address.', 'jbportal' ), 60 );
		wp_safe_redirect( wp_get_referer() ?: home_url( '/' ) );
		exit;
	}

	$id = wp_insert_post( array(
		'post_type'   => 'job_alert',
		'post_status' => 'publish',
		'post_title'  => sprintf( '%s — %s', $email, $keyword ?: __( 'Any job', 'jbportal' ) ),
		'post_author' => get_current_user_id(),
	) );

	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_alert_email', $email );
		update_post_meta( $id, '_alert_keyword', $keyword );
		update_post_meta( $id, '_alert_location', $location );
		update_post_meta( $id, '_alert_category', $category );
		update_post_meta( $id, '_alert_type', $type );
		update_post_meta( $id, '_alert_token', wp_generate_password( 24, false ) );
		set_transient( 'jbportal_alert_ok', __( 'Job alert saved. You\'ll get matching jobs by email.', 'jbportal' ), 60 );
	}

	wp_safe_redirect( wp_get_referer() ?: home_url( '/' ) );
	exit;
}
add_action( 'template_redirect', 'jbportal_handle_save_alert' );

/**
 * Daily cron — send digests.
 */
function jbportal_send_job_alerts() {
	$alerts = get_posts( array( 'post_type' => 'job_alert', 'posts_per_page' => -1, 'post_status' => 'publish' ) );
	$since  = strtotime( '-1 day' );

	foreach ( $alerts as $alert ) {
		$email    = get_post_meta( $alert->ID, '_alert_email', true );
		$keyword  = get_post_meta( $alert->ID, '_alert_keyword', true );
		$location = get_post_meta( $alert->ID, '_alert_location', true );
		$category = get_post_meta( $alert->ID, '_alert_category', true );
		$type     = get_post_meta( $alert->ID, '_alert_type', true );
		$token    = get_post_meta( $alert->ID, '_alert_token', true );

		if ( ! is_email( $email ) ) {
			continue;
		}

		$args = array(
			'post_type'      => 'job_listing',
			'posts_per_page' => 25,
			'date_query'     => array( array( 'after' => gmdate( 'Y-m-d H:i:s', $since ) ) ),
		);
		if ( $keyword )  { $args['s'] = $keyword; }
		if ( $location ) { $args['meta_query'][] = array( 'key' => '_job_location', 'value' => $location, 'compare' => 'LIKE' ); }
		if ( $category || $type ) {
			$tax_query = array( 'relation' => 'AND' );
			if ( $category ) { $tax_query[] = array( 'taxonomy' => 'job_category', 'field' => 'slug', 'terms' => $category ); }
			if ( $type )     { $tax_query[] = array( 'taxonomy' => 'job_type', 'field' => 'slug', 'terms' => $type ); }
			$args['tax_query'] = $tax_query;
		}

		$matches = get_posts( $args );
		if ( ! $matches ) {
			continue;
		}

		$lines = array();
		foreach ( $matches as $job ) {
			$company = jbportal_get_job_company( $job->ID );
			$lines[] = sprintf( "• %s — %s\n  %s", $job->post_title, $company['name'] ?: '', get_permalink( $job ) );
		}

		$unsub = add_query_arg( array( 'jbportal_unsubscribe' => 1, 'id' => $alert->ID, 'token' => $token ), home_url( '/' ) );
		$body  = sprintf(
			"%s\n\n%s\n\n--\n%s\n%s",
			sprintf( __( 'New jobs matching your alert "%s":', 'jbportal' ), $keyword ?: __( 'all jobs', 'jbportal' ) ),
			implode( "\n\n", $lines ),
			__( 'Unsubscribe from this alert:', 'jbportal' ),
			$unsub
		);

		wp_mail( $email, sprintf( __( '[%s] %d new jobs for you', 'jbportal' ), get_bloginfo( 'name' ), count( $matches ) ), $body );
	}
}
add_action( 'jbportal_daily_job_alerts', 'jbportal_send_job_alerts' );

/**
 * Handle unsubscribe link.
 */
function jbportal_handle_alert_unsubscribe() {
	if ( empty( $_GET['jbportal_unsubscribe'] ) || empty( $_GET['id'] ) || empty( $_GET['token'] ) ) {
		return;
	}
	$id    = (int) $_GET['id'];
	$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
	if ( hash_equals( (string) get_post_meta( $id, '_alert_token', true ), $token ) ) {
		wp_delete_post( $id, true );
		wp_die( esc_html__( 'You\'ve been unsubscribed from this job alert.', 'jbportal' ), '', array( 'response' => 200, 'back_link' => true ) );
	}
}
add_action( 'template_redirect', 'jbportal_handle_alert_unsubscribe' );

/**
 * Render alert subscribe form (used in widget/sidebar/shortcode).
 */
function jbportal_render_alert_form() {
	$ok  = get_transient( 'jbportal_alert_ok' );
	$err = get_transient( 'jbportal_alert_err' );
	if ( $ok )  { delete_transient( 'jbportal_alert_ok' ); }
	if ( $err ) { delete_transient( 'jbportal_alert_err' ); }
	?>
	<form class="jb-form jb-alert-form" method="post">
		<?php wp_nonce_field( 'jbportal_save_alert', 'jbportal_alert_nonce' ); ?>
		<?php if ( $ok )  : ?><div class="jb-notice jb-notice-success"><?php echo esc_html( $ok ); ?></div><?php endif; ?>
		<?php if ( $err ) : ?><div class="jb-notice jb-notice-error"><?php echo esc_html( $err ); ?></div><?php endif; ?>
		<label><?php esc_html_e( 'Email', 'jbportal' ); ?><input type="email" name="alert_email" required></label>
		<label><?php esc_html_e( 'Keyword', 'jbportal' ); ?><input type="text" name="alert_keyword" placeholder="<?php esc_attr_e( 'e.g. designer', 'jbportal' ); ?>"></label>
		<label><?php esc_html_e( 'Location', 'jbportal' ); ?><input type="text" name="alert_location"></label>
		<button type="submit" class="jb-btn jb-btn-primary"><?php esc_html_e( 'Create Alert', 'jbportal' ); ?></button>
	</form>
	<?php
}
add_shortcode( 'jbportal_job_alert', function() { ob_start(); jbportal_render_alert_form(); return ob_get_clean(); } );
