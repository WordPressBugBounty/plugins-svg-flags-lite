<?php
/**
 * SVG Flags
 * php version 7.4
 *
 * @category  WPGO_Plugins
 * @package   SVG_Flags
 * @author    David Gwyer <d.v.gwyer@gmail.com>
 * @copyright 2026 WPGO Plugins
 * @license   https://www.gnu.org/licenses/gpl-2.0.txt GPL v2 or later
 * @link      https://wpgoplugins.com/plugins/svg-flags/
 *
 * @wordpress-plugin
 * Plugin Name: SVG Flags – Country Flag Blocks and Galleries
 * Plugin URI: https://wpgoplugins.com/plugins/svg-flags/
 * Description: Add responsive SVG flags, accessible flag images, and multi-country galleries with blocks or shortcodes.
 * Version: 0.9.7
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * Author: David Gwyer
 * Author URI: https://wpgoplugins.com
 * Text Domain: svg-flags-lite
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt

 * @fs_ignore       /assets
 */

/*
Copyright 2017-2026 David Gwyer (email : david@wpgoplugins.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace WPGO_Plugins\SVG_Flags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( __NAMESPACE__ . '\svg_flags_fs' ) ) {
	svg_flags_fs()->set_basename( true, __FILE__ );
} else {

	if ( ! function_exists( __NAMESPACE__ . '\svg_flags_fs' ) ) {
		/**
		 * Create a helper function for easy SDK access.
		 */
		function svg_flags_fs() {
			global $svg_flags_fs;

			if ( ! isset( $svg_flags_fs ) ) {
				// Include Freemius SDK.
				include_once __DIR__ . '/vendor/freemius/start.php';

				$svg_flags_fs = fs_dynamic_init(
					array(
						'id'                  => '5235',
						'slug'                => 'svg-flags-lite',
						'premium_slug'        => 'svg-flags-premium',
						'type'                => 'plugin',
						'public_key'          => 'pk_336fe1d95cd6f69e27f07834ef2a4',
						'is_premium'          => false,
						'premium_suffix'      => 'Pro',
						// If your plugin is a serviceware, set this option to false.
						'has_premium_version' => true,
						'has_addons'          => false,
						'has_paid_plans'      => true,
						'menu'                => array(
							'slug'       => 'svg-flags-wpgoplugins',
							'first-path' => 'options-general.php?page=svg-flags-wpgoplugins',
							'parent'     => array(
								'slug' => 'options-general.php',
							),
						),
					)
				);
			}

			return $svg_flags_fs;
		}

		// Init Freemius.
		svg_flags_fs();
		// Signal that SDK was initiated.
		do_action( 'svg_flags_fs_loaded' );
	}

	// Initialize plugin.
	$module_roots = array(
		'dir'  => plugin_dir_path( __FILE__ ),
		'uri'  => plugins_url( '', __FILE__ ),
		'file' => __FILE__,
	);
	require_once $module_roots['dir'] . 'classes/class-main.php';
	Main::init( $module_roots );

}
