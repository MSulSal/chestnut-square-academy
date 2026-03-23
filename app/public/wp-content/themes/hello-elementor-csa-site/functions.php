<?php
/**
 * Chestnut mock child theme functions.
 *
 * @package HelloElementorChestnutMock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect Elementor editor/preview contexts to avoid frontend script/filter clashes.
 *
 * @return bool
 */
function csa_site_is_elementor_editor_context() {
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

function csa_site_enqueue_assets() {
	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'csa-site-core',
		get_stylesheet_directory_uri() . '/assets/css/csa-core.css',
		array(),
		$theme_version
	);

	wp_enqueue_style(
		'csa-site-core-footer',
		get_stylesheet_directory_uri() . '/assets/css/csa-core-footer.css',
		array( 'csa-site-core' ),
		$theme_version
	);

	wp_enqueue_style(
		'csa-site-overrides',
		get_stylesheet_directory_uri() . '/assets/css/csa-overrides.css',
		array( 'csa-site-core-footer' ),
		$theme_version
	);

	wp_enqueue_style(
		'csa-site-theme',
		get_stylesheet_uri(),
		array( 'csa-site-overrides' ),
		$theme_version
	);

	if ( ! csa_site_is_elementor_editor_context() ) {
		wp_enqueue_script(
			'csa-site-js',
			get_stylesheet_directory_uri() . '/assets/js/csa-site.js',
			array(),
			$theme_version,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'csa_site_enqueue_assets', 20 );

function csa_site_disable_page_title( $show ) {
	if ( is_page() ) {
		return false;
	}

	return $show;
}
add_filter( 'hello_elementor_page_title', 'csa_site_disable_page_title' );

function csa_site_add_body_classes( $classes ) {
	$classes[] = 'csa-site';

	if ( is_front_page() ) {
		$classes[] = 'page-home';
	}

	return $classes;
}
add_filter( 'body_class', 'csa_site_add_body_classes' );

/**
 * Register dashboard-editable menus used by header/footer.
 */
function csa_site_register_nav_menus() {
	register_nav_menus(
		array(
			'csa_primary'      => __( 'CSA Primary Navigation', 'hello-elementor-csa-site' ),
			'csa_footer_quick' => __( 'CSA Footer Quick Links', 'hello-elementor-csa-site' ),
			'csa_footer_contact' => __( 'CSA Footer Contact Links', 'hello-elementor-csa-site' ),
		)
	);
}
add_action( 'after_setup_theme', 'csa_site_register_nav_menus', 20 );

/**
 * One-time seed for menu locations so non-technical owners start with editable menus.
 */
function csa_site_seed_default_nav_menus() {
	if ( '1' === get_option( 'csa_site_nav_seeded_v1', '0' ) ) {
		return;
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}

	$defs = array(
		'csa_primary' => array(
			'name'  => 'CSA Primary Navigation',
			'items' => array(
				array(
					'title' => 'LIFE AT CHESTNUT',
					'url'   => home_url( '/life-at-chestnut/' ),
				),
				array(
					'title' => 'About Us',
					'url'   => home_url( '/company/' ),
				),
				array(
					'title' => 'FAQ',
					'url'   => home_url( '/faq/' ),
				),
				array(
					'title' => 'Contact Us',
					'url'   => home_url( '/contact-us/' ),
				),
			),
		),
		'csa_footer_quick' => array(
			'name'  => 'CSA Footer Quick Links',
			'items' => array(
				array(
					'title' => 'Home',
					'url'   => home_url( '/' ),
				),
				array(
					'title' => 'Life at Chestnut',
					'url'   => home_url( '/life-at-chestnut/' ),
				),
				array(
					'title' => 'About Us',
					'url'   => home_url( '/company/' ),
				),
				array(
					'title' => 'FAQ',
					'url'   => home_url( '/faq/' ),
				),
				array(
					'title' => 'Contact Us',
					'url'   => home_url( '/contact-us/' ),
				),
			),
		),
		'csa_footer_contact' => array(
			'name'  => 'CSA Footer Contact Links',
			'items' => array(
				array(
					'title' => 'Get Directions',
					'url'   => 'https://maps.google.com/?q=402+S+Chestnut+St,+McKinney,+TX',
				),
				array(
					'title' => 'Schedule a Tour',
					'url'   => home_url( '/contact-us/' ),
				),
				array(
					'title' => 'Privacy Policy',
					'url'   => home_url( '/privacy-policy/' ),
				),
			),
		),
	);

	$changed = false;

	foreach ( $defs as $location => $def ) {
		if ( ! empty( $locations[ $location ] ) ) {
			continue;
		}

		$menu_obj = wp_get_nav_menu_object( $def['name'] );
		$menu_id  = $menu_obj ? (int) $menu_obj->term_id : 0;

		if ( $menu_id <= 0 ) {
			$menu_id = (int) wp_create_nav_menu( $def['name'] );
		}

		if ( $menu_id <= 0 ) {
			continue;
		}

		$existing_items = wp_get_nav_menu_items( $menu_id );
		if ( ! is_array( $existing_items ) || 0 === count( $existing_items ) ) {
			foreach ( $def['items'] as $item ) {
				if ( empty( $item['title'] ) || empty( $item['url'] ) ) {
					continue;
				}
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'  => $item['title'],
						'menu-item-url'    => $item['url'],
						'menu-item-status' => 'publish',
					)
				);
			}
		}

		$locations[ $location ] = $menu_id;
		$changed                = true;
	}

	if ( $changed ) {
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	update_option( 'csa_site_nav_seeded_v1', '1' );
}
add_action( 'init', 'csa_site_seed_default_nav_menus', 25 );

/**
 * Return footer text defaults used in Theme Customizer.
 *
 * @return array<string,string>
 */
function csa_site_footer_text_defaults() {
	return array(
		'school_name' => 'Chestnut Square Academy',
		'address_1'   => '402 S. Chestnut St.',
		'address_2'   => 'McKinney, TX',
		'hours'       => 'Monday-Friday: 6:00 AM-6:00 PM',
		'copyright'   => sprintf( '&copy; %s Chestnut Square Academy. All rights reserved.', gmdate( 'Y' ) ),
	);
}

/**
 * Register footer text fields for no-code dashboard editing.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function csa_site_register_customizer_fields( $wp_customize ) {
	if ( ! is_object( $wp_customize ) ) {
		return;
	}

	$defaults = csa_site_footer_text_defaults();

	$wp_customize->add_section(
		'csa_site_footer_content',
		array(
			'title'       => __( 'CSA Footer Content', 'hello-elementor-csa-site' ),
			'priority'    => 165,
			'description' => __( 'Update footer business details without touching code.', 'hello-elementor-csa-site' ),
		)
	);

	$fields = array(
		'school_name' => __( 'School Name', 'hello-elementor-csa-site' ),
		'address_1'   => __( 'Address Line 1', 'hello-elementor-csa-site' ),
		'address_2'   => __( 'Address Line 2', 'hello-elementor-csa-site' ),
		'hours'       => __( 'Hours', 'hello-elementor-csa-site' ),
		'copyright'   => __( 'Copyright Text', 'hello-elementor-csa-site' ),
	);

	foreach ( $fields as $key => $label ) {
		$setting_key = 'csa_site_footer_' . $key;

		$wp_customize->add_setting(
			$setting_key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			$setting_key,
			array(
				'type'    => 'text',
				'section' => 'csa_site_footer_content',
				'label'   => $label,
			)
		);
	}
}
add_action( 'customize_register', 'csa_site_register_customizer_fields' );

/**
 * Custom walker to preserve legacy navbar DOM shape while using dashboard menus.
 */
if ( class_exists( 'Walker_Nav_Menu' ) && ! class_exists( 'csa_site_Toplevel_Menu_Walker' ) ) {
	class csa_site_Toplevel_Menu_Walker extends Walker_Nav_Menu {
		/**
		 * Start submenu list.
		 *
		 * @param string   $output Used to append additional content.
		 * @param int      $depth  Depth of menu item.
		 * @param stdClass $args   Additional args.
		 */
		public function start_lvl( &$output, $depth = 0, $args = null ) {
			$indent  = str_repeat( "\t", (int) $depth );
			$output .= "\n{$indent}<ul class=\"submenu\">\n";
		}

		/**
		 * End submenu list.
		 *
		 * @param string   $output Used to append additional content.
		 * @param int      $depth  Depth of menu item.
		 * @param stdClass $args   Additional args.
		 */
		public function end_lvl( &$output, $depth = 0, $args = null ) {
			$indent  = str_repeat( "\t", (int) $depth );
			$output .= "{$indent}</ul>\n";
		}

		/**
		 * Start one menu item.
		 *
		 * @param string   $output Used to append additional content.
		 * @param WP_Post  $item   Menu item object.
		 * @param int      $depth  Depth of menu item.
		 * @param stdClass $args   Additional args.
		 * @param int      $id     Current item ID.
		 */
		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
			unset( $id, $args );

			if ( ! $item instanceof WP_Post ) {
				return;
			}

			$classes = is_array( $item->classes ) ? $item->classes : array();
			$classes = array_filter( array_map( 'sanitize_html_class', $classes ) );
			$classes[] = 'menu-item-' . (int) $item->ID;
			$class_attr = implode( ' ', array_filter( $classes ) );

			$output .= '<li class="' . esc_attr( trim( $class_attr ) ) . '">';

			$attrs = '';
			if ( ! empty( $item->url ) ) {
				$attrs .= ' href="' . esc_url( $item->url ) . '"';
			}
			if ( ! empty( $item->target ) ) {
				$attrs .= ' target="' . esc_attr( $item->target ) . '"';
			}
			if ( ! empty( $item->xfn ) ) {
				$attrs .= ' rel="' . esc_attr( $item->xfn ) . '"';
			}

			$title = apply_filters( 'the_title', $item->title, $item->ID );
			$link  = '<a' . $attrs . '>' . esc_html( $title ) . '</a>';

			if ( 0 === (int) $depth ) {
				$output .= '<span class="toplevel">' . $link . '</span>';
			} else {
				$output .= $link;
			}
		}

		/**
		 * End one menu item.
		 *
		 * @param string   $output Used to append additional content.
		 * @param WP_Post  $item   Menu item object.
		 * @param int      $depth  Depth of menu item.
		 * @param stdClass $args   Additional args.
		 */
		public function end_el( &$output, $item, $depth = 0, $args = null ) {
			unset( $item, $depth, $args );
			$output .= '</li>';
		}
	}
}

/**
 * Render primary header nav with dashboard menu fallback.
 */
function csa_site_render_primary_nav_menu() {
	if ( has_nav_menu( 'csa_primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'csa_primary',
				'container'      => false,
				'menu_class'     => 'one-row-flex top-menu',
				'menu_id'        => 'menu-header-primary',
				'depth'          => 2,
				'fallback_cb'    => '__return_empty_string',
				'walker'         => new csa_site_Toplevel_Menu_Walker(),
			)
		);
		return;
	}

	$items = array(
		array(
			'label' => 'LIFE AT CHESTNUT',
			'url'   => home_url( '/life-at-chestnut/' ),
		),
		array(
			'label' => 'About Us',
			'url'   => home_url( '/company/' ),
		),
		array(
			'label' => 'FAQ',
			'url'   => home_url( '/faq/' ),
		),
		array(
			'label' => 'Contact Us',
			'url'   => home_url( '/contact-us/' ),
		),
	);

	echo '<ul id="menu-header-primary" class="one-row-flex top-menu">';
	foreach ( $items as $item ) {
		if ( empty( $item['label'] ) || empty( $item['url'] ) ) {
			continue;
		}
		echo '<li><span class="toplevel"><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></span></li>';
	}
	echo '</ul>';
}

/**
 * Render footer nav list with dashboard menu fallback.
 *
 * @param string                 $location       Menu location key.
 * @param string                 $menu_id        Menu ID attribute.
 * @param array<int,array<string,string>> $fallback_items Fallback item list.
 */
function csa_site_render_footer_menu( $location, $menu_id, $fallback_items ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'menu',
				'menu_id'        => $menu_id,
				'depth'          => 1,
				'fallback_cb'    => '__return_empty_string',
			)
		);
		return;
	}

	echo '<ul id="' . esc_attr( $menu_id ) . '" class="menu">';
	foreach ( $fallback_items as $item ) {
		if ( empty( $item['label'] ) || empty( $item['url'] ) ) {
			continue;
		}

		$target = '';
		$rel    = '';
		if ( ! empty( $item['external'] ) ) {
			$target = ' target="_blank"';
			$rel    = ' rel="noopener noreferrer"';
		}

		echo '<li><a href="' . esc_url( $item['url'] ) . '"' . $target . $rel . '>' . esc_html( $item['label'] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Resolve site logo URL from Elementor Site Settings (fallback: WP custom logo).
 *
 * @return string
 */
function csa_site_get_elementor_site_logo_url() {
	$logo_id = 0;

	if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->kits_manager ) ) {
		$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit_for_frontend();

		if ( is_object( $kit ) && method_exists( $kit, 'get_settings' ) ) {
			$site_logo = $kit->get_settings( 'site_logo' );

			if ( is_array( $site_logo ) && isset( $site_logo['id'] ) ) {
				$logo_id = (int) $site_logo['id'];
			} elseif ( is_numeric( $site_logo ) ) {
				$logo_id = (int) $site_logo;
			}
		}
	}

	if ( $logo_id <= 0 ) {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
	}

	if ( $logo_id <= 0 ) {
		return '';
	}

	$url = wp_get_attachment_image_url( $logo_id, 'full' );

	return is_string( $url ) ? $url : '';
}

/**
 * Return the academies page markup used for runtime sync.
 *
 * @return string
 */
function csa_site_academies_markup() {
	$root = trailingslashit( home_url() );

	return <<<HTML
<main id="main-content">
	<section class="kma-academies-hero">
		<div class="kma-academies-hero-left" aria-hidden="true"></div>
		<div class="kma-academies-hero-right">
			<div class="kma-academies-hero-inner">
				<h1>Find a Chestnut Square Academy&reg; Child Care Near You</h1>
				<p>All across the country, Chestnut Square Academy Educational Child Care is helping prepare children for life. Find the most convenient of our 360+ locations near you.</p>
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
				<p>Chestnut Square Academy Educational Child Care helps children make the most of learning moments in locations across the country. Discover one near you.</p>
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
function csa_site_set_elementor_html( $post_id, $html ) {
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
function csa_site_allow_legacy_runtime_sync() {
	return (bool) apply_filters( 'csa_site_allow_legacy_runtime_sync', false );
}

/**
 * Runtime one-time page sync for legacy parity snapshots.
 */
function csa_site_runtime_page_sync() {
	if ( ! csa_site_allow_legacy_runtime_sync() ) {
		return;
	}

	global $wpdb;

	if ( function_exists( 'kms_get_seed_profile' ) && 'mock-parity' !== kms_get_seed_profile() ) {
		return;
	}

	if ( get_option( 'csa_site_runtime_sync_ver' ) === '1.0.3' ) {
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

		csa_site_set_elementor_html( (int) $home_page->ID, $home_content );
	}

	$academies_page = get_page_by_path( 'academies', OBJECT, 'page' );
	if ( $academies_page instanceof WP_Post ) {
		$academies_markup = csa_site_academies_markup();

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

		csa_site_set_elementor_html( (int) $academies_page->ID, $academies_markup );
	}

	update_option( 'csa_site_runtime_sync_ver', '1.0.3' );
}
add_action( 'init', 'csa_site_runtime_page_sync', 35 );

/**
 * Render-time parity overrides for pages that may still hold legacy builder snapshots.
 *
 * @param string $content Rendered content.
 * @return string
 */
function csa_site_render_parity_overrides( $content ) {
	if ( ! is_string( $content ) ) {
		return $content;
	}

	if ( ! csa_site_allow_legacy_runtime_sync() ) {
		return $content;
	}

	if ( function_exists( 'kms_get_seed_profile' ) && 'mock-parity' !== kms_get_seed_profile() ) {
		return $content;
	}

	if ( csa_site_is_elementor_editor_context() ) {
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
			$updated_markup = csa_site_academies_markup();

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
				csa_site_set_elementor_html( $post_id, $updated_markup );
			}

			return $updated_markup;
		}
	}

	return $content;
}
add_filter( 'the_content', 'csa_site_render_parity_overrides', 99 );


