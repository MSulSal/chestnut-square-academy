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
		'https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Nunito+Sans:wght@400;600;700&display=swap',
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
