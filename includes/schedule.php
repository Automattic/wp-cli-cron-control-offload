<?php

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;

if ( ! defined( 'WP_CLI' ) || ! \WP_CLI ) {
	return false;
}

use WP_CLI;
use WP_CLI_Command;

/**
 * Offload WP-CLI commands to cron
 */
class CLI extends WP_CLI_Command {
	/**
	 * Create an event to run a given WP-CLI command
	 *
	 * @subcommand create
	 * @synopsis --command=<command> [--timestamp=<timestamp>]
	 */
	public function create( $args, $assoc_args ) {
		$command = WP_CLI\Utils\get_flag_value( $assoc_args, 'command', '' );
		$command = validate_command( $command );

		if ( is_wp_error( $command ) ) {
			WP_CLI::error( $command->get_error_message() );
		}

		$timestamp = WP_CLI\Utils\get_flag_value( $assoc_args, 'timestamp', null );
		if ( is_numeric( $timestamp ) ) {
			$timestamp = absint( $timestamp );
		}

		$scheduled = schedule_cli_command( $command, $timestamp );

		if ( is_wp_error( $scheduled ) ) {
			WP_CLI::error( $scheduled->get_error_message() );
		}

		WP_CLI::success( __( 'Command scheduled!', 'wp-cli-cron-control-offload' ) );
	}
}

WP_CLI::add_command( CLI_NAMESPACE, __NAMESPACE__ . '\CLI' );
