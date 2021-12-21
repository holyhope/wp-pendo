<?php // Source: https://developer.wordpress.org/plugins/settings/custom-settings-page/
/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */
 
/**
 * custom option and settings
 */
function wppendo_settings_init() {
    // Register a new setting for "wppendo" page.
    register_setting( 'wppendo', 'wppendo_options' );
 
    // Register a new section in the "wppendo" page.
    add_settings_section(
        'wppendo_section_developers',
        __( 'The Matrix has you.', 'wppendo' ), 'wppendo_section_developers_callback',
        'wppendo'
    );
 
    // Register a new field in the "wppendo_section_developers" section, inside the "wppendo" page.
    add_settings_field(
        'wppendo_field_pill', // As of WP 4.6 this value is used only internally.
                                // Use $args' label_for to populate the id inside the callback.
            __( 'Pill', 'wppendo' ),
        'wppendo_field_pill_cb',
        'wppendo',
        'wppendo_section_developers',
        array(
            'label_for'         => 'wppendo_field_pill',
            'class'             => 'wppendo_row',
            'wppendo_custom_data' => 'custom',
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
 * Developers section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function wppendo_section_developers_callback( $args ) {
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'wppendo' ); ?></p>
    <?php
}
 
/**
 * Pill field callbakc function.
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 * @param array $args
 */
function wppendo_field_pill_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'wppendo_options' );
    ?>
    <select
            id="<?php echo esc_attr( $args['label_for'] ); ?>"
            data-custom="<?php echo esc_attr( $args['wppendo_custom_data'] ); ?>"
            name="wppendo_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
        <option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
            <?php esc_html_e( 'red pill', 'wppendo' ); ?>
        </option>
        <option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
            <?php esc_html_e( 'blue pill', 'wppendo' ); ?>
        </option>
    </select>
    <p class="description">
        <?php esc_html_e( 'You take the blue pill and the story ends. You wake in your bed and you believe whatever you want to believe.', 'wppendo' ); ?>
    </p>
    <p class="description">
        <?php esc_html_e( 'You take the red pill and you stay in Wonderland and I show you how deep the rabbit-hole goes.', 'wppendo' ); ?>
    </p>
    <?php
}
 
/**
 * Add the top level menu page.
 */
function wppendo_options_page() {
    add_menu_page(
        'wppendo',
        'wppendo Options',
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
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
 
    // add error/update messages
 
    // check if the user have submitted the settings
    // WordPress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'wppendo_messages', 'wppendo_message', __( 'Settings Saved', 'wppendo' ), 'updated' );
    }
 
    // show error/update messages
    settings_errors( 'wppendo_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "wppendo"
            settings_fields( 'wppendo' );
            // output setting sections and their fields
            // (sections are registered for "wppendo", each field is registered to a specific section)
            do_settings_sections( 'wppendo' );
            // output save settings button
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}