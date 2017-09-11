=== WP-CLI Cron Control Offload ===
Contributors: ethitter, automattic
Tags: wp-cli, cli, cron, cron control
Requires at least: 4.8.1
Tested up to: 4.9
Requires PHP: 7.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Schedule WP-CLI tasks to run via Cron Control

== Description ==

Offload WP-CLI requests to cron, to be executed via (Cron Control)[https://github.com/Automattic/Cron-Control] and its CLI runner.

Provides a WP-CLI command to schedule these events. A UI is under consideration.

== Installation ==

1. Upload the `wp-cli-cron-control-offload` directry to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Why is PHP 7 required? =

To support arrays in constants set using `define()`, PHP 7 is required. These are used to set whitelists and blacklists, though filters are also provided; see below.

= Does this support custom WP-CLI commands? =

Yes. By default, no restrictions are placed on what commands are supported, as those restrictions depend on the environment where this plugin is used. That said, see the following sections regarding support for whitelists and blacklists.

= Can I dynamically block commands? =

Yes, using the `wp_cli_cron_control_offload_is_command_allowed` filter. Note that the blacklist described below takes precedence over this filter (the filter is ignored). Additionally, if a whitelist is provided, the filter uses it as the default.

= Can commands be blocked or blacklisted? =

Yes, using either the `WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_BLACKLIST` constant or the `wp_cli_cron_control_offload_command_blacklist` filter. If defined, the constant takes precedence and the filter is ignored.

Regardless of whether the constant or filter is used, either should provide an array of top-level commands to permit:

```
array(
	'post',
	'site',
)
```

= Can commands be restricted or whitelisted? =

Yes, using either the `WP_CLI_CRON_CONTROL_OFFLOAD_COMMAND_WHITELIST` constant or the `wp_cli_cron_control_offload_command_whitelist` filter. If defined, the constant takes precedence and the filter is ignored.

Regardless of whether the constant or filter is used, either should provide an array of top-level commands to block:

```
array(
	'cli',
	'core',
	'eval',
	'eval-file',
)
```

== Changelog ==

= 0.1.0 =
* Initial release
