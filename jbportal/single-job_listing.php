<?php
/**
 * Single job listing.
 *
 * @package jbportal
 */

get_header();
while ( have_posts() ) :
	the_post();
	$post_id  = get_the_ID();
	$company  = jbportal_get_job_company( $post_id );
	$location = jbportal_get_job_location( $post_id );
	$salary   = jbportal_get_salary( $post_id );
	$exp      = get_post_meta( $post_id, '_job_experience', true );
	$deadline = get_post_meta( $post_id, '_job_deadline', true );
	$apply_url   = get_post_meta( $post_id, '_job_apply_url', true );
	$apply_email = get_post_meta( $post_id, '_job_apply_email', true );
	$apply_phone = get_post_meta( $post_id, '_job_apply_phone', true );
	$apply_type  = get_post_meta( $post_id, '_job_apply_type', true ) ?: ( $apply_url ? 'external' : 'internal' );
	$video_url   = get_post_meta( $post_id, '_job_video_url', true );
	$allow_anon  = (bool) get_post_meta( $post_id, '_job_allow_anon_apply', true );
	$is_featured = jbportal_is_featured( $post_id );
	$is_urgent   = (bool) get_post_meta( $post_id, '_job_urgent', true );
	$is_filled   = (bool) get_post_meta( $post_id, '_job_filled', true );
	$ok_msg  = get_transient( 'jbportal_apply_ok_' . $post_id );
	$err_msg = get_transient( 'jbportal_apply_err_' . $post_id );
	if ( $ok_msg ) { delete_transient( 'jbportal_apply_ok_' . $post_id ); }
	if ( $err_msg ) { delete_transient( 'jbportal_apply_err_' . $post_id ); }
	?>

	<section class="jb-job-banner">
		<div class="jb-container jb-job-banner-inner">
			<div class="jb-job-banner-main">
				<div class="jb-job-logo">
					<?php
					if ( $company['id'] && has_post_thumbnail( $company['id'] ) ) {
						echo get_the_post_thumbnail( $company['id'], 'jbportal-job-thumb' );
					} elseif ( has_post_thumbnail() ) {
						the_post_thumbnail( 'jbportal-job-thumb' );
					} else {
						echo '<span class="jb-logo-fallback">' . esc_html( strtoupper( substr( $company['name'] ?: get_the_title(), 0, 1 ) ) ) . '</span>';
					}
					?>
				</div>
				<div class="jb-job-headline">
					<div class="jb-job-badges">
						<?php if ( $is_featured ) : ?><span class="jb-badge jb-badge-featured"><?php esc_html_e( 'Featured', 'jbportal' ); ?></span><?php endif; ?>
						<?php if ( $is_urgent ) : ?><span class="jb-badge jb-badge-urgent"><?php esc_html_e( 'Urgent', 'jbportal' ); ?></span><?php endif; ?>
						<?php if ( $is_filled ) : ?><span class="jb-badge jb-badge-filled"><?php esc_html_e( 'Position filled', 'jbportal' ); ?></span><?php endif; ?>
						<?php echo jbportal_job_type_badge( $post_id ); // phpcs:ignore ?>
					</div>
					<h1 class="jb-job-title"><?php the_title(); ?></h1>
					<div class="jb-job-meta">
						<?php if ( $company['name'] ) : ?>
							<span>🏢 <?php echo $company['url'] ? '<a href="' . esc_url( $company['url'] ) . '">' . esc_html( $company['name'] ) . '</a>' : esc_html( $company['name'] ); ?></span>
						<?php endif; ?>
						<?php if ( $location ) : ?><span>📍 <?php echo esc_html( $location ); ?></span><?php endif; ?>
						<?php if ( $salary ) : ?><span>💰 <?php echo esc_html( $salary ); ?></span><?php endif; ?>
						<?php if ( $exp ) : ?><span>⌛ <?php echo esc_html( sprintf( __( '%s yrs experience', 'jbportal' ), $exp ) ); ?></span><?php endif; ?>
						<span>🗓 <?php printf( esc_html__( 'Posted %s', 'jbportal' ), esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'jbportal' ) ) ); ?></span>
					</div>
				</div>
			</div>
			<div class="jb-job-actions">
				<?php if ( 'external' === $apply_type && $apply_url ) : ?>
					<a class="jb-btn jb-btn-primary jb-btn-lg" href="<?php echo esc_url( $apply_url ); ?>" target="_blank" rel="nofollow noopener"><?php esc_html_e( 'Apply on Site', 'jbportal' ); ?></a>
				<?php elseif ( 'email' === $apply_type && $apply_email ) : ?>
					<a class="jb-btn jb-btn-primary jb-btn-lg" href="mailto:<?php echo esc_attr( $apply_email ); ?>?subject=<?php echo esc_attr( rawurlencode( get_the_title() ) ); ?>"><?php esc_html_e( 'Apply by Email', 'jbportal' ); ?></a>
				<?php elseif ( 'phone' === $apply_type && $apply_phone ) : ?>
					<a class="jb-btn jb-btn-primary jb-btn-lg" href="tel:<?php echo esc_attr( $apply_phone ); ?>"><?php esc_html_e( 'Call to Apply', 'jbportal' ); ?></a>
				<?php else : ?>
					<a class="jb-btn jb-btn-primary jb-btn-lg" href="#apply"><?php esc_html_e( 'Apply Now', 'jbportal' ); ?></a>
				<?php endif; ?>
				<button class="jb-btn jb-btn-ghost jb-bookmark" data-job-id="<?php echo esc_attr( $post_id ); ?>">
					<span class="jb-heart">♡</span> <?php esc_html_e( 'Save', 'jbportal' ); ?>
				</button>
				<?php jbportal_render_share_buttons(); ?>
			</div>
		</div>
	</section>

	<div class="jb-container jb-layout-2col jb-job-page">
		<article class="jb-content">
			<?php if ( $video_url ) :
				$oembed = wp_oembed_get( $video_url );
				if ( $oembed ) : ?>
					<div class="jb-job-video"><?php echo $oembed; // phpcs:ignore ?></div>
				<?php endif;
			endif; ?>

			<div class="jb-job-description"><?php the_content(); ?></div>

			<?php
			$skills = get_the_terms( $post_id, 'skill' );
			if ( $skills && ! is_wp_error( $skills ) ) : ?>
				<h3><?php esc_html_e( 'Skills', 'jbportal' ); ?></h3>
				<ul class="jb-skill-list">
					<?php foreach ( $skills as $s ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $s ) ); ?>"><?php echo esc_html( $s->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<div id="apply" class="jb-apply-block">
				<h2><?php esc_html_e( 'Apply for this job', 'jbportal' ); ?></h2>
				<?php if ( $ok_msg ) : ?>
					<div class="jb-notice jb-notice-success"><?php echo esc_html( $ok_msg ); ?></div>
				<?php endif; ?>
				<?php if ( $err_msg ) : ?>
					<div class="jb-notice jb-notice-error"><?php echo esc_html( $err_msg ); ?></div>
				<?php endif; ?>

				<?php if ( ! $is_filled && 'internal' === $apply_type ) :
					$can_apply = is_user_logged_in() || $allow_anon;
					if ( ! $can_apply ) : ?>
						<p><a class="jb-btn jb-btn-primary" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in to apply', 'jbportal' ); ?></a></p>
					<?php else : ?>
				<form class="jb-form jb-apply-form" method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'jbportal_apply', 'jbportal_apply_nonce' ); ?>
					<input type="hidden" name="job_id" value="<?php echo esc_attr( $post_id ); ?>">
					<div class="jb-grid-2">
						<label><?php esc_html_e( 'Full Name', 'jbportal' ); ?><input type="text" name="applicant_name" required></label>
						<label><?php esc_html_e( 'Email', 'jbportal' ); ?><input type="email" name="applicant_email" required></label>
						<label><?php esc_html_e( 'Phone', 'jbportal' ); ?><input type="tel" name="applicant_phone"></label>
						<label><?php esc_html_e( 'Resume URL', 'jbportal' ); ?><input type="url" name="applicant_resume_url" placeholder="https://"></label>
					</div>
					<label><?php esc_html_e( 'Or upload a resume (PDF/DOC)', 'jbportal' ); ?><input type="file" name="applicant_resume" accept=".pdf,.doc,.docx,.odt,.rtf"></label>
					<label><?php esc_html_e( 'Cover Letter', 'jbportal' ); ?><textarea name="applicant_cover" rows="6" placeholder="<?php esc_attr_e( 'Tell the team why you\'re a great fit…', 'jbportal' ); ?>"></textarea></label>
					<button type="submit" class="jb-btn jb-btn-primary jb-btn-lg"><?php esc_html_e( 'Submit Application', 'jbportal' ); ?></button>
				</form>
				<?php endif; // can_apply ?>
				<?php elseif ( $is_filled ) : ?>
					<p><?php esc_html_e( 'This position is no longer accepting applications.', 'jbportal' ); ?></p>
				<?php endif; ?>
			</div>
		</article>

		<aside class="jb-sidebar">
			<div class="jb-card jb-job-aside">
				<h3><?php esc_html_e( 'Job overview', 'jbportal' ); ?></h3>
				<ul class="jb-info-list">
					<?php if ( $salary ) : ?><li><strong><?php esc_html_e( 'Salary', 'jbportal' ); ?></strong><span><?php echo esc_html( $salary ); ?></span></li><?php endif; ?>
					<?php if ( $location ) : ?><li><strong><?php esc_html_e( 'Location', 'jbportal' ); ?></strong><span><?php echo esc_html( $location ); ?></span></li><?php endif; ?>
					<?php if ( $exp ) : ?><li><strong><?php esc_html_e( 'Experience', 'jbportal' ); ?></strong><span><?php echo esc_html( $exp ); ?></span></li><?php endif; ?>
					<?php if ( $deadline ) : ?><li><strong><?php esc_html_e( 'Deadline', 'jbportal' ); ?></strong><span><?php echo esc_html( mysql2date( get_option( 'date_format' ), $deadline ) ); ?></span></li><?php endif; ?>
					<li><strong><?php esc_html_e( 'Applications', 'jbportal' ); ?></strong><span><?php echo (int) jbportal_get_application_count( $post_id ); ?></span></li>
				</ul>
			</div>

			<?php if ( $company['id'] ) : ?>
				<div class="jb-card">
					<h3><?php esc_html_e( 'About the company', 'jbportal' ); ?></h3>
					<div class="jb-aside-company">
						<?php echo get_the_post_thumbnail( $company['id'], array( 64, 64 ) ); ?>
						<div>
							<a href="<?php echo esc_url( $company['url'] ); ?>"><strong><?php echo esc_html( $company['name'] ); ?></strong></a>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $company['id'] ), 22 ) ); ?></p>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $location ) : ?>
				<div class="jb-card jb-map-card">
					<h3><?php esc_html_e( 'Location', 'jbportal' ); ?></h3>
					<iframe class="jb-map" loading="lazy" referrerpolicy="no-referrer" src="https://www.openstreetmap.org/export/embed.html?bbox=&layer=mapnik&marker=<?php echo esc_attr( urlencode( $location ) ); ?>" style="border:0;width:100%;height:200px;border-radius:8px"></iframe>
				</div>
			<?php endif; ?>

			<?php if ( is_active_sidebar( 'sidebar-jobs' ) ) { dynamic_sidebar( 'sidebar-jobs' ); } ?>
		</aside>
	</div>

	<?php
	// Related jobs.
	$cats = wp_get_post_terms( $post_id, 'job_category', array( 'fields' => 'ids' ) );
	if ( $cats ) :
		$related = new WP_Query( array(
			'post_type'      => 'job_listing',
			'posts_per_page' => 3,
			'post__not_in'   => array( $post_id ),
			'no_found_rows'  => true,
			'tax_query'      => array( array( 'taxonomy' => 'job_category', 'terms' => $cats ) ),
		) );
		if ( $related->have_posts() ) : ?>
			<section class="jb-section jb-section-alt">
				<div class="jb-container">
					<header class="jb-section-head"><h2><?php esc_html_e( 'Similar jobs', 'jbportal' ); ?></h2></header>
					<div class="jb-jobs-grid">
						<?php while ( $related->have_posts() ) : $related->the_post(); get_template_part( 'template-parts/content', 'job' ); endwhile; ?>
					</div>
				</div>
			</section>
		<?php endif;
		wp_reset_postdata();
	endif;
	?>

<?php endwhile;
get_footer();
