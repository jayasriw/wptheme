<?php
/**
 * Template Name: Candidate Profile (Edit)
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
		$candidates = get_posts( array(
			'post_type'      => 'candidate',
			'posts_per_page' => 1,
			'author'         => $user_id,
			'post_status'    => array( 'publish', 'draft' ),
		) );
		$candidate = $candidates ? $candidates[0] : null;

		if ( ! empty( $_POST['jbportal_candidate_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['jbportal_candidate_nonce'] ) ), 'jbportal_candidate_edit' ) ) {
			$title   = sanitize_text_field( wp_unslash( $_POST['candidate_title_post'] ?? '' ) );
			$content = wp_kses_post( wp_unslash( $_POST['candidate_content'] ?? '' ) );

			$args = array( 'post_type' => 'candidate', 'post_title' => $title, 'post_content' => $content, 'post_author' => $user_id, 'post_status' => 'publish' );
			if ( $candidate ) {
				$args['ID'] = $candidate->ID;
				wp_update_post( $args );
			} else {
				$cid_new = wp_insert_post( $args );
				$candidate = get_post( $cid_new );
			}
			$cid = $candidate->ID;
			foreach ( array( '_candidate_title', '_candidate_location', '_candidate_email', '_candidate_phone', '_candidate_experience', '_candidate_expected_salary', '_candidate_resume_url', '_candidate_linkedin', '_candidate_website' ) as $key ) {
				$short = str_replace( '_candidate_', '', $key );
				if ( isset( $_POST[ 'candidate_' . $short ] ) ) {
					$val = wp_unslash( $_POST[ 'candidate_' . $short ] );
					if ( in_array( $key, array( '_candidate_resume_url', '_candidate_linkedin', '_candidate_website' ), true ) ) {
						update_post_meta( $cid, $key, esc_url_raw( $val ) );
					} elseif ( '_candidate_email' === $key ) {
						update_post_meta( $cid, $key, sanitize_email( $val ) );
					} else {
						update_post_meta( $cid, $key, sanitize_text_field( $val ) );
					}
				}
			}
			update_post_meta( $cid, '_candidate_available', ! empty( $_POST['candidate_available'] ) ? 1 : 0 );

			if ( isset( $_POST['candidate_skills'] ) ) {
				$skills = array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_POST['candidate_skills'] ) ) ) );
				wp_set_object_terms( $cid, $skills, 'skill' );
			}

			if ( ! empty( $_FILES['candidate_photo']['name'] ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$attach_id = media_handle_upload( 'candidate_photo', $cid );
				if ( $attach_id && ! is_wp_error( $attach_id ) ) {
					set_post_thumbnail( $cid, $attach_id );
				}
			}
			if ( ! empty( $_FILES['candidate_resume']['name'] ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				$upload = wp_handle_upload( $_FILES['candidate_resume'], array( 'test_form' => false ) );
				if ( ! empty( $upload['url'] ) ) {
					update_post_meta( $cid, '_candidate_resume_url', $upload['url'] );
				}
			}
			echo '<div class="jb-notice jb-notice-success">' . esc_html__( 'Profile saved.', 'jbportal' ) . '</div>';
		}

		$t = function( $k ) use ( $candidate ) { return $candidate ? esc_attr( get_post_meta( $candidate->ID, $k, true ) ) : ''; };
		$skills_current = $candidate ? wp_get_post_terms( $candidate->ID, 'skill', array( 'fields' => 'names' ) ) : array();
		?>
		<header class="jb-page-header">
			<h1 class="jb-page-title"><?php esc_html_e( 'Edit candidate profile', 'jbportal' ); ?></h1>
			<p><?php esc_html_e( 'A complete profile gets 4x more views.', 'jbportal' ); ?></p>
		</header>

		<form class="jb-form" method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'jbportal_candidate_edit', 'jbportal_candidate_nonce' ); ?>
			<div class="jb-form-section">
				<h2><?php esc_html_e( 'About you', 'jbportal' ); ?></h2>
				<label><?php esc_html_e( 'Full name', 'jbportal' ); ?><input type="text" name="candidate_title_post" required value="<?php echo $candidate ? esc_attr( $candidate->post_title ) : ''; ?>"></label>
				<label><?php esc_html_e( 'Headline / role', 'jbportal' ); ?><input type="text" name="candidate_title" placeholder="<?php esc_attr_e( 'e.g. Senior Frontend Developer', 'jbportal' ); ?>" value="<?php echo $t( '_candidate_title' ); ?>"></label>
				<label><?php esc_html_e( 'Bio', 'jbportal' ); ?><textarea name="candidate_content" rows="6"><?php echo $candidate ? esc_textarea( $candidate->post_content ) : ''; ?></textarea></label>
				<label><?php esc_html_e( 'Photo', 'jbportal' ); ?><input type="file" name="candidate_photo" accept="image/*"></label>
				<label class="jb-check"><input type="checkbox" name="candidate_available" value="1" <?php checked( $candidate && get_post_meta( $candidate->ID, '_candidate_available', true ) ); ?>> <?php esc_html_e( 'Available for hire', 'jbportal' ); ?></label>
			</div>
			<div class="jb-form-section">
				<h2><?php esc_html_e( 'Details', 'jbportal' ); ?></h2>
				<div class="jb-grid-2">
					<label><?php esc_html_e( 'Location', 'jbportal' ); ?><input type="text" name="candidate_location" value="<?php echo $t( '_candidate_location' ); ?>"></label>
					<label><?php esc_html_e( 'Years of experience', 'jbportal' ); ?><input type="number" name="candidate_experience" value="<?php echo $t( '_candidate_experience' ); ?>"></label>
					<label><?php esc_html_e( 'Expected salary', 'jbportal' ); ?><input type="text" name="candidate_expected_salary" value="<?php echo $t( '_candidate_expected_salary' ); ?>"></label>
					<label><?php esc_html_e( 'Email', 'jbportal' ); ?><input type="email" name="candidate_email" value="<?php echo $t( '_candidate_email' ); ?>"></label>
					<label><?php esc_html_e( 'Phone', 'jbportal' ); ?><input type="tel" name="candidate_phone" value="<?php echo $t( '_candidate_phone' ); ?>"></label>
					<label>LinkedIn<input type="url" name="candidate_linkedin" value="<?php echo $t( '_candidate_linkedin' ); ?>"></label>
				</div>
				<label><?php esc_html_e( 'Resume URL', 'jbportal' ); ?><input type="url" name="candidate_resume_url" value="<?php echo $t( '_candidate_resume_url' ); ?>"></label>
				<label><?php esc_html_e( 'Or upload resume (PDF/DOC)', 'jbportal' ); ?><input type="file" name="candidate_resume" accept=".pdf,.doc,.docx"></label>
				<label><?php esc_html_e( 'Skills (comma-separated)', 'jbportal' ); ?><input type="text" name="candidate_skills" value="<?php echo esc_attr( implode( ', ', $skills_current ) ); ?>"></label>
			</div>
			<button type="submit" class="jb-btn jb-btn-primary jb-btn-lg"><?php esc_html_e( 'Save profile', 'jbportal' ); ?></button>
		</form>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
