<?php
/**
 * Plugin Name: CSA Launch Kit
 * Plugin URI: https://chestnutsquareacademy.local
 * Description: One-click starter setup for Chestnut Square Academy pages, menus, and Schedule a Tour form.
 * Version: 1.0.0
 * Author: CSA Web Team
 * License: GPL-2.0-or-later
 * Text Domain: csa-launch-kit
 *
 * @package CsaLaunchKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CSA_LAUNCH_KIT_VERSION', '1.0.0' );

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
 * Render tools page.
 */
function csa_lk_render_tools_page() {
	$status = isset( $_GET['csa_setup'] ) ? sanitize_text_field( wp_unslash( $_GET['csa_setup'] ) ) : '';
	?>
	<div class="wrap">
		<h1>CSA Launch Kit</h1>
		<p>Run one-click setup to create or update starter pages, menu links, and homepage assignment.</p>
		<?php if ( 'done' === $status ) : ?>
			<div class="notice notice-success is-dismissible"><p>Setup complete.</p></div>
		<?php elseif ( 'error' === $status ) : ?>
			<div class="notice notice-error is-dismissible"><p>Setup could not complete. Please check permissions and try again.</p></div>
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

		<hr />

		<h2>Next Steps</h2>
		<ol>
			<li>Activate <strong>Hello Elementor CSA</strong> child theme.</li>
			<li>Activate <strong>Elementor</strong> plugin.</li>
			<li>Open pages in Elementor and replace all <code>[VERIFY]</code> placeholders before publishing.</li>
			<li>Set the Schedule a Tour notification email in Settings > CSA Tour Form.</li>
		</ol>
	</div>
	<?php
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
  <div class="csa-cta-row">
    <a class="csa-btn csa-btn-primary" href="/contact-schedule-a-tour/">Schedule a Tour</a>
    <a class="csa-btn csa-btn-secondary" href="tel:[VERIFY PHONE]">Call Now [VERIFY]</a>
  </div>
</section>

<section class="csa-shell">
  <h2>Quick Facts</h2>
  <div class="csa-quickfacts">
    <div class="csa-fact"><strong>Location:</strong><br>402 S Chestnut St, McKinney, TX 75069 [VERIFY]</div>
    <div class="csa-fact"><strong>Hours:</strong><br>Monday-Friday, 6:00 AM-6:00 PM [VERIFY]</div>
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
    <li><strong>How do I get started?</strong> Submit a tour request form or call us directly [VERIFY PHONE].</li>
  </ul>
  <p><a href="/faq/">View All FAQs</a></p>
</section>

<section class="csa-shell csa-card">
  <h2>Ready to Visit Chestnut Square Academy?</h2>
  <p>We would love to meet your family, answer your questions, and help you find the right fit.</p>
  <div class="csa-cta-row">
    <a class="csa-btn csa-btn-primary" href="/contact-schedule-a-tour/">Schedule a Tour</a>
    <a class="csa-btn csa-btn-secondary" href="tel:[VERIFY PHONE]">Call Now [VERIFY]</a>
  </div>
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

  <p><a class="csa-btn csa-btn-primary" href="/contact-schedule-a-tour/">Schedule a Tour</a></p>
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

  <p><a class="csa-btn csa-btn-primary" href="/contact-schedule-a-tour/">Schedule a Tour</a></p>
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

  <p><a class="csa-btn csa-btn-primary" href="/contact-schedule-a-tour/">Want to visit in person? Schedule a Tour</a></p>
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
  <p>We aim to reply as soon as possible during business hours. For urgent questions, call us directly [VERIFY PHONE].</p>

  <p><a class="csa-btn csa-btn-primary" href="/contact-schedule-a-tour/">Schedule a Tour</a></p>
</section>
HTML;

	$contact = <<<HTML
<section class="csa-shell">
  <h1>Contact and Schedule a Tour</h1>
  <p>We know choosing care is a big decision. We are here to answer your questions and help you feel confident about next steps.</p>

  <div class="csa-grid">
    <article class="csa-card">
      <h2>Contact Details</h2>
      <p><strong>Address:</strong> 402 S Chestnut St, McKinney, TX 75069 [VERIFY]</p>
      <p><strong>Phone:</strong> [VERIFY]</p>
      <p><strong>Email:</strong> [VERIFY]</p>
      <p><strong>Hours:</strong> Monday-Friday, [VERIFY]</p>
      <h3>What happens next?</h3>
      <ol>
        <li>Submit the form with your preferred day/time.</li>
        <li>Our team will contact you to confirm your visit.</li>
        <li>Tour the school, meet staff, and discuss enrollment options.</li>
      </ol>
    </article>
    <article class="csa-card">
      <h2>Map</h2>
      <iframe class="csa-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=402+S+Chestnut+St,+McKinney,+TX+75069&output=embed" title="Map to Chestnut Square Academy"></iframe>
    </article>
  </div>

  <article class="csa-card">
    <h2>Schedule a Tour</h2>
    [csa_schedule_tour_form]
  </article>
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
	);
}

/**
 * Register schedule-tour shortcode.
 */
function csa_lk_register_shortcodes() {
	add_shortcode( 'csa_schedule_tour_form', 'csa_lk_render_tour_form' );
}
add_action( 'init', 'csa_lk_register_shortcodes' );

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
