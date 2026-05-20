<?php
/**
 * Template Name: Home — Agency
 * Design-forward, visual-heavy layout; companies first.
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-hero jb-hero-agency">
	<div class="jb-container">
		<div class="jb-hero-agency-grid">
			<div class="jb-hero-agency-copy">
				<h1 class="jb-hero-title"><?php esc_html_e( 'Creative careers worth chasing.', 'jbportal' ); ?></h1>
				<p class="jb-hero-subtitle"><?php esc_html_e( 'Design, marketing, and creative roles at the world\'s best agencies.', 'jbportal' ); ?></p>
				<?php get_template_part( 'template-parts/job-search' ); ?>
			</div>
			<div class="jb-hero-agency-features">
				<?php
				$featured = new WP_Query( array(
					'post_type'      => 'job_listing',
					'posts_per_page' => 3,
					'meta_key'       => '_job_featured',
					'meta_value'     => '1',
				) );
				while ( $featured->have_posts() ) { $featured->the_post(); ?>
					<a class="jb-hero-agency-card" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) { the_post_thumbnail( array( 40, 40 ) ); } ?>
						<span><?php the_title(); ?></span>
					</a>
				<?php } wp_reset_postdata(); ?>
			</div>
		</div>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head"><h2><?php esc_html_e( 'Top creative employers', 'jbportal' ); ?></h2></header>
		<?php echo do_shortcode( '[jbportal_companies limit="8"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'All creative roles', 'jbportal' ); ?></h2>
			<a class="jb-section-link" href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'See all →', 'jbportal' ); ?></a>
		</header>
		<?php echo do_shortcode( '[jbportal_jobs limit="9"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-cta">
	<div class="jb-container jb-cta-inner">
		<div>
			<h2><?php esc_html_e( 'Grow your team with creative talent.', 'jbportal' ); ?></h2>
			<p><?php esc_html_e( 'Reach thousands of designers, marketers, and creators.', 'jbportal' ); ?></p>
		</div>
		<a class="jb-btn jb-btn-primary jb-btn-lg" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( 'Post a Role', 'jbportal' ); ?></a>
	</div>
</section>

<?php get_footer(); ?>
