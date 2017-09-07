<?php

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;

/**
 *
 */
function run_event( $args ) {
	if ( ! defined( 'WP_CLI' ) || ! \WP_CLI ) {
		trigger_error( 'Attempted to run event without WP-CLI loaded. ' . compact( $args ), E_USER_WARNING );
		return false;
	}

	// TODO: run event, sending output to error log
}
add_action( ACTION, __NAMESPACE__ . '\run_event' );
