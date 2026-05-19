<?php
/**
 * Admin settings page for customizing email templates.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_email_templates() {
	return array(
		'new_user'        => array( __( 'New User Registered', 'jbportal' ), __( 'Welcome to {site}', 'jbportal' ), __( "Hi {name},\n\nThanks for joining {site}!", 'jbportal' ) ),
		'new_application' => array( __( 'New Application', 'jbportal' ), __( '[{site}] New application: {job}', 'jbportal' ), __( "Name: {name}\nEmail: {email}\nResume: {resume}\n\n{cover}", 'jbportal' ) ),
		'application_status' => array( __( 'Application Status Changed', 'jbportal' ), __( '[{site}] Your application: {status}', 'jbportal' ), __( "Your application for \"{job}\" is now: {status}.", 'jbportal' ) ),
		'job_published'   => array( __( 'Job Published', 'jbportal' ), __( '[{site}] Your job is live', 'jbportal' ), __( "Your job \"{job}\" is now published at {url}.", 'jbportal' ) ),
		'job_expired'     => array( __( 'Job Expired', 'jbportal' ), __( '[{site}] Your job has expired', 'jbportal' ), __( "Your job \"{job}\" has expired.", 'jbportal' ) ),
		'meeting_invite'  => array( __( 'Meeting Invitation', 'jbportal' ), __( '[{site}] Interview invitation', 'jbportal' ), __( "When: {when}\nLink: {url}\n\n{notes}", 'jbportal' ) ),
		'wire_transfer'   => array( __( 'New Wire Transfer', 'jbportal' ), __( '[{site}] New wire transfer received', 'jbportal' ), __( "A new wire transfer payment has been recorded.", 'jbportal' ) ),
	);
}

function jbportal_render_email_template( $key, $vars = array() ) {
	$opt   = get_option( 'jbportal_email_templates', array() );
	$tpl   = jbportal_email_templates();
	$entry = isset( $opt[ $key ] ) ? $opt[ $key ] : array( 'subject' => $tpl[ $key ][1], 'body' => $tpl[ $key ][2] );

	$defaults = array(
		'{site}' => get_bloginfo( 'name' ),
		'{url}'  => home_url( '/' ),
	);
	$vars = array_merge( $defaults, $vars );
	return array(
		'subject' => strtr( $entry['subject'], $vars ),
		'body'    => strtr( $entry['body'], $vars ),
	);
}

function jbportal_email_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=job_listing',
		__( 'Email Templates', 'jbportal' ),
		__( 'Email Templates', 'jbportal' ),
		'manage_options',
		'jbportal-emails',
		'jbportal_email_settings_page'
	);
}
add_action( 'admin_menu', 'jbportal_email_settings_menu' );

function jbportal_email_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$templates = jbportal_email_templates();
	$saved     = get_option( 'jbportal_email_templates', array() );

	if ( ! empty( $_POST['jbportal_email_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_email_nonce'] ) ), 'jbportal_emails' ) ) {
		$new = array();
		foreach ( $templates as $key => $row ) {
			$new[ $key ] = array(
				'subject' => sanitize_text_field( wp_unslash( $_POST[ "subject_$key" ] ?? $row[1] ) ),
				'body'    => sanitize_textarea_field( wp_unslash( $_POST[ "body_$key" ] ?? $row[2] ) ),
			);
		}
		update_option( 'jbportal_email_templates', $new );
		$saved = $new;
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Email templates saved.', 'jbportal' ) . '</p></div>';
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Email Templates', 'jbportal' ); ?></h1>
		<p><?php esc_html_e( 'Use these placeholders: {site}, {name}, {email}, {job}, {url}, {resume}, {cover}, {status}, {when}, {notes}.', 'jbportal' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'jbportal_emails', 'jbportal_email_nonce' ); ?>
			<?php foreach ( $templates as $key => $row ) :
				$subject = isset( $saved[ $key ]['subject'] ) ? $saved[ $key ]['subject'] : $row[1];
				$body    = isset( $saved[ $key ]['body'] )    ? $saved[ $key ]['body']    : $row[2];
				?>
				<h2><?php echo esc_html( $row[0] ); ?></h2>
				<table class="form-table">
					<tr><th><?php esc_html_e( 'Subject', 'jbportal' ); ?></th>
						<td><input type="text" name="subject_<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $subject ); ?>" class="regular-text"></td></tr>
					<tr><th><?php esc_html_e( 'Body', 'jbportal' ); ?></th>
						<td><textarea name="body_<?php echo esc_attr( $key ); ?>" rows="6" class="large-text"><?php echo esc_textarea( $body ); ?></textarea></td></tr>
				</table>
			<?php endforeach; ?>
			<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save templates', 'jbportal' ); ?></button></p>
		</form>
	</div>
	<?php
}
