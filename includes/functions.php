<?php

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;

/**
 * Check if command is allowed
 *
 * @param string $command
 * @return bool
 */
function is_command_allowed( $command ) {
	return in_array( $command, get_command_whitelist(), true ) && ! in_array( $command, get_command_blacklist(), true );
}

/**
 * Most commands must be whitelisted
 *
 * @return array
 */
function get_command_whitelist() {
	// TODO: constant!
	// Supported built-in commands
	$whitelist = array(
		'cache',
		'cap',
		'comment',
		'cron-control',
		'cron-control-fixers',
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
	// TODO: constant!
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

	$scheduled = wp_schedule_single_event( strtotime( '+30 seconds' ), ACTION, array( 'command' => $event_args ) );

	return false !== $scheduled;
}

/**
 * Validate WP-CLI command to be scheduled
 *
 * @param string $args
 * @return array|\WP_Error
 */
function validate_args( $args ) {
	// Strip `wp` if included
	if ( 0 === stripos( $args, 'wp' ) ) {
		$args = trim( substr( $args, 2 ) );
	}

	// Block disallowed commands
	$command = explode( ' ', $args );
	$command = array_shift( $command );
	if ( ! is_command_allowed( $command ) ) {
		return new \WP_Error( "$command not allowed" );
	}

	// Don't worry about the user WP-CLI runs as
	if ( false === stripos( $args, '--allow-root' ) ) {
		$args .= ' --allow-root';
	}

	// TODO: validate further

	// Nothing to run
	if ( empty( $args ) ) {
		return new \WP_Error( 'Invalid command provided' );
	}

	return $args;
}
