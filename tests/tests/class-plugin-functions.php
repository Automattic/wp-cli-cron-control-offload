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
	function test_whitelist() {
		$this->assertTrue( WP_CLI_Cron_Control_Offload\is_command_allowed( 'wp post list' ) );
		$this->assertTrue( WP_CLI_Cron_Control_Offload\is_command_allowed( 'post list' ) );
	}

	/**
	 * Test blacklisted commands
	 */
	function test_blacklist() {
		$this->assertFalse( WP_CLI_Cron_Control_Offload\is_command_allowed( 'wp cli info' ) );
		$this->assertFalse( WP_CLI_Cron_Control_Offload\is_command_allowed( 'cli info' ) );
	}
}
