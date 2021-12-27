<?php
/**
 * Class HiddenCapabilitiesTest
 *
 * @package Wp_Pendo
 */

/**
 * Sample test case.
 */
class UtilsTest extends WP_UnitTestCase {
	/**
	 * Tests that the plugin is ready to use with a valid configuration.
	 */
	public function test_ready_no_args() {
		update_option(
			'wppendo_snippet_options',
			array(
				'region'  => 'test',
				'api_key' => 'test-key',
			)
		);

		$this->assertTrue( wppendo_is_ready() );
	}

	/**
	 * Tests that the plugin is not ready to use with an invalid configuration.
	 */
	public function test_not_ready_no_args() {
		update_option(
			'wppendo_snippet_options',
			array(
				'region'  => 'test',
				'api_key' => '',
			)
		);

		$this->assertFalse( wppendo_is_ready() );
	}

	/**
	 * Tests that the plugin is ready to use with a valid configuration.
	 */
	public function test_ready_with_args() {
		$options = array(
			'region'  => 'test',
			'api_key' => 'test-key',
		);

		$this->assertTrue( wppendo_is_ready( $options ) );
	}

	/**
	 * Tests that the plugin is not ready to use with an invalid configuration.
	 */
	public function test_not_ready_with_args() {
		$options = array(
			'region'  => 'test',
			'api_key' => '',
		);

		$this->assertFalse( wppendo_is_ready( $options ) );
	}

	/**
	 * Tests that the plugin is ready to use with a valid configuration.
	 */
	public function test_not_ready_empty_options_no_args() {
		$this->assertFalse( wppendo_is_ready() );
	}

	/**
	 * Tests that the plugin is not ready to use with an invalid configuration.
	 */
	public function test_not_ready_empty_options_with_args() {
		$this->assertFalse( wppendo_is_ready( array() ) );
	}
}
