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
	register_setting(
		'wppendo-page',
		'wppendo_tracking',
		array(
			'default'           => array(
				'admin_pages'  => false,
				'hidden_roles' => array(),
			),
			'show_in_rest'      => false,
			'sanitize_callback' => 'wppendo_sanitize_tracking',
		)
	);

	add_settings_section(
		'wppendo_section_global',
		__( 'Global configuration', 'wppendo' ),
		'wppendo_section_global_callback',
		'wppendo-page'
	);

	add_settings_field(
		'wppendo_field_admin_pages',
		__( 'Track admin pages', 'wppendo' ),
		'wppendo_field_admin_pages_cb',
		'wppendo-page',
		'wppendo_section_global',
		array(
			'label_for' => 'admin_pages',
			'class'     => 'wppendo_row',
		)
	);

	add_settings_field(
		'wppendo_field_hidden_roles',
		__( 'Ignore visitors with role', 'wppendo' ),
		'wppendo_field_hidden_roles_cb',
		'wppendo-page',
		'wppendo_section_global',
		array(
			'label_for' => 'hidden_roles',
			'class'     => 'wppendo_row',
		)
	);

	register_setting(
		'wppendo-page',
		'wppendo_snippet_options',
		array(
			'default'           => array(
				'region'  => 'eu',
				'api_key' => '',
			),
			'show_in_rest'      => false,
			'sanitize_callback' => 'wppendo_sanitize_snippet_options',
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
 * Sanitize admin tracking settings.
 *
 * @param array $options   The settings value.
 */
function wppendo_sanitize_tracking( $options ) {
	$admin_pages = ! empty( $options['admin_pages'] ) && 'on' === $options['admin_pages'];

	if ( is_array( $options['hidden_roles'] ) ) {
		$hidden_roles = array_map( 'strval', $options['hidden_roles'] );
	} else {
		if ( ! empty( $options['hidden_roles'] ) ) {
			add_settings_error(
				'wppendo_messages',
				'invalid_roles_list',
				__( 'Invalid roles list', 'wppendo' ),
				'error'
			);
		}

		$hidden_roles = array();
	}

	$roles         = wp_roles();
	$invalid_roles = array();

	foreach ( $hidden_roles as $role ) {
		if ( $roles->is_role( $role ) ) {
			$roles->add_cap( $role, 'wppendo_ignored' );
			$role_setted[ $role ] = true;
		} else {
			array_push( $invalid_roles, $role );
		}
	}

	foreach ( $roles->roles as $role_name => $role ) {
		if ( ! isset( $role_setted[ $role_name ] ) ) {
			$roles->remove_cap( $role_name, 'wppendo_ignored' );
		}
	}

	if ( ! empty( $invalid_roles ) ) {
		add_settings_error(
			'wppendo_messages',
			'unknown_roles',
			/* translators: list of unknown roles */
			sprintf( _n( 'Unknown role: %s', 'Unknown roles: %s', count( $invalid_roles ), 'wppendo' ), implode( ', ', $invalid_roles ) ),
			'error'
		);
	}

	return array(
		'admin_pages'  => $admin_pages,
		'hidden_roles' => $hidden_roles,
	);
}


/**
 * Sanitize snippet settings.
 *
 * @param array $options   The settings value.
 */
function wppendo_sanitize_snippet_options( $options ) {
	$region  = empty( $options['region'] ) ? 'eu' : strval( $options['region'] );
	$api_key = empty( $options['api_key'] ) ? '' : strval( $options['api_key'] );

	$valid_regions = wppendo_valid_regions();

	if ( ! isset( $valid_regions[ $region ] ) ) {
		add_settings_error( 'wppendo_messages', 'settings_updated', __( 'Invalid region', 'wppendo' ), 'error' );

		$region = 'eu';
	}

	return array(
		'region'  => $region,
		'api_key' => trim( $api_key ),
	);
}


/**
 * Get valid Pendo region.
 */
function wppendo_valid_regions() {
	return apply_filters(
		'wppendo_valid_regions',
		array(
			'eu' => __( 'Europe', 'wppendo' ),
			'us' => __( 'United States', 'wppendo' ),
		)
	);
}


/**
 * Add updated settings message.
 */
function wppendo_add_updated_message() {
	add_settings_error( 'wppendo_messages', 'settings_updated', __( 'Settings Saved', 'wppendo' ), 'updated' );
}


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
function wppendo_field_admin_pages_cb( $args ) {
	$options = get_option( 'wppendo_tracking' );
	?>
	<input type="checkbox"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="wppendo_tracking[<?php echo esc_attr( $args['label_for'] ); ?>]"
			value="on"
			<?php checked( $options['admin_pages'], true ); ?> />
	<p class="description">
		<?php esc_html_e( 'Check the box if you want to track user on administration pages.', 'wppendo' ); ?>
	</p>
	<?php
}


/**
 * Hidden roles field callback function.
 *
 * @param array $args   The field array, defining label_for.
 */
function wppendo_field_hidden_roles_cb( $args ) {
	$options   = get_option( 'wppendo_tracking' );
	$roles     = wp_roles();
	$need_sync = false;

	?>
	<ul id="<?php echo esc_attr( $args['label_for'] ); ?>">
		<?php
		foreach ( $roles->roles as $role_name => $role ) :
			$expected_ignored = in_array( $role_name, $options['hidden_roles'], true );
			$label_for        = $args['label_for'] . '-' . $role_name;
			$ignored          = isset( $role['capabilities']['wppendo_ignored'] ) && true === $role['capabilities']['wppendo_ignored'];
			$need_sync        = $need_sync || ( $expected_ignored && ! $ignored ) || ( $ignored && ! $expected_ignored );
			?>
			<li>
				<input type="checkbox"
						id="<?php echo esc_attr( $label_for ); ?>"
						name="wppendo_tracking[<?php echo esc_attr( $args['label_for'] ); ?>][]"
						value="<?php echo esc_attr( $role_name ); ?>"
						<?php checked( $expected_ignored, true ); ?> />
				<label for="<?php echo esc_attr( $label_for ); ?>">
					<?php echo esc_html( translate_user_role( $role['name'] ) ); ?>
				</label>
			</li>
		<?php endforeach; ?>
	</ul>
	<p class="description">
		<?php esc_html_e( 'Select roles you do not want to track.', 'wppendo' ); ?>
	</p>
	<?php
	if ( $need_sync ) :
		?>
		<p class="description warning">
			<span class="dashicons dashicons-warning"></span>
			<?php esc_html_e( 'Some roles are not up to date. Please save settings to fix the issue.', 'wppendo' ); ?>
		</p>
		<?php
	endif;
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
