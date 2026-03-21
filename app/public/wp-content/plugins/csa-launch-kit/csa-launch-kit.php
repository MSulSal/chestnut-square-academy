<?php
/**
 * Plugin Name: CSA Launch Kit
 * Plugin URI: https://chestnutsquareacademy.local
 * Description: One-click starter setup for Chestnut Square Academy pages, menus, business profile, and Schedule a Tour form.
 * Version: 1.4.0
 * Author: CSA Web Team
 * License: GPL-2.0-or-later
 * Text Domain: csa-launch-kit
 *
 * @package CsaLaunchKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CSA_LAUNCH_KIT_VERSION', '1.4.0' );

/**
 * Return default business profile values.
 *
 * @return array<string,string>
 */
function csa_lk_get_business_defaults() {
	return array(
		'csa_lk_business_name'        => 'Chestnut Square Academy',
		'csa_lk_business_address'     => '402 S Chestnut St, McKinney, TX 75069 [VERIFY]',
		'csa_lk_business_phone'       => '[VERIFY PHONE]',
		'csa_lk_business_email'       => get_option( 'admin_email' ),
		'csa_lk_business_hours'       => 'Monday-Friday, 6:00 AM-6:00 PM [VERIFY]',
		'csa_lk_business_map_embed'   => 'https://www.google.com/maps?q=402+S+Chestnut+St,+McKinney,+TX+75069&output=embed',
		'csa_lk_business_description' => 'Trusted early learning and childcare in Downtown McKinney, Texas.',
	);
}

/**
 * Get business option value with fallback to defaults.
 *
 * @param string $key Option key.
 * @return string
 */
function csa_lk_get_business_option( $key ) {
	$defaults = csa_lk_get_business_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	$value    = get_option( $key, $default );

	return is_string( $value ) ? $value : $default;
}

/**
 * Get normalized business profile data.
 *
 * @return array<string,string>
 */
function csa_lk_get_business_profile_data() {
	return array(
		'name'        => csa_lk_get_business_option( 'csa_lk_business_name' ),
		'address'     => csa_lk_get_business_option( 'csa_lk_business_address' ),
		'phone'       => csa_lk_get_business_option( 'csa_lk_business_phone' ),
		'email'       => csa_lk_get_business_option( 'csa_lk_business_email' ),
		'hours'       => csa_lk_get_business_option( 'csa_lk_business_hours' ),
		'map_embed'   => csa_lk_get_business_option( 'csa_lk_business_map_embed' ),
		'description' => csa_lk_get_business_option( 'csa_lk_business_description' ),
	);
}

/**
 * Check if value still contains unresolved verification tokens.
 *
 * @param string $value Field value.
 * @return bool
 */
function csa_lk_has_placeholder_token( $value ) {
	if ( ! is_string( $value ) ) {
		return true;
	}

	$needles = array(
		'[VERIFY]',
		'[VERIFY PHONE]',
		'[DO NOT PUBLISH UNTIL CONFIRMED]',
	);

	foreach ( $needles as $needle ) {
		if ( false !== stripos( $value, $needle ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Check whether a plugin is active.
 *
 * @param string $plugin_file Plugin main file path (relative to plugins dir).
 * @return bool
 */
function csa_lk_is_plugin_active( $plugin_file ) {
	if ( function_exists( 'is_plugin_active' ) ) {
		return is_plugin_active( $plugin_file );
	}

	if ( defined( 'ABSPATH' ) ) {
		$admin_plugin_file = ABSPATH . 'wp-admin/includes/plugin.php';
		if ( file_exists( $admin_plugin_file ) ) {
			require_once $admin_plugin_file;
			if ( function_exists( 'is_plugin_active' ) ) {
				return is_plugin_active( $plugin_file );
			}
		}
	}

	return false;
}

/**
 * Check whether Elementor plugin is active.
 *
 * @return bool
 */
function csa_lk_is_elementor_active() {
	return csa_lk_is_plugin_active( 'elementor/elementor.php' );
}

/**
 * Plugin activation callback.
 */
function csa_lk_activate() {
	if ( ! get_option( 'csa_lk_tour_email' ) ) {
		update_option( 'csa_lk_tour_email', get_option( 'admin_email' ) );
	}

	if ( ! get_option( 'csa_lk_tour_success_message' ) ) {
		update_option( 'csa_lk_tour_success_message', 'Thank you. Your tour request has been received. Our team will contact you soon to confirm your visit.' );
	}

	foreach ( csa_lk_get_business_defaults() as $key => $value ) {
		if ( ! get_option( $key ) ) {
			update_option( $key, $value );
		}
	}

	if ( false === get_option( 'csa_lk_enable_local_schema', false ) ) {
		update_option( 'csa_lk_enable_local_schema', '1' );
	}

	if ( false === get_option( 'csa_lk_enable_faq_schema', false ) ) {
		update_option( 'csa_lk_enable_faq_schema', '1' );
	}

	if ( false === get_option( 'csa_lk_domain_verified', false ) ) {
		update_option( 'csa_lk_domain_verified', '0' );
	}

	csa_lk_run_setup( false );
}
register_activation_hook( __FILE__, 'csa_lk_activate' );

/**
 * Register private post type for form submissions.
 */
function csa_lk_register_tour_post_type() {
	register_post_type(
		'csa_tour_request',
		array(
			'labels'             => array(
				'name'          => 'Tour Requests',
				'singular_name' => 'Tour Request',
			),
			'public'             => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'supports'           => array( 'title', 'editor', 'custom-fields' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'publicly_queryable' => false,
			'menu_icon'          => 'dashicons-calendar-alt',
		)
	);
}
add_action( 'init', 'csa_lk_register_tour_post_type' );

/**
 * Add settings pages.
 */
function csa_lk_add_admin_pages() {
	add_management_page(
		'CSA Launch Kit',
		'CSA Launch Kit',
		'manage_options',
		'csa-launch-kit',
		'csa_lk_render_tools_page'
	);

	add_options_page(
		'CSA Tour Form',
		'CSA Tour Form',
		'manage_options',
		'csa-tour-form',
		'csa_lk_render_settings_page'
	);

	add_options_page(
		'CSA Business Profile',
		'CSA Business Profile',
		'manage_options',
		'csa-business-profile',
		'csa_lk_render_business_settings_page'
	);
}
add_action( 'admin_menu', 'csa_lk_add_admin_pages' );

/**
 * Register settings fields.
 */
function csa_lk_register_settings() {
	register_setting(
		'csa_lk_form_settings',
		'csa_lk_tour_email',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
			'default'           => get_option( 'admin_email' ),
		)
	);

	register_setting(
		'csa_lk_form_settings',
		'csa_lk_tour_success_message',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_textarea_field',
			'default'           => 'Thank you. Your tour request has been received. Our team will contact you soon to confirm your visit.',
		)
	);

	$business_fields = array(
		'csa_lk_business_name'        => 'sanitize_text_field',
		'csa_lk_business_address'     => 'sanitize_textarea_field',
		'csa_lk_business_phone'       => 'sanitize_text_field',
		'csa_lk_business_email'       => 'sanitize_email',
		'csa_lk_business_hours'       => 'sanitize_text_field',
		'csa_lk_business_map_embed'   => 'esc_url_raw',
		'csa_lk_business_description' => 'sanitize_textarea_field',
	);

	foreach ( $business_fields as $field => $callback ) {
		register_setting(
			'csa_lk_business_settings',
			$field,
			array(
				'type'              => 'string',
				'sanitize_callback' => $callback,
				'default'           => csa_lk_get_business_option( $field ),
			)
		);
	}

	register_setting(
		'csa_lk_business_settings',
		'csa_lk_enable_local_schema',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '1',
		)
	);

	register_setting(
		'csa_lk_business_settings',
		'csa_lk_enable_faq_schema',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '1',
		)
	);

	register_setting(
		'csa_lk_business_settings',
		'csa_lk_domain_verified',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '0',
		)
	);
}
add_action( 'admin_init', 'csa_lk_register_settings' );

/**
 * Warn admin when Elementor is inactive.
 */
function csa_lk_elementor_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || strpos( $screen->id, 'csa' ) === false ) {
		return;
	}

	if ( ! did_action( 'elementor/loaded' ) ) {
		echo '<div class="notice notice-warning"><p><strong>Elementor is not active.</strong> Activate Elementor to edit pages visually for your resume/portfolio workflow.</p></div>';
	}
}
add_action( 'admin_notices', 'csa_lk_elementor_notice' );

/**
 * Show publish blocker warning when unresolved placeholders remain.
 */
function csa_lk_publish_blocker_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$audit = csa_lk_get_publish_audit();
	if ( $audit['blocking_count'] < 1 ) {
		return;
	}

	$screen = get_current_screen();
	if ( $screen && strpos( $screen->id, 'csa-launch-kit' ) !== false ) {
		return;
	}

	$link = admin_url( 'tools.php?page=csa-launch-kit' );
	echo '<div class="notice notice-warning"><p><strong>CSA publish blockers:</strong> ' . esc_html( (string) $audit['blocking_count'] ) . ' unresolved verification items. <a href="' . esc_url( $link ) . '">Review in CSA Launch Kit</a>.</p></div>';
}
add_action( 'admin_notices', 'csa_lk_publish_blocker_notice' );

/**
 * Register dashboard widget for quick launch status.
 */
function csa_lk_register_dashboard_widget() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_add_dashboard_widget(
		'csa_lk_dashboard_widget',
		'CSA Launch Status',
		'csa_lk_render_dashboard_widget'
	);
}
add_action( 'wp_dashboard_setup', 'csa_lk_register_dashboard_widget' );

/**
 * Render dashboard widget content.
 */
function csa_lk_render_dashboard_widget() {
	$audit = csa_lk_get_publish_audit();
	?>
	<p><strong>Blocking items:</strong> <?php echo esc_html( (string) $audit['blocking_count'] ); ?></p>
	<p>
		Business: <?php echo esc_html( (string) count( $audit['business_issues'] ) ); ?> |
		Technical: <?php echo esc_html( (string) count( $audit['technical_issues'] ) ); ?> |
		Pages: <?php echo esc_html( (string) ( count( $audit['page_issues'] ) + count( $audit['missing_pages'] ) ) ); ?>
	</p>
	<ul>
		<li><a href="<?php echo esc_url( admin_url( 'tools.php?page=csa-launch-kit' ) ); ?>">Open CSA Launch Kit</a></li>
		<li><a href="<?php echo esc_url( admin_url( 'options-general.php?page=csa-business-profile' ) ); ?>">Edit CSA Business Profile</a></li>
		<li><a href="<?php echo esc_url( admin_url( 'options-general.php?page=csa-tour-form' ) ); ?>">Edit CSA Tour Form Settings</a></li>
		<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">Review Pages in Elementor</a></li>
		<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=csa_tour_request' ) ); ?>">View Tour Requests</a></li>
	</ul>
	<p><strong>Indexing mode:</strong> <?php echo '1' === (string) get_option( 'blog_public', '1' ) ? 'Production indexing enabled' : 'Staging noindex enabled'; ?></p>
	<?php if ( ! empty( $audit['technical_notices'] ) ) : ?>
		<p><strong>Notices:</strong></p>
		<ul>
			<?php foreach ( $audit['technical_notices'] as $notice ) : ?>
				<li><?php echo esc_html( $notice ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<p>Publish only after blockers are zero and all [VERIFY] items are resolved.</p>
	<?php
}

/**
 * Render tools page.
 */
function csa_lk_render_tools_page() {
	$status = isset( $_GET['csa_setup'] ) ? sanitize_text_field( wp_unslash( $_GET['csa_setup'] ) ) : '';
	$indexing_status = isset( $_GET['csa_indexing'] ) ? sanitize_text_field( wp_unslash( $_GET['csa_indexing'] ) ) : '';
	$audit  = csa_lk_get_publish_audit();
	?>
	<div class="wrap">
		<h1>CSA Launch Kit</h1>
		<p>Run one-click setup to create or update starter pages, menu links, and homepage assignment.</p>
		<?php if ( 'done' === $status ) : ?>
			<div class="notice notice-success is-dismissible"><p>Setup complete.</p></div>
		<?php elseif ( 'error' === $status ) : ?>
			<div class="notice notice-error is-dismissible"><p>Setup could not complete. Please check permissions and try again.</p></div>
		<?php endif; ?>
		<?php if ( 'staging' === $indexing_status ) : ?>
			<div class="notice notice-warning is-dismissible"><p>Staging mode enabled. Search indexing is now discouraged.</p></div>
		<?php elseif ( 'production' === $indexing_status ) : ?>
			<div class="notice notice-success is-dismissible"><p>Production indexing enabled. Search indexing is now allowed.</p></div>
		<?php elseif ( 'indexing-error' === $indexing_status ) : ?>
			<div class="notice notice-error is-dismissible"><p>Could not change indexing mode. Please try again.</p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'csa_lk_run_setup' ); ?>
			<input type="hidden" name="action" value="csa_lk_run_setup" />
			<p>
				<label>
					<input type="checkbox" name="overwrite" value="1" />
					Overwrite existing page content (leave unchecked to only create missing pages).
				</label>
			</p>
			<?php submit_button( 'Run Starter Setup' ); ?>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 8px;">
			<?php wp_nonce_field( 'csa_lk_download_report' ); ?>
			<input type="hidden" name="action" value="csa_lk_download_report" />
			<?php submit_button( 'Download Preflight Report', 'secondary', 'submit', false ); ?>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 8px;">
			<?php wp_nonce_field( 'csa_lk_set_indexing_mode' ); ?>
			<input type="hidden" name="action" value="csa_lk_set_indexing_mode" />
			<input type="hidden" name="mode" value="staging" />
			<?php submit_button( 'Enable Staging Mode (Noindex)', 'secondary', 'submit', false ); ?>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 8px;">
			<?php wp_nonce_field( 'csa_lk_set_indexing_mode' ); ?>
			<input type="hidden" name="action" value="csa_lk_set_indexing_mode" />
			<input type="hidden" name="mode" value="production" />
			<?php submit_button( 'Enable Production Indexing', 'secondary', 'submit', false ); ?>
		</form>

		<hr />

		<h2>Publish Preflight Audit</h2>
		<p><strong>Blocking items:</strong> <?php echo esc_html( (string) $audit['blocking_count'] ); ?></p>

		<h3>Business Profile Checks</h3>
		<?php if ( empty( $audit['business_issues'] ) ) : ?>
			<p>All required business profile fields look complete.</p>
		<?php else : ?>
			<ul>
				<?php foreach ( $audit['business_issues'] as $issue ) : ?>
					<li><?php echo esc_html( $issue ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<h3>Technical Readiness Checks</h3>
		<?php if ( empty( $audit['technical_issues'] ) ) : ?>
			<p>Core technical checks look good.</p>
		<?php else : ?>
			<ul>
				<?php foreach ( $audit['technical_issues'] as $issue ) : ?>
					<li><?php echo esc_html( $issue ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( ! empty( $audit['technical_notices'] ) ) : ?>
			<h4>Technical Notices</h4>
			<ul>
				<?php foreach ( $audit['technical_notices'] as $notice ) : ?>
					<li><?php echo esc_html( $notice ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( ! empty( $audit['plugin_statuses'] ) ) : ?>
			<h4>Recommended Plugin Status</h4>
			<table class="widefat striped" style="max-width: 700px;">
				<thead>
					<tr>
						<th>Plugin</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $audit['plugin_statuses'] as $plugin_row ) : ?>
						<tr>
							<td><?php echo esc_html( (string) $plugin_row['label'] ); ?></td>
							<td><?php echo ! empty( $plugin_row['active'] ) ? 'Active' : 'Inactive'; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<h3>Page Placeholder Checks</h3>
		<?php if ( ! empty( $audit['missing_pages'] ) ) : ?>
			<p><strong>Missing Required Pages:</strong></p>
			<ul>
				<?php foreach ( $audit['missing_pages'] as $missing_page ) : ?>
					<li><?php echo esc_html( $missing_page ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( empty( $audit['page_issues'] ) ) : ?>
			<p>No placeholder tokens were found in core pages.</p>
		<?php else : ?>
			<table class="widefat striped" style="max-width: 980px;">
				<thead>
					<tr>
						<th>Page</th>
						<th>Placeholder Hits</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $audit['page_issues'] as $row ) : ?>
						<tr>
							<td><a href="<?php echo esc_url( get_edit_post_link( $row['id'] ) ); ?>"><?php echo esc_html( $row['title'] ); ?></a></td>
							<td><?php echo esc_html( (string) $row['hits'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<hr />

		<h2>Next Steps</h2>
		<ol>
			<li>Activate <strong>Hello Elementor CSA</strong> child theme.</li>
			<li>Activate <strong>Elementor</strong> plugin.</li>
			<li>Fill out <strong>Settings > CSA Business Profile</strong>.</li>
			<li>Open pages in Elementor and replace all <code>[VERIFY]</code> placeholders before publishing.</li>
			<li>Set the Schedule a Tour notification email in Settings > CSA Tour Form.</li>
		</ol>
	</div>
	<?php
}

/**
 * Build preflight audit summary.
 *
 * @return array<string,mixed>
 */
function csa_lk_get_publish_audit() {
	$business_issues = array();
	$page_issues     = array();
	$missing_pages   = array();
	$technical_issues = array();
	$technical_notices = array();
	$plugin_statuses = array();
	$blocking_count  = 0;

	$required_fields = array(
		'csa_lk_business_name'    => 'Business name is missing or unresolved.',
		'csa_lk_business_address' => 'Address is missing or unresolved.',
		'csa_lk_business_phone'   => 'Phone is missing or unresolved.',
		'csa_lk_business_email'   => 'Email is missing or unresolved.',
		'csa_lk_business_hours'   => 'Hours are missing or unresolved.',
	);

	foreach ( $required_fields as $field => $message ) {
		$value = csa_lk_get_business_option( $field );
		if ( '' === trim( $value ) || csa_lk_has_placeholder_token( $value ) ) {
			$business_issues[] = $message;
			++$blocking_count;
		}
	}

	$core_slugs = array(
		'home',
		'about',
		'programs',
		'gallery',
		'faq',
		'contact-schedule-a-tour',
	);

	foreach ( $core_slugs as $slug ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );
		if ( ! $page ) {
			$missing_pages[] = ucfirst( str_replace( '-', ' ', $slug ) );
			++$blocking_count;
			continue;
		}

		$hits  = 0;
		$hits += substr_count( $page->post_content, '[VERIFY]' );
		$hits += substr_count( $page->post_content, '[VERIFY PHONE]' );
		$hits += substr_count( $page->post_content, '[DO NOT PUBLISH UNTIL CONFIRMED]' );

		if ( $hits > 0 ) {
			$page_issues[] = array(
				'id'    => (int) $page->ID,
				'title' => $page->post_title,
				'hits'  => $hits,
			);
			$blocking_count += $hits;
		}
	}

	if ( 'hello-elementor-csa' !== get_stylesheet() ) {
		$technical_issues[] = 'Hello Elementor CSA child theme is not active.';
		++$blocking_count;
	}

	if ( ! csa_lk_is_elementor_active() ) {
		$technical_issues[] = 'Elementor plugin is not active.';
		++$blocking_count;
	}

	$home_page = get_page_by_path( 'home', OBJECT, 'page' );
	if ( 'page' !== get_option( 'show_on_front' ) ) {
		$technical_issues[] = 'Front page display is not set to a static page.';
		++$blocking_count;
	} elseif ( ! $home_page || (int) get_option( 'page_on_front' ) !== (int) $home_page->ID ) {
		$technical_issues[] = 'Homepage is not assigned to the Home page.';
		++$blocking_count;
	}

	$permalink_structure = (string) get_option( 'permalink_structure' );
	if ( '' === $permalink_structure ) {
		$technical_issues[] = 'Permalinks are set to Plain. Change to Post name before launch.';
		++$blocking_count;
	}

	$tour_email = (string) get_option( 'csa_lk_tour_email', '' );
	if ( ! is_email( $tour_email ) ) {
		$technical_issues[] = 'Tour form notification email is invalid or missing.';
		++$blocking_count;
	}

	$map_embed = csa_lk_get_business_option( 'csa_lk_business_map_embed' );
	if ( '' === trim( $map_embed ) || ! filter_var( $map_embed, FILTER_VALIDATE_URL ) ) {
		$technical_issues[] = 'Map embed URL is missing or invalid.';
		++$blocking_count;
	}

	if ( '1' !== (string) get_option( 'csa_lk_domain_verified', '0' ) ) {
		$technical_issues[] = 'Domain ownership/DNS verification is not confirmed in CSA Business Profile.';
		++$blocking_count;
	}

	if ( '1' !== (string) get_option( 'blog_public', '1' ) ) {
		$technical_notices[] = 'Search engine visibility is currently discouraged (good for staging, switch for production launch).';
	}

	$recommended_plugins = array(
		'wordpress-seo/wp-seo.php'               => 'Yoast SEO',
		'updraftplus/updraftplus.php'            => 'UpdraftPlus',
		'wp-mail-smtp/wp_mail_smtp.php'          => 'WP Mail SMTP',
		'better-wp-security/better-wp-security.php' => 'Solid Security',
	);

	foreach ( $recommended_plugins as $plugin_file => $label ) {
		$is_active         = csa_lk_is_plugin_active( $plugin_file );
		$plugin_statuses[] = array(
			'label'  => $label,
			'active' => $is_active,
		);

		if ( ! $is_active ) {
			$technical_notices[] = 'Recommended plugin inactive: ' . $label . '.';
		}
	}

	return array(
		'business_issues' => $business_issues,
		'page_issues'     => $page_issues,
		'missing_pages'   => $missing_pages,
		'technical_issues' => $technical_issues,
		'technical_notices' => $technical_notices,
		'plugin_statuses' => $plugin_statuses,
		'blocking_count'  => $blocking_count,
	);
}

/**
 * Build preflight report text content.
 *
 * @param array<string,mixed> $audit Audit array.
 * @return string
 */
function csa_lk_build_preflight_report( $audit ) {
	$lines   = array();
	$lines[] = 'CSA Preflight Report';
	$lines[] = 'Generated: ' . gmdate( 'Y-m-d H:i:s' ) . ' UTC';
	$lines[] = 'Blocking items: ' . (string) $audit['blocking_count'];
	$lines[] = '';

	$sections = array(
		'Business Issues'   => isset( $audit['business_issues'] ) ? $audit['business_issues'] : array(),
		'Technical Issues'  => isset( $audit['technical_issues'] ) ? $audit['technical_issues'] : array(),
		'Technical Notices' => isset( $audit['technical_notices'] ) ? $audit['technical_notices'] : array(),
		'Missing Pages'     => isset( $audit['missing_pages'] ) ? $audit['missing_pages'] : array(),
	);

	foreach ( $sections as $title => $items ) {
		$lines[] = '## ' . $title;
		if ( empty( $items ) ) {
			$lines[] = '- None';
		} else {
			foreach ( $items as $item ) {
				$lines[] = '- ' . wp_strip_all_tags( (string) $item );
			}
		}
		$lines[] = '';
	}

	$lines[] = '## Page Placeholder Issues';
	if ( empty( $audit['page_issues'] ) ) {
		$lines[] = '- None';
	} else {
		foreach ( $audit['page_issues'] as $row ) {
			$title = isset( $row['title'] ) ? (string) $row['title'] : 'Unknown page';
			$hits  = isset( $row['hits'] ) ? (int) $row['hits'] : 0;
			$lines[] = '- ' . $title . ': ' . (string) $hits . ' placeholder hits';
		}
	}
	$lines[] = '';

	$lines[] = '## Recommended Plugin Status';
	if ( empty( $audit['plugin_statuses'] ) ) {
		$lines[] = '- None';
	} else {
		foreach ( $audit['plugin_statuses'] as $plugin_row ) {
			$label = isset( $plugin_row['label'] ) ? (string) $plugin_row['label'] : 'Unknown plugin';
			$state = ! empty( $plugin_row['active'] ) ? 'Active' : 'Inactive';
			$lines[] = '- ' . $label . ': ' . $state;
		}
	}
	$lines[] = '';

	$lines[] = 'Recommendation: Launch only when blocking items are 0.';

	return implode( "\n", $lines ) . "\n";
}

/**
 * Render form settings page.
 */
function csa_lk_render_settings_page() {
	?>
	<div class="wrap">
		<h1>CSA Tour Form Settings</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'csa_lk_form_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="csa_lk_tour_email">Notification Email</label></th>
					<td>
						<input type="email" class="regular-text" id="csa_lk_tour_email" name="csa_lk_tour_email" value="<?php echo esc_attr( get_option( 'csa_lk_tour_email', get_option( 'admin_email' ) ) ); ?>" />
						<p class="description">Tour requests are sent to this inbox.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_tour_success_message">Success Message</label></th>
					<td>
						<textarea class="large-text" rows="3" id="csa_lk_tour_success_message" name="csa_lk_tour_success_message"><?php echo esc_textarea( get_option( 'csa_lk_tour_success_message', '' ) ); ?></textarea>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Render business profile settings page.
 */
function csa_lk_render_business_settings_page() {
	$profile = csa_lk_get_business_profile_data();
	$nap     = "Name: {$profile['name']}\nAddress: {$profile['address']}\nPhone: {$profile['phone']}";
	?>
	<div class="wrap">
		<h1>CSA Business Profile</h1>
		<p>These values power reusable shortcodes and local schema output.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'csa_lk_business_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="csa_lk_business_name">Business Name</label></th>
					<td><input type="text" class="regular-text" id="csa_lk_business_name" name="csa_lk_business_name" value="<?php echo esc_attr( csa_lk_get_business_option( 'csa_lk_business_name' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_address">Address</label></th>
					<td><textarea class="large-text" rows="2" id="csa_lk_business_address" name="csa_lk_business_address"><?php echo esc_textarea( csa_lk_get_business_option( 'csa_lk_business_address' ) ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_phone">Phone</label></th>
					<td><input type="text" class="regular-text" id="csa_lk_business_phone" name="csa_lk_business_phone" value="<?php echo esc_attr( csa_lk_get_business_option( 'csa_lk_business_phone' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_email">Public Email</label></th>
					<td><input type="email" class="regular-text" id="csa_lk_business_email" name="csa_lk_business_email" value="<?php echo esc_attr( csa_lk_get_business_option( 'csa_lk_business_email' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_hours">Hours</label></th>
					<td><input type="text" class="regular-text" id="csa_lk_business_hours" name="csa_lk_business_hours" value="<?php echo esc_attr( csa_lk_get_business_option( 'csa_lk_business_hours' ) ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_map_embed">Google Map Embed URL</label></th>
					<td><textarea class="large-text" rows="2" id="csa_lk_business_map_embed" name="csa_lk_business_map_embed"><?php echo esc_textarea( csa_lk_get_business_option( 'csa_lk_business_map_embed' ) ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_description">Business Description</label></th>
					<td><textarea class="large-text" rows="2" id="csa_lk_business_description" name="csa_lk_business_description"><?php echo esc_textarea( csa_lk_get_business_option( 'csa_lk_business_description' ) ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row">Schema Output</th>
					<td>
						<input type="hidden" name="csa_lk_enable_local_schema" value="0" />
						<label style="display:block;margin-bottom:8px;">
							<input type="checkbox" name="csa_lk_enable_local_schema" value="1" <?php checked( '1', (string) get_option( 'csa_lk_enable_local_schema', '1' ) ); ?> />
							Enable built-in LocalBusiness schema
						</label>
						<input type="hidden" name="csa_lk_enable_faq_schema" value="0" />
						<label style="display:block;">
							<input type="checkbox" name="csa_lk_enable_faq_schema" value="1" <?php checked( '1', (string) get_option( 'csa_lk_enable_faq_schema', '1' ) ); ?> />
							Enable built-in FAQ schema
						</label>
						<p class="description">Disable these if your SEO plugin already outputs equivalent schema to avoid duplicates.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Domain Ownership Verification</th>
					<td>
						<input type="hidden" name="csa_lk_domain_verified" value="0" />
						<label>
							<input type="checkbox" name="csa_lk_domain_verified" value="1" <?php checked( '1', (string) get_option( 'csa_lk_domain_verified', '0' ) ); ?> />
							I confirm domain ownership is in the client account and DNS points only to the approved website.
						</label>
						<p class="description">Preflight will block launch until this is confirmed.</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>

		<hr />
		<h2>Citation Copy Block</h2>
		<p>Use this exact NAP block for Google Business Profile and directory consistency.</p>
		<textarea class="large-text code" rows="4" readonly><?php echo esc_textarea( $nap ); ?></textarea>

		<h2>Business Profile JSON (Reference)</h2>
		<p>Use this for documentation and migration records.</p>
		<textarea class="large-text code" rows="10" readonly><?php echo esc_textarea( wp_json_encode( $profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ); ?></textarea>
	</div>
	<?php
}

/**
 * Handle setup action.
 */
function csa_lk_handle_setup_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to run setup.', 'csa-launch-kit' ) );
	}

	check_admin_referer( 'csa_lk_run_setup' );
	$overwrite = ! empty( $_POST['overwrite'] );
	$result    = csa_lk_run_setup( $overwrite );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'      => 'csa-launch-kit',
				'csa_setup' => $result ? 'done' : 'error',
			),
			admin_url( 'tools.php' )
		)
	);
	exit;
}
add_action( 'admin_post_csa_lk_run_setup', 'csa_lk_handle_setup_action' );

/**
 * Handle preflight report download.
 */
function csa_lk_handle_download_report_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to download this report.', 'csa-launch-kit' ) );
	}

	check_admin_referer( 'csa_lk_download_report' );

	$audit      = csa_lk_get_publish_audit();
	$report     = csa_lk_build_preflight_report( $audit );
	$filename   = 'csa-preflight-report-' . gmdate( 'Ymd-His' ) . '.txt';

	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

	echo $report; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'admin_post_csa_lk_download_report', 'csa_lk_handle_download_report_action' );

/**
 * Handle indexing mode switch for staging/production.
 */
function csa_lk_handle_set_indexing_mode_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to change indexing mode.', 'csa-launch-kit' ) );
	}

	check_admin_referer( 'csa_lk_set_indexing_mode' );
	$mode = isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : '';

	if ( 'staging' === $mode ) {
		update_option( 'blog_public', '0' );
		$status = 'staging';
	} elseif ( 'production' === $mode ) {
		update_option( 'blog_public', '1' );
		$status = 'production';
	} else {
		$status = 'indexing-error';
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'         => 'csa-launch-kit',
				'csa_indexing' => $status,
			),
			admin_url( 'tools.php' )
		)
	);
	exit;
}
add_action( 'admin_post_csa_lk_set_indexing_mode', 'csa_lk_handle_set_indexing_mode_action' );

/**
 * One-click setup for pages/menu/homepage.
 *
 * @param bool $overwrite Whether to overwrite content for existing pages.
 * @return bool
 */
function csa_lk_run_setup( $overwrite = false ) {
	$blueprints = csa_lk_get_page_blueprints();
	$page_ids   = array();

	foreach ( $blueprints as $slug => $page ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );

		$post_args = array(
			'post_title'   => $page['title'],
			'post_name'    => $slug,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => $page['content'],
		);

		if ( $existing && ! $overwrite ) {
			$page_ids[ $slug ] = (int) $existing->ID;
			continue;
		}

		if ( $existing ) {
			$post_args['ID']   = (int) $existing->ID;
			$updated_page_id   = wp_update_post( $post_args, true );
			$page_ids[ $slug ] = is_wp_error( $updated_page_id ) ? (int) $existing->ID : (int) $updated_page_id;
		} else {
			$new_page_id       = wp_insert_post( $post_args, true );
			$page_ids[ $slug ] = is_wp_error( $new_page_id ) ? 0 : (int) $new_page_id;
		}
	}

	if ( empty( $page_ids['home'] ) ) {
		return false;
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) $page_ids['home'] );
	update_option( 'page_for_posts', 0 );

	csa_lk_assign_menus( $page_ids );
	update_option( 'csa_lk_last_setup_at', current_time( 'mysql' ) );

	return true;
}

/**
 * Create/update menu and assign to Hello Elementor menu locations.
 *
 * @param array<string,int> $page_ids Page IDs by slug.
 */
function csa_lk_assign_menus( $page_ids ) {
	$menu_name = 'CSA Primary Menu';
	$menu_obj  = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu_obj ? (int) $menu_obj->term_id : 0;

	if ( ! $menu_id ) {
		$menu_id = wp_create_nav_menu( $menu_name );
	}

	if ( ! $menu_id || is_wp_error( $menu_id ) ) {
		return;
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! empty( $items ) ) {
		foreach ( $items as $item ) {
			wp_delete_post( (int) $item->ID, true );
		}
	}

	$menu_order = array(
		'home',
		'about',
		'programs',
		'gallery',
		'faq',
		'contact-schedule-a-tour',
	);

	$position = 1;
	foreach ( $menu_order as $slug ) {
		if ( empty( $page_ids[ $slug ] ) ) {
			continue;
		}

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object-id' => (int) $page_ids[ $slug ],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position,
			)
		);
		++$position;
	}

	$locations           = get_theme_mod( 'nav_menu_locations' );
	$locations           = is_array( $locations ) ? $locations : array();
	$locations['menu-1'] = (int) $menu_id;
	$locations['menu-2'] = (int) $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Provide starter page blueprints.
 *
 * @return array<string,array<string,string>>
 */
function csa_lk_get_page_blueprints() {
	$home = <<<HTML
<section class="csa-shell csa-hero">
  <h1>Trusted Early Learning in Downtown McKinney</h1>
  <p>A warm, dependable place for your child to learn, play, and grow while your family feels informed and supported.</p>
  <div class="csa-note"><strong>[DO NOT PUBLISH UNTIL CONFIRMED]</strong> Replace all placeholders marked with [VERIFY].</div>
  <div class="csa-cta-row">[csa_tour_button label="Schedule a Tour"] [csa_call_button label="Call Now"]</div>
</section>

<section class="csa-shell">
  <h2>Quick Facts</h2>
  <div class="csa-quickfacts">
    <div class="csa-fact"><strong>Location:</strong><br>[csa_address]</div>
    <div class="csa-fact"><strong>Hours:</strong><br>[csa_hours]</div>
    <div class="csa-fact"><strong>Ages Served:</strong><br>[VERIFY]</div>
    <div class="csa-fact"><strong>Meals:</strong><br>Breakfast/Lunch [VERIFY]</div>
  </div>
</section>

<section class="csa-shell">
  <h2>Why Families Choose Us</h2>
  <div class="csa-grid">
    <article class="csa-card"><h3>Caring, Consistent Teachers</h3><p>Your child is known by name and supported every day.</p></article>
    <article class="csa-card"><h3>Safe, Structured Days</h3><p>Predictable routines help children feel secure and thrive.</p></article>
    <article class="csa-card"><h3>Family Communication</h3><p>We keep parents informed so you always know how your child is doing.</p></article>
    <article class="csa-card"><h3>Community Roots</h3><p>Proudly serving families in and around Downtown McKinney.</p></article>
  </div>
</section>

<section class="csa-shell">
  <h2>Programs Snapshot</h2>
  <div class="csa-grid">
    <article class="csa-card"><h3>Infants [VERIFY]</h3><p>Nurturing care, responsive routines, and early sensory learning.</p></article>
    <article class="csa-card"><h3>Toddlers [VERIFY]</h3><p>Guided exploration, language growth, and social development.</p></article>
    <article class="csa-card"><h3>Preschool / Pre-K [VERIFY]</h3><p>Play-based learning that builds kindergarten-ready confidence.</p></article>
    <article class="csa-card"><h3>School-Age [VERIFY]</h3><p>[DO NOT PUBLISH UNTIL CONFIRMED] Before/after-school support if currently active.</p></article>
  </div>
</section>

<section class="csa-shell">
  <h2>A Neighborhood School in Historic Downtown McKinney</h2>
  <p>We are proud to be part of the Downtown McKinney community. Our school blends a family-first atmosphere with a clean, modern learning environment where children can grow with confidence.</p>
</section>

<section class="csa-shell">
  <h2>FAQ Preview</h2>
  <ul>
    <li><strong>Do you offer tours?</strong> Yes, families are encouraged to tour.</li>
    <li><strong>What ages do you accept?</strong> Final placement depends on current enrollment and licensing [VERIFY].</li>
    <li><strong>How do I get started?</strong> Submit a tour request form or call us directly.</li>
  </ul>
  <p><a href="/faq/">View All FAQs</a></p>
</section>

<section class="csa-shell csa-card">
  <h2>Ready to Visit Chestnut Square Academy?</h2>
  <p>We would love to meet your family, answer your questions, and help you find the right fit.</p>
  <div class="csa-cta-row">[csa_tour_button label="Schedule a Tour"] [csa_call_button label="Call Now"]</div>
</section>
HTML;

	$about = <<<HTML
<section class="csa-shell">
  <h1>About Chestnut Square Academy</h1>
  <p>Chestnut Square Academy is an early learning center serving families in Downtown McKinney, Texas. We are committed to dependable care, a safe environment, and meaningful early-learning experiences for every child.</p>

  <h2>Our Approach</h2>
  <p>We believe children learn best when they feel secure, encouraged, and engaged. Our team combines warm relationships with age-appropriate activities that support social, emotional, and early academic growth.</p>

  <h2>Message from Our Director [VERIFY]</h2>
  <p>"Welcome to Chestnut Square Academy. Our goal is to partner with your family and create a positive, nurturing experience for your child each day."</p>

  <h2>Rooted in Downtown McKinney</h2>
  <p>Our school is proud to be part of the Downtown McKinney community. We value neighborhood connection, family trust, and a welcoming atmosphere where parents feel comfortable and children feel at home.</p>

  <h2>Meet Our Team [VERIFY]</h2>
  <p>Our teachers and staff are dedicated to caring for each child with patience, consistency, and professionalism.</p>

  <p>[csa_tour_button label="Schedule a Tour"]</p>
</section>
HTML;

	$programs = <<<HTML
<section class="csa-shell">
  <h1>Programs</h1>
  <p>We offer age-appropriate care and learning experiences designed to support each stage of early childhood development. Final age-group availability is confirmed during your tour.</p>

  <div class="csa-grid">
    <article class="csa-card"><h2>Infants [VERIFY]</h2><p>Calm, nurturing, and responsive routines support healthy early development.</p></article>
    <article class="csa-card"><h2>Toddlers [VERIFY]</h2><p>Guided play, movement, social learning, and language-rich interaction.</p></article>
    <article class="csa-card"><h2>Preschool / Pre-K [VERIFY]</h2><p>Hands-on learning for confidence, independence, and kindergarten readiness.</p></article>
    <article class="csa-card"><h2>School-Age [VERIFY]</h2><p>[DO NOT PUBLISH UNTIL CONFIRMED] Before/after-school support if currently active.</p></article>
  </div>

  <h2>A Typical Day</h2>
  <p>Children follow a consistent daily rhythm that includes active play, guided learning, meals/snacks [VERIFY], rest time (age-dependent), and smooth transitions.</p>

  <h2>Meals and Snacks [VERIFY]</h2>
  <p>[DO NOT PUBLISH UNTIL CONFIRMED] Confirm exactly which meals/snacks are currently provided.</p>

  <h2>Spanish Exposure / Enrichment [VERIFY]</h2>
  <p>[DO NOT PUBLISH UNTIL CONFIRMED] Confirm whether this is formal instruction or language exposure.</p>

  <h2>Transportation and Field Trips [VERIFY]</h2>
  <p>[DO NOT PUBLISH UNTIL CONFIRMED] Confirm availability by age group and season.</p>

  <p>[csa_tour_button label="Schedule a Tour"]</p>
</section>
HTML;

	$gallery = <<<HTML
<section class="csa-shell">
  <h1>Gallery</h1>
  <p>A look inside everyday moments at Chestnut Square Academy.</p>
  <div class="csa-note">Replace placeholder images with real school photos first, then approved social assets, then stock only if necessary.</div>

  <h2>Suggested Photo Groups</h2>
  <ul>
    <li>Classrooms</li>
    <li>Learning Through Play</li>
    <li>Teachers and Children</li>
    <li>Outdoor Moments</li>
    <li>Our Downtown Location</li>
  </ul>

  <p>Alt-text template examples:</p>
  <ul>
    <li>"Teacher guiding toddler sensory activity at Chestnut Square Academy"</li>
    <li>"Preschool children in circle time at Chestnut Square Academy"</li>
    <li>"Exterior of Chestnut Square Academy in Downtown McKinney"</li>
  </ul>

  <p>[csa_tour_button label="Want to visit in person? Schedule a Tour"]</p>
</section>
HTML;

	$faq = <<<HTML
<section class="csa-shell">
  <h1>Frequently Asked Questions</h1>
  <h2>What are your hours?</h2>
  <p>Public listings show Monday-Friday around 6:00 AM-6:00 PM. Please confirm current hours directly [VERIFY].</p>

  <h2>What ages do you serve?</h2>
  <p>We serve multiple age groups in early childhood. Current openings and age placements are confirmed during enrollment [VERIFY].</p>

  <h2>Do you offer tours?</h2>
  <p>Yes. Families are encouraged to schedule a tour to meet staff and see classrooms.</p>

  <h2>Do you provide meals or snacks?</h2>
  <p>Meals/snacks may be available based on program operations [VERIFY].</p>

  <h2>Is Spanish part of your program?</h2>
  <p>Spanish exposure/enrichment may be offered in age-appropriate ways [VERIFY].</p>

  <h2>Do you offer transportation services?</h2>
  <p>Transportation may be available depending on program and age group [VERIFY].</p>

  <h2>Do you take field trips?</h2>
  <p>Field trips may be part of select programs, depending on age and season [VERIFY].</p>

  <h2>How does enrollment work?</h2>
  <p>Start with a tour request. After your visit, our team will share next steps for availability and paperwork.</p>

  <h2>How quickly will someone follow up?</h2>
  <p>We aim to reply as soon as possible during business hours. For urgent questions, call us directly: [csa_phone_link]</p>

  <p>[csa_tour_button label="Schedule a Tour"]</p>
</section>
HTML;

	$contact = <<<HTML
<section class="csa-shell">
  <h1>Contact and Schedule a Tour</h1>
  <p>We know choosing care is a big decision. We are here to answer your questions and help you feel confident about next steps.</p>

  <div class="csa-grid">
    <article class="csa-card">
      <h2>Contact Details</h2>
      <p><strong>Address:</strong> [csa_address]</p>
      <p><strong>Phone:</strong> [csa_phone_link]</p>
      <p><strong>Email:</strong> [csa_email_link]</p>
      <p><strong>Hours:</strong> [csa_hours]</p>
      <h3>What happens next?</h3>
      <ol>
        <li>Submit the form with your preferred day/time.</li>
        <li>Our team will contact you to confirm your visit.</li>
        <li>Tour the school, meet staff, and discuss enrollment options.</li>
      </ol>
    </article>
    <article class="csa-card">
      <h2>Map</h2>
      [csa_map_embed]
    </article>
  </div>

  <article class="csa-card">
    <h2>Schedule a Tour</h2>
    [csa_schedule_tour_form]
  </article>
</section>
HTML;

	$careers = <<<HTML
<section class="csa-shell">
  <h1>Careers</h1>
  <p>Interested in joining our team? We would love to hear from caring, dependable early childhood professionals.</p>
  <p>[DO NOT PUBLISH UNTIL CONFIRMED] Add current hiring status, role openings, and application email.</p>
  <p><strong>Application Contact:</strong> [csa_email_link]</p>
  <p><strong>Phone:</strong> [csa_phone_link]</p>
</section>
HTML;

	$parent_resources = <<<HTML
<section class="csa-shell">
  <h1>Parent Resources</h1>
  <p>This page can be used for calendars, parent reminders, required forms, and policy updates.</p>
  <ul>
    <li>[OPTIONAL] Monthly calendar PDF link</li>
    <li>[OPTIONAL] Parent handbook link</li>
    <li>[OPTIONAL] Holiday closure schedule</li>
    <li>[OPTIONAL] Illness policy reminders</li>
  </ul>
  <p>[DO NOT PUBLISH UNTIL CONFIRMED] Add only current and approved resources.</p>
</section>
HTML;

	$privacy = <<<HTML
<section class="csa-shell">
  <h1>Privacy Policy</h1>
  <p>Chestnut Square Academy respects your privacy. Information submitted through this website is used only to respond to inquiries and tour requests.</p>
  <p>We do not sell personal information to third parties.</p>
  <p>[DO NOT PUBLISH UNTIL CONFIRMED] Replace this starter policy with your approved final policy text.</p>
</section>
HTML;

	return array(
		'home'                     => array(
			'title'   => 'Home',
			'content' => $home,
		),
		'about'                    => array(
			'title'   => 'About',
			'content' => $about,
		),
		'programs'                 => array(
			'title'   => 'Programs',
			'content' => $programs,
		),
		'gallery'                  => array(
			'title'   => 'Gallery',
			'content' => $gallery,
		),
		'faq'                      => array(
			'title'   => 'FAQ',
			'content' => $faq,
		),
		'contact-schedule-a-tour'  => array(
			'title'   => 'Contact / Schedule a Tour',
			'content' => $contact,
		),
		'careers'                  => array(
			'title'   => 'Careers',
			'content' => $careers,
		),
		'parent-resources'         => array(
			'title'   => 'Parent Resources',
			'content' => $parent_resources,
		),
		'privacy-policy'           => array(
			'title'   => 'Privacy Policy',
			'content' => $privacy,
		),
	);
}

/**
 * Register schedule-tour shortcode.
 */
function csa_lk_register_shortcodes() {
	add_shortcode( 'csa_schedule_tour_form', 'csa_lk_render_tour_form' );
	add_shortcode( 'csa_address', 'csa_lk_shortcode_address' );
	add_shortcode( 'csa_phone', 'csa_lk_shortcode_phone' );
	add_shortcode( 'csa_email', 'csa_lk_shortcode_email' );
	add_shortcode( 'csa_hours', 'csa_lk_shortcode_hours' );
	add_shortcode( 'csa_call_button', 'csa_lk_shortcode_call_button' );
	add_shortcode( 'csa_tour_button', 'csa_lk_shortcode_tour_button' );
	add_shortcode( 'csa_phone_link', 'csa_lk_shortcode_phone_link' );
	add_shortcode( 'csa_email_link', 'csa_lk_shortcode_email_link' );
	add_shortcode( 'csa_map_embed', 'csa_lk_shortcode_map_embed' );
}
add_action( 'init', 'csa_lk_register_shortcodes' );

/**
 * Build tel URI from a phone value.
 *
 * @param string $phone Phone string.
 * @return string
 */
function csa_lk_phone_to_tel_uri( $phone ) {
	$numeric = preg_replace( '/[^0-9+]/', '', (string) $phone );

	if ( empty( $numeric ) || csa_lk_has_placeholder_token( (string) $phone ) ) {
		return '#';
	}

	return 'tel:' . $numeric;
}

/**
 * Shortcode: address.
 *
 * @return string
 */
function csa_lk_shortcode_address() {
	return esc_html( csa_lk_get_business_option( 'csa_lk_business_address' ) );
}

/**
 * Shortcode: phone.
 *
 * @return string
 */
function csa_lk_shortcode_phone() {
	return esc_html( csa_lk_get_business_option( 'csa_lk_business_phone' ) );
}

/**
 * Shortcode: email.
 *
 * @return string
 */
function csa_lk_shortcode_email() {
	return esc_html( csa_lk_get_business_option( 'csa_lk_business_email' ) );
}

/**
 * Shortcode: hours.
 *
 * @return string
 */
function csa_lk_shortcode_hours() {
	return esc_html( csa_lk_get_business_option( 'csa_lk_business_hours' ) );
}

/**
 * Shortcode: call button.
 *
 * @param array<string,string> $atts Attributes.
 * @return string
 */
function csa_lk_shortcode_call_button( $atts ) {
	$atts  = shortcode_atts(
		array(
			'label' => 'Call Now',
		),
		$atts
	);
	$phone = csa_lk_get_business_option( 'csa_lk_business_phone' );
	$href  = csa_lk_phone_to_tel_uri( $phone );

	return '<a class="csa-btn csa-btn-secondary" href="' . esc_url( $href ) . '">' . esc_html( $atts['label'] ) . '</a>';
}

/**
 * Shortcode: tour button.
 *
 * @param array<string,string> $atts Attributes.
 * @return string
 */
function csa_lk_shortcode_tour_button( $atts ) {
	$atts = shortcode_atts(
		array(
			'label' => 'Schedule a Tour',
		),
		$atts
	);

	return '<a class="csa-btn csa-btn-primary" href="' . esc_url( home_url( '/contact-schedule-a-tour/' ) ) . '">' . esc_html( $atts['label'] ) . '</a>';
}

/**
 * Shortcode: phone link.
 *
 * @return string
 */
function csa_lk_shortcode_phone_link() {
	$phone = csa_lk_get_business_option( 'csa_lk_business_phone' );
	$href  = csa_lk_phone_to_tel_uri( $phone );

	return '<a href="' . esc_url( $href ) . '">' . esc_html( $phone ) . '</a>';
}

/**
 * Shortcode: email link.
 *
 * @return string
 */
function csa_lk_shortcode_email_link() {
	$email = csa_lk_get_business_option( 'csa_lk_business_email' );
	$href  = is_email( $email ) ? 'mailto:' . sanitize_email( $email ) : '#';

	return '<a href="' . esc_url( $href ) . '">' . esc_html( $email ) . '</a>';
}

/**
 * Shortcode: map embed iframe.
 *
 * @return string
 */
function csa_lk_shortcode_map_embed() {
	$src = csa_lk_get_business_option( 'csa_lk_business_map_embed' );

	if ( empty( $src ) ) {
		return '<p>[VERIFY] Add map embed URL in Settings > CSA Business Profile.</p>';
	}

	return '<iframe class="csa-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="' . esc_url( $src ) . '" title="Map to Chestnut Square Academy"></iframe>';
}

/**
 * Output LocalBusiness schema when profile data is complete.
 */
function csa_lk_output_localbusiness_schema() {
	if ( is_admin() ) {
		return;
	}

	if ( '1' !== (string) get_option( 'csa_lk_enable_local_schema', '1' ) ) {
		return;
	}

	$required = array(
		csa_lk_get_business_option( 'csa_lk_business_name' ),
		csa_lk_get_business_option( 'csa_lk_business_address' ),
		csa_lk_get_business_option( 'csa_lk_business_phone' ),
		csa_lk_get_business_option( 'csa_lk_business_email' ),
	);

	foreach ( $required as $value ) {
		if ( '' === trim( $value ) || csa_lk_has_placeholder_token( $value ) ) {
			return;
		}
	}

	$profile = csa_lk_get_business_profile_data();

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'ChildCare',
		'name'        => $profile['name'],
		'url'         => home_url( '/' ),
		'telephone'   => $profile['phone'],
		'email'       => $profile['email'],
		'description' => $profile['description'],
		'address'     => array(
			'@type'         => 'PostalAddress',
			'streetAddress' => $profile['address'],
		),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
}
add_action( 'wp_head', 'csa_lk_output_localbusiness_schema', 40 );

/**
 * Extract FAQ question/answer pairs from page content.
 *
 * @param string $content HTML content.
 * @return array<int,array<string,string>>
 */
function csa_lk_extract_faq_pairs( $content ) {
	$pairs = array();

	if ( ! is_string( $content ) || '' === trim( $content ) ) {
		return $pairs;
	}

	if ( ! preg_match_all( '/<h2[^>]*>(.*?)<\\/h2>\\s*<p[^>]*>(.*?)<\\/p>/is', $content, $matches, PREG_SET_ORDER ) ) {
		return $pairs;
	}

	foreach ( $matches as $match ) {
		$question = html_entity_decode( wp_strip_all_tags( $match[1] ), ENT_QUOTES, 'UTF-8' );
		$answer   = html_entity_decode( wp_strip_all_tags( $match[2] ), ENT_QUOTES, 'UTF-8' );

		$question = trim( preg_replace( '/\\s+/', ' ', $question ) );
		$answer   = trim( preg_replace( '/\\s+/', ' ', $answer ) );

		if ( '' === $question || '' === $answer ) {
			continue;
		}

		if ( csa_lk_has_placeholder_token( $question ) || csa_lk_has_placeholder_token( $answer ) ) {
			continue;
		}

		$pairs[] = array(
			'question' => $question,
			'answer'   => $answer,
		);
	}

	return $pairs;
}

/**
 * Output FAQPage schema on the FAQ page when content is publish-ready.
 */
function csa_lk_output_faq_schema() {
	if ( is_admin() || ! is_page( 'faq' ) ) {
		return;
	}

	if ( '1' !== (string) get_option( 'csa_lk_enable_faq_schema', '1' ) ) {
		return;
	}

	$post = get_post();
	if ( ! $post || 'page' !== $post->post_type ) {
		return;
	}

	$content = do_shortcode( (string) $post->post_content );
	$pairs   = csa_lk_extract_faq_pairs( $content );

	if ( count( $pairs ) < 2 ) {
		return;
	}

	$entities = array();
	foreach ( $pairs as $pair ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $pair['question'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $pair['answer'],
			),
		);
	}

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
}
add_action( 'wp_head', 'csa_lk_output_faq_schema', 41 );

/**
 * Shortcode output for tour form.
 *
 * @return string
 */
function csa_lk_render_tour_form() {
	$status = isset( $_GET['csa_tour'] ) ? sanitize_text_field( wp_unslash( $_GET['csa_tour'] ) ) : '';
	$output = '';

	if ( 'success' === $status ) {
		$message = get_option( 'csa_lk_tour_success_message', '' );
		$output .= '<p class="csa-note"><strong>' . esc_html( $message ) . '</strong></p>';
	} elseif ( 'missing' === $status ) {
		$output .= '<p class="csa-note"><strong>Please fill in all required fields.</strong></p>';
	} elseif ( 'error' === $status ) {
		$output .= '<p class="csa-note"><strong>Something went wrong while submitting your request. Please try again or call us directly.</strong></p>';
	}

	$output .= '<form class="csa-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	$output .= wp_nonce_field( 'csa_lk_submit_tour', 'csa_lk_nonce', true, false );
	$output .= '<input type="hidden" name="action" value="csa_lk_submit_tour" />';
	$output .= '<div style="position:absolute;left:-9999px;"><label>Leave this field empty<input type="text" name="website" value="" autocomplete="off" /></label></div>';

	$output .= '<p><label for="csa_parent_name">Parent/Guardian Name *</label><input id="csa_parent_name" name="parent_name" type="text" required /></p>';
	$output .= '<p><label for="csa_child_age">Child Age *</label><select id="csa_child_age" name="child_age" required>';
	$output .= '<option value="">Select one</option>';
	$output .= '<option value="6 weeks - 12 months">6 weeks - 12 months</option>';
	$output .= '<option value="1 - 2 years">1 - 2 years</option>';
	$output .= '<option value="3 - 4 years">3 - 4 years</option>';
	$output .= '<option value="5+ years">5+ years [VERIFY]</option>';
	$output .= '<option value="Other">Other</option>';
	$output .= '</select></p>';

	$output .= '<p><label for="csa_phone">Phone *</label><input id="csa_phone" name="phone" type="tel" required /></p>';
	$output .= '<p><label for="csa_email">Email *</label><input id="csa_email" name="email" type="email" required /></p>';
	$output .= '<p><label for="csa_tour_time">Preferred Tour Day/Time *</label><input id="csa_tour_time" name="tour_time" type="text" required placeholder="Example: Tuesday at 10:00 AM" /></p>';
	$output .= '<p><label for="csa_notes">Questions / Notes</label><textarea id="csa_notes" name="notes" rows="4"></textarea></p>';
	$output .= '<p><label><input type="checkbox" name="privacy_agree" value="1" required /> I agree to be contacted about my tour request.</label></p>';
	$output .= '<p><button class="csa-btn csa-btn-primary" type="submit">Submit Tour Request</button></p>';
	$output .= '<p><small>Your information is used only to respond to your tour request and is not sold to third parties.</small></p>';
	$output .= '</form>';

	return $output;
}

/**
 * Process tour form submission.
 */
function csa_lk_handle_tour_submission() {
	if ( ! isset( $_POST['csa_lk_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['csa_lk_nonce'] ) ), 'csa_lk_submit_tour' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'csa-launch-kit' ) );
	}

	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/contact-schedule-a-tour/' );
	$redirect = remove_query_arg( 'csa_tour', $redirect );

	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'csa_tour', 'success', $redirect ) );
		exit;
	}

	$parent_name = isset( $_POST['parent_name'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_name'] ) ) : '';
	$child_age   = isset( $_POST['child_age'] ) ? sanitize_text_field( wp_unslash( $_POST['child_age'] ) ) : '';
	$phone       = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$tour_time   = isset( $_POST['tour_time'] ) ? sanitize_text_field( wp_unslash( $_POST['tour_time'] ) ) : '';
	$notes       = isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '';
	$consent     = isset( $_POST['privacy_agree'] ) ? sanitize_text_field( wp_unslash( $_POST['privacy_agree'] ) ) : '';

	if ( ! $parent_name || ! $child_age || ! $phone || ! $email || ! $tour_time || ! $consent ) {
		wp_safe_redirect( add_query_arg( 'csa_tour', 'missing', $redirect ) );
		exit;
	}

	$post_content = "Parent/Guardian: {$parent_name}\n";
	$post_content .= "Child Age: {$child_age}\n";
	$post_content .= "Phone: {$phone}\n";
	$post_content .= "Email: {$email}\n";
	$post_content .= "Preferred Tour Time: {$tour_time}\n";
	$post_content .= "Notes: {$notes}\n";

	$inserted = wp_insert_post(
		array(
			'post_type'    => 'csa_tour_request',
			'post_status'  => 'private',
			'post_title'   => 'Tour Request - ' . $parent_name . ' - ' . current_time( 'Y-m-d H:i' ),
			'post_content' => $post_content,
		),
		true
	);

	$to      = get_option( 'csa_lk_tour_email', get_option( 'admin_email' ) );
	$subject = 'New Tour Request - Chestnut Square Academy';
	$body    = "A new tour request was submitted:\n\n" . $post_content;
	$headers = array( 'Reply-To: ' . $parent_name . ' <' . $email . '>' );

	wp_mail( $to, $subject, $body, $headers );

	if ( is_wp_error( $inserted ) ) {
		wp_safe_redirect( add_query_arg( 'csa_tour', 'error', $redirect ) );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'csa_tour', 'success', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_csa_lk_submit_tour', 'csa_lk_handle_tour_submission' );
add_action( 'admin_post_csa_lk_submit_tour', 'csa_lk_handle_tour_submission' );
