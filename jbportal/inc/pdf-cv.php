<?php
/**
 * PDF CV generator — renders a print-ready candidate profile page.
 *
 * Accessed via ?jbportal_pdf_cv=1&uid=X&nonce=Y
 * The browser's native Print → Save as PDF produces the downloadable CV.
 * A "Download CV as PDF" button triggers window.print() on this page.
 *
 * @package jbportal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'template_redirect', 'jbportal_maybe_serve_pdf_cv' );
function jbportal_maybe_serve_pdf_cv() {
	if ( empty( $_GET['jbportal_pdf_cv'] ) ) {
		return;
	}
	$uid   = (int) ( $_GET['uid'] ?? get_current_user_id() );
	$nonce = sanitize_key( wp_unslash( $_GET['nonce'] ?? '' ) );

	// Anyone can view their own CV; employer can view if logged in and CV downloads permitted.
	$viewer = get_current_user_id();
	$own    = $viewer === $uid;
	$can    = $own || ( $viewer && current_user_can( 'manage_options' ) );

	if ( ! $can && $viewer ) {
		$can = jbportal_user_is_employer( $viewer ) && jbportal_can_see_contact_info( $viewer );
	}

	if ( ! wp_verify_nonce( $nonce, 'jbportal_pdf_cv_' . $uid ) || ! $can ) {
		wp_die( esc_html__( 'You do not have permission to view this CV.', 'jbportal' ) );
	}

	// Increment employer download counter.
	if ( ! $own && $viewer ) {
		jbportal_membership_increment( $viewer, 'cv_downloads' );
	}

	// Find the candidate post for this user.
	$candidates = get_posts( array( 'post_type' => 'candidate', 'posts_per_page' => 1, 'author' => $uid, 'post_status' => 'publish' ) );
	$cpost      = $candidates ? $candidates[0] : null;
	$cid        = $cpost ? $cpost->ID : 0;

	$user    = get_userdata( $uid );
	$name    = $cpost ? $cpost->post_title : ( $user ? $user->display_name : '' );
	$title   = $cid ? get_post_meta( $cid, '_candidate_title', true ) : '';
	$loc     = $cid ? get_post_meta( $cid, '_candidate_location', true ) : '';
	$email   = $cid ? get_post_meta( $cid, '_candidate_email', true ) : ( $user ? $user->user_email : '' );
	$phone   = $cid ? get_post_meta( $cid, '_candidate_phone', true ) : '';
	$exp     = $cid ? get_post_meta( $cid, '_candidate_experience', true ) : '';
	$salary  = $cid ? get_post_meta( $cid, '_candidate_expected_salary', true ) : '';
	$linked  = $cid ? get_post_meta( $cid, '_candidate_linkedin', true ) : '';
	$site    = $cid ? get_post_meta( $cid, '_candidate_website', true ) : '';
	$content = $cpost ? apply_filters( 'the_content', $cpost->post_content ) : '';
	$skills  = $cid ? get_the_terms( $cid, 'skill' ) : array();

	// Output standalone HTML page (no WP header/footer).
	header( 'Content-Type: text/html; charset=utf-8' );
	?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_locale() ); ?>">
<head>
<meta charset="UTF-8">
<title><?php echo esc_html( $name ); ?> — CV</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 13px; color: #1a202c; line-height: 1.6; padding: 0; background: #fff; }
.cv-wrap { max-width: 780px; margin: 0 auto; padding: 40px 48px; }
.cv-header { display: flex; gap: 24px; align-items: center; border-bottom: 3px solid #0d9488; padding-bottom: 20px; margin-bottom: 24px; }
.cv-avatar img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; }
.cv-avatar-placeholder { width: 90px; height: 90px; border-radius: 50%; background: #0d9488; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 700; }
.cv-name { font-size: 26px; font-weight: 700; color: #0f172a; }
.cv-role { font-size: 15px; color: #0d9488; font-weight: 600; margin-top: 2px; }
.cv-meta { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 8px; font-size: 12px; color: #4a5568; }
.cv-meta span { display: flex; align-items: center; gap: 4px; }
.cv-section { margin-bottom: 22px; }
.cv-section h2 { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #0d9488; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 10px; }
.cv-skills { display: flex; flex-wrap: wrap; gap: 6px; }
.cv-skill { background: #ebf8f6; color: #0d9488; border-radius: 20px; padding: 2px 10px; font-size: 12px; }
.cv-content p { margin-bottom: 8px; }
.cv-print-btn { position: fixed; top: 16px; right: 16px; background: #0d9488; color: #fff; border: none; border-radius: 6px; padding: 8px 16px; font-size: 14px; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
.cv-print-btn:hover { background: #0b7a70; }
@media print {
  .cv-print-btn { display: none; }
  body { padding: 0; }
  .cv-wrap { padding: 20px 24px; }
}
</style>
</head>
<body>
<button class="cv-print-btn" onclick="window.print()"><?php esc_html_e( 'Save as PDF', 'jbportal' ); ?></button>
<div class="cv-wrap">
  <div class="cv-header">
    <div class="cv-avatar">
      <?php if ( $cid && has_post_thumbnail( $cid ) ) : ?>
        <img src="<?php echo esc_url( get_the_post_thumbnail_url( $cid, array( 90, 90 ) ) ); ?>" alt="">
      <?php else : ?>
        <div class="cv-avatar-placeholder"><?php echo esc_html( strtoupper( substr( $name, 0, 1 ) ) ); ?></div>
      <?php endif; ?>
    </div>
    <div>
      <div class="cv-name"><?php echo esc_html( $name ); ?></div>
      <?php if ( $title ) : ?><div class="cv-role"><?php echo esc_html( $title ); ?></div><?php endif; ?>
      <div class="cv-meta">
        <?php if ( $loc )    : ?><span>📍 <?php echo esc_html( $loc ); ?></span><?php endif; ?>
        <?php if ( $email )  : ?><span>✉ <?php echo esc_html( $email ); ?></span><?php endif; ?>
        <?php if ( $phone )  : ?><span>📞 <?php echo esc_html( $phone ); ?></span><?php endif; ?>
        <?php if ( $exp )    : ?><span>⌛ <?php echo esc_html( $exp ); ?> <?php esc_html_e( 'yrs exp.', 'jbportal' ); ?></span><?php endif; ?>
        <?php if ( $salary ) : ?><span>💰 <?php echo esc_html( $salary ); ?></span><?php endif; ?>
        <?php if ( $linked ) : ?><span>🔗 <?php echo esc_html( $linked ); ?></span><?php endif; ?>
        <?php if ( $site )   : ?><span>🌐 <?php echo esc_html( $site ); ?></span><?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ( $content ) : ?>
  <div class="cv-section cv-content">
    <h2><?php esc_html_e( 'Profile', 'jbportal' ); ?></h2>
    <?php echo wp_kses_post( $content ); ?>
  </div>
  <?php endif; ?>

  <?php if ( $skills && ! is_wp_error( $skills ) ) : ?>
  <div class="cv-section">
    <h2><?php esc_html_e( 'Skills', 'jbportal' ); ?></h2>
    <div class="cv-skills">
      <?php foreach ( $skills as $s ) : ?>
        <span class="cv-skill"><?php echo esc_html( $s->name ); ?></span>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
<?php
	exit;
}

/* ── Helper: build the PDF CV URL for a user ────────────────── */

function jbportal_pdf_cv_url( $user_id ) {
	return add_query_arg( array(
		'jbportal_pdf_cv' => 1,
		'uid'             => $user_id,
		'nonce'           => wp_create_nonce( 'jbportal_pdf_cv_' . $user_id ),
	), home_url( '/' ) );
}
