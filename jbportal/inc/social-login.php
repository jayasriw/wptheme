<?php
/**
 * Social login + magic link.
 *
 * For Google/Facebook/LinkedIn OAuth you'll want a plugin like
 * "Nextend Social Login" — this file registers the buttons and
 * the corresponding action hooks so a plugin can drop in.
 *
 * As a built-in fallback we ship a "magic link" email login that
 * works without any third-party service.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jbportal_render_social_buttons() {
	?>
	<div class="jb-social-login">
		<?php
		/**
		 * Hook for OAuth plugins to inject Google / Facebook / LinkedIn buttons.
		 */
		do_action( 'jbportal_social_login_buttons' );
		?>
		<form method="post" class="jb-magic-form">
			<?php wp_nonce_field( 'jbportal_magic', 'jbportal_magic_nonce' ); ?>
			<input type="email" name="magic_email" placeholder="<?php esc_attr_e( 'you@example.com', 'jbportal' ); ?>" required>
			<button type="submit" class="jb-btn jb-btn-ghost"><?php esc_html_e( 'Email me a sign-in link', 'jbportal' ); ?></button>
		</form>
	</div>
	<?php
}

function jbportal_handle_magic_link() {
	if ( ! empty( $_POST['jbportal_magic_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_magic_nonce'] ) ), 'jbportal_magic' ) ) {
		$email = sanitize_email( wp_unslash( $_POST['magic_email'] ?? '' ) );
		if ( is_email( $email ) ) {
			$user = get_user_by( 'email', $email );
			if ( ! $user ) {
				// Create a candidate account.
				$user_id = wp_insert_user( array(
					'user_login' => sanitize_user( current( explode( '@', $email ) ) . '_' . wp_generate_password( 4, false ) ),
					'user_email' => $email,
					'user_pass'  => wp_generate_password( 24 ),
					'role'       => 'jb_candidate',
				) );
				$user = is_wp_error( $user_id ) ? null : get_user_by( 'id', $user_id );
			}
			if ( $user ) {
				$token = wp_generate_password( 32, false );
				set_transient( 'jbportal_magic_' . $token, $user->ID, 30 * MINUTE_IN_SECONDS );
				$link = add_query_arg( array( 'jbportal_magic' => $token ), home_url( '/' ) );
				wp_mail(
					$email,
					sprintf( __( '[%s] Your sign-in link', 'jbportal' ), get_bloginfo( 'name' ) ),
					sprintf( __( "Click to sign in (expires in 30 minutes):\n\n%s", 'jbportal' ), $link )
				);
			}
		}
		set_transient( 'jbportal_magic_sent', 1, 60 );
		wp_safe_redirect( add_query_arg( 'magic_sent', '1', wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	if ( ! empty( $_GET['jbportal_magic'] ) ) {
		$token = sanitize_text_field( wp_unslash( $_GET['jbportal_magic'] ) );
		$uid   = get_transient( 'jbportal_magic_' . $token );
		if ( $uid ) {
			delete_transient( 'jbportal_magic_' . $token );
			wp_set_current_user( (int) $uid );
			wp_set_auth_cookie( (int) $uid, true );
			wp_safe_redirect( home_url( '/dashboard/' ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'jbportal_handle_magic_link' );

function jbportal_inject_buttons_on_login() {
	echo '<div style="margin-top:1rem">';
	jbportal_render_social_buttons();
	echo '</div>';
}
add_action( 'login_form', 'jbportal_inject_buttons_on_login' );
add_action( 'register_form', 'jbportal_inject_buttons_on_login' );
