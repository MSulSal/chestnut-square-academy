<?php
/**
 * Custom footer for Kiddie mock theme.
 *
 * @package HelloElementorKiddieMock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$home_url         = home_url( '/' );
$company_url      = home_url( '/company/' );
$programs_url     = home_url( '/our-curriculum/' );
$faq_url          = home_url( '/faq/' );
$contact_url      = home_url( '/contact-us/' );
$privacy_url      = home_url( '/privacy-policy/' );
$footer_logo_default = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/new-logo-csa-tree.png';
$footer_logo         = apply_filters( 'kms_asset_url', $footer_logo_default, 'footer_logo' );
?>

<footer id="footer" class="padding-top padding-bottom clearfix">
	<div class="content-wrapper">
		<div class="company-info">
			<div class="logo">
				<img src="<?php echo esc_url( $footer_logo ); ?>" alt="Chestnut Square Academy">
			</div>
			<p>
				<span class="bold">Chestnut Square Academy</span><br>
				402 S. Chestnut St.<br>
				McKinney, TX<br>
				Monday-Friday: 6:00 AM-6:00 PM
			</p>
			<div class="copyright">
				<p>&copy; 2026 Chestnut Square Academy. All rights reserved.</p>
				<div class="copyright-links">
					<p><a class="pp" href="<?php echo esc_url( $privacy_url ); ?>">Privacy Policy</a></p>
				</div>
			</div>
		</div>

		<div class="links">
			<div class="quick-links">
				<p class="eyebrow">Quick Links</p>
				<ul id="menu-footer-quick" class="menu">
					<li><a href="<?php echo esc_url( $home_url ); ?>">Home</a></li>
					<li><a href="<?php echo esc_url( $company_url ); ?>">About Us</a></li>
					<li><a href="<?php echo esc_url( $programs_url ); ?>">Programs</a></li>
					<li><a href="<?php echo esc_url( $faq_url ); ?>">FAQ</a></li>
					<li><a href="<?php echo esc_url( $contact_url ); ?>">Contact Us</a></li>
				</ul>
			</div>
			<div class="contact">
				<p class="eyebrow">Contact</p>
				<ul id="menu-footer-contact" class="menu">
					<li><a href="<?php echo esc_url( $contact_url ); ?>">Schedule a Tour</a></li>
					<li><a href="https://maps.google.com/?q=402+S+Chestnut+St,+McKinney,+TX" target="_blank" rel="noopener noreferrer">Get Directions</a></li>
					<li><a href="<?php echo esc_url( $privacy_url ); ?>">Privacy Policy</a></li>
				</ul>
			</div>
			<div class="social">
				<p class="eyebrow">Social</p>
				<ul id="menu-footer-social" class="menu">
					<li><a href="https://www.facebook.com/" aria-label="Facebook - opens in a new tab" target="_blank" rel="noopener noreferrer"><i class="icon-facebook"></i></a></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="be-ix-link-block content-wrapper">
		<div class="be-related-link-container">
			<div class="be-label">Also of Interest</div>
			<ul class="be-list">
				<li class="be-list-item"><a class="be-related-link" href="<?php echo esc_url( $programs_url ); ?>">Programs Overview</a></li>
				<li class="be-list-item"><a class="be-related-link" href="<?php echo esc_url( $faq_url ); ?>">Parent FAQs</a></li>
				<li class="be-list-item"><a class="be-related-link" href="<?php echo esc_url( $contact_url ); ?>">Schedule a Tour</a></li>
			</ul>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
