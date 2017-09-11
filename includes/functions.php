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

	$event_args = array(
		'command' => $command,
	);

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
 * @return string|WP_Error
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
		/* translators: 1: Disallowed command */
		return new WP_Error( 'blocked-command', sprintf( __( '`%1$s` not allowed', 'wp-cli-cron-control-offload' ), $first_command ) );
	}

	// Don't worry about the user WP-CLI runs as.
	if ( false === stripos( $command, '--allow-root' ) ) {
		$command .= ' --allow-root';
	}

	// TODO: validate further // @codingStandardsIgnoreLine

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
	// Command explicitly disallowed.
	if ( in_array( $command, get_command_blacklist(), true ) ) {
		return false;
	}

	// If there's a whitelist, default to it.
	if ( ! empty( get_command_whitelist() ) ) {
		add_filter( 'wp_cli_cron_control_offload_is_command_allowed', __NAMESPACE__ . '\command_is_whitelisted', 9, 2 );
	}

	return apply_filters( 'wp_cli_cron_control_offload_is_command_allowed', true, $command );
}

/**
 * Filter callback to check a command against a whitelist
 *
 * @param bool   $whitelisted Command is allowed.
 * @param string $command Command to check.
 * @return bool
 */
function command_is_whitelisted( $whitelisted, $command ) {
	return in_array( $command, get_command_whitelist(), true );
}

/**
 * Support a whitelist of commands
 *
 * @return array
 */
function get_command_whitelist() {
	if ( defined( 'WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_WHITELIST' ) && is_array( \WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_WHITELIST ) ) {
		return \WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_WHITELIST;
	}

	return apply_filters( 'wp_cli_cron_control_offload_command_whitelist', array() );
}

/**
 * Allow commands to be blocked
 *
 * @return array
 */
function get_command_blacklist() {
	if ( defined( 'WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_BLACKLIST' ) && is_array( \WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_BLACKLIST ) ) {
		return \WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_BLACKLIST;
	}

	return apply_filters( 'wp_cli_cron_control_offload_command_blacklist', array() );
}
