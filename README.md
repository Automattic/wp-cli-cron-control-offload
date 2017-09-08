# WP-CLI Cron Control Offload #
**Contributors:** ethitter, automattic  
**Tags:** wp-cli, cli, cron, cron control  
**Requires at least:** 4.8.1  
**Tested up to:** 4.9  
**Requires PHP:** 5.3  
**Stable tag:** 0.1.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Schedule WP-CLI tasks to run via Cron Control

## Description ##

Offload WP-CLI requests to cron, to be executed via (Cron Control)[https://github.com/Automattic/Cron-Control] and its CLI runner.

Provides a WP-CLI command to schedule these events. A UI is under consideration.

## Installation ##

1. Upload the `wp-cli-cron-control-offload` directry to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions ##

### Does this support custom WP-CLI commands? ###

Yes, after whitelisting them using the `wp_cli_cron_control_offload_command_whitelist` filter.

## Changelog ##

### 0.1.0 ###
* Initial release
