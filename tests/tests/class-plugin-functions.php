<?php
/**
 * Test plugin's common functions
 *
 * @package WP_CLI_Cron_Control_Offload
 */

namespace Automattic\WP\WP_CLI_Cron_Control_Offload\Tests;
use Automattic\WP\WP_CLI_Cron_Control_Offload;
use WP_UnitTestCase;

/**
 * Core function tests
 */
class Plugin_Functions extends WP_UnitTestCase {
	/**
	 * Prepare test environment
	 */
	function setUp() {
		parent::setUp();

		// make sure the schedule is clear.
		_set_cron_array( array() );
	}

	/**
	 * Clean up after our tests
	 */
	function tearDown() {
		// make sure the schedule is clear.
		_set_cron_array( array() );

		parent::tearDown();
	}

	/**
	 * Test whitelisted commands
	 */
	function test_whitelist_using_is_command_allowed() {
		$this->assertTrue( WP_CLI_Cron_Control_Offload\is_command_allowed( 'post' ) );
	}

	/**
	 * Test blacklisted commands
	 */
	function test_blacklist_using_is_command_allowed() {
		$this->assertFalse( WP_CLI_Cron_Control_Offload\is_command_allowed( 'cli' ) );
	}

	/**
	 * Test whitelisted command validation
	 *
	 * @dataProvider whitelisted_command_provider
	 *
	 * @param string $command Command to test.
	 */
	function test_whitelist_using_validate_command( $command ) {
		$this->assertTrue( is_string( WP_CLI_Cron_Control_Offload\validate_command( $command ) ) );
	}

	/**
	 * Data provider for command whitelisting
	 *
	 * @return array
	 */
	function whitelisted_command_provider() {
		return array(
			array( 'wp post list' ),
			array( 'post list' ),
		);
	}

	/**
	 * Test blacklisted command validation
	 *
	 * @dataProvider blacklisted_command_provider
	 *
	 * @param string $command Command to test.
	 */
	function test_blacklist_using_validate_command( $command ) {
		$this->assertWPError( WP_CLI_Cron_Control_Offload\validate_command( $command ) );
	}

	/**
	 * Data provider for command blacklisting
	 *
	 * @return array
	 */
	function blacklisted_command_provider() {
		return array(
			array( 'wp cli info' ),
			array( 'cli info' ),
		);
	}

	/**
	 * Test scheduling several of the same allowed event
	 */
	function test_allowed_event_scheduling() {
		// Should succeed, returning a timestamp.
		$this->assertTrue( is_int( WP_CLI_Cron_Control_Offload\schedule_cli_command( 'wp post list' ) ) );

		// Should be blocked as a duplicate, thanks to Core's 10-minute lookahead.
		$this->assertWPError( WP_CLI_Cron_Control_Offload\schedule_cli_command( 'wp post list' ) );

		// Should also fail as normalization makes it a duplicate.
		$this->assertWPError( WP_CLI_Cron_Control_Offload\schedule_cli_command( 'post list' ) );
	}

	/**
	 * Test scheduling several of the same blocked event
	 *
	 * @dataProvider blocked_events_provider
	 *
	 * @param string $command Command to test.
	 */
	function test_blocked_event_scheduling( $command ) {
		$this->assertWPError( WP_CLI_Cron_Control_Offload\schedule_cli_command( $command ) );
	}

	/**
	 * Data provider for blocked-event scheduling
	 *
	 * @return array
	 */
	function blocked_events_provider() {
		return array(
			array( 'wp cli info' ), // Should fail, is a blocked event.
			array( 'wp cli info' ), // Should fail as a blocked event, would otherwise fail as a duplicate.
			array( 'cli info' ), // Should also fail as a blocked event, though normalization would also block it as a duplicate.
		);
	}

	/**
	 * Test each blocked bash operator
	 *
	 * @dataProvider invalid_bash_operators_provider
	 *
	 * @param string $command Command to test.
	 */
	function test_for_invalid_bash_operators( $command ) {
		$this->assertWPError( WP_CLI_Cron_Control_Offload\validate_command( $command ) );
	}

	/**
	 * Data provider for unsupported bash operators
	 *
	 * @return array
	 */
	function invalid_bash_operators_provider() {
		return array(
			array( 'post list & date' ),
			array( 'post list | date' ),
			array( 'post list > /tmp/nope' ),
			array( 'post list 2> /tmp/nope' ),
			array( 'post list 1>&2 /tmp/nope' ),
			array( 'post list 2>&1 /tmp/nope' ),
			array( 'post list &> /tmp/nope' ),
		);
	}
}
