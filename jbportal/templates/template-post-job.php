<?php
/**
 * Template Name: Post a Job
 *
 * @package jbportal
 */

get_header();
$user_id = get_current_user_id();
$ok  = $user_id ? get_transient( 'jbportal_post_ok_' . $user_id ) : '';
$err = $user_id ? get_transient( 'jbportal_post_err_' . $user_id ) : '';
if ( $ok )  { delete_transient( 'jbportal_post_ok_' . $user_id ); }
if ( $err ) { delete_transient( 'jbportal_post_err_' . $user_id ); }
?>
<div class="jb-container jb-layout-1col jb-post-job">
	<header class="jb-page-header">
		<h1 class="jb-page-title"><?php esc_html_e( 'Post a Job', 'jbportal' ); ?></h1>
		<p><?php esc_html_e( 'Reach thousands of qualified candidates in minutes.', 'jbportal' ); ?></p>
	</header>

	<?php if ( $ok )  : ?><div class="jb-notice jb-notice-success"><?php echo esc_html( $ok ); ?></div><?php endif; ?>
	<?php if ( $err ) : ?><div class="jb-notice jb-notice-error"><?php echo esc_html( $err ); ?></div><?php endif; ?>

	<?php if ( ! is_user_logged_in() ) : ?>
		<div class="jb-card">
			<p><?php esc_html_e( 'Please sign in to post a job.', 'jbportal' ); ?></p>
			<a class="jb-btn jb-btn-primary" href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Sign in', 'jbportal' ); ?></a>
			<a class="jb-btn jb-btn-ghost" href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_html_e( 'Create account', 'jbportal' ); ?></a>
		</div>
	<?php else : ?>
		<form class="jb-form jb-post-job-form" method="post">
			<?php wp_nonce_field( 'jbportal_post_job', 'jbportal_post_job_nonce' ); ?>

			<div class="jb-form-section">
				<h2><?php esc_html_e( 'Job details', 'jbportal' ); ?></h2>
				<label><?php esc_html_e( 'Job Title', 'jbportal' ); ?>
					<input type="text" name="job_title" required placeholder="<?php esc_attr_e( 'e.g. Senior Frontend Developer', 'jbportal' ); ?>">
				</label>
				<label><?php esc_html_e( 'Job Description', 'jbportal' ); ?>
					<textarea name="job_description" rows="10" required></textarea>
				</label>
				<div class="jb-grid-2">
					<label><?php esc_html_e( 'Category', 'jbportal' ); ?>
						<select name="job_category">
							<option value=""><?php esc_html_e( 'Select category', 'jbportal' ); ?></option>
							<?php foreach ( get_terms( array( 'taxonomy' => 'job_category', 'hide_empty' => false ) ) as $t ) {
								printf( '<option value="%s">%s</option>', esc_attr( $t->slug ), esc_html( $t->name ) );
							} ?>
						</select>
					</label>
					<label><?php esc_html_e( 'Job Type', 'jbportal' ); ?>
						<select name="job_type">
							<option value=""><?php esc_html_e( 'Select type', 'jbportal' ); ?></option>
							<?php foreach ( get_terms( array( 'taxonomy' => 'job_type', 'hide_empty' => false ) ) as $t ) {
								printf( '<option value="%s">%s</option>', esc_attr( $t->slug ), esc_html( $t->name ) );
							} ?>
						</select>
					</label>
				</div>
			</div>

			<div class="jb-form-section">
				<h2><?php esc_html_e( 'Company & location', 'jbportal' ); ?></h2>
				<div class="jb-grid-2">
					<label><?php esc_html_e( 'Company Name', 'jbportal' ); ?><input type="text" name="job_company" required></label>
					<label><?php esc_html_e( 'Location', 'jbportal' ); ?><input type="text" name="job_location" placeholder="<?php esc_attr_e( 'City, country', 'jbportal' ); ?>"></label>
				</div>
				<label class="jb-check"><input type="checkbox" name="job_remote" value="1"> <?php esc_html_e( 'This is a remote-friendly position', 'jbportal' ); ?></label>
			</div>

			<div class="jb-form-section">
				<h2><?php esc_html_e( 'Compensation', 'jbportal' ); ?></h2>
				<div class="jb-grid-2">
					<label><?php esc_html_e( 'Salary Min', 'jbportal' ); ?><input type="number" name="job_salary_min" min="0"></label>
					<label><?php esc_html_e( 'Salary Max', 'jbportal' ); ?><input type="number" name="job_salary_max" min="0"></label>
				</div>
			</div>

			<div class="jb-form-section">
				<h2><?php esc_html_e( 'How to apply', 'jbportal' ); ?></h2>
				<div class="jb-grid-2">
					<label><?php esc_html_e( 'Apply Email', 'jbportal' ); ?><input type="email" name="job_apply_email"></label>
					<label><?php esc_html_e( 'External Apply URL', 'jbportal' ); ?><input type="url" name="job_apply_url" placeholder="https://"></label>
				</div>
			</div>

			<button type="submit" class="jb-btn jb-btn-primary jb-btn-lg"><?php esc_html_e( 'Submit Job', 'jbportal' ); ?></button>
		</form>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
