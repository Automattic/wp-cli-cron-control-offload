<?php

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;

if ( ! defined( 'WP_CLI' ) || ! \WP_CLI ) {
	return;
}

use WP_CLI;

/**
 *
 */
function run_event( $args ) {
	return false;
}
add_action( ACTION, __NAMESPACE__ . '\run_event' );
