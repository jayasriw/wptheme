<?php
/**
 * Simple GDPR-friendly cookie notice.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_render_cookie_notice() {
	if ( ! get_theme_mod( 'jbportal_cookie_enabled', true ) ) {
		return;
	}
	$text = get_theme_mod( 'jbportal_cookie_text', __( 'We use cookies to give you the best experience on our site.', 'jbportal' ) );
	$policy_url = get_theme_mod( 'jbportal_cookie_policy_url', '' );
	?>
	<div class="jb-cookie-notice" id="jb-cookie-notice" hidden>
		<p><?php echo wp_kses_post( $text ); ?>
			<?php if ( $policy_url ) : ?>
				<a href="<?php echo esc_url( $policy_url ); ?>"><?php esc_html_e( 'Learn more', 'jbportal' ); ?></a>
			<?php endif; ?>
		</p>
		<div class="jb-cookie-actions">
			<button type="button" class="jb-btn jb-btn-ghost jb-btn-sm" data-jb-cookie="decline"><?php esc_html_e( 'Decline', 'jbportal' ); ?></button>
			<button type="button" class="jb-btn jb-btn-primary jb-btn-sm" data-jb-cookie="accept"><?php esc_html_e( 'Accept', 'jbportal' ); ?></button>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'jbportal_render_cookie_notice' );

function jbportal_cookie_customizer( $wp_customize ) {
	$wp_customize->add_section( 'jbportal_cookie', array( 'title' => __( 'Cookie Notice', 'jbportal' ), 'panel' => 'jbportal_panel' ) );

	$wp_customize->add_setting( 'jbportal_cookie_enabled', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'jbportal_cookie_enabled', array( 'label' => __( 'Enable cookie banner', 'jbportal' ), 'section' => 'jbportal_cookie', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'jbportal_cookie_text', array( 'default' => __( 'We use cookies to give you the best experience on our site.', 'jbportal' ), 'sanitize_callback' => 'wp_kses_post' ) );
	$wp_customize->add_control( 'jbportal_cookie_text', array( 'label' => __( 'Banner text', 'jbportal' ), 'section' => 'jbportal_cookie', 'type' => 'textarea' ) );

	$wp_customize->add_setting( 'jbportal_cookie_policy_url', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'jbportal_cookie_policy_url', array( 'label' => __( 'Policy URL', 'jbportal' ), 'section' => 'jbportal_cookie', 'type' => 'url' ) );
}
add_action( 'customize_register', 'jbportal_cookie_customizer' );
