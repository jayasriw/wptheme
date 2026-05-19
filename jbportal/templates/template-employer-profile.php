<?php
/**
 * Template Name: Employer Profile (Edit Company)
 *
 * @package jbportal
 */

get_header();
?>
<div class="jb-container jb-layout-1col jb-edit-profile">
	<?php if ( ! is_user_logged_in() ) : ?>
		<p><a class="jb-btn jb-btn-primary" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in', 'jbportal' ); ?></a></p>
	<?php else :
		$user_id = get_current_user_id();
		// Find company owned by this user (post_author).
		$companies = get_posts( array(
			'post_type'      => 'company',
			'posts_per_page' => 1,
			'author'         => $user_id,
			'post_status'    => array( 'publish', 'draft', 'pending' ),
		) );
		$company = $companies ? $companies[0] : null;

		if ( ! empty( $_POST['jbportal_company_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_company_nonce'] ) ), 'jbportal_company_edit' ) ) {
			$title   = sanitize_text_field( wp_unslash( $_POST['company_title'] ?? '' ) );
			$content = wp_kses_post( wp_unslash( $_POST['company_content'] ?? '' ) );

			$args = array( 'post_type' => 'company', 'post_title' => $title, 'post_content' => $content, 'post_author' => $user_id, 'post_status' => 'publish' );
			if ( $company ) {
				$args['ID'] = $company->ID;
				wp_update_post( $args );
			} else {
				$company_id = wp_insert_post( $args );
				$company = get_post( $company_id );
			}
			$cid = $company->ID;

			foreach ( array( '_company_website', '_company_email', '_company_phone', '_company_address', '_company_size', '_company_founded', '_company_twitter', '_company_linkedin', '_company_facebook' ) as $key ) {
				$short = str_replace( '_company_', '', $key );
				if ( isset( $_POST[ 'company_' . $short ] ) ) {
					$val = wp_unslash( $_POST[ 'company_' . $short ] );
					if ( in_array( $key, array( '_company_website', '_company_twitter', '_company_linkedin', '_company_facebook' ), true ) ) {
						update_post_meta( $cid, $key, esc_url_raw( $val ) );
					} elseif ( '_company_email' === $key ) {
						update_post_meta( $cid, $key, sanitize_email( $val ) );
					} else {
						update_post_meta( $cid, $key, sanitize_text_field( $val ) );
					}
				}
			}

			// Logo upload.
			if ( ! empty( $_FILES['company_logo']['name'] ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$attach_id = media_handle_upload( 'company_logo', $cid );
				if ( $attach_id && ! is_wp_error( $attach_id ) ) {
					set_post_thumbnail( $cid, $attach_id );
				}
			}
			echo '<div class="jb-notice jb-notice-success">' . esc_html__( 'Company profile saved.', 'jbportal' ) . '</div>';
		}

		$t = function( $k ) use ( $company ) { return $company ? esc_attr( get_post_meta( $company->ID, $k, true ) ) : ''; };
		?>

		<header class="jb-page-header">
			<h1 class="jb-page-title"><?php esc_html_e( 'Edit company profile', 'jbportal' ); ?></h1>
			<p><?php esc_html_e( 'Tell candidates who you are and how to reach you.', 'jbportal' ); ?></p>
		</header>

		<form class="jb-form" method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'jbportal_company_edit', 'jbportal_company_nonce' ); ?>
			<div class="jb-form-section">
				<h2><?php esc_html_e( 'About', 'jbportal' ); ?></h2>
				<label><?php esc_html_e( 'Company name', 'jbportal' ); ?><input type="text" name="company_title" required value="<?php echo $company ? esc_attr( $company->post_title ) : ''; ?>"></label>
				<label><?php esc_html_e( 'Description', 'jbportal' ); ?><textarea name="company_content" rows="6"><?php echo $company ? esc_textarea( $company->post_content ) : ''; ?></textarea></label>
				<label><?php esc_html_e( 'Logo', 'jbportal' ); ?><input type="file" name="company_logo" accept="image/*"></label>
			</div>
			<div class="jb-form-section">
				<h2><?php esc_html_e( 'Contact', 'jbportal' ); ?></h2>
				<div class="jb-grid-2">
					<label><?php esc_html_e( 'Website', 'jbportal' ); ?><input type="url" name="company_website" value="<?php echo $t( '_company_website' ); ?>"></label>
					<label><?php esc_html_e( 'Email', 'jbportal' ); ?><input type="email" name="company_email" value="<?php echo $t( '_company_email' ); ?>"></label>
					<label><?php esc_html_e( 'Phone', 'jbportal' ); ?><input type="tel" name="company_phone" value="<?php echo $t( '_company_phone' ); ?>"></label>
					<label><?php esc_html_e( 'Address', 'jbportal' ); ?><input type="text" name="company_address" value="<?php echo $t( '_company_address' ); ?>"></label>
					<label><?php esc_html_e( 'Company size', 'jbportal' ); ?><input type="text" name="company_size" value="<?php echo $t( '_company_size' ); ?>"></label>
					<label><?php esc_html_e( 'Founded', 'jbportal' ); ?><input type="number" name="company_founded" value="<?php echo $t( '_company_founded' ); ?>"></label>
				</div>
			</div>
			<div class="jb-form-section">
				<h2><?php esc_html_e( 'Social', 'jbportal' ); ?></h2>
				<div class="jb-grid-2">
					<label>Twitter / X<input type="url" name="company_twitter" value="<?php echo $t( '_company_twitter' ); ?>"></label>
					<label>LinkedIn<input type="url" name="company_linkedin" value="<?php echo $t( '_company_linkedin' ); ?>"></label>
					<label>Facebook<input type="url" name="company_facebook" value="<?php echo $t( '_company_facebook' ); ?>"></label>
				</div>
			</div>
			<button type="submit" class="jb-btn jb-btn-primary jb-btn-lg"><?php esc_html_e( 'Save profile', 'jbportal' ); ?></button>
		</form>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
