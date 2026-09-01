<?php

namespace WPGO_Plugins\SVG_Flags;

/**
 * Render the flag grid shortcode and dynamic block.
 */
class SVG_Flag_Grid_Shortcode {

	protected static $instance;
	protected $module_roots;
	protected $custom_plugin_data;
	protected $country_codes;

	/**
	 * Main class constructor.
	 */
	protected function __construct( $module_roots, $custom_plugin_data ) {
		$this->module_roots       = $module_roots;
		$this->custom_plugin_data = $custom_plugin_data;
		$this->country_codes      = $custom_plugin_data->country_codes;

		add_shortcode( 'svg-flag-grid', array( $this, 'render_svg_flag_grid_shortcode' ) );
	}

	/**
	 * Create the shared renderer instance.
	 */
	public static function create_instance( $module_roots, $custom_plugin_data ) {
		if ( ! self::$instance ) {
			self::$instance = new self( $module_roots, $custom_plugin_data );
		}

		return self::$instance;
	}

	/**
	 * Return the shared renderer instance.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			return null;
		}

		return self::$instance;
	}

	/**
	 * Render the dynamic block.
	 */
	public function render_svg_flag_grid_block( $attributes ) {
		$attributes['gutenberg_block'] = true;

		return $this->render_svg_flag_grid( $attributes );
	}

	/**
	 * Render the shortcode.
	 */
	public function render_svg_flag_grid_shortcode( $attributes ) {
		$attributes = is_array( $attributes ) ? $attributes : array();
		$attributes['gutenberg_block'] = false;

		return $this->render_svg_flag_grid( $attributes );
	}

	/**
	 * Build a responsive grid of unique, validated flag images.
	 */
	public function render_svg_flag_grid( $attributes ) {
		$defaults = array(
			'flags'      => 'gb,us,ca,fr,de,jp',
			'columns'    => 3,
			'gap'        => '1',
			'gap_unit'   => 'rem',
			'size'       => '8',
			'size_unit'  => 'rem',
			'square'     => false,
			'caption'    => true,
		);

		$is_block = isset( $attributes['gutenberg_block'] ) && true === $attributes['gutenberg_block'];
		$atts = $is_block
			? array_merge( $defaults, $attributes )
			: shortcode_atts( $defaults, $attributes, 'svg-flag-grid' );

		$flags = $this->normalize_flags( $atts['flags'] );
		$flags = apply_filters( 'svg_flag_grid_flags', $flags, $atts );
		$flags = $this->normalize_flags( $flags );

		if ( empty( $flags ) ) {
			return '';
		}

		$columns = min( 8, max( 1, absint( $atts['columns'] ) ) );
		$gap = Utility::sanitize_css_size( $atts['gap'], $atts['gap_unit'] );
		$size = Utility::sanitize_css_size( $atts['size'], $atts['size_unit'] );
		$aspect_ratio = Utility::is_truthy( $atts['square'] ) ? '1x1' : '4x3';
		$show_caption = Utility::is_truthy( $atts['caption'] );
		$grid_style = 'grid-template-columns:repeat(' . $columns . ',minmax(0,1fr));';

		if ( '' !== $gap ) {
			$grid_style .= 'gap:' . $gap . ';';
		}

		$items = '';
		foreach ( $flags as $flag ) {
			$code = strtolower( $flag );
			$name = $this->country_codes[ strtoupper( $code ) ];
			$image_url = trailingslashit( $this->module_roots['uri'] )
				. 'assets/flag-icon-css/flags/' . $aspect_ratio . '/' . $code . '.svg';
			$image_style = '' !== $size ? ' style="width:' . esc_attr( $size ) . ';"' : '';
			$caption_text = apply_filters( 'svg_flag_grid_caption_text', $name, $code, $atts );
			$caption = $show_caption
				? '<figcaption class="svg-flag-grid__caption">' . esc_html( $caption_text ) . '</figcaption>'
				: '';

			$item = '<figure class="svg-flag-grid__item">'
				. '<img class="svg-flag-grid__image"' . $image_style
				. ' src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" decoding="async">'
				. $caption
				. '</figure>';

			$items .= apply_filters( 'svg_flag_grid_item_html', $item, $code, $name, $atts );
		}

		$grid_class = apply_filters( 'svg_flag_grid_class', 'svg-flag-grid', $atts );
		$grid_style = apply_filters( 'svg_flag_grid_style', $grid_style, $atts );
		$html = '<div class="' . esc_attr( $grid_class ) . '" style="' . esc_attr( safecss_filter_attr( $grid_style ) ) . '">'
			. $items
			. '</div>';

		return apply_filters( 'svg_flag_grid_html', $html, $flags, $atts );
	}

	/**
	 * Normalize a list of country codes and cap output to a practical size.
	 */
	protected function normalize_flags( $flags ) {
		if ( ! is_array( $flags ) ) {
			$flags = preg_split( '/[\s,]+/', (string) $flags, -1, PREG_SPLIT_NO_EMPTY );
		}

		$normalized = array();
		foreach ( array_slice( $flags, 0, 100 ) as $flag ) {
			$code = strtoupper( sanitize_key( (string) $flag ) );
			if ( isset( $this->country_codes[ $code ] ) ) {
				$normalized[] = $code;
			}
		}

		return array_values( array_unique( $normalized ) );
	}
}
