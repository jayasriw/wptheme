<?php
/**
 * Single candidate.
 *
 * @package jbportal
 */

get_header();
while ( have_posts() ) :
	the_post();
	$cid        = get_the_ID();
	$title      = get_post_meta( $cid, '_candidate_title', true );
	$loc        = get_post_meta( $cid, '_candidate_location', true );
	$email      = get_post_meta( $cid, '_candidate_email', true );
	$phone      = get_post_meta( $cid, '_candidate_phone', true );
	$exp        = get_post_meta( $cid, '_candidate_experience', true );
	$salary     = get_post_meta( $cid, '_candidate_expected_salary', true );
	$resume     = get_post_meta( $cid, '_candidate_resume_url', true );
	$linked     = get_post_meta( $cid, '_candidate_linkedin', true );
	$site       = get_post_meta( $cid, '_candidate_website', true );
	$avail      = (bool) get_post_meta( $cid, '_candidate_available', true );
	$video_url  = get_post_meta( $cid, '_candidate_video_url', true );
	$visibility = get_post_meta( $cid, '_candidate_resume_visibility', true ) ?: 'public';

	// Determine resume/contact visibility based on _candidate_resume_visibility.
	$viewer_id   = get_current_user_id();
	$cand_author = (int) get_post_field( 'post_author', $cid );
	$is_admin    = $viewer_id && current_user_can( 'manage_options' );
	$is_owner    = $viewer_id && ( $viewer_id === $cand_author );
	if ( 'public' === $visibility ) {
		$visibility_ok = true;
	} elseif ( 'employers' === $visibility ) {
		$visibility_ok = $is_owner || $is_admin || ( $viewer_id && jbportal_user_is_employer( $viewer_id ) );
	} else { // private
		$visibility_ok = $is_owner || $is_admin;
	}
	?>
	<section class="jb-company-banner">
		<div class="jb-container jb-company-banner-inner">
			<div class="jb-company-logo"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( array( 160, 160 ) ); } ?></div>
			<div class="jb-company-headline">
				<h1><?php the_title(); ?></h1>
				<?php if ( $title ) : ?><p class="jb-candidate-title"><?php echo esc_html( $title ); ?></p><?php endif; ?>
				<div class="jb-company-meta">
					<?php if ( $loc ) : ?><span>📍 <?php echo esc_html( $loc ); ?></span><?php endif; ?>
					<?php if ( $exp ) : ?><span>⌛ <?php echo esc_html( sprintf( __( '%s yrs', 'jbportal' ), $exp ) ); ?></span><?php endif; ?>
					<?php if ( $salary ) : ?><span>💰 <?php echo esc_html( $salary ); ?></span><?php endif; ?>
					<?php if ( $avail ) : ?><span class="jb-badge jb-badge-available"><?php esc_html_e( 'Available', 'jbportal' ); ?></span><?php endif; ?>
				</div>
				<div class="jb-company-actions" style="margin-top:1rem">
					<?php if ( is_user_logged_in() ) { jbportal_follow_button( $cid ); } ?>
					<?php jbportal_render_share_buttons(); ?>
					<?php
					$cv_uid = (int) get_post_field( 'post_author', $cid );
					$viewer = get_current_user_id();
					if ( $cv_uid && ( $viewer === $cv_uid || ( $viewer && ( current_user_can( 'manage_options' ) || ( jbportal_user_is_employer( $viewer ) && jbportal_can_see_contact_info( $viewer ) ) ) ) ) ) : ?>
						<a class="jb-btn jb-btn-ghost" href="<?php echo esc_url( jbportal_pdf_cv_url( $cv_uid ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Download CV (PDF)', 'jbportal' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<div class="jb-container jb-layout-2col">
		<article class="jb-content">
			<?php if ( $video_url ) :
				$oembed = wp_oembed_get( esc_url( $video_url ) );
				if ( $oembed ) : ?>
					<div class="jb-candidate-video"><?php echo $oembed; // phpcs:ignore ?></div>
				<?php endif;
			endif; ?>
			<?php the_content(); ?>
			<?php $skills = get_the_terms( $cid, 'skill' ); if ( $skills && ! is_wp_error( $skills ) ) : ?>
				<h3><?php esc_html_e( 'Skills', 'jbportal' ); ?></h3>
				<ul class="jb-skill-list">
					<?php foreach ( $skills as $s ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $s ) ); ?>"><?php echo esc_html( $s->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</article>
		<aside class="jb-sidebar">
			<div class="jb-card">
				<h3><?php esc_html_e( 'Contact', 'jbportal' ); ?></h3>
				<?php
				$viewer_id      = get_current_user_id();
				$cand_author    = (int) get_post_field( 'post_author', $cid );
				$can_see_contact = $visibility_ok && $viewer_id && (
					$viewer_id === $cand_author
					|| current_user_can( 'manage_options' )
					|| ( jbportal_user_is_employer( $viewer_id ) && jbportal_can_see_contact_info( $viewer_id ) )
				);
				?>
				<?php if ( $can_see_contact ) : ?>
				<ul class="jb-info-list">
					<?php if ( $email )  : ?><li><strong><?php esc_html_e( 'Email', 'jbportal' ); ?></strong><span><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></span></li><?php endif; ?>
					<?php if ( $phone )  : ?><li><strong><?php esc_html_e( 'Phone', 'jbportal' ); ?></strong><span><?php echo esc_html( $phone ); ?></span></li><?php endif; ?>
					<?php if ( $linked ) : ?><li><strong>LinkedIn</strong><span><a href="<?php echo esc_url( $linked ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Profile', 'jbportal' ); ?></a></span></li><?php endif; ?>
					<?php if ( $site )   : ?><li><strong><?php esc_html_e( 'Website', 'jbportal' ); ?></strong><span><a href="<?php echo esc_url( $site ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Visit', 'jbportal' ); ?></a></span></li><?php endif; ?>
				</ul>
				<?php if ( $resume ) : ?>
					<a class="jb-btn jb-btn-primary" href="<?php echo esc_url( $resume ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Download Resume', 'jbportal' ); ?></a>
				<?php endif; ?>
				<?php elseif ( ! $visibility_ok ) : ?>
					<?php if ( 'private' === $visibility ) : ?>
						<p class="jb-contact-gate"><?php esc_html_e( 'This candidate\'s profile is private.', 'jbportal' ); ?></p>
					<?php else : ?>
						<p class="jb-contact-gate"><?php esc_html_e( 'This information is visible to employers only.', 'jbportal' ); ?></p>
					<?php endif; ?>
				<?php else : ?>
					<p class="jb-contact-gate"><?php esc_html_e( 'Upgrade your membership plan to view candidate contact details.', 'jbportal' ); ?></p>
					<?php if ( jbportal_wc_active() ) : ?>
						<a class="jb-btn jb-btn-primary jb-btn-sm" href="<?php echo esc_url( home_url( '/membership-plans/' ) ); ?>"><?php esc_html_e( 'View Plans', 'jbportal' ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php if ( is_user_logged_in() && get_the_author_meta( 'ID' ) && get_the_author_meta( 'ID' ) !== get_current_user_id() ) : ?>
				<div class="jb-card">
					<h3><?php esc_html_e( 'Message this candidate', 'jbportal' ); ?></h3>
					<form class="jb-form" method="post">
						<?php wp_nonce_field( 'jbportal_send_message', 'jbportal_message_nonce' ); ?>
						<input type="hidden" name="to_user" value="<?php echo esc_attr( get_the_author_meta( 'ID' ) ); ?>">
						<label><?php esc_html_e( 'Subject', 'jbportal' ); ?><input type="text" name="msg_subject" required></label>
						<label><?php esc_html_e( 'Message', 'jbportal' ); ?><textarea name="msg_body" rows="5" required></textarea></label>
						<button class="jb-btn jb-btn-primary" type="submit"><?php esc_html_e( 'Send Message', 'jbportal' ); ?></button>
					</form>
				</div>
			<?php endif; ?>
		</aside>
	</div>

	<div class="jb-container" style="margin-top:2rem">
		<?php jbportal_render_candidate_reviews( $cid ); ?>
	</div>

<?php endwhile;
get_footer();
