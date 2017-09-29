<?php
/**
 * PHPUnit bootstrap file
 *
 * @package WP_CLI_Cron_Control_Offload
 */

/**
 * Constants needed to test whitelist/blacklist
 */
define( 'WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_WHITELIST', array(
	'post',
) );

define( 'WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_BLACKLIST', array(
	'cli',
) );

// Locate Core's test lib.
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/wp-cli-cron-control-offload.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
