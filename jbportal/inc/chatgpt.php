<?php
/**
 * ChatGPT / OpenAI integration — AI-generated job descriptions.
 *
 * Admin settings: Jobs → AI Settings (API key).
 * Front-end: "Generate with AI" button on the Post a Job form.
 * AJAX handler calls OpenAI Chat Completions API and returns the text.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ── Admin settings page ───────────────────────────────────── */

add_action( 'admin_menu', 'jbportal_chatgpt_admin_menu' );
function jbportal_chatgpt_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=job_listing',
		__( 'AI Settings', 'jbportal' ),
		__( 'AI Settings', 'jbportal' ),
		'manage_options',
		'jbportal-ai-settings',
		'jbportal_chatgpt_settings_page'
	);
}

function jbportal_chatgpt_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	if ( ! empty( $_POST['jbportal_ai_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_ai_nonce'] ) ), 'jbportal_ai_settings' ) ) {
		update_option( 'jbportal_openai_key', sanitize_text_field( wp_unslash( $_POST['openai_key'] ?? '' ) ) );
		update_option( 'jbportal_openai_model', sanitize_text_field( wp_unslash( $_POST['openai_model'] ?? 'gpt-4o-mini' ) ) );
		update_option( 'jbportal_ai_enabled', isset( $_POST['ai_enabled'] ) ? '1' : '0' );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'jbportal' ) . '</p></div>';
	}
	$key     = get_option( 'jbportal_openai_key', '' );
	$model   = get_option( 'jbportal_openai_model', 'gpt-4o-mini' );
	$enabled = get_option( 'jbportal_ai_enabled', '0' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'AI Job Description Settings', 'jbportal' ); ?></h1>
		<form method="post">
			<?php wp_nonce_field( 'jbportal_ai_settings', 'jbportal_ai_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Enable AI', 'jbportal' ); ?></th>
					<td><input type="checkbox" name="ai_enabled" value="1" <?php checked( $enabled, '1' ); ?>></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'OpenAI API Key', 'jbportal' ); ?></th>
					<td><input type="password" name="openai_key" value="<?php echo esc_attr( $key ); ?>" style="width:400px" autocomplete="off"><br><small><?php esc_html_e( 'Obtain from platform.openai.com', 'jbportal' ); ?></small></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Model', 'jbportal' ); ?></th>
					<td>
						<select name="openai_model">
							<?php foreach ( array( 'gpt-4o-mini', 'gpt-4o', 'gpt-4-turbo', 'gpt-3.5-turbo' ) as $m ) {
								printf( '<option value="%s"%s>%s</option>', esc_attr( $m ), selected( $model, $m, false ), esc_html( $m ) );
							} ?>
						</select>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/* ── AJAX handler ──────────────────────────────────────────── */

add_action( 'wp_ajax_jbportal_generate_description', 'jbportal_ajax_generate_description' );
function jbportal_ajax_generate_description() {
	check_ajax_referer( 'jbportal_nonce', 'nonce' );

	if ( get_option( 'jbportal_ai_enabled' ) !== '1' ) {
		wp_send_json_error( array( 'message' => __( 'AI job descriptions are not enabled.', 'jbportal' ) ) );
	}

	$api_key = get_option( 'jbportal_openai_key', '' );
	if ( ! $api_key ) {
		wp_send_json_error( array( 'message' => __( 'OpenAI API key not configured. Please contact the site admin.', 'jbportal' ) ) );
	}

	$title    = sanitize_text_field( wp_unslash( $_POST['job_title'] ?? '' ) );
	$company  = sanitize_text_field( wp_unslash( $_POST['job_company'] ?? '' ) );
	$location = sanitize_text_field( wp_unslash( $_POST['job_location'] ?? '' ) );
	$type     = sanitize_text_field( wp_unslash( $_POST['job_type'] ?? '' ) );

	if ( ! $title ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a job title first.', 'jbportal' ) ) );
	}

	$prompt = sprintf(
		'Write a professional, engaging job description for a "%s" position%s%s%s. Include: role overview (2-3 sentences), key responsibilities (5-7 bullets), required qualifications (4-6 bullets), and a brief closing statement. Use plain text with clear section headings. Keep it under 400 words.',
		$title,
		$company  ? " at $company"  : '',
		$location ? " in $location" : '',
		$type     ? " ($type)"      : ''
	);

	$model = get_option( 'jbportal_openai_model', 'gpt-4o-mini' );

	$response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', array(
		'timeout' => 30,
		'headers' => array(
			'Authorization' => 'Bearer ' . $api_key,
			'Content-Type'  => 'application/json',
		),
		'body' => wp_json_encode( array(
			'model'       => $model,
			'messages'    => array( array( 'role' => 'user', 'content' => $prompt ) ),
			'max_tokens'  => 600,
			'temperature' => 0.7,
		) ),
	) );

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => $response->get_error_message() ) );
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	$text = $body['choices'][0]['message']['content'] ?? '';

	if ( ! $text ) {
		wp_send_json_error( array( 'message' => __( 'No response from AI. Please try again.', 'jbportal' ) ) );
	}

	wp_send_json_success( array( 'description' => $text ) );
}

/* ── Localize AI enabled flag for JS ────────────────────────── */

add_filter( 'jbportal_script_data', 'jbportal_chatgpt_script_data' );
function jbportal_chatgpt_script_data( $data ) {
	$data['aiEnabled'] = get_option( 'jbportal_ai_enabled' ) === '1' ? true : false;
	return $data;
}
