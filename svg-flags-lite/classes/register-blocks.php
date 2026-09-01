<?php

namespace WPGO_Plugins\SVG_Flags;

/*
 *    Register blocks
 */

class Register_Blocks {


	protected $module_roots;

	/* Main class constructor. */
	public function __construct( $module_roots ) {
		$this->module_roots = $module_roots;

		add_filter( 'block_categories_all', array( &$this, 'add_block_category' ), 10, 2 );
		add_action( 'init', array( &$this, 'register_dynamic_blocks' ) );
	}

	/**
	 * Add custom block category.
	 */
	public function add_block_category( $categories, $context ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'svg-flags',
					// 'icon' => 'chart-line',
					'title' => __( 'SVG Flags', 'svg-flags-lite' ),
				),
			)
		);
	}

	/**
	 * Register the dynamic blocks.
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function register_dynamic_blocks() {
		// svg-flag block atts.
		$svg_flag_attr = array(
			// 'gutenberg_block' => [
			// 'type' => 'boolean',
			// 'default' => true,
			// ],
			'flag'          => array(
				'type'    => 'string',
				'default' => '{"value":"GB","label":"United Kingdom"}',
			),
			'size'          => array(
				'type'    => 'string',
				'default' => '5',
			),
			'size_unit'     => array(
				'type'    => 'string',
				'default' => 'em',
			),
			// 'width' => [
			// 'type' => 'string',
			// 'default' => '5em',
			// ],
			// 'height' => [
			// 'type' => 'string',
			// 'default' => '5em',
			// ],
			'square'        => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'caption'       => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'inline'        => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'inline_valign' => array(
				'type'    => 'string',
				'default' => 'middle',
			),
			'random'        => array(
				'type'    => 'boolean',
				'default' => false,
			),
		);

		if ( svg_flags_fs()->can_use_premium_code__premium_only() ) {
			// Premium only block attributes.
			$svg_flag_attr['id']             = array(
				'type'    => 'string',
				'default' => '',
			);
			$svg_flag_attr['flag_class']     = array(
				'type'    => 'string',
				'default' => '',
			);
			$svg_flag_attr['tooltip']        = array(
				'type'    => 'boolean',
				'default' => false,
			);
			$svg_flag_attr['custom_tooltip'] = array(
				'type'    => 'string',
				'default' => '',
			);
			$svg_flag_attr['custom_caption'] = array(
				'type'    => 'string',
				'default' => '',
			);
		}

		// Register the blocks.
		register_block_type(
			'svg-flags/svg-flag',
			array(
				'api_version'     => 3,
				'render_callback' => array( SVG_Flag_Shortcode::get_instance(), 'render_svg_flag_block' ),
				// 'render_callback' => __NAMESPACE__ . '\\SVG_Flag_Shortcode::render_svg_flag',
				// 'render_callback' => __NAMESPACE__ . '\\T1::render_st',
				'attributes'      => $svg_flag_attr,
			)
		);

		// svg-flag-image block atts.
		$svg_flag_image_attr = array(
			// 'gutenberg_block' => [
			// 'type' => 'boolean',
			// 'default' => true,
			// ],
			'flag'          => array(
				'type'    => 'string',
				'default' => '{"value":"GB","label":"United Kingdom"}',
			),
			'size'          => array(
				'type'    => 'string',
				'default' => '5',
			),
			'size_unit'     => array(
				'type'    => 'string',
				'default' => 'em',
			),
			// 'width' => [
			// 'type' => 'string',
			// 'default' => '5em',
			// ],
			// 'height' => [
			// 'type' => 'string',
			// 'default' => '5em',
			// ],
			'square'        => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'caption'       => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'inline'        => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'inline_valign' => array(
				'type'    => 'string',
				'default' => 'middle',
			),
			'random'        => array(
				'type'    => 'boolean',
				'default' => false,
			),
		);

		if ( svg_flags_fs()->can_use_premium_code__premium_only() ) {
			// premium only block attributes
			$svg_flag_image_attr['id']             = array(
				'type'    => 'string',
				'default' => '',
			);
			$svg_flag_image_attr['flag_class']     = array(
				'type'    => 'string',
				'default' => '',
			);
			$svg_flag_image_attr['tooltip']        = array(
				'type'    => 'boolean',
				'default' => false,
			);
			$svg_flag_image_attr['custom_tooltip'] = array(
				'type'    => 'string',
				'default' => '',
			);
			$svg_flag_image_attr['custom_caption'] = array(
				'type'    => 'string',
				'default' => '',
			);
			$svg_flag_image_attr['border']         = array(
				'type'    => 'string',
				'default' => '',
			);
			$svg_flag_image_attr['border_radius']  = array(
				'type'    => 'string',
				'default' => '',
			);
			$svg_flag_image_attr['padding']        = array(
				'type'    => 'string',
				'default' => '',
			);
			$svg_flag_image_attr['margin']         = array(
				'type'    => 'string',
				'default' => '',
			);
		}

		register_block_type(
			'svg-flags/svg-flag-image',
			array(
				'api_version'     => 3,
				'render_callback' => array( SVG_Flag_Image_Shortcode::get_instance(), 'render_svg_flag_image_block' ),
				// 'render_callback' => __NAMESPACE__ . '\\SVG_Flag_Shortcode::render_svg_flag',
				// 'render_callback' => __NAMESPACE__ . '\\T1::render_st',
				'attributes'      => $svg_flag_image_attr,
			)
		);

		// SVG flag grid block attributes.
		$svg_flag_grid_attr = array(
			'flags'     => array(
				'type'    => 'array',
				'items'   => array('type' => 'string'),
				'default' => array('GB', 'US', 'CA', 'FR', 'DE', 'JP'),
			),
			'columns'   => array(
				'type'    => 'number',
				'default' => 3,
			),
			'gap'       => array(
				'type'    => 'string',
				'default' => '1',
			),
			'gap_unit'  => array(
				'type'    => 'string',
				'default' => 'rem',
			),
			'size'      => array(
				'type'    => 'string',
				'default' => '8',
			),
			'size_unit' => array(
				'type'    => 'string',
				'default' => 'rem',
			),
			'square'    => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'caption'   => array(
				'type'    => 'boolean',
				'default' => true,
			),
		);

		$svg_flag_grid_attr = apply_filters( 'svg_flag_grid_block_attributes', $svg_flag_grid_attr );

		register_block_type(
			'svg-flags/svg-flag-grid',
			array(
				'api_version'     => 3,
				'render_callback' => array( SVG_Flag_Grid_Shortcode::get_instance(), 'render_svg_flag_grid_block' ),
				'attributes'      => $svg_flag_grid_attr,
			)
		);

	}
} /* End class definition */
