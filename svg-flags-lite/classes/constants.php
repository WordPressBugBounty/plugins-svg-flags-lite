<?php
/**
 * Shared SVG Flags configuration.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Builds the small configuration object consumed by plugin services.
 */
class Constants {

	/**
	 * Whether this installation can run premium code.
	 *
	 * @var bool
	 */
	public $is_premium = false;

	/**
	 * Stable WordPress admin page slug.
	 *
	 * @var string
	 */
	public $plugin_slug = 'svg-flags-wpgoplugins';

	/**
	 * Prefix shared by registered scripts and styles.
	 *
	 * @var string
	 */
	public $enqueue_prefix = 'svg-flags';

	/**
	 * Flag labels keyed by upstream code.
	 *
	 * @var array<string, string>
	 */
	public $country_codes = array();

	/**
	 * Hook suffix for the native settings screen.
	 *
	 * @var string
	 */
	public $settings_page_hook = 'settings_page_svg-flags-wpgoplugins';

	/**
	 * URL for the native settings screen.
	 *
	 * @var string
	 */
	public $main_settings_url = '';

	/**
	 * Freemius-managed upgrade URL.
	 *
	 * @var string
	 */
	public $freemius_upgrade_url = '';

	/**
	 * Public support URL.
	 *
	 * @var string
	 */
	public $contact_us_url = 'https://wpgoplugins.com/contact-us/';

	/**
	 * Main class constructor.
	 *
	 * @param array<string, string> $module_roots Common plugin paths.
	 */
	public function __construct( $module_roots ) {
		$this->is_premium = svg_flags_fs()->is_premium();

		$countries_file = $module_roots['dir'] . 'assets/flag-icon-css/country.json';
		$countries      = wp_json_file_decode( $countries_file, array( 'associative' => true ) );

		if ( is_array( $countries ) ) {
			foreach ( $countries as $country ) {
				if ( ! is_array( $country ) || empty( $country['code'] ) || empty( $country['name'] ) ) {
					continue;
				}

				$code = strtoupper( sanitize_key( $country['code'] ) );
				if ( ! preg_match( '/^[A-Z0-9-]{2,8}$/', $code ) ) {
					continue;
				}

				$this->country_codes[ $code ] = sanitize_text_field( $country['name'] );
			}
		}

		if ( empty( $this->country_codes ) ) {
			$this->country_codes = array( 'GB' => __( 'United Kingdom', 'svg-flags-lite' ) );
		}

		$this->main_settings_url = add_query_arg(
			'page',
			$this->plugin_slug,
			admin_url( 'options-general.php' )
		);

		$this->freemius_upgrade_url = svg_flags_fs()->get_upgrade_url();
	}
}
