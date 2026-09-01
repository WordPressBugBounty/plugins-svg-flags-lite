<?php
/**
 * SVG Flags Home page and starter-draft actions.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the plugin Home page.
 */
class Home {

	/** Documentation destination. */
	const DOCUMENTATION_URL = 'https://wpgoplugins.com/document/svg-flags-documentation/';

	/** Public support destination. */
	const SUPPORT_URL = 'https://wordpress.org/support/plugin/svg-flags-lite/';

	/** Product destination. */
	const PRODUCT_URL = 'https://wpgoplugins.com/plugins/svg-flags/';

	/** Simple Sitemap product destination. */
	const SIMPLE_SITEMAP_URL = 'https://wpgoplugins.com/plugins/simple-sitemap/';

	/** ChartQuill product destination. */
	const CHARTQUILL_URL = 'https://wpgoplugins.com/plugins/chartquill/';

	/** TableQuill product destination. */
	const TABLEQUILL_URL = 'https://wpgoplugins.com/plugins/tablequill/';

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
	 * Settings model.
	 *
	 * @var Settings_Options
	 */
	private $settings;

	/**
	 * Main class constructor.
	 *
	 * @param array<string, mixed> $plugin_data Plugin headers.
	 * @param Constants            $configuration Plugin configuration.
	 * @param Settings_Options     $settings Settings model.
	 */
	public function __construct( $plugin_data, $configuration, $settings ) {
		$this->plugin_data   = $plugin_data;
		$this->configuration = $configuration;
		$this->settings      = $settings;

		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'admin_post_svg_flags_create_page', array( $this, 'create_page' ) );
	}

	/**
	 * Register the top-level menu, Home route and hidden legacy routes.
	 */
	public function add_menu_pages() {
		add_menu_page(
			__( 'SVG Flags Home', 'svg-flags-lite' ),
			__( 'SVG Flags', 'svg-flags-lite' ),
			'manage_options',
			$this->configuration->home_slug,
			array( $this, 'render_page' ),
			'dashicons-flag',
			58
		);

		add_submenu_page(
			$this->configuration->plugin_slug,
			__( 'SVG Flags Home', 'svg-flags-lite' ),
			__( 'Home', 'svg-flags-lite' ),
			'manage_options',
			$this->configuration->home_slug,
			array( $this, 'render_page' )
		);

		foreach ( array( $this->configuration->plugin_slug, $this->configuration->plugin_slug . '-welcome' ) as $legacy_slug ) {
			add_submenu_page(
				'options-general.php',
				__( 'SVG Flags Home', 'svg-flags-lite' ),
				__( 'SVG Flags', 'svg-flags-lite' ),
				'manage_options',
				$legacy_slug,
				array( $this, 'render_page' )
			);
			remove_submenu_page( 'options-general.php', $legacy_slug );
		}
	}

	/**
	 * Create a private starter Page containing the selected block.
	 */
	public function create_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to create an SVG Flags starter page.', 'svg-flags-lite' ) );
		}

		check_admin_referer( 'svg_flags_create_page' );

		$layout = isset( $_GET['layout'] ) ? sanitize_key( wp_unslash( $_GET['layout'] ) ) : 'grid';
		if ( $this->is_premium_layout( $layout ) && ! $this->configuration->is_premium ) {
			wp_safe_redirect( add_query_arg( 'svg_flags_pro_required', '1', $this->configuration->home_url ) );
			exit;
		}

		$starter = $this->get_starter_definition( $layout );
		if ( null === $starter ) {
			wp_safe_redirect( add_query_arg( 'svg_flags_create_error', '1', $this->configuration->home_url ) );
			exit;
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $starter['title'],
				'post_content' => wp_slash( $starter['content'] ),
				'post_status'  => 'draft',
				'post_type'    => 'page',
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			wp_safe_redirect( add_query_arg( 'svg_flags_create_error', '1', $this->configuration->home_url ) );
			exit;
		}

		$edit_url = get_edit_post_link( $post_id, 'raw' );
		wp_safe_redirect( $edit_url ? $edit_url : admin_url( 'edit.php?post_type=page' ) );
		exit;
	}

	/**
	 * Render the Home page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage SVG Flags.', 'svg-flags-lite' ) );
		}

		$is_premium   = $this->configuration->is_premium;
		$image_root   = trailingslashit( Main::$module_roots['uri'] ) . 'assets/images/';
		$free_actions = $this->get_free_feature_actions();
		$pro_actions  = $this->get_pro_feature_actions();
		$actions      = $free_actions;

		if ( $is_premium ) {
			$actions = array_merge( $actions, $pro_actions );
		}
		?>
		<div class="wrap svg-flags-admin svg-flags-admin--home">
			<?php Admin_View::render_header( $this->plugin_data, $this->configuration, __( 'Home', 'svg-flags-lite' ) ); ?>

			<?php if ( isset( $_GET['svg_flags_create_error'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'WordPress could not create the starter Page. Please try again.', 'svg-flags-lite' ); ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['svg_flags_pro_required'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-warning"><p><?php esc_html_e( 'This starter requires an active SVG Flags Pro licence.', 'svg-flags-lite' ); ?></p></div>
			<?php endif; ?>

			<div class="svg-flags-admin__top-grid">
				<section class="svg-flags-admin__panel svg-flags-admin__start" aria-labelledby="svg-flags-start-title">
					<h2 id="svg-flags-start-title"><?php esc_html_e( 'Start here', 'svg-flags-lite' ); ?></h2>
					<div class="svg-flags-admin__start-body">
						<div class="svg-flags-admin__start-media">
							<img src="<?php echo esc_url( $image_root . 'screenshots/svg-flags-grid-editor.png' ); ?>" alt="<?php esc_attr_e( 'An SVG Flag Grid selected in the WordPress editor with its country and layout controls visible.', 'svg-flags-lite' ); ?>">
						</div>
						<div class="svg-flags-admin__start-copy">
							<h3><?php esc_html_e( 'Create a flag gallery', 'svg-flags-lite' ); ?></h3>
							<p><?php esc_html_e( 'We’ll create a ready-to-edit draft Page with an SVG Flag Grid already inserted. Choose the countries, review the layout and publish when you are ready.', 'svg-flags-lite' ); ?></p>
							<a class="button button-primary button-hero" href="<?php echo esc_url( $this->get_create_page_url( 'grid' ) ); ?>"><?php esc_html_e( 'Create flag gallery', 'svg-flags-lite' ); ?></a>
							<p class="svg-flags-admin__starter-hint"><?php esc_html_e( 'Creates a draft only. Choose from more ready-made examples below.', 'svg-flags-lite' ); ?></p>
						</div>
					</div>
				</section>

				<aside class="svg-flags-admin__panel svg-flags-admin__links" aria-labelledby="svg-flags-links-title">
					<h2 id="svg-flags-links-title"><?php esc_html_e( 'Quick links', 'svg-flags-lite' ); ?></h2>
					<nav aria-label="<?php esc_attr_e( 'SVG Flags resources', 'svg-flags-lite' ); ?>">
						<a href="<?php echo esc_url( self::DOCUMENTATION_URL ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-book" aria-hidden="true"></span><span><?php esc_html_e( 'Documentation', 'svg-flags-lite' ); ?></span><?php Admin_View::external_link_text(); ?></a>
						<a href="<?php echo esc_url( $this->configuration->new_features_url ); ?>"><span class="dashicons dashicons-media-document" aria-hidden="true"></span><span><?php esc_html_e( 'New features', 'svg-flags-lite' ); ?></span><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a>
						<a href="<?php echo esc_url( $this->configuration->main_settings_url ); ?>"><span class="dashicons dashicons-admin-settings" aria-hidden="true"></span><span><?php esc_html_e( 'Settings', 'svg-flags-lite' ); ?></span><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a>
						<?php if ( $is_premium ) : ?>
							<a href="<?php echo esc_url( $this->configuration->contact_us_url ); ?>"><span class="dashicons dashicons-sos" aria-hidden="true"></span><span><?php esc_html_e( 'Contact Pro support', 'svg-flags-lite' ); ?></span><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a>
						<?php else : ?>
							<a href="<?php echo esc_url( self::SUPPORT_URL ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-sos" aria-hidden="true"></span><span><?php esc_html_e( 'Support forum', 'svg-flags-lite' ); ?></span><?php Admin_View::external_link_text(); ?></a>
						<?php endif; ?>
					</nav>
				</aside>
			</div>

			<section class="svg-flags-admin__section" aria-labelledby="svg-flags-quick-start-title">
				<div class="svg-flags-admin__section-heading">
					<div><span class="svg-flags-admin__eyebrow"><?php esc_html_e( 'Quick start', 'svg-flags-lite' ); ?></span><h2 id="svg-flags-quick-start-title"><?php esc_html_e( 'From draft to finished flag collection', 'svg-flags-lite' ); ?></h2></div>
					<a href="<?php echo esc_url( self::DOCUMENTATION_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Read the full guide', 'svg-flags-lite' ); ?> <?php Admin_View::external_link_text(); ?></a>
				</div>
				<div class="svg-flags-admin__steps">
					<article><span class="svg-flags-admin__step-number">1</span><img src="<?php echo esc_url( $image_root . 'svg-flag-block-test-insert.png' ); ?>" alt="<?php esc_attr_e( 'SVG Flags blocks available in the WordPress block inserter.', 'svg-flags-lite' ); ?>"><h3><?php esc_html_e( 'Start with the right block', 'svg-flags-lite' ); ?></h3><p><?php esc_html_e( 'Use the prepared draft or insert a Flag, Flag Image or Flag Grid block yourself.', 'svg-flags-lite' ); ?></p></article>
					<article><span class="svg-flags-admin__step-number">2</span><img src="<?php echo esc_url( $image_root . 'svg-flag-block-test-free-settings.png' ); ?>" alt="<?php esc_attr_e( 'SVG Flag block controls for choosing a country and changing its dimensions.', 'svg-flags-lite' ); ?>"><h3><?php esc_html_e( 'Choose and arrange flags', 'svg-flags-lite' ); ?></h3><p><?php esc_html_e( 'Search 271 country, territory, regional and organisation flags, then adjust the layout.', 'svg-flags-lite' ); ?></p></article>
					<article><span class="svg-flags-admin__step-number">3</span><img src="<?php echo esc_url( $image_root . 'screenshots/svg-flags-frontend.png' ); ?>" alt="<?php esc_attr_e( 'A published page containing SVG flag examples and a flag gallery.', 'svg-flags-lite' ); ?>"><h3><?php esc_html_e( 'Review and publish', 'svg-flags-lite' ); ?></h3><p><?php esc_html_e( 'Preview the accessible local SVG output, then publish when the page is ready.', 'svg-flags-lite' ); ?></p></article>
				</div>
			</section>

			<section class="svg-flags-admin__section" aria-labelledby="svg-flags-features-title">
				<div class="svg-flags-admin__section-heading">
					<div>
						<span class="svg-flags-admin__eyebrow svg-flags-admin__eyebrow--free"><?php echo esc_html( $is_premium ? __( 'Your flag toolkit', 'svg-flags-lite' ) : __( 'Included with Free', 'svg-flags-lite' ) ); ?></span>
						<h2 id="svg-flags-features-title"><?php echo esc_html( $is_premium ? __( 'Create with every ready-made example', 'svg-flags-lite' ) : __( 'Start with a prepared flag layout', 'svg-flags-lite' ) ); ?></h2>
						<p><?php esc_html_e( 'Each create action opens a private draft with the chosen example already inserted. Settings apply only to newly inserted blocks.', 'svg-flags-lite' ); ?></p>
					</div>
				</div>
				<div class="svg-flags-admin__action-grid">
					<?php foreach ( $actions as $action ) : ?>
						<article class="svg-flags-admin__action-card<?php echo 'pro' === $action['edition'] ? ' svg-flags-admin__action-card--pro' : ''; ?>">
							<?php if ( ! $is_premium ) : ?>
								<span class="svg-flags-admin__action-edition svg-flags-admin__action-edition--free"><?php esc_html_e( 'Free', 'svg-flags-lite' ); ?></span>
							<?php endif; ?>
							<span class="dashicons dashicons-<?php echo esc_attr( $action['icon'] ); ?>" aria-hidden="true"></span>
							<h3><?php echo esc_html( $action['title'] ); ?></h3>
							<p><?php echo esc_html( $action['description'] ); ?></p>
							<a class="button" href="<?php echo esc_url( $action['url'] ); ?>"><?php echo esc_html( $action['label'] ); ?></a>
						</article>
					<?php endforeach; ?>
				</div>
			</section>

			<?php if ( ! $is_premium ) : ?>
				<section class="svg-flags-admin__section svg-flags-admin__pro-showcase" aria-labelledby="svg-flags-pro-title">
					<div class="svg-flags-admin__section-heading">
						<div>
							<span class="svg-flags-admin__eyebrow svg-flags-admin__eyebrow--pro"><?php esc_html_e( 'Available in Pro', 'svg-flags-lite' ); ?></span>
							<h2 id="svg-flags-pro-title"><?php esc_html_e( 'More ready-made Pro examples', 'svg-flags-lite' ); ?></h2>
							<p><?php esc_html_e( 'Unlock presentation controls and create an editable draft for each Pro example.', 'svg-flags-lite' ); ?></p>
						</div>
					</div>
					<div class="svg-flags-admin__action-grid">
						<?php foreach ( $pro_actions as $action ) : ?>
							<article class="svg-flags-admin__action-card svg-flags-admin__action-card--pro">
								<span class="svg-flags-admin__action-edition"><?php esc_html_e( 'Pro', 'svg-flags-lite' ); ?></span>
								<span class="dashicons dashicons-<?php echo esc_attr( $action['icon'] ); ?>" aria-hidden="true"></span>
								<h3><?php echo esc_html( $action['title'] ); ?></h3>
								<p><?php echo esc_html( $action['description'] ); ?></p>
								<a class="button" href="<?php echo esc_url( $this->configuration->freemius_upgrade_url ); ?>"><?php esc_html_e( 'Explore Pro', 'svg-flags-lite' ); ?></a>
							</article>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="svg-flags-admin__upgrade-panel" aria-labelledby="svg-flags-upgrade-title">
					<div><span class="dashicons dashicons-star-filled" aria-hidden="true"></span><h2 id="svg-flags-upgrade-title"><?php esc_html_e( 'Need more control over presentation?', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'SVG Flags Pro adds linked cards, responsive linked grids, searchable directories, custom captions and deeper presentation controls.', 'svg-flags-lite' ); ?></p></div>
					<a class="button button-primary" href="<?php echo esc_url( $this->configuration->freemius_upgrade_url ); ?>"><?php esc_html_e( 'Explore SVG Flags Pro', 'svg-flags-lite' ); ?></a>
				</section>
			<?php endif; ?>

			<section class="svg-flags-admin__support" aria-labelledby="svg-flags-support-title">
				<div><h2 id="svg-flags-support-title"><?php esc_html_e( 'Need help or have an idea?', 'svg-flags-lite' ); ?></h2><p><?php esc_html_e( 'Report a problem, describe a country-display workflow or suggest the next useful feature.', 'svg-flags-lite' ); ?></p></div>
				<div class="svg-flags-admin__actions"><a class="button" href="<?php echo esc_url( $this->configuration->contact_us_url ); ?>"><?php esc_html_e( 'Contact WPGO Plugins', 'svg-flags-lite' ); ?></a><a class="button" href="<?php echo esc_url( self::PRODUCT_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View product page', 'svg-flags-lite' ); ?> <?php Admin_View::external_link_text(); ?></a></div>
			</section>

			<?php $this->render_companion_plugins(); ?>
		</div>
		<?php
	}

	/**
	 * Return the ready-made Free examples.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function get_free_feature_actions() {
		return array(
			array(
				'edition'     => 'free',
				'icon'        => 'grid-view',
				'title'       => __( 'Flag gallery', 'svg-flags-lite' ),
				'description' => __( 'Create a balanced six-country gallery using your saved grid defaults.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'grid' ),
				'label'       => __( 'Create gallery draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'free',
				'icon'        => 'admin-site-alt3',
				'title'       => __( 'European flags', 'svg-flags-lite' ),
				'description' => __( 'Start with Europe, the United Kingdom, Ireland, France, Germany, Spain, Italy and the Netherlands.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'europe' ),
				'label'       => __( 'Create Europe draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'free',
				'icon'        => 'admin-site',
				'title'       => __( 'Flags of the Americas', 'svg-flags-lite' ),
				'description' => __( 'Create a captioned gallery for the United States, Canada, Mexico, Brazil, Argentina and Chile.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'americas' ),
				'label'       => __( 'Create Americas draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'free',
				'icon'        => 'admin-site-alt2',
				'title'       => __( 'Asia-Pacific flags', 'svg-flags-lite' ),
				'description' => __( 'Prepare a regional gallery for Japan, China, India, South Korea, Australia, New Zealand and ASEAN.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'asia-pacific' ),
				'label'       => __( 'Create Asia-Pacific draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'free',
				'icon'        => 'groups',
				'title'       => __( 'Organisation flags', 'svg-flags-lite' ),
				'description' => __( 'Show the European Union, United Nations and ASEAN in a clear three-column layout.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'organisations' ),
				'label'       => __( 'Create organisation draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'free',
				'icon'        => 'ellipsis',
				'title'       => __( 'Compact flag strip', 'svg-flags-lite' ),
				'description' => __( 'Fit eight familiar flags into one compact row without captions.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'compact-grid' ),
				'label'       => __( 'Create compact draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'free',
				'icon'        => 'screenoptions',
				'title'       => __( 'Square flag gallery', 'svg-flags-lite' ),
				'description' => __( 'Create a tidy two-row gallery using the square flag presentation.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'square-grid' ),
				'label'       => __( 'Create square draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'free',
				'icon'        => 'format-image',
				'title'       => __( 'Country profile flag', 'svg-flags-lite' ),
				'description' => __( 'Insert a larger semantic flag image with its accessible country caption.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'profile' ),
				'label'       => __( 'Create profile draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'free',
				'icon'        => 'flag',
				'title'       => __( 'Inline country flag', 'svg-flags-lite' ),
				'description' => __( 'Insert a compact flag ready to sit naturally beside text or other inline content.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'inline' ),
				'label'       => __( 'Create inline draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'free',
				'icon'        => 'admin-settings',
				'title'       => __( 'Defaults and shortcodes', 'svg-flags-lite' ),
				'description' => __( 'Set defaults for future blocks and review examples for classic content and templates.', 'svg-flags-lite' ),
				'url'         => $this->configuration->main_settings_url,
				'label'       => __( 'Open settings', 'svg-flags-lite' ),
			),
		);
	}

	/**
	 * Return the ready-made Pro examples.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function get_pro_feature_actions() {
		return array(
			array(
				'edition'     => 'pro',
				'icon'        => 'heading',
				'title'       => __( 'Flag heading', 'svg-flags-lite' ),
				'description' => __( 'Pair a decorative flag with an accessible H1–H6 heading and Pro presentation controls.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'heading' ),
				'label'       => __( 'Create heading draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'pro',
				'icon'        => 'format-image',
				'title'       => __( 'Framed profile flag', 'svg-flags-lite' ),
				'description' => __( 'Start with a bordered, rounded and padded flag image for a profile or location card.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'framed-profile' ),
				'label'       => __( 'Create framed draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'pro',
				'icon'        => 'editor-quote',
				'title'       => __( 'Custom caption', 'svg-flags-lite' ),
				'description' => __( 'Create a flag image with a purpose-specific caption instead of the default country name.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'custom-caption' ),
				'label'       => __( 'Create caption draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'pro',
				'icon'        => 'info-outline',
				'title'       => __( 'Flag with tooltip', 'svg-flags-lite' ),
				'description' => __( 'Add concise hover text plus a reusable ID and class for custom site styling.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'tooltip-flag' ),
				'label'       => __( 'Create tooltip draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'pro',
				'icon'        => 'admin-links',
				'title'       => __( 'Linked flag card', 'svg-flags-lite' ),
				'description' => __( 'Create a polished country card with a flag, title, supporting text and one destination link.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'linked-card' ),
				'label'       => __( 'Create card draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'pro',
				'icon'        => 'grid-view',
				'title'       => __( 'Linked regional grid', 'svg-flags-lite' ),
				'description' => __( 'Start with a responsive flag grid whose cards can use custom captions and destination links.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'linked-grid' ),
				'label'       => __( 'Create linked-grid draft', 'svg-flags-lite' ),
			),
			array(
				'edition'     => 'pro',
				'icon'        => 'search',
				'title'       => __( 'Searchable flag directory', 'svg-flags-lite' ),
				'description' => __( 'Insert the complete flag catalogue with live search, region filtering and responsive columns.', 'svg-flags-lite' ),
				'url'         => $this->get_create_page_url( 'directory' ),
				'label'       => __( 'Create directory draft', 'svg-flags-lite' ),
			),
		);
	}

	/**
	 * Build a nonce-protected starter action URL.
	 *
	 * @param string $layout Starter layout key.
	 * @return string
	 */
	private function get_create_page_url( $layout ) {
		$url = add_query_arg(
			array(
				'action' => 'svg_flags_create_page',
				'layout' => $layout,
			),
			admin_url( 'admin-post.php' )
		);

		return wp_nonce_url( $url, 'svg_flags_create_page' );
	}

	/**
	 * Return one starter title and serialized block.
	 *
	 * @param string $layout Starter layout key.
	 * @return array{title: string, content: string}|null
	 */
	private function get_starter_definition( $layout ) {
		$defaults = $this->settings->block_defaults();
		$starters = array(
			'grid'           => array(
				'title' => __( 'Flag gallery', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-grid',
				'attrs' => $defaults['grid'],
			),
			'europe'         => array(
				'title' => __( 'European flag gallery', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-grid',
				'attrs' => array_merge(
					$defaults['grid'],
					array(
						'flags'   => array( 'EU', 'GB', 'IE', 'FR', 'DE', 'ES', 'IT', 'NL' ),
						'columns' => 4,
						'size'    => '6',
					)
				),
			),
			'americas'       => array(
				'title' => __( 'Flags of the Americas', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-grid',
				'attrs' => array_merge(
					$defaults['grid'],
					array(
						'flags'   => array( 'US', 'CA', 'MX', 'BR', 'AR', 'CL' ),
						'columns' => 3,
					)
				),
			),
			'asia-pacific'   => array(
				'title' => __( 'Asia-Pacific flag gallery', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-grid',
				'attrs' => array_merge(
					$defaults['grid'],
					array(
						'flags'   => array( 'JP', 'CN', 'IN', 'KR', 'AU', 'NZ', 'ASEAN' ),
						'columns' => 4,
						'size'    => '6',
					)
				),
			),
			'organisations'  => array(
				'title' => __( 'International organisation flags', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-grid',
				'attrs' => array_merge(
					$defaults['grid'],
					array(
						'flags'   => array( 'EU', 'UN', 'ASEAN' ),
						'columns' => 3,
						'size'    => '7',
					)
				),
			),
			'compact-grid'   => array(
				'title' => __( 'Compact flag strip', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-grid',
				'attrs' => array_merge(
					$defaults['grid'],
					array(
						'flags'   => array( 'GB', 'US', 'CA', 'FR', 'DE', 'JP', 'AU', 'BR' ),
						'columns' => 8,
						'gap'     => '0.5',
						'size'    => '4',
						'caption' => false,
					)
				),
			),
			'square-grid'    => array(
				'title' => __( 'Square flag gallery', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-grid',
				'attrs' => array_merge(
					$defaults['grid'],
					array(
						'flags'   => array( 'GB', 'US', 'CA', 'FR', 'DE', 'JP' ),
						'columns' => 3,
						'square'  => true,
					)
				),
			),
			'profile'        => array(
				'title' => __( 'Country profile flag', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-image',
				'attrs' => array_merge(
					$defaults['flagImage'],
					array(
						'flag'      => $this->get_flag_attribute( 'GB' ),
						'size'      => '12',
						'size_unit' => 'rem',
						'caption'   => true,
					)
				),
			),
			'image'          => array(
				'title' => __( 'Flag image', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-image',
				'attrs' => $defaults['flagImage'],
			),
			'inline'         => array(
				'title' => __( 'Inline country flag', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag',
				'attrs' => array_merge(
					$defaults['flag'],
					array(
						'flag'         => $this->get_flag_attribute( 'GB' ),
						'size'         => '1.5',
						'size_unit'    => 'em',
						'inline'       => true,
						'inline_valign' => 'middle',
						'caption'      => false,
					)
				),
			),
			'flag'           => array(
				'title' => __( 'Country flag', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag',
				'attrs' => $defaults['flag'],
			),
			'heading'        => array(
				'title' => __( 'Flag heading', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-heading',
				'attrs' => array_merge(
					$defaults['flagHeading'],
					array(
						'flag'        => $this->get_flag_attribute( 'FR' ),
						'heading'     => __( 'France office', 'svg-flags-lite' ),
						'heading_tag' => 'h2',
					)
				),
			),
			'framed-profile' => array(
				'title' => __( 'Framed profile flag', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-image',
				'attrs' => array_merge(
					$defaults['flagImage'],
					array(
						'flag'          => $this->get_flag_attribute( 'CA' ),
						'size'          => '12',
						'size_unit'     => 'rem',
						'caption'       => true,
						'border'        => '2px solid #d63638',
						'border_radius' => '12px',
						'padding'       => '8px',
						'flag_class'    => 'location-profile-flag',
					)
				),
			),
			'custom-caption' => array(
				'title' => __( 'Custom-caption flag', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-image',
				'attrs' => array_merge(
					$defaults['flagImage'],
					array(
						'flag'           => $this->get_flag_attribute( 'US' ),
						'size'           => '10',
						'size_unit'      => 'rem',
						'caption'        => true,
						'custom_caption' => __( 'North America team', 'svg-flags-lite' ),
					)
				),
			),
			'tooltip-flag'   => array(
				'title' => __( 'Flag with tooltip', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag',
				'attrs' => array_merge(
					$defaults['flag'],
					array(
						'flag'           => $this->get_flag_attribute( 'JP' ),
						'size'           => '4',
						'size_unit'      => 'rem',
						'tooltip'        => true,
						'custom_tooltip' => __( 'Japan office', 'svg-flags-lite' ),
						'id'             => 'japan-office-flag',
						'flag_class'     => 'office-flag',
					)
				),
			),
			'linked-card'    => array(
				'title' => __( 'Linked country card', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-card',
				'attrs' => array_merge(
					$defaults['flagCard'] ?? array(),
					array(
						'flag'        => $this->get_flag_attribute( 'GB' ),
						'title'       => __( 'United Kingdom', 'svg-flags-lite' ),
						'description' => __( 'Meet the team serving customers across the United Kingdom.', 'svg-flags-lite' ),
						'url'         => home_url( '/united-kingdom/' ),
					)
				),
			),
			'linked-grid'    => array(
				'title' => __( 'Linked regional flag grid', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-grid',
				'attrs' => array_merge(
					$defaults['grid'],
					array(
						'flags'           => array( 'GB', 'US', 'CA', 'FR', 'DE', 'JP' ),
						'columns'         => 3,
						'columns_tablet'  => 2,
						'columns_mobile'  => 1,
						'card_style'      => true,
						'item_data'       => array(
							'GB' => array( 'caption' => __( 'United Kingdom office', 'svg-flags-lite' ), 'url' => home_url( '/united-kingdom/' ) ),
							'US' => array( 'caption' => __( 'United States office', 'svg-flags-lite' ), 'url' => home_url( '/united-states/' ) ),
							'CA' => array( 'caption' => __( 'Canada office', 'svg-flags-lite' ), 'url' => home_url( '/canada/' ) ),
						),
					)
				),
			),
			'directory'      => array(
				'title' => __( 'Searchable flag directory', 'svg-flags-lite' ),
				'block' => 'svg-flags/svg-flag-directory',
				'attrs' => array_merge(
					$defaults['directory'] ?? array(),
					array(
						'show_search' => true,
						'show_filter' => true,
					)
				),
			),
		);

		if ( ! isset( $starters[ $layout ] ) ) {
			return null;
		}

		$starter = $starters[ $layout ];
		$content = serialize_block(
			array(
				'blockName'    => $starter['block'],
				'attrs'        => $starter['attrs'],
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);

		return array(
			'title'   => $starter['title'],
			'content' => $content,
		);
	}

	/**
	 * Check whether a starter uses Pro-only controls or blocks.
	 *
	 * @param string $layout Starter layout key.
	 * @return bool
	 */
	private function is_premium_layout( $layout ) {
		return in_array( $layout, array( 'heading', 'framed-profile', 'custom-caption', 'tooltip-flag', 'linked-card', 'linked-grid', 'directory' ), true );
	}

	/**
	 * Render the shared WPGO companion-product section.
	 */
	private function render_companion_plugins() {
		$plugins = array(
			array(
				'icon'        => 'networking',
				'title'       => __( 'Simple Sitemap', 'svg-flags-lite' ),
				'description' => __( 'Create visitor-friendly HTML sitemaps with blocks and shortcodes.', 'svg-flags-lite' ),
				'url'         => self::SIMPLE_SITEMAP_URL,
				'action'      => __( 'Explore Simple Sitemap', 'svg-flags-lite' ),
			),
			array(
				'icon'        => 'chart-bar',
				'title'       => __( 'ChartQuill', 'svg-flags-lite' ),
				'description' => __( 'Create responsive, accessible charts from WordPress or imported data.', 'svg-flags-lite' ),
				'url'         => self::CHARTQUILL_URL,
				'action'      => __( 'Explore ChartQuill', 'svg-flags-lite' ),
			),
			array(
				'icon'        => 'editor-table',
				'title'       => __( 'TableQuill', 'svg-flags-lite' ),
				'description' => __( 'Build reusable, responsive tables from WordPress content or imported data.', 'svg-flags-lite' ),
				'url'         => self::TABLEQUILL_URL,
				'action'      => __( 'Explore TableQuill', 'svg-flags-lite' ),
			),
		);
		?>
		<section class="svg-flags-admin__companions" aria-labelledby="svg-flags-companions-title">
			<div class="svg-flags-admin__section-heading">
				<div>
					<span class="svg-flags-admin__eyebrow"><?php esc_html_e( 'More from WPGO Plugins', 'svg-flags-lite' ); ?></span>
					<h2 id="svg-flags-companions-title"><?php esc_html_e( 'Build a clearer, more useful WordPress site', 'svg-flags-lite' ); ?></h2>
					<p><?php esc_html_e( 'These companion plugins help visitors navigate content and understand data alongside your flag-powered pages.', 'svg-flags-lite' ); ?></p>
				</div>
			</div>
			<div class="svg-flags-admin__companion-grid">
				<?php foreach ( $plugins as $plugin ) : ?>
					<article class="svg-flags-admin__companion-card">
						<span class="svg-flags-admin__companion-icon dashicons dashicons-<?php echo esc_attr( $plugin['icon'] ); ?>" aria-hidden="true"></span>
						<div class="svg-flags-admin__companion-copy"><h3><?php echo esc_html( $plugin['title'] ); ?></h3><p><?php echo esc_html( $plugin['description'] ); ?></p></div>
						<a class="button" href="<?php echo esc_url( $plugin['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $plugin['action'] ); ?> <?php Admin_View::external_link_text(); ?></a>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Serialize one known flag selection for a block attribute.
	 *
	 * @param string $code Flag code.
	 * @return string
	 */
	private function get_flag_attribute( $code ) {
		$code  = strtoupper( $code );
		$label = $this->configuration->country_codes[ $code ] ?? $code;

		return wp_json_encode(
			array(
				'value' => $code,
				'label' => $label,
			)
		);
	}
}
