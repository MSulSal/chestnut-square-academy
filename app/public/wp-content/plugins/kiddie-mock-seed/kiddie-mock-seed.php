<?php
/**
 * Plugin Name: Kiddie Mock Seed
 * Description: Builds a full Kiddie Academy style frontend mock across all key pages for WordPress + Elementor testing.
 * Version: 1.0.3
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
 * Apply Elementor document data for editable page content.
 *
 * @param int    $post_id Post ID.
 * @param string $html HTML block.
 */
function kms_set_elementor_document( $post_id, $html ) {
	if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
		return;
	}

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

	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
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

	flush_rewrite_rules();
}

/**
 * One-time safety sync: mirror post_content into Elementor document meta.
 *
 * This keeps frontend rendering aligned even when page content was updated
 * but cached/stale Elementor document data remained behind.
 */
function kms_sync_elementor_documents_once() {
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
	$done = isset( $_GET['kms_ok'] ) && '1' === $_GET['kms_ok'];
	?>
	<div class="wrap">
		<h1>Kiddie Mock Seed</h1>
		<?php if ( $done ) : ?>
			<div class="notice notice-success"><p>Seed completed successfully.</p></div>
		<?php endif; ?>
		<p>Rebuild the full Kiddie-style mock page tree, including Elementor-editable page content.</p>
		<form method="post">
			<?php wp_nonce_field( 'kms_run_seed' ); ?>
			<input type="hidden" name="kms_action" value="run_seed">
			<p><label><input type="checkbox" name="kms_overwrite" value="1" checked> Overwrite existing page content</label></p>
			<p><button type="submit" class="button button-primary">Run Full Mock Seed</button></p>
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
