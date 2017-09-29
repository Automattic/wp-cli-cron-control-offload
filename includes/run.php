<?php
/**
 * Execution handling
 *
 * @package WP_CLI_Cron_Control_Offload
 */

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;
use WP_CLI;
use WP_Error;

/**
 * Intended for non-interactive use, so all output ends up in the error log
 *
 * @param string $command WP-CLI command to execute.
 * @return null
 */
function run_event( $command ) {
	if ( ! defined( 'WP_CLI' ) || ! \WP_CLI ) {
		$no_wp_cli = new WP_Error( 'no-wp-cli', __( 'Attempted to run event without WP-CLI loaded.', 'wp-cli-cron-control-offload' ) );
		do_action( 'wp_cli_cron_control_offload_run_error', $no_wp_cli, $command );
		return;
	}

	$validated = validate_command( $command );
	if ( is_wp_error( $validated ) ) {
		do_action( 'wp_cli_cron_control_offload_run_error', $validated, $command );
		return;
	}

	$start = microtime( true );

	$output = WP_CLI::runcommand( $command, array(
		'exit_error' => false, // Don't kill the cron process if the WP-CLI command fails, otherwise we can't capture the error.
		'launch'     => true,  // Don't reuse as we're in cron context.
		'return'     => 'all', // We want STDERR and the exit code, in addition to STDOUT.
	) );

	$end = microtime( true );

	// Command failed.
	if ( is_wp_error( $output ) ) {
		do_action( 'wp_cli_cron_control_offload_run_error', $output, $command );
		return;
	} elseif ( ! is_object( $output ) ) {
		$error = new WP_Error( 'command-run-unknown-failure', __( 'Command execution failed with an unexpected error.', 'wp-cli-cron-control-offload' ), $output );
		do_action( 'wp_cli_cron_control_offload_run_error', $error, $command );
		return;
	}

	// On success, reformat response for logging.
	$output->command  = $command;
	$output->start    = $start;
	$output->end      = $end;
	$output->duration = $end - $start;

	do_action( 'wp_cli_cron_control_offload_run_success', $output, $command );
}
add_action( ACTION, __NAMESPACE__ . '\run_event' );
