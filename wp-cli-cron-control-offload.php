<?php
/**
 * Plugin Name:     WP-CLI Cron Control Offload
 * Plugin URI:      https://automattic.com/
 * Description:     Schedule WP-CLI tasks to run via Cron Control
 * Author:          Erick Hitter, Automattic
 * Author URI:      https://vip.wordpress.com/
 * Text Domain:     wp-cli-cron-control-offload
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         WP_CLI_Cron_Control_Offload
 */

namespace Automattic\WP\WP_CLI_Cron_Control_Offload;

const ACTION         = 'wp_cli_cron_control_offload';
const CLI_NAMESPACE  = 'cli-cron-offload';
const MESSAGE_PREFIX = 'WP-CLI via Cron';

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/class-cli.php';
require_once __DIR__ . '/includes/run.php';
