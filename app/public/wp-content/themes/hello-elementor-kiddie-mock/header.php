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
					<img src="<?php echo esc_url( $desktop_logo ); ?>" alt="Chestnut Square Academy logo for desktop" />
				</a>
			</div>
		</div>

		<div class="nav-right">
			<div class="link-menu">
				<div class="main-nav">
					<?php if ( function_exists( 'kiddie_mock_render_primary_nav_menu' ) ) : ?>
						<?php kiddie_mock_render_primary_nav_menu(); ?>
					<?php endif; ?>
				</div>

				<div class="function-nav">
					<div class="mobile-logo">
						<a href="<?php echo esc_url( $home_url ); ?>">
							<img src="<?php echo esc_url( $mobile_logo ); ?>" alt="Chestnut Square Academy logo for mobile" />
						</a>
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
