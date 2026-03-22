<?php
/**
 * Custom header for Kiddie mock theme.
 *
 * @package HelloElementorKiddieMock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=5.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a href="#main-content" class="skip-link">Skip to main content</a>

<?php
$home_url                 = home_url( '/' );
$programs_url             = home_url( '/our-curriculum/' );
$company_url              = home_url( '/company/' );
$faq_url                  = home_url( '/faq/' );
$contact_url              = home_url( '/contact-us/' );
$header_cta_url           = $contact_url;
$header_cta_label         = 'Schedule a Tour';
$header_cta_aria          = 'Schedule a Tour - Contact Us';
$desktop_logo_default     = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/new-logo-csa-navbar.png';
$mobile_logo_default      = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/new-logo-csa-navbar.png';
$elementor_site_logo      = function_exists( 'kiddie_mock_get_elementor_site_logo_url' ) ? trim( (string) kiddie_mock_get_elementor_site_logo_url() ) : '';

if ( '' !== $elementor_site_logo ) {
	$desktop_logo = $elementor_site_logo;
	$mobile_logo  = $elementor_site_logo;
} else {
	$desktop_logo = apply_filters( 'kms_asset_url', $desktop_logo_default, 'header_logo_desktop' );
	$mobile_logo  = apply_filters( 'kms_asset_url', $mobile_logo_default, 'header_logo_mobile' );
}
?>

<header id="header" class="">
	<div class="inner-container">
		<div class="nav-left">
			<div class="logo">
				<a href="<?php echo esc_url( $home_url ); ?>">
					<img src="<?php echo esc_url( $desktop_logo ); ?>" alt="Kiddie academy header logo for desktop" />
				</a>
			</div>
		</div>

		<div class="nav-right">
			<div class="link-menu">
				<div class="support-nav">
					<div class="top-quicklinks one-row-flex">
						<div class="links-for-parents">
							<a href="<?php echo esc_url( $programs_url ); ?>">Programs</a>
							<a href="<?php echo esc_url( $faq_url ); ?>">FAQ</a>
							<a href="<?php echo esc_url( $contact_url ); ?>">Contact Us</a>
						</div>
						<div class="header-search" tabindex="0"><span class="toplevel icon-search"></span></div>
						<div class="search-nav">
							<div class="site-search">
								<form action="<?php echo esc_url( $home_url ); ?>" method="get">
									<input type="text" name="s" id="searchBox" value="" placeholder="Search our site" class="search-input">
									<button type="submit" class="search-button" id="searchButton"><span class="icon-search"></span></button>
								</form>
							</div>
						</div>
					</div>
				</div>

				<div class="main-nav">
					<ul class="one-row-flex top-menu">
						<li><span class="toplevel"><a href="<?php echo esc_url( $company_url ); ?>">About Us</a></span></li>
						<li><span class="toplevel"><a href="<?php echo esc_url( $programs_url ); ?>">Programs</a></span></li>
						<li><span class="toplevel"><a href="<?php echo esc_url( $faq_url ); ?>">FAQ</a></span></li>
						<li><span class="toplevel"><a href="<?php echo esc_url( $contact_url ); ?>">Contact Us</a></span></li>
					</ul>
				</div>

				<div class="function-nav">
					<div class="mobile-logo">
						<a href="<?php echo esc_url( $home_url ); ?>">
							<img src="<?php echo esc_url( $mobile_logo ); ?>" alt="Kiddie academy header logo for mobile" />
						</a>
					</div>
					<div class="find-button">
						<a href="<?php echo esc_url( $header_cta_url ); ?>" class="button-round" aria-label="<?php echo esc_attr( $header_cta_aria ); ?>"><?php echo esc_html( $header_cta_label ); ?></a>
					</div>
					<div class="expand-button mobile-button">
						<span class="toplevel">
							<input type="checkbox" id="menu_checkbox">
							<label for="menu_checkbox">
								<div></div>
								<div></div>
								<div></div>
							</label>
						</span>
					</div>
				</div>

				<div id="compact-menu">
					<div class="container">
						<a href="<?php echo esc_url( $header_cta_url ); ?>" class="button-round shadow" aria-label="<?php echo esc_attr( $header_cta_aria ); ?>"><?php echo esc_html( $header_cta_label ); ?></a>
						<div class="expand-button-desktop">
							<span class="toplevel">
								<input type="checkbox" id="compact_menu_checkbox">
								<label for="compact_menu_checkbox">
									<div></div>
									<div></div>
									<div></div>
								</label>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</header>
