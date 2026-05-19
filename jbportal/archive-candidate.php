<?php
/**
 * Candidates archive.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-archive-hero">
	<div class="jb-container">
		<h1 class="jb-page-title"><?php esc_html_e( 'Featured Candidates', 'jbportal' ); ?></h1>
		<p class="jb-archive-subtitle"><?php esc_html_e( 'Find talented people ready to join your team.', 'jbportal' ); ?></p>
	</div>
</section>

<div class="jb-container">
	<?php if ( have_posts() ) : ?>
		<div class="jb-candidates-grid">
			<?php while ( have_posts() ) : the_post();
				$title    = get_post_meta( get_the_ID(), '_candidate_title', true );
				$location = get_post_meta( get_the_ID(), '_candidate_location', true );
				$exp      = get_post_meta( get_the_ID(), '_candidate_experience', true );
				$avail    = get_post_meta( get_the_ID(), '_candidate_available', true );
				?>
				<article class="jb-candidate-card">
					<div class="jb-candidate-avatar">
						<?php if ( has_post_thumbnail() ) { the_post_thumbnail( array( 96, 96 ) ); } else {
							echo '<span class="jb-logo-fallback">' . esc_html( strtoupper( substr( get_the_title(), 0, 1 ) ) ) . '</span>';
						} ?>
					</div>
					<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<?php if ( $title ) : ?><div class="jb-candidate-title"><?php echo esc_html( $title ); ?></div><?php endif; ?>
					<div class="jb-candidate-meta">
						<?php if ( $location ) : ?><span>📍 <?php echo esc_html( $location ); ?></span><?php endif; ?>
						<?php if ( $exp ) : ?><span>⌛ <?php echo esc_html( sprintf( __( '%s yrs', 'jbportal' ), $exp ) ); ?></span><?php endif; ?>
					</div>
					<?php if ( $avail ) : ?><span class="jb-badge jb-badge-available"><?php esc_html_e( 'Available', 'jbportal' ); ?></span><?php endif; ?>
					<a class="jb-btn jb-btn-ghost jb-btn-sm" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View Profile', 'jbportal' ); ?></a>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No candidates yet.', 'jbportal' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
