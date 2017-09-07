<?php

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;

/**
 * Check if subcommand is allowed
 *
 * @param string $subcommand
 * @return bool
 */
function is_subcommand_allowed( $subcommand ) {
	return in_array( $subcommand, get_subcommand_whitelist(), true ) && ! in_array( $subcommand, get_subcommand_blacklist(), true );
}

/**
 * Most commands must be whitelisted
 *
 * @return array
 */
function get_subcommand_whitelist() {
	return array();
}

/**
 * Certain commands should never be supported
 *
 * @return array
 */
function get_subcommand_blacklist() {
	return array(
		'cli',
		'config',
		'core',
		'cron',
		'db',
		'eval',
		'eval-file',
		'scaffold',
		'server',
	);
}
