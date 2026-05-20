<?php
/**
 * Company card.
 *
 * @package jbportal
 */

$cid     = get_the_ID();
$size    = get_post_meta( $cid, '_company_size', true );
$founded = get_post_meta( $cid, '_company_founded', true );
$verified = (bool) get_post_meta( $cid, '_company_verified', true );

$job_count = new WP_Query( array(
	'post_type'      => 'job_listing',
	'posts_per_page' => 1,
	'fields'         => 'ids',
	'meta_query'     => array(
		'relation' => 'OR',
		array( 'key' => '_job_company_id', 'value' => $cid ),
		array( 'key' => '_job_company', 'value' => get_the_title(), 'compare' => '=' ),
	),
	'no_found_rows'  => false,
) );
$count = $job_count->found_posts;
wp_reset_postdata();
?>
<article id="post-<?php echo esc_attr( $cid ); ?>" <?php post_class( 'jb-company-card' ); ?>>
	<a class="jb-company-card-logo" href="<?php the_permalink(); ?>">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'jbportal-company-logo' );
		} else {
			echo '<span class="jb-logo-fallback">' . esc_html( strtoupper( substr( get_the_title(), 0, 1 ) ) ) . '</span>';
		}
		?>
	</a>
	<h3 class="jb-company-card-name">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		<?php if ( $verified ) : ?><span class="jb-badge jb-badge-verified" title="<?php esc_attr_e( 'Verified', 'jbportal' ); ?>">✓</span><?php endif; ?>
	</h3>
	<div class="jb-company-card-meta">
		<?php if ( $size ) : ?><span>👥 <?php echo esc_html( $size ); ?></span><?php endif; ?>
		<?php if ( $founded ) : ?><span>📅 <?php echo esc_html( $founded ); ?></span><?php endif; ?>
	</div>
	<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
	<a class="jb-company-card-cta" href="<?php the_permalink(); ?>"><?php printf( esc_html__( '%d open jobs →', 'jbportal' ), (int) $count ); ?></a>
</article>
