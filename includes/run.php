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
		trigger_error( 'Attempted to run event without WP-CLI loaded. (' . var_export( $command, true ) . ')', E_USER_WARNING );
		return;
	}

	if ( ! validate_args( $command ) ) {
		trigger_error( 'Attempted to run blocked WP-CLI command. (' . var_export( $command, true ) . ')', E_USER_WARNING );
		return;
	}

	$start = microtime( true );

	$output = \WP_CLI::runcommand( $command, array(
		'return' => 'all',
	) );

	$end = microtime( true );

	// Command failed
	if ( ! is_object( $output ) || is_wp_error( $output ) ) {
		trigger_error( 'WP-CLI command failed. (' . var_export( $command, true ) . ')', E_USER_WARNING );
		trigger_error( var_export( $output, true ), E_USER_WARNING );
		return;
	}

	// On success, reformat response for logging
	$output->command  = $command;
	$output->start    = $start;
	$output->end      = $end;
	$output->duration = $end - $start;

	$output = var_export( $output, true );
	$output = ACTION . ":\n{$output}";

	error_log( $output );
}
add_action( ACTION, __NAMESPACE__ . '\run_event' );
