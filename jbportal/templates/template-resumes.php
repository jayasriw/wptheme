<?php
/**
 * Template Name: Resume Database
 *
 * @package jbportal
 */

get_header();

$keyword  = isset( $_GET['keyword'] ) ? sanitize_text_field( wp_unslash( $_GET['keyword'] ) ) : '';
$location = isset( $_GET['location'] ) ? sanitize_text_field( wp_unslash( $_GET['location'] ) ) : '';
$skill    = isset( $_GET['skill'] ) ? sanitize_title( wp_unslash( $_GET['skill'] ) ) : '';
$avail    = ! empty( $_GET['available'] );

$args = array(
	'post_type'      => 'candidate',
	'posts_per_page' => 12,
	'paged'          => max( 1, (int) get_query_var( 'paged' ) ),
);
$meta = array( 'relation' => 'AND' );
if ( $keyword )  { $args['s'] = $keyword; }
if ( $location ) { $meta[] = array( 'key' => '_candidate_location', 'value' => $location, 'compare' => 'LIKE' ); }
if ( $avail )    { $meta[] = array( 'key' => '_candidate_available', 'value' => '1' ); }
if ( count( $meta ) > 1 ) { $args['meta_query'] = $meta; }
if ( $skill )    { $args['tax_query'] = array( array( 'taxonomy' => 'skill', 'field' => 'slug', 'terms' => $skill ) ); }
$q = new WP_Query( $args );
?>
<section class="jb-archive-hero">
	<div class="jb-container">
		<h1 class="jb-page-title"><?php esc_html_e( 'Resume Database', 'jbportal' ); ?></h1>
		<p class="jb-archive-subtitle"><?php esc_html_e( 'Search candidates by keyword, location, skill or availability.', 'jbportal' ); ?></p>
		<form class="jb-search-hero" method="get">
			<div class="jb-search-hero-field"><span class="jb-search-icon">🔎</span><input type="search" name="keyword" placeholder="<?php esc_attr_e( 'Name, role or keyword', 'jbportal' ); ?>" value="<?php echo esc_attr( $keyword ); ?>"></div>
			<div class="jb-search-hero-field"><span class="jb-search-icon">📍</span><input type="text" name="location" placeholder="<?php esc_attr_e( 'Location', 'jbportal' ); ?>" value="<?php echo esc_attr( $location ); ?>"></div>
			<div class="jb-search-hero-field">
				<select name="skill">
					<option value=""><?php esc_html_e( 'Any skill', 'jbportal' ); ?></option>
					<?php foreach ( get_terms( array( 'taxonomy' => 'skill', 'hide_empty' => false ) ) as $s ) {
						printf( '<option value="%s" %s>%s</option>', esc_attr( $s->slug ), selected( $skill, $s->slug, false ), esc_html( $s->name ) );
					} ?>
				</select>
			</div>
			<button class="jb-btn jb-btn-primary jb-btn-lg"><?php esc_html_e( 'Search', 'jbportal' ); ?></button>
		</form>
		<label class="jb-check" style="margin-top:.75rem"><input type="checkbox" name="available" value="1" form="" <?php checked( $avail ); ?> onchange="this.form && this.form.submit();"> <?php esc_html_e( 'Available only', 'jbportal' ); ?></label>
	</div>
</section>

<div class="jb-container">
	<?php if ( $q->have_posts() ) : ?>
		<div class="jb-candidates-grid">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$title = get_post_meta( get_the_ID(), '_candidate_title', true );
				$loc   = get_post_meta( get_the_ID(), '_candidate_location', true );
				$exp   = get_post_meta( get_the_ID(), '_candidate_experience', true );
				?>
				<article class="jb-candidate-card">
					<div class="jb-candidate-avatar"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( array( 96, 96 ) ); } else { echo '<span class="jb-logo-fallback">' . esc_html( strtoupper( substr( get_the_title(), 0, 1 ) ) ) . '</span>'; } ?></div>
					<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<?php if ( $title ) : ?><div class="jb-candidate-title"><?php echo esc_html( $title ); ?></div><?php endif; ?>
					<div class="jb-candidate-meta">
						<?php if ( $loc ) : ?><span>📍 <?php echo esc_html( $loc ); ?></span><?php endif; ?>
						<?php if ( $exp ) : ?><span>⌛ <?php echo esc_html( sprintf( __( '%s yrs', 'jbportal' ), $exp ) ); ?></span><?php endif; ?>
					</div>
					<a class="jb-btn jb-btn-ghost jb-btn-sm" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View profile', 'jbportal' ); ?></a>
				</article>
			<?php endwhile; ?>
		</div>
		<?php
		echo paginate_links( array( // phpcs:ignore
			'total'   => $q->max_num_pages,
			'current' => max( 1, (int) get_query_var( 'paged' ) ),
		) );
		?>
	<?php else : ?>
		<p><?php esc_html_e( 'No candidates match your search.', 'jbportal' ); ?></p>
	<?php endif;
	wp_reset_postdata(); ?>
</div>

<?php get_footer(); ?>
