<?php
/**
 * Plugin Name: Kiddie Mock Seed
 * Description: Builds a full Kiddie Academy style frontend mock across all key pages for WordPress + Elementor testing.
 * Version: 1.3.2
 * Author: CSA Web Team
 * License: GPL-2.0-or-later
 * Text Domain: kiddie-mock-seed
 *
 * @package KiddieMockSeed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	return array(
		'header_logo_desktop' => 'https://kiddieacademy.com/wp-content/themes/kiddieacademy/assets/img/kiddie-academy-logo.png',
		'header_logo_mobile'  => 'https://kiddieacademy.com/wp-content/themes/kiddieacademy/assets/img/2023-refresh/kiddie-academy-logo-stacked.svg',
		'footer_logo'         => 'https://kiddieacademy.com/wp-content/themes/kiddieacademy/assets/img/2023-refresh/ka-logo-white-footer.svg',
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
	return kms_replace_asset_urls_in_markup( $content );
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

	return kms_replace_asset_urls_in_markup( $content );
}
add_filter( 'elementor/widget/render_content', 'kms_filter_elementor_widget_assets', 10, 2 );

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
				<h4>Community Begins Here</h4>
				<p>Kiddie Academy focuses on helping children learn through curiosity, confidence, and meaningful day-to-day experiences.</p>
				<p>Families can explore programs, connect with local Academies, and learn how educators support growth at each stage.</p>
				<a class="button-round" href="/contact-us/">Request Information</a>
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
				<p>Every Academy environment is designed to support age-appropriate learning, social development, and strong family partnership.</p>
				<p>Use the main navigation to explore approach, programs, enrollment pathways, and frequently asked questions.</p>
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
			<span class="current-page">Kiddie Academy FAQs</span>
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
				<div data-answer="q1" hidden><p>Programs vary by Academy location, with options organized by age and developmental stage from infants through school-age programs.</p></div>
			</div>
			<div>
				<p data-question="q2"><strong>Do you offer full-day and part-day options?</strong><i class="fa-solid fa-chevron-down"></i></p>
				<div data-answer="q2" hidden><p>Scheduling options can differ by location and classroom availability. Contact your preferred Academy to review current enrollment options.</p></div>
			</div>
			<div>
				<p data-question="q3"><strong>How do we schedule a tour?</strong><i class="fa-solid fa-chevron-down"></i></p>
				<div data-answer="q3" hidden><p>Use our contact page and submit your preferred day/time.</p></div>
			</div>
			<div>
				<p data-question="q4"><strong>What should we bring on the first day?</strong><i class="fa-solid fa-chevron-down"></i></p>
				<div data-answer="q4" hidden><p>Your Academy will share a classroom-specific checklist before start date, including required forms, comfort items, and daily essentials.</p></div>
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
			<div class="intro"><h2>Learning for Every Age</h2></div>
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
 * Resolve final page HTML for blueprint.
 *
 * @param string $template Template key.
 * @param string $title Title.
 * @param string $path Full path.
 * @return string
 */
function kms_get_page_html( $template, $title, $path ) {
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
		'®'                                  => '&reg;',
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
 * Seed all pages and key reading options.
 *
 * @param bool $overwrite Overwrite existing content.
 */
function kms_run_seed( $overwrite = true ) {
	$blueprints = kms_sort_blueprints_by_depth( kms_get_page_blueprints() );

	foreach ( $blueprints as $blueprint ) {
		kms_upsert_page( $blueprint, $overwrite );
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

	kms_set_seed_profile( 'mock-parity' );
	flush_rewrite_rules();
}

/**
 * Get current frontend seed profile.
 *
 * @return string
 */
function kms_get_seed_profile() {
	$profile = get_option( 'kms_seed_profile', 'mock-parity' );

	return is_string( $profile ) ? $profile : 'mock-parity';
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
	return array(
		'hero'      => 'https://kiddieacademy.com/wp-content/uploads/2024/09/landing-hero-jpg.avif',
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
		array( 'path' => 'company', 'title' => 'About Us' ),
		array( 'path' => 'our-curriculum', 'title' => 'Programs' ),
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
		'company'        => 'Learn about Chestnut Square Academy, our family-first approach, and our commitment to the Downtown McKinney community.',
		'our-curriculum' => 'Explore age-based early learning programs, daily routines, and enrichment opportunities at Chestnut Square Academy.',
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
								kms_owner_heading_widget( $post_id, $counter, 'Director Message', 'h2' ),
								kms_owner_text_widget( $post_id, $counter, '<p>Welcome to our school community. We believe children thrive when they feel safe, known, and encouraged every day.</p>' ),
								kms_owner_text_widget( $post_id, $counter, '<p>Our team is committed to partnering with families and making each classroom a place where learning and care go hand in hand.</p>' ),
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
								kms_owner_image_widget( $post_id, $counter, $images['staff'] ),
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
		kms_owner_make_container(
			$post_id,
			$counter,
			array(
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
				kms_owner_heading_widget( $post_id, $counter, 'Gallery', 'h1' ),
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
		'company'        => $about_data,
		'our-curriculum' => $programs_data,
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
		kms_native_dom_get_attr( $node, 'class' ),
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
		kms_native_dom_get_attr( $node, 'class' ),
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
		kms_native_dom_get_attr( $node, 'class' ),
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
		kms_native_dom_get_attr( $node, 'class' ),
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

	kms_run_seed( true );
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
	kms_run_seed( true );
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
		'Kiddie Mock Seed',
		'Kiddie Mock Seed',
		'manage_options',
		'kiddie-mock-seed',
		'kms_render_tools_page'
	);

	add_theme_page(
		'Kiddie Mock Assets',
		'Kiddie Mock Assets',
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
			'1.2.0'
		);
		if ( ! kms_is_elementor_editor_context() ) {
			wp_enqueue_script(
				'kms-native-parity-front',
				plugin_dir_url( __FILE__ ) . 'assets/js/native-parity-front.js',
				array(),
				'1.2.0',
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

	if ( 'run_seed' === $action ) {
		check_admin_referer( 'kms_run_seed' );

		$overwrite = isset( $_POST['kms_overwrite'] ) && '1' === $_POST['kms_overwrite'];
		kms_run_seed( $overwrite );

		$redirect = add_query_arg(
			array(
				'page'   => 'kiddie-mock-seed',
				'kms_ok' => '1',
			),
			admin_url( 'tools.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}

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
	$done        = isset( $_GET['kms_ok'] ) && '1' === $_GET['kms_ok'];
	$owner_done  = isset( $_GET['kms_owner_ok'] ) && '1' === $_GET['kms_owner_ok'];
	$native_done = isset( $_GET['kms_native_ok'] ) && '1' === $_GET['kms_native_ok'];
	$profile     = kms_get_seed_profile();
	?>
	<div class="wrap">
		<h1>Kiddie Mock Seed</h1>
		<?php if ( $done ) : ?>
			<div class="notice notice-success"><p>Seed completed successfully.</p></div>
		<?php endif; ?>
		<?php if ( $owner_done ) : ?>
			<div class="notice notice-success"><p>Owner Edit Mode seed completed successfully.</p></div>
		<?php endif; ?>
		<?php if ( $native_done ) : ?>
			<div class="notice notice-success"><p>Native Parity Mode seed completed successfully.</p></div>
		<?php endif; ?>

		<p><strong>Active profile:</strong> <code><?php echo esc_html( $profile ); ?></code></p>

		<hr>
		<h2>Mock Parity Mode</h2>
		<p>Rebuild the full Kiddie-style mock page tree using layout-parity HTML sections.</p>
		<form method="post" style="margin-bottom: 16px;">
			<?php wp_nonce_field( 'kms_run_seed' ); ?>
			<input type="hidden" name="kms_action" value="run_seed">
			<p><label><input type="checkbox" name="kms_overwrite" value="1" checked> Overwrite existing page content</label></p>
			<p><button type="submit" class="button button-primary">Run Full Mock Seed</button></p>
		</form>

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
		<h2>Native Parity Mode</h2>
		<p>Seed all pages with fully native Elementor widgets while preserving Kiddie Academy structure/content as closely as possible.</p>
		<form method="post">
			<?php wp_nonce_field( 'kms_run_native_seed' ); ?>
			<input type="hidden" name="kms_action" value="run_native_seed">
			<p><label><input type="checkbox" name="kms_overwrite" value="1" checked> Overwrite existing page content</label></p>
			<p><button type="submit" class="button">Run Native Parity Seed</button></p>
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
		<h1>Kiddie Mock Assets</h1>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success"><p>Asset replacements saved.</p></div>
		<?php endif; ?>

		<?php if ( $reset ) : ?>
			<div class="notice notice-warning"><p>Asset replacements were reset to defaults.</p></div>
		<?php endif; ?>

		<p>
			Use this screen to swap images without touching layout code.
			Your client can update media here and keep the full mock design intact.
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
