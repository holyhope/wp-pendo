<?php
/**
 * Add plugin settings page to the admin panel.
 *
 * @package  Wp_Pendo
 * @internal Never define functions inside callbacks.
 * These functions could be run multiple times; this would result in a fatal error.
 */

/**
 * Custom option and settings
 */
function wppendo_settings_init() {
	register_setting( 'wppendo-page', 'wppendo_admin_tracking', array(
		'default'      => false,
		'show_in_rest' => false,
	) );

	add_settings_section(
		'wppendo_section_global',
		__( 'Global configuration', 'wppendo' ),
		'wppendo_section_global_callback',
		'wppendo-page'
	);

	add_settings_field(
		'wppendo_field_region',
		__( 'Track admin pages', 'wppendo' ),
		'wppendo_field_admin_tracking_cb',
		'wppendo-page',
		'wppendo_section_global',
		array(
			'label_for' => 'admin_tracking',
			'class'     => 'wppendo_row',
		)
	);

	register_setting(
		'wppendo-page',
		'wppendo_snippet_options',
		array(
			'default'      => array(
				'region'  => 'eu',
				'api_key' => '',
			),
			'show_in_rest' => false,
		)
	);

	add_settings_section(
		'wppendo_section_snippet',
		__( 'Snippet configuration', 'wppendo' ),
		'wppendo_section_snippet_callback',
		'wppendo-page'
	);

	add_settings_field(
		'wppendo_field_region',
		__( 'Region', 'wppendo' ),
		'wppendo_field_region_cb',
		'wppendo-page',
		'wppendo_section_snippet',
		array(
			'label_for' => 'region',
			'class'     => 'wppendo_row',
		)
	);

	add_settings_field(
		'wppendo_field_api_key',
		__( 'API key', 'wppendo' ),
		'wppendo_field_api_key_cb',
		'wppendo-page',
		'wppendo_section_snippet',
		array(
			'label_for' => 'api_key',
			'class'     => 'wppendo_row',
		)
	);
}

/**
 * Register our wppendo_settings_init to the admin_init action hook.
 */
add_action( 'admin_init', 'wppendo_settings_init' );


/**
 * Custom option and settings:
 *  - callback functions
 */


/**
 * Global section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function wppendo_section_global_callback( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>">
		<?php esc_html_e( 'Global settings.', 'wppendo' ); ?>
	</p>
	<?php
}


/**
 * Admin pages tracking field callback function.
 *
 * @param array $args   The field array, defining label_for.
 */
function wppendo_field_admin_tracking_cb( $args ) {
	// Get the value of the setting we've registered with register_setting()
	$value = get_option( 'wppendo_admin_tracking' );
	?>
	<input type="checkbox"
			id="<?php esc_attr_e( $args['label_for'] ); ?>"
			name="wppendo_admin_tracking"
			<?php checked( $value, 'on' ); ?> />
	<p class="description">
		<?php esc_html_e( 'Check the box if you want to track user on administration pages.', 'wppendo' ); ?>
	</p>
	<?php
}


/**
 * Snippet section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function wppendo_section_snippet_callback( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>">
		<?php esc_html_e( 'Customize snippet settings.', 'wppendo' ); ?>
	</p>
	<?php
}


/**
 * Region field callback function.
 *
 * @param array $args   The field array, defining label_for.
 */
function wppendo_field_region_cb( $args ) {
	$options = get_option( 'wppendo_snippet_options' );
	?>
	<select
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="wppendo_snippet_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
		<?php foreach ( wppendo_valid_regions() as $region => $label ) : ?>
		<option
				value="<?php echo esc_attr( $region ); ?>"
				<?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], $region, false ) ) : ( '' ); ?>>
			<?php echo esc_html( $label ); ?>
		</option>
		<?php endforeach; ?>
	</select>
	<p class="description">
		<?php esc_html_e( 'Select the region of your Pendo account.', 'wppendo' ); ?>
	</p>
	<?php
}


/**
 * API key field callback function.
 *
 * @param array $args   The field array, defining label_for.
 */
function wppendo_field_api_key_cb( $args ) {
	$options = get_option( 'wppendo_snippet_options' );
	?>
	<input id="<?php echo esc_attr( $args['label_for'] ); ?>"
			value="<?php echo esc_attr( $options[ $args['label_for'] ] ); ?>"
			name="wppendo_snippet_options[<?php echo esc_attr( $args['label_for'] ); ?>]" />
	<p class="description">
		<?php esc_html_e( 'The key used to fetch Pendo script.', 'wppendo' ); ?>
	</p>
	<?php
}


/**
 * Add the top level menu page.
 */
function wppendo_options_page() {
	add_options_page(
		__( 'Pendo settings', 'wppendo' ),
		__( 'Pendo', 'wppendo' ),
		'manage_options',
		'wppendo',
		'wppendo_options_page_html'
	);
}

/**
 * Register our wppendo_options_page to the admin_menu action hook.
 */
add_action( 'admin_menu', 'wppendo_options_page' );


/**
 * Top level menu callback function
 */
function wppendo_options_page_html() {
	// check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// show error/update messages.
	settings_errors( 'wppendo_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			// output security fields for the registered setting "wppendo".
			settings_fields( 'wppendo-page' );

			do_settings_sections( 'wppendo-page' );

			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
}
