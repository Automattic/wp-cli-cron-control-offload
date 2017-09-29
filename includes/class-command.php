<?php
/**
 * Class Command
 *
 * @package WP_CLI_Cron_Control_Offload
 */

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;

if ( ! defined( 'WP_CLI' ) || ! \WP_CLI ) {
	return;
}

use WP_CLI;
use WP_CLI_Command;

/**
 * Offload WP-CLI commands to cron
 */
class Command extends WP_CLI_Command {
	/**
	 * Create an event to run a given WP-CLI command
	 *
	 * @subcommand create
	 * @synopsis --command=<command> [--timestamp=<timestamp>]
	 * @param array $args Array of positional arguments.
	 * @param array $assoc_args Array of flags.
	 */
	public function create( $args, $assoc_args ) {
		$command = WP_CLI\Utils\get_flag_value( $assoc_args, 'command', '' );

		$timestamp = WP_CLI\Utils\get_flag_value( $assoc_args, 'timestamp', null );
		if ( is_numeric( $timestamp ) ) {
			$timestamp = absint( $timestamp );
		}

		$scheduled = schedule_cli_command( $command, $timestamp );

		if ( is_wp_error( $scheduled ) ) {
			WP_CLI::error( $scheduled->get_error_message() );
		}

		/* translators: 1: Human time difference, 2. Timestamp in UTC  */
		WP_CLI::success( sprintf( __( 'Command scheduled for %1$s from now (%2$s)', 'wp-cli-cron-control-offload' ), human_time_diff( $scheduled ), date( 'Y-m-d H:i:s T', $scheduled ) ) );
	}
}

WP_CLI::add_command( CLI_NAMESPACE, __NAMESPACE__ . '\Command' );
