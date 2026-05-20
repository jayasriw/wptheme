<?php
/**
 * Hero / inline job search form.
 *
 * @package jbportal
 */

$archive = get_post_type_archive_link( 'job_listing' );
$keyword = isset( $_GET['keyword'] ) ? sanitize_text_field( wp_unslash( $_GET['keyword'] ) ) : '';
$loc     = isset( $_GET['location'] ) ? sanitize_text_field( wp_unslash( $_GET['location'] ) ) : '';
$cat_sel = isset( $_GET['category'] ) ? (array) $_GET['category'] : array();
$cat_sel = $cat_sel ? sanitize_title( reset( $cat_sel ) ) : '';
?>
<form class="jb-search-hero" method="get" action="<?php echo esc_url( $archive ); ?>">
	<div class="jb-search-hero-field jb-search-hero-keyword">
		<span class="jb-search-icon">🔎</span>
		<input type="search" name="keyword" placeholder="<?php esc_attr_e( 'Job title, keyword or company', 'jbportal' ); ?>" value="<?php echo esc_attr( $keyword ); ?>">
	</div>
	<div class="jb-search-hero-field jb-search-hero-location">
		<span class="jb-search-icon">📍</span>
		<input type="text" name="location" placeholder="<?php esc_attr_e( 'City, country or remote', 'jbportal' ); ?>" value="<?php echo esc_attr( $loc ); ?>">
	</div>
	<div class="jb-search-hero-field jb-search-hero-cat">
		<select name="category[]">
			<option value=""><?php esc_html_e( 'All categories', 'jbportal' ); ?></option>
			<?php
			$cats = get_terms( array( 'taxonomy' => 'job_category', 'hide_empty' => false ) );
			if ( $cats && ! is_wp_error( $cats ) ) {
				foreach ( $cats as $c ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $c->slug ),
						selected( $cat_sel, $c->slug, false ),
						esc_html( $c->name )
					);
				}
			}
			?>
		</select>
	</div>
	<button type="submit" class="jb-btn jb-btn-primary jb-btn-lg"><?php esc_html_e( 'Find Jobs', 'jbportal' ); ?></button>
</form>
