<?php
/**
 * Search form.
 *
 * @package jbportal
 */
?>
<form role="search" method="get" class="jb-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="s"><?php esc_html_e( 'Search', 'jbportal' ); ?></label>
	<input type="search" id="s" name="s" placeholder="<?php esc_attr_e( 'Search the site…', 'jbportal' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
	<button type="submit" class="jb-btn jb-btn-primary"><?php esc_html_e( 'Search', 'jbportal' ); ?></button>
</form>
