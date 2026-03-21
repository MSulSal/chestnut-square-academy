<?php
/**
 * Theme functions for Hello Elementor CSA.
 *
 * @package hello-elementor-csa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue theme stylesheet and fonts.
 */
function csa_theme_enqueue_assets() {
	wp_enqueue_style(
		'csa-google-fonts',
		'https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700&family=Nunito+Sans:wght@400;600;700;800&family=Quicksand:wght@600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'hello-elementor-csa-style',
		get_stylesheet_uri(),
		array( 'hello-elementor-theme-style' ),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'csa_theme_enqueue_assets', 20 );

/**
 * Theme supports.
 */
function csa_theme_setup() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'csa_theme_setup' );

/**
 * Hide default theme page titles on static pages.
 *
 * Page templates already include intentional in-content headings.
 *
 * @param bool $show_title Whether to show theme-level title.
 * @return bool
 */
function csa_theme_hide_default_page_titles( $show_title ) {
	if ( is_singular( 'page' ) ) {
		return false;
	}

	return $show_title;
}
add_filter( 'hello_elementor_page_title', 'csa_theme_hide_default_page_titles' );
