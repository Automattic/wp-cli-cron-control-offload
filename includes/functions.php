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
	$command = parse_command( $command );

	// Failed to parse command.
	if ( is_wp_error( $command ) ) {
		return $command;
	}

	if ( ! is_command_allowed( $command['positional_args'][0] ) ) {
		/* translators: 1: Disallowed command */
		return new WP_Error( 'blocked-command', sprintf( __( '`%1$s` not allowed', 'wp-cli-cron-control-offload' ), $command['positional_args'][0] ) );
	}

	$command = implode_parsed_command( $command );

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
		return _filter_list_allow_only_additions( \WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_WHITELIST, 'wp_cli_cron_control_offload_command_whitelist' );
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
		return _filter_list_allow_only_additions( \WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_BLACKLIST, 'wp_cli_cron_control_offload_command_blacklist' );
	}

	return apply_filters( 'wp_cli_cron_control_offload_command_blacklist', array() );
}

/**
 * Splits positional args from associative args.
 *
 * Adapted from `WP_CLI\Configurator\extract_assoc()`.
 *
 * @param array|string $command String or array of command to parse.
 * @return array|WP_Error
 */
function parse_command( $command ) {
	// Borrowed code expects an array, but a string is easier for our needs.
	if ( is_string( $command ) ) {
		$command = explode( ' ', $command );
	}

	// Bad request.
	if ( ! is_array( $command ) || empty( $command ) ) {
		return new WP_Error( 'command-parse-failed', __( 'Failed to parse requested command', 'wp-cli-cron-control-offload' ) );
	}

	// Don't care about existing keys, so reset to zero-indexed array.
	$command = array_values( $command );

	// `wp` is not part of the parsed command when WP-CLI is invoked.
	if ( 'wp' === $command[0] ) {
		unset( $command[0] );
	}

	// Match naming in what's borrowed from WP-CLI.
	$arguments = $command;

	// Start what's borrowed from WP-CLI. @codingStandardsIgnoreStart
	$positional_args = $assoc_args = $global_assoc = $local_assoc = array();

	foreach ( $arguments as $arg ) {
		$positional_arg = $assoc_arg = null;

		if ( preg_match( '|^--no-([^=]+)$|', $arg, $matches ) ) {
			$assoc_arg = array( $matches[1], false );
		} elseif ( preg_match( '|^--([^=]+)$|', $arg, $matches ) ) {
			$assoc_arg = array( $matches[1], true );
		} elseif ( preg_match( '|^--([^=]+)=(.*)|s', $arg, $matches ) ) {
			$assoc_arg = array( $matches[1], $matches[2] );
		} else {
			$positional = $arg;
		}

		if ( ! is_null( $assoc_arg ) ) {
			// Start addition.
			// Skip, allowing WP-CLI to inherit
			if ( 'allow-root' === $assoc_arg[0] ) {
				continue;
			}
			// End addition.
			$assoc_args[] = $assoc_arg;
			if ( count( $positional_args ) ) {
				$local_assoc[] = $assoc_arg;
			} else {
				$global_assoc[] = $assoc_arg;
			}
		} else if ( ! is_null( $positional ) ) {
			$positional_args[] = $positional;
		}

	}
	// End what's borrowed from WP-CLI. @codingStandardsIgnoreEnd

	// Nothing to do.
	if ( ! isset( $positional_args[0] ) || empty( $positional_args[0] ) ) {
		return new WP_Error( 'no-command-specified', __( 'No command was provided.', 'wp-cli-cron-control-offload' ) );
	}

	return compact( 'positional_args', 'assoc_args', 'global_assoc', 'local_assoc' );
}

/**
 * Restores a parsed command to a string WP-CLI can run
 *
 * @param array $command Parsed command to convert to string.
 * @return string|WP_Error
 */
function implode_parsed_command( $command ) {
	if ( ! is_array( $command ) || empty( $command['positional_args'] ) ) {
		return new WP_Error( 'no-command-specified', __( 'No command was provided.', 'wp-cli-cron-control-offload' ) );
	}

	$to_implode = array();

	if ( ! empty( $command['global_assoc'] ) ) {
		$global     = array_map( __NAMESPACE__ . '\_assoc_arg_array_to_string', $command['global_assoc'] );
		$to_implode = array_merge( $to_implode, $global );
	}

	$to_implode = array_merge( $to_implode, $command['positional_args'] );

	if ( ! empty( $command['local_assoc'] ) ) {
		$local      = array_map( __NAMESPACE__ . '\_assoc_arg_array_to_string', $command['local_assoc'] );
		$to_implode = array_merge( $to_implode, $local );
	}

	$imploded = trim( implode( ' ', $to_implode ) );

	if ( empty( $imploded ) ) {
		return new WP_Error( 'command-implode-failed', __( 'Failed to convert command array to string.', 'wp-cli-cron-control-offload' ) );
	}

	return $imploded;
}

/**
 * Convert an associative arg's array representation to a string for WP-CLI
 *
 * @param array $assoc_arg Associative arg to convert.
 * @return string
 */
function _assoc_arg_array_to_string( $assoc_arg ) {
	if ( true === $assoc_arg[1] ) {
		return '--' . $assoc_arg[0];
	} else {
		return sprintf( '--%1$s=%2$s', $assoc_arg[0], $assoc_arg[1] );
	}
}

/**
 * Allow whitelist or blacklist to be filtered, permitting ONLY additions
 *
 * @param array  $constant List value from constant, to be added to.
 * @param string $filter_tag String for list filter.
 * @return array
 */
function _filter_list_allow_only_additions( $constant, $filter_tag ) {
	$list = $constant;
	$list = array_values( $list ); // Keys are irrelevant, and dropping them reinforces the additive nature of the following filter.

	$additional = apply_filters( $filter_tag, array(), $list );

	if ( ! is_array( $additional ) || empty( $additional ) ) {
		return $constant;
	}

	$additional = array_values( $additional ); // Stop any funny business with string keys.

	$list = array_merge( $list, $additional );
	$list = array_unique( $list, SORT_STRING ); // Force type conversion to retain value from constant if filter tries funny business.

	return empty( $list ) ? $constant : $list;
}
