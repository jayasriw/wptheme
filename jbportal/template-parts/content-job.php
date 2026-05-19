<?php
/**
 * Job listing card.
 *
 * @package jbportal
 */

$post_id  = get_the_ID();
$company  = jbportal_get_job_company( $post_id );
$location = jbportal_get_job_location( $post_id );
$salary   = jbportal_get_salary( $post_id );
$featured = jbportal_is_featured( $post_id );
$remote   = (bool) get_post_meta( $post_id, '_job_remote', true );
$urgent   = (bool) get_post_meta( $post_id, '_job_urgent', true );
?>
<article id="post-<?php echo esc_attr( $post_id ); ?>" <?php post_class( 'jb-job-card' . ( $featured ? ' is-featured' : '' ) ); ?>>
	<div class="jb-job-card-logo">
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
	<div class="jb-job-card-body">
		<div class="jb-job-card-meta">
			<?php if ( $company['name'] ) : ?>
				<span class="jb-job-company"><?php echo esc_html( $company['name'] ); ?></span>
			<?php endif; ?>
			<span class="jb-job-time"><?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?> <?php esc_html_e( 'ago', 'jbportal' ); ?></span>
		</div>
		<h3 class="jb-job-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<div class="jb-job-card-info">
			<?php if ( $location ) : ?><span>📍 <?php echo esc_html( $location ); ?></span><?php endif; ?>
			<?php if ( $salary ) : ?><span>💰 <?php echo esc_html( $salary ); ?></span><?php endif; ?>
		</div>
		<div class="jb-job-card-badges">
			<?php echo jbportal_job_type_badge( $post_id ); // phpcs:ignore ?>
			<?php if ( $remote ) : ?><span class="jb-badge jb-badge-remote"><?php esc_html_e( 'Remote', 'jbportal' ); ?></span><?php endif; ?>
			<?php if ( $featured ) : ?><span class="jb-badge jb-badge-featured"><?php esc_html_e( 'Featured', 'jbportal' ); ?></span><?php endif; ?>
			<?php if ( $urgent ) : ?><span class="jb-badge jb-badge-urgent"><?php esc_html_e( 'Urgent', 'jbportal' ); ?></span><?php endif; ?>
		</div>
	</div>
	<div class="jb-job-card-actions">
		<button class="jb-bookmark jb-icon-btn" data-job-id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( 'Save job', 'jbportal' ); ?>">♡</button>
		<a class="jb-btn jb-btn-primary jb-btn-sm" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View', 'jbportal' ); ?></a>
	</div>
</article>
