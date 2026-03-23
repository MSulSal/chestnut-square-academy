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
 * Keep the CSA child theme active even after migrations/imports.
 *
 * Some migrations preserve older theme slugs in options. This guard runs as an
 * MU plugin so it does not depend on normal plugin activation.
 */
function csa_theme_guard_enforce_theme() {
	$target_stylesheet = 'hello-elementor-csa-site';
	$target_template   = 'hello-elementor';

	$current_theme = wp_get_theme();
	if ( ! $current_theme instanceof WP_Theme ) {
		return;
	}

	$current_stylesheet = (string) $current_theme->get_stylesheet();
	$current_template   = (string) $current_theme->get_template();

	if ( $current_stylesheet === $target_stylesheet && $current_template === $target_template ) {
		return;
	}

	$target_theme = wp_get_theme( $target_stylesheet );
	if ( ! $target_theme instanceof WP_Theme || ! $target_theme->exists() ) {
		return;
	}

	switch_theme( $target_stylesheet, $target_template );
}
add_action( 'init', 'csa_theme_guard_enforce_theme', 1 );

