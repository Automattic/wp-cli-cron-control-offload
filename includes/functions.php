<?php
/**
 * Common plugin functions
 *
 * @package WP_CLI_Cron_Control_Offload
 */

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;
use WP_Error;

/**
 * Create cron event for a given WP-CLI command
 *
 * @param string $command WP-CLI command to schedule.
 * @param int    $timestamp Optional. Unix timestamp to schedule command to run at.
 * @return int|WP_Error
 */
function schedule_cli_command( $command, $timestamp = null ) {
	$command = validate_command( $command );

	if ( is_wp_error( $command ) ) {
		return $command;
	}

	if ( ! is_int( $timestamp ) ) {
		$timestamp = strtotime( '+30 seconds' );
	}

	if ( $timestamp <= time() ) {
		return new WP_Error( 'invalid-timestamp', __( 'Timestamp is in the past.', 'wp-cli-cron-control-offload' ) );
	}

	$event_args = array( 'command' => $command );

	$scheduled = wp_schedule_single_event( $timestamp, ACTION, $event_args );

	if ( false === $scheduled ) {
		return new WP_Error( 'not-scheduled', __( 'Command may already be scheduled, or it was blocked via the `schedule_event` filter.', 'wp-cli-cron-control-offload' ) );
	}

	return $timestamp;
}

/**
 * Validate WP-CLI command to be scheduled
 *
 * @param string $command WP-CLI command to validate.
 * @return array|WP_Error
 */
function validate_command( $command ) {
	$command = trim( $command );

	// Strip `wp` if included.
	if ( 0 === stripos( $command, 'wp' ) ) {
		$command = trim( substr( $command, 2 ) );
	}

	// Block disallowed commands.
	$first_command = explode( ' ', $command );
	$first_command = array_shift( $first_command );
	if ( ! is_command_allowed( $first_command ) ) {
		return new WP_Error( 'blocked-command', sprintf( __( '`%1$s` not allowed', 'wp-cli-cron-control-offload' ), $first_command ) );
	}

	// Don't worry about the user WP-CLI runs as.
	if ( false === stripos( $command, '--allow-root' ) ) {
		$command .= ' --allow-root';
	}

	// Nothing to run.
	if ( empty( $command ) ) {
		return new WP_Error( 'invalid-command', 'Invalid command provided' );
	}

	return $command;
}

/**
 * Check if command is allowed
 *
 * @param string $command Top-level WP-CLI command to check against blacklist and whitelist.
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
	// Supported built-in commands.
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

	return apply_filters( 'wp_cli_cron_control_offload_command_whitelist', $whitelist );
}

/**
 * Certain commands should never be allowed
 *
 * @return array
 */
function get_command_blacklist() {
	// TODO: constant!
	return array(
		CLI_NAMESPACE, // Don't support scheduling loops.
		'cli',
		'config',
		'core',
		'cron',
		'db',
		'eval',
		'eval-file',
		'export',
		'import',
		'package',
		'scaffold',
		'server',
	);
}
