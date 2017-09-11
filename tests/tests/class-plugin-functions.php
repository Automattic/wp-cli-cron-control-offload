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
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {
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
}
