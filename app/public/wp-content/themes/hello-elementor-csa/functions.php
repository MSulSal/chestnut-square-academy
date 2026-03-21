<?php
/**
 * Theme functions for Hello Elementor CSA.
 *
 * @package hello-elementor-csa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue theme stylesheet and fonts.
 */
function csa_theme_enqueue_assets() {
	wp_enqueue_style(
		'csa-google-fonts',
		'https://fonts.googleapis.com/css2?family=Bree+Serif&family=Nunito+Sans:wght@400;600;700;800&family=Permanent+Marker&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'hello-elementor-csa-style',
		get_stylesheet_uri(),
		array( 'hello-elementor-theme-style' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'csa_theme_enqueue_assets', 20 );

/**
 * Theme supports.
 */
function csa_theme_setup() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'csa_theme_setup' );

/**
 * Render a utility top bar above the site header.
 */
function csa_theme_render_top_bar() {
	if ( is_admin() ) {
		return;
	}

	$address = get_option( 'csa_lk_business_address', '402 S. Chestnut St., McKinney, TX' );
	$phone   = get_option( 'csa_lk_business_phone', '(469) 555-0100' );
	$hours   = get_option( 'csa_lk_business_hours', 'Monday-Friday, 6:00 AM-6:00 PM' );
	$tel_uri = 'tel:' . preg_replace( '/[^0-9+]/', '', (string) $phone );

	?>
	<div class="csa-topbar" role="complementary" aria-label="Academy quick info">
		<div class="csa-topbar-inner">
			<div class="csa-topbar-meta">
				<span class="csa-topbar-item"><?php echo esc_html( $address ); ?></span>
				<span class="csa-topbar-item"><a href="<?php echo esc_url( $tel_uri ); ?>"><?php echo esc_html( $phone ); ?></a></span>
				<span class="csa-topbar-item">Today's Hours: <?php echo esc_html( $hours ); ?></span>
			</div>
			<div class="csa-topbar-links">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
				<a href="<?php echo esc_url( home_url( '/parent-resources/' ) ); ?>">Enrolled Family Resources</a>
				<a href="<?php echo esc_url( home_url( '/careers/' ) ); ?>">Careers</a>
				<a href="<?php echo esc_url( home_url( '/contact-schedule-a-tour/' ) ); ?>">Contact Us</a>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_body_open', 'csa_theme_render_top_bar' );

/**
 * Hide default theme page titles on static pages.
 *
 * Page templates already include intentional in-content headings.
 *
 * @param bool $show_title Whether to show theme-level title.
 * @return bool
 */
function csa_theme_hide_default_page_titles( $show_title ) {
	if ( is_singular( 'page' ) ) {
		return false;
	}

	return $show_title;
}
add_filter( 'hello_elementor_page_title', 'csa_theme_hide_default_page_titles' );
