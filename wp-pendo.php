<?php
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

include_once 'settings.php';

function wppendo_script_url( $region, $apiKey ) {
    return apply_filters( 'wppendo_script_url', "https://cdn.${region}.pendo.io/agent/static/${apiKey}/pendo.js" );
}

function wppendo_visitor() {
    $user = wp_get_current_user();

    return apply_filters( 'wppendo_visitor_data', array(
        'id'        => $user->ID,
        'role'      => isset( $user->roles[0] ) ? $user->roles[0] : 'visitor',
        'email'     => $user->user_email,
        'full_name' => $user->display_name,
    ) );
}

function wppendo_register_scripts() {
    $options = get_option( 'wppendo_snippet_options' );

    $url = wppendo_script_url( $options['region'], $options['api_key'] );
    wp_register_script( 'pendo', $url, array(), '0.1.0', true );

    $settings = wp_json_encode( array(
        'visitor'       => wppendo_visitor(),
        'disableGuides' => false,
    ) );
    wp_add_inline_script( 'pendo', "pendo.initialize($settings)" );
}

add_action( 'init', 'wppendo_register_scripts' );

function wppendo_enqueue_scripts() {
    $options = get_option( 'wppendo_snippet_options' );
    if ( empty( $options['api_key'] ) ) {
        return;
    }

    wp_enqueue_script( 'pendo' );
}

add_action( 'wp_enqueue_scripts', 'wppendo_enqueue_scripts' );

function wppendo_admin_register_scripts() {
    $adminTrackingEnabled = get_option( 'wppendo_admin_tracking' );

    if ( $adminTrackingEnabled ) {
        add_action( 'login_enqueue_scripts', 'wppendo_enqueue_scripts' );
        add_action( 'admin_enqueue_scripts', 'wppendo_enqueue_scripts' );
    }
}

add_action( 'init', 'wppendo_admin_register_scripts' );