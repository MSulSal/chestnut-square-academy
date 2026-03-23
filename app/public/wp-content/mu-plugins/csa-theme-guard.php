<?php
/**
 * Plugin Name: CSA Theme Guard
 * Description: Forces the production-safe Chestnut Square Academy theme when available.
 * Version: 1.0.0
 * Author: CSA Web Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get target theme slugs.
 *
 * @return array{stylesheet:string,template:string}
 */
function csa_theme_guard_get_target() {
	return array(
		'stylesheet' => 'hello-elementor-csa-site',
		'template'   => 'hello-elementor',
	);
}

/**
 * Check if CSA target child theme exists.
 *
 * @return bool
 */
function csa_theme_guard_target_exists() {
	$target = csa_theme_guard_get_target();
	$theme  = wp_get_theme( $target['stylesheet'] );

	return $theme instanceof WP_Theme && $theme->exists();
}

/**
 * Runtime stylesheet override so migrated DB values cannot force a wrong theme.
 *
 * @param mixed $pre_option Existing pre_option value.
 * @return mixed
 */
function csa_theme_guard_pre_option_stylesheet( $pre_option ) {
	if ( csa_theme_guard_target_exists() ) {
		$target = csa_theme_guard_get_target();
		return $target['stylesheet'];
	}

	return $pre_option;
}
add_filter( 'pre_option_stylesheet', 'csa_theme_guard_pre_option_stylesheet', 1 );

/**
 * Runtime template override so migrated DB values cannot force a wrong theme.
 *
 * @param mixed $pre_option Existing pre_option value.
 * @return mixed
 */
function csa_theme_guard_pre_option_template( $pre_option ) {
	if ( csa_theme_guard_target_exists() ) {
		$target = csa_theme_guard_get_target();
		return $target['template'];
	}

	return $pre_option;
}
add_filter( 'pre_option_template', 'csa_theme_guard_pre_option_template', 1 );

/**
 * Persist the corrected theme once in the database for long-term stability.
 */
function csa_theme_guard_persist_theme_once() {
	if ( ! csa_theme_guard_target_exists() ) {
		return;
	}

	$target               = csa_theme_guard_get_target();
	$current_stylesheet   = (string) get_option( 'stylesheet', '' );
	$current_template     = (string) get_option( 'template', '' );
	$needs_stylesheet_fix = $current_stylesheet !== $target['stylesheet'];
	$needs_template_fix   = $current_template !== $target['template'];

	if ( ! $needs_stylesheet_fix && ! $needs_template_fix ) {
		return;
	}

	update_option( 'stylesheet', $target['stylesheet'] );
	update_option( 'template', $target['template'] );
}
add_action( 'init', 'csa_theme_guard_persist_theme_once', 2 );

/**
 * Replace stale theme-folder URLs in stored content after migrations.
 *
 * @param string $content HTML content.
 * @return string
 */
function csa_theme_guard_replace_legacy_theme_paths( $content ) {
	if ( ! is_string( $content ) || '' === $content ) {
		return $content;
	}

	$replacements = array(
		'/wp-content/themes/hello-elementor-kiddie-mock/' => '/wp-content/themes/hello-elementor-csa-site/',
		'/wp-content/themes/hello-elementor-csa/'         => '/wp-content/themes/hello-elementor-csa-site/',
		'Kiddie Academy'                                  => 'Chestnut Square Academy',
		'kiddie academy'                                  => 'chestnut square academy',
		'5/6 years'                                       => '4/5 years',
		'5/6 year'                                        => '4/5 year',
		'5/6'                                             => '4/5',
	);

	return str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
}
add_filter( 'the_content', 'csa_theme_guard_replace_legacy_theme_paths', 1 );
add_filter( 'elementor/frontend/the_content', 'csa_theme_guard_replace_legacy_theme_paths', 1 );

/**
 * Ensure CSA body markers exist even if theme state drifts during migration.
 *
 * @param array<int,string> $classes Existing body classes.
 * @return array<int,string>
 */
function csa_theme_guard_body_classes( $classes ) {
	if ( ! is_array( $classes ) ) {
		$classes = array();
	}

	if ( ! in_array( 'csa-site', $classes, true ) ) {
		$classes[] = 'csa-site';
	}

	if ( is_front_page() && ! in_array( 'page-home', $classes, true ) ) {
		$classes[] = 'page-home';
	}

	return $classes;
}
add_filter( 'body_class', 'csa_theme_guard_body_classes', 5 );
