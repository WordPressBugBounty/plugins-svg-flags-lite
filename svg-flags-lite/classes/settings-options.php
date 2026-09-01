<?php
/**
 * SVG Flags settings model.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Provides safe defaults for newly created content.
 */
class Settings_Options {

	/** Option name stored in WordPress. */
	const OPTION_NAME = 'svg_flags_options';

	/** Settings API group. */
	const OPTION_GROUP = 'svg_flags_options_group';

	/**
	 * Country labels keyed by alpha-2 or upstream code.
	 *
	 * @var array<string, string>
	 */
	private $country_codes;

	/**
	 * Whether the current installation has an active Pro entitlement.
	 *
	 * @var bool
	 */
	private $is_premium;

	/**
	 * Main class constructor.
	 *
	 * @param array<string, string> $country_codes Available country labels.
	 * @param bool                  $is_premium Active Pro entitlement.
	 */
	public function __construct( $country_codes, $is_premium = false ) {
		$this->country_codes = $country_codes;
		$this->is_premium   = (bool) $is_premium;
	}

	/**
	 * Return the stable plugin defaults.
	 *
	 * These remain separate from server-side block schema defaults so changing an
	 * option cannot alter previously saved content.
	 *
	 * @return array<string, mixed>
	 */
	public function defaults() {
		return array(
			'default_flag'       => 'GB',
			'default_size'       => '5',
			'default_size_unit'  => 'em',
			'default_square'     => false,
			'default_caption'    => false,
			'grid_flags'         => array( 'GB', 'US', 'CA', 'FR', 'DE', 'JP' ),
			'grid_columns'       => 3,
			'grid_gap'           => '1',
			'grid_gap_unit'      => 'rem',
			'grid_size'          => '8',
			'grid_size_unit'     => 'rem',
			'grid_square'        => false,
			'grid_caption'       => true,
			'pro_grid_columns_tablet' => 3,
			'pro_grid_columns_mobile' => 2,
			'pro_grid_search'          => false,
			'pro_grid_filter'          => false,
			'pro_grid_card_style'      => false,
			'pro_grid_card_background' => '#ffffff',
			'pro_grid_card_border'     => '#dcdcde',
			'pro_grid_card_radius'     => '10',
			'pro_grid_card_padding'    => '16',
			'pro_card_layout'          => 'horizontal',
			'pro_card_flag_size'       => '4',
			'pro_card_flag_size_unit'  => 'rem',
			'pro_card_background'      => '#ffffff',
			'pro_card_text_color'      => '#1d2327',
			'pro_card_border'          => '#dcdcde',
			'pro_card_radius'          => '12',
			'pro_card_padding'         => '20',
			'pro_directory_columns'        => 5,
			'pro_directory_columns_tablet' => 3,
			'pro_directory_columns_mobile' => 2,
			'pro_directory_search'         => true,
			'pro_directory_filter'         => true,
			'pro_directory_square'         => false,
			'pro_directory_show_codes'     => false,
		);
	}

	/**
	 * Return the current options merged with stable defaults.
	 *
	 * @return array<string, mixed>
	 */
	public function get() {
		$stored = get_option( self::OPTION_NAME, array() );
		$stored = is_array( $stored ) ? $stored : array();

		return array_merge( $this->defaults(), $stored );
	}

	/**
	 * Sanitize Settings API input.
	 *
	 * @param mixed $input Submitted options.
	 * @return array<string, mixed>
	 */
	public function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = $this->defaults();
		$country  = isset( $input['default_flag'] ) ? strtoupper( sanitize_key( $input['default_flag'] ) ) : $defaults['default_flag'];

		if ( ! isset( $this->country_codes[ $country ] ) ) {
			$country = $defaults['default_flag'];
		}

		return array(
			'default_flag'       => $country,
			'default_size'       => $this->sanitize_number( $input['default_size'] ?? $defaults['default_size'], 0.25, 1000, $defaults['default_size'] ),
			'default_size_unit'  => $this->sanitize_unit( $input['default_size_unit'] ?? $defaults['default_size_unit'], $defaults['default_size_unit'] ),
			'default_square'     => ! empty( $input['default_square'] ),
			'default_caption'    => ! empty( $input['default_caption'] ),
			'grid_flags'         => $this->sanitize_flags( $input['grid_flags'] ?? $defaults['grid_flags'] ),
			'grid_columns'       => min( 8, max( 1, absint( $input['grid_columns'] ?? $defaults['grid_columns'] ) ) ),
			'grid_gap'           => $this->sanitize_number( $input['grid_gap'] ?? $defaults['grid_gap'], 0, 1000, $defaults['grid_gap'] ),
			'grid_gap_unit'      => $this->sanitize_unit( $input['grid_gap_unit'] ?? $defaults['grid_gap_unit'], $defaults['grid_gap_unit'], true ),
			'grid_size'          => $this->sanitize_number( $input['grid_size'] ?? $defaults['grid_size'], 0.25, 1000, $defaults['grid_size'] ),
			'grid_size_unit'     => $this->sanitize_unit( $input['grid_size_unit'] ?? $defaults['grid_size_unit'], $defaults['grid_size_unit'], true ),
			'grid_square'        => ! empty( $input['grid_square'] ),
			'grid_caption'       => ! empty( $input['grid_caption'] ),
			'pro_grid_columns_tablet' => min( 8, max( 1, absint( $input['pro_grid_columns_tablet'] ?? $defaults['pro_grid_columns_tablet'] ) ) ),
			'pro_grid_columns_mobile' => min( 8, max( 1, absint( $input['pro_grid_columns_mobile'] ?? $defaults['pro_grid_columns_mobile'] ) ) ),
			'pro_grid_search'          => ! empty( $input['pro_grid_search'] ),
			'pro_grid_filter'          => ! empty( $input['pro_grid_filter'] ),
			'pro_grid_card_style'      => ! empty( $input['pro_grid_card_style'] ),
			'pro_grid_card_background' => $this->sanitize_color( $input['pro_grid_card_background'] ?? $defaults['pro_grid_card_background'], $defaults['pro_grid_card_background'] ),
			'pro_grid_card_border'     => $this->sanitize_color( $input['pro_grid_card_border'] ?? $defaults['pro_grid_card_border'], $defaults['pro_grid_card_border'] ),
			'pro_grid_card_radius'     => $this->sanitize_number( $input['pro_grid_card_radius'] ?? $defaults['pro_grid_card_radius'], 0, 100, $defaults['pro_grid_card_radius'] ),
			'pro_grid_card_padding'    => $this->sanitize_number( $input['pro_grid_card_padding'] ?? $defaults['pro_grid_card_padding'], 0, 100, $defaults['pro_grid_card_padding'] ),
			'pro_card_layout'          => $this->sanitize_choice( $input['pro_card_layout'] ?? $defaults['pro_card_layout'], array( 'horizontal', 'vertical' ), $defaults['pro_card_layout'] ),
			'pro_card_flag_size'       => $this->sanitize_number( $input['pro_card_flag_size'] ?? $defaults['pro_card_flag_size'], 0.25, 1000, $defaults['pro_card_flag_size'] ),
			'pro_card_flag_size_unit'  => $this->sanitize_unit( $input['pro_card_flag_size_unit'] ?? $defaults['pro_card_flag_size_unit'], $defaults['pro_card_flag_size_unit'] ),
			'pro_card_background'      => $this->sanitize_color( $input['pro_card_background'] ?? $defaults['pro_card_background'], $defaults['pro_card_background'] ),
			'pro_card_text_color'      => $this->sanitize_color( $input['pro_card_text_color'] ?? $defaults['pro_card_text_color'], $defaults['pro_card_text_color'] ),
			'pro_card_border'          => $this->sanitize_color( $input['pro_card_border'] ?? $defaults['pro_card_border'], $defaults['pro_card_border'] ),
			'pro_card_radius'          => $this->sanitize_number( $input['pro_card_radius'] ?? $defaults['pro_card_radius'], 0, 100, $defaults['pro_card_radius'] ),
			'pro_card_padding'         => $this->sanitize_number( $input['pro_card_padding'] ?? $defaults['pro_card_padding'], 0, 100, $defaults['pro_card_padding'] ),
			'pro_directory_columns'        => min( 8, max( 1, absint( $input['pro_directory_columns'] ?? $defaults['pro_directory_columns'] ) ) ),
			'pro_directory_columns_tablet' => min( 8, max( 1, absint( $input['pro_directory_columns_tablet'] ?? $defaults['pro_directory_columns_tablet'] ) ) ),
			'pro_directory_columns_mobile' => min( 8, max( 1, absint( $input['pro_directory_columns_mobile'] ?? $defaults['pro_directory_columns_mobile'] ) ) ),
			'pro_directory_search'         => ! empty( $input['pro_directory_search'] ),
			'pro_directory_filter'         => ! empty( $input['pro_directory_filter'] ),
			'pro_directory_square'         => ! empty( $input['pro_directory_square'] ),
			'pro_directory_show_codes'     => ! empty( $input['pro_directory_show_codes'] ),
		);
	}

	/**
	 * Return insertion defaults in block-attribute form.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function block_defaults() {
		$options = $this->get();
		$flag    = $options['default_flag'];
		$label   = $this->country_codes[ $flag ] ?? $flag;

		$single = array(
			'flag'      => wp_json_encode(
				array(
					'value' => $flag,
					'label' => $label,
				)
			),
			'size'      => $options['default_size'],
			'size_unit' => $options['default_size_unit'],
			'square'    => $options['default_square'],
			'caption'   => $options['default_caption'],
		);

		$defaults = array(
			'flag'       => $single,
			'flagImage'  => $single,
			'flagHeading' => array_merge(
				$single,
				array(
					'size'      => '1',
					'size_unit' => 'em',
				)
			),
			'grid'       => array(
				'flags'     => $options['grid_flags'],
				'columns'   => $options['grid_columns'],
				'gap'       => $options['grid_gap'],
				'gap_unit'  => $options['grid_gap_unit'],
				'size'      => $options['grid_size'],
				'size_unit' => $options['grid_size_unit'],
				'square'    => $options['grid_square'],
				'caption'   => $options['grid_caption'],
			),
		);

		if ( ! $this->is_premium ) {
			return $defaults;
		}

		$defaults['grid'] = array_merge(
			$defaults['grid'],
			array(
				'columns_tablet'   => $options['pro_grid_columns_tablet'],
				'columns_mobile'   => $options['pro_grid_columns_mobile'],
				'enable_search'    => $options['pro_grid_search'],
				'enable_filter'    => $options['pro_grid_filter'],
				'card_style'       => $options['pro_grid_card_style'],
				'card_background'  => $options['pro_grid_card_background'],
				'card_border_color' => $options['pro_grid_card_border'],
				'card_radius'      => $options['pro_grid_card_radius'],
				'card_padding'     => $options['pro_grid_card_padding'],
			)
		);
		$defaults['flagCard'] = array(
			'flag'             => $single['flag'],
			'layout'           => $options['pro_card_layout'],
			'flag_size'        => $options['pro_card_flag_size'],
			'flag_size_unit'   => $options['pro_card_flag_size_unit'],
			'background_color' => $options['pro_card_background'],
			'text_color'       => $options['pro_card_text_color'],
			'border_color'     => $options['pro_card_border'],
			'border_radius'    => $options['pro_card_radius'],
			'padding'          => $options['pro_card_padding'],
		);
		$defaults['directory'] = array(
			'columns'         => $options['pro_directory_columns'],
			'columns_tablet'  => $options['pro_directory_columns_tablet'],
			'columns_mobile'  => $options['pro_directory_columns_mobile'],
			'show_search'     => $options['pro_directory_search'],
			'show_filter'     => $options['pro_directory_filter'],
			'square'          => $options['pro_directory_square'],
			'show_codes'      => $options['pro_directory_show_codes'],
		);

		return $defaults;
	}

	/**
	 * Sanitize a supported choice.
	 *
	 * @param mixed             $value Submitted value.
	 * @param array<int,string> $choices Supported values.
	 * @param string            $fallback Default value.
	 * @return string
	 */
	private function sanitize_choice( $value, $choices, $fallback ) {
		$value = sanitize_key( (string) $value );

		return in_array( $value, $choices, true ) ? $value : $fallback;
	}

	/**
	 * Sanitize one hexadecimal colour.
	 *
	 * @param mixed  $value Submitted value.
	 * @param string $fallback Default value.
	 * @return string
	 */
	private function sanitize_color( $value, $fallback ) {
		$color = sanitize_hex_color( (string) $value );

		return $color ? $color : $fallback;
	}

	/**
	 * Normalize a list of country codes.
	 *
	 * @param mixed $flags Submitted flags.
	 * @return array<int, string>
	 */
	private function sanitize_flags( $flags ) {
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

		$normalized = array_values( array_unique( $normalized ) );

		return empty( $normalized ) ? $this->defaults()['grid_flags'] : $normalized;
	}

	/**
	 * Sanitize a numeric control while preserving a compact string value.
	 *
	 * @param mixed        $value Submitted value.
	 * @param float        $minimum Minimum allowed value.
	 * @param float        $maximum Maximum allowed value.
	 * @param int|float|string $fallback Default value.
	 * @return string
	 */
	private function sanitize_number( $value, $minimum, $maximum, $fallback ) {
		if ( ! is_numeric( $value ) ) {
			return (string) $fallback;
		}

		$value = min( $maximum, max( $minimum, (float) $value ) );

		return rtrim( rtrim( number_format( $value, 4, '.', '' ), '0' ), '.' );
	}

	/**
	 * Sanitize a supported CSS unit.
	 *
	 * @param mixed  $unit Submitted unit.
	 * @param string $fallback Default unit.
	 * @param bool   $allow_percentage Whether percentage is supported.
	 * @return string
	 */
	private function sanitize_unit( $unit, $fallback, $allow_percentage = false ) {
		$units = array( 'px', 'em', 'rem', 'vw', 'vh' );
		if ( $allow_percentage ) {
			$units[] = '%';
		}

		$unit = sanitize_text_field( (string) $unit );

		return in_array( $unit, $units, true ) ? $unit : $fallback;
	}
}
