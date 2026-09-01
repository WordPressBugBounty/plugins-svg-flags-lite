<?php
/**
 * Bootstrap plugin classes.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

/**
 * Main bootstrap class.
 */
class BootStrap {

	/**
	 * Common root paths/directories.
	 *
	 * @var $module_roots
	 */
	protected $module_roots;

	/**
	 * Main class constructor.
	 */
	public function __construct() {
		$this->module_roots = Main::$module_roots;
		$this->load_supported_features();
	}

	/**
	 * Load plugin features.
	 */
	public function load_supported_features() {
		$root = $this->module_roots['dir'];

		// Load plugin constants/data.
		require_once $root . 'classes/constants.php';
		$custom_plugin_data = new Constants( $this->module_roots );
		require_once $root . 'classes/settings-options.php';
		$settings_options = new Settings_Options( $custom_plugin_data->country_codes, $custom_plugin_data->is_premium );

		// Shared rendering helpers.
		require_once $root . 'classes/utility.php';
		$plugin_data = get_plugin_data( $this->module_roots['file'], false, false );

		if ( svg_flags_fs()->can_use_premium_code__premium_only() ) {
			// Load pro plugin features.
			$path = $root . 'classes/modules/modules_bootstrap.php';
			if ( file_exists( $path ) ) {
				require_once $path;
				new Modules_BootStrap( $this->module_roots, $custom_plugin_data );
			}
		}

		// Enqueue plugin scripts.
		require_once $root . 'classes/enqueue-scripts.php';
		new Enqueue_Scripts( $this->module_roots, $custom_plugin_data, $settings_options );

		// Native plugin admin pages.
		require_once $root . 'classes/plugin-admin-pages/admin-view.php';
		require_once $root . 'classes/plugin-admin-pages/support-diagnostics.php';
		$support_diagnostics = new Support_Diagnostics( $plugin_data, $custom_plugin_data );
		require_once $root . 'classes/plugin-admin-pages/home.php';
		new Home( $plugin_data, $custom_plugin_data, $settings_options );
		require_once $root . 'classes/plugin-admin-pages/settings.php';
		new Settings( $plugin_data, $custom_plugin_data, $settings_options, $support_diagnostics );
		require_once $root . 'classes/plugin-admin-pages/new-features.php';
		new New_Features( $plugin_data, $custom_plugin_data );
		require_once $root . 'classes/plugin-admin-pages/menu-controller.php';
		new Menu_Controller( $custom_plugin_data );

		// Register blocks.
		require_once $root . 'classes/register-blocks.php';
		new Register_Blocks( $this->module_roots );

		// Shortcodes bootstrap.
		require_once $root . 'classes/shortcodes/shortcodes.php';
		new Shortcodes( $this->module_roots, $custom_plugin_data );

		// Links on the main Plugins screen.
		require_once $root . 'classes/plugin-links.php';
		new Plugin_Links( $this->module_roots, $custom_plugin_data );

	}
} /* End class definition */
