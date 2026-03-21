<?php
/**
 * Plugin Name: Kiddie Mock Seed
 * Description: Builds a full Kiddie Academy style frontend mock across all key pages for WordPress + Elementor testing.
 * Version: 1.0.0
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
 * Build generic subpage HTML.
 *
 * @param string $title Page title.
 * @param string $path Full path.
 * @return string
 */
function kms_get_generic_html( $title, $path ) {
	$title_html = esc_html( $title );
	$path_html  = esc_html( strtoupper( str_replace( '/', ' / ', $path ) ) );

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
				<h1>{$title_html}</h1>
				<p>{$path_html}</p>
			</div>
		</div>
	</section>
	<section class="image-text padding-bottom margin-top overlapping image-text media-image media-cover">
		<div class="content-wrapper">
			<div class="image">
				<img data-lazy-src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1400&q=80" class="lazy fill-container" alt="Children learning in classroom">
			</div>
			<div class="text">
				<h4>Learning with momentum</h4>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum bibendum, turpis non feugiat feugiat, enim nisl ultricies lacus, vitae posuere ex neque at sem.</p>
				<p>Phasellus posuere justo ac odio tristique, in malesuada ligula vulputate. Curabitur ultrices eros at lectus facilisis, ac sodales felis gravida.</p>
				<a class="button-round" href="/contact-us/">Contact Us</a>
			</div>
		</div>
	</section>
	<section class="image-text margin-bottom overlapping text-image media-image media-cover">
		<div class="content-wrapper">
			<div class="image">
				<img data-lazy-src="https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=1400&q=80" class="lazy fill-container" alt="Teacher and child reading">
			</div>
			<div class="text">
				<h4>Built for Parent-Friendly Scanning</h4>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed convallis finibus convallis. Proin malesuada, nisi in viverra pulvinar, sem augue euismod velit, et elementum justo nibh vitae est.</p>
				<p>Integer sagittis sollicitudin ullamcorper. Curabitur pretium dictum purus, in luctus risus pharetra a.</p>
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
				<div data-answer="q1" hidden><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p></div>
			</div>
			<div>
				<p data-question="q2"><strong>Do you offer full-day and part-day options?</strong><i class="fa-solid fa-chevron-down"></i></p>
				<div data-answer="q2" hidden><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p></div>
			</div>
			<div>
				<p data-question="q3"><strong>How do we schedule a tour?</strong><i class="fa-solid fa-chevron-down"></i></p>
				<div data-answer="q3" hidden><p>Use our contact page and submit your preferred day/time.</p></div>
			</div>
			<div>
				<p data-question="q4"><strong>What should we bring on the first day?</strong><i class="fa-solid fa-chevron-down"></i></p>
				<div data-answer="q4" hidden><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p></div>
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
	<section class="subpage-hero padding-bottom padding-top offset-bg-parent">
		<div class="offset-bg tan extend-left round-bottom-right no-media"></div>
		<div class="text-and-image content-wrapper no-media">
			<div class="text-left">
				<h1>Find Your Academy</h1>
				<p>Search by city, state, or zip code.</p>
			</div>
		</div>
	</section>
	<div class="find-academy margin-top" id="find-academy">
		<div class="content-wrapper">
			<div class="text-container">
				<h4>Find an Academy Near You</h4>
				<p>Kiddie Academy Educational Child Care helps children make the most of learning moments in locations across the country.</p>
			</div>
			<div class="locator">
				<div class="form">
					<div class="input-container">
						<input type="text" name="location" class="semi-transparent location-search-autocomplete" placeholder="City, State or Zipcode" aria-label="City, State or Zipcode" />
					</div>
					<button class="button" type="submit">Find Your Academy</button>
				</div>
				<a class="get-current-location" href="/academies/?useMyLocation=true"><i class="fa-solid fa-location-crosshairs"></i>Use your current location</a>
			</div>
		</div>
	</div>
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
				<img data-lazy-src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1400&q=80" class="lazy fill-container" alt="{$title_html} classroom">
			</div>
			<div class="text">
				<h4>{$title_html} Program Overview</h4>
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla facilisi. Nunc feugiat, mauris et commodo tincidunt, erat nisi varius est, non efficitur arcu erat vitae augue.</p>
				<p>Donec sollicitudin lorem at nibh suscipit, vitae convallis arcu placerat.</p>
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
 * Apply Elementor document data for editable page content.
 *
 * @param int    $post_id Post ID.
 * @param string $html HTML block.
 */
function kms_set_elementor_document( $post_id, $html ) {
	if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
		return;
	}

	$section_id = substr( md5( 'sec-' . $post_id ), 0, 8 );
	$column_id  = substr( md5( 'col-' . $post_id ), 0, 8 );
	$widget_id  = substr( md5( 'wid-' . $post_id ), 0, 8 );

	$data = array(
		array(
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
								'html' => $html,
							),
							'elements'   => array(),
						),
					),
					'isInner'  => false,
				),
			),
			'isInner'  => false,
		),
	);

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
 * Activation callback.
 */
function kms_activate() {
	kms_run_seed( true );
}
register_activation_hook( __FILE__, 'kms_activate' );

/**
 * Add tools page.
 */
function kms_add_tools_page() {
	add_management_page(
		'Kiddie Mock Seed',
		'Kiddie Mock Seed',
		'manage_options',
		'kiddie-mock-seed',
		'kms_render_tools_page'
	);
}
add_action( 'admin_menu', 'kms_add_tools_page' );

/**
 * Handle tools form submit.
 */
function kms_handle_tools_actions() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! isset( $_POST['kms_action'] ) || 'run_seed' !== $_POST['kms_action'] ) {
		return;
	}

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
