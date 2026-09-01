<?php
/**
 * SVG Flags New Features page.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the current product changes.
 */
class New_Features {

	/** @var array<string, mixed> Plugin headers. */
	private $plugin_data;

	/** @var Constants Plugin configuration. */
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

		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
	}

	/** Register the current and hidden legacy routes. */
	public function add_options_page() {
		add_submenu_page(
			$this->configuration->plugin_slug,
			__( 'SVG Flags New Features', 'svg-flags-lite' ),
			__( 'New Features', 'svg-flags-lite' ),
			'manage_options',
			$this->configuration->new_features_slug,
			array( $this, 'render_page' )
		);

		add_submenu_page(
			'options-general.php',
			__( 'SVG Flags New Features', 'svg-flags-lite' ),
			__( 'SVG Flags', 'svg-flags-lite' ),
			'manage_options',
			$this->configuration->new_features_slug,
			array( $this, 'render_page' )
		);
		remove_submenu_page( 'options-general.php', $this->configuration->new_features_slug );
	}

	/** Render the New Features page. */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view SVG Flags updates.', 'svg-flags-lite' ) );
		}
		?>
		<div class="wrap svg-flags-admin svg-flags-admin--features">
			<?php Admin_View::render_header( $this->plugin_data, $this->configuration, __( 'New Features', 'svg-flags-lite' ) ); ?>

			<section class="svg-flags-admin__feature-hero" aria-labelledby="svg-flags-release-title">
				<span class="svg-flags-admin__eyebrow"><?php esc_html_e( 'Current development version', 'svg-flags-lite' ); ?></span>
				<h2 id="svg-flags-release-title"><?php esc_html_e( 'Linked flag experiences and faster Pro starting points', 'svg-flags-lite' ); ?></h2>
				<p><?php esc_html_e( 'Version 0.10.0 adds Linked Flag Cards, advanced responsive grids, a searchable Flag Directory, Pro defaults and a unified Home experience with more safe starter drafts.', 'svg-flags-lite' ); ?></p>
				<div class="svg-flags-admin__actions"><a class="button button-primary" href="<?php echo esc_url( $this->configuration->home_url ); ?>"><?php esc_html_e( 'Open Home', 'svg-flags-lite' ); ?></a><a class="button" href="<?php echo esc_url( $this->configuration->main_settings_url ); ?>"><?php esc_html_e( 'Open Settings', 'svg-flags-lite' ); ?></a></div>
			</section>

			<div class="svg-flags-admin__release-grid">
				<article><span class="dashicons dashicons-admin-links" aria-hidden="true"></span><h3><?php esc_html_e( 'Linked Flag Cards', 'svg-flags-lite' ); ?></h3><p><?php esc_html_e( 'Build accessible country cards with a flag, title, supporting description, optional destination and controlled visual treatment.', 'svg-flags-lite' ); ?></p></article>
				<article><span class="dashicons dashicons-grid-view" aria-hidden="true"></span><h3><?php esc_html_e( 'A stronger Pro Grid', 'svg-flags-lite' ); ?></h3><p><?php esc_html_e( 'Choose responsive columns, per-flag captions and links, live search, region filtering and optional card styling.', 'svg-flags-lite' ); ?></p></article>
				<article><span class="dashicons dashicons-search" aria-hidden="true"></span><h3><?php esc_html_e( 'Searchable Flag Directory', 'svg-flags-lite' ); ?></h3><p><?php esc_html_e( 'Publish the complete 271-flag catalogue or selected regions with live visitor search and filtering.', 'svg-flags-lite' ); ?></p></article>
				<article><span class="dashicons dashicons-admin-settings" aria-hidden="true"></span><h3><?php esc_html_e( 'Pro defaults', 'svg-flags-lite' ); ?></h3><p><?php esc_html_e( 'Save useful starting values for Linked Flag Cards, advanced grids and directories without changing existing content.', 'svg-flags-lite' ); ?></p></article>
				<article><span class="dashicons dashicons-welcome-view-site" aria-hidden="true"></span><h3><?php esc_html_e( 'Unified product Home', 'svg-flags-lite' ); ?></h3><p><?php esc_html_e( 'Find more ready-made examples, relevant companion plugins, documentation and edition-aware support from one predictable screen.', 'svg-flags-lite' ); ?></p></article>
				<article><span class="dashicons dashicons-sos" aria-hidden="true"></span><h3><?php esc_html_e( 'Safer support summaries', 'svg-flags-lite' ); ?></h3><p><?php esc_html_e( 'Copy useful environment details without exposing site content, URLs, settings, user data or licence information.', 'svg-flags-lite' ); ?></p></article>
				<article><span class="dashicons dashicons-unlock" aria-hidden="true"></span><h3><?php esc_html_e( 'Accurate edition status', 'svg-flags-lite' ); ?></h3><p><?php esc_html_e( 'Home, Settings and feature loading switch back to Free when a Pro licence is no longer active; licensed Pro uses one clear action list.', 'svg-flags-lite' ); ?></p></article>
			</div>

			<section class="svg-flags-admin__support" aria-labelledby="svg-flags-changelog-title">
				<div><h2 id="svg-flags-changelog-title"><?php esc_html_e( 'Looking for the full release history?', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'The public changelog lists compatibility updates, new blocks, library changes and fixes by version.', 'svg-flags-lite' ); ?></p></div>
				<a class="button" href="https://wordpress.org/plugins/svg-flags-lite/#developers" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View changelog', 'svg-flags-lite' ); ?> <?php Admin_View::external_link_text(); ?></a>
			</section>
		</div>
		<?php
	}
}
