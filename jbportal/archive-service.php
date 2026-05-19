<?php
/**
 * Freelance services archive.
 *
 * @package jbportal
 */

get_header();

$paged    = get_query_var( 'paged' ) ?: 1;
$category = isset( $_GET['cat'] ) ? sanitize_text_field( wp_unslash( $_GET['cat'] ) ) : '';
$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$sort     = isset( $_GET['sort'] ) ? sanitize_key( $_GET['sort'] ) : 'recent';

$args = array(
	'post_type'      => 'jb_service',
	'post_status'    => 'publish',
	'posts_per_page' => 12,
	'paged'          => $paged,
);

if ( 'price_asc' === $sort ) {
	$args['meta_key'] = '_service_price';
	$args['orderby']  = 'meta_value_num';
	$args['order']    = 'ASC';
} elseif ( 'price_desc' === $sort ) {
	$args['meta_key'] = '_service_price';
	$args['orderby']  = 'meta_value_num';
	$args['order']    = 'DESC';
} else {
	$args['orderby'] = 'date';
	$args['order']   = 'DESC';
}

if ( $search ) {
	$args['s'] = $search;
}
?>

<section class="jb-hero jb-hero-sm">
	<div class="jb-container">
		<h1><?php esc_html_e( 'Freelance Services', 'jbportal' ); ?></h1>
		<p><?php esc_html_e( 'Hire skilled professionals for your project.', 'jbportal' ); ?></p>
	</div>
</section>

<div class="jb-container" style="padding-top:2rem">
	<div class="jb-services-filter-bar">
		<form method="get" class="jb-filter-form" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;margin-bottom:2rem">
			<input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search services…', 'jbportal' ); ?>" class="jb-input">
			<select name="sort" class="jb-select">
				<option value="recent" <?php selected( $sort, 'recent' ); ?>><?php esc_html_e( 'Newest', 'jbportal' ); ?></option>
				<option value="price_asc" <?php selected( $sort, 'price_asc' ); ?>><?php esc_html_e( 'Price: Low to High', 'jbportal' ); ?></option>
				<option value="price_desc" <?php selected( $sort, 'price_desc' ); ?>><?php esc_html_e( 'Price: High to Low', 'jbportal' ); ?></option>
			</select>
			<button class="jb-btn jb-btn-primary" type="submit"><?php esc_html_e( 'Filter', 'jbportal' ); ?></button>
		</form>
	</div>

	<?php
	$q = new WP_Query( $args );
	if ( $q->have_posts() ) : ?>
		<div class="jb-services-grid">
			<?php while ( $q->have_posts() ) : $q->the_post();
				get_template_part( 'template-parts/content', 'service' );
			endwhile; ?>
		</div>
		<?php
		$big = 999999999;
		echo wp_kses_post( paginate_links( array(
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => $paged,
			'total'   => $q->max_num_pages,
		) ) );
	else : ?>
		<p><?php esc_html_e( 'No services found.', 'jbportal' ); ?></p>
	<?php endif;
	wp_reset_postdata(); ?>
</div>

<?php get_footer();
