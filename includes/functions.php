<?php

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;

/**
 * Create cron event for a given WP-CLI command
 *
 * @param string $command
 * @param int $timestamp Optional.
 * @return bool|\WP_Error
 */
function schedule_cli_command( $command, $timestamp = null ) {
	$command = validate_command( $command );

	if ( is_wp_error( $command ) ) {
		return $command;
	}

	if ( ! is_int( $timestamp ) ) {
		$timestamp = strtotime( '+30 seconds' );
	}

	$event_args = array( 'command' => $command, );

	$scheduled = wp_schedule_single_event( $timestamp, ACTION, $event_args );

	return false !== $scheduled;
}

/**
 * Validate WP-CLI command to be scheduled
 *
 * @param string $command
 * @return array|\WP_Error
 */
function validate_command( $command ) {
	$command = trim( $command );

	// Strip `wp` if included
	if ( 0 === stripos( $command, 'wp' ) ) {
		$command = trim( substr( $command, 2 ) );
	}

	// Block disallowed commands
	$first_command = explode( ' ', $command );
	$first_command = array_shift( $first_command );
	if ( ! is_command_allowed( $first_command ) ) {
		return new \WP_Error( sprintf( __( '%1$s: `%2$s` not allowed', 'wp-cli-cron-control-offload' ), MESSAGE_PREFIX, $first_command ) );
	}

	// Don't worry about the user WP-CLI runs as
	if ( false === stripos( $command, '--allow-root' ) ) {
		$command .= ' --allow-root';
	}

	// TODO: validate further

	// Nothing to run
	if ( empty( $command ) ) {
		return new \WP_Error( 'Invalid command provided' );
	}

	return $command;
}

/**
 * Check if command is allowed
 *
 * @param string $command
 * @return bool
 */
function is_command_allowed( $command ) {
	return ! in_array( $command, get_command_blacklist(), true ) && in_array( $command, get_command_whitelist(), true );
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
