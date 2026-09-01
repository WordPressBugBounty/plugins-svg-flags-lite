<?php
/**
 * SVG Flags asset registration.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin's admin, editor, and frontend assets.
 */
class Enqueue_Scripts {

	/**
	 * Common plugin paths.
	 *
	 * @var array<string, string>
	 */
	protected $module_roots;

	/**
	 * Country labels keyed by alpha-2 code.
	 *
	 * @var array<string, string>
	 */
	protected $country_codes;

	/** @var array<string, array{name: string, continent: string}> */
	protected $country_catalog;

	/**
	 * Prefix shared by registered scripts and styles.
	 *
	 * @var string
	 */
	protected $enqueue_prefix;

	/**
	 * Hook suffix for the native settings screen.
	 *
	 * @var string
	 */
	protected $settings_page_hook;

	/**
	 * Admin page slugs owned by this plugin.
	 *
	 * @var array<int, string>
	 */
	protected $admin_page_slugs;

	/**
	 * Settings model.
	 *
	 * @var Settings_Options
	 */
	protected $settings;

	/**
	 * WordPress editor script dependencies.
	 *
	 * @var array<int, string>
	 */
	protected $editor_dependencies;

	/**
	 * Main class constructor.
	 *
	 * @param array<string, string> $module_roots      Common plugin paths.
	 * @param Constants             $custom_plugin_data Plugin configuration.
	 * @param Settings_Options      $settings Settings model.
	 */
	public function __construct( $module_roots, $custom_plugin_data, $settings ) {
		$this->module_roots        = $module_roots;
		$this->country_codes       = $custom_plugin_data->country_codes;
		$this->country_catalog     = $custom_plugin_data->country_catalog;
		$this->enqueue_prefix      = $custom_plugin_data->enqueue_prefix;
		$this->settings_page_hook  = $custom_plugin_data->settings_page_hook;
		$this->settings            = $settings;
		$this->admin_page_slugs    = array(
			$custom_plugin_data->home_slug,
			$custom_plugin_data->settings_slug,
			$custom_plugin_data->new_features_slug,
			$custom_plugin_data->plugin_slug . '-welcome',
		);
		$this->editor_dependencies = array(
			'wp-api-fetch',
			'wp-block-editor',
			'wp-blocks',
			'wp-components',
			'wp-compose',
			'wp-data',
			'wp-element',
			'wp-hooks',
			'wp-i18n',
			'wp-server-side-render',
			'wp-url',
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_settings_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
	}

	/**
	 * Load the self-contained settings page styles.
	 *
	 * @param string $hook_suffix Current admin screen hook.
	 */
	public function enqueue_admin_settings_assets( $hook_suffix ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! in_array( $page, $this->admin_page_slugs, true ) ) {
			return;
		}

		$this->enqueue_style( 'admin-settings-css', 'assets/css/admin-settings.css' );
		$this->enqueue_style( 'core-css', 'assets/flag-icon-css/css/flag-icon.min.css' );
		$this->enqueue_script(
			'admin-settings-js',
			'assets/js/admin-settings.js',
			array()
		);
		wp_localize_script(
			$this->enqueue_prefix . '-admin-settings-js',
			'svg_flags_admin_data',
			array(
				'copySuccess' => __( 'Support summary copied.', 'svg-flags-lite' ),
				'copyError'   => __( 'Copy failed. Select the summary and copy it manually.', 'svg-flags-lite' ),
			)
		);
	}

	/**
	 * Load general frontend styles.
	 */
	public function enqueue_frontend_assets() {
		$this->enqueue_style( 'plugin-css', 'assets/css/frontend.css' );
		$this->enqueue_style( 'core-css', 'assets/flag-icon-css/css/flag-icon.min.css' );
	}

	/**
	 * Load assets used by the block editor.
	 */
	public function enqueue_block_editor_assets() {
		$dependencies = $this->editor_dependencies;

		if ( svg_flags_fs()->can_use_premium_code__premium_only() ) {
			$this->enqueue_script(
				'extend-blocks-pro-js',
				'classes/modules/js/extend.blocks.pro.js',
				array( 'wp-components', 'wp-element', 'wp-hooks', 'wp-i18n' )
			);
			$this->enqueue_script(
				'block-editor-pro-js',
				'classes/modules/js/block.editor.pro.js',
				$this->editor_dependencies
			);
			$this->enqueue_style( 'block-editor-pro-css', 'classes/modules/css/block.editor.styles.pro.css' );

			$dependencies[] = $this->enqueue_prefix . '-extend-blocks-pro-js';
			$dependencies[] = $this->enqueue_prefix . '-block-editor-pro-js';
		}

		$handle = $this->enqueue_prefix . '-block-editor-js';
		$path   = 'assets/js/block.editor.js';
		wp_register_script(
			$handle,
			plugins_url( $path, $this->module_roots['file'] ),
			$dependencies,
			$this->asset_version( $path ),
			true
		);
		wp_localize_script(
			$handle,
			'svg_flags_editor_data',
			array(
				'countries' => $this->country_codes,
				'catalog'   => $this->country_catalog,
				'defaults'  => $this->settings->block_defaults(),
			)
		);
		wp_enqueue_script( $handle );

		$this->enqueue_style( 'block-editor-css', 'assets/css/block.editor.styles.css' );
	}

	/**
	 * Load styles shared by editor and frontend block rendering.
	 */
	public function enqueue_block_assets() {
		if ( svg_flags_fs()->can_use_premium_code__premium_only() ) {
			$this->enqueue_style( 'block-pro-css', 'classes/modules/css/block.styles.pro.css' );
			$this->enqueue_script(
				'frontend-pro-js',
				'classes/modules/js/frontend.pro.js',
				array()
			);
			wp_localize_script(
				$this->enqueue_prefix . '-frontend-pro-js',
				'svg_flags_frontend_data',
				array(
					'oneFlag'   => __( '1 flag', 'svg-flags-lite' ),
					'manyFlags' => __( '%d flags', 'svg-flags-lite' ),
				)
			);
		}

		$this->enqueue_style( 'core-css', 'assets/flag-icon-css/css/flag-icon.min.css' );
		$this->enqueue_style( 'block-css', 'assets/css/block.styles.css' );
	}

	/**
	 * Enqueue a plugin script using its file modification time as the version.
	 *
	 * @param string             $suffix       Handle suffix.
	 * @param string             $relative_path Plugin-relative asset path.
	 * @param array<int, string> $dependencies Script dependencies.
	 */
	private function enqueue_script( $suffix, $relative_path, $dependencies ) {
		wp_enqueue_script(
			$this->enqueue_prefix . '-' . $suffix,
			plugins_url( $relative_path, $this->module_roots['file'] ),
			$dependencies,
			$this->asset_version( $relative_path ),
			true
		);
	}

	/**
	 * Enqueue a plugin stylesheet using its file modification time as the version.
	 *
	 * @param string $suffix        Handle suffix.
	 * @param string $relative_path Plugin-relative asset path.
	 */
	private function enqueue_style( $suffix, $relative_path ) {
		wp_enqueue_style(
			$this->enqueue_prefix . '-' . $suffix,
			plugins_url( $relative_path, $this->module_roots['file'] ),
			array(),
			$this->asset_version( $relative_path )
		);
	}

	/**
	 * Return a deterministic development-safe asset version.
	 *
	 * @param string $relative_path Plugin-relative asset path.
	 * @return int|false
	 */
	private function asset_version( $relative_path ) {
		$path = $this->module_roots['dir'] . $relative_path;
		return file_exists( $path ) ? filemtime( $path ) : false;
	}
}
