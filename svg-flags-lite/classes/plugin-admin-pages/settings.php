<?php
/**
 * Native SVG Flags Settings page.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders plugin options and support diagnostics.
 */
class Settings {

	/** @var array<string, mixed> WordPress plugin header data. */
	private $plugin_data;

	/** @var Constants Plugin configuration. */
	private $configuration;

	/** @var Settings_Options Settings model. */
	private $settings;

	/** @var Support_Diagnostics Support diagnostics. */
	private $diagnostics;

	/**
	 * Main class constructor.
	 *
	 * @param array<string, mixed> $plugin_data Plugin headers.
	 * @param Constants            $configuration Plugin configuration.
	 * @param Settings_Options     $settings Settings model.
	 * @param Support_Diagnostics  $diagnostics Support diagnostics.
	 */
	public function __construct( $plugin_data, $configuration, $settings, $diagnostics ) {
		$this->plugin_data   = $plugin_data;
		$this->configuration = $configuration;
		$this->settings      = $settings;
		$this->diagnostics   = $diagnostics;

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'admin_post_svg_flags_reset_settings', array( $this, 'reset_settings' ) );
	}

	/** Register the Settings API option. */
	public function register_settings() {
		register_setting(
			Settings_Options::OPTION_GROUP,
			Settings_Options::OPTION_NAME,
			array( $this->settings, 'sanitize' )
		);
	}

	/** Add the Settings submenu page. */
	public function add_options_page() {
		add_submenu_page(
			$this->configuration->plugin_slug,
			__( 'SVG Flags Settings', 'svg-flags-lite' ),
			__( 'Settings', 'svg-flags-lite' ),
			'manage_options',
			$this->configuration->settings_slug,
			array( $this, 'render_page' )
		);
	}

	/** Reset settings to their stable defaults. */
	public function reset_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to reset SVG Flags settings.', 'svg-flags-lite' ) );
		}

		check_admin_referer( 'svg_flags_reset_settings' );
		delete_option( Settings_Options::OPTION_NAME );

		wp_safe_redirect( add_query_arg( 'svg_flags_settings_reset', '1', $this->configuration->main_settings_url ) );
		exit;
	}

	/** Render the Settings page. */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage SVG Flags.', 'svg-flags-lite' ) );
		}

		$options = $this->settings->get();
		?>
		<div class="wrap svg-flags-admin svg-flags-admin--settings">
			<?php Admin_View::render_header( $this->plugin_data, $this->configuration, __( 'Settings', 'svg-flags-lite' ) ); ?>

			<?php settings_errors(); ?>
			<?php if ( isset( $_GET['svg_flags_settings_reset'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'SVG Flags settings were reset to their defaults.', 'svg-flags-lite' ); ?></p></div>
			<?php endif; ?>

			<div class="svg-flags-admin__settings-intro">
				<div>
					<span class="svg-flags-admin__eyebrow"><?php esc_html_e( 'New content only', 'svg-flags-lite' ); ?></span>
					<h2><?php esc_html_e( 'Choose your starting point', 'svg-flags-lite' ); ?></h2>
					<p><?php esc_html_e( 'These values are written into newly inserted blocks and starter drafts. Existing blocks and shortcodes keep their saved output.', 'svg-flags-lite' ); ?></p>
				</div>
				<div class="svg-flags-admin__actions">
					<a class="button" href="<?php echo esc_url( $this->configuration->home_url ); ?>"><?php esc_html_e( 'Back to Home', 'svg-flags-lite' ); ?></a>
					<a class="button" href="<?php echo esc_url( Home::DOCUMENTATION_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Documentation', 'svg-flags-lite' ); ?> <?php Admin_View::external_link_text(); ?></a>
				</div>
			</div>

			<form action="options.php" method="post">
				<?php settings_fields( Settings_Options::OPTION_GROUP ); ?>

				<section class="svg-flags-admin__settings-panel" aria-labelledby="svg-flags-single-defaults-title">
					<div class="svg-flags-admin__settings-heading">
						<span class="dashicons dashicons-flag" aria-hidden="true"></span>
						<div><h2 id="svg-flags-single-defaults-title"><?php esc_html_e( 'Single flag defaults', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'Used when you insert a new SVG Flag or SVG Flag Image block.', 'svg-flags-lite' ); ?></p></div>
					</div>
					<div class="svg-flags-admin__form-grid">
						<label>
							<span><?php esc_html_e( 'Default country or territory', 'svg-flags-lite' ); ?></span>
							<select name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[default_flag]">
								<?php foreach ( $this->configuration->country_codes as $code => $name ) : ?>
									<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $options['default_flag'], $code ); ?>><?php echo esc_html( $name . ' (' . $code . ')' ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<div class="svg-flags-admin__field-pair">
							<label><span><?php esc_html_e( 'Flag size', 'svg-flags-lite' ); ?></span><input type="number" min="0.25" max="1000" step="0.25" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[default_size]" value="<?php echo esc_attr( $options['default_size'] ); ?>"></label>
							<?php $this->render_unit_select( 'default_size_unit', $options['default_size_unit'] ); ?>
						</div>
						<label class="svg-flags-admin__checkbox"><input type="checkbox" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[default_square]" value="1" <?php checked( $options['default_square'] ); ?>><span><?php esc_html_e( 'Use square flags', 'svg-flags-lite' ); ?></span></label>
						<label class="svg-flags-admin__checkbox"><input type="checkbox" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[default_caption]" value="1" <?php checked( $options['default_caption'] ); ?>><span><?php esc_html_e( 'Show country names', 'svg-flags-lite' ); ?></span></label>
					</div>
				</section>

				<section class="svg-flags-admin__settings-panel" aria-labelledby="svg-flags-grid-defaults-title">
					<div class="svg-flags-admin__settings-heading">
						<span class="dashicons dashicons-grid-view" aria-hidden="true"></span>
						<div><h2 id="svg-flags-grid-defaults-title"><?php esc_html_e( 'Flag Grid defaults', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'Used by newly inserted Flag Grid blocks and the Home-page gallery starter.', 'svg-flags-lite' ); ?></p></div>
					</div>
					<div class="svg-flags-admin__form-grid">
						<label class="svg-flags-admin__field-wide"><span><?php esc_html_e( 'Countries and territories', 'svg-flags-lite' ); ?></span><input type="text" class="regular-text code" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[grid_flags]" value="<?php echo esc_attr( implode( ', ', $options['grid_flags'] ) ); ?>"><small><?php esc_html_e( 'Enter comma-separated codes such as GB, US, CA, FR.', 'svg-flags-lite' ); ?></small></label>
						<label><span><?php esc_html_e( 'Columns', 'svg-flags-lite' ); ?></span><input type="number" min="1" max="8" step="1" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[grid_columns]" value="<?php echo esc_attr( $options['grid_columns'] ); ?>"></label>
						<div class="svg-flags-admin__field-pair"><label><span><?php esc_html_e( 'Gap', 'svg-flags-lite' ); ?></span><input type="number" min="0" max="1000" step="0.25" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[grid_gap]" value="<?php echo esc_attr( $options['grid_gap'] ); ?>"></label><?php $this->render_unit_select( 'grid_gap_unit', $options['grid_gap_unit'], true ); ?></div>
						<div class="svg-flags-admin__field-pair"><label><span><?php esc_html_e( 'Maximum flag width', 'svg-flags-lite' ); ?></span><input type="number" min="0.25" max="1000" step="0.25" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[grid_size]" value="<?php echo esc_attr( $options['grid_size'] ); ?>"></label><?php $this->render_unit_select( 'grid_size_unit', $options['grid_size_unit'], true ); ?></div>
						<label class="svg-flags-admin__checkbox"><input type="checkbox" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[grid_square]" value="1" <?php checked( $options['grid_square'] ); ?>><span><?php esc_html_e( 'Use square flags', 'svg-flags-lite' ); ?></span></label>
						<label class="svg-flags-admin__checkbox"><input type="checkbox" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[grid_caption]" value="1" <?php checked( $options['grid_caption'] ); ?>><span><?php esc_html_e( 'Show country names', 'svg-flags-lite' ); ?></span></label>
					</div>
				</section>

				<?php if ( $this->configuration->is_premium ) : ?>
					<section class="svg-flags-admin__settings-panel" aria-labelledby="svg-flags-pro-grid-defaults-title">
						<div class="svg-flags-admin__settings-heading"><span class="dashicons dashicons-grid-view" aria-hidden="true"></span><div><h2 id="svg-flags-pro-grid-defaults-title"><?php esc_html_e( 'Pro Grid defaults', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'Responsive columns, search and optional card styling for newly inserted Flag Grid blocks.', 'svg-flags-lite' ); ?></p></div></div>
						<div class="svg-flags-admin__form-grid">
							<label><span><?php esc_html_e( 'Tablet columns', 'svg-flags-lite' ); ?></span><input type="number" min="1" max="8" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_grid_columns_tablet]" value="<?php echo esc_attr( $options['pro_grid_columns_tablet'] ); ?>"></label>
							<label><span><?php esc_html_e( 'Mobile columns', 'svg-flags-lite' ); ?></span><input type="number" min="1" max="8" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_grid_columns_mobile]" value="<?php echo esc_attr( $options['pro_grid_columns_mobile'] ); ?>"></label>
							<label class="svg-flags-admin__checkbox"><input type="checkbox" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_grid_search]" value="1" <?php checked( $options['pro_grid_search'] ); ?>><span><?php esc_html_e( 'Show search field', 'svg-flags-lite' ); ?></span></label>
							<label class="svg-flags-admin__checkbox"><input type="checkbox" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_grid_filter]" value="1" <?php checked( $options['pro_grid_filter'] ); ?>><span><?php esc_html_e( 'Show region filter', 'svg-flags-lite' ); ?></span></label>
							<label class="svg-flags-admin__checkbox"><input type="checkbox" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_grid_card_style]" value="1" <?php checked( $options['pro_grid_card_style'] ); ?>><span><?php esc_html_e( 'Use card styling', 'svg-flags-lite' ); ?></span></label>
							<?php $this->render_color_input( 'pro_grid_card_background', __( 'Card background', 'svg-flags-lite' ), $options['pro_grid_card_background'] ); ?>
							<?php $this->render_color_input( 'pro_grid_card_border', __( 'Card border', 'svg-flags-lite' ), $options['pro_grid_card_border'] ); ?>
							<label><span><?php esc_html_e( 'Card corner radius (px)', 'svg-flags-lite' ); ?></span><input type="number" min="0" max="100" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_grid_card_radius]" value="<?php echo esc_attr( $options['pro_grid_card_radius'] ); ?>"></label>
							<label><span><?php esc_html_e( 'Card padding (px)', 'svg-flags-lite' ); ?></span><input type="number" min="0" max="100" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_grid_card_padding]" value="<?php echo esc_attr( $options['pro_grid_card_padding'] ); ?>"></label>
						</div>
					</section>

					<section class="svg-flags-admin__settings-panel" aria-labelledby="svg-flags-card-defaults-title">
						<div class="svg-flags-admin__settings-heading"><span class="dashicons dashicons-admin-links" aria-hidden="true"></span><div><h2 id="svg-flags-card-defaults-title"><?php esc_html_e( 'Linked Flag Card defaults', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'The starting layout and visual treatment for new country cards.', 'svg-flags-lite' ); ?></p></div></div>
						<div class="svg-flags-admin__form-grid">
							<label><span><?php esc_html_e( 'Layout', 'svg-flags-lite' ); ?></span><select name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_card_layout]"><option value="horizontal" <?php selected( $options['pro_card_layout'], 'horizontal' ); ?>><?php esc_html_e( 'Horizontal', 'svg-flags-lite' ); ?></option><option value="vertical" <?php selected( $options['pro_card_layout'], 'vertical' ); ?>><?php esc_html_e( 'Vertical', 'svg-flags-lite' ); ?></option></select></label>
							<div class="svg-flags-admin__field-pair"><label><span><?php esc_html_e( 'Flag width', 'svg-flags-lite' ); ?></span><input type="number" min="0.25" max="1000" step="0.25" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_card_flag_size]" value="<?php echo esc_attr( $options['pro_card_flag_size'] ); ?>"></label><?php $this->render_unit_select( 'pro_card_flag_size_unit', $options['pro_card_flag_size_unit'] ); ?></div>
							<?php $this->render_color_input( 'pro_card_background', __( 'Background', 'svg-flags-lite' ), $options['pro_card_background'] ); ?>
							<?php $this->render_color_input( 'pro_card_text_color', __( 'Text colour', 'svg-flags-lite' ), $options['pro_card_text_color'] ); ?>
							<?php $this->render_color_input( 'pro_card_border', __( 'Border', 'svg-flags-lite' ), $options['pro_card_border'] ); ?>
							<label><span><?php esc_html_e( 'Corner radius (px)', 'svg-flags-lite' ); ?></span><input type="number" min="0" max="100" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_card_radius]" value="<?php echo esc_attr( $options['pro_card_radius'] ); ?>"></label>
							<label><span><?php esc_html_e( 'Padding (px)', 'svg-flags-lite' ); ?></span><input type="number" min="0" max="100" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_card_padding]" value="<?php echo esc_attr( $options['pro_card_padding'] ); ?>"></label>
						</div>
					</section>

					<section class="svg-flags-admin__settings-panel" aria-labelledby="svg-flags-directory-defaults-title">
						<div class="svg-flags-admin__settings-heading"><span class="dashicons dashicons-search" aria-hidden="true"></span><div><h2 id="svg-flags-directory-defaults-title"><?php esc_html_e( 'Flag Directory defaults', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'The default controls and responsive layout for a new searchable directory.', 'svg-flags-lite' ); ?></p></div></div>
						<div class="svg-flags-admin__form-grid">
							<label><span><?php esc_html_e( 'Desktop columns', 'svg-flags-lite' ); ?></span><input type="number" min="1" max="8" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_directory_columns]" value="<?php echo esc_attr( $options['pro_directory_columns'] ); ?>"></label>
							<label><span><?php esc_html_e( 'Tablet columns', 'svg-flags-lite' ); ?></span><input type="number" min="1" max="8" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_directory_columns_tablet]" value="<?php echo esc_attr( $options['pro_directory_columns_tablet'] ); ?>"></label>
							<label><span><?php esc_html_e( 'Mobile columns', 'svg-flags-lite' ); ?></span><input type="number" min="1" max="8" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_directory_columns_mobile]" value="<?php echo esc_attr( $options['pro_directory_columns_mobile'] ); ?>"></label>
							<label class="svg-flags-admin__checkbox"><input type="checkbox" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_directory_search]" value="1" <?php checked( $options['pro_directory_search'] ); ?>><span><?php esc_html_e( 'Show search field', 'svg-flags-lite' ); ?></span></label>
							<label class="svg-flags-admin__checkbox"><input type="checkbox" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_directory_filter]" value="1" <?php checked( $options['pro_directory_filter'] ); ?>><span><?php esc_html_e( 'Show region filter', 'svg-flags-lite' ); ?></span></label>
							<label class="svg-flags-admin__checkbox"><input type="checkbox" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_directory_square]" value="1" <?php checked( $options['pro_directory_square'] ); ?>><span><?php esc_html_e( 'Use square flags', 'svg-flags-lite' ); ?></span></label>
							<label class="svg-flags-admin__checkbox"><input type="checkbox" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[pro_directory_show_codes]" value="1" <?php checked( $options['pro_directory_show_codes'] ); ?>><span><?php esc_html_e( 'Show flag codes', 'svg-flags-lite' ); ?></span></label>
						</div>
					</section>
				<?php endif; ?>

				<?php submit_button( __( 'Save defaults', 'svg-flags-lite' ) ); ?>
			</form>

			<?php if ( ! $this->configuration->is_premium ) : ?>
				<section class="svg-flags-admin__settings-panel svg-flags-admin__pro-settings-preview" aria-labelledby="svg-flags-pro-settings-preview-title">
					<div class="svg-flags-admin__settings-heading"><span class="dashicons dashicons-star-filled" aria-hidden="true"></span><div><h2 id="svg-flags-pro-settings-preview-title"><?php esc_html_e( 'Save more Pro starting points', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'Pro adds real defaults for responsive grids, linked cards and searchable directories. They are written only into new blocks, so established pages stay unchanged.', 'svg-flags-lite' ); ?></p></div></div>
					<ul class="svg-flags-admin__benefit-list"><li><?php esc_html_e( 'Choose tablet and mobile columns before inserting a grid.', 'svg-flags-lite' ); ?></li><li><?php esc_html_e( 'Set the default Linked Flag Card layout, colours, radius and padding.', 'svg-flags-lite' ); ?></li><li><?php esc_html_e( 'Start every directory with search, region filtering and your preferred columns.', 'svg-flags-lite' ); ?></li></ul>
					<a class="button button-primary" href="<?php echo esc_url( $this->configuration->freemius_upgrade_url ); ?>"><?php esc_html_e( 'Explore SVG Flags Pro', 'svg-flags-lite' ); ?></a>
				</section>
			<?php endif; ?>

			<section class="svg-flags-admin__settings-panel" aria-labelledby="svg-flags-shortcodes-title">
				<div class="svg-flags-admin__settings-heading"><span class="dashicons dashicons-editor-code" aria-hidden="true"></span><div><h2 id="svg-flags-shortcodes-title"><?php esc_html_e( 'Shortcode quick reference', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'Use shortcodes in classic content, templates and page builders. Block defaults do not change shortcode output.', 'svg-flags-lite' ); ?></p></div></div>
				<div class="svg-flags-admin__shortcodes">
					<code>[svg-flag flag="gb" size="3"]</code>
					<code>[svg-flag-image flag="fr" size="96" size_unit="px" caption="true"]</code>
					<code>[svg-flag-grid flags="gb,us,ca,fr,de,jp" columns="3" caption="true"]</code>
					<?php if ( $this->configuration->is_premium ) : ?><code>[svg-flag-heading flag="de" heading="Germany" heading_tag="h2"]</code><code>[svg-flag-card flag="gb" title="United Kingdom" url="/united-kingdom/"]</code><code>[svg-flag-directory continents="Europe,Asia" show_search="true" show_filter="true"]</code><?php endif; ?>
				</div>
			</section>

			<section class="svg-flags-admin__settings-panel" aria-labelledby="svg-flags-diagnostics-title">
				<div class="svg-flags-admin__settings-heading"><span class="dashicons dashicons-sos" aria-hidden="true"></span><div><h2 id="svg-flags-diagnostics-title"><?php esc_html_e( 'Support diagnostics', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'Copy this summary when requesting support. It contains software versions and active plugin names, but no site content, URLs, settings, user data or licence details.', 'svg-flags-lite' ); ?></p></div></div>
				<textarea id="svg-flags-support-summary" class="large-text code" rows="14" readonly><?php echo esc_textarea( $this->diagnostics->get_summary() ); ?></textarea>
				<button type="button" class="button" data-svg-flags-copy="#svg-flags-support-summary"><?php esc_html_e( 'Copy support summary', 'svg-flags-lite' ); ?></button>
				<span class="svg-flags-admin__copy-status" role="status" aria-live="polite"></span>
			</section>

			<section class="svg-flags-admin__danger-zone" aria-labelledby="svg-flags-reset-title">
				<div><h2 id="svg-flags-reset-title"><?php esc_html_e( 'Reset block defaults', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'Restore the SVG Flags starting values. Existing blocks and shortcodes are not changed.', 'svg-flags-lite' ); ?></p></div>
				<a class="button" data-svg-flags-confirm="<?php esc_attr_e( 'Reset SVG Flags defaults?', 'svg-flags-lite' ); ?>" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=svg_flags_reset_settings' ), 'svg_flags_reset_settings' ) ); ?>"><?php esc_html_e( 'Reset defaults', 'svg-flags-lite' ); ?></a>
			</section>
		</div>
		<?php
	}

	/**
	 * Render a supported CSS-unit select.
	 *
	 * @param string $field Field key.
	 * @param string $selected_unit Current unit.
	 * @param bool   $allow_percentage Whether percentage is supported.
	 */
	private function render_unit_select( $field, $selected_unit, $allow_percentage = false ) {
		$units = array( 'px', 'em', 'rem', 'vw', 'vh' );
		if ( $allow_percentage ) {
			$units[] = '%';
		}
		?>
		<label>
			<span><?php esc_html_e( 'Unit', 'svg-flags-lite' ); ?></span>
			<select name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[<?php echo esc_attr( $field ); ?>]">
				<?php foreach ( $units as $unit ) : ?>
					<option value="<?php echo esc_attr( $unit ); ?>" <?php selected( $selected_unit, $unit ); ?>><?php echo esc_html( $unit ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<?php
	}

	/**
	 * Render a native colour control.
	 *
	 * @param string $field Field key.
	 * @param string $label User-facing label.
	 * @param string $value Current colour.
	 */
	private function render_color_input( $field, $label, $value ) {
		?>
		<label><span><?php echo esc_html( $label ); ?></span><input type="color" name="<?php echo esc_attr( Settings_Options::OPTION_NAME ); ?>[<?php echo esc_attr( $field ); ?>]" value="<?php echo esc_attr( $value ); ?>"></label>
		<?php
	}
}
