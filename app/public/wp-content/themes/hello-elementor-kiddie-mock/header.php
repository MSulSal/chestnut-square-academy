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
$academies_url            = home_url( '/academies/' );
$approach_url             = home_url( '/academies/approach-to-childcare/' );
$programs_url             = home_url( '/our-curriculum/' );
$tuition_url              = home_url( '/academies/enrollment-and-tuition/' );
$company_url              = home_url( '/company/' );
$careers_url              = home_url( '/careers/' );
$for_parents_url          = home_url( '/for-parents/' );
$franchising_url          = home_url( '/franchising/' );
$contact_url              = home_url( '/contact-us/' );
$leadership_url           = home_url( '/academic-leadership/' );
$community_url            = home_url( '/community-essentials/' );
$testimonials_url         = home_url( '/parent-testimonials/' );
$newsroom_url             = home_url( '/newsroom/' );
$infant_url               = home_url( '/academies/programs/infant-daycare/' );
$toddler_url              = home_url( '/academies/programs/toddler-daycare-curriculum/' );
$early_preschool_url      = home_url( '/academies/programs/early-preschool/' );
$preschool_url            = home_url( '/academies/programs/preschool/' );
$pre_kindergarten_url     = home_url( '/academies/programs/pre-kindergarten/' );
$kindergarten_url         = home_url( '/academies/programs/kindergarten/' );
$school_age_url           = home_url( '/academies/programs/school-age-programs/' );
$summer_camp_url          = home_url( '/academies/programs/summer-camp/' );
$is_academies_index       = is_page( 'academies' );
$header_cta_url           = $is_academies_index ? $academies_url : home_url( '/contact-us/' );
$header_cta_label         = $is_academies_index ? 'Find Your Academy' : 'Request Info';
$header_cta_aria          = $is_academies_index ? 'Find Your Academy - View All Academies' : 'Request Info';
$desktop_logo_default     = 'https://kiddieacademy.com/wp-content/themes/kiddieacademy/assets/img/kiddie-academy-logo.png';
$mobile_logo_default      = 'https://kiddieacademy.com/wp-content/themes/kiddieacademy/assets/img/2023-refresh/kiddie-academy-logo-stacked.svg';
$desktop_logo             = apply_filters( 'kms_asset_url', $desktop_logo_default, 'header_logo_desktop' );
$mobile_logo              = apply_filters( 'kms_asset_url', $mobile_logo_default, 'header_logo_mobile' );
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
							<a href="<?php echo esc_url( $for_parents_url ); ?>">Family Essentials Blog</a>
							<a href="<?php echo esc_url( $franchising_url ); ?>">Franchise With Us</a>
							<a href="<?php echo esc_url( $careers_url ); ?>">Careers</a>
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
						<li><span class="toplevel"><a href="<?php echo esc_url( $approach_url ); ?>">Approach to Care</a></span></li>
						<li>
							<span class="toplevel has-child curriculum">
								<a href="<?php echo esc_url( $programs_url ); ?>">Programs</a>
								<div class="child-menu-icon"><i class="fa-solid fa-chevron-down"></i></div>
							</span>
							<ul class="curriculum-submenu submenu">
								<li><a href="<?php echo esc_url( $infant_url ); ?>" aria-label="Infant 6 weeks to 12 months"><span class="title">Infant</span> <span class="age">6 weeks to 12 months</span></a></li>
								<li><a href="<?php echo esc_url( $toddler_url ); ?>" aria-label="Toddler 13 to 24 months"><span class="title">Toddler</span> <span class="age">13 to 24 months</span></a></li>
								<li><a href="<?php echo esc_url( $early_preschool_url ); ?>" aria-label="Early Preschool 2-Year-Olds"><span class="title">Early Preschool</span> <span class="age">2-Year-Olds</span></a></li>
								<li><a href="<?php echo esc_url( $preschool_url ); ?>" aria-label="Preschool 3-Year-Olds"><span class="title">Preschool</span> <span class="age">3-Year-Olds</span></a></li>
								<li><a href="<?php echo esc_url( $pre_kindergarten_url ); ?>" aria-label="Pre-Kindergarten 4-Year-Olds"><span class="title">Pre-Kindergarten</span> <span class="age">4-Year-Olds</span></a></li>
								<li><a href="<?php echo esc_url( $kindergarten_url ); ?>" aria-label="Kindergarten 5-Year-Olds"><span class="title">Kindergarten</span> <span class="age">5-Year-Olds</span></a></li>
								<li><a href="<?php echo esc_url( $school_age_url ); ?>" aria-label="School Age 5 to 12-Year-Olds"><span class="title">School Age</span> <span class="age">5 to 12-Year-Olds</span></a></li>
								<li><a href="<?php echo esc_url( $summer_camp_url ); ?>" aria-label="Summer Camp 2 to 12-Year-Olds"><span class="title">Summer Camp</span> <span class="age">2 to 12-Year-Olds</span></a></li>
							</ul>
						</li>
						<li><span class="toplevel"><a href="<?php echo esc_url( $tuition_url ); ?>">Tuition & Enrollment</a></span></li>
						<li>
							<span class="toplevel has-child about">
								<a href="<?php echo esc_url( $company_url ); ?>">About Us</a>
								<div class="child-menu-icon"><i class="fa-solid fa-chevron-down"></i></div>
							</span>
							<ul class="about-submenu submenu">
								<li><a href="<?php echo esc_url( $franchising_url ); ?>"><span>Franchise With Us</span></a></li>
								<li><a href="<?php echo esc_url( $careers_url ); ?>"><span>Careers</span></a></li>
								<li><a href="<?php echo esc_url( $leadership_url ); ?>"><span>Leadership</span></a></li>
								<li><a href="<?php echo esc_url( $community_url ); ?>"><span>Social Responsibility</span></a></li>
								<li><a href="<?php echo esc_url( $testimonials_url ); ?>" aria-label="Parent Testimonials"><span>Testimonials</span></a></li>
								<div class="break"></div>
								<li><a href="<?php echo esc_url( $newsroom_url ); ?>"><span>Newsroom</span></a></li>
								<li><a href="<?php echo esc_url( $contact_url ); ?>"><span>Contact Us</span></a></li>
							</ul>
						</li>
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
