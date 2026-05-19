<?php
/**
 * Header.
 *
 * @package jbportal
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'jbportal' ); ?></a>

<div class="jb-topbar">
	<div class="jb-container jb-topbar-inner">
		<div class="jb-topbar-left">
			<?php $phone = get_theme_mod( 'jbportal_contact_phone' ); $email = get_theme_mod( 'jbportal_contact_email' ); ?>
			<?php if ( $phone ) : ?><span>☎ <?php echo esc_html( $phone ); ?></span><?php endif; ?>
			<?php if ( $email ) : ?><span>✉ <?php echo esc_html( $email ); ?></span><?php endif; ?>
		</div>
		<div class="jb-topbar-right">
			<?php if ( is_user_logged_in() ) : ?>
				<a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>"><?php esc_html_e( 'Dashboard', 'jbportal' ); ?></a>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log out', 'jbportal' ); ?></a>
			<?php else : ?>
				<a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Sign in', 'jbportal' ); ?></a>
				<a href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_html_e( 'Register', 'jbportal' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>

<header id="masthead" class="jb-header">
	<div class="jb-container jb-header-inner">
		<div class="jb-branding">
			<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
				<a class="jb-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="jb-logo-mark">jb</span><span class="jb-logo-word"><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
		<nav class="jb-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'jbportal' ); ?>">
			<button class="jb-nav-toggle" aria-expanded="false" aria-controls="jb-primary-menu">
				<span class="jb-nav-bar"></span><span class="jb-nav-bar"></span><span class="jb-nav-bar"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'jbportal' ); ?></span>
			</button>
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'menu_id'        => 'jb-primary-menu',
					'container'      => false,
					'menu_class'     => 'jb-menu',
				) );
			} else {
				echo '<ul id="jb-primary-menu" class="jb-menu">';
				printf( '<li><a href="%s">%s</a></li>', esc_url( home_url( '/' ) ), esc_html__( 'Home', 'jbportal' ) );
				printf( '<li><a href="%s">%s</a></li>', esc_url( get_post_type_archive_link( 'job_listing' ) ), esc_html__( 'Jobs', 'jbportal' ) );
				printf( '<li><a href="%s">%s</a></li>', esc_url( get_post_type_archive_link( 'company' ) ), esc_html__( 'Companies', 'jbportal' ) );
				printf( '<li><a href="%s">%s</a></li>', esc_url( get_post_type_archive_link( 'candidate' ) ), esc_html__( 'Candidates', 'jbportal' ) );
				echo '</ul>';
			}
			?>
		</nav>
		<div class="jb-header-cta">
			<a class="jb-btn jb-btn-ghost" href="<?php echo esc_url( home_url( '/post-a-job/' ) ); ?>"><?php esc_html_e( 'Post a Job', 'jbportal' ); ?></a>
			<a class="jb-btn jb-btn-primary" href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>"><?php esc_html_e( 'Dashboard', 'jbportal' ); ?></a>
		</div>
	</div>
</header>

<main id="main" class="jb-site-main">
