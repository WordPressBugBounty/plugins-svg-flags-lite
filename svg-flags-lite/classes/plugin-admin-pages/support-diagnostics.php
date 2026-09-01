<?php
/**
 * Privacy-safe SVG Flags support diagnostics.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Builds a support summary without site content, URLs, options or licence data.
 */
class Support_Diagnostics {

	/**
	 * Plugin headers.
	 *
	 * @var array<string, mixed>
	 */
	private $plugin_data;

	/**
	 * Plugin configuration.
	 *
	 * @var Constants
	 */
	private $configuration;

	/**
	 * Main class constructor.
	 *
	 * @param array<string, mixed> $plugin_data Plugin headers.
	 * @param Constants            $configuration Plugin configuration.
	 */
	public function __construct( $plugin_data, $configuration ) {
		$this->plugin_data   = $plugin_data;
		$this->configuration = $configuration;
	}

	/**
	 * Return the complete support summary.
	 *
	 * @return string
	 */
	public function get_summary() {
		$theme   = wp_get_theme();
		$plugins = $this->get_active_plugin_names();
		$blocks  = $this->get_registered_blocks();
		$lines   = array(
			'SVG Flags support summary',
			'SVG Flags version: ' . ( $this->plugin_data['Version'] ?? 'Unknown' ),
			'SVG Flags tier: ' . ( $this->configuration->is_premium ? 'Pro' : 'Free' ),
			'Flag library: flag-icons 7.5.0 (' . count( $this->configuration->country_codes ) . ' entries)',
			'WordPress version: ' . get_bloginfo( 'version' ),
			'PHP version: ' . PHP_VERSION,
			'Locale: ' . determine_locale(),
			'Multisite: ' . ( is_multisite() ? 'Yes' : 'No' ),
			'Environment: ' . wp_get_environment_type(),
			'Active theme: ' . $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ),
			'Registered SVG Flags blocks:',
		);

		foreach ( $blocks as $block ) {
			$lines[] = '  - ' . $block;
		}

		$lines[] = 'Active plugins:';
		foreach ( $plugins as $plugin ) {
			$lines[] = '  - ' . $plugin;
		}

		return implode( "\n", $lines );
	}

	/**
	 * Return registered plugin blocks.
	 *
	 * @return array<int, string>
	 */
	private function get_registered_blocks() {
		$registry = \WP_Block_Type_Registry::get_instance();
		$blocks   = array_filter(
			array_keys( $registry->get_all_registered() ),
			static function ( $block_name ) {
				return 0 === strpos( $block_name, 'svg-flags/' );
			}
		);
		sort( $blocks );

		return array_values( $blocks );
	}

	/**
	 * Return active plugin display names and versions.
	 *
	 * @return array<int, string>
	 */
	private function get_active_plugin_names() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins   = get_plugins();
		$active_files  = (array) get_option( 'active_plugins', array() );
		$active_names  = array();
		$network_files = is_multisite() ? array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) : array();

		foreach ( array_unique( array_merge( $active_files, $network_files ) ) as $plugin_file ) {
			if ( ! isset( $all_plugins[ $plugin_file ] ) ) {
				continue;
			}

			$name    = $all_plugins[ $plugin_file ]['Name'] ?? '';
			$version = $all_plugins[ $plugin_file ]['Version'] ?? '';
			if ( '' !== $name ) {
				$active_names[] = trim( $name . ' ' . $version );
			}
		}

		sort( $active_names, SORT_NATURAL | SORT_FLAG_CASE );

		return $active_names;
	}
}
