<?php
/**
 * Theme functions and definitions
 *
 * @package VaccinateOHTheme
 */

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
function vaccinateoh_child_enqueue_scripts() {
	wp_enqueue_style(
		'vaccinateoh-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'vaccinateoh-theme-style',
		],
		'1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'vaccinateoh_child_enqueue_scripts', 20 );
