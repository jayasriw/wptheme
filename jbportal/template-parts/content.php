<?php
/**
 * Default content part for blog posts.
 *
 * @package jbportal
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'jb-post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="jb-post-thumb" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium_large' ); ?></a>
	<?php endif; ?>
	<div class="jb-post-body">
		<div class="jb-post-meta"><?php echo esc_html( get_the_date() ); ?> · <?php the_author(); ?></div>
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
		<a class="jb-read-more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more →', 'jbportal' ); ?></a>
	</div>
</article>
