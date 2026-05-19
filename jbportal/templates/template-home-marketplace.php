<?php
/**
 * Template Name: Home — Freelance Marketplace
 *
 * @package jbportal
 */

get_header();
?>

<section class="jb-hero jb-hero-marketplace">
	<div class="jb-container jb-hero-inner">
		<div class="jb-hero-text">
			<h1><?php echo esc_html( get_theme_mod( 'jbportal_hero_heading', __( 'Find Freelance Talent', 'jbportal' ) ) ); ?></h1>
			<p class="jb-hero-sub"><?php echo esc_html( get_theme_mod( 'jbportal_hero_sub', __( 'Browse services from skilled professionals. Get work done quickly and professionally.', 'jbportal' ) ) ); ?></p>
			<div class="jb-hero-search">
				<form method="get" action="<?php echo esc_url( get_post_type_archive_link( 'jb_service' ) ); ?>">
					<input type="text" name="s" class="jb-hero-search-input" placeholder="<?php esc_attr_e( 'Search services…', 'jbportal' ); ?>">
					<button class="jb-btn jb-btn-primary" type="submit"><?php esc_html_e( 'Search', 'jbportal' ); ?></button>
				</form>
			</div>
		</div>
	</div>
</section>

<!-- Featured Services -->
<section class="jb-section">
	<div class="jb-container">
		<div class="jb-section-head">
			<h2><?php esc_html_e( 'Featured Services', 'jbportal' ); ?></h2>
			<a class="jb-link-more" href="<?php echo esc_url( get_post_type_archive_link( 'jb_service' ) ); ?>"><?php esc_html_e( 'Browse all', 'jbportal' ); ?> →</a>
		</div>
		<?php
		$featured_svc = new WP_Query( array(
			'post_type'      => 'jb_service',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'meta_query'     => array( array( 'key' => '_service_featured', 'value' => '1' ) ),
		) );
		if ( $featured_svc->have_posts() ) : ?>
			<div class="jb-services-grid">
				<?php while ( $featured_svc->have_posts() ) : $featured_svc->the_post();
					get_template_part( 'template-parts/content', 'service' );
				endwhile; wp_reset_postdata(); ?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'No featured services yet.', 'jbportal' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<!-- Browse by Category -->
<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<h2 class="jb-section-title"><?php esc_html_e( 'Browse Categories', 'jbportal' ); ?></h2>
		<?php echo do_shortcode( '[jbportal_categories show_count="1"]' ); ?>
	</div>
</section>

<!-- Latest Services -->
<section class="jb-section">
	<div class="jb-container">
		<div class="jb-section-head">
			<h2><?php esc_html_e( 'Latest Services', 'jbportal' ); ?></h2>
		</div>
		<?php
		$latest_svc = new WP_Query( array(
			'post_type'      => 'jb_service',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		if ( $latest_svc->have_posts() ) : ?>
			<div class="jb-services-grid jb-services-grid-4">
				<?php while ( $latest_svc->have_posts() ) : $latest_svc->the_post();
					get_template_part( 'template-parts/content', 'service' );
				endwhile; wp_reset_postdata(); ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- How it works -->
<section class="jb-section jb-section-alt">
	<div class="jb-container">
		<h2 class="jb-section-title" style="text-align:center"><?php esc_html_e( 'How it works', 'jbportal' ); ?></h2>
		<div class="jb-how-grid">
			<div class="jb-how-step">
				<div class="jb-how-icon">🔍</div>
				<h3><?php esc_html_e( 'Find a service', 'jbportal' ); ?></h3>
				<p><?php esc_html_e( 'Browse thousands of services from skilled freelancers.', 'jbportal' ); ?></p>
			</div>
			<div class="jb-how-step">
				<div class="jb-how-icon">🛒</div>
				<h3><?php esc_html_e( 'Place an order', 'jbportal' ); ?></h3>
				<p><?php esc_html_e( 'Order directly and securely through our platform.', 'jbportal' ); ?></p>
			</div>
			<div class="jb-how-step">
				<div class="jb-how-icon">✅</div>
				<h3><?php esc_html_e( 'Get it done', 'jbportal' ); ?></h3>
				<p><?php esc_html_e( 'Receive your deliverable and release payment when satisfied.', 'jbportal' ); ?></p>
			</div>
		</div>
	</div>
</section>

<!-- Stats -->
<section class="jb-section">
	<div class="jb-container">
		<?php echo do_shortcode( '[jbportal_stats]' ); ?>
	</div>
</section>

<?php get_footer();
