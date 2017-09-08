<?php

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;

/**
 * Intended for non-interactive use, so all output ends up in the error log
 *
 * @param string $command
 * @return null
 */
function run_event( $command ) {
	if ( ! defined( 'WP_CLI' ) || ! \WP_CLI ) {
		// TODO: reschedule at least once or twice
		trigger_error( sprintf( __( '%1$s: Attempted to run event without WP-CLI loaded. (%2$s)', 'wp-cli-cron-control-offload' ), MESSAGE_PREFIX, var_export( $command, true ) ), E_USER_WARNING );
		return;
	}

	if ( ! validate_args( $command ) ) {
		trigger_error( sprintf( __( '%1$s: Attempted to run blocked WP-CLI command. (%2$s)', 'wp-cli-cron-control-offload' ), MESSAGE_PREFIX, var_export( $command, true ) ), E_USER_WARNING );
		return;
	}

	$start = microtime( true );

	$output = \WP_CLI::runcommand( $command, array(
		'exit_error' => false, // Don't kill the cron process if the WP-CLI command fails, otherwise we can't capture the error
		'launch'     => true,  // Don't reuse as we're in cron context
		'return'     => 'all', // We want STDERR and the exit code, in addition to STDOUT
	) );

	$end = microtime( true );

	// Command failed
	if ( ! is_object( $output ) || is_wp_error( $output ) ) {
		trigger_error( sprintf( __( '%1$s: WP-CLI command failed. (%2$s)', 'wp-cli-cron-control-offload' ), MESSAGE_PREFIX, var_export( $command, true ) ), E_USER_WARNING );

		$message = is_wp_error( $output ) ? $output->get_error_message() : var_export( $output, true );
		trigger_error( $message, E_USER_WARNING );
		return;
	}

	// On success, reformat response for logging
	$output->command  = $command;
	$output->start    = $start;
	$output->end      = $end;
	$output->duration = $end - $start;

	$output = var_export( $output, true );
	$output = MESSAGE_PREFIX . ":\n{$output}";

	error_log( $output );
}
add_action( ACTION, __NAMESPACE__ . '\run_event' );
