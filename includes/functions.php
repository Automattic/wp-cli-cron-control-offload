<?php

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;

/**
 * Check if subcommand is allowed
 *
 * @param string $subcommand
 * @return bool
 */
function is_subcommand_allowed( $subcommand ) {
	return in_array( $subcommand, get_command_whitelist(), true ) && ! in_array( $subcommand, get_command_blacklist(), true );
}

/**
 * Most commands must be whitelisted
 *
 * @return array
 */
function get_command_whitelist() {
	// Supported built-in commands
	$whitelist = array(
		'cache',
		'cap',
		'comment',
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
function get_command_blacklist() {
	return array(
		'cli',
		'config',
		'core',
		'cron',
		'db',
		'eval',
		'export',
		'eval-file',
		'import',
		'package',
		'scaffold',
		'server',
	);
}

/**
 * Create cron event for a given WP-CLI command
 *
 * return bool|\WP_Error
 */
function schedule_cli_command( $args ) {
	$event_args = validate_args( $args );

	if ( is_wp_error( $event_args ) ) {
		return $event_args;
	}

	$scheduled = wp_schedule_single_event( strtotime( '+30 seconds' ), ACTION, $event_args );

	return false !== $scheduled;
}

/**
 * Validate WP-CLI command to be scheduled
 *
 * @param array $args
 * @return array|\WP_Error
 */
function validate_args( $args ) {
	$validated_args = array();

	// TODO: validate
	// TODO: strip leading "wp"
	// TODO: check first positional argument against `is_command_allowed()`

	$validated_args['command'] = $args;

	if ( empty( $validated_args ) ) {
		return new \WP_Error( 'Arguments could not be parsed for validation.' );
	}

	return $validated_args;
}
