<?php
/**
 * Custom footer for Chestnut mock theme.
 *
 * @package HelloElementorChestnutMock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$home_url         = home_url( '/' );
$contact_url      = home_url( '/contact-us/' );
$privacy_url      = home_url( '/privacy-policy/' );
$footer_logo_default = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/new-logo-csa-tree.png';
$footer_logo         = apply_filters( 'kms_asset_url', $footer_logo_default, 'footer_logo' );
$defaults            = function_exists( 'csa_site_footer_text_defaults' ) ? csa_site_footer_text_defaults() : array();
$footer_school_name  = get_theme_mod( 'csa_site_footer_school_name', isset( $defaults['school_name'] ) ? $defaults['school_name'] : 'Chestnut Square Academy' );
$footer_address_1    = get_theme_mod( 'csa_site_footer_address_1', isset( $defaults['address_1'] ) ? $defaults['address_1'] : '402 S. Chestnut St.' );
$footer_address_2    = get_theme_mod( 'csa_site_footer_address_2', isset( $defaults['address_2'] ) ? $defaults['address_2'] : 'McKinney, TX' );
$footer_hours        = get_theme_mod( 'csa_site_footer_hours', isset( $defaults['hours'] ) ? $defaults['hours'] : 'Monday-Friday: 6:00 AM-6:00 PM' );
$footer_copyright    = get_theme_mod( 'csa_site_footer_copyright', isset( $defaults['copyright'] ) ? $defaults['copyright'] : '&copy; ' . gmdate( 'Y' ) . ' Chestnut Square Academy. All rights reserved.' );

$quick_fallback = array(
	array(
		'label' => 'Home',
		'url'   => $home_url,
	),
	array(
		'label' => 'Life at Chestnut',
		'url'   => home_url( '/life-at-chestnut/' ),
	),
	array(
		'label' => 'About Us',
		'url'   => home_url( '/company/' ),
	),
	array(
		'label' => 'FAQ',
		'url'   => home_url( '/faq/' ),
	),
	array(
		'label' => 'Contact Us',
		'url'   => $contact_url,
	),
);

$contact_fallback = array(
	array(
		'label'    => 'Get Directions',
		'url'      => 'https://maps.google.com/?q=402+S+Chestnut+St,+McKinney,+TX',
		'external' => true,
	),
	array(
		'label' => 'Schedule a Tour',
		'url'   => $contact_url,
	),
	array(
		'label' => 'Privacy Policy',
		'url'   => $privacy_url,
	),
);
?>

<footer id="footer" class="padding-top padding-bottom clearfix">
	<div class="content-wrapper">
		<div class="company-info">
			<div class="logo">
				<img src="<?php echo esc_url( $footer_logo ); ?>" alt="Chestnut Square Academy">
			</div>
			<p>
				<span class="bold"><?php echo esc_html( (string) $footer_school_name ); ?></span><br>
				<?php echo esc_html( (string) $footer_address_1 ); ?><br>
				<?php echo esc_html( (string) $footer_address_2 ); ?><br>
				<?php echo esc_html( (string) $footer_hours ); ?>
			</p>
			<div class="copyright">
				<p><?php echo wp_kses_post( (string) $footer_copyright ); ?></p>
				<div class="copyright-links">
					<p><a class="pp" href="<?php echo esc_url( $privacy_url ); ?>">Privacy Policy</a></p>
				</div>
			</div>
		</div>

		<div class="links">
			<div class="quick-links">
				<p class="eyebrow">Quick Links</p>
				<?php if ( function_exists( 'csa_site_render_footer_menu' ) ) : ?>
					<?php csa_site_render_footer_menu( 'csa_footer_quick', 'menu-footer-quick', $quick_fallback ); ?>
				<?php endif; ?>
			</div>
			<div class="contact">
				<p class="eyebrow">Contact</p>
				<?php if ( function_exists( 'csa_site_render_footer_menu' ) ) : ?>
					<?php csa_site_render_footer_menu( 'csa_footer_contact', 'menu-footer-contact', $contact_fallback ); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>


