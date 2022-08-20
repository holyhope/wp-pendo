<?php
/**
 * Add plugin settings page to the admin panel.
 *
 * @package  Pendo
 * @internal Never define functions inside callbacks.
 * These functions could be run multiple times; this would result in a fatal error.
 */


/**
 * Define constant for each available region
 */
define( 'PENDHOPE_US_REGION', 'us');
define( 'PENDHOPE_EU_REGION', 'eu');


/**
 * Custom option and settings
 */
function pendhope_settings_init() {
	register_setting(
		'pendhope-page',
		'pendhope_tracking',
		array(
			'default'           => array(
				'admin_pages' => false,
			),
			'show_in_rest'      => false,
			'sanitize_callback' => 'pendhope_sanitize_tracking',
		)
	);

	add_settings_section(
		'pendhope_section_global',
		__( 'Global configuration', 'pendhope' ),
		'pendhope_section_global_callback',
		'pendhope-page'
	);

	add_settings_field(
		'pendhope_field_admin_pages',
		__( 'Track admin pages', 'pendhope' ),
		'pendhope_field_admin_pages_cb',
		'pendhope-page',
		'pendhope_section_global',
		array(
			'label_for' => 'admin_pages',
			'class'     => 'pendhope_row',
		)
	);

	add_settings_field(
		'pendhope_field_hidden_roles',
		__( 'Ignore visitors with role', 'pendhope' ),
		'pendhope_field_hidden_roles_cb',
		'pendhope-page',
		'pendhope_section_global',
		array(
			'label_for' => 'hidden_roles',
			'class'     => 'pendhope_row',
		)
	);

	register_setting(
		'pendhope-page',
		'pendhope_snippet_options',
		array(
			'default'           => array(
				'region'  => PENDHOPE_EU_REGION,
				'api_key' => '',
			),
			'show_in_rest'      => false,
			'sanitize_callback' => 'pendhope_sanitize_snippet_options',
		)
	);

	add_settings_section(
		'pendhope_section_snippet',
		__( 'Snippet configuration', 'pendhope' ),
		'pendhope_section_snippet_callback',
		'pendhope-page'
	);

	add_settings_field(
		'pendhope_field_region',
		__( 'Region', 'pendhope' ),
		'pendhope_field_region_cb',
		'pendhope-page',
		'pendhope_section_snippet',
		array(
			'label_for' => 'region',
			'class'     => 'pendhope_row',
		)
	);

	add_settings_field(
		'pendhope_field_api_key',
		__( 'API key', 'pendhope' ),
		'pendhope_field_api_key_cb',
		'pendhope-page',
		'pendhope_section_snippet',
		array(
			'label_for' => 'api_key',
			'class'     => 'pendhope_row',
		)
	);
}

/**
 * Register our pendhope_settings_init to the admin_init action hook.
 */
add_action( 'admin_init', 'pendhope_settings_init' );


/**
 * Sanitize admin tracking settings.
 *
 * @param array $options   The settings value.
 */
function pendhope_sanitize_tracking( $options ) {
	$admin_pages = ! empty( $options['admin_pages'] ) && 'on' === $options['admin_pages'];

	if ( is_array( $options['hidden_roles'] ) ) {
		$hidden_roles = array_map( 'strval', $options['hidden_roles'] );
	} else {
		if ( ! empty( $options['hidden_roles'] ) ) {
			add_settings_error(
				'pendhope_messages',
				'invalid_roles_list',
				__( 'Invalid roles list', 'pendhope' ),
				'error'
			);
		}

		$hidden_roles = array();
	}

	$roles         = wp_roles();
	$invalid_roles = array();

	foreach ( $hidden_roles as $role ) {
		if ( $roles->is_role( $role ) ) {
			$roles->add_cap( $role, 'pendhope_ignored' );
			$role_setted[ $role ] = true;
		} else {
			array_push( $invalid_roles, $role );
		}
	}

	foreach ( $roles->roles as $role_name => $role ) {
		if ( ! isset( $role_setted[ $role_name ] ) ) {
			$roles->remove_cap( $role_name, 'pendhope_ignored' );
		}
	}

	if ( ! empty( $invalid_roles ) ) {
		add_settings_error(
			'pendhope_messages',
			'unknown_roles',
			/* translators: list of unknown roles */
			sprintf( _n( 'Role %s does not exist.', 'Roles %s do not exist.', count( $invalid_roles ), 'pendhope' ), implode( ', ', $invalid_roles ) ),
			'error'
		);
	}

	return array(
		'admin_pages' => $admin_pages,
	);
}


/**
 * Sanitize snippet settings.
 *
 * @param array $options   The settings value.
 */
function pendhope_sanitize_snippet_options( $options ) {
	$region  = empty( $options['region'] ) ? PENDHOPE_EU_REGION : strval( $options['region'] );
	$api_key = empty( $options['api_key'] ) ? '' : strval( $options['api_key'] );

	$valid_regions = pendhope_valid_regions();

	if ( ! isset( $valid_regions[ $region ] ) ) {
		add_settings_error( 'pendhope_messages', 'settings_updated', __( 'Invalid region', 'pendhope' ), 'error' );

		$region = PENDHOPE_EU_REGION;
	}

	return array(
		'region'  => $region,
		'api_key' => trim( $api_key ),
	);
}


/**
 * Get valid Pendo region.
 */
function pendhope_valid_regions() {
	return apply_filters(
		'pendhope_valid_regions',
		array(
			PENDHOPE_EU_REGION => __( 'Europe', 'pendhope' ),
			PENDHOPE_US_REGION => __( 'United States', 'pendhope' ),
		)
	);
}


/**
 * Add updated settings message.
 */
function pendhope_add_updated_message() {
	add_settings_error( 'pendhope_messages', 'settings_updated', __( 'Settings Saved', 'pendhope' ), 'updated' );
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
function pendhope_section_global_callback( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>">
		<?php esc_html_e( 'Global settings.', 'pendhope' ); ?>
	</p>
	<?php
}


/**
 * Admin pages tracking field callback function.
 *
 * @param array $args   The field array, defining label_for.
 */
function pendhope_field_admin_pages_cb( $args ) {
	$options = get_option( 'pendhope_tracking' );
	?>
	<input type="checkbox"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="pendhope_tracking[<?php echo esc_attr( $args['label_for'] ); ?>]"
			value="on"
			<?php checked( $options['admin_pages'], true ); ?> />
	<p class="description">
		<?php esc_html_e( 'Check the box if you want to track user on administration pages.', 'pendhope' ); ?>
	</p>
	<?php
}


/**
 * Hidden roles field callback function.
 *
 * @param array $args   The field array, defining label_for.
 */
function pendhope_field_hidden_roles_cb( $args ) {
	$roles = wp_roles();

	?>
	<ul id="<?php echo esc_attr( $args['label_for'] ); ?>">
		<?php
		foreach ( $roles->roles as $role_name => $role ) :
			$label_for = $args['label_for'] . '-' . $role_name;
			$ignored   = isset( $role['capabilities']['pendhope_ignored'] ) && true === $role['capabilities']['pendhope_ignored'];
			?>
			<li>
				<input type="checkbox"
						id="<?php echo esc_attr( $label_for ); ?>"
						name="pendhope_tracking[<?php echo esc_attr( $args['label_for'] ); ?>][]"
						value="<?php echo esc_attr( $role_name ); ?>"
						<?php checked( $ignored, true ); ?> />
				<label for="<?php echo esc_attr( $label_for ); ?>">
					<?php echo esc_html( translate_user_role( $role['name'] ) ); ?>
				</label>
			</li>
		<?php endforeach; ?>
	</ul>
	<p class="description">
		<?php esc_html_e( 'Select roles you do not want to track.', 'pendhope' ); ?>
	</p>
	<?php
}


/**
 * Snippet section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function pendhope_section_snippet_callback( $args ) {
	?>
	<p id="<?php echo esc_attr( $args['id'] ); ?>">
		<?php esc_html_e( 'Customize snippet settings.', 'pendhope' ); ?>
	</p>
	<?php
}


/**
 * Region field callback function.
 *
 * @param array $args   The field array, defining label_for.
 */
function pendhope_field_region_cb( $args ) {
	$options = get_option( 'pendhope_snippet_options' );
	?>
	<select
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="pendhope_snippet_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
		<?php foreach ( pendhope_valid_regions() as $region => $label ) : ?>
		<option
				value="<?php echo esc_attr( $region ); ?>"
				<?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], $region, false ) ) : ( '' ); ?>>
			<?php echo esc_html( $label ); ?>
		</option>
		<?php endforeach; ?>
	</select>
	<p class="description">
		<?php esc_html_e( 'Select the region of your Pendo account.', 'pendhope' ); ?>
	</p>
	<?php
}


/**
 * API key field callback function.
 *
 * @param array $args   The field array, defining label_for.
 */
function pendhope_field_api_key_cb( $args ) {
	$options = get_option( 'pendhope_snippet_options' );
	?>
	<input id="<?php echo esc_attr( $args['label_for'] ); ?>"
			value="<?php echo esc_attr( $options[ $args['label_for'] ] ); ?>"
			name="pendhope_snippet_options[<?php echo esc_attr( $args['label_for'] ); ?>]" />
	<p class="description">
		<?php esc_html_e( 'The key used to fetch Pendo script.', 'pendhope' ); ?>
	</p>
	<?php
}


/**
 * Add the top level menu page.
 */
function pendhope_options_page() {
	add_options_page(
		__( 'Pendhope settings', 'pendhope' ),
		__( 'Pendhope', 'pendhope' ),
		'manage_options',
		'pendhope',
		'pendhope_options_page_html'
	);
}

/**
 * Register our pendhope_options_page to the admin_menu action hook.
 */
add_action( 'admin_menu', 'pendhope_options_page' );


/**
 * Top level menu callback function
 */
function pendhope_options_page_html() {
	// check user capabilities.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// show error/update messages.
	settings_errors( 'pendhope_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			// output security fields for the registered setting "pendhope".
			settings_fields( 'pendhope-page' );

			do_settings_sections( 'pendhope-page' );

			submit_button( 'Save Settings' );
			?>
		</form>
	</div>
	<?php
}


/**
 * Add settings shortcut to plugin links.
 *
 * @param array $links   The list of links related to the plugin.
 */
function pendhope_add_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=pendhope">' . __( 'Settings', 'pendhope' ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}
