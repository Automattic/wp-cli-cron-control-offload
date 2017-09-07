<?php

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;

/**
 * Intended for non-interactive use, so all output ends up in the error log
 *
 * @param array $args
 * @return null
 */
function run_event( $args ) {
	if ( ! defined( 'WP_CLI' ) || ! \WP_CLI ) {
		// TODO: reschedule at least once or twice
		trigger_error( 'Attempted to run event without WP-CLI loaded. ' . var_export( $args, true ), E_USER_WARNING );
		return false;
	}

	// TODO: run event, sending output to error log
	trigger_error( var_export( $args, true ), E_USER_NOTICE );
}
add_action( ACTION, __NAMESPACE__ . '\run_event' );
