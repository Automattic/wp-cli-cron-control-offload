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
	 */
	function test_whitelist_using_validate_command() {
		$this->assertTrue( is_string( WP_CLI_Cron_Control_Offload\validate_command( 'wp post list' ) ) );
		$this->assertTrue( is_string( WP_CLI_Cron_Control_Offload\validate_command( 'post list' ) ) );
	}

	/**
	 * Test blacklisted command validation
	 */
	function test_blacklist_using_validate_command() {
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\validate_command( 'wp cli info' ) ) );
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\validate_command( 'cli info' ) ) );
	}

	/**
	 * Test scheduling several of the same allowed event
	 */
	function test_allowed_event_scheduling() {
		// Should succeed, returning a timestamp.
		$this->assertTrue( is_int( WP_CLI_Cron_Control_Offload\schedule_cli_command( 'wp post list' ) ) );

		// Should be blocked as a duplicate, thanks to Core's 10-minute lookahead.
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\schedule_cli_command( 'wp post list' ) ) );

		// Should also fail as normalization makes it a duplicate.
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\schedule_cli_command( 'post list' ) ) );
	}

	/**
	 * Test scheduling several of the same blocked event
	 */
	function test_blocked_event_scheduling() {
		// Should fail, is a blocked event.
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\schedule_cli_command( 'wp cli info' ) ) );

		// Should fail as a blocked event, would otherwise fail as a duplicate.
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\schedule_cli_command( 'wp cli info' ) ) );

		// Should also fail as a blocked event, though normalization would also block it as a duplicate.
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\schedule_cli_command( 'cli info' ) ) );
	}

	/**
	 * Test each blocked bash operator
	 */
	function test_for_invalid_bash_operators() {
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\validate_command( 'post list & date' ) ) );
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\validate_command( 'post list | date' ) ) );
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\validate_command( 'post list > /tmp/nope' ) ) );
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\validate_command( 'post list 2> /tmp/nope' ) ) );
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\validate_command( 'post list 1>&2 /tmp/nope' ) ) );
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\validate_command( 'post list 2>&1 /tmp/nope' ) ) );
		$this->assertTrue( is_wp_error( WP_CLI_Cron_Control_Offload\validate_command( 'post list &> /tmp/nope' ) ) );
	}
}
