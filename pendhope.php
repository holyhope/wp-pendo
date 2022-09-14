<?php // phpcs:ignore Squiz.Commenting.FileComment.Missing
/**
 * Plugin Name:     Pendhope
 * Plugin URI:      https://github.com/holyhope/wp-pendo
 * Description:     Enable pendo.io on your blog
 * Author:          Pierre PÉRONNET
 * Author URI:      https://github.com/holyhope
 * Text Domain:     pendo
 * Domain Path:     /languages
 * Version:         0.2.3
 *
 * @package         Pendo
 */

require_once 'settings.php';


/**
 * Is the plugin ready to use.
 *
 * @param array $options   Plugin settings.
 */
function pendhope_is_ready( $options = array() ) {
	if ( empty( $options ) ) {
		$options = get_option( 'pendhope_snippet_options', array() );
	}

	return ! empty( $options['api_key'] );
}


/**
 * Get url of the pendo script.
 *
 * @param array $region   The Pendo region to get script from.
 * @param array $api_key  The Pendo API key.
 */
function pendhope_script_url( $region, $api_key ) {
	if ( PENDHOPE_US_REGION == $region ) {
		return apply_filters( 'pendhope_script_url', "https://cdn.pendo.io/agent/static/${api_key}/pendo.js" );
	}

	return apply_filters( 'pendhope_script_url', "https://cdn.${region}.pendo.io/agent/static/${api_key}/pendo.js" );
}


/**
 * Get the data of the current visitor.
 */
function pendhope_current_visitor() {
	if ( ! is_user_logged_in() ) {
		return array();
	}

	$user = wp_get_current_user();

	return apply_filters(
		'pendhope_visitor_data',
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
function pendhope_register_scripts() {
	$options = get_option(
		'pendhope_snippet_options',
		array(
			'region' => 'eu',
		)
	);

	if ( ! pendhope_is_ready( $options ) ) {
		return;
	}

	$url = pendhope_script_url( $options['region'], $options['api_key'] );
	wp_register_script( 'pendhope', $url, array(), '0.1.0', true );

	$settings = wp_json_encode(
		array(
			'visitor'       => pendhope_current_visitor(),
			'disableGuides' => false,
		)
	);
	wp_add_inline_script( 'pendhope', "pendhope.initialize($settings)" );
}

add_action( 'wp_enqueue_scripts', 'pendhope_register_scripts' );
add_action( 'login_enqueue_scripts', 'pendhope_register_scripts' );
add_action( 'admin_enqueue_scripts', 'pendhope_register_scripts' );


/**
 * Enqueue Pendo scripts.
 */
function pendhope_enqueue_scripts() {
	// check user capabilities.
	if ( current_user_can( 'pendhope_ignored' ) ) {
		return;
	}

	$options = get_option( 'pendhope_snippet_options', array() );

	if ( ! pendhope_is_ready( $options ) ) {
		return;
	}

	wp_enqueue_script( 'pendhope' );
}

add_action( 'wp_enqueue_scripts', 'pendhope_enqueue_scripts' );


/**
 * Enqueue Pendo scripts for admin and login pages.
 */
function pendhope_admin_enqueue_scripts() {
	$admin_tracking_enabled = get_option( 'pendhope_admin_tracking', false );

	if ( $admin_tracking_enabled ) {
		add_action( 'login_enqueue_scripts', 'pendhope_enqueue_scripts' );
		add_action( 'admin_enqueue_scripts', 'pendhope_enqueue_scripts' );
	}
}

add_action( 'init', 'pendhope_admin_enqueue_scripts' );


/**
 * Initialize admin side.
 */
function pendhope_admin_init() {
	$plugin_name = plugin_basename( __FILE__ );

	add_filter( "plugin_action_links_$plugin_name", 'pendhope_add_settings_link' );
}

add_action( 'admin_init', 'pendhope_admin_init' );
