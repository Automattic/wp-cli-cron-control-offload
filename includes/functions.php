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
	// Supported built-in commands
	$whitelist = array(
		'cache',
		'cap',
		'comment',
		'import',
		'media',
		'menu',
		'network',
		'option',
		'plugin',
		'post',
		'post-type',
		'rewrite',
		'role',
		'sidebar',
		'site',
		'super-admin',
		'taxonomy',
		'term',
		'theme',
		'transient',
		'user',
		'widget',
	);

	return apply_filters( 'wp_cli_cron_control_offload_subcommand_whitelist', $whitelist );
}

/**
 * Certain commands should never be allowed
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
		'package',
		'scaffold',
		'server',
	);
}
