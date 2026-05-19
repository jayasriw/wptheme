<?php
/**
 * Social OAuth – Google Sign-In.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register admin settings page.
 */
function jbportal_social_oauth_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=job_listing',
		__( 'Social Login Settings', 'jbportal' ),
		__( 'Social Login', 'jbportal' ),
		'manage_options',
		'jbportal-social-login',
		'jbportal_social_oauth_settings_page'
	);
}
add_action( 'admin_menu', 'jbportal_social_oauth_admin_menu' );

function jbportal_social_oauth_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Access denied.', 'jbportal' ) );
	}

	if ( isset( $_POST['jbportal_social_save'] ) ) {
		if ( ! isset( $_POST['jbportal_social_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_social_nonce'] ) ), 'jbportal_social_save' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'jbportal' ) );
		}
		update_option( 'jbportal_google_client_id',     sanitize_text_field( wp_unslash( $_POST['jbportal_google_client_id'] ?? '' ) ) );
		update_option( 'jbportal_google_client_secret', sanitize_text_field( wp_unslash( $_POST['jbportal_google_client_secret'] ?? '' ) ) );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Social login settings saved.', 'jbportal' ) . '</p></div>';
	}

	$client_id     = get_option( 'jbportal_google_client_id', '' );
	$client_secret = get_option( 'jbportal_google_client_secret', '' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Social Login Settings', 'jbportal' ); ?></h1>
		<form method="post">
			<?php wp_nonce_field( 'jbportal_social_save', 'jbportal_social_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="jbportal_google_client_id"><?php esc_html_e( 'Google Client ID', 'jbportal' ); ?></label></th>
					<td><input type="text" id="jbportal_google_client_id" name="jbportal_google_client_id" value="<?php echo esc_attr( $client_id ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="jbportal_google_client_secret"><?php esc_html_e( 'Google Client Secret', 'jbportal' ); ?></label></th>
					<td><input type="text" id="jbportal_google_client_secret" name="jbportal_google_client_secret" value="<?php echo esc_attr( $client_secret ); ?>" class="regular-text"></td>
				</tr>
			</table>
			<p><?php
				$redirect_uri = jbportal_google_redirect_uri();
				printf(
					/* translators: %s: redirect URI */
					esc_html__( 'Redirect URI (add to Google Console): %s', 'jbportal' ),
					'<code>' . esc_html( $redirect_uri ) . '</code>'
				);
			?></p>
			<p class="submit"><input type="submit" name="jbportal_social_save" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'jbportal' ); ?>"></p>
		</form>
	</div>
	<?php
}

/**
 * Get the OAuth redirect URI.
 *
 * @return string
 */
function jbportal_google_redirect_uri() {
	return add_query_arg( 'jbportal_google_callback', '1', home_url( '/' ) );
}

/**
 * Build the Google OAuth authorization URL.
 *
 * @return string
 */
function jbportal_google_oauth_url() {
	$client_id = get_option( 'jbportal_google_client_id', '' );
	if ( ! $client_id ) {
		return '';
	}

	$state = wp_create_nonce( 'jbportal_google_oauth_state' );
	set_transient( 'jbportal_google_state_' . $state, '1', 600 );

	return add_query_arg( array(
		'client_id'     => rawurlencode( $client_id ),
		'redirect_uri'  => rawurlencode( jbportal_google_redirect_uri() ),
		'response_type' => 'code',
		'scope'         => rawurlencode( 'openid email profile' ),
		'state'         => rawurlencode( $state ),
	), 'https://accounts.google.com/o/oauth2/v2/auth' );
}

/**
 * Render a "Sign in with Google" button.
 */
function jbportal_google_signin_button() {
	$url = jbportal_google_oauth_url();
	if ( ! $url || is_user_logged_in() ) {
		return;
	}
	?>
	<div class="jb-google-signin" style="margin-top:1rem;text-align:center">
		<a href="<?php echo esc_url( $url ); ?>" class="jb-btn jb-btn-ghost" style="display:inline-flex;align-items:center;gap:.5rem">
			<svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z"/></svg>
			<?php esc_html_e( 'Sign in with Google', 'jbportal' ); ?>
		</a>
	</div>
	<?php
}
add_action( 'register_form', 'jbportal_google_signin_button' );
add_action( 'login_form', 'jbportal_google_signin_button' );

/**
 * Handle Google OAuth callback.
 */
function jbportal_google_oauth_callback() {
	if ( empty( $_GET['jbportal_google_callback'] ) || empty( $_GET['code'] ) ) {
		return;
	}

	$code  = sanitize_text_field( wp_unslash( $_GET['code'] ) );
	$state = sanitize_text_field( wp_unslash( $_GET['state'] ?? '' ) );

	// Verify state nonce.
	if ( ! $state || ! get_transient( 'jbportal_google_state_' . $state ) ) {
		wp_die( esc_html__( 'Invalid OAuth state. Please try again.', 'jbportal' ) );
	}
	delete_transient( 'jbportal_google_state_' . $state );

	if ( ! wp_verify_nonce( $state, 'jbportal_google_oauth_state' ) ) {
		wp_die( esc_html__( 'Security verification failed.', 'jbportal' ) );
	}

	$client_id     = get_option( 'jbportal_google_client_id', '' );
	$client_secret = get_option( 'jbportal_google_client_secret', '' );

	if ( ! $client_id || ! $client_secret ) {
		wp_die( esc_html__( 'Google OAuth is not configured.', 'jbportal' ) );
	}

	// Exchange code for token.
	$token_response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
		'body' => array(
			'code'          => $code,
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'redirect_uri'  => jbportal_google_redirect_uri(),
			'grant_type'    => 'authorization_code',
		),
	) );

	if ( is_wp_error( $token_response ) ) {
		wp_die( esc_html__( 'Failed to exchange OAuth code for token.', 'jbportal' ) );
	}

	$token_body = json_decode( wp_remote_retrieve_body( $token_response ), true );
	if ( empty( $token_body['access_token'] ) ) {
		wp_die( esc_html__( 'No access token received from Google.', 'jbportal' ) );
	}

	// Fetch user info.
	$userinfo_response = wp_remote_get( 'https://www.googleapis.com/oauth2/v3/userinfo', array(
		'headers' => array(
			'Authorization' => 'Bearer ' . sanitize_text_field( $token_body['access_token'] ),
		),
	) );

	if ( is_wp_error( $userinfo_response ) ) {
		wp_die( esc_html__( 'Failed to fetch user info from Google.', 'jbportal' ) );
	}

	$userinfo = json_decode( wp_remote_retrieve_body( $userinfo_response ), true );

	if ( empty( $userinfo['email'] ) || empty( $userinfo['sub'] ) ) {
		wp_die( esc_html__( 'Could not retrieve email from Google.', 'jbportal' ) );
	}

	$email     = sanitize_email( $userinfo['email'] );
	$google_id = sanitize_text_field( $userinfo['sub'] );
	$name      = sanitize_text_field( $userinfo['name'] ?? $email );

	// Find or create the WP user.
	$existing_user = get_user_by( 'email', $email );
	if ( $existing_user ) {
		$user_id = $existing_user->ID;
	} else {
		$username = sanitize_user( strtolower( str_replace( ' ', '', $name ) ) . '_' . wp_rand( 1000, 9999 ) );
		$user_id  = wp_create_user( $username, wp_generate_password(), $email );
		if ( is_wp_error( $user_id ) ) {
			wp_die( esc_html( $user_id->get_error_message() ) );
		}
		wp_update_user( array(
			'ID'           => $user_id,
			'display_name' => $name,
			'first_name'   => sanitize_text_field( $userinfo['given_name'] ?? '' ),
			'last_name'    => sanitize_text_field( $userinfo['family_name'] ?? '' ),
		) );
		// Default to candidate role.
		$new_user = new WP_User( $user_id );
		$new_user->set_role( 'jb_candidate' );
	}

	update_user_meta( $user_id, 'jb_google_id', $google_id );

	// Log in the user.
	wp_set_auth_cookie( $user_id, true );

	wp_safe_redirect( home_url( '/dashboard/' ) );
	exit;
}
add_action( 'template_redirect', 'jbportal_google_oauth_callback' );
