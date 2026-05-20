<?php
/**
 * Template Name: Contact
 *
 * @package jbportal
 */

get_header();
$sent = false; $err = '';
if ( ! empty( $_POST['jbportal_contact_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_contact_nonce'] ) ), 'jbportal_contact' ) ) {
	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$subject = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
	if ( $name && is_email( $email ) && $message ) {
		$to   = get_theme_mod( 'jbportal_contact_email', get_option( 'admin_email' ) );
		$body = "From: $name <$email>\n\n$message";
		$sent = wp_mail( $to, '[jbportal] ' . ( $subject ?: __( 'New contact message', 'jbportal' ) ), $body, array( 'Reply-To: ' . $email ) );
	} else {
		$err = __( 'Please complete all required fields.', 'jbportal' );
	}
}
?>
<div class="jb-container jb-layout-2col">
	<div class="jb-content">
		<header class="jb-page-header">
			<h1 class="jb-page-title"><?php esc_html_e( 'Get in touch', 'jbportal' ); ?></h1>
			<p><?php esc_html_e( 'Questions, partnerships, support — we\'d love to hear from you.', 'jbportal' ); ?></p>
		</header>
		<?php if ( $sent )  : ?><div class="jb-notice jb-notice-success"><?php esc_html_e( 'Thanks — we\'ll be in touch shortly.', 'jbportal' ); ?></div><?php endif; ?>
		<?php if ( $err )   : ?><div class="jb-notice jb-notice-error"><?php echo esc_html( $err ); ?></div><?php endif; ?>
		<form class="jb-form" method="post">
			<?php wp_nonce_field( 'jbportal_contact', 'jbportal_contact_nonce' ); ?>
			<div class="jb-grid-2">
				<label><?php esc_html_e( 'Your name', 'jbportal' ); ?><input type="text" name="name" required></label>
				<label><?php esc_html_e( 'Your email', 'jbportal' ); ?><input type="email" name="email" required></label>
			</div>
			<label><?php esc_html_e( 'Subject', 'jbportal' ); ?><input type="text" name="subject"></label>
			<label><?php esc_html_e( 'Message', 'jbportal' ); ?><textarea name="message" rows="6" required></textarea></label>
			<button type="submit" class="jb-btn jb-btn-primary"><?php esc_html_e( 'Send Message', 'jbportal' ); ?></button>
		</form>
	</div>
	<aside class="jb-sidebar">
		<div class="jb-card">
			<h3><?php esc_html_e( 'Contact details', 'jbportal' ); ?></h3>
			<ul class="jb-info-list">
				<li><strong><?php esc_html_e( 'Email', 'jbportal' ); ?></strong><span><?php echo esc_html( get_theme_mod( 'jbportal_contact_email', 'hello@jbportal.test' ) ); ?></span></li>
				<li><strong><?php esc_html_e( 'Phone', 'jbportal' ); ?></strong><span><?php echo esc_html( get_theme_mod( 'jbportal_contact_phone', '+1 (415) 555-0100' ) ); ?></span></li>
				<li><strong><?php esc_html_e( 'Address', 'jbportal' ); ?></strong><span><?php echo esc_html( get_theme_mod( 'jbportal_contact_address', '500 Market St, San Francisco, CA' ) ); ?></span></li>
			</ul>
		</div>
	</aside>
</div>
<?php get_footer(); ?>
