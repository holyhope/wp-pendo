<?php // phpcs:ignore Squiz.Commenting.FileComment.Missing
/**
 * Plugin Name:     WP Pendo
 * Plugin URI:      https://github.com/holyhope/wp-pendo
 * Description:     Enable pendo.io on your blog
 * Author:          Pierre PÉRONNET
 * Author URI:      https://github.com/holyhope
 * Text Domain:     wp-pendo
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wp_Pendo
 */

require_once 'settings.php';


/**
 * Get url of the pendo script.
 *
 * @param array $region   The Pendo region to get script from.
 * @param array $api_key  The Pendo API key.
 */
function wppendo_script_url( $region, $api_key ) {
	return apply_filters( 'wppendo_script_url', "https://cdn.${region}.pendo.io/agent/static/${api_key}/pendo.js" );
}


/**
 * Get the data of the current visitor.
 */
function wppendo_current_visitor() {
	if ( ! is_user_logged_in() ) {
		return array();
	}

	$user = wp_get_current_user();

	return apply_filters(
		'wppendo_visitor_data',
		array(
			'id'        => $user->ID,
			'roles'     => $user->roles,
			'email'     => $user->user_email,
			'full_name' => $user->display_name,
		)
	);
}


/**
 * Register Pendo scripts.
 */
function wppendo_register_scripts() {
	$options = get_option( 'wppendo_snippet_options' );

	$url = wppendo_script_url( $options['region'], $options['api_key'] );
	wp_register_script( 'pendo', $url, array(), '0.1.0', true );

	$settings = wp_json_encode(
		array(
			'visitor'       => wppendo_current_visitor(),
			'disableGuides' => false,
		)
	);
	wp_add_inline_script( 'pendo', "pendo.initialize($settings)" );
}

add_action( 'init', 'wppendo_register_scripts' );


/**
 * Enqueue Pendo scripts for public pages.
 */
function wppendo_enqueue_scripts() {
	// check user capabilities.
	if ( current_user_can( 'wppendo_ignored' ) ) {
		return;
	}

	$options = get_option( 'wppendo_snippet_options' );
	if ( empty( $options['api_key'] ) ) {
		return;
	}

	wp_enqueue_script( 'pendo' );
}

add_action( 'wp_enqueue_scripts', 'wppendo_enqueue_scripts' );


/**
 * Enqueue Pendo scripts for admin pages.
 */
function wppendo_admin_enqueue_scripts() {
	$admin_tracking_enabled = get_option( 'wppendo_admin_tracking', false );

	if ( $admin_tracking_enabled ) {
		add_action( 'login_enqueue_scripts', 'wppendo_enqueue_scripts' );
		add_action( 'admin_enqueue_scripts', 'wppendo_enqueue_scripts' );
	}
}

add_action( 'init', 'wppendo_admin_enqueue_scripts' );


/**
 * Initialize admin side.
 */
function wppendo_admin_init() {
	$plugin_name = plugin_basename( __FILE__ );

	add_filter( "plugin_action_links_$plugin_name", 'wppendo_add_settings_link' );
}

add_action( 'admin_init', 'wppendo_admin_init' );
