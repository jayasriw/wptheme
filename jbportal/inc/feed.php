<?php
/**
 * Custom RSS feed for job listings.
 *
 * Accessible at /feed/jobs/ after flushing rewrite rules.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'jbportal_register_job_feed' );
function jbportal_register_job_feed() {
	add_feed( 'jobs', 'jbportal_job_feed_render' );
}

function jbportal_job_feed_render() {
	$jobs = new WP_Query( array(
		'post_type'      => 'job_listing',
		'post_status'    => 'publish',
		'posts_per_page' => apply_filters( 'jbportal_feed_limit', 50 ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	header( 'Content-Type: ' . feed_content_type( 'rss2' ) . '; charset=' . get_option( 'blog_charset' ), true );
	echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . '"?>' . "\n";
	?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:job="https://jbportal.io/feed/ns/job/"
>
<channel>
	<title><?php bloginfo_rss( 'name' ); ?> — <?php esc_html_e( 'Job Listings', 'jbportal' ); ?></title>
	<link><?php echo esc_url( get_post_type_archive_link( 'job_listing' ) ); ?></link>
	<description><?php bloginfo_rss( 'description' ); ?></description>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<lastBuildDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false ) ); ?></lastBuildDate>
	<atom:link href="<?php echo esc_url( get_feed_link( 'jobs' ) ); ?>" rel="self" type="application/rss+xml"/>

	<?php while ( $jobs->have_posts() ) :
		$jobs->the_post();
		$jid      = get_the_ID();
		$company  = get_post_meta( $jid, '_job_company', true );
		$location = get_post_meta( $jid, '_job_location', true );
		$salary   = jbportal_get_salary( $jid );
		$deadline = get_post_meta( $jid, '_job_deadline', true );
		$remote   = (bool) get_post_meta( $jid, '_job_remote', true );
		$featured = (bool) get_post_meta( $jid, '_job_featured', true );
		$types    = get_the_terms( $jid, 'job_type' );
		$type_str = ( $types && ! is_wp_error( $types ) ) ? implode( ', ', wp_list_pluck( $types, 'name' ) ) : '';
		?>
	<item>
		<title><?php the_title_rss(); ?></title>
		<link><?php the_permalink_rss(); ?></link>
		<guid isPermaLink="true"><?php the_permalink_rss(); ?></guid>
		<pubDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ) ); ?></pubDate>
		<description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
		<content:encoded><![CDATA[<?php the_content_feed( 'rss2' ); ?>]]></content:encoded>
		<?php if ( $company )  : ?><job:company><?php echo esc_html( $company ); ?></job:company><?php endif; ?>
		<?php if ( $location ) : ?><job:location><?php echo esc_html( $location ); ?></job:location><?php endif; ?>
		<?php if ( $salary )   : ?><job:salary><?php echo esc_html( $salary ); ?></job:salary><?php endif; ?>
		<?php if ( $type_str ) : ?><job:type><?php echo esc_html( $type_str ); ?></job:type><?php endif; ?>
		<?php if ( $deadline ) : ?><job:deadline><?php echo esc_html( $deadline ); ?></job:deadline><?php endif; ?>
		<?php if ( $remote )   : ?><job:remote>true</job:remote><?php endif; ?>
		<?php if ( $featured ) : ?><job:featured>true</job:featured><?php endif; ?>
		<?php
		$categories = get_the_terms( $jid, 'job_category' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $cat ) {
				echo '<category><![CDATA[' . esc_html( $cat->name ) . ']]></category>' . "\n\t\t";
			}
		}
		?>
		<?php if ( has_post_thumbnail() ) : ?>
		<enclosure url="<?php echo esc_url( get_the_post_thumbnail_url( $jid, 'full' ) ); ?>" type="image/jpeg" length="0"/>
		<?php endif; ?>
	</item>
	<?php endwhile; wp_reset_postdata(); ?>
</channel>
</rss>
	<?php
}
