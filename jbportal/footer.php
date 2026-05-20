<?php
/**
 * Footer.
 *
 * @package jbportal
 */
?>
</main>

<footer class="jb-footer" role="contentinfo">
	<div class="jb-container jb-footer-grid">
		<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
			<div class="jb-footer-col">
				<?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
					<?php dynamic_sidebar( 'footer-' . $i ); ?>
				<?php elseif ( 1 === $i ) : ?>
					<h4 class="widget-title"><?php bloginfo( 'name' ); ?></h4>
					<p><?php bloginfo( 'description' ); ?></p>
				<?php elseif ( 2 === $i ) : ?>
					<h4 class="widget-title"><?php esc_html_e( 'For Candidates', 'jbportal' ); ?></h4>
					<ul>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?>"><?php esc_html_e( 'Browse Jobs', 'jbportal' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>"><?php esc_html_e( 'My Applications', 'jbportal' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/dashboard/?tab=bookmarks' ) ); ?>"><?php esc_html_e( 'Bookmarks', 'jbportal' ); ?></a></li>
					</ul>
				<?php elseif ( 3 === $i ) : ?>
					<h4 class="widget-title"><?php esc_html_e( 'For Employers', 'jbportal' ); ?></h4>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( 'Post a Job', 'jbportal' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Pricing', 'jbportal' ); ?></a></li>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'company' ) ); ?>"><?php esc_html_e( 'Companies', 'jbportal' ); ?></a></li>
					</ul>
				<?php else : ?>
					<h4 class="widget-title"><?php esc_html_e( 'Newsletter', 'jbportal' ); ?></h4>
					<p><?php esc_html_e( 'Get hand-picked jobs in your inbox every week.', 'jbportal' ); ?></p>
					<form class="jb-newsletter">
						<input type="email" name="email" placeholder="<?php esc_attr_e( 'you@example.com', 'jbportal' ); ?>" required>
						<button type="submit" class="jb-btn jb-btn-primary"><?php esc_html_e( 'Subscribe', 'jbportal' ); ?></button>
						<span class="jb-newsletter-message"></span>
					</form>
				<?php endif; ?>
			</div>
		<?php endfor; ?>
	</div>
	<div class="jb-footer-bottom">
		<div class="jb-container">
			<p><?php echo wp_kses_post( get_theme_mod( 'jbportal_footer_copy', sprintf( __( '© %s jbportal. All rights reserved.', 'jbportal' ), gmdate( 'Y' ) ) ) ); ?></p>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'jb-footer-menu',
					'depth'          => 1,
				) );
			}
			?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
