<?php
/**
 * Front page (homepage).
 *
 * @package jbportal
 */

get_header(); ?>

<section class="jb-hero">
	<div class="jb-container jb-hero-inner">
		<div class="jb-hero-copy">
			<span class="jb-eyebrow"><?php echo esc_html( get_theme_mod( 'jbportal_hero_eyebrow', __( 'The smarter way to hire', 'jbportal' ) ) ); ?></span>
			<h1 class="jb-hero-title"><?php echo wp_kses_post( get_theme_mod( 'jbportal_hero_title', __( 'Find the job that fits your life.', 'jbportal' ) ) ); ?></h1>
			<p class="jb-hero-subtitle"><?php echo wp_kses_post( get_theme_mod( 'jbportal_hero_subtitle', __( 'Discover thousands of open positions across every industry and city.', 'jbportal' ) ) ); ?></p>

			<?php get_template_part( 'template-parts/job-search' ); ?>

			<ul class="jb-hero-stats">
				<li><?php echo esc_html( get_theme_mod( 'jbportal_hero_stat_1', '25,000+ Open Jobs' ) ); ?></li>
				<li><?php echo esc_html( get_theme_mod( 'jbportal_hero_stat_2', '12,500+ Companies' ) ); ?></li>
				<li><?php echo esc_html( get_theme_mod( 'jbportal_hero_stat_3', '180,000+ Candidates' ) ); ?></li>
			</ul>
		</div>
		<div class="jb-hero-art" aria-hidden="true">
			<div class="jb-hero-card jb-hero-card-1">
				<div class="jb-hero-dot"></div>
				<div class="jb-hero-lines">
					<span></span><span></span><span></span>
				</div>
			</div>
			<div class="jb-hero-card jb-hero-card-2">
				<strong><?php esc_html_e( 'New', 'jbportal' ); ?></strong>
				<p><?php esc_html_e( 'Senior Product Designer', 'jbportal' ); ?></p>
				<small><?php esc_html_e( 'Remote · Full Time', 'jbportal' ); ?></small>
			</div>
			<div class="jb-hero-card jb-hero-card-3">
				<strong>★ 4.9</strong>
				<p><?php esc_html_e( 'Average employer rating', 'jbportal' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'Browse by category', 'jbportal' ); ?></h2>
			<p><?php esc_html_e( 'Explore opportunities across the fields you care about.', 'jbportal' ); ?></p>
		</header>
		<?php echo do_shortcode( '[jbportal_categories limit="8"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'Featured jobs', 'jbportal' ); ?></h2>
			<a class="jb-section-link" href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'View all jobs →', 'jbportal' ); ?></a>
		</header>
		<?php echo do_shortcode( '[jbportal_jobs limit="6" featured="1"]' ); ?>
		<?php
		// Fallback to latest jobs if none are featured.
		$has_featured = new WP_Query( array(
			'post_type' => 'job_listing', 'posts_per_page' => 1,
			'meta_query' => array( array( 'key' => '_job_featured', 'value' => '1' ) ),
		) );
		if ( ! $has_featured->have_posts() ) {
			echo do_shortcode( '[jbportal_jobs limit="6"]' );
		}
		wp_reset_postdata();
		?>
	</div>
</section>

<section class="jb-section jb-section-stats">
	<div class="jb-container">
		<?php echo do_shortcode( '[jbportal_stats]' ); ?>
	</div>
</section>

<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'Top companies hiring now', 'jbportal' ); ?></h2>
			<a class="jb-section-link" href="<?php echo esc_url( get_post_type_archive_link( 'company' ) ); ?>"><?php esc_html_e( 'All companies →', 'jbportal' ); ?></a>
		</header>
		<?php echo do_shortcode( '[jbportal_companies limit="8"]' ); ?>
	</div>
</section>

<section class="jb-section jb-section-cta">
	<div class="jb-container jb-cta-inner">
		<div>
			<h2><?php esc_html_e( 'Hiring? Reach the candidates you want.', 'jbportal' ); ?></h2>
			<p><?php esc_html_e( 'Post a job in minutes and get applications from qualified candidates this week.', 'jbportal' ); ?></p>
		</div>
		<div class="jb-cta-actions">
			<a class="jb-btn jb-btn-primary jb-btn-lg" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( 'Post a Job', 'jbportal' ); ?></a>
			<a class="jb-btn jb-btn-ghost jb-btn-lg" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'See Pricing', 'jbportal' ); ?></a>
		</div>
	</div>
</section>

<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'Loved by teams and candidates', 'jbportal' ); ?></h2>
		</header>
		<?php echo do_shortcode( '[jbportal_testimonials]' ); ?>
	</div>
</section>

<?php
// Blog teaser.
$blog = new WP_Query( array( 'posts_per_page' => 3, 'no_found_rows' => true ) );
if ( $blog->have_posts() ) : ?>
<section class="jb-section">
	<div class="jb-container">
		<header class="jb-section-head">
			<h2><?php esc_html_e( 'From the blog', 'jbportal' ); ?></h2>
			<a class="jb-section-link" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'All posts →', 'jbportal' ); ?></a>
		</header>
		<div class="jb-post-grid">
			<?php while ( $blog->have_posts() ) : $blog->the_post(); ?>
				<article class="jb-post-card">
					<a class="jb-post-thumb" href="<?php the_permalink(); ?>"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large' ); } ?></a>
					<div class="jb-post-body">
						<div class="jb-post-meta"><?php echo esc_html( get_the_date() ); ?></div>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
					</div>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
