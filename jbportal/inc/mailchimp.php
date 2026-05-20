<?php
/**
 * MailChimp integration — push newsletter signups to a MailChimp list.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_mailchimp_subscribe( $email ) {
	$api_key = get_option( 'jbportal_mailchimp_api_key' );
	$list_id = get_option( 'jbportal_mailchimp_list_id' );
	if ( ! $api_key || ! $list_id ) {
		return false;
	}
	$dc = substr( strrchr( $api_key, '-' ), 1 );
	if ( ! $dc ) {
		return false;
	}
	$url = "https://{$dc}.api.mailchimp.com/3.0/lists/{$list_id}/members";

	$response = wp_remote_post( $url, array(
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $api_key ),
			'Content-Type'  => 'application/json',
		),
		'body'    => wp_json_encode( array( 'email_address' => $email, 'status' => 'subscribed' ) ),
		'timeout' => 15,
	) );
	if ( is_wp_error( $response ) ) {
		return false;
	}
	return wp_remote_retrieve_response_code( $response ) < 400;
}

/**
 * Hook into our existing newsletter AJAX so it also pushes to MailChimp.
 */
function jbportal_mailchimp_on_newsletter( $email ) {
	jbportal_mailchimp_subscribe( $email );
}
add_action( 'jbportal_newsletter_signup', 'jbportal_mailchimp_on_newsletter' );

function jbportal_mailchimp_menu() {
	add_submenu_page(
		'edit.php?post_type=job_listing',
		__( 'Integrations', 'jbportal' ),
		__( 'Integrations', 'jbportal' ),
		'manage_options',
		'jbportal-integrations',
		'jbportal_mailchimp_page'
	);
}
add_action( 'admin_menu', 'jbportal_mailchimp_menu' );

function jbportal_mailchimp_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! empty( $_POST['jbportal_mc_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_mc_nonce'] ) ), 'jbportal_mc' ) ) {
		update_option( 'jbportal_mailchimp_api_key', sanitize_text_field( wp_unslash( $_POST['mc_api'] ?? '' ) ) );
		update_option( 'jbportal_mailchimp_list_id', sanitize_text_field( wp_unslash( $_POST['mc_list'] ?? '' ) ) );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Saved.', 'jbportal' ) . '</p></div>';
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Integrations', 'jbportal' ); ?></h1>
		<h2>MailChimp</h2>
		<form method="post">
			<?php wp_nonce_field( 'jbportal_mc', 'jbportal_mc_nonce' ); ?>
			<table class="form-table">
				<tr><th><?php esc_html_e( 'API Key', 'jbportal' ); ?></th><td><input type="text" class="regular-text" name="mc_api" value="<?php echo esc_attr( get_option( 'jbportal_mailchimp_api_key' ) ); ?>"></td></tr>
				<tr><th><?php esc_html_e( 'Audience / List ID', 'jbportal' ); ?></th><td><input type="text" class="regular-text" name="mc_list" value="<?php echo esc_attr( get_option( 'jbportal_mailchimp_list_id' ) ); ?>"></td></tr>
			</table>
			<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'jbportal' ); ?></button></p>
		</form>
	</div>
	<?php
}
