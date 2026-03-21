<?php
/**
 * Custom footer for Kiddie mock theme.
 *
 * @package HelloElementorKiddieMock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$academies_url    = home_url( '/academies/' );
$faq_url          = home_url( '/faq/' );
$newsroom_url     = home_url( '/newsroom/' );
$store_url        = home_url( '/store/' );
$testimonials_url = home_url( '/parent-testimonials/' );
$careers_url      = home_url( '/corporate-careers/' );
$franchise_url    = home_url( '/franchising/' );
$realestate_url   = home_url( '/franchising/real-estate/' );
$contact_url      = home_url( '/contact-us/' );
$privacy_url      = home_url( '/privacy-policy/' );
$terms_url        = home_url( '/terms-conditions/' );
$footer_logo      = apply_filters( 'kms_asset_url', 'https://kiddieacademy.com/wp-content/themes/kiddieacademy/assets/img/2023-refresh/ka-logo-white-footer.svg', 'footer_logo' );
?>

<footer id="footer" class="padding-top padding-bottom clearfix">
	<div class="content-wrapper">
		<div class="company-info">
			<div class="logo">
				<img src="<?php echo esc_url( $footer_logo ); ?>" alt="Kiddie Academy Educational Child Care">
			</div>
			<p>
				<span class="bold">Corporate Headquarters</span><br>
				3415 Box Hill Corporate Center Dr.<br>
				Abingdon, Maryland 21009<br>
				<a href="tel:800-554-3343" aria-label="(800) 554-3343">(800) 554-3343</a>
			</p>
			<div class="copyright">
				<p>&copy; 2008-2026 Essential Brands, Inc. All rights reserved.</p>
				<div class="copyright-links">
					<p><a class="pp" href="<?php echo esc_url( $privacy_url ); ?>">Privacy Policy</a></p>
					<p><a class="tc" href="<?php echo esc_url( $terms_url ); ?>">Terms & Conditions</a></p>
				</div>
			</div>
		</div>

		<div class="links">
			<div class="quick-links">
				<p class="eyebrow">Quick Links</p>
				<ul id="menu-footer-quick" class="menu">
					<li><a href="<?php echo esc_url( $academies_url ); ?>" aria-label="Find Your Academy - View All Academies">View All Academies</a></li>
					<li><a href="<?php echo esc_url( $faq_url ); ?>">Kiddie Academy FAQs</a></li>
					<li><a href="<?php echo esc_url( $newsroom_url ); ?>">Newsroom</a></li>
					<li><a href="<?php echo esc_url( $store_url ); ?>">Store</a></li>
					<li><a href="<?php echo esc_url( $testimonials_url ); ?>">Parent Testimonials</a></li>
				</ul>
			</div>
			<div class="contact">
				<p class="eyebrow">Contact</p>
				<ul id="menu-footer-contact" class="menu">
					<li><a href="<?php echo esc_url( $careers_url ); ?>">Corporate Careers</a></li>
					<li><a href="<?php echo esc_url( $franchise_url ); ?>">Franchise With Us</a></li>
					<li><a href="<?php echo esc_url( $realestate_url ); ?>">Real Estate</a></li>
					<li><a href="<?php echo esc_url( $contact_url ); ?>">Contact Us</a></li>
				</ul>
			</div>
			<div class="social">
				<p class="eyebrow">Social</p>
				<ul id="menu-footer-social" class="menu">
					<li><a href="https://www.facebook.com/KiddieAcademy" aria-label="Facebook - opens in a new tab" target="_blank" rel="noopener noreferrer"><i class="icon-facebook"></i></a></li>
					<li><a href="https://twitter.com/kiddieacademy" aria-label="Twitter (X) - opens in a new tab" target="_blank" rel="noopener noreferrer"><i class="icon-x"></i></a></li>
					<li><a href="https://www.youtube.com/communitybeginshere" aria-label="YouTube - opens in a new tab" target="_blank" rel="noopener noreferrer"><i class="icon-youtube"></i></a></li>
					<li><a href="https://www.instagram.com/kiddieacademyhq/" aria-label="Instagram - opens in a new tab" target="_blank" rel="noopener noreferrer"><i class="icon-instagram"></i></a></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="be-ix-link-block content-wrapper">
		<div class="be-related-link-container">
			<div class="be-label">Also of Interest</div>
			<ul class="be-list">
				<li class="be-list-item"><a class="be-related-link" href="<?php echo esc_url( $academies_url ); ?>">Childcare Near Me</a></li>
				<li class="be-list-item"><a class="be-related-link" href="<?php echo esc_url( $careers_url ); ?>">Child Care Jobs & Careers</a></li>
				<li class="be-list-item"><a class="be-related-link" href="<?php echo esc_url( home_url( '/academies/programs/preschool/' ) ); ?>">Preschool for 3-Year-Olds</a></li>
			</ul>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
