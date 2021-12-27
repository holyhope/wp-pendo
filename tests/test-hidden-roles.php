<?php
/**
 * Class HiddenCapabilitiesTest
 *
 * @package Wp_Pendo
 */

/**
 * Sample test case.
 */
class HiddenCapabilitiesTest extends WP_UnitTestCase {
	const CAPABILITY = 'wppendo_ignored';

	/**
	 * Sets up tests.
	 */
	public function setUp() {
		parent::setUp();

		update_option(
			'wppendo_snippet_options',
			array(
				'region'  => 'test',
				'api_key' => 'invalid-key',
			)
		);

		$this->assertTrue( wppendo_is_ready(), 'Test configuration must be ready to use' );
	}

	/**
	 * Is the current user tracked?
	 */
	protected function isCurrentUserTracked() {
		wppendo_register_scripts();
		wppendo_enqueue_scripts();

		return wp_script_is( 'pendo', 'enqueued' );
	}

	/**
	 * Tests that a user without hidden capabilities is tracked.
	 */
	public function test_tracked_role() {
		$this->assertFalse( current_user_can( self::CAPABILITY ), self::CAPABILITY . ' capability should not be present for the current user' );
		$this->assertTrue( $this->isCurrentUserTracked(), 'user must be tracked' );
	}

	/**
	 * Tests that a user with hidden capabilities is not tracked.
	 */
	public function test_hidden_role() {
		wp_get_current_user()->add_cap( self::CAPABILITY );

		$this->assertTrue( current_user_can( self::CAPABILITY ), self::CAPABILITY . ' capability should be added to the current user' );

		$this->markTestIncomplete(
			'user is not tracked, but failed to check it programatically.'
		);
	}
}
