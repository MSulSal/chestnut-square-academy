<?php
/**
 * Plugin Name: CSA Launch Kit
 * Plugin URI: https://chestnutsquareacademy.local
 * Description: One-click starter setup for Chestnut Square Academy pages, menus, business profile, and Schedule a Tour form.
 * Version: 1.7.0
 * Author: CSA Web Team
 * License: GPL-2.0-or-later
 * Text Domain: csa-launch-kit
 *
 * @package CsaLaunchKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CSA_LAUNCH_KIT_VERSION', '1.7.0' );

/**
 * Return default business profile values.
 *
 * @return array<string,string>
 */
function csa_lk_get_business_defaults() {
	return array(
		'csa_lk_business_name'        => 'Chestnut Square Academy',
		'csa_lk_business_address'     => '402 S Chestnut St, McKinney, TX 75069',
		'csa_lk_business_phone'       => '(972) 369-7512',
		'csa_lk_business_email'       => get_option( 'admin_email' ),
		'csa_lk_business_hours'       => 'Monday-Friday, 6:00 AM-6:00 PM',
		'csa_lk_business_map_embed'   => 'https://www.google.com/maps?q=402+S+Chestnut+St,+McKinney,+TX+75069&output=embed',
		'csa_lk_business_description' => 'Chestnut Square Academy is a Texas Rising Star daycare in Downtown McKinney, offering care for children from 6 weeks through age 5/6 in a small, family-focused center.',
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
 * Get recommended plugin map.
 *
 * @return array<string,string>
 */
function csa_lk_get_recommended_plugins() {
	return array(
		'wordpress-seo/wp-seo.php'                  => 'Yoast SEO',
		'updraftplus/updraftplus.php'               => 'UpdraftPlus',
		'wp-mail-smtp/wp_mail_smtp.php'             => 'WP Mail SMTP',
		'better-wp-security/better-wp-security.php' => 'Solid Security',
	);
}

/**
 * Get owner-facing data entry map with examples.
 *
 * @return array<int,array<string,string>>
 */
function csa_lk_get_owner_data_entry_map() {
	return array(
		array(
			'field'   => 'Business Name',
			'where'   => 'LocalBusiness schema, citation block, global profile references.',
			'example' => 'Chestnut Square Academy',
			'edit'    => 'Settings > CSA Business Profile',
		),
		array(
			'field'   => 'Address',
			'where'   => 'Contact page, quick facts sections, map/citation references.',
			'example' => '402 S Chestnut St, McKinney, TX 75069',
			'edit'    => 'Settings > CSA Business Profile',
		),
		array(
			'field'   => 'Phone',
			'where'   => 'Call buttons and phone links across pages.',
			'example' => '(972) 369-7512 [VERIFY]',
			'edit'    => 'Settings > CSA Business Profile',
		),
		array(
			'field'   => 'Public Email',
			'where'   => 'Contact page and email links.',
			'example' => 'director@yourdomain.com [VERIFY]',
			'edit'    => 'Settings > CSA Business Profile',
		),
		array(
			'field'   => 'Hours',
			'where'   => 'Quick facts, contact details, FAQ wording.',
			'example' => 'Monday-Friday, 6:00 AM-6:00 PM',
			'edit'    => 'Settings > CSA Business Profile',
		),
		array(
			'field'   => 'Google Map Embed URL',
			'where'   => 'Map block on Contact / Schedule a Tour page.',
			'example' => 'https://www.google.com/maps?q=402+S+Chestnut+St,+McKinney,+TX+75069&output=embed',
			'edit'    => 'Settings > CSA Business Profile',
		),
		array(
			'field'   => 'Business Description',
			'where'   => 'LocalBusiness schema description.',
			'example' => 'Trusted early learning and childcare in Downtown McKinney, Texas.',
			'edit'    => 'Settings > CSA Business Profile',
		),
		array(
			'field'   => 'Tour Notification Email',
			'where'   => 'Admin routing for Schedule a Tour submissions.',
			'example' => 'enrollment@example.com',
			'edit'    => 'Settings > CSA Tour Form',
		),
		array(
			'field'   => 'Tour Success Message',
			'where'   => 'Confirmation text shown after form submit.',
			'example' => 'Thank you. Your tour request has been received...',
			'edit'    => 'Settings > CSA Tour Form',
		),
		array(
			'field'   => 'Page Content Placeholders',
			'where'   => 'Home/About/Programs/Gallery/FAQ/Contact and optional pages.',
			'example' => 'Replace all [VERIFY] and [DO NOT PUBLISH UNTIL CONFIRMED] tokens.',
			'edit'    => 'Pages > Edit with Elementor',
		),
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
	$plugin_status = isset( $_GET['csa_plugins'] ) ? sanitize_text_field( wp_unslash( $_GET['csa_plugins'] ) ) : '';
	$audit  = csa_lk_get_publish_audit();
	$data_map = csa_lk_get_owner_data_entry_map();
	?>
	<div class="wrap">
		<h1>CSA Launch Kit</h1>
		<p>Run one-click setup to create or update starter pages, menu links, and homepage assignment.</p>
		<p><strong>Current phase:</strong> content scaffold + verification gating. Final polished visuals are completed in Elementor after this setup pass.</p>
		<p><strong>Starter content source note:</strong> seeded copy intentionally blends public listing signals (address/hours/program hints) with clearly marked <code>[VERIFY]</code> items so owners can replace unknown facts safely.</p>
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
		<?php if ( 'activated' === $plugin_status ) : ?>
			<div class="notice notice-success is-dismissible"><p>Recommended plugins activated (where available).</p></div>
		<?php elseif ( 'plugin-error' === $plugin_status ) : ?>
			<div class="notice notice-error is-dismissible"><p>Could not activate one or more recommended plugins. Check plugin list and try again.</p></div>
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
			<?php wp_nonce_field( 'csa_lk_activate_recommended_plugins' ); ?>
			<input type="hidden" name="action" value="csa_lk_activate_recommended_plugins" />
			<?php submit_button( 'Activate Recommended Plugins', 'secondary', 'submit', false ); ?>
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

		<h2>Owner Data Entry Map (With Examples)</h2>
		<p>Use this as a fill-in guide for client-provided data. No real data needs to be injected by developers.</p>
		<table class="widefat striped" style="max-width: 1200px;">
			<thead>
				<tr>
					<th>Field</th>
					<th>Where It Appears</th>
					<th>Example Format</th>
					<th>Where To Edit</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data_map as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( $entry['field'] ); ?></td>
						<td><?php echo esc_html( $entry['where'] ); ?></td>
						<td><?php echo esc_html( $entry['example'] ); ?></td>
						<td><?php echo esc_html( $entry['edit'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

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

	foreach ( csa_lk_get_recommended_plugins() as $plugin_file => $label ) {
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
						<input type="email" class="regular-text" id="csa_lk_tour_email" name="csa_lk_tour_email" placeholder="Example: enrollment@example.com" value="<?php echo esc_attr( get_option( 'csa_lk_tour_email', get_option( 'admin_email' ) ) ); ?>" />
						<p class="description">Tour requests are sent to this inbox. Example format: <code>enrollment@example.com</code>.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_tour_success_message">Success Message</label></th>
					<td>
						<textarea class="large-text" rows="3" id="csa_lk_tour_success_message" name="csa_lk_tour_success_message" placeholder="Example: Thank you. Your tour request has been received."><?php echo esc_textarea( get_option( 'csa_lk_tour_success_message', '' ) ); ?></textarea>
						<p class="description">Shown after successful submission. Keep tone warm and clear.</p>
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
					<td>
						<input type="text" class="regular-text" id="csa_lk_business_name" name="csa_lk_business_name" placeholder="Example: Chestnut Square Academy" value="<?php echo esc_attr( csa_lk_get_business_option( 'csa_lk_business_name' ) ); ?>" />
						<p class="description">Used for citation block and schema name.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_address">Address</label></th>
					<td>
						<textarea class="large-text" rows="2" id="csa_lk_business_address" name="csa_lk_business_address" placeholder="Example: 402 S Chestnut St, McKinney, TX 75069"><?php echo esc_textarea( csa_lk_get_business_option( 'csa_lk_business_address' ) ); ?></textarea>
						<p class="description">Shown on Contact page and citation references.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_phone">Phone</label></th>
					<td>
						<input type="text" class="regular-text" id="csa_lk_business_phone" name="csa_lk_business_phone" placeholder="Example: (972) 555-0123" value="<?php echo esc_attr( csa_lk_get_business_option( 'csa_lk_business_phone' ) ); ?>" />
						<p class="description">Used for click-to-call buttons and phone links.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_email">Public Email</label></th>
					<td>
						<input type="email" class="regular-text" id="csa_lk_business_email" name="csa_lk_business_email" placeholder="Example: director@example.com" value="<?php echo esc_attr( csa_lk_get_business_option( 'csa_lk_business_email' ) ); ?>" />
						<p class="description">Shown on Contact page and used in schema.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_hours">Hours</label></th>
					<td>
						<input type="text" class="regular-text" id="csa_lk_business_hours" name="csa_lk_business_hours" placeholder="Example: Monday-Friday, 6:00 AM-6:00 PM" value="<?php echo esc_attr( csa_lk_get_business_option( 'csa_lk_business_hours' ) ); ?>" />
						<p class="description">Appears in quick facts, contact details, and FAQ references.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_map_embed">Google Map Embed URL</label></th>
					<td>
						<textarea class="large-text" rows="2" id="csa_lk_business_map_embed" name="csa_lk_business_map_embed" placeholder="Example: https://www.google.com/maps?q=402+S+Chestnut+St,+McKinney,+TX+75069&output=embed"><?php echo esc_textarea( csa_lk_get_business_option( 'csa_lk_business_map_embed' ) ); ?></textarea>
						<p class="description">Used in Contact page map block via <code>[csa_map_embed]</code>.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="csa_lk_business_description">Business Description</label></th>
					<td>
						<textarea class="large-text" rows="2" id="csa_lk_business_description" name="csa_lk_business_description" placeholder="Example: Trusted early learning and childcare in Downtown McKinney, Texas."><?php echo esc_textarea( csa_lk_get_business_option( 'csa_lk_business_description' ) ); ?></textarea>
						<p class="description">Used for LocalBusiness schema description.</p>
					</td>
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
 * Handle one-click activation of recommended plugins.
 */
function csa_lk_handle_activate_recommended_plugins_action() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_die( esc_html__( 'You do not have permission to activate plugins.', 'csa-launch-kit' ) );
	}

	check_admin_referer( 'csa_lk_activate_recommended_plugins' );

	if ( ! function_exists( 'activate_plugin' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$has_error = false;
	foreach ( csa_lk_get_recommended_plugins() as $plugin_file => $label ) {
		if ( csa_lk_is_plugin_active( $plugin_file ) ) {
			continue;
		}

		$absolute = WP_PLUGIN_DIR . '/' . $plugin_file;
		if ( ! file_exists( $absolute ) ) {
			$has_error = true;
			continue;
		}

		$result = activate_plugin( $plugin_file, '', false, true );
		if ( is_wp_error( $result ) ) {
			$has_error = true;
		}
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'        => 'csa-launch-kit',
				'csa_plugins' => $has_error ? 'plugin-error' : 'activated',
			),
			admin_url( 'tools.php' )
		)
	);
	exit;
}
add_action( 'admin_post_csa_lk_activate_recommended_plugins', 'csa_lk_handle_activate_recommended_plugins_action' );

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
  <div class="csa-hero-grid">
    <div>
      <div class="csa-logo-wrap">[csa_logo]</div>
      <h1>Nurturing Early Learning in Historic Downtown McKinney</h1>
      <p>Chestnut Square Academy is located in the heart of the Historic Chestnut Village and offers warm, reliable childcare for families in Downtown McKinney.</p>
      <div class="csa-cta-row">[csa_tour_button label="Schedule a Tour"] [csa_call_button label="Call Now"]</div>
    </div>
    <div class="csa-hero-photo">[csa_vibe_photo]</div>
  </div>
</section>

<section class="csa-shell">
  <h2>Quick Facts</h2>
  <div class="csa-quickfacts">
    <div class="csa-fact"><strong>Location:</strong><br>[csa_address]</div>
    <div class="csa-fact"><strong>Hours:</strong><br>[csa_hours]</div>
    <div class="csa-fact"><strong>Ages Served:</strong><br>6 weeks to 5/6 years</div>
    <div class="csa-fact"><strong>Center Size:</strong><br>Small facility with occupancy of 46</div>
  </div>
</section>

<section class="csa-shell">
  <h2>Why Families Choose Us</h2>
  <div class="csa-grid">
    <article class="csa-card"><h3>Texas Rising Star Program</h3><p>CSA is a Texas Rising Star daycare located in Downtown McKinney.</p></article>
    <article class="csa-card"><h3>Small-Center Attention</h3><p>With occupancy of 46, children and families receive personal, attentive care.</p></article>
    <article class="csa-card"><h3>Family Commitment</h3><p>Our team is committed to children and their families with dependable daily support.</p></article>
    <article class="csa-card"><h3>Downtown Convenience</h3><p>A trusted neighborhood center in the Historic Chestnut Village area.</p></article>
  </div>
</section>

<section class="csa-shell">
  <h2>Programs Snapshot</h2>
  <div class="csa-grid">
    <article class="csa-card"><h3>Infants</h3><p>Responsive, nurturing care that supports comfort, bonding, and early development.</p></article>
    <article class="csa-card"><h3>Toddlers</h3><p>Active learning through movement, language, exploration, and guided play.</p></article>
    <article class="csa-card"><h3>Preschool</h3><p>Hands-on activities that build confidence, social growth, and school readiness.</p></article>
    <article class="csa-card"><h3>Pre-K</h3><p>A steady transition year focused on routine, independence, and learning foundations.</p></article>
  </div>
</section>

<section class="csa-shell">
  <h2>A Warm, Everyday Learning Environment</h2>
  <p>From arrival to pickup, children are supported with caring relationships, age-appropriate learning, and a calm daily rhythm that helps them feel secure and engaged.</p>
</section>

<section class="csa-shell">
  <h2>Gallery Highlights</h2>
  <div class="csa-grid">
    <article class="csa-card"><h3>Classroom Moments</h3><p>A look inside age-based spaces where children learn and grow each day.</p></article>
    <article class="csa-card"><h3>Teacher Interactions</h3><p>Warm guidance and one-on-one support throughout the day.</p></article>
    <article class="csa-card"><h3>Creative Activities</h3><p>Hands-on projects that build confidence, curiosity, and joy.</p></article>
    <article class="csa-card"><h3>Our Downtown Home</h3><p>Located at 402 S Chestnut Street in the heart of Historic Downtown McKinney.</p></article>
  </div>
</section>

<section class="csa-shell">
  <h2>A Neighborhood School in Historic Downtown McKinney</h2>
  <p>Chestnut Square Academy blends neighborhood warmth with professional childcare in a location families know and trust.</p>
</section>

<section class="csa-shell">
  <h2>FAQ Preview</h2>
  <ul>
    <li><strong>What ages do you accept?</strong> We provide care for children from 6 weeks to 5/6 years of age.</li>
    <li><strong>Are you Texas Rising Star?</strong> Yes, CSA is part of the Texas Rising Star Program.</li>
    <li><strong>Can we schedule a tour?</strong> Yes. Submit a request and our team will follow up to confirm your visit.</li>
  </ul>
  <p><a href="/faq/">View All FAQs</a></p>
</section>

<section class="csa-shell csa-card">
  <h2>Ready to Visit Chestnut Square Academy?</h2>
  <p>We would love to meet your family, answer your questions, and help you find the right fit.</p>
  <div class="csa-cta-row">[csa_tour_button label="Schedule a Tour"] [csa_call_button label="Call Now"]</div>
</section>

<section class="csa-shell">
  <h2>Contact and Map</h2>
  <div class="csa-grid">
    <article class="csa-card">
      <p><strong>Address:</strong> [csa_address]</p>
      <p><strong>Phone:</strong> [csa_phone_link]</p>
      <p><strong>Hours:</strong> [csa_hours]</p>
    </article>
    <article class="csa-card">[csa_map_embed]</article>
  </div>
</section>
HTML;

	$about = <<<HTML
<section class="csa-shell">
  <h1>About Chestnut Square Academy</h1>
  <p>Chestnut Square Academy is located in the heart of the Historic Chestnut Village in Downtown McKinney. We offer care for children from 6 weeks to 5/6 years of age in a warm, family-centered environment.</p>

  <h2>Our Family-First Approach</h2>
  <p>We are committed to our children as well as their families. Every day is built around safety, consistency, and caring teacher-child relationships that support social, emotional, and early learning growth.</p>

  <h2>What Families Can Expect</h2>
  <ul>
    <li>A welcoming environment where children are known by name</li>
    <li>Age-appropriate activities that keep children engaged throughout the day</li>
    <li>Clear communication and a dependable daily routine</li>
    <li>A small-center setting with occupancy of 46</li>
  </ul>

  <h2>Rooted in Downtown McKinney</h2>
  <p>Our location makes Chestnut Square Academy a trusted neighborhood option for families who want quality childcare in a familiar and convenient part of McKinney.</p>

  <h2>Meet Our Team</h2>
  <div class="csa-grid">
    <article class="csa-card"><h3>Caring Educators</h3><p>Our teachers support each age group with patience, structure, and encouragement.</p></article>
    <article class="csa-card"><h3>Family Partnership</h3><p>We work closely with parents to help each child thrive in and out of the classroom.</p></article>
    <article class="csa-card"><h3>Consistent Leadership</h3><p>Our staff is focused on creating a dependable experience families can trust.</p></article>
  </div>

  <p>[csa_tour_button label="Schedule a Tour"]</p>
</section>
HTML;

	$programs = <<<HTML
<section class="csa-shell">
  <h1>Programs</h1>
  <p>We offer age-appropriate care and learning experiences for children from 6 weeks to 5/6 years of age.</p>

  <div class="csa-grid">
    <article class="csa-card"><h2>Infants</h2><p>Nurturing care that supports early bonding, comfort, and developmental milestones.</p></article>
    <article class="csa-card"><h2>Toddlers</h2><p>Guided exploration, language growth, and active social learning.</p></article>
    <article class="csa-card"><h2>Preschool</h2><p>Hands-on early learning focused on confidence, routines, and kindergarten foundations.</p></article>
    <article class="csa-card"><h2>Pre-K</h2><p>Structured preparation for school with daily practice in independence and classroom readiness.</p></article>
  </div>

  <h2>A Typical Day</h2>
  <p>Children follow a consistent daily rhythm that includes:</p>
  <ul>
    <li>Morning welcome and transition</li>
    <li>Teacher-led learning and guided play</li>
    <li>Creative activity blocks and movement</li>
    <li>Rest or quiet-time routines by age group</li>
    <li>Afternoon activities and pickup support</li>
  </ul>

  <h2>A Small-Center Experience</h2>
  <p>With occupancy at 46, CSA provides a close-knit setting where children receive personal attention and families build strong relationships with staff.</p>

  <p>[csa_tour_button label="Schedule a Tour"]</p>
</section>
HTML;

	$gallery = <<<HTML
<section class="csa-shell">
  <h1>Gallery</h1>
  <p>A look inside everyday moments at Chestnut Square Academy.</p>

  <h2>Featured Moments</h2>
  <div class="csa-gallery-grid">
    <figure class="csa-gallery-tile">[csa_vibe_photo]<figcaption>Hands-on learning with caring teacher support</figcaption></figure>
    <figure class="csa-gallery-tile">[csa_vibe_photo]<figcaption>Creative classroom activities that spark curiosity</figcaption></figure>
    <figure class="csa-gallery-tile">[csa_vibe_photo]<figcaption>Warm, relationship-based early learning</figcaption></figure>
  </div>

  <h2>Photo Themes</h2>
  <ul>
    <li>Classroom learning time</li>
    <li>Teacher-child interaction</li>
    <li>Art, sensory, and activity moments</li>
    <li>School environment and daily routines</li>
  </ul>

  <p>[csa_tour_button label="Want to visit in person? Schedule a Tour"]</p>
</section>
HTML;

	$faq = <<<HTML
<section class="csa-shell">
  <h1>Frequently Asked Questions</h1>
  <h2>What are your hours?</h2>
  <p>We are open Monday through Friday, 6:00 AM to 6:00 PM.</p>

  <h2>What ages do you serve?</h2>
  <p>Chestnut Square Academy offers care for children from 6 weeks to 5/6 years of age.</p>

  <h2>Do you offer tours?</h2>
  <p>Yes. Families are encouraged to schedule a tour to meet staff and see classrooms.</p>

  <h2>What should I bring to a tour?</h2>
  <p>Bring your child age details, preferred start timeline, and any questions you would like to discuss with our team.</p>

  <h2>How does enrollment work?</h2>
  <p>Start with a tour request. After your visit, our team will share next steps for availability and paperwork.</p>

  <h2>Are you part of Texas Rising Star?</h2>
  <p>Yes. CSA is part of the Texas Rising Star Program and serves families in Downtown McKinney.</p>

  <h2>How large is the center?</h2>
  <p>CSA is a small facility with occupancy of 46, which helps us maintain a personal, family-focused environment.</p>

  <h2>How quickly will someone follow up?</h2>
  <p>We aim to reply as soon as possible during business hours. For urgent questions, call us directly: [csa_phone_link]</p>

  <p>[csa_tour_button label="Schedule a Tour"]</p>
</section>
HTML;

	$contact = <<<HTML
<section class="csa-shell">
  <h1>Contact and Schedule a Tour</h1>
  <p>Choosing care is a big decision. We are here to answer questions and help your family feel confident about next steps.</p>

  <div class="csa-grid">
    <article class="csa-card">
      <h2>Contact Details</h2>
      <p><strong>Address:</strong> [csa_address]</p>
      <p><strong>Phone:</strong> [csa_phone_link]</p>
      <p><strong>Hours:</strong> [csa_hours]</p>
      <h3>What happens next?</h3>
      <ol>
        <li>Submit the form with your preferred day/time.</li>
        <li>Our team will contact you to confirm your visit window.</li>
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
  <p>If you are passionate about early learning and family partnership, we welcome your interest.</p>
  <p><strong>Phone:</strong> [csa_phone_link]</p>
</section>
HTML;

	$parent_resources = <<<HTML
<section class="csa-shell">
  <h1>Parent Resources</h1>
  <p>Helpful updates and planning tools for current families.</p>
  <ul>
    <li>Monthly calendar and event dates</li>
    <li>School reminders and closure notices</li>
    <li>Family communication updates</li>
    <li>General policy and routine information</li>
  </ul>
</section>
HTML;

	$privacy = <<<HTML
<section class="csa-shell">
  <h1>Privacy Policy</h1>
  <p>Chestnut Square Academy respects your privacy. Information submitted through this website is used only to respond to inquiries and tour requests.</p>
  <p>We do not sell personal information to third parties.</p>
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
	add_shortcode( 'csa_logo', 'csa_lk_shortcode_logo' );
	add_shortcode( 'csa_vibe_photo', 'csa_lk_shortcode_vibe_photo' );
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
		return '';
	}

	return '<iframe class="csa-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="' . esc_url( $src ) . '" title="Map to Chestnut Square Academy"></iframe>';
}

/**
 * Build theme asset image URI if file exists.
 *
 * @param string $filename Asset file name.
 * @return string
 */
function csa_lk_get_theme_image_uri( $filename ) {
	$filename = sanitize_file_name( $filename );

	if ( '' === $filename ) {
		return '';
	}

	$file_path = trailingslashit( get_stylesheet_directory() ) . 'assets/images/' . $filename;
	if ( ! file_exists( $file_path ) ) {
		return '';
	}

	return trailingslashit( get_stylesheet_directory_uri() ) . 'assets/images/' . rawurlencode( $filename );
}

/**
 * Shortcode: logo image.
 *
 * @return string
 */
function csa_lk_shortcode_logo() {
	$src = csa_lk_get_theme_image_uri( 'logo.jpg' );

	if ( '' === $src ) {
		return '';
	}

	return '<img class="csa-logo-image" src="' . esc_url( $src ) . '" alt="Chestnut Square Academy logo" loading="eager" decoding="async" />';
}

/**
 * Shortcode: vibe image.
 *
 * @return string
 */
function csa_lk_shortcode_vibe_photo() {
	$src = csa_lk_get_theme_image_uri( 'vibe.jpg' );

	if ( '' === $src ) {
		return '';
	}

	return '<img class="csa-vibe-image" src="' . esc_url( $src ) . '" alt="Teacher and child participating in a hands-on classroom activity" loading="lazy" decoding="async" />';
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
	$output .= '<option value="5+ years">5+ years</option>';
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
