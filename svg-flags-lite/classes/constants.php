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
	 * Stable Home page slug.
	 *
	 * @var string
	 */
	public $home_slug = 'svg-flags-wpgoplugins';

	/**
	 * Stable Settings page slug.
	 *
	 * @var string
	 */
	public $settings_slug = 'svg-flags-wpgoplugins-settings';

	/**
	 * Stable New Features page slug.
	 *
	 * @var string
	 */
	public $new_features_slug = 'svg-flags-wpgoplugins-new-features';

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
	 * Flag metadata keyed by upstream code.
	 *
	 * @var array<string, array{name: string, continent: string}>
	 */
	public $country_catalog = array();

	/**
	 * Hook suffix for the native settings screen.
	 *
	 * @var string
	 */
	public $settings_page_hook = 'svg-flags_page_svg-flags-wpgoplugins-settings';

	/**
	 * URL for the plugin Home screen.
	 *
	 * @var string
	 */
	public $home_url = '';

	/**
	 * URL for the native settings screen.
	 *
	 * @var string
	 */
	public $main_settings_url = '';

	/**
	 * URL for the New Features screen.
	 *
	 * @var string
	 */
	public $new_features_url = '';

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
		// The admin edition must follow the active entitlement, not merely the
		// premium package that happens to be installed.
		$this->is_premium = svg_flags_fs()->can_use_premium_code__premium_only();

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
				$this->country_catalog[ $code ] = array(
					'name'      => sanitize_text_field( $country['name'] ),
					'continent' => empty( $country['continent'] )
						? 'Other'
						: sanitize_text_field( $country['continent'] ),
				);
			}
		}

		if ( empty( $this->country_codes ) ) {
			$this->country_codes = array( 'GB' => 'United Kingdom' );
			$this->country_catalog = array(
				'GB' => array(
					'name'      => 'United Kingdom',
					'continent' => 'Europe',
				),
			);
		}

		$this->home_url = add_query_arg(
			'page',
			$this->home_slug,
			admin_url( 'admin.php' )
		);

		$this->main_settings_url = add_query_arg(
			'page',
			$this->settings_slug,
			admin_url( 'admin.php' )
		);

		$this->new_features_url = add_query_arg(
			'page',
			$this->new_features_slug,
			admin_url( 'admin.php' )
		);

		$this->freemius_upgrade_url = svg_flags_fs()->get_upgrade_url();
	}
}
