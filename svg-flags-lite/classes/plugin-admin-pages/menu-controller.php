<?php
/**
 * SVG Flags admin menu ordering.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps plugin-owned pages in front of optional Freemius pages.
 */
class Menu_Controller {

	/** @var Constants Plugin configuration. */
	private $configuration;

	/**
	 * Main class constructor.
	 *
	 * @param Constants $configuration Plugin configuration.
	 */
	public function __construct( $configuration ) {
		$this->configuration = $configuration;
		add_action( 'admin_menu', array( $this, 'prepare_menu' ), PHP_INT_MAX );
	}

	/**
	 * Put Home, Settings and New Features first after optional pages are registered.
	 */
	public function prepare_menu() {
		global $submenu;

		$parent = $this->configuration->plugin_slug;
		if ( empty( $submenu[ $parent ] ) || ! is_array( $submenu[ $parent ] ) ) {
			return;
		}

		$preferred = array(
			$this->configuration->home_slug,
			$this->configuration->settings_slug,
			$this->configuration->new_features_slug,
		);
		$ordered   = array();
		$remaining = $submenu[ $parent ];

		if ( $this->configuration->is_premium ) {
			$remaining = array_filter(
				$remaining,
				static function ( $item ) {
					return ! isset( $item[2] ) || false === strpos( (string) $item[2], 'wordpress.org/support/plugin/svg-flags-lite' );
				}
			);
		}

		foreach ( $preferred as $slug ) {
			foreach ( $remaining as $index => $item ) {
				if ( isset( $item[2] ) && $slug === $item[2] ) {
					$ordered[] = $item;
					unset( $remaining[ $index ] );
					break;
				}
			}
		}

		$submenu[ $parent ] = array_merge( $ordered, array_values( $remaining ) );
	}
}
