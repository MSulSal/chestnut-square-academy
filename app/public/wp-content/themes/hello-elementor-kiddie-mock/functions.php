<?php
/**
 * Kiddie mock child theme functions.
 *
 * @package HelloElementorKiddieMock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
		'kiddie-mock-overrides',
		get_stylesheet_directory_uri() . '/assets/css/kiddie-overrides.css',
		array( 'kiddie-mock-core' ),
		$theme_version
	);

	wp_enqueue_style(
		'kiddie-mock-theme',
		get_stylesheet_uri(),
		array( 'kiddie-mock-overrides' ),
		$theme_version
	);

	wp_enqueue_script(
		'kiddie-mock-js',
		get_stylesheet_directory_uri() . '/assets/js/kiddie-mock.js',
		array(),
		$theme_version,
		true
	);
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
