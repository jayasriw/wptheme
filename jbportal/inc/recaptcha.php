<?php
/**
 * Google reCAPTCHA v3 integration.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register admin settings page.
 */
function jbportal_recaptcha_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=job_listing',
		__( 'reCAPTCHA Settings', 'jbportal' ),
		__( 'reCAPTCHA', 'jbportal' ),
		'manage_options',
		'jbportal-recaptcha',
		'jbportal_recaptcha_settings_page'
	);
}
add_action( 'admin_menu', 'jbportal_recaptcha_admin_menu' );

function jbportal_recaptcha_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Access denied.', 'jbportal' ) );
	}

	if ( isset( $_POST['jbportal_recaptcha_save'] ) ) {
		if ( ! isset( $_POST['jbportal_recaptcha_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_recaptcha_nonce'] ) ), 'jbportal_recaptcha_save' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'jbportal' ) );
		}
		update_option( 'jbportal_recaptcha_site_key',   sanitize_text_field( wp_unslash( $_POST['jbportal_recaptcha_site_key'] ?? '' ) ) );
		update_option( 'jbportal_recaptcha_secret_key', sanitize_text_field( wp_unslash( $_POST['jbportal_recaptcha_secret_key'] ?? '' ) ) );
		update_option( 'jbportal_recaptcha_enabled',    ! empty( $_POST['jbportal_recaptcha_enabled'] ) ? '1' : '' );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'reCAPTCHA settings saved.', 'jbportal' ) . '</p></div>';
	}

	$site_key   = get_option( 'jbportal_recaptcha_site_key', '' );
	$secret_key = get_option( 'jbportal_recaptcha_secret_key', '' );
	$enabled    = get_option( 'jbportal_recaptcha_enabled', '' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'reCAPTCHA v3 Settings', 'jbportal' ); ?></h1>
		<form method="post">
			<?php wp_nonce_field( 'jbportal_recaptcha_save', 'jbportal_recaptcha_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="jbportal_recaptcha_enabled"><?php esc_html_e( 'Enable reCAPTCHA', 'jbportal' ); ?></label></th>
					<td><input type="checkbox" id="jbportal_recaptcha_enabled" name="jbportal_recaptcha_enabled" value="1" <?php checked( '1', $enabled ); ?>></td>
				</tr>
				<tr>
					<th scope="row"><label for="jbportal_recaptcha_site_key"><?php esc_html_e( 'Site Key', 'jbportal' ); ?></label></th>
					<td><input type="text" id="jbportal_recaptcha_site_key" name="jbportal_recaptcha_site_key" value="<?php echo esc_attr( $site_key ); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="jbportal_recaptcha_secret_key"><?php esc_html_e( 'Secret Key', 'jbportal' ); ?></label></th>
					<td><input type="text" id="jbportal_recaptcha_secret_key" name="jbportal_recaptcha_secret_key" value="<?php echo esc_attr( $secret_key ); ?>" class="regular-text"></td>
				</tr>
			</table>
			<p class="submit"><input type="submit" name="jbportal_recaptcha_save" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'jbportal' ); ?>"></p>
		</form>
	</div>
	<?php
}

/**
 * Verify a reCAPTCHA v3 token.
 *
 * @param string $token The reCAPTCHA response token from the client.
 * @return bool True if score >= 0.5.
 */
function jbportal_recaptcha_verify( $token ) {
	$secret_key = get_option( 'jbportal_recaptcha_secret_key', '' );
	if ( ! $secret_key || ! $token ) {
		return false;
	}

	$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
		'body' => array(
			'secret'   => $secret_key,
			'response' => sanitize_text_field( $token ),
		),
	) );

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( empty( $data['success'] ) ) {
		return false;
	}

	$score = isset( $data['score'] ) ? (float) $data['score'] : 0.0;
	return $score >= 0.5;
}

/**
 * Enqueue reCAPTCHA v3 script on the front end if enabled.
 */
function jbportal_recaptcha_enqueue() {
	$enabled  = get_option( 'jbportal_recaptcha_enabled', '' );
	$site_key = get_option( 'jbportal_recaptcha_site_key', '' );

	if ( ! $enabled || ! $site_key ) {
		return;
	}

	wp_enqueue_script(
		'google-recaptcha-v3',
		'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $site_key ),
		array(),
		null,
		true
	);

	wp_add_inline_script( 'google-recaptcha-v3', sprintf(
		"grecaptcha.ready(function(){
			document.querySelectorAll('.jb-form').forEach(function(form){
				form.addEventListener('submit',function(e){
					e.preventDefault();
					var f=this;
					grecaptcha.execute(%s,{action:'submit'}).then(function(token){
						var inp=f.querySelector('[name=\"g-recaptcha-response\"]');
						if(!inp){inp=document.createElement('input');inp.type='hidden';inp.name='g-recaptcha-response';f.appendChild(inp);}
						inp.value=token;
						f.submit();
					});
				});
			});
		});",
		wp_json_encode( $site_key )
	) );
}
add_action( 'wp_enqueue_scripts', 'jbportal_recaptcha_enqueue' );
