<?php
/**
 * Kiddie mock child theme functions.
 *
 * @package HelloElementorKiddieMock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect Elementor editor/preview contexts to avoid frontend script/filter clashes.
 *
 * @return bool
 */
function kiddie_mock_is_elementor_editor_context() {
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

function kiddie_mock_enqueue_assets() {
	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'kiddie-mock-core',
		get_stylesheet_directory_uri() . '/assets/css/kiddie-core.css',
		array(),
		$theme_version
	);

	wp_enqueue_style(
		'kiddie-mock-core-footer',
		get_stylesheet_directory_uri() . '/assets/css/kiddie-core-footer.css',
		array( 'kiddie-mock-core' ),
		$theme_version
	);

	wp_enqueue_style(
		'kiddie-mock-overrides',
		get_stylesheet_directory_uri() . '/assets/css/kiddie-overrides.css',
		array( 'kiddie-mock-core-footer' ),
		$theme_version
	);

	wp_enqueue_style(
		'kiddie-mock-theme',
		get_stylesheet_uri(),
		array( 'kiddie-mock-overrides' ),
		$theme_version
	);

	if ( ! kiddie_mock_is_elementor_editor_context() ) {
		wp_enqueue_script(
			'kiddie-mock-js',
			get_stylesheet_directory_uri() . '/assets/js/kiddie-mock.js',
			array(),
			$theme_version,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'kiddie_mock_enqueue_assets', 20 );

function kiddie_mock_disable_page_title( $show ) {
	if ( is_page() ) {
		return false;
	}

	return $show;
}
add_filter( 'hello_elementor_page_title', 'kiddie_mock_disable_page_title' );

function kiddie_mock_add_body_classes( $classes ) {
	$classes[] = 'kiddie-mock-site';

	if ( is_front_page() ) {
		$classes[] = 'page-home';
	}

	return $classes;
}
add_filter( 'body_class', 'kiddie_mock_add_body_classes' );

/**
 * Return the academies page markup used for runtime sync.
 *
 * @return string
 */
function kiddie_mock_academies_markup() {
	$root = trailingslashit( home_url() );

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
						<a class="get-current-location" href="{$root}academies/?useMyLocation=true"><i class="fa-solid fa-location-crosshairs"></i>Use your current location</a>
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
				<a class="get-current-location" href="{$root}academies/?useMyLocation=true"><i class="fa-solid fa-location-crosshairs"></i>Use your current location</a>
			</div>
		</div>
	</section>
</main>
HTML;
}

/**
 * Keep Elementor document meta aligned to provided raw HTML.
 *
 * @param int    $post_id Post ID.
 * @param string $html    HTML content.
 */
function kiddie_mock_set_elementor_html( $post_id, $html ) {
	if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
		return;
	}

	$section_id = substr( md5( 'he-sec-' . $post_id ), 0, 8 );
	$column_id  = substr( md5( 'he-col-' . $post_id ), 0, 8 );
	$widget_id  = substr( md5( 'he-wid-' . $post_id ), 0, 8 );

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
 * Runtime one-time page sync for parity-critical sections.
 */
function kiddie_mock_runtime_page_sync() {
	global $wpdb;

	if ( function_exists( 'kms_get_seed_profile' ) && 'mock-parity' !== kms_get_seed_profile() ) {
		return;
	}

	if ( get_option( 'kiddie_mock_runtime_sync_ver' ) === '1.0.3' ) {
		return;
	}

	$home_page = get_page_by_path( 'home', OBJECT, 'page' );
	if ( $home_page instanceof WP_Post ) {
		$home_content = (string) $home_page->post_content;
		$home_content = str_replace(
			'Where <span>Learning</span> Grows',
			'Where <span class="headline-highlight">LEARNING</span> Grows',
			$home_content
		);
		$home_content = str_replace(
			'Where <span class="headline-highlight">Learning</span> Grows',
			'Where <span class="headline-highlight">LEARNING</span> Grows',
			$home_content
		);

		if ( $home_content !== (string) $home_page->post_content ) {
			$wpdb->update(
				$wpdb->posts,
				array(
					'post_content'      => $home_content,
					'post_modified'     => current_time( 'mysql' ),
					'post_modified_gmt' => current_time( 'mysql', true ),
				),
				array( 'ID' => (int) $home_page->ID ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);
		}

		kiddie_mock_set_elementor_html( (int) $home_page->ID, $home_content );
	}

	$academies_page = get_page_by_path( 'academies', OBJECT, 'page' );
	if ( $academies_page instanceof WP_Post ) {
		$academies_markup = kiddie_mock_academies_markup();

		$wpdb->update(
			$wpdb->posts,
			array(
				'post_content'      => $academies_markup,
				'post_modified'     => current_time( 'mysql' ),
				'post_modified_gmt' => current_time( 'mysql', true ),
			),
			array( 'ID' => (int) $academies_page->ID ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		kiddie_mock_set_elementor_html( (int) $academies_page->ID, $academies_markup );
	}

	update_option( 'kiddie_mock_runtime_sync_ver', '1.0.3' );
}
add_action( 'init', 'kiddie_mock_runtime_page_sync', 35 );

/**
 * Render-time parity overrides for pages that may still hold legacy builder snapshots.
 *
 * @param string $content Rendered content.
 * @return string
 */
function kiddie_mock_render_parity_overrides( $content ) {
	if ( ! is_string( $content ) ) {
		return $content;
	}

	if ( function_exists( 'kms_get_seed_profile' ) && 'mock-parity' !== kms_get_seed_profile() ) {
		return $content;
	}

	if ( kiddie_mock_is_elementor_editor_context() ) {
		return $content;
	}

	if ( is_front_page() ) {
		$content = str_replace(
			'Where <span>Learning</span> Grows',
			'Where <span class="headline-highlight">LEARNING</span> Grows',
			$content
		);
		$content = str_replace(
			'Where <span class="headline-highlight">Learning</span> Grows',
			'Where <span class="headline-highlight">LEARNING</span> Grows',
			$content
		);
	}

	if ( is_page( 'academies' ) ) {
		if ( false !== strpos( $content, 'kma-academies-hero' ) ) {
			return $content;
		}

		if ( false !== strpos( $content, 'subpage-hero padding-bottom padding-top offset-bg-parent' ) && false !== strpos( $content, 'Find Your Academy' ) ) {
			global $wpdb;
			$post_id       = (int) get_queried_object_id();
			$updated_markup = kiddie_mock_academies_markup();

			if ( $post_id > 0 ) {
				$wpdb->update(
					$wpdb->posts,
					array(
						'post_content'      => $updated_markup,
						'post_modified'     => current_time( 'mysql' ),
						'post_modified_gmt' => current_time( 'mysql', true ),
					),
					array( 'ID' => $post_id ),
					array( '%s', '%s', '%s' ),
					array( '%d' )
				);
				kiddie_mock_set_elementor_html( $post_id, $updated_markup );
			}

			return $updated_markup;
		}
	}

	return $content;
}
add_filter( 'the_content', 'kiddie_mock_render_parity_overrides', 99 );
