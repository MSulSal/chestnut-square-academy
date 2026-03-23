<?php
/**
 * Plugin Name: CSA Site Tools
 * Description: Seeds and maintains the Chestnut Square Academy Elementor site with owner-friendly no-code tools.
 * Version: 1.3.30
 * Author: CSA Web Team
 * License: GPL-2.0-or-later
 * Text Domain: csa-site-tools
 *
 * @package CSASiteTools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One-time safety alignment: ensure the intended CSA child theme is active.
 *
 * This prevents barebones rendering if WordPress is still pointing at an older
 * theme folder that no longer carries full runtime templates/styles.
 */
function kms_ensure_csa_site_theme_active_once() {
	if ( '1.0.0' === (string) get_option( 'kms_theme_alignment_ver', '' ) ) {
		return;
	}

	$target_stylesheet = 'hello-elementor-csa-site';
	$target_template   = 'hello-elementor';
	$current_theme     = wp_get_theme();

	if ( ! $current_theme instanceof WP_Theme ) {
		return;
	}

	$current_stylesheet = (string) $current_theme->get_stylesheet();
	$current_template   = (string) $current_theme->get_template();

	if ( $current_stylesheet !== $target_stylesheet || $current_template !== $target_template ) {
		$target_theme = wp_get_theme( $target_stylesheet );
		if ( $target_theme instanceof WP_Theme && $target_theme->exists() ) {
			switch_theme( $target_stylesheet, $target_template );
		}
	}

	update_option( 'kms_theme_alignment_ver', '1.0.0' );
}
add_action( 'init', 'kms_ensure_csa_site_theme_active_once', 1 );

/**
 * Build a stable asset slot key from source URL.
 *
 * @param string $url Source URL.
 * @return string
 */
function kms_build_asset_key( $url ) {
	$path = wp_parse_url( $url, PHP_URL_PATH );
	$base = is_string( $path ) ? pathinfo( $path, PATHINFO_FILENAME ) : '';
	$base = sanitize_key( $base );

	if ( '' === $base ) {
		$base = 'asset';
	}

	return $base . '-' . substr( md5( $url ), 0, 6 );
}

/**
 * Return manual asset slots used outside seeded page markup.
 *
 * @return array<string,string>
 */
function kms_get_theme_asset_defaults() {
	$theme_base = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/';

	return array(
		'header_logo_desktop' => $theme_base . 'new-logo-csa-navbar.png',
		'header_logo_mobile'  => $theme_base . 'new-logo-csa-navbar.png',
		'footer_logo'         => $theme_base . 'new-logo-csa-tree.png',
	);
}

/**
 * Scan seeded HTML templates for image/background URLs and build slot catalog.
 *
 * @return array<string,array<string,string>>
 */
function kms_get_template_asset_catalog() {
	$catalog = array();
	$files   = glob( plugin_dir_path( __FILE__ ) . 'templates/*.html' );

	if ( ! is_array( $files ) ) {
		return $catalog;
	}

	foreach ( $files as $file ) {
		$raw = file_get_contents( $file );
		if ( ! is_string( $raw ) || '' === $raw ) {
			continue;
		}

		$matches = array();

		preg_match_all( '/(?:src|data-lazy-src|data-lazy-srcset)\s*=\s*"([^"]+)"/i', $raw, $matches );
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $url ) {
				if ( ! is_string( $url ) || '' === $url || false === strpos( $url, 'http' ) ) {
					continue;
				}
				$key             = kms_build_asset_key( $url );
				$catalog[ $key ] = array(
					'key'         => $key,
					'default_url' => $url,
					'label'       => basename( $file ) . ' / ' . $key,
				);
			}
		}

		$style_matches = array();
		preg_match_all( '/background-image:\s*url\([\'"]([^\'"]+)[\'"]\)/i', $raw, $style_matches );
		if ( ! empty( $style_matches[1] ) ) {
			foreach ( $style_matches[1] as $url ) {
				if ( ! is_string( $url ) || '' === $url || false === strpos( $url, 'http' ) ) {
					continue;
				}
				$key             = kms_build_asset_key( $url );
				$catalog[ $key ] = array(
					'key'         => $key,
					'default_url' => $url,
					'label'       => basename( $file ) . ' / ' . $key,
				);
			}
		}
	}

	return $catalog;
}

/**
 * Return inline-builder image URLs that do not live in template files.
 *
 * @return array<string,array<string,string>>
 */
function kms_get_inline_asset_catalog() {
	$urls = array(
		'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1400&q=80' => 'Generic section image A',
		'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=1400&q=80' => 'Generic section image B',
	);

	$catalog = array();
	foreach ( $urls as $url => $label ) {
		$key             = kms_build_asset_key( $url );
		$catalog[ $key ] = array(
			'key'         => $key,
			'default_url' => $url,
			'label'       => $label,
		);
	}

	return $catalog;
}

/**
 * Return final asset slot catalog (theme + template + inline).
 *
 * @return array<string,array<string,string>>
 */
function kms_get_asset_catalog() {
	$catalog = array();

	foreach ( kms_get_theme_asset_defaults() as $key => $url ) {
		$catalog[ $key ] = array(
			'key'         => $key,
			'default_url' => $url,
			'label'       => 'Theme / ' . $key,
		);
	}

	foreach ( kms_get_template_asset_catalog() as $key => $item ) {
		$catalog[ $key ] = $item;
	}

	foreach ( kms_get_inline_asset_catalog() as $key => $item ) {
		$catalog[ $key ] = $item;
	}

	ksort( $catalog );

	return $catalog;
}

/**
 * Get saved asset override map.
 *
 * @return array<string,string>
 */
function kms_get_asset_overrides() {
	$value = get_option( 'kms_asset_overrides', array() );

	return is_array( $value ) ? $value : array();
}

/**
 * Set asset overrides.
 *
 * @param array<string,string> $overrides Override map.
 */
function kms_set_asset_overrides( $overrides ) {
	update_option( 'kms_asset_overrides', $overrides );
}

/**
 * Resolve asset URL by key with optional override.
 *
 * @param string $default_url Default URL.
 * @param string $asset_key   Asset slot key.
 * @return string
 */
function kms_resolve_asset_url( $default_url, $asset_key ) {
	$overrides = kms_get_asset_overrides();

	if ( isset( $overrides[ $asset_key ] ) && '' !== $overrides[ $asset_key ] ) {
		return esc_url_raw( $overrides[ $asset_key ] );
	}

	return $default_url;
}
add_filter( 'kms_asset_url', 'kms_resolve_asset_url', 10, 2 );

/**
 * Replace template asset URLs in rendered markup using saved overrides.
 *
 * @param string $markup HTML markup.
 * @return string
 */
function kms_replace_asset_urls_in_markup( $markup ) {
	if ( ! is_string( $markup ) || '' === $markup ) {
		return $markup;
	}

	$overrides = kms_get_asset_overrides();
	if ( empty( $overrides ) ) {
		return $markup;
	}

	foreach ( kms_get_asset_catalog() as $key => $item ) {
		if ( empty( $item['default_url'] ) || empty( $overrides[ $key ] ) ) {
			continue;
		}

		$markup = str_replace( $item['default_url'], esc_url_raw( $overrides[ $key ] ), $markup );
	}

	return $markup;
}

/**
 * Filter post content for asset URL overrides.
 *
 * @param string $content Content.
 * @return string
 */
function kms_filter_the_content_assets( $content ) {
	$content = kms_replace_asset_urls_in_markup( $content );

	return kms_apply_real_data_text_replacements( $content );
}
add_filter( 'the_content', 'kms_filter_the_content_assets', 25 );

/**
 * Filter Elementor widget render output for asset URL overrides.
 *
 * @param string                    $content Content.
 * @param \Elementor\Widget_Base    $widget  Widget instance.
 * @return string
 */
function kms_filter_elementor_widget_assets( $content, $widget ) {
	unset( $widget );

	$content = kms_replace_asset_urls_in_markup( $content );

	return kms_apply_real_data_text_replacements( $content );
}
add_filter( 'elementor/widget/render_content', 'kms_filter_elementor_widget_assets', 10, 2 );
add_filter( 'elementor/frontend/the_content', 'kms_apply_real_data_text_replacements', 20 );

/**
 * Return all seeded pages and hierarchy paths.
 *
 * @return array<int,array<string,string>>
 */
function kms_get_page_blueprints() {
	return array(
		array( 'path' => 'home', 'title' => 'Home', 'template' => 'home' ),
		array( 'path' => 'for-parents', 'title' => 'Family Essentials Blog', 'template' => 'generic' ),
		array( 'path' => 'franchising', 'title' => 'Franchise With Us', 'template' => 'generic' ),
		array( 'path' => 'careers', 'title' => 'Careers', 'template' => 'generic' ),
		array( 'path' => 'our-curriculum', 'title' => 'Programs', 'template' => 'our-curriculum' ),
		array( 'path' => 'company', 'title' => 'About Us', 'template' => 'company' ),
		array( 'path' => 'contact-us', 'title' => 'Contact Us', 'template' => 'contact-us' ),
		array( 'path' => 'faq', 'title' => 'Kiddie Academy FAQs', 'template' => 'faq' ),
		array( 'path' => 'parent-testimonials', 'title' => 'Parent Testimonials', 'template' => 'generic' ),
		array( 'path' => 'newsroom', 'title' => 'Newsroom', 'template' => 'generic' ),
		array( 'path' => 'academic-leadership', 'title' => 'Leadership', 'template' => 'generic' ),
		array( 'path' => 'community-essentials', 'title' => 'Social Responsibility', 'template' => 'generic' ),
		array( 'path' => 'privacy-policy', 'title' => 'Privacy Policy', 'template' => 'generic' ),
		array( 'path' => 'terms-conditions', 'title' => 'Terms & Conditions', 'template' => 'generic' ),
		array( 'path' => 'store', 'title' => 'Store', 'template' => 'generic' ),
		array( 'path' => 'corporate-careers', 'title' => 'Corporate Careers', 'template' => 'generic' ),
		array( 'path' => 'academies', 'title' => 'View All Academies', 'template' => 'academies' ),
		array( 'path' => 'academies/approach-to-childcare', 'title' => 'Approach to Care', 'template' => 'academies-approach-to-childcare' ),
		array( 'path' => 'academies/enrollment-and-tuition', 'title' => 'Tuition & Enrollment', 'template' => 'generic' ),
		array( 'path' => 'academies/programs', 'title' => 'Academy Programs', 'template' => 'programs-index' ),
		array( 'path' => 'academies/programs/infant-daycare', 'title' => 'Infant', 'template' => 'program-detail' ),
		array( 'path' => 'academies/programs/toddler-daycare-curriculum', 'title' => 'Toddler', 'template' => 'program-detail' ),
		array( 'path' => 'academies/programs/early-preschool', 'title' => 'Early Preschool', 'template' => 'program-detail' ),
		array( 'path' => 'academies/programs/preschool', 'title' => 'Preschool', 'template' => 'program-detail' ),
		array( 'path' => 'academies/programs/pre-kindergarten', 'title' => 'Pre-Kindergarten', 'template' => 'program-detail' ),
		array( 'path' => 'academies/programs/kindergarten', 'title' => 'Kindergarten', 'template' => 'program-detail' ),
		array( 'path' => 'academies/programs/school-age-programs', 'title' => 'School Age', 'template' => 'program-detail' ),
		array( 'path' => 'academies/programs/summer-camp', 'title' => 'Summer Camp', 'template' => 'program-detail' ),
		array( 'path' => 'franchising/real-estate', 'title' => 'Real Estate', 'template' => 'generic' ),
	);
}

/**
 * Sort page blueprint list by hierarchy depth.
 *
 * @param array<int,array<string,string>> $items Items.
 * @return array<int,array<string,string>>
 */
function kms_sort_blueprints_by_depth( $items ) {
	usort(
		$items,
		static function ( $a, $b ) {
			$a_depth = substr_count( $a['path'], '/' );
			$b_depth = substr_count( $b['path'], '/' );

			if ( $a_depth === $b_depth ) {
				return strcmp( $a['path'], $b['path'] );
			}

			return $a_depth <=> $b_depth;
		}
	);

	return $items;
}

/**
 * Load HTML template file from plugin directory.
 *
 * @param string $template Template name.
 * @return string
 */
function kms_get_template_file_html( $template ) {
	$file = plugin_dir_path( __FILE__ ) . 'templates/' . $template . '.html';

	if ( ! file_exists( $file ) ) {
		return '';
	}

	$content = file_get_contents( $file );

	return is_string( $content ) ? $content : '';
}

/**
 * Return display copy + image defaults for generic seeded pages.
 *
 * @param string $path  Page path.
 * @param string $title Page title.
 * @return array<string,string>
 */
function kms_get_generic_page_profile( $path, $title ) {
	$profiles = array(
		'for-parents'                      => array(
			'eyebrow' => 'Parent Resources',
			'subhead' => 'Family guidance, developmental insights, and practical tips from early learning experts.',
		),
		'franchising'                      => array(
			'eyebrow' => 'Business Opportunity',
			'subhead' => 'Explore franchise ownership with a mission-focused early childhood education brand.',
		),
		'careers'                          => array(
			'eyebrow' => 'Join Our Team',
			'subhead' => 'Grow your career in a supportive environment that helps children and families thrive.',
		),
		'academies/enrollment-and-tuition' => array(
			'eyebrow' => 'Enrollment Journey',
			'subhead' => 'Learn what enrollment can include and how to connect with a local Academy for availability details.',
		),
		'parent-testimonials'              => array(
			'eyebrow' => 'Family Voices',
			'subhead' => 'Hear what parents value most about safety, communication, and everyday learning moments.',
		),
		'newsroom'                         => array(
			'eyebrow' => 'Latest Updates',
			'subhead' => 'Explore news, announcements, and community stories from across the Kiddie Academy network.',
		),
		'academic-leadership'              => array(
			'eyebrow' => 'Leadership',
			'subhead' => 'Meet the leaders shaping strategy, curriculum quality, and educator development.',
		),
		'community-essentials'             => array(
			'eyebrow' => 'Social Responsibility',
			'subhead' => 'Learn how community engagement and service are woven into the Academy experience.',
		),
		'corporate-careers'                => array(
			'eyebrow' => 'Corporate Team',
			'subhead' => 'Find opportunities to support families, franchisees, and educators behind the scenes.',
		),
		'franchising/real-estate'          => array(
			'eyebrow' => 'Real Estate',
			'subhead' => 'Review location strategy and site criteria supporting successful Academy development.',
		),
		'privacy-policy'                   => array(
			'eyebrow' => 'Privacy',
			'subhead' => 'Understand how data is handled, protected, and used across digital experiences.',
		),
		'terms-conditions'                 => array(
			'eyebrow' => 'Terms',
			'subhead' => 'Review terms and conditions associated with use of this site and related services.',
		),
		'store'                            => array(
			'eyebrow' => 'Academy Store',
			'subhead' => 'Explore branded resources and items that support family and classroom engagement.',
		),
	);

	$defaults = array(
		'eyebrow' => 'Kiddie Academy',
		'subhead' => 'Explore information and resources tailored for families, educators, and community partners.',
	);

	$profile = isset( $profiles[ $path ] ) ? $profiles[ $path ] : $defaults;

	return array(
		'title'      => $title,
		'eyebrow'    => $profile['eyebrow'],
		'subhead'    => $profile['subhead'],
		'image_main' => 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Infant.jpg',
		'image_alt'  => 'Children engaged in classroom learning at Kiddie Academy',
		'image_side' => 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Toddler.jpg',
	);
}

/**
 * Build generic subpage HTML.
 *
 * @param string $title Page title.
 * @param string $path Full path.
 * @return string
 */
function kms_get_generic_html( $title, $path ) {
	$profile      = kms_get_generic_page_profile( $path, $title );
	$title_html   = esc_html( $profile['title'] );
	$eyebrow_html = esc_html( $profile['eyebrow'] );
	$subhead_html = esc_html( $profile['subhead'] );
	$image_main   = esc_url( $profile['image_main'] );
	$image_side   = esc_url( $profile['image_side'] );
	$image_alt    = esc_attr( $profile['image_alt'] );

	return <<<HTML
<main id="main-content">
	<div class="content-wrapper">
		<div class="breadcrumbs">
			<a href="/">Home</a>
			<span> / </span>
			<span class="current-page">{$title_html}</span>
		</div>
	</div>
	<section class="subpage-hero padding-bottom padding-top offset-bg-parent">
		<div class="offset-bg tan extend-left round-bottom-right no-media"></div>
		<div class="text-and-image content-wrapper no-media">
			<div class="text-left">
				<p class="eyebrow">{$eyebrow_html}</p>
				<h1>{$title_html}</h1>
				<p>{$subhead_html}</p>
			</div>
		</div>
	</section>
	<section class="image-text padding-bottom margin-top overlapping image-text media-image media-cover">
		<div class="content-wrapper">
			<div class="image">
				<img data-lazy-src="{$image_main}" class="lazy fill-container" alt="{$image_alt}">
			</div>
			<div class="text">
				<h4>Rooted in Downtown McKinney</h4>
				<p>Chestnut Square Academy supports early learners with warm care, structured routines, and joyful classroom experiences.</p>
				<p>Families can explore programs, ask enrollment questions, and schedule a tour to see daily life in action.</p>
				<a class="button-round" href="/contact-us/">Schedule a Tour</a>
			</div>
		</div>
	</section>
	<section class="image-text margin-bottom overlapping text-image media-image media-cover">
		<div class="content-wrapper">
			<div class="image">
				<img data-lazy-src="{$image_side}" class="lazy fill-container" alt="Teacher supporting learning activities in classroom">
			</div>
			<div class="text">
				<h4>Learning Moments That Matter</h4>
				<p>Our classrooms are designed to support age-appropriate learning, social growth, and strong family partnership.</p>
				<p>Use the main navigation to explore our programs, frequently asked questions, and next steps for enrollment.</p>
			</div>
		</div>
	</section>
</main>
HTML;
}

/**
 * Build FAQ page HTML.
 *
 * @return string
 */
function kms_get_faq_html() {
	return <<<HTML
<main id="main-content">
	<div class="content-wrapper">
		<div class="breadcrumbs">
			<a href="/">Home</a>
			<span> / </span>
			<span class="current-page">Chestnut Square Academy FAQs</span>
		</div>
	</div>
	<section class="subpage-hero padding-bottom padding-top offset-bg-parent">
		<div class="offset-bg tan extend-left round-bottom-right no-media"></div>
		<div class="text-and-image content-wrapper no-media">
			<div class="text-left">
				<h1>Your Most Asked Questions</h1>
			</div>
		</div>
	</section>
	<section id="faqs" class="faq-preview-section content-wrapper padding-top padding-bottom">
		<div class="text">
			<h5 class="desktop">Frequently Asked Questions</h5>
		</div>
		<div class="links">
			<div>
				<p data-question="q1"><strong>What ages do you serve?</strong><i class="fa-solid fa-chevron-down"></i></p>
				<div data-answer="q1" hidden><p>Chestnut Square Academy serves children from 6 weeks through 4/5 years with age-based classrooms and routines.</p></div>
			</div>
			<div>
				<p data-question="q2"><strong>Do you offer full-day and part-day options?</strong><i class="fa-solid fa-chevron-down"></i></p>
				<div data-answer="q2" hidden><p>Scheduling options depend on classroom availability. Contact us to review current openings and daily schedule options.</p></div>
			</div>
			<div>
				<p data-question="q3"><strong>How do we schedule a tour?</strong><i class="fa-solid fa-chevron-down"></i></p>
				<div data-answer="q3" hidden><p>Use the contact page to send your preferred day and time, and our team will follow up to confirm.</p></div>
			</div>
			<div>
				<p data-question="q4"><strong>How can I learn about tuition and enrollment?</strong><i class="fa-solid fa-chevron-down"></i></p>
				<div data-answer="q4" hidden><p>Tuition depends on your child&rsquo;s age and weekly schedule. Contact us directly for current enrollment details.</p></div>
			</div>
		</div>
	</section>
</main>
HTML;
}

/**
 * Build academies index HTML.
 *
 * @return string
 */
function kms_get_academies_html() {
	return <<<HTML
<main id="main-content">
	<section class="kma-academies-hero">
		<div class="kma-academies-hero-left" aria-hidden="true"></div>
		<div class="kma-academies-hero-right">
			<div class="kma-academies-hero-inner">
				<h1>Find a Kiddie Academy&reg; Child Care Near You</h1>
				<p>All across the country, Kiddie Academy Educational Child Care is helping prepare children for life. Find the most convenient of our 360+ locations near you.</p>
				<div class="locator-small">
					<div class="locator">
						<div class="form">
							<div class="input-container">
								<input type="text" name="location" class="semi-transparent location-search-autocomplete" placeholder="City, State or Zip" aria-label="City, State or Zip" />
							</div>
							<button class="button" type="submit">Search Academies</button>
						</div>
						<a class="get-current-location" href="/academies/?useMyLocation=true"><i class="fa-solid fa-location-crosshairs"></i>Use your current location</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="kma-state-strip">
		<div class="content-wrapper">
			<div class="kma-state-strip-text">
				<h2>Find an Academy in Your State</h2>
			</div>
			<div class="kma-state-strip-form">
				<div class="kma-select-wrap">
					<select aria-label="Select Your State">
						<option selected>Select Your State</option>
						<option>Texas</option>
						<option>California</option>
						<option>Florida</option>
						<option>Maryland</option>
					</select>
				</div>
				<button class="button-round">Show</button>
			</div>
		</div>
	</section>

	<section class="find-academy" id="find-academy">
		<div class="content-wrapper">
			<div class="text-container">
				<h4>Find an Academy Near You</h4>
				<p>Kiddie Academy Educational Child Care helps children make the most of learning moments in locations across the country. Discover one near you.</p>
			</div>
			<div class="locator">
				<div class="form">
					<div class="input-container">
						<input type="text" name="location-2" class="semi-transparent location-search-autocomplete" placeholder="City, State or Zipcode" aria-label="City, State or Zipcode" />
					</div>
					<button class="button" type="submit">Find Your Academy</button>
				</div>
				<a class="get-current-location" href="/academies/?useMyLocation=true"><i class="fa-solid fa-location-crosshairs"></i>Use your current location</a>
			</div>
		</div>
	</section>
</main>
HTML;
}

/**
 * Build programs index HTML.
 *
 * @return string
 */
function kms_get_programs_index_html() {
	return <<<HTML
<main id="main-content">
	<section class="subpage-hero padding-bottom padding-top offset-bg-parent">
		<div class="offset-bg tan extend-left round-bottom-right no-media"></div>
		<div class="text-and-image content-wrapper no-media">
			<div class="text-left">
				<h1>Programs at a Glance</h1>
				<p>Find your program for every age and stage.</p>
			</div>
		</div>
	</section>
	<section id="curriculum" class="padding-top padding-bottom">
		<div class="tan-bg"></div>
		<div class="container content-wrapper">
			<div class="intro"><h2>Learning by Age Group</h2></div>
			<div class="slides desktop">
				<div class="programs-list">
					<div class="slide" data-program="infant"><a href="/academies/programs/infant-daycare/"><div class="detail-container"><p class="program-title">Infant</p><p class="overview">6 weeks to 12 months</p></div></a></div>
					<div class="slide" data-program="toddler"><a href="/academies/programs/toddler-daycare-curriculum/"><div class="detail-container"><p class="program-title">Toddler</p><p class="overview">13 to 24 months</p></div></a></div>
					<div class="slide" data-program="preschool"><a href="/academies/programs/preschool/"><div class="detail-container"><p class="program-title">Preschool</p><p class="overview">3-Year-Olds</p></div></a></div>
				</div>
				<div class="programs-image">
					<img data-program="infant" data-lazy-src="https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Infant.jpg" alt="Infant Program" class="lazy" />
					<img data-program="toddler" data-lazy-src="https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Toddler.jpg" alt="Toddler Program" class="lazy" />
					<img data-program="preschool" data-lazy-src="https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Preschool.jpg" alt="Preschool Program" class="lazy" />
				</div>
			</div>
		</div>
	</section>
</main>
HTML;
}

/**
 * Build program detail HTML.
 *
 * @param string $title Program title.
 * @return string
 */
function kms_get_program_detail_html( $title ) {
	$title_html = esc_html( $title );
	$image_url  = 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Infant.jpg';

	if ( 'Toddler' === $title ) {
		$image_url = 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Toddler.jpg';
	} elseif ( 'Early Preschool' === $title ) {
		$image_url = 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Early-Preschool.jpg';
	} elseif ( 'Preschool' === $title ) {
		$image_url = 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Preschool.jpg';
	} elseif ( 'Pre-Kindergarten' === $title ) {
		$image_url = 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-PreK.jpg';
	} elseif ( 'Kindergarten' === $title ) {
		$image_url = 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Kindergarten.jpg';
	} elseif ( 'School Age' === $title ) {
		$image_url = 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-School-Aged.jpg';
	} elseif ( 'Summer Camp' === $title ) {
		$image_url = 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Summer-updated.jpg';
	}

	$image_url = esc_url( $image_url );

	return <<<HTML
<main id="main-content">
	<section class="subpage-hero padding-bottom padding-top offset-bg-parent">
		<div class="offset-bg tan extend-left round-bottom-right no-media"></div>
		<div class="text-and-image content-wrapper no-media">
			<div class="text-left">
				<h1>{$title_html}</h1>
				<p>Learning with momentum for every stage.</p>
			</div>
		</div>
	</section>
	<section class="image-text padding-bottom margin-top overlapping image-text media-image media-cover">
		<div class="content-wrapper">
			<div class="image">
				<img data-lazy-src="{$image_url}" class="lazy fill-container" alt="{$title_html} classroom">
			</div>
			<div class="text">
				<h4>{$title_html} Program Overview</h4>
				<p>Our teachers support developmental milestones through guided play, structured routines, and responsive care.</p>
				<p>Each classroom experience is designed to help children build confidence, communication skills, and curiosity.</p>
				<a class="button-round" href="/contact-us/">Schedule a Tour</a>
			</div>
		</div>
	</section>
</main>
HTML;
}

/**
 * Default gallery items for the Life at Chestnut page.
 *
 * @return array<int,array<string,mixed>>
 */
function kms_get_life_gallery_default_items() {
	return array(
		array(
			'image_id'     => 0,
			'image_url'    => 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Infant.jpg',
			'title'        => 'Calm Beginnings',
			'description'  => 'Warm care and comforting routines help our youngest learners feel safe and supported.',
			'alt'          => 'Infant classroom moments',
		),
		array(
			'image_id'     => 0,
			'image_url'    => 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Toddler.jpg',
			'title'        => 'Hands-On Discovery',
			'description'  => 'Toddlers explore, move, and communicate through guided play and meaningful interactions.',
			'alt'          => 'Toddler activity time',
		),
		array(
			'image_id'     => 0,
			'image_url'    => 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Preschool.jpg',
			'title'        => 'Growing Confidence',
			'description'  => 'Preschoolers build early academic and social skills with playful, age-appropriate learning.',
			'alt'          => 'Preschool learning activity',
		),
		array(
			'image_id'     => 0,
			'image_url'    => 'https://kiddieacademy.com/wp-content/uploads/2024/08/teacher-parent-circle-time.jpg',
			'title'        => 'Trusted Relationships',
			'description'  => 'Our team creates nurturing connections with every child and keeps families closely informed.',
			'alt'          => 'Teacher and child during reading time',
		),
		array(
			'image_id'     => 0,
			'image_url'    => 'https://kiddieacademy.com/wp-content/uploads/2024/08/learnon-classroom-group-activity.jpg',
			'title'        => 'Everyday Joy',
			'description'  => 'Music, movement, art, and story time bring joyful learning moments into every day.',
			'alt'          => 'Small group classroom project',
		),
		array(
			'image_id'     => 0,
			'image_url'    => 'https://kiddieacademy.com/wp-content/uploads/2024/08/kiddie-academy-center-exterior.jpg',
			'title'        => 'Downtown McKinney Home',
			'description'  => 'Located in Historic Downtown McKinney, our center is a welcoming neighborhood place for families.',
			'alt'          => 'School exterior',
		),
	);
}

/**
 * Sanitize life-gallery item rows from admin input.
 *
 * @param mixed $raw_items Raw submitted rows.
 * @return array<int,array<string,mixed>>
 */
function kms_sanitize_life_gallery_items( $raw_items ) {
	if ( ! is_array( $raw_items ) ) {
		return array();
	}

	$items = array();

	foreach ( $raw_items as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$image_id  = isset( $row['image_id'] ) ? absint( $row['image_id'] ) : 0;
		$image_url = isset( $row['image_url'] ) ? esc_url_raw( trim( (string) $row['image_url'] ) ) : '';

		if ( $image_id > 0 ) {
			$attachment_url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( is_string( $attachment_url ) && '' !== $attachment_url ) {
				$image_url = esc_url_raw( $attachment_url );
			}
		}

		if ( 0 === $image_id && '' === $image_url ) {
			continue;
		}

		$title       = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';
		$description = isset( $row['description'] ) ? sanitize_text_field( $row['description'] ) : '';
		$alt         = isset( $row['alt'] ) ? sanitize_text_field( $row['alt'] ) : '';

		if ( '' === $title ) {
			$title = 'Life at Chestnut Moment';
		}

		if ( '' === $alt ) {
			$alt = $title;
		}

		$items[] = array(
			'image_id'    => $image_id,
			'image_url'   => $image_url,
			'title'       => $title,
			'description' => $description,
			'alt'         => $alt,
		);
	}

	return $items;
}

/**
 * Get currently configured life gallery rows (dashboard-managed).
 *
 * @return array<int,array<string,mixed>>
 */
function kms_get_life_gallery_items() {
	$stored = get_option( 'kms_life_gallery_items', null );

	if ( null === $stored ) {
		return kms_get_life_gallery_default_items();
	}

	return kms_sanitize_life_gallery_items( $stored );
}

/**
 * Humanize a file name into a readable title.
 *
 * @param string $filename Filename.
 * @return string
 */
function kms_humanize_filename( $filename ) {
	$name = preg_replace( '/\.[^.]+$/', '', (string) $filename );
	$name = preg_replace( '/[_-]+/', ' ', (string) $name );
	$name = preg_replace( '/\s+/', ' ', (string) $name );
	$name = trim( (string) $name );

	if ( '' === $name ) {
		return 'Life at Chestnut Moment';
	}

	return ucwords( $name );
}

/**
 * Locate local docs folder path for life-at-chestnut seed images.
 *
 * @return string
 */
function kms_get_docs_life_gallery_dir() {
	$project_root = wp_normalize_path( (string) dirname( ABSPATH, 2 ) );
	$legacy_root  = wp_normalize_path( (string) dirname( ABSPATH, 3 ) );
	$candidates   = array(
		$project_root . '/docs/life-at-chestnut',
		$legacy_root . '/docs/life-at-chestnut',
	);

	foreach ( $candidates as $candidate ) {
		if ( is_dir( $candidate ) ) {
			return $candidate;
		}
	}

	return $candidates[0];
}

/**
 * Find previously imported life-gallery attachment by source hash.
 *
 * @param string $hash File hash.
 * @return int
 */
function kms_find_life_gallery_attachment_by_hash( $hash ) {
	if ( ! is_string( $hash ) || '' === $hash ) {
		return 0;
	}

	$ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'fields'         => 'ids',
			'posts_per_page' => 1,
			'meta_key'       => '_kms_life_gallery_hash',
			'meta_value'     => $hash,
			'orderby'        => 'ID',
			'order'          => 'DESC',
		)
	);

	if ( is_array( $ids ) && ! empty( $ids[0] ) ) {
		return absint( $ids[0] );
	}

	return 0;
}

/**
 * Import image files from docs/life-at-chestnut into Media Library and gallery option.
 *
 * @return array<int,array<string,mixed>>|WP_Error
 */
function kms_import_life_gallery_from_docs() {
	$dir = kms_get_docs_life_gallery_dir();

	if ( ! is_dir( $dir ) ) {
		return new WP_Error( 'kms_no_docs_gallery_dir', 'Could not find docs/life-at-chestnut directory.' );
	}

	$patterns = array( '*.jpg', '*.jpeg', '*.png', '*.webp', '*.avif' );
	$files    = array();

	foreach ( $patterns as $pattern ) {
		$matches = glob( wp_normalize_path( trailingslashit( $dir ) . $pattern ) );
		if ( is_array( $matches ) ) {
			$files = array_merge( $files, $matches );
		}
	}

	$files = array_values( array_unique( array_filter( $files, 'is_string' ) ) );
	natsort( $files );
	$files = array_values( $files );

	if ( empty( $files ) ) {
		return new WP_Error( 'kms_no_docs_gallery_images', 'No image files were found in docs/life-at-chestnut.' );
	}

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}
	if ( ! function_exists( 'wp_handle_sideload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	$items = array();

	foreach ( $files as $file_path ) {
		$file_path = wp_normalize_path( (string) $file_path );
		if ( '' === $file_path || ! file_exists( $file_path ) ) {
			continue;
		}

		$filename = wp_basename( $file_path );
		$title    = kms_humanize_filename( $filename );
		$hash     = md5_file( $file_path );
		$hash     = is_string( $hash ) && '' !== $hash ? $hash : md5( $filename );

		$attachment_id = kms_find_life_gallery_attachment_by_hash( $hash );

		if ( $attachment_id <= 0 ) {
			$contents = file_get_contents( $file_path );
			if ( false === $contents ) {
				continue;
			}

			$upload = wp_upload_bits( $filename, null, $contents );
			if ( ! is_array( $upload ) || ! empty( $upload['error'] ) || empty( $upload['file'] ) ) {
				continue;
			}

			$filetype = wp_check_filetype( $upload['file'], null );

			$attachment_id = wp_insert_attachment(
				array(
					'post_title'     => $title,
					'post_status'    => 'inherit',
					'post_mime_type' => isset( $filetype['type'] ) ? (string) $filetype['type'] : 'image/jpeg',
				),
				$upload['file']
			);

			if ( is_wp_error( $attachment_id ) || $attachment_id <= 0 ) {
				continue;
			}

			$meta = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
			if ( is_array( $meta ) ) {
				wp_update_attachment_metadata( $attachment_id, $meta );
			}

			update_post_meta( $attachment_id, '_kms_life_gallery_hash', $hash );
		}

		$image_url = wp_get_attachment_image_url( $attachment_id, 'full' );
		if ( ! is_string( $image_url ) || '' === $image_url ) {
			continue;
		}

		$items[] = array(
			'image_id'    => absint( $attachment_id ),
			'image_url'   => esc_url_raw( $image_url ),
			'title'       => $title,
			'description' => '',
			'alt'         => $title,
		);
	}

	if ( empty( $items ) ) {
		return new WP_Error( 'kms_docs_gallery_import_failed', 'No images could be imported from docs/life-at-chestnut.' );
	}

	update_option( 'kms_life_gallery_items', $items );

	return $items;
}

/**
 * Build Life at Chestnut page HTML from dashboard-managed gallery rows.
 *
 * @return string
 */
function kms_get_life_at_chestnut_html() {
	$items = kms_get_life_gallery_items();
	$cards = '';
	$image_pool = array();

	foreach ( $items as $item ) {
		$image_source = isset( $item['image_url'] ) ? esc_url_raw( (string) $item['image_url'] ) : '';
		$image_url   = '' !== $image_source ? esc_url( $image_source ) : '';
		$title       = isset( $item['title'] ) ? esc_html( (string) $item['title'] ) : 'Life at Chestnut Moment';
		$alt         = isset( $item['alt'] ) ? esc_attr( (string) $item['alt'] ) : esc_attr( $title );

		if ( '' === $image_url ) {
			continue;
		}

		$cards .= <<<HTML
			<div class="single-column image-only">
				<div class="image image-frame">
					<img class="fill-container" data-lazy-src="{$image_url}" alt="{$alt}">
				</div>
			</div>
HTML;

		$image_pool[] = array(
			'url' => $image_source,
			'alt' => isset( $item['alt'] ) ? wp_strip_all_tags( (string) $item['alt'] ) : wp_strip_all_tags( (string) $title ),
		);
	}

	$featured_image_url = isset( $image_pool[0]['url'] ) ? esc_url( (string) $image_pool[0]['url'] ) : 'https://kiddieacademy.com/wp-content/uploads/2024/08/teacher-parent-circle-time.jpg';
	$featured_image_alt = isset( $image_pool[0]['alt'] ) ? (string) $image_pool[0]['alt'] : 'Children learning together at Chestnut Square Academy';
	$featured_image_alt = esc_attr( $featured_image_alt );
	$image_pool_json    = wp_json_encode( $image_pool );

	if ( ! is_string( $image_pool_json ) || '' === $image_pool_json ) {
		$image_pool_json = '[]';
	}

	return <<<HTML
<main id="main-content" class="life-gallery-page">
<section class="subpage-hero padding-bottom padding-top offset-bg-parent">
	<div class="offset-bg tan extend-left round-bottom-right no-media"></div>
	<div class="text-and-image content-wrapper no-media">
		<div class="text-left">
			<h1>LIFE AT CHESTNUT</h1>
			<p>A glimpse into our classrooms, daily rhythm, and warm community in Downtown McKinney.</p>
		</div>
	</div>
</section>

<section class="life-age-groups padding-top padding-bottom">
	<style>
		.life-age-groups .life-age-tabs { display:flex; flex-wrap:wrap; gap:10px; margin:0 0 18px; }
		.life-age-groups .life-age-tab { border:1px solid #d0cac4; background:#fff; color:#24313A; padding:10px 16px; font-weight:800; cursor:pointer; border-radius:999px; }
		.life-age-groups .life-age-tab.is-active { background:#2E7D32; color:#fff; border-color:#2E7D32; }
		.life-age-groups .life-age-featured { margin:0 0 18px; max-width:860px; }
		.life-age-groups .life-age-featured img { display:block; width:100%; height:auto; object-fit:cover; }
		.life-age-groups .life-age-panel { display:none; border-left:3px solid #E0A96D; padding:4px 0 4px 14px; }
		.life-age-groups .life-age-panel.is-active { display:block; }
	</style>
	<div class="content-wrapper">
		<div class="intro">
			<h2>Learning by Age Group</h2>
		</div>
		<div class="life-age-tabs" role="tablist" aria-label="Age group tabs">
			<button class="life-age-tab is-active" type="button" data-life-tab="infants" role="tab" aria-selected="true">Infants</button>
			<button class="life-age-tab" type="button" data-life-tab="toddlers" role="tab" aria-selected="false">Toddlers</button>
			<button class="life-age-tab" type="button" data-life-tab="early-preschool" role="tab" aria-selected="false">Early Preschool</button>
			<button class="life-age-tab" type="button" data-life-tab="preschool-prek" role="tab" aria-selected="false">Preschool &amp; Pre-K</button>
		</div>
		<div class="life-age-featured">
			<img class="life-age-featured-img" src="{$featured_image_url}" data-lazy-src="{$featured_image_url}" alt="{$featured_image_alt}">
		</div>
		<div class="life-age-panels">
			<div class="life-age-panel is-active" data-life-panel="infants" role="tabpanel">
				<p>6 weeks to 12 months. Gentle routines, responsive care, and early developmental milestones in a calm classroom.</p>
			</div>
			<div class="life-age-panel" data-life-panel="toddlers" role="tabpanel">
				<p>13 to 24 months. Language growth, movement, and social-emotional learning through guided play and exploration.</p>
			</div>
			<div class="life-age-panel" data-life-panel="early-preschool" role="tabpanel">
				<p>2-year-olds. Structured discovery, growing independence, and consistent routines that support confidence.</p>
			</div>
			<div class="life-age-panel" data-life-panel="preschool-prek" role="tabpanel">
				<p>3 to 5 years. Literacy, early math, creative expression, and school-readiness skills built through daily practice.</p>
			</div>
		</div>
	</div>
	<script>
	(function () {
		var scope = document.querySelector('.life-age-groups');
		if (!scope) { return; }
		var tabs = scope.querySelectorAll('.life-age-tab');
		var panels = scope.querySelectorAll('.life-age-panel');
		var featured = scope.querySelector('.life-age-featured-img');
		var galleryImages = {$image_pool_json};
		if (!tabs.length || !panels.length) { return; }
		function pickRandomImage() {
			if (!featured || !galleryImages || !galleryImages.length) { return; }
			var next = galleryImages[Math.floor(Math.random() * galleryImages.length)];
			if (!next || !next.url) { return; }
			featured.setAttribute('src', next.url);
			featured.setAttribute('data-lazy-src', next.url);
			if (next.alt) { featured.setAttribute('alt', next.alt); }
		}
		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				var key = tab.getAttribute('data-life-tab');
				tabs.forEach(function (t) {
					t.classList.remove('is-active');
					t.setAttribute('aria-selected', 'false');
				});
				panels.forEach(function (p) {
					p.classList.remove('is-active');
				});
				tab.classList.add('is-active');
				tab.setAttribute('aria-selected', 'true');
				var panel = scope.querySelector('.life-age-panel[data-life-panel=\"' + key + '\"]');
				if (panel) { panel.classList.add('is-active'); }
				pickRandomImage();
			});
		});
	})();
	</script>
</section>

<section class="column-3-image-text-cards life-gallery-grid padding-top padding-bottom">
	<div class="content-wrapper">
		<div class="column-3">
{$cards}
		</div>
	</div>
</section>
</main>
HTML;
}

/**
 * Build simplified Contact Us page HTML with one unified content block.
 *
 * @return string
 */
function kms_get_contact_us_html() {
	return <<<HTML
<main id="main-content">
<section class="subpage-hero padding-bottom padding-top offset-bg-parent">
	<div class="offset-bg tan extend-left round-bottom-right no-media"></div>
	<div class="text-and-image content-wrapper no-media">
		<div class="text-left">
			<h1>Contact Us</h1>
			<p>Tell us about your family and we will follow up with available tour times.</p>
		</div>
	</div>
</section>

<section id="contact-details" class="full-width-text margin-top left-aligned bg-default">
	<div class="content-wrapper">
		<div class="content">
			<div class="text">
				<h2>Visit Chestnut Square Academy</h2>
				<div class="card-copy">
					<p>402 S. Chestnut St., McKinney, TX</p>
					<p>Hours: Monday-Friday, 6:00 AM-6:00 PM</p>
					<p>Texas Rising Star daycare in Downtown McKinney.</p>
					<p>Schedule a tour through our form and our team will contact you with current availability and next steps.</p>
					<p><a href="https://maps.google.com/?q=402+S+Chestnut+St,+McKinney,+TX">Get Directions</a></p>
				</div>
			</div>
		</div>
	</div>
</section>
</main>
HTML;
}

/**
 * Refresh the Life at Chestnut page from gallery settings.
 */
function kms_sync_life_gallery_page() {
	$blueprint = array(
		'path'     => 'life-at-chestnut',
		'title'    => 'Life at Chestnut',
		'template' => 'life-at-chestnut',
	);

	kms_upsert_small_business_page( $blueprint, true );
}

/**
 * Resolve final page HTML for blueprint.
 *
 * @param string $template Template key.
 * @param string $title Title.
 * @param string $path Full path.
 * @return string
 */
function kms_get_page_html( $template, $title, $path ) {
	if ( 'life-at-chestnut' === $template ) {
		return kms_get_life_at_chestnut_html();
	}

	if ( 'contact-us' === $template ) {
		return kms_get_contact_us_html();
	}

	if ( 'faq' === $template ) {
		return kms_get_faq_html();
	}

	if ( 'academies' === $template ) {
		return kms_get_academies_html();
	}

	if ( 'programs-index' === $template ) {
		return kms_get_programs_index_html();
	}

	if ( 'program-detail' === $template ) {
		return kms_get_program_detail_html( $title );
	}

	if ( 'generic' === $template ) {
		return kms_get_generic_html( $title, $path );
	}

	$file_html = kms_get_template_file_html( $template );

	if ( '' !== $file_html ) {
		return $file_html;
	}

	return kms_get_generic_html( $title, $path );
}

/**
 * Localize internal links from reference markup to this local WP install.
 *
 * @param string $html HTML.
 * @return string
 */
function kms_localize_internal_links( $html ) {
	$root = trailingslashit( home_url() );

	$replacements = array(
		'href="https://kiddieacademy.com/'   => 'href="' . $root,
		"href='https://kiddieacademy.com/"   => "href='" . $root,
		'action="https://kiddieacademy.com/' => 'action="' . $root,
		"action='https://kiddieacademy.com/" => "action='" . $root,
		'href="/'                            => 'href="' . $root,
		"href='/"                            => "href='" . $root,
		'action="/'                          => 'action="' . $root,
		"action='/"                          => "action='" . $root,
		'Â®'                                  => '&reg;',
	);

	return strtr( $html, $replacements );
}

/**
 * Split full HTML markup into top-level blocks for Elementor editing.
 *
 * @param string $html HTML block.
 * @return array<int,string>
 */
function kms_split_html_for_elementor( $html ) {
	$markup = trim( (string) $html );

	if ( '' === $markup ) {
		return array();
	}

	// Strip a single outer <main> wrapper so each top-level section/div can be
	// represented as an independent Elementor section.
	$markup = (string) preg_replace( '/^\s*<main\b[^>]*>/i', '', $markup );
	$markup = (string) preg_replace( '/<\/main>\s*$/i', '', $markup );
	$markup = trim( $markup );

	if ( '' === $markup ) {
		return array();
	}

	if ( ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) ) {
		return array( $markup );
	}

	$doc        = new DOMDocument();
	$wrapped    = '<?xml encoding="utf-8" ?><div id="kms-root">' . $markup . '</div>';
	$libxml_old = libxml_use_internal_errors( true );
	$loaded     = $doc->loadHTML( $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $libxml_old );

	if ( ! $loaded ) {
		return array( $markup );
	}

	$xpath = new DOMXPath( $doc );
	$nodes = $xpath->query( '//*[@id="kms-root"]/*|//*[@id="kms-root"]/comment()' );

	$chunks = array();

	if ( $nodes instanceof DOMNodeList ) {
		foreach ( $nodes as $node ) {
			$chunk = trim( (string) $doc->saveHTML( $node ) );

			if ( '' !== $chunk ) {
				$chunks[] = $chunk;
			}
		}
	}

	if ( empty( $chunks ) ) {
		return array( $markup );
	}

	return $chunks;
}

/**
 * Persist Elementor document metadata on a post.
 *
 * @param int                    $post_id Post ID.
 * @param array<int,mixed>|mixed $data    Elementor document data.
 */
function kms_store_elementor_document( $post_id, $data ) {
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
	delete_post_meta( $post_id, '_elementor_element_cache' );
	delete_post_meta( $post_id, '_elementor_css' );
}

/**
 * Apply Elementor document data for editable page content.
 *
 * @param int    $post_id Post ID.
 * @param string $html HTML block.
 */
function kms_set_elementor_document( $post_id, $html ) {
	$chunks = kms_split_html_for_elementor( $html );

	if ( empty( $chunks ) ) {
		$chunks = array( (string) $html );
	}

	$data = array();

	foreach ( $chunks as $index => $chunk_html ) {
		$seed       = $post_id . '-' . $index;
		$section_id = substr( md5( 'sec-' . $seed ), 0, 8 );
		$column_id  = substr( md5( 'col-' . $seed ), 0, 8 );
		$widget_id  = substr( md5( 'wid-' . $seed ), 0, 8 );

		$data[] = array(
			'id'       => $section_id,
			'elType'   => 'section',
			'settings' => array(),
			'elements' => array(
				array(
					'id'       => $column_id,
					'elType'   => 'column',
					'settings' => array(
						'_column_size' => 100,
					),
					'elements' => array(
						array(
							'id'         => $widget_id,
							'elType'     => 'widget',
							'widgetType' => 'html',
							'settings'   => array(
								'html' => $chunk_html,
							),
							'elements'   => array(),
						),
					),
					'isInner'  => false,
				),
			),
			'isInner'  => false,
		);
	}

	kms_store_elementor_document( $post_id, $data );
}

/**
 * Create/update a page from blueprint.
 *
 * @param array<string,string> $blueprint Blueprint.
 * @param bool                 $overwrite Overwrite content.
 * @return int
 */
function kms_upsert_page( $blueprint, $overwrite ) {
	$path      = $blueprint['path'];
	$title     = $blueprint['title'];
	$template  = $blueprint['template'];
	$slug      = basename( $path );
	$parent_id = 0;

	if ( false !== strpos( $path, '/' ) ) {
		$parent_path = dirname( $path );
		$parent_page = get_page_by_path( $parent_path, OBJECT, 'page' );
		if ( $parent_page instanceof WP_Post ) {
			$parent_id = (int) $parent_page->ID;
		}
	}

	$page = get_page_by_path( $path, OBJECT, 'page' );

	$postarr = array(
		'post_title'     => $title,
		'post_name'      => $slug,
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'post_parent'    => $parent_id,
		'comment_status' => 'closed',
	);

	if ( $page instanceof WP_Post ) {
		$postarr['ID'] = (int) $page->ID;
	} else {
		$postarr['post_content'] = '';
	}

	if ( $overwrite || ! isset( $postarr['ID'] ) ) {
		$html                  = kms_get_page_html( $template, $title, $path );
		$postarr['post_content'] = kms_localize_internal_links( $html );
	}

	$page_id = wp_insert_post( wp_slash( $postarr ), true );

	if ( is_wp_error( $page_id ) ) {
		return 0;
	}

	update_post_meta( $page_id, '_wp_page_template', 'default' );

	if ( $overwrite || ! $page instanceof WP_Post ) {
		kms_set_elementor_document( $page_id, $postarr['post_content'] );
	}

	return (int) $page_id;
}

/**
 * Get current frontend seed profile.
 *
 * @return string
 */
function kms_get_seed_profile() {
	$profile = get_option( 'kms_seed_profile', 'native-parity' );

	return is_string( $profile ) ? $profile : 'native-parity';
}

/**
 * Save frontend seed profile.
 *
 * @param string $profile Profile key.
 */
function kms_set_seed_profile( $profile ) {
	update_option( 'kms_seed_profile', sanitize_key( (string) $profile ) );
}

/**
 * Build deterministic Elementor node ID for owner-edit mode.
 *
 * @param int    $post_id Post ID.
 * @param int    $counter Running counter.
 * @param string $prefix  ID prefix.
 * @return string
 */
function kms_owner_make_node_id( $post_id, &$counter, $prefix ) {
	$counter++;

	return substr( md5( 'owner-' . $prefix . '-' . $post_id . '-' . $counter ), 0, 8 );
}

/**
 * Build a generic Elementor widget payload for owner-edit mode.
 *
 * @param int                 $post_id  Post ID.
 * @param int                 $counter  Running counter.
 * @param string              $type     Elementor widget type.
 * @param array<string,mixed> $settings Elementor widget settings.
 * @param string              $classes  Optional CSS classes.
 * @param string              $css_id   Optional CSS ID.
 * @return array<string,mixed>
 */
function kms_owner_make_widget( $post_id, &$counter, $type, $settings, $classes = '', $css_id = '' ) {
	if ( '' !== $classes ) {
		$settings['css_classes']  = $classes;
		$settings['_css_classes'] = $classes;
	}

	if ( '' !== $css_id ) {
		$settings['css_id']  = $css_id;
		$settings['_css_id'] = $css_id;
	}

	return array(
		'id'         => kms_owner_make_node_id( $post_id, $counter, 'wid' ),
		'elType'     => 'widget',
		'widgetType' => $type,
		'settings'   => $settings,
		'elements'   => array(),
	);
}

/**
 * Build a container payload for owner-edit mode.
 *
 * @param int                 $post_id   Post ID.
 * @param int                 $counter   Running counter.
 * @param array<int,mixed>    $elements  Child Elementor elements.
 * @param string              $classes   Optional CSS classes.
 * @param string              $css_id    Optional CSS ID.
 * @param bool                $is_inner  Inner container flag.
 * @param string              $html_tag  HTML tag.
 * @param array<string,mixed> $settings  Extra Elementor container settings.
 * @return array<string,mixed>
 */
function kms_owner_make_container( $post_id, &$counter, $elements, $classes = '', $css_id = '', $is_inner = false, $html_tag = 'section', $settings = array() ) {
	$base_settings = array(
		'content_width'  => 'full',
		'flex_direction' => 'column',
	);

	if ( '' !== $classes ) {
		$base_settings['css_classes']  = $classes;
		$base_settings['_css_classes'] = $classes;
	}

	if ( '' !== $css_id ) {
		$base_settings['css_id']  = $css_id;
		$base_settings['_css_id'] = $css_id;
	}

	if ( 'div' !== $html_tag ) {
		$base_settings['html_tag'] = $html_tag;
	}

	$container_settings = array_merge( $base_settings, $settings );

	return array(
		'id'       => kms_owner_make_node_id( $post_id, $counter, 'con' ),
		'elType'   => 'container',
		'settings' => $container_settings,
		'elements' => is_array( $elements ) ? $elements : array(),
		'isInner'  => (bool) $is_inner,
	);
}

/**
 * Build a heading widget for owner-edit mode.
 *
 * @param int    $post_id Post ID.
 * @param int    $counter Running counter.
 * @param string $title   Heading text/HTML.
 * @param string $tag     Heading tag.
 * @param string $classes Optional CSS classes.
 * @return array<string,mixed>
 */
function kms_owner_heading_widget( $post_id, &$counter, $title, $tag = 'h2', $classes = '' ) {
	return kms_owner_make_widget(
		$post_id,
		$counter,
		'heading',
		array(
			'title'       => (string) $title,
			'header_size' => (string) $tag,
		),
		$classes
	);
}

/**
 * Build a text-editor widget for owner-edit mode.
 *
 * @param int    $post_id Post ID.
 * @param int    $counter Running counter.
 * @param string $html    HTML copy.
 * @param string $classes Optional CSS classes.
 * @return array<string,mixed>
 */
function kms_owner_text_widget( $post_id, &$counter, $html, $classes = '' ) {
	return kms_owner_make_widget(
		$post_id,
		$counter,
		'text-editor',
		array(
			'editor' => (string) $html,
		),
		$classes
	);
}

/**
 * Build an image widget for owner-edit mode.
 *
 * @param int    $post_id Post ID.
 * @param int    $counter Running counter.
 * @param string $url     Image URL.
 * @param string $classes Optional CSS classes.
 * @return array<string,mixed>
 */
function kms_owner_image_widget( $post_id, &$counter, $url, $classes = '' ) {
	return kms_owner_make_widget(
		$post_id,
		$counter,
		'image',
		array(
			'image'      => array(
				'id'  => 0,
				'url' => esc_url_raw( (string) $url ),
			),
			'image_size' => 'full',
		),
		$classes
	);
}

/**
 * Build a button widget for owner-edit mode.
 *
 * @param int    $post_id Post ID.
 * @param int    $counter Running counter.
 * @param string $text    Button label.
 * @param string $url     Target URL.
 * @param string $classes Optional CSS classes.
 * @return array<string,mixed>
 */
function kms_owner_button_widget( $post_id, &$counter, $text, $url, $classes = '' ) {
	return kms_owner_make_widget(
		$post_id,
		$counter,
		'button',
		array(
			'text' => (string) $text,
			'size' => 'md',
			'link' => array(
				'url' => (string) $url,
			),
		),
		$classes
	);
}

/**
 * Build an icon-list item payload.
 *
 * @param string $text Item text.
 * @param string $url  Optional item URL.
 * @return array<string,mixed>
 */
function kms_owner_icon_item( $text, $url = '' ) {
	return array(
		'_id'           => substr( md5( (string) $text . (string) $url ), 0, 8 ),
		'text'          => (string) $text,
		'icon'          => 'fas fa-circle-check',
		'selected_icon' => array(
			'value'   => 'fas fa-circle-check',
			'library' => 'fa-solid',
		),
		'link'          => array(
			'url' => (string) $url,
		),
	);
}

/**
 * Build an icon-list widget for owner-edit mode.
 *
 * @param int                 $post_id Post ID.
 * @param int                 $counter Running counter.
 * @param array<int,mixed>    $items   Icon list items.
 * @param string              $classes Optional CSS classes.
 * @return array<string,mixed>
 */
function kms_owner_icon_list_widget( $post_id, &$counter, $items, $classes = '' ) {
	return kms_owner_make_widget(
		$post_id,
		$counter,
		'icon-list',
		array(
			'icon_list'     => is_array( $items ) ? $items : array(),
			'space_between' => 12,
		),
		$classes
	);
}

/**
 * Build an accordion widget for owner-edit mode.
 *
 * @param int                 $post_id Post ID.
 * @param int                 $counter Running counter.
 * @param array<int,mixed>    $tabs    Accordion tabs.
 * @param string              $classes Optional CSS classes.
 * @return array<string,mixed>
 */
function kms_owner_accordion_widget( $post_id, &$counter, $tabs, $classes = '' ) {
	return kms_owner_make_widget(
		$post_id,
		$counter,
		'accordion',
		array(
			'tabs'           => is_array( $tabs ) ? $tabs : array(),
			'active_item_no' => '1',
		),
		$classes
	);
}

/**
 * Build a shortcode widget for owner-edit mode.
 *
 * @param int    $post_id   Post ID.
 * @param int    $counter   Running counter.
 * @param string $shortcode Shortcode value.
 * @param string $classes   Optional CSS classes.
 * @return array<string,mixed>
 */
function kms_owner_shortcode_widget( $post_id, &$counter, $shortcode, $classes = '' ) {
	return kms_owner_make_widget(
		$post_id,
		$counter,
		'shortcode',
		array(
			'shortcode' => (string) $shortcode,
		),
		$classes
	);
}

/**
 * Return owner-edit shared image defaults.
 *
 * @return array<string,string>
 */
function kms_owner_shared_images() {
	$theme_base = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/';

	return array(
		'hero'      => $theme_base . 'cover.png',
		'classroom' => 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Preschool.jpg',
		'infant'    => 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Infant.jpg',
		'toddler'   => 'https://kiddieacademy.com/academies/wp-content/uploads/2024/05/Learning-Age-Toddler.jpg',
		'staff'     => 'https://kiddieacademy.com/wp-content/uploads/2024/08/teacher-parent-circle-time.jpg',
		'activity'  => 'https://kiddieacademy.com/wp-content/uploads/2024/08/learnon-classroom-group-activity.jpg',
		'exterior'  => 'https://kiddieacademy.com/wp-content/uploads/2024/08/kiddie-academy-center-exterior.jpg',
	);
}

/**
 * Return page blueprints for owner-edit mode.
 *
 * @return array<int,array<string,string>>
 */
function kms_get_owner_edit_blueprints() {
	return array(
		array( 'path' => 'home', 'title' => 'Home' ),
		array( 'path' => 'life-at-chestnut', 'title' => 'Life at Chestnut' ),
		array( 'path' => 'company', 'title' => 'About Us' ),
		array( 'path' => 'faq', 'title' => 'Frequently Asked Questions' ),
		array( 'path' => 'contact-us', 'title' => 'Contact Us' ),
		array( 'path' => 'academies', 'title' => 'Find an Academy' ),
	);
}

/**
 * Return owner-edit plain page copy fallback for post_content.
 *
 * @param string $path Page path.
 * @return string
 */
function kms_get_owner_plain_content( $path ) {
	$copy = array(
		'home'           => 'Chestnut Square Academy is an early learning center in Downtown McKinney serving families with warm, dependable care and play-based learning.',
		'life-at-chestnut' => 'A look into daily life at Chestnut Square Academy through classroom and community moments.',
		'company'        => 'Learn about Chestnut Square Academy, our family-first approach, and our commitment to the Downtown McKinney community.',
		'faq'            => 'Answers to common family questions about programs, enrollment, daily routines, and communication.',
		'contact-us'     => 'Get in touch with Chestnut Square Academy to ask questions or schedule a tour.',
		'academies'      => 'Find location and contact details for Chestnut Square Academy in Downtown McKinney.',
	);

	return isset( $copy[ $path ] ) ? (string) $copy[ $path ] : 'Early learning and childcare information.';
}

/**
 * Build owner-edit Elementor document data by page path.
 *
 * @param string $path    Page path.
 * @param int    $post_id Post ID.
 * @return array<int,mixed>
 */
function kms_build_owner_page_data( $path, $post_id ) {
	$counter = 0;
	$home    = trailingslashit( home_url() );
	$images  = kms_owner_shared_images();

	$hero_left = array(
		kms_owner_heading_widget( $post_id, $counter, 'Nurturing Early Learners in Downtown McKinney', 'h1', 'kms-owner-title' ),
		kms_owner_text_widget( $post_id, $counter, '<p>Warm care. Structured learning. A neighborhood school experience families can trust.</p>', 'kms-owner-lead' ),
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_button_widget( $post_id, $counter, 'Schedule a Tour', $home . 'contact-us/', 'kms-owner-btn-primary' ),
				kms_owner_button_widget( $post_id, $counter, 'Call the School', $home . 'contact-us/', 'kms-owner-btn-secondary' ),
			),
			'kms-owner-button-row',
			'',
			true,
			'div'
		),
	);

	$home_data = array(
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_make_container(
					$post_id,
					$counter,
					array(
						kms_owner_make_container( $post_id, $counter, $hero_left, 'kms-owner-col', '', true, 'div' ),
						kms_owner_make_container(
							$post_id,
							$counter,
							array( kms_owner_image_widget( $post_id, $counter, $images['hero'], 'kms-owner-hero-image' ) ),
							'kms-owner-col',
							'',
							true,
							'div'
						),
					),
					'kms-owner-grid kms-owner-grid-two',
					'',
					true,
					'div'
				),
			),
			'kms-owner-section kms-owner-hero',
			'owner-home-hero'
		),
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_heading_widget( $post_id, $counter, 'Quick Facts', 'h2' ),
				kms_owner_icon_list_widget(
					$post_id,
					$counter,
					array(
						kms_owner_icon_item( 'Located in Historic Downtown McKinney' ),
						kms_owner_icon_item( 'Hours: Monday-Friday, 6:00 AM-6:00 PM' ),
						kms_owner_icon_item( 'Serving early learners from infancy through early school years' ),
						kms_owner_icon_item( 'Texas Rising Star participant' ),
					)
				),
			),
			'kms-owner-section kms-owner-facts'
		),
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_heading_widget( $post_id, $counter, 'Programs for Every Stage', 'h2' ),
				kms_owner_make_container(
					$post_id,
					$counter,
					array(
						kms_owner_make_container(
							$post_id,
							$counter,
							array(
								kms_owner_image_widget( $post_id, $counter, $images['infant'] ),
								kms_owner_heading_widget( $post_id, $counter, 'Infants', 'h3' ),
								kms_owner_text_widget( $post_id, $counter, '<p>Gentle routines, safe care, and meaningful developmental moments from the very beginning.</p>' ),
							),
							'kms-owner-card',
							'',
							true,
							'div'
						),
						kms_owner_make_container(
							$post_id,
							$counter,
							array(
								kms_owner_image_widget( $post_id, $counter, $images['toddler'] ),
								kms_owner_heading_widget( $post_id, $counter, 'Toddlers', 'h3' ),
								kms_owner_text_widget( $post_id, $counter, '<p>Hands-on exploration, language growth, and social-emotional development through play.</p>' ),
							),
							'kms-owner-card',
							'',
							true,
							'div'
						),
						kms_owner_make_container(
							$post_id,
							$counter,
							array(
								kms_owner_image_widget( $post_id, $counter, $images['classroom'] ),
								kms_owner_heading_widget( $post_id, $counter, 'Preschool & Pre-K', 'h3' ),
								kms_owner_text_widget( $post_id, $counter, '<p>School-readiness experiences with literacy, math, creativity, and confidence-building activities.</p>' ),
							),
							'kms-owner-card',
							'',
							true,
							'div'
						),
					),
					'kms-owner-grid kms-owner-grid-three',
					'',
					true,
					'div'
				),
			),
			'kms-owner-section'
		),
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_heading_widget( $post_id, $counter, 'Common Family Questions', 'h2' ),
				kms_owner_accordion_widget(
					$post_id,
					$counter,
					array(
						array( '_id' => 'oq1', 'tab_title' => 'What ages do you serve?', 'tab_content' => 'Chestnut Square Academy serves children from infancy through early school years, based on current classroom availability.' ),
						array( '_id' => 'oq2', 'tab_title' => 'How do I schedule a tour?', 'tab_content' => 'Use the Schedule a Tour button and our team will follow up to confirm a time that works for your family.' ),
						array( '_id' => 'oq3', 'tab_title' => 'What are your hours?', 'tab_content' => 'Our standard schedule is Monday-Friday, 6:00 AM to 6:00 PM.' ),
						array( '_id' => 'oq4', 'tab_title' => 'Where are you located?', 'tab_content' => '402 S. Chestnut St., McKinney, Texas in the heart of Historic Downtown McKinney.' ),
					)
				),
			),
			'kms-owner-section kms-owner-faq'
		),
	);

	$about_data = array(
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_heading_widget( $post_id, $counter, 'A Small School with a Big Heart', 'h1' ),
				kms_owner_text_widget( $post_id, $counter, '<p>Chestnut Square Academy is rooted in Downtown McKinney and built around close relationships with children and families.</p>' ),
				kms_owner_heading_widget( $post_id, $counter, 'Our Approach', 'h2' ),
				kms_owner_icon_list_widget(
					$post_id,
					$counter,
					array(
						kms_owner_icon_item( 'Warm, responsive care in every classroom' ),
						kms_owner_icon_item( 'Age-appropriate learning experiences' ),
						kms_owner_icon_item( 'Family partnership and communication' ),
						kms_owner_icon_item( 'Community connection in Historic Downtown McKinney' ),
					)
				),
			),
			'kms-owner-section'
		),
	);

	$programs_data = array(
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_heading_widget( $post_id, $counter, 'Programs', 'h1' ),
				kms_owner_text_widget( $post_id, $counter, '<p>Our programs are designed to support growth at each stage with a balance of structure, care, and playful discovery.</p>' ),
			),
			'kms-owner-section kms-owner-hero'
		),
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_make_container(
					$post_id,
					$counter,
					array(
						kms_owner_make_container( $post_id, $counter, array( kms_owner_heading_widget( $post_id, $counter, 'Infants', 'h3' ), kms_owner_text_widget( $post_id, $counter, '<p>Supportive care and developmental play from 6 weeks and up.</p>' ) ), 'kms-owner-card', '', true, 'div' ),
						kms_owner_make_container( $post_id, $counter, array( kms_owner_heading_widget( $post_id, $counter, 'Toddlers', 'h3' ), kms_owner_text_widget( $post_id, $counter, '<p>Language, movement, and social learning through guided exploration.</p>' ) ), 'kms-owner-card', '', true, 'div' ),
						kms_owner_make_container( $post_id, $counter, array( kms_owner_heading_widget( $post_id, $counter, 'Preschool / Pre-K', 'h3' ), kms_owner_text_widget( $post_id, $counter, '<p>Early academics, creative play, and routines that build confidence.</p>' ) ), 'kms-owner-card', '', true, 'div' ),
					),
					'kms-owner-grid kms-owner-grid-three',
					'',
					true,
					'div'
				),
			),
			'kms-owner-section'
		),
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_heading_widget( $post_id, $counter, 'Daily Rhythm', 'h2' ),
				kms_owner_icon_list_widget(
					$post_id,
					$counter,
					array(
						kms_owner_icon_item( 'Arrival and welcome activities' ),
						kms_owner_icon_item( 'Learning centers and guided instruction' ),
						kms_owner_icon_item( 'Meals, rest, and outdoor play' ),
						kms_owner_icon_item( 'Afternoon enrichment and family handoff' ),
					)
				),
			),
			'kms-owner-section'
		),
	);

	$gallery_data = array(
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_heading_widget( $post_id, $counter, 'Life at Chestnut', 'h1' ),
				kms_owner_text_widget( $post_id, $counter, '<p>A look at daily life at Chestnut Square Academy.</p>' ),
			),
			'kms-owner-section kms-owner-hero'
		),
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_make_container(
					$post_id,
					$counter,
					array(
						kms_owner_image_widget( $post_id, $counter, $images['classroom'] ),
						kms_owner_image_widget( $post_id, $counter, $images['activity'] ),
						kms_owner_image_widget( $post_id, $counter, $images['staff'] ),
						kms_owner_image_widget( $post_id, $counter, $images['exterior'] ),
					),
					'kms-owner-grid kms-owner-grid-gallery',
					'',
					true,
					'div'
				),
			),
			'kms-owner-section'
		),
	);

	$faq_data = array(
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_heading_widget( $post_id, $counter, 'Frequently Asked Questions', 'h1' ),
				kms_owner_text_widget( $post_id, $counter, '<p>Helpful answers for families considering Chestnut Square Academy.</p>' ),
			),
			'kms-owner-section kms-owner-hero'
		),
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_accordion_widget(
					$post_id,
					$counter,
					array(
						array( '_id' => 'fq1', 'tab_title' => 'What are your operating hours?', 'tab_content' => 'Monday-Friday, 6:00 AM to 6:00 PM.' ),
						array( '_id' => 'fq2', 'tab_title' => 'Where are you located?', 'tab_content' => '402 S. Chestnut St., McKinney, TX.' ),
						array( '_id' => 'fq3', 'tab_title' => 'Do you provide meals?', 'tab_content' => 'Meal offerings may include breakfast and lunch depending on classroom schedule.' ),
						array( '_id' => 'fq4', 'tab_title' => 'Is your school part of Texas Rising Star?', 'tab_content' => 'Yes, Chestnut Square Academy participates in the Texas Rising Star program.' ),
						array( '_id' => 'fq5', 'tab_title' => 'How do I start enrollment?', 'tab_content' => 'Schedule a tour first so we can discuss your family needs and current availability.' ),
					)
				),
			),
			'kms-owner-section kms-owner-faq'
		),
	);

	$contact_data = array(
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_heading_widget( $post_id, $counter, 'Schedule a Tour', 'h1' ),
				kms_owner_text_widget( $post_id, $counter, '<p>Tell us about your family and we will follow up with available tour times.</p>' ),
			),
			'kms-owner-section kms-owner-hero'
		),
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_make_container(
					$post_id,
					$counter,
					array(
						kms_owner_make_container(
							$post_id,
							$counter,
							array(
								kms_owner_heading_widget( $post_id, $counter, 'Contact Information', 'h2' ),
								kms_owner_icon_list_widget(
									$post_id,
									$counter,
									array(
										kms_owner_icon_item( '402 S. Chestnut St., McKinney, TX' ),
										kms_owner_icon_item( 'Monday-Friday, 6:00 AM-6:00 PM' ),
										kms_owner_icon_item( 'Texas Rising Star Daycare in Downtown McKinney' ),
									)
								),
								kms_owner_button_widget( $post_id, $counter, 'Get Directions', 'https://maps.google.com/?q=402+S+Chestnut+St,+McKinney,+TX', 'kms-owner-btn-secondary' ),
							),
							'kms-owner-col',
							'',
							true,
							'div'
						),
						kms_owner_make_container(
							$post_id,
							$counter,
							array(
								kms_owner_heading_widget( $post_id, $counter, 'Tour Request Form', 'h2' ),
								kms_owner_text_widget( $post_id, $counter, '<p>Use your preferred form plugin shortcode below, then replace this text with your live form.</p>' ),
								kms_owner_shortcode_widget( $post_id, $counter, '[fluentform id=\"1\"]', 'kms-owner-form-shortcode' ),
							),
							'kms-owner-col',
							'',
							true,
							'div'
						),
					),
					'kms-owner-grid kms-owner-grid-two',
					'',
					true,
					'div'
				),
			),
			'kms-owner-section'
		),
	);

	$academies_data = array(
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_heading_widget( $post_id, $counter, 'Chestnut Square Academy', 'h1' ),
				kms_owner_text_widget( $post_id, $counter, '<p>In the heart of Historic Downtown McKinney.</p>' ),
				kms_owner_button_widget( $post_id, $counter, 'Contact Us', $home . 'contact-us/', 'kms-owner-btn-primary' ),
			),
			'kms-owner-section kms-owner-hero'
		),
	);

	$map = array(
		'home'           => $home_data,
		'life-at-chestnut' => $gallery_data,
		'company'        => $about_data,
		'gallery'        => $gallery_data,
		'faq'            => $faq_data,
		'contact-us'     => $contact_data,
		'academies'      => $academies_data,
	);

	if ( isset( $map[ $path ] ) ) {
		return $map[ $path ];
	}

	return array(
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
				kms_owner_heading_widget( $post_id, $counter, 'Owner Edit Page', 'h1' ),
				kms_owner_text_widget( $post_id, $counter, '<p>This page is ready for direct editing in Elementor.</p>' ),
			),
			'kms-owner-section'
		),
	);
}

/**
 * Create or update one owner-edit page.
 *
 * @param array<string,string> $blueprint Page blueprint.
 * @param bool                 $overwrite Whether to overwrite existing content.
 * @return int
 */
function kms_upsert_owner_page( $blueprint, $overwrite ) {
	$path      = (string) $blueprint['path'];
	$title     = (string) $blueprint['title'];
	$slug      = basename( $path );
	$parent_id = 0;

	if ( false !== strpos( $path, '/' ) ) {
		$parent_path = dirname( $path );
		$parent_page = get_page_by_path( $parent_path, OBJECT, 'page' );

		if ( $parent_page instanceof WP_Post ) {
			$parent_id = (int) $parent_page->ID;
		}
	}

	$page = get_page_by_path( $path, OBJECT, 'page' );

	$postarr = array(
		'post_title'     => $title,
		'post_name'      => $slug,
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'post_parent'    => $parent_id,
		'comment_status' => 'closed',
	);

	if ( $page instanceof WP_Post ) {
		$postarr['ID'] = (int) $page->ID;
	} else {
		$postarr['post_content'] = '';
	}

	if ( $overwrite || ! isset( $postarr['ID'] ) ) {
		$postarr['post_content'] = kms_get_owner_plain_content( $path );
	}

	$page_id = wp_insert_post( wp_slash( $postarr ), true );

	if ( is_wp_error( $page_id ) ) {
		return 0;
	}

	update_post_meta( $page_id, '_wp_page_template', 'default' );

	if ( $overwrite || ! $page instanceof WP_Post ) {
		kms_store_elementor_document( $page_id, kms_build_owner_page_data( $path, $page_id ) );
	}

	return (int) $page_id;
}

/**
 * Seed owner-edit mode templates.
 *
 * @param bool $overwrite Whether to overwrite existing content.
 */
function kms_run_owner_edit_seed( $overwrite = true ) {
	foreach ( kms_get_owner_edit_blueprints() as $blueprint ) {
		kms_upsert_owner_page( $blueprint, $overwrite );
	}

	$home = get_page_by_path( 'home', OBJECT, 'page' );
	if ( $home instanceof WP_Post ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $home->ID );
	}

	kms_set_seed_profile( 'owner-edit' );
	flush_rewrite_rules();
}

/**
 * Check whether a tag should remain a structural Elementor container in native parity mode.
 *
 * @param string $tag HTML tag.
 * @return bool
 */
function kms_native_parity_is_structural_tag( $tag ) {
	return in_array( $tag, array( 'main', 'section', 'div', 'article', 'aside', 'header', 'footer', 'nav', 'ul', 'ol', 'li' ), true );
}

/**
 * Read an attribute from a DOM element safely.
 *
 * @param DOMNode $node Node.
 * @param string  $name Attribute name.
 * @return string
 */
function kms_native_dom_get_attr( $node, $name ) {
	if ( ! $node instanceof DOMElement ) {
		return '';
	}

	$value = $node->getAttribute( $name );

	return is_string( $value ) ? trim( $value ) : '';
}

/**
 * Serialize DOM node outer HTML safely.
 *
 * @param DOMNode $node Node.
 * @return string
 */
function kms_native_dom_outer_html( $node ) {
	if ( ! $node instanceof DOMNode || ! $node->ownerDocument instanceof DOMDocument ) {
		return '';
	}

	$html = $node->ownerDocument->saveHTML( $node );

	return is_string( $html ) ? $html : '';
}

/**
 * Build native parity class string, preserving source classes and carrying source IDs via class token.
 *
 * @param DOMNode $node Node.
 * @return string
 */
function kms_native_parity_node_classes( $node ) {
	$classes = trim( (string) kms_native_dom_get_attr( $node, 'class' ) );
	$dom_id  = trim( (string) kms_native_dom_get_attr( $node, 'id' ) );

	if ( '' !== $dom_id ) {
		$classes .= ' kms-dom-id-' . sanitize_html_class( $dom_id );
	}

	if ( $node instanceof DOMElement && $node->hasAttributes() ) {
		$allowed_data_attrs = array( 'data-program', 'data-question', 'data-answer' );

		foreach ( $node->attributes as $attr ) {
			if ( ! $attr instanceof DOMAttr ) {
				continue;
			}

			$name  = strtolower( trim( (string) $attr->name ) );
			$value = trim( (string) $attr->value );

			if ( '' === $value || ! in_array( $name, $allowed_data_attrs, true ) ) {
				continue;
			}

			$key = substr( $name, 5 );
			if ( '' === $key ) {
				continue;
			}

			$classes .= ' kms-data-' . sanitize_html_class( $key ) . '-' . sanitize_html_class( $value );
		}
	}

	return trim( preg_replace( '/\s+/', ' ', (string) $classes ) );
}

/**
 * Serialize DOM node inner HTML safely.
 *
 * @param DOMNode $node Node.
 * @return string
 */
function kms_native_dom_inner_html( $node ) {
	if ( ! $node instanceof DOMNode || ! $node->ownerDocument instanceof DOMDocument ) {
		return '';
	}

	$html = '';
	foreach ( $node->childNodes as $child ) {
		$part = $node->ownerDocument->saveHTML( $child );
		if ( is_string( $part ) ) {
			$html .= $part;
		}
	}

	return trim( $html );
}

/**
 * Detect whether a DOM element contains unsupported custom data-* attributes.
 *
 * @param DOMNode          $node          Node.
 * @param array<int,string> $allowed_attrs Allow-listed data-* attribute names.
 * @return bool
 */
function kms_native_dom_has_data_attrs( $node, $allowed_attrs = array() ) {
	if ( ! $node instanceof DOMElement || ! $node->hasAttributes() ) {
		return false;
	}

	$allowed = array();
	if ( is_array( $allowed_attrs ) ) {
		foreach ( $allowed_attrs as $allowed_attr ) {
			$name = strtolower( trim( (string) $allowed_attr ) );
			if ( '' !== $name ) {
				$allowed[] = $name;
			}
		}
	}

	foreach ( $node->attributes as $attr ) {
		if ( ! $attr instanceof DOMAttr ) {
			continue;
		}

		$name = strtolower( (string) $attr->name );
		if ( 0 === strpos( $name, 'data-' ) && ! in_array( $name, $allowed, true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Build a native heading widget from a heading DOM node.
 *
 * @param int     $post_id Post ID.
 * @param int     $counter Running counter.
 * @param DOMNode $node    Heading node.
 * @param string  $tag     Heading tag.
 * @return array<string,mixed>|null
 */
function kms_native_parity_heading_widget( $post_id, &$counter, $node, $tag ) {
	$content = trim( (string) kms_native_dom_inner_html( $node ) );
	if ( '' === $content ) {
		$content = trim( (string) $node->textContent );
	}

	if ( '' === $content ) {
		return null;
	}

	return kms_owner_make_widget(
		$post_id,
		$counter,
		'heading',
		array(
			'title'       => (string) $content,
			'header_size' => (string) $tag,
		),
		kms_native_parity_node_classes( $node ),
		kms_native_dom_get_attr( $node, 'id' )
	);
}

/**
 * Build a native image widget from an image DOM node when safe.
 *
 * @param int     $post_id Post ID.
 * @param int     $counter Running counter.
 * @param DOMNode $node    Image node.
 * @return array<string,mixed>|null
 */
function kms_native_parity_image_widget( $post_id, &$counter, $node ) {
	if (
		! $node instanceof DOMElement ||
		kms_native_dom_has_data_attrs(
			$node,
			array(
				'data-lazy-src',
				'data-lazy-srcset',
				'data-src',
				'data-srcset',
			)
		)
	) {
		return null;
	}

	$src = trim( (string) kms_native_dom_get_attr( $node, 'src' ) );
	if ( '' === $src ) {
		$src = trim( (string) kms_native_dom_get_attr( $node, 'data-lazy-src' ) );
	}

	if ( '' === $src ) {
		return null;
	}

	$settings = array(
		'image'      => array(
			'id'  => 0,
			'url' => esc_url_raw( (string) $src ),
		),
		'image_size' => 'full',
	);

	$alt = trim( (string) kms_native_dom_get_attr( $node, 'alt' ) );
	if ( '' !== $alt ) {
		$settings['alt'] = $alt;
	}

	return kms_owner_make_widget(
		$post_id,
		$counter,
		'image',
		$settings,
		kms_native_parity_node_classes( $node ),
		kms_native_dom_get_attr( $node, 'id' )
	);
}

/**
 * Determine whether an anchor tag should be converted to a native button widget.
 *
 * @param DOMNode $node Anchor node.
 * @return bool
 */
function kms_native_parity_is_button_anchor( $node ) {
	if ( ! $node instanceof DOMElement ) {
		return false;
	}

	$class = strtolower( (string) kms_native_dom_get_attr( $node, 'class' ) );
	if ( '' === $class ) {
		return false;
	}

	return false !== strpos( $class, 'button' ) || false !== strpos( $class, 'btn' ) || false !== strpos( $class, 'cta' );
}

/**
 * Build a native button widget from an anchor DOM node when safe.
 *
 * @param int     $post_id Post ID.
 * @param int     $counter Running counter.
 * @param DOMNode $node    Anchor node.
 * @return array<string,mixed>|null
 */
function kms_native_parity_button_widget( $post_id, &$counter, $node ) {
	if ( ! $node instanceof DOMElement || kms_native_dom_has_data_attrs( $node ) ) {
		return null;
	}

	if ( (int) $node->childElementCount > 0 ) {
		return null;
	}

	$href = trim( (string) kms_native_dom_get_attr( $node, 'href' ) );
	$text = trim( (string) $node->textContent );

	if ( '' === $href || '' === $text ) {
		return null;
	}

	return kms_owner_make_widget(
		$post_id,
		$counter,
		'button',
		array(
			'text' => $text,
			'size' => 'md',
			'link' => array(
				'url' => (string) $href,
			),
		),
		kms_native_parity_node_classes( $node ),
		kms_native_dom_get_attr( $node, 'id' )
	);
}

/**
 * Build a native text-editor widget for parity mode.
 *
 * @param int    $post_id Post ID.
 * @param int    $counter Running counter.
 * @param string $html    HTML content.
 * @return array<string,mixed>
 */
function kms_native_parity_text_widget( $post_id, &$counter, $html ) {
	return kms_owner_make_widget(
		$post_id,
		$counter,
		'text-editor',
		array(
			'editor' => (string) $html,
		)
	);
}

/**
 * Convert one DOM node into a native parity Elementor element.
 *
 * @param DOMNode $node     Node.
 * @param int     $post_id  Post ID.
 * @param int     $counter  Running counter.
 * @param bool    $is_inner Is inner container.
 * @return array<string,mixed>|null
 */
function kms_native_parity_dom_to_element( $node, $post_id, &$counter, $is_inner ) {
	if ( ! $node instanceof DOMNode ) {
		return null;
	}

	if ( XML_COMMENT_NODE === $node->nodeType ) {
		return null;
	}

	if ( XML_TEXT_NODE === $node->nodeType ) {
		$text = trim( (string) $node->nodeValue );

		if ( '' === $text ) {
			return null;
		}

		return kms_native_parity_text_widget( $post_id, $counter, '<p>' . esc_html( $text ) . '</p>' );
	}

	if ( XML_ELEMENT_NODE !== $node->nodeType ) {
		return null;
	}

	$tag = strtolower( (string) $node->nodeName );

	// Script tags are moved to the theme/plugin JS layer where needed.
	if ( 'script' === $tag ) {
		return null;
	}

	$outer_html = trim( (string) kms_native_dom_outer_html( $node ) );
	if ( '' === $outer_html ) {
		return null;
	}

	if ( in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ) {
		$heading_widget = kms_native_parity_heading_widget( $post_id, $counter, $node, $tag );
		if ( is_array( $heading_widget ) ) {
			return $heading_widget;
		}
	}

	if ( 'img' === $tag ) {
		$image_widget = kms_native_parity_image_widget( $post_id, $counter, $node );
		if ( is_array( $image_widget ) ) {
			return $image_widget;
		}
	}

	if ( 'a' === $tag && kms_native_parity_is_button_anchor( $node ) ) {
		$button_widget = kms_native_parity_button_widget( $post_id, $counter, $node );
		if ( is_array( $button_widget ) ) {
			return $button_widget;
		}
	}

	if ( ! kms_native_parity_is_structural_tag( $tag ) ) {
		return kms_native_parity_text_widget( $post_id, $counter, $outer_html );
	}

	$children = array();
	foreach ( $node->childNodes as $child ) {
		$child_element = kms_native_parity_dom_to_element( $child, $post_id, $counter, true );
		if ( is_array( $child_element ) ) {
			$children[] = $child_element;
		}
	}

	if ( empty( $children ) ) {
		return kms_native_parity_text_widget( $post_id, $counter, $outer_html );
	}

	return kms_owner_make_container(
		$post_id,
		$counter,
		$children,
		kms_native_parity_node_classes( $node ),
		kms_native_dom_get_attr( $node, 'id' ),
		(bool) $is_inner,
		$tag
	);
}

/**
 * Build native parity Elementor data from page HTML.
 *
 * @param string $html    HTML source.
 * @param int    $post_id Post ID.
 * @return array<int,mixed>
 */
function kms_build_native_parity_data( $html, $post_id ) {
	$markup = trim( (string) $html );
	$counter = 0;

	if ( '' === $markup || ! class_exists( 'DOMDocument' ) || ! class_exists( 'DOMXPath' ) ) {
		return array(
			kms_native_parity_text_widget( $post_id, $counter, $markup ),
		);
	}

	$doc        = new DOMDocument();
	$wrapped    = '<?xml encoding="utf-8" ?><div id="kms-native-root">' . $markup . '</div>';
	$libxml_old = libxml_use_internal_errors( true );
	$loaded     = $doc->loadHTML( $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $libxml_old );

	if ( ! $loaded ) {
		return array(
			kms_native_parity_text_widget( $post_id, $counter, $markup ),
		);
	}

	$xpath   = new DOMXPath( $doc );
	$nodes   = $xpath->query( '//*[@id="kms-native-root"]/*|//*[@id="kms-native-root"]/text()|//*[@id="kms-native-root"]/comment()' );

	$data = array();

	if ( $nodes instanceof DOMNodeList ) {
		foreach ( $nodes as $node ) {
			$element = kms_native_parity_dom_to_element( $node, $post_id, $counter, false );
			if ( is_array( $element ) ) {
				$data[] = $element;
			}
		}
	}

	if ( empty( $data ) ) {
		return array(
			kms_native_parity_text_widget( $post_id, $counter, $markup ),
		);
	}

	return $data;
}

/**
 * Upsert one page using native parity Elementor data.
 *
 * @param array<string,string> $blueprint Page blueprint.
 * @param bool                 $overwrite Overwrite content.
 * @return int
 */
function kms_upsert_native_parity_page( $blueprint, $overwrite ) {
	$path      = $blueprint['path'];
	$title     = $blueprint['title'];
	$template  = $blueprint['template'];
	$slug      = basename( $path );
	$parent_id = 0;

	if ( false !== strpos( $path, '/' ) ) {
		$parent_path = dirname( $path );
		$parent_page = get_page_by_path( $parent_path, OBJECT, 'page' );
		if ( $parent_page instanceof WP_Post ) {
			$parent_id = (int) $parent_page->ID;
		}
	}

	$page = get_page_by_path( $path, OBJECT, 'page' );

	$postarr = array(
		'post_title'     => $title,
		'post_name'      => $slug,
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'post_parent'    => $parent_id,
		'comment_status' => 'closed',
	);

	if ( $page instanceof WP_Post ) {
		$postarr['ID'] = (int) $page->ID;
	} else {
		$postarr['post_content'] = '';
	}

	$page_html = '';
	if ( $overwrite || ! isset( $postarr['ID'] ) ) {
		$page_html               = kms_get_page_html( $template, $title, $path );
		$page_html               = kms_localize_internal_links( $page_html );
		$page_html               = kms_replace_asset_urls_in_markup( $page_html );
		$postarr['post_content'] = wp_strip_all_tags( (string) $title );
	}

	$page_id = wp_insert_post( wp_slash( $postarr ), true );

	if ( is_wp_error( $page_id ) ) {
		return 0;
	}

	update_post_meta( $page_id, '_wp_page_template', 'default' );

	if ( $overwrite || ! $page instanceof WP_Post ) {
		kms_store_elementor_document( $page_id, kms_build_native_parity_data( $page_html, $page_id ) );
	}

	return (int) $page_id;
}

/**
 * Run native parity seed (fully native Elementor widgets + maximum Kiddie parity).
 *
 * @param bool $overwrite Overwrite content.
 */
function kms_run_native_parity_seed( $overwrite = true ) {
	$blueprints = kms_sort_blueprints_by_depth( kms_get_page_blueprints() );

	foreach ( $blueprints as $blueprint ) {
		kms_upsert_native_parity_page( $blueprint, $overwrite );
	}

	$home = get_page_by_path( 'home', OBJECT, 'page' );
	if ( $home instanceof WP_Post ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $home->ID );
	}

	$blog = get_page_by_path( 'newsroom', OBJECT, 'page' );
	if ( $blog instanceof WP_Post ) {
		update_option( 'page_for_posts', (int) $blog->ID );
	}

	kms_set_seed_profile( 'native-parity' );
	flush_rewrite_rules();
}

/**
 * Return reduced page set for a single-location daycare operation.
 *
 * @return array<int,array<string,string>>
 */
function kms_get_small_business_blueprints() {
	return array(
		array( 'path' => 'home', 'title' => 'Home', 'template' => 'home' ),
		array( 'path' => 'life-at-chestnut', 'title' => 'Life at Chestnut', 'template' => 'life-at-chestnut' ),
		array( 'path' => 'faq', 'title' => 'Frequently Asked Questions', 'template' => 'faq' ),
		array( 'path' => 'contact-us', 'title' => 'Contact Us', 'template' => 'contact-us' ),
	);
}

/**
 * Remove DOM nodes for XPath selector list.
 *
 * @param DOMXPath             $xpath  XPath instance.
 * @param array<int,string>    $queries XPath queries.
 */
function kms_small_business_remove_nodes( $xpath, $queries ) {
	if ( ! $xpath instanceof DOMXPath || ! is_array( $queries ) ) {
		return;
	}

	foreach ( $queries as $query ) {
		if ( ! is_string( $query ) || '' === $query ) {
			continue;
		}

		$nodes = $xpath->query( $query );
		if ( ! $nodes instanceof DOMNodeList || 0 === $nodes->length ) {
			continue;
		}

		for ( $index = $nodes->length - 1; $index >= 0; $index-- ) {
			$node = $nodes->item( $index );
			if ( ! $node instanceof DOMNode || ! $node->parentNode instanceof DOMNode ) {
				continue;
			}

			$node->parentNode->removeChild( $node );
		}
	}
}

/**
 * Map oversized-franchise links to small-business core routes.
 *
 * @param string $href Source link.
 * @return string
 */
function kms_small_business_map_href( $href ) {
	if ( ! is_string( $href ) || '' === trim( $href ) ) {
		return $href;
	}

	$parsed = wp_parse_url( $href );
	if ( ! is_array( $parsed ) || empty( $parsed['path'] ) ) {
		return $href;
	}

	$path = '/' . ltrim( (string) $parsed['path'], '/' );

	if ( preg_match( '#^/academies(?:/|$)#', $path ) ) {
		return home_url( '/contact-us/' );
	}

	if ( preg_match( '#^/(our-curriculum|academies/programs)(?:/|$)#', $path ) ) {
		return home_url( '/life-at-chestnut/' );
	}

	if ( preg_match( '#^/company(?:/|$)#', $path ) ) {
		return home_url( '/#about-home' );
	}

	if ( preg_match( '#^/privacy-policy(?:/|$)#', $path ) ) {
		return home_url( '/contact-us/' );
	}

	if ( preg_match( '#^/(careers|franchising|corporate-careers)(?:/|$)#', $path ) ) {
		return home_url( '/contact-us/' );
	}

	if ( preg_match( '#^/(academic-leadership|community-essentials|accreditation)(?:/|$)#', $path ) ) {
		return home_url( '/company/' );
	}

	if ( preg_match( '#^/(parent-testimonials|newsroom|store)(?:/|$)#', $path ) ) {
		return home_url( '/faq/' );
	}

	return $href;
}

/**
 * Return homepage About block markup used for one-page navigation.
 *
 * @return string
 */
function kms_get_home_about_section_html() {
	return <<<HTML
<section id="about-home" class="about-home full-width-text margin-top margin-bottom left-aligned bg-default">
	<div class="content-wrapper">
		<div class="content">
			<div class="text">
				<span id="about-home"></span>
				<h2>A Small School with a Big Heart</h2>
				<p>Chestnut Square Academy is located in the heart of Historic Downtown McKinney and serves children from 6 weeks through 4/5 years of age.</p>
				<p>As a small center, we prioritize close relationships with families, consistent routines, and warm daily care.</p>
				<p>CSA is part of the Texas Rising Star Program and committed to serving children and their families with dependable, community-centered support.</p>
				<div class="card-copy">
					<h3>Our Approach</h3>
					<p>Our classrooms focus on:</p>
					<ul>
						<li>Warm, responsive care in every room</li>
						<li>Age-appropriate learning experiences</li>
						<li>Family partnership and communication</li>
						<li>A neighborhood-centered school community in Downtown McKinney</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>
HTML;
}

/**
 * Redirect retired single-site routes to active destinations.
 */
function kms_redirect_retired_small_business_routes() {
	if ( is_admin() ) {
		return;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
	$request_uri = trim( wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );

	if ( 'company' === $request_uri ) {
		wp_safe_redirect( home_url( '/#about-home' ), 301 );
		exit;
	}

	if ( 'privacy-policy' === $request_uri ) {
		wp_safe_redirect( home_url( '/contact-us/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'kms_redirect_retired_small_business_routes', 1 );

/**
 * Apply small-business section/component trimming without touching styles.
 *
 * @param string $path Page path.
 * @param string $html Original HTML.
 * @return string
 */
function kms_trim_small_business_html( $path, $html ) {
	if ( ! is_string( $html ) || '' === trim( $html ) ) {
		return $html;
	}

	$queries_map = array(
		'home'           => array(
			"//*[@id='hero']//*[contains(concat(' ', normalize-space(@class), ' '), ' locator ')]",
			"//*[@id='curriculum']",
			"//*[@id='why-kiddie']",
			"//section[contains(translate(normalize-space(string(.)),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'learning with momentum')]",
			"//*[@id='join-us']",
			"//*[@id='start-your-career']",
			"//*[@id='testimonial']",
			"//*[@id='contact-us-academy']",
			"//*[@id='faqs']",
			"//*[contains(concat(' ', normalize-space(@class), ' '), ' featured-blogs ')]",
			"//*[contains(concat(' ', normalize-space(@class), ' '), ' curated-blog-posts ')]",
			"//*[contains(concat(' ', normalize-space(@class), ' '), ' blog-search ')]/ancestor::section[1]",
			"//*[contains(concat(' ', normalize-space(@class), ' '), ' blog-preview-section ')]",
			"//*[@data-program='school-age']",
			"//*[@data-program='summer-camp-program']",
			"//*[@data-program='kindergarten']",
			"//*[contains(@href,'/kindergarten/')]/ancestor::*[contains(concat(' ', normalize-space(@class), ' '), ' slide ')][1]",
			"//*[contains(@href,'school-age-programs')]/ancestor::*[contains(concat(' ', normalize-space(@class), ' '), ' slide ')][1]",
			"//*[contains(@href,'summer-camp')]/ancestor::*[contains(concat(' ', normalize-space(@class), ' '), ' slide ')][1]",
		),
		'company'        => array(
			"//*[@id='experts']",
			"//*[@id='standards']",
			"//*[@id='join-us']",
			"//*[contains(concat(' ', normalize-space(@class), ' '), ' featured-blogs ')]",
			"//*[@id='contact-us-academy']",
		),
		'our-curriculum' => array(
			"//*[self::section and contains(concat(' ', normalize-space(@class), ' '), ' small-cards ')]",
			"//*[self::section and contains(concat(' ', normalize-space(@class), ' '), ' image-text ')]",
			"//*[@id='testimonial']",
			"//*[@id='contact-us-academy']",
			"//*[@data-program='school-age']",
			"//*[@data-program='summer-camp-program']",
			"//*[@data-program='kindergarten']",
			"//*[contains(@href,'/kindergarten/')]/ancestor::*[contains(concat(' ', normalize-space(@class), ' '), ' slide ')][1]",
			"//*[contains(@href,'school-age-programs')]/ancestor::*[contains(concat(' ', normalize-space(@class), ' '), ' slide ')][1]",
			"//*[contains(@href,'summer-camp')]/ancestor::*[contains(concat(' ', normalize-space(@class), ' '), ' slide ')][1]",
		),
		'life-at-chestnut' => array(
			"//*[contains(concat(' ', normalize-space(@class), ' '), ' blog-preview-section ')]",
			"//*[@id='contact-us-academy']",
		),
		'contact-us'     => array(
			"//*[@id='contact-us-academy']",
		),
	);

	if ( isset( $queries_map[ $path ] ) ) {
		$internal_errors = libxml_use_internal_errors( true );
		$document        = new DOMDocument( '1.0', 'UTF-8' );
		$wrapped_html    = '<div id="kms-small-root">' . $html . '</div>';

		if ( $document->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapped_html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ) ) {
			$xpath = new DOMXPath( $document );
			kms_small_business_remove_nodes( $xpath, $queries_map[ $path ] );

			$root = $document->getElementById( 'kms-small-root' );
			if ( $root instanceof DOMElement ) {
				$rebuilt = '';
				foreach ( $root->childNodes as $child ) {
					$rebuilt .= $document->saveHTML( $child );
				}
				$html = $rebuilt;
			}
		}

		libxml_clear_errors();
		libxml_use_internal_errors( $internal_errors );
	}

	if ( 'home' === $path && false === strpos( $html, 'id="about-home"' ) ) {
		$about_section = kms_get_home_about_section_html();
		$count         = 0;
		$updated_home  = preg_replace( '/(<section id="hero"[\s\S]*?<\/section>)/i', '$1' . $about_section, $html, 1, $count );

		if ( is_string( $updated_home ) && $count > 0 ) {
			$html = $updated_home;
		} else {
			$html .= $about_section;
		}
	}

	$internal_errors = libxml_use_internal_errors( true );
	$document        = new DOMDocument( '1.0', 'UTF-8' );
	$wrapped_html    = '<div id="kms-small-links-root">' . $html . '</div>';

	if ( $document->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapped_html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ) ) {
		$anchors = $document->getElementsByTagName( 'a' );
		if ( $anchors instanceof DOMNodeList && $anchors->length > 0 ) {
			foreach ( $anchors as $anchor ) {
				if ( ! $anchor instanceof DOMElement || ! $anchor->hasAttribute( 'href' ) ) {
					continue;
				}

				$href    = (string) $anchor->getAttribute( 'href' );
				$mapped  = kms_small_business_map_href( $href );
				if ( $mapped !== $href ) {
					$anchor->setAttribute( 'href', $mapped );
				}
			}
		}

		$root = $document->getElementById( 'kms-small-links-root' );
		if ( $root instanceof DOMElement ) {
			$rebuilt = '';
			foreach ( $root->childNodes as $child ) {
				$rebuilt .= $document->saveHTML( $child );
			}
			$html = $rebuilt;
		}
	}

	libxml_clear_errors();
	libxml_use_internal_errors( $internal_errors );

	$hero_tagline_html = 'Rooted in Care.<br><span class="headline-highlight">Growing Together.</span>';

	$text_replacements = array(
		'Where <span class="headline-highlight">LEARNING</span> Grows' => $hero_tagline_html,
		'Where <span class="headline-highlight">Learning</span> Grows' => $hero_tagline_html,
		'Where <span>Learning</span> Grows' => $hero_tagline_html,
		'Rooted in Care. Growing Together.' => $hero_tagline_html,
		'Rooted in Care.<br>Growing Together.' => $hero_tagline_html,
		'Learning for Every Age' => 'Learning by Age Group',
		'5/6 years of age' => '4/5 years of age',
		'5/6 years old' => '4/5 years old',
		'5/6 years' => '4/5 years',
		'Programs at a Glance' => 'LIFE AT CHESTNUT',
		'<p class="program-title">Pre-Kindergarten</p>' => '<p class="program-title">Pre-K</p>',
		'aria-label="Pre-Kindergarten 4-Year-Olds"' => 'aria-label="Pre-K 4 to 5-Year-Olds"',
		'alt="Pre-Kindergarten Program (mobile)"' => 'alt="Pre-K Program (mobile)"',
		'alt="Pre-Kindergarten Program"' => 'alt="Pre-K Program"',
		'Learning with momentum for every stage.' => '',
		'Learning with momentum' => '',
		'https://kiddieacademy.com/wp-content/uploads/2024/09/landing-hero-jpg.avif' => trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/cover.png',
		'https://kiddieacademy.com/wp-content/uploads/2024/09/landing-hero-mobile-updated2-jpg.avif' => trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/cover.png',
		'alt="Kiddie Academy"' => 'alt="Chestnut Square Academy"',
		'Find an Academy Near You'             => 'Schedule a Tour',
		'Find Your Academy'                    => 'Schedule a Tour',
		'View All Academies'                   => 'Schedule a Tour',
		'Why Parents Love Kiddie Academy<sup>Ã‚Â®</sup>' => 'Why Families Choose Chestnut Square Academy',
		'Why Parents Love Kiddie Academy<sup>Â®</sup>' => 'Why Families Choose Chestnut Square Academy',
		'The Kiddie Academy Difference'        => 'The Chestnut Square Academy Difference',
		'A Curriculum Focused on Outcomes'     => 'A Care-and-Learning Approach',
		'Our educators nurture, educate, and inspire your child through our proprietary <em>Life Essentials<sup>Ã‚Â®</sup></em> curriculum, which is designed to focus on the six outcomes that prepare children for life.' => 'Our teachers create calm, nurturing classrooms where children build confidence, routines, and early learning skills every day.',
		'About Life Essentials'                => 'Our Approach',
		'Your childÃ¢â‚¬â„¢s safety is our first priority. Every Kiddie Academy Educational Child Care features secure, restricted entries and employees are trained on health and safety protocols.' => 'Your child&rsquo;s safety is our first priority. Our team follows classroom safety procedures and daily supervision practices designed for young learners.',
		'About Health & Safety'                => 'Health & Safety',
		'We are a community where lifelong friendships and lasting memories are formed.' => 'As a small Downtown McKinney center, we build close relationships with children and families.',
		'Community Begins Here<sup>Ã‚Â®</sup>'    => 'A Neighborhood School Community',
		'Community Begins Here<sup>Â®</sup>'     => 'Rooted in Care',
		'Making the Most of Every Learning Moment, from Day One' => 'Care, Learning, and Family Partnership in Downtown McKinney',
		'WeÃ¢â‚¬â„¢ve been shaping, fueling, and nurturing childrenÃ¢â‚¬â„¢s natural curiosity since we opened our first Academy over 40 years ago. WeÃ¢â‚¬â„¢re driven to prepare children for life. Through our passion for early childhood education, community commitment, and our <em>Life Essentials</em><sup>Ã‚Â®</sup> curriculum, weÃ¢â‚¬â„¢re here to educate and encourage your child to do more and be moreÃ¢â‚¬â€not just while theyÃ¢â‚¬â„¢re with us, but outside the classroom as well.' => 'Chestnut Square Academy is a small childcare center in Historic Downtown McKinney serving children from 6 weeks through 4/5 years. As a Texas Rising Star participant, we focus on warm relationships, reliable care, and strong early-learning foundations.',
		'Weâ€™ve been shaping, fueling, and nurturing childrenâ€™s natural curiosity since we opened our first Academy over 40 years ago. Weâ€™re driven to prepare children for life. Through our passion for early childhood education, community commitment, and our <em>daily learning</em><sup>Â®</sup> curriculum, weâ€™re here to educate and encourage your child to do more and be moreâ€”not just while theyâ€™re with us, but outside the classroom as well.' => 'Chestnut Square Academy is a small childcare center in Historic Downtown McKinney serving children from 6 weeks through 4/5 years. As a Texas Rising Star daycare in Downtown McKinney, we focus on warm relationships, reliable care, and strong early-learning foundations.',
		'Explore Our History'                  => 'Schedule a Tour',
		'href="/about-us/timeline/"'          => 'href="/contact-us/"',
		'Care and curriculum go hand in hand. We nurture and guide children, inspiring them to develop a life-long love of learning. Our proprietary curriculum, <em>Life Essentials</em>, was crafted to focus on <a href="/contact-us/" aria-label="Outcomes for Life - six key outcomes">six key outcomes</a>, preparing your child for life.' => 'Care and learning go hand in hand. We guide children with age-appropriate activities, supportive routines, and family communication that helps each child grow with confidence.',
		'We are a community that nurtures, educates, and inspires children for the future in Kiddie Academy locations across the country.' => 'We are a close-knit school community that nurtures, educates, and inspires children in the heart of Downtown McKinney.',
		'Founded in 1981, George and Pauline Miller combined their passion to create an educational child care brand that creates lasting memories. The Miller family continues to own and operate Kiddie Academy to this day. Over the years, they have empowered other families to own and operate Academies in their communities.' => 'Chestnut Square Academy is family-focused and community-centered, with a small enrollment that allows personal attention and strong relationships.',
		'What are your teacher qualifications and training requirements?' => 'What are your teacher qualifications and training requirements?',
		'All Kiddie Academy educators must meet or exceed the state requirements for child care providers, including background clearance, education qualifications, and ongoing professional development.' => 'Our educators meet Texas licensing requirements, including background checks, required training, and ongoing professional development.',
		'How much is tuition at Kiddie Academy ?' => 'How can I learn about tuition and enrollment?',
		'Tuition at Kiddie Academy is affected by your child&rsquo;s age, programs, days and hours your child attends, and location. Contact your local Academy for specific costs.' => 'Tuition depends on your child&rsquo;s age and weekly schedule. Contact us directly for current enrollment details.',
		'Focused on six key outcomes for your child, our proprietary, developmentally-appropriate' => 'Our age-appropriate lessons combine teacher guidance, hands-on play, and social-emotional growth throughout the day.',
		'Life Essentials'                      => 'daily learning',
		'Kiddie Academy FAQs'                 => 'Chestnut Square Academy FAQs',
		'Programs vary by Academy location, with options organized by age and developmental stage from infants through school-age programs.' => 'Programs are organized by age and developmental stage from infancy through pre-k.',
		'Scheduling options can differ by location and classroom availability. Contact your preferred Academy to review current enrollment options.' => 'Scheduling options depend on classroom availability. Contact us to review current openings and tour times.',
		'What should we bring on the first day?' => 'What should we bring on the first day?',
		'Your Academy will share a classroom-specific checklist before start date, including required forms, comfort items, and daily essentials.' => 'Our team will share a classroom checklist before your start date, including required forms and daily comfort items.',
		'Learning with momentum'               => 'Schedule a Tour',
		'Our approach to early education is to capture the momentum of curiosity and involve parents in every minute of it.' => 'We would love to meet your family, answer your questions, and help you explore the best classroom fit for your child.',
		'Contact <br>Kiddie Academy Corporate' => 'Contact <br>Chestnut Square Academy',
		'Kiddie Academy Corporate'             => 'Chestnut Square Academy',
		'3415 Box Hill Corporate Center Drive' => '402 S. Chestnut St.',
		'Abingdon, MD 21009'                   => 'McKinney, TX',
		'Kiddie Academy<sup>Ã‚Â®</sup>'          => 'Chestnut Square Academy',
		'Kiddie Academy<sup>Â®</sup>'           => 'Chestnut Square Academy',
		'Local: <a href="tel:410-515-0788"><b>410-515-0788</b></a>' => 'Hours: Monday-Friday, 6:00 AM-6:00 PM',
		'Toll-free: <a href="tel:800-554-3343"><b>800-554-3343</b></a>' => '',
		'To contact your local Kiddie Academy, <a href="/contact-us/"><b>find our nearest location here</b></a>.' => 'Schedule a tour and our team will contact you with current availability.',
		'Kiddie Academy Educational Child Care' => 'Chestnut Square Academy',
		'Kiddie Academy Parent'                => 'Chestnut Square Academy Family',
		'Every Academy environment is designed to support age-appropriate learning, social development, and strong family partnership.' => 'Our classrooms support age-appropriate learning, social growth, and strong family partnership.',
	);

	$html = str_replace( array_keys( $text_replacements ), array_values( $text_replacements ), $html );

	$html = preg_replace(
		'/To contact your local Kiddie Academy,\s*<a[^>]*>\s*<b>find our nearest location here<\/b>\s*<\/a>\s*\.?/i',
		'Schedule a tour and our team will contact you with current availability.',
		$html
	);

	$html = preg_replace(
		'/Kiddie Academy<sup>\s*(?:Ã‚)?Â®\s*<\/sup>/i',
		'Chestnut Square Academy',
		$html
	);

	return $html;
}

/**
 * Return targeted text-only replacements for real page data migration.
 *
 * @return array<string,string>
 */
function kms_get_real_data_text_replacements() {
	return array(
		'Learning for Every Age' => 'Learning by Age Group',
		'Community Begins Here<sup>Ãƒâ€šÃ‚Â®</sup>' => 'Rooted in Care',
		'Community Begins Here<sup>Ã‚Â®</sup>' => 'Rooted in Care',
		'Community Begins Here<sup>Â®</sup>' => 'Rooted in Care',
		'Community Begins Here Â®' => 'Rooted in Care',
		'daily learning Â®' => 'daily learning',
		'daily learning &reg;' => 'daily learning',
		'Our educators nurture, educate, and inspire your child through our proprietary daily learning Â® curriculum, which is designed to focus on the six outcomes that prepare children for life.' => 'Our teachers create calm, nurturing classrooms where children build confidence, routines, and early learning skills every day.',
		'Our proprietary curriculum, daily learning , was crafted to focus on six key outcomes , preparing your child for life.' => 'Our approach combines age-appropriate activities, supportive routines, and family communication that helps each child grow with confidence.',
		'Your childâ€™s safety is our first priority. Every Chestnut Square Academy features secure, restricted entries and employees are trained on health and safety protocols.' => 'Your childâ€™s safety is our first priority. Our team follows classroom safety procedures and daily supervision practices designed for young learners.',
		'Weâ€™ve been shaping, fueling, and nurturing childrenâ€™s natural curiosity since we opened our first Academy over 40 years ago. Weâ€™re driven to prepare children for life. Through our passion for early childhood education, community commitment, and our daily learning Â® curriculum, weâ€™re here to educate and encourage your child to do more and be moreâ€”not just while theyâ€™re with us, but outside the classroom as well.' => 'Chestnut Square Academy is a small childcare center in Historic Downtown McKinney serving children from 6 weeks through 4/5 years. As a Texas Rising Star daycare in Downtown McKinney, we focus on warm relationships, reliable care, and strong early-learning foundations.',
		'We\'ve been shaping, fueling, and nurturing children\'s natural curiosity since we opened our first Academy over 40 years ago. We\'re driven to prepare children for life. Through our passion for early childhood education, community commitment, and our daily learning Â® curriculum, we\'re here to educate and encourage your child to do more and be more-not just while they\'re with us, but outside the classroom as well.' => 'Chestnut Square Academy is a small childcare center in Historic Downtown McKinney serving children from 6 weeks through 4/5 years. As a Texas Rising Star daycare in Downtown McKinney, we focus on warm relationships, reliable care, and strong early-learning foundations.',
		'Kiddie Academy' => 'Chestnut Square Academy',
	);
}

/**
 * Apply text replacements to a string payload.
 *
 * @param string $value Raw text/HTML/JSON string.
 * @return string
 */
function kms_apply_real_data_text_replacements( $value ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return $value;
	}

	$replacements = kms_get_real_data_text_replacements();
	$value        = str_replace( array_keys( $replacements ), array_values( $replacements ), $value );

		$regex_replacements = array(
		'/Learning for Every Age/iu' => 'Learning by Age Group',
		'/\b5\s*\/\s*6\b/iu' => '4/5',
		'/Community Begins Here(?:\s*<sup>.*?<\/sup>|\s*&reg;|\s*®|\s*Â®)?/isu' => 'Rooted in Care',
		'/Our educators nurture, educate, and inspire your child through our proprietary(?:\s*<em>)?\s*daily learning(?:\s*<\/em>)?(?:\s*<sup>[^<]*<\/sup>|\s*&reg;|\s*®|\s*Â®)? curriculum, which is designed to focus on the six outcomes that prepare children for life\./iu' => 'Our teachers create calm, nurturing classrooms where children build confidence, routines, and early learning skills every day.',
		'/Our proprietary curriculum,\s*daily learning\s*,\s*was crafted to focus on\s*six key outcomes\s*,\s*preparing your child for life\./iu' => 'Our approach combines age-appropriate activities, supportive routines, and family communication that helps each child grow with confidence.',
		'/We.{0,16}ve been shaping, fueling, and nurturing children.{0,16}s natural curiosity since we opened our first Academy over 40 years ago\..*?outside the classroom as well(?:\.|!|<br\s*\/?>)?/isu' => 'Chestnut Square Academy is a small childcare center in Historic Downtown McKinney serving children from 6 weeks through 4/5 years. As a Texas Rising Star daycare in Downtown McKinney, we focus on warm relationships, reliable care, and strong early-learning foundations.',
	);

	foreach ( $regex_replacements as $pattern => $replacement ) {
		$updated = preg_replace( $pattern, $replacement, $value );
		if ( is_string( $updated ) ) {
			$value = $updated;
		}
	}

	return $value;
}

/**
 * One-time migration: update real page content + Elementor document text.
 *
 * Text-only migration; does not alter layout structure.
 */
function kms_migrate_real_page_text_once() {
	if ( '1.0.5' === (string) get_option( 'kms_real_text_migration_ver', '' ) ) {
		return;
	}

	$pages = get_posts(
		array(
			'post_type'      => array( 'page', 'elementor_library', 'wp_template', 'wp_template_part' ),
			'post_status'    => array( 'publish', 'private', 'draft', 'pending' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	if ( ! is_array( $pages ) || empty( $pages ) ) {
		update_option( 'kms_real_text_migration_ver', '1.0.5' );
		return;
	}

	foreach ( $pages as $page_id ) {
		$page_id = (int) $page_id;
		if ( $page_id <= 0 ) {
			continue;
		}

		$post = get_post( $page_id );
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$updated_content = kms_apply_real_data_text_replacements( (string) $post->post_content );
		if ( $updated_content !== (string) $post->post_content ) {
			wp_update_post(
				array(
					'ID'           => $page_id,
					'post_content' => $updated_content,
				)
			);
		}

		$elementor_raw = get_post_meta( $page_id, '_elementor_data', true );
		if ( is_string( $elementor_raw ) && '' !== $elementor_raw ) {
			$updated_elementor = kms_apply_real_data_text_replacements( $elementor_raw );
			if ( $updated_elementor !== $elementor_raw ) {
				update_post_meta( $page_id, '_elementor_data', wp_slash( $updated_elementor ) );
			}
		}
	}

	update_option( 'kms_real_text_migration_ver', '1.0.5' );
}
add_action( 'init', 'kms_migrate_real_page_text_once', 54 );

/**
 * Upsert one page using reduced native parity data.
 *
 * @param array<string,string> $blueprint Blueprint.
 * @param bool                 $overwrite Overwrite existing content.
 * @return int
 */
function kms_upsert_small_business_page( $blueprint, $overwrite ) {
	$path      = (string) $blueprint['path'];
	$title     = (string) $blueprint['title'];
	$template  = (string) $blueprint['template'];
	$slug      = basename( $path );
	$parent_id = 0;

	if ( false !== strpos( $path, '/' ) ) {
		$parent_path = dirname( $path );
		$parent_page = get_page_by_path( $parent_path, OBJECT, 'page' );
		if ( $parent_page instanceof WP_Post ) {
			$parent_id = (int) $parent_page->ID;
		}
	}

	$page = get_page_by_path( $path, OBJECT, 'page' );

	$postarr = array(
		'post_title'     => $title,
		'post_name'      => $slug,
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'post_parent'    => $parent_id,
		'comment_status' => 'closed',
	);

	if ( $page instanceof WP_Post ) {
		$postarr['ID'] = (int) $page->ID;
	} else {
		$postarr['post_content'] = '';
	}

	$page_html = '';
	if ( $overwrite || ! isset( $postarr['ID'] ) ) {
		$page_html               = kms_get_page_html( $template, $title, $path );
		$page_html               = kms_localize_internal_links( $page_html );
		$page_html               = kms_replace_asset_urls_in_markup( $page_html );
		$page_html               = kms_trim_small_business_html( $path, $page_html );
		$postarr['post_content'] = wp_strip_all_tags( (string) $title );
	}

	$page_id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $page_id ) ) {
		return 0;
	}

	update_post_meta( $page_id, '_wp_page_template', 'default' );

	if ( $overwrite || ! $page instanceof WP_Post ) {
		kms_store_elementor_document( $page_id, kms_build_native_parity_data( $page_html, $page_id ) );
	}

	return (int) $page_id;
}

/**
 * Archive full-parity pages not needed for the small-business build.
 *
 * @param array<int,string> $keep_paths Kept page paths.
 */
function kms_archive_non_small_business_pages( $keep_paths ) {
	$sorted = array_reverse( kms_sort_blueprints_by_depth( kms_get_page_blueprints() ) );

	foreach ( $sorted as $blueprint ) {
		$path = (string) $blueprint['path'];
		if ( in_array( $path, $keep_paths, true ) ) {
			continue;
		}

		$page = get_page_by_path( $path, OBJECT, 'page' );
		if ( ! $page instanceof WP_Post || 'trash' === $page->post_status ) {
			continue;
		}

		wp_trash_post( (int) $page->ID );
	}

	$keep_slugs = array();
	foreach ( $keep_paths as $keep_path ) {
		$keep_slugs[] = basename( (string) $keep_path );
	}

	$all_pages = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future' ),
			'numberposts' => -1,
		)
	);

	foreach ( $all_pages as $page ) {
		if ( ! $page instanceof WP_Post ) {
			continue;
		}

		if ( in_array( $page->post_name, $keep_slugs, true ) ) {
			continue;
		}

		if ( 'trash' === $page->post_status ) {
			continue;
		}

		wp_trash_post( (int) $page->ID );
	}
}

/**
 * Run one-time simplification pass for single-location daycare operation.
 *
 * @param bool $overwrite Overwrite existing content.
 */
function kms_run_small_business_simplification( $overwrite = true ) {
	$blueprints = kms_sort_blueprints_by_depth( kms_get_small_business_blueprints() );
	$keep_paths = array();

	foreach ( $blueprints as $blueprint ) {
		$keep_paths[] = (string) $blueprint['path'];
		kms_upsert_small_business_page( $blueprint, $overwrite );
	}

	$home = get_page_by_path( 'home', OBJECT, 'page' );
	if ( $home instanceof WP_Post ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $home->ID );
	}

	update_option( 'page_for_posts', 0 );
	kms_archive_non_small_business_pages( $keep_paths );
	kms_set_seed_profile( 'native-parity' );
	update_option( 'kms_small_business_simplify_ver', '1.0.8' );
	flush_rewrite_rules();
}

/**
 * Apply small-business simplification once after plugin/theme updates.
 */
function kms_apply_small_business_simplification_once() {
	if ( '1.0.8' === (string) get_option( 'kms_small_business_simplify_ver', '' ) ) {
		return;
	}

	kms_run_small_business_simplification( true );
}
add_action( 'init', 'kms_apply_small_business_simplification_once', 50 );

/**
 * One-time refresh of Home hero text/image after branding updates.
 */
function kms_refresh_home_hero_once() {
	if ( '1.0.4' === (string) get_option( 'kms_home_hero_refresh_ver', '' ) ) {
		return;
	}

	$blueprints = kms_get_small_business_blueprints();
	foreach ( $blueprints as $blueprint ) {
		if ( isset( $blueprint['path'] ) && in_array( (string) $blueprint['path'], array( 'home', 'life-at-chestnut' ), true ) ) {
			kms_upsert_small_business_page( $blueprint, true );
		}
	}

	update_option( 'kms_home_hero_refresh_ver', '1.0.4' );
}
add_action( 'init', 'kms_refresh_home_hero_once', 52 );

/**
 * One-time structural refresh:
 * - Home: remove age-group/why sections and add About-on-home anchor sections
 * - Life at Chestnut: add age-group tabs above gallery
 * - Contact Us: simplify to one unified content block
 * - Archive separate About/Privacy pages from nav flow
 */
function kms_refresh_structure_for_one_page_about_once() {
	if ( '1.0.3' === (string) get_option( 'kms_structure_refresh_20260323_ver', '' ) ) {
		return;
	}

	$blueprints = kms_get_small_business_blueprints();
	$keep_paths = array();

	foreach ( $blueprints as $blueprint ) {
		$path = isset( $blueprint['path'] ) ? (string) $blueprint['path'] : '';
		if ( '' === $path ) {
			continue;
		}

		$keep_paths[] = $path;

		if ( in_array( $path, array( 'home', 'life-at-chestnut', 'contact-us' ), true ) ) {
			kms_upsert_small_business_page( $blueprint, true );
		}
	}

	$home = get_page_by_path( 'home', OBJECT, 'page' );
	if ( $home instanceof WP_Post ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $home->ID );
	}

	kms_archive_non_small_business_pages( $keep_paths );
	update_option( 'kms_structure_refresh_20260323_ver', '1.0.3' );
}
add_action( 'init', 'kms_refresh_structure_for_one_page_about_once', 53 );

/**
 * One-time auto import from docs/life-at-chestnut when gallery option is empty.
 */
function kms_auto_import_life_gallery_once() {
	if ( '1.0.2' === (string) get_option( 'kms_life_gallery_auto_import_ver', '' ) ) {
		return;
	}

	$existing = kms_sanitize_life_gallery_items( get_option( 'kms_life_gallery_items', array() ) );
	$should_import = empty( $existing );

	$docs_dir   = kms_get_docs_life_gallery_dir();
	$docs_files = array();
	if ( is_dir( $docs_dir ) ) {
		$docs_patterns = array( '*.jpg', '*.jpeg', '*.png', '*.webp', '*.avif' );
		foreach ( $docs_patterns as $pattern ) {
			$matches = glob( wp_normalize_path( trailingslashit( $docs_dir ) . $pattern ) );
			if ( is_array( $matches ) ) {
				$docs_files = array_merge( $docs_files, $matches );
			}
		}
	}

	$has_docs_files = ! empty( $docs_files );

	if ( ! $should_import && $has_docs_files ) {
		$has_local_uploads = false;
		foreach ( $existing as $item ) {
			$image_id  = isset( $item['image_id'] ) ? absint( $item['image_id'] ) : 0;
			$image_url = isset( $item['image_url'] ) ? (string) $item['image_url'] : '';
			if ( $image_id > 0 || false !== strpos( $image_url, '/wp-content/uploads/' ) ) {
				$has_local_uploads = true;
				break;
			}
		}

		// If only remote fallback images are present, replace with local docs gallery set.
		if ( ! $has_local_uploads ) {
			$should_import = true;
		}
	}

	if ( ! $should_import ) {
		update_option( 'kms_life_gallery_auto_import_ver', '1.0.2' );
		return;
	}

	$imported = kms_import_life_gallery_from_docs();
	if ( ! is_wp_error( $imported ) && ! empty( $imported ) ) {
		kms_sync_life_gallery_page();
	}

	update_option( 'kms_life_gallery_auto_import_ver', '1.0.2' );
}
add_action( 'init', 'kms_auto_import_life_gallery_once', 54 );

/**
 * One-time refresh to apply image-only Life at Chestnut gallery layout.
 */
function kms_refresh_life_gallery_layout_once() {
	if ( '1.0.0' === (string) get_option( 'kms_life_gallery_layout_refresh_ver', '' ) ) {
		return;
	}

	kms_sync_life_gallery_page();
	update_option( 'kms_life_gallery_layout_refresh_ver', '1.0.0' );
}
add_action( 'init', 'kms_refresh_life_gallery_layout_once', 55 );

/**
 * Migrate legacy Kiddie/old-logo asset overrides to current CSA defaults.
 */
function kms_migrate_legacy_asset_overrides_once() {
	if ( '1.0.4' === (string) get_option( 'kms_asset_override_migration_ver', '' ) ) {
		return;
	}

	$overrides = kms_get_asset_overrides();
	$defaults  = kms_get_theme_asset_defaults();
	$changed   = false;

	$slots = array( 'header_logo_desktop', 'header_logo_mobile', 'footer_logo' );
	foreach ( $slots as $slot ) {
		$current = isset( $overrides[ $slot ] ) ? trim( (string) $overrides[ $slot ] ) : '';
		if ( '' === $current ) {
			$overrides[ $slot ] = $defaults[ $slot ];
			$changed            = true;
			continue;
		}

		if ( 'footer_logo' === $slot && $current !== $defaults[ $slot ] ) {
			$overrides[ $slot ] = $defaults[ $slot ];
			$changed            = true;
			continue;
		}

		if (
			in_array( $slot, array( 'header_logo_desktop', 'header_logo_mobile' ), true ) &&
			(
				false !== stripos( $current, 'new-logo-csa.png' ) ||
				false !== stripos( $current, 'new-logo-csa-navbar.png' ) ||
				false !== stripos( $current, 'new-logo-csa-tree-navbar.png' ) ||
				false !== stripos( $current, 'logo-square-navbar.png' ) ||
				false !== stripos( $current, 'ka-logo' )
			)
		) {
			$overrides[ $slot ] = $defaults[ $slot ];
			$changed            = true;
		}
	}

	if ( $changed ) {
		kms_set_asset_overrides( $overrides );
	}

	update_option( 'kms_asset_override_migration_ver', '1.0.4' );
}
add_action( 'init', 'kms_migrate_legacy_asset_overrides_once', 45 );

/**
 * One-time safety sync: mirror post_content into Elementor document meta.
 *
 * This keeps frontend rendering aligned even when page content was updated
 * but cached/stale Elementor document data remained behind.
 */
function kms_sync_elementor_documents_once() {
	if ( '1' === (string) get_option( 'kms_disable_legacy_runtime_sync', '1' ) ) {
		return;
	}

	if ( get_option( 'kms_elementor_sync_version' ) === '1.0.4' ) {
		return;
	}

	$blueprints = kms_get_page_blueprints();

	foreach ( $blueprints as $blueprint ) {
		$page = get_page_by_path( $blueprint['path'], OBJECT, 'page' );
		if ( ! $page instanceof WP_Post ) {
			continue;
		}

		kms_set_elementor_document( (int) $page->ID, (string) $page->post_content );
	}

	update_option( 'kms_elementor_sync_version', '1.0.4' );
}
add_action( 'init', 'kms_sync_elementor_documents_once', 25 );

/**
 * One-time runtime reseed to guarantee current templates are reflected on site.
 */
function kms_runtime_reseed_once() {
	if ( '1' === (string) get_option( 'kms_disable_legacy_runtime_sync', '1' ) ) {
		return;
	}

	if ( get_option( 'kms_runtime_seed_version' ) === '1.0.3' ) {
		return;
	}

	kms_run_native_parity_seed( true );
	kms_run_small_business_simplification( true );
	update_option( 'kms_runtime_seed_version', '1.0.3' );
}
add_action( 'init', 'kms_runtime_reseed_once', 30 );

/**
 * Get blueprint template key for a seeded page path.
 *
 * @param string $path Page path.
 * @return string
 */
function kms_get_template_for_path( $path ) {
	foreach ( kms_get_page_blueprints() as $blueprint ) {
		if ( isset( $blueprint['path'], $blueprint['template'] ) && $blueprint['path'] === $path ) {
			return (string) $blueprint['template'];
		}
	}

	return '';
}

/**
 * Runtime upgrade for legacy generic/program-detail content snapshots.
 *
 * Keeps frontend output aligned with latest seeded templates and updates
 * post content + Elementor document data so subsequent edits stay in sync.
 *
 * @param string $content Rendered content.
 * @return string
 */
function kms_upgrade_legacy_seeded_content( $content ) {
	if ( is_admin() || ! is_singular( 'page' ) || ! is_string( $content ) ) {
		return $content;
	}

	$page = get_queried_object();
	if ( ! $page instanceof WP_Post ) {
		return $content;
	}

	$path     = (string) get_page_uri( $page );
	$template = kms_get_template_for_path( $path );

	if ( '' === $template ) {
		return $content;
	}

	$needs_upgrade = false;
	$new_content   = '';

	if ( 'generic' === $template ) {
		$needs_upgrade = false !== strpos( $content, 'Learning with momentum' ) || false !== strpos( $content, 'Lorem ipsum' );

		if ( $needs_upgrade ) {
			$new_content = kms_localize_internal_links( kms_get_generic_html( (string) $page->post_title, $path ) );
		}
	} elseif ( 'program-detail' === $template ) {
		$needs_upgrade = false !== strpos( $content, 'Lorem ipsum' );

		if ( $needs_upgrade ) {
			$new_content = kms_localize_internal_links( kms_get_program_detail_html( (string) $page->post_title ) );
		}
	} elseif ( 'faq' === $template ) {
		$needs_upgrade = false !== strpos( $content, 'Lorem ipsum' );

		if ( $needs_upgrade ) {
			$new_content = kms_localize_internal_links( kms_get_faq_html() );
		}
	} elseif ( 'home' === $template ) {
		$has_escaped_hero_css = false !== strpos( $content, '#hero .background-image {<br />' ) || false !== strpos( $content, '&#8216;https://kiddieacademy.com/wp-content/uploads/2024/09/landing-hero' );

		if ( $has_escaped_hero_css ) {
			$needs_upgrade = true;
			$new_content   = kms_localize_internal_links( kms_get_template_file_html( 'home' ) );

			if ( '' === $new_content ) {
				$new_content = kms_localize_internal_links( kms_get_page_html( 'home', (string) $page->post_title, $path ) );
			}
		}
	}

	if ( ! $needs_upgrade || '' === $new_content ) {
		return $content;
	}

	wp_update_post(
		array(
			'ID'           => (int) $page->ID,
			'post_content' => $new_content,
		)
	);

	kms_set_elementor_document( (int) $page->ID, $new_content );

	return $new_content;
}
add_filter( 'the_content', 'kms_upgrade_legacy_seeded_content', 96 );

/**
 * Activation callback.
 */
function kms_activate() {
	kms_run_native_parity_seed( true );
	kms_run_small_business_simplification( true );
}
register_activation_hook( __FILE__, 'kms_activate' );

/**
 * Render a friendlier slot label for asset manager UI.
 *
 * @param string $raw_label Raw slot label.
 * @return string
 */
function kms_pretty_asset_label( $raw_label ) {
	$label = str_replace( array( '-', '_', '/' ), ' ', (string) $raw_label );
	$label = preg_replace( '/\s+/', ' ', $label );

	return ucwords( trim( (string) $label ) );
}

/**
 * Add admin pages.
 */
function kms_add_admin_pages() {
	add_management_page(
		'CSA Site Tools',
		'CSA Site Tools',
		'manage_options',
		'kiddie-mock-seed',
		'kms_render_tools_page'
	);

	add_theme_page(
		'CSA Site Assets',
		'CSA Site Assets',
		'manage_options',
		'kiddie-mock-assets',
		'kms_render_assets_page'
	);
}
add_action( 'admin_menu', 'kms_add_admin_pages' );

/**
 * Load admin-side scripts for media picker on asset page.
 *
 * @param string $hook_suffix Current admin screen hook suffix.
 */
function kms_enqueue_admin_assets( $hook_suffix ) {
	if ( 'tools_page_kiddie-mock-seed' === $hook_suffix ) {
		wp_enqueue_media();
		wp_enqueue_script(
			'kms-admin-life-gallery',
			plugin_dir_url( __FILE__ ) . 'assets/js/admin-life-gallery.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);
	}

	if ( 'appearance_page_kiddie-mock-assets' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'kms-admin-assets',
		plugin_dir_url( __FILE__ ) . 'assets/js/admin-assets.js',
		array( 'jquery' ),
		'1.0.0',
		true
	);
}
add_action( 'admin_enqueue_scripts', 'kms_enqueue_admin_assets' );

/**
 * Add owner-edit mode body class on frontend.
 *
 * @param array<int,string> $classes Body classes.
 * @return array<int,string>
 */
function kms_add_frontend_body_classes( $classes ) {
	if ( 'owner-edit' === kms_get_seed_profile() ) {
		$classes[] = 'kms-owner-mode';
	}

	if ( 'native-parity' === kms_get_seed_profile() ) {
		$classes[] = 'kms-native-parity-mode';
	}

	return $classes;
}
add_filter( 'body_class', 'kms_add_frontend_body_classes' );

/**
 * Detect Elementor editor/preview context.
 *
 * @return bool
 */
function kms_is_elementor_editor_context() {
	if ( is_admin() ) {
		return true;
	}

	if ( isset( $_GET['elementor-preview'] ) ) {
		return true;
	}

	if ( isset( $_GET['action'] ) && 'elementor' === sanitize_key( (string) wp_unslash( $_GET['action'] ) ) ) {
		return true;
	}

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) ) {
		$editor = \Elementor\Plugin::$instance->editor;
		if ( is_object( $editor ) && method_exists( $editor, 'is_edit_mode' ) && $editor->is_edit_mode() ) {
			return true;
		}
	}

	return false;
}

/**
 * Enqueue owner-edit mode styles when active.
 */
function kms_enqueue_owner_edit_styles() {
	$profile = kms_get_seed_profile();

	if ( 'owner-edit' === $profile ) {
		wp_enqueue_style(
			'kms-owner-edit-mode',
			plugin_dir_url( __FILE__ ) . 'assets/css/owner-edit-mode.css',
			array(),
			'1.0.0'
		);
	}

	if ( 'native-parity' === $profile ) {
		wp_enqueue_style(
			'kms-native-parity-mode',
			plugin_dir_url( __FILE__ ) . 'assets/css/native-parity-mode.css',
			array(),
			'1.4.7'
		);
		if ( ! kms_is_elementor_editor_context() ) {
			wp_enqueue_script(
				'kms-native-parity-front',
				plugin_dir_url( __FILE__ ) . 'assets/js/native-parity-front.js',
				array(),
				'1.4.7',
				true
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'kms_enqueue_owner_edit_styles', 40 );

/**
 * Handle tools form submit.
 */
function kms_handle_tools_actions() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! isset( $_POST['kms_action'] ) ) {
		return;
	}

	$action = sanitize_key( wp_unslash( $_POST['kms_action'] ) );

	if ( 'run_owner_seed' === $action ) {
		check_admin_referer( 'kms_run_owner_seed' );

		$overwrite = isset( $_POST['kms_overwrite'] ) && '1' === $_POST['kms_overwrite'];
		kms_run_owner_edit_seed( $overwrite );

		$redirect = add_query_arg(
			array(
				'page'         => 'kiddie-mock-seed',
				'kms_owner_ok' => '1',
			),
			admin_url( 'tools.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	if ( 'run_native_seed' === $action ) {
		check_admin_referer( 'kms_run_native_seed' );

		$overwrite = isset( $_POST['kms_overwrite'] ) && '1' === $_POST['kms_overwrite'];
		kms_run_native_parity_seed( $overwrite );

		$redirect = add_query_arg(
			array(
				'page'          => 'kiddie-mock-seed',
				'kms_native_ok' => '1',
			),
			admin_url( 'tools.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	if ( 'save_life_gallery' === $action ) {
		check_admin_referer( 'kms_save_life_gallery' );

		$items = array();
		if ( isset( $_POST['kms_life_gallery'] ) ) {
			$items = kms_sanitize_life_gallery_items( wp_unslash( $_POST['kms_life_gallery'] ) );
		}

		update_option( 'kms_life_gallery_items', $items );
		kms_sync_life_gallery_page();

		$redirect = add_query_arg(
			array(
				'page'            => 'kiddie-mock-seed',
				'kms_gallery_ok'  => '1',
			),
			admin_url( 'tools.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	if ( 'import_life_gallery_docs' === $action ) {
		check_admin_referer( 'kms_import_life_gallery_docs' );

		$imported = kms_import_life_gallery_from_docs();

		if ( is_wp_error( $imported ) ) {
			$redirect = add_query_arg(
				array(
					'page'            => 'kiddie-mock-seed',
					'kms_gallery_err' => rawurlencode( $imported->get_error_message() ),
				),
				admin_url( 'tools.php' )
			);

			wp_safe_redirect( $redirect );
			exit;
		}

		kms_sync_life_gallery_page();

		$redirect = add_query_arg(
			array(
				'page'               => 'kiddie-mock-seed',
				'kms_gallery_import' => '1',
				'kms_gallery_count'  => (string) count( $imported ),
			),
			admin_url( 'tools.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	if ( 'sync_life_gallery_page' === $action ) {
		check_admin_referer( 'kms_sync_life_gallery_page' );
		kms_sync_life_gallery_page();

		$redirect = add_query_arg(
			array(
				'page'             => 'kiddie-mock-seed',
				'kms_gallery_sync' => '1',
			),
			admin_url( 'tools.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	if ( 'save_assets' === $action ) {
		check_admin_referer( 'kms_save_assets' );

		$catalog   = kms_get_asset_catalog();
		$submitted = array();
		$overrides = array();

		if ( isset( $_POST['kms_assets'] ) && is_array( $_POST['kms_assets'] ) ) {
			$submitted = wp_unslash( $_POST['kms_assets'] );
		}

		foreach ( $catalog as $key => $item ) {
			unset( $item );

			if ( ! isset( $submitted[ $key ] ) ) {
				continue;
			}

			$raw_value = trim( (string) $submitted[ $key ] );
			if ( '' === $raw_value ) {
				continue;
			}

			$url = esc_url_raw( $raw_value );
			if ( '' !== $url ) {
				$overrides[ $key ] = $url;
			}
		}

		kms_set_asset_overrides( $overrides );

		$redirect = add_query_arg(
			array(
				'page'          => 'kiddie-mock-assets',
				'kms_assets_ok' => '1',
			),
			admin_url( 'themes.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	if ( 'reset_assets' === $action ) {
		check_admin_referer( 'kms_reset_assets' );
		kms_set_asset_overrides( array() );

		$redirect = add_query_arg(
			array(
				'page'             => 'kiddie-mock-assets',
				'kms_assets_reset' => '1',
			),
			admin_url( 'themes.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}
}
add_action( 'admin_init', 'kms_handle_tools_actions' );

/**
 * Render admin tools page.
 */
function kms_render_tools_page() {
	$owner_done      = isset( $_GET['kms_owner_ok'] ) && '1' === $_GET['kms_owner_ok'];
	$native_done     = isset( $_GET['kms_native_ok'] ) && '1' === $_GET['kms_native_ok'];
	$gallery_saved   = isset( $_GET['kms_gallery_ok'] ) && '1' === $_GET['kms_gallery_ok'];
	$gallery_import  = isset( $_GET['kms_gallery_import'] ) && '1' === $_GET['kms_gallery_import'];
	$gallery_sync    = isset( $_GET['kms_gallery_sync'] ) && '1' === $_GET['kms_gallery_sync'];
	$gallery_count   = isset( $_GET['kms_gallery_count'] ) ? absint( $_GET['kms_gallery_count'] ) : 0;
	$gallery_error   = isset( $_GET['kms_gallery_err'] ) ? sanitize_text_field( wp_unslash( rawurldecode( (string) $_GET['kms_gallery_err'] ) ) ) : '';
	$gallery_items   = kms_get_life_gallery_items();
	$profile         = kms_get_seed_profile();
	$docs_gallery_dir = kms_get_docs_life_gallery_dir();
	?>
	<div class="wrap">
		<h1>CSA Site Tools</h1>
		<?php if ( $owner_done ) : ?>
			<div class="notice notice-success"><p>Owner Edit Mode seed completed successfully.</p></div>
		<?php endif; ?>
		<?php if ( $native_done ) : ?>
			<div class="notice notice-success"><p>Native Parity Mode seed completed successfully.</p></div>
		<?php endif; ?>
		<?php if ( $gallery_saved ) : ?>
			<div class="notice notice-success"><p>Life at Chestnut gallery saved and synced to the page.</p></div>
		<?php endif; ?>
		<?php if ( $gallery_import ) : ?>
			<div class="notice notice-success"><p>Imported <?php echo esc_html( (string) $gallery_count ); ?> image(s) from docs/life-at-chestnut and synced the page.</p></div>
		<?php endif; ?>
		<?php if ( $gallery_sync ) : ?>
			<div class="notice notice-success"><p>Life at Chestnut gallery page synced from saved dashboard items.</p></div>
		<?php endif; ?>
		<?php if ( '' !== $gallery_error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $gallery_error ); ?></p></div>
		<?php endif; ?>

		<p><strong>Active profile:</strong> <code><?php echo esc_html( $profile ); ?></code></p>

		<hr>
		<h2>Owner Edit Mode</h2>
		<p>Apply stricter owner-edit templates built with native Elementor widgets for direct drag-and-drop editing on core pages.</p>
		<form method="post">
			<?php wp_nonce_field( 'kms_run_owner_seed' ); ?>
			<input type="hidden" name="kms_action" value="run_owner_seed">
			<p><label><input type="checkbox" name="kms_overwrite" value="1" checked> Overwrite existing page content</label></p>
			<p><button type="submit" class="button">Run Owner Edit Mode Seed</button></p>
		</form>

		<hr>
		<h2>Native Parity Mode (Recommended)</h2>
		<p>Seed all pages with fully native Elementor widgets while preserving the current site structure and styling as closely as possible. This is the best mode for no-code owner handoff.</p>
		<form method="post">
			<?php wp_nonce_field( 'kms_run_native_seed' ); ?>
			<input type="hidden" name="kms_action" value="run_native_seed">
			<p><label><input type="checkbox" name="kms_overwrite" value="1" checked> Overwrite existing page content</label></p>
			<p><button type="submit" class="button button-primary">Run Native Parity Seed</button></p>
		</form>

		<hr>
		<h2>Life at Chestnut Gallery Manager</h2>
		<p>Manage gallery images without code. Add/remove rows here, then click Save to sync the Life at Chestnut page.</p>
		<p><strong>Source import folder:</strong> <code><?php echo esc_html( $docs_gallery_dir ); ?></code></p>

		<form method="post" style="margin-bottom: 12px;">
			<?php wp_nonce_field( 'kms_import_life_gallery_docs' ); ?>
			<input type="hidden" name="kms_action" value="import_life_gallery_docs">
			<p><button type="submit" class="button">Import All Images from docs/life-at-chestnut</button></p>
		</form>

		<form method="post">
			<?php wp_nonce_field( 'kms_save_life_gallery' ); ?>
			<input type="hidden" name="kms_action" value="save_life_gallery">

			<table class="widefat striped" id="kms-life-gallery-table">
				<thead>
					<tr>
						<th style="width: 20%;">Image</th>
						<th style="width: 20%;">Preview</th>
						<th style="width: 20%;">Title</th>
						<th>Description</th>
						<th style="width: 18%;">Alt Text</th>
						<th style="width: 6%;">Remove</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $gallery_items as $index => $item ) : ?>
						<?php
						$image_id    = isset( $item['image_id'] ) ? absint( $item['image_id'] ) : 0;
						$image_url   = isset( $item['image_url'] ) ? (string) $item['image_url'] : '';
						$title       = isset( $item['title'] ) ? (string) $item['title'] : '';
						$description = isset( $item['description'] ) ? (string) $item['description'] : '';
						$alt         = isset( $item['alt'] ) ? (string) $item['alt'] : '';
						?>
						<tr class="kms-life-gallery-row">
							<td>
								<input type="hidden" name="kms_life_gallery[<?php echo esc_attr( (string) $index ); ?>][image_id]" value="<?php echo esc_attr( (string) $image_id ); ?>" class="kms-life-image-id">
								<input type="url" name="kms_life_gallery[<?php echo esc_attr( (string) $index ); ?>][image_url]" value="<?php echo esc_attr( $image_url ); ?>" class="regular-text code kms-life-image-url" style="width: 100%;">
								<p>
									<button type="button" class="button kms-life-select-media">Choose</button>
									<button type="button" class="button kms-life-clear-media">Clear</button>
								</p>
							</td>
							<td>
								<img src="<?php echo esc_url( $image_url ); ?>" class="kms-life-preview" alt="" style="width: 140px; height: 92px; object-fit: cover; border: 1px solid #dcdcde; border-radius: 4px; background: #f6f7f7;">
							</td>
							<td>
								<input type="text" name="kms_life_gallery[<?php echo esc_attr( (string) $index ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" class="regular-text" style="width: 100%;">
							</td>
							<td>
								<input type="text" name="kms_life_gallery[<?php echo esc_attr( (string) $index ); ?>][description]" value="<?php echo esc_attr( $description ); ?>" class="regular-text" style="width: 100%;">
							</td>
							<td>
								<input type="text" name="kms_life_gallery[<?php echo esc_attr( (string) $index ); ?>][alt]" value="<?php echo esc_attr( $alt ); ?>" class="regular-text" style="width: 100%;">
							</td>
							<td style="text-align:center;">
								<button type="button" class="button-link-delete kms-life-remove-row">Remove</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p style="margin-top: 10px;">
				<button type="button" class="button kms-life-add-row">Add Gallery Item</button>
				<button type="submit" class="button button-primary">Save Gallery and Sync Page</button>
			</p>
		</form>

		<form method="post" style="margin-top: 6px;">
			<?php wp_nonce_field( 'kms_sync_life_gallery_page' ); ?>
			<input type="hidden" name="kms_action" value="sync_life_gallery_page">
			<p><button type="submit" class="button">Sync Life at Chestnut Page Only</button></p>
		</form>
	</div>
	<?php
}

/**
 * Render asset replacement page for non-technical client handoff.
 */
function kms_render_assets_page() {
	$catalog   = kms_get_asset_catalog();
	$overrides = kms_get_asset_overrides();
	$saved     = isset( $_GET['kms_assets_ok'] ) && '1' === $_GET['kms_assets_ok'];
	$reset     = isset( $_GET['kms_assets_reset'] ) && '1' === $_GET['kms_assets_reset'];
	?>
	<div class="wrap">
		<h1>CSA Site Assets</h1>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success"><p>Asset replacements saved.</p></div>
		<?php endif; ?>

		<?php if ( $reset ) : ?>
			<div class="notice notice-warning"><p>Asset replacements were reset to defaults.</p></div>
		<?php endif; ?>

		<p>
			Use this screen to swap images without touching layout code.
			Your client can update media here while keeping the full site design intact.
		</p>

		<p>
			<input type="search" id="kms-asset-search" class="regular-text" placeholder="Filter assets by name...">
			<span class="description">Total slots: <?php echo esc_html( (string) count( $catalog ) ); ?></span>
		</p>

		<form method="post">
			<?php wp_nonce_field( 'kms_save_assets' ); ?>
			<input type="hidden" name="kms_action" value="save_assets">

			<table class="widefat striped">
				<thead>
					<tr>
						<th style="width: 20%;">Asset Slot</th>
						<th style="width: 20%;">Preview</th>
						<th>Replacement URL (optional)</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $catalog as $key => $item ) : ?>
						<?php
						$default_url = isset( $item['default_url'] ) ? (string) $item['default_url'] : '';
						$current_url = isset( $overrides[ $key ] ) ? (string) $overrides[ $key ] : '';
						$preview_url = '' !== $current_url ? $current_url : $default_url;
						$label_raw   = isset( $item['label'] ) ? (string) $item['label'] : $key;
						$label       = kms_pretty_asset_label( $label_raw );
						$field_id    = 'kms_asset_' . $key;
						$preview_id  = 'kms_preview_' . $key;
						?>
						<tr data-kms-row data-kms-label="<?php echo esc_attr( strtolower( $label . ' ' . $key ) ); ?>">
							<td>
								<strong><?php echo esc_html( $label ); ?></strong><br>
								<code><?php echo esc_html( $key ); ?></code>
							</td>
							<td>
								<img
									id="<?php echo esc_attr( $preview_id ); ?>"
									src="<?php echo esc_url( $preview_url ); ?>"
									alt="<?php echo esc_attr( $label ); ?>"
									style="width: 160px; height: 100px; object-fit: cover; border: 1px solid #dcdcde; border-radius: 4px; background: #f6f7f7;"
								>
							</td>
							<td>
								<input
									type="url"
									id="<?php echo esc_attr( $field_id ); ?>"
									name="kms_assets[<?php echo esc_attr( $key ); ?>]"
									value="<?php echo esc_attr( $current_url ); ?>"
									placeholder="<?php echo esc_attr( $default_url ); ?>"
									class="regular-text code"
									style="width: 100%; max-width: 780px;"
								>
								<p class="description">Default source:
									<a href="<?php echo esc_url( $default_url ); ?>" target="_blank" rel="noopener noreferrer">open</a>
								</p>
								<p>
									<button
										type="button"
										class="button kms-select-media"
										data-target="<?php echo esc_attr( $field_id ); ?>"
										data-preview="<?php echo esc_attr( $preview_id ); ?>"
									>Choose from Media Library</button>
									<button
										type="button"
										class="button kms-clear-media"
										data-target="<?php echo esc_attr( $field_id ); ?>"
										data-preview="<?php echo esc_attr( $preview_id ); ?>"
									>Clear</button>
								</p>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p style="margin-top: 16px;">
				<button type="submit" class="button button-primary">Save Asset Replacements</button>
			</p>
		</form>

		<form method="post" style="margin-top: 8px;">
			<?php wp_nonce_field( 'kms_reset_assets' ); ?>
			<input type="hidden" name="kms_action" value="reset_assets">
			<button type="submit" class="button">Reset All to Default Sources</button>
		</form>
	</div>
	<?php
}

