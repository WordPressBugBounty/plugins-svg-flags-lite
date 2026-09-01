<?php
/**
 * Shared SVG Flags admin-page presentation helpers.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the product header shared by Home, Settings and New Features.
 */
class Admin_View {

	/**
	 * Render the standard product header.
	 *
	 * @param array<string, mixed> $plugin_data Plugin headers.
	 * @param Constants            $configuration Plugin configuration.
	 * @param string               $page_name Page label.
	 */
	public static function render_header( $plugin_data, $configuration, $page_name ) {
		$version    = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '';
		$edition    = $configuration->is_premium ? __( 'Pro', 'svg-flags-lite' ) : __( 'Free', 'svg-flags-lite' );
		$plugin_uri = trailingslashit( Main::$module_roots['uri'] );
		$feedback   = add_query_arg(
			array(
				'topic'   => 'feature_request',
				'summary' => 'SVG Flags plugin feedback',
			),
			$configuration->contact_us_url
		);
		?>
		<header class="svg-flags-admin__header">
			<div class="svg-flags-admin__brand">
				<img src="<?php echo esc_url( $plugin_uri . 'assets/images/svg-flags.png' ); ?>" alt="" width="64" height="64">
				<div>
					<h1>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: admin page name. */
								__( 'SVG Flags %s', 'svg-flags-lite' ),
								$page_name
							)
						);
						?>
					</h1>
					<div class="svg-flags-admin__meta">
						<a href="<?php echo esc_url( $configuration->new_features_url ); ?>">v<?php echo esc_html( $version ); ?></a>
						<span class="svg-flags-admin__edition svg-flags-admin__edition--<?php echo $configuration->is_premium ? 'pro' : 'free'; ?>"><?php echo esc_html( $edition ); ?></span>
					</div>
				</div>
			</div>
			<aside class="svg-flags-admin__feedback" aria-label="<?php esc_attr_e( 'SVG Flags feedback', 'svg-flags-lite' ); ?>">
				<span class="dashicons dashicons-format-chat" aria-hidden="true"></span>
				<span>
					<strong><?php esc_html_e( 'Help improve SVG Flags', 'svg-flags-lite' ); ?></strong>
					<small><?php esc_html_e( 'Your feedback helps us improve SVG Flags.', 'svg-flags-lite' ); ?></small>
				</span>
				<a class="button" href="<?php echo esc_url( $feedback ); ?>"><?php esc_html_e( 'Share feedback', 'svg-flags-lite' ); ?></a>
			</aside>
		</header>
		<?php
	}

	/**
	 * Render accessible external-link text.
	 */
	public static function external_link_text() {
		?>
		<span class="dashicons dashicons-external" aria-hidden="true"></span><span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'svg-flags-lite' ); ?></span>
		<?php
	}
}
