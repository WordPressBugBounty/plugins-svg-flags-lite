<?php
/**
 * Native SVG Flags settings and getting-started page.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the plugin-owned admin page.
 */
class Settings {

	/**
	 * WordPress plugin header data.
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
	 * @param array<string, mixed> $plugin_data       WordPress plugin headers.
	 * @param Constants            $custom_plugin_data Plugin configuration.
	 */
	public function __construct( $plugin_data, $custom_plugin_data ) {
		$this->plugin_data   = $plugin_data;
		$this->configuration = $custom_plugin_data;

		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
	}

	/**
	 * Add the durable settings route and retain hidden legacy URLs.
	 */
	public function add_options_page() {
		add_options_page(
			__( 'SVG Flags', 'svg-flags-lite' ),
			__( 'SVG Flags', 'svg-flags-lite' ),
			'manage_options',
			$this->configuration->plugin_slug,
			array( $this, 'render_page' )
		);

		$legacy_slugs = array(
			$this->configuration->plugin_slug . '-new-features',
			$this->configuration->plugin_slug . '-welcome',
		);

		foreach ( $legacy_slugs as $legacy_slug ) {
			add_submenu_page(
				'options-general.php',
				__( 'SVG Flags', 'svg-flags-lite' ),
				__( 'SVG Flags', 'svg-flags-lite' ),
				'manage_options',
				$legacy_slug,
				array( $this, 'render_page' )
			);
			remove_submenu_page( 'options-general.php', $legacy_slug );
		}
	}

	/**
	 * Render the complete settings and onboarding page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage SVG Flags.', 'svg-flags-lite' ) );
		}

		$version     = isset( $this->plugin_data['Version'] ) ? $this->plugin_data['Version'] : '';
		$is_premium  = $this->configuration->is_premium;
		$edition     = $is_premium ? __( 'Pro', 'svg-flags-lite' ) : __( 'Free', 'svg-flags-lite' );
		$upgrade_url = $this->configuration->freemius_upgrade_url;
		?>
		<div class="wrap svg-flags-admin">
			<section class="svg-flags-admin__hero">
				<div class="svg-flags-admin__hero-copy">
					<div class="svg-flags-admin__eyebrow">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: plugin version, 2: plugin edition. */
								__( 'Version %1$s · %2$s edition', 'svg-flags-lite' ),
								$version,
								$edition
							)
						);
						?>
					</div>
					<h1><?php esc_html_e( 'Publish polished flag collections—without leaving WordPress.', 'svg-flags-lite' ); ?></h1>
					<p><?php esc_html_e( 'Choose from 271 current country, territory, regional, and organisation flags, control their presentation visually, and publish through blocks or shortcodes.', 'svg-flags-lite' ); ?></p>
					<div class="svg-flags-admin__actions">
						<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>">
							<?php esc_html_e( 'Create a page', 'svg-flags-lite' ); ?>
						</a>
						<a class="button" href="https://wpgoplugins.com/document/svg-flags-documentation/" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Read the documentation', 'svg-flags-lite' ); ?>
						</a>
						<?php if ( ! $is_premium ) : ?>
							<a class="button svg-flags-admin__upgrade" href="<?php echo esc_url( $upgrade_url ); ?>">
								<?php esc_html_e( 'Explore SVG Flags Pro', 'svg-flags-lite' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
				<div class="svg-flags-admin__preview" aria-label="<?php esc_attr_e( 'Example flag collection', 'svg-flags-lite' ); ?>">
					<?php foreach ( array( 'gb', 'us', 'ca', 'fr', 'de', 'jp' ) as $flag ) : ?>
						<span class="fi fi-<?php echo esc_attr( $flag ); ?> flag-icon flag-icon-<?php echo esc_attr( $flag ); ?>" aria-hidden="true"></span>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="svg-flags-admin__section" aria-labelledby="svg-flags-start-title">
				<div class="svg-flags-admin__section-heading">
					<h2 id="svg-flags-start-title"><?php esc_html_e( 'Three useful ways to add flags', 'svg-flags-lite' ); ?></h2>
					<p><?php esc_html_e( 'No configuration is required. Insert a block or paste a shortcode and publish.', 'svg-flags-lite' ); ?></p>
				</div>
				<div class="svg-flags-admin__cards">
					<article class="svg-flags-admin__card">
						<span class="dashicons dashicons-grid-view" aria-hidden="true"></span>
						<h3><?php esc_html_e( 'Flag Grid', 'svg-flags-lite' ); ?></h3>
						<p><?php esc_html_e( 'Build a responsive gallery with multiple countries, flexible columns, spacing, sizing, ratios, and optional captions.', 'svg-flags-lite' ); ?></p>
					</article>
					<article class="svg-flags-admin__card">
						<span class="dashicons dashicons-format-image" aria-hidden="true"></span>
						<h3><?php esc_html_e( 'Flag Image', 'svg-flags-lite' ); ?></h3>
						<p><?php esc_html_e( 'Add a semantic SVG image with an accessible country name, responsive sizing, lazy loading, and square or 4:3 presentation.', 'svg-flags-lite' ); ?></p>
					</article>
					<article class="svg-flags-admin__card">
						<span class="dashicons dashicons-flag" aria-hidden="true"></span>
						<h3><?php esc_html_e( 'CSS Flag', 'svg-flags-lite' ); ?></h3>
						<p><?php esc_html_e( 'Use the lightweight flag block for compact inline or block-level flags, captions, and flexible sizing.', 'svg-flags-lite' ); ?></p>
					</article>
				</div>
			</section>

			<div class="svg-flags-admin__columns">
				<section class="svg-flags-admin__panel" aria-labelledby="svg-flags-shortcodes-title">
					<h2 id="svg-flags-shortcodes-title"><?php esc_html_e( 'Shortcode quick start', 'svg-flags-lite' ); ?></h2>
					<p><?php esc_html_e( 'Shortcodes remain available for classic content, templates, and programmatic layouts.', 'svg-flags-lite' ); ?></p>
					<pre><code>[svg-flag flag="gb" size="3"]</code></pre>
					<pre><code>[svg-flag-image flag="fr" size="96" size_unit="px" caption="true"]</code></pre>
					<pre><code>[svg-flag-grid flags="gb,us,ca,fr,de,jp" columns="3" caption="true"]</code></pre>
				</section>

				<section class="svg-flags-admin__panel svg-flags-admin__panel--pro" aria-labelledby="svg-flags-pro-title">
					<h2 id="svg-flags-pro-title"><?php esc_html_e( 'Match every flag to your design with Pro', 'svg-flags-lite' ); ?></h2>
					<p><?php esc_html_e( 'Add flag headings, custom captions and tooltips, IDs and CSS classes, plus border, spacing, and presentation controls.', 'svg-flags-lite' ); ?></p>
					<?php if ( $is_premium ) : ?>
						<p class="svg-flags-admin__status"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php esc_html_e( 'Pro features are active on this site.', 'svg-flags-lite' ); ?></p>
					<?php else : ?>
						<a class="button button-primary" href="<?php echo esc_url( $upgrade_url ); ?>">
							<?php esc_html_e( 'See Pro pricing', 'svg-flags-lite' ); ?>
						</a>
					<?php endif; ?>
				</section>
			</div>

			<section class="svg-flags-admin__support" aria-labelledby="svg-flags-support-title">
				<div>
					<h2 id="svg-flags-support-title"><?php esc_html_e( 'Need help or have an idea?', 'svg-flags-lite' ); ?></h2>
					<p><?php esc_html_e( 'Report a problem, suggest a country-display workflow, or tell us what would make SVG Flags more useful on your sites.', 'svg-flags-lite' ); ?></p>
				</div>
				<div class="svg-flags-admin__actions">
					<a class="button" href="<?php echo esc_url( $this->configuration->contact_us_url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Contact WPGO Plugins', 'svg-flags-lite' ); ?>
					</a>
					<a class="button" href="https://wordpress.org/support/plugin/svg-flags-lite/" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'WordPress.org support', 'svg-flags-lite' ); ?>
					</a>
				</div>
			</section>
		</div>
		<?php
	}
}
