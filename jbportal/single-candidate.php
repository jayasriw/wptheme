<?php
/**
 * Single candidate.
 *
 * @package jbportal
 */

get_header();
while ( have_posts() ) :
	the_post();
	$cid     = get_the_ID();
	$title   = get_post_meta( $cid, '_candidate_title', true );
	$loc     = get_post_meta( $cid, '_candidate_location', true );
	$email   = get_post_meta( $cid, '_candidate_email', true );
	$phone   = get_post_meta( $cid, '_candidate_phone', true );
	$exp     = get_post_meta( $cid, '_candidate_experience', true );
	$salary  = get_post_meta( $cid, '_candidate_expected_salary', true );
	$resume  = get_post_meta( $cid, '_candidate_resume_url', true );
	$linked  = get_post_meta( $cid, '_candidate_linkedin', true );
	$site    = get_post_meta( $cid, '_candidate_website', true );
	$avail   = (bool) get_post_meta( $cid, '_candidate_available', true );
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
				</div>
			</div>
		</div>
	</section>

	<div class="jb-container jb-layout-2col">
		<article class="jb-content">
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
				<ul class="jb-info-list">
					<?php if ( $email )  : ?><li><strong><?php esc_html_e( 'Email', 'jbportal' ); ?></strong><span><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></span></li><?php endif; ?>
					<?php if ( $phone )  : ?><li><strong><?php esc_html_e( 'Phone', 'jbportal' ); ?></strong><span><?php echo esc_html( $phone ); ?></span></li><?php endif; ?>
					<?php if ( $linked ) : ?><li><strong>LinkedIn</strong><span><a href="<?php echo esc_url( $linked ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Profile', 'jbportal' ); ?></a></span></li><?php endif; ?>
					<?php if ( $site )   : ?><li><strong><?php esc_html_e( 'Website', 'jbportal' ); ?></strong><span><a href="<?php echo esc_url( $site ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Visit', 'jbportal' ); ?></a></span></li><?php endif; ?>
				</ul>
				<?php if ( $resume ) : ?>
					<a class="jb-btn jb-btn-primary" href="<?php echo esc_url( $resume ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Download Resume', 'jbportal' ); ?></a>
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
