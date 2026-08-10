<?php
/**
 * Links displayed for SVG Flags on the Plugins screen.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a direct route to the native SVG Flags settings page.
 */
class Plugin_Links {

	/**
	 * URL for the native settings screen.
	 *
	 * @var string
	 */
	private $settings_url;

	/**
	 * Freemius-managed upgrade URL.
	 *
	 * @var string
	 */
	private $upgrade_url;

	/**
	 * Whether the current installation is licensed as Pro.
	 *
	 * @var bool
	 */
	private $is_premium;

	/**
	 * Main class constructor.
	 *
	 * @param array<string, string> $module_roots      Common plugin paths.
	 * @param Constants             $custom_plugin_data Plugin configuration.
	 */
	public function __construct( $module_roots, $custom_plugin_data ) {
		$this->settings_url = $custom_plugin_data->main_settings_url;
		$this->upgrade_url  = $custom_plugin_data->freemius_upgrade_url;
		$this->is_premium   = $custom_plugin_data->is_premium;

		add_filter(
			'plugin_action_links_' . plugin_basename( $module_roots['file'] ),
			array( $this, 'add_settings_link' )
		);
	}

	/**
	 * Add the settings link before the standard plugin actions.
	 *
	 * @param array<int|string, string> $links Existing action links.
	 * @return array<int|string, string>
	 */
	public function add_settings_link( $links ) {
		array_unshift(
			$links,
			'<a href="' . esc_url( $this->settings_url ) . '">' . esc_html__( 'Get started', 'svg-flags-lite' ) . '</a>'
		);

		if ( ! $this->is_premium ) {
			$links['svg-flags-upgrade'] = '<a href="' . esc_url( $this->upgrade_url ) . '">' . esc_html__( 'Upgrade to Pro', 'svg-flags-lite' ) . '</a>';
		}

		return $links;
	}
}
