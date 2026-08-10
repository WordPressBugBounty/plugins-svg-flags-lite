<?php
/**
 * Shared SVG Flags rendering helpers.
 *
 * @package SVG_Flags
 */

namespace WPGO_Plugins\SVG_Flags;

defined( 'ABSPATH' ) || exit;

/**
 * Stateless helpers used by shortcode and block renderers.
 */
class Utility {

	/**
	 * Build a safe set of common HTML attributes.
	 *
	 * @param string|array<int, string> $class_attribute Class names.
	 * @param string|array<int, string> $style_attribute Inline declarations.
	 * @param string                    $title_attribute Optional title.
	 * @return string
	 */
	public static function build_el_attributes( $class_attribute, $style_attribute, $title_attribute ) {
		$el_attributes = '';

		if ( ! empty( $class_attribute ) ) {
			$classes = preg_split( '/\s+/', trim( implode( ' ', (array) $class_attribute ) ) );
			$classes = array_filter( array_map( 'sanitize_html_class', $classes ) );

			if ( ! empty( $classes ) ) {
				$el_attributes .= ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
			}
		}

		if ( ! empty( $style_attribute ) ) {
			$styles = safecss_filter_attr( implode( ' ', (array) $style_attribute ) );

			if ( '' !== $styles ) {
				$el_attributes .= ' style="' . esc_attr( $styles ) . '"';
			}
		}

		if ( ! empty( $title_attribute ) ) {
			$el_attributes .= ' title="' . esc_attr( $title_attribute ) . '"';
		}

		return $el_attributes;
	}

	/**
	 * Normalize a country selection from either a shortcode code or block JSON.
	 *
	 * @param mixed                 $flag          Country selection.
	 * @param array<string, string> $country_codes Available countries.
	 * @param string                $fallback      Fallback country code.
	 * @return string
	 */
	public static function normalize_flag( $flag, $country_codes, $fallback = 'gb' ) {
		if ( is_string( $flag ) && 0 === strpos( ltrim( $flag ), '{' ) ) {
			$selection = json_decode( $flag, true );
			$flag      = is_array( $selection ) && isset( $selection['value'] ) ? $selection['value'] : $fallback;
		}

		$flag     = strtolower( sanitize_key( (string) $flag ) );
		$fallback = strtolower( sanitize_key( (string) $fallback ) );

		return isset( $country_codes[ strtoupper( $flag ) ] ) ? $flag : $fallback;
	}

	/**
	 * Convert common shortcode and block boolean representations to a boolean.
	 *
	 * @param mixed $value Candidate value.
	 * @return bool
	 */
	public static function is_truthy( $value ) {
		return in_array( $value, array( true, 1, '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Return a safe CSS length, or an empty string for an invalid value.
	 *
	 * @param mixed  $size Candidate size.
	 * @param string $unit Candidate unit.
	 * @return string
	 */
	public static function sanitize_css_size( $size, $unit = '' ) {
		$size = trim( (string) $size );
		$unit = strtolower( trim( (string) $unit ) );

		if ( '' === $unit && preg_match( '/^(?:\d+(?:\.\d+)?|\.\d+)(?:px|em|rem|%|vw|vh|vmin|vmax)$/', $size ) ) {
			return $size;
		}

		$allowed_units = array( 'px', 'em', 'rem', '%', 'vw', 'vh', 'vmin', 'vmax' );
		if ( ! is_numeric( $size ) || ! in_array( $unit, $allowed_units, true ) ) {
			return '';
		}

		return max( 0, (float) $size ) . $unit;
	}

	/**
	 * Rebuild the legacy filtered ID attribute safely.
	 *
	 * @param mixed $attribute Candidate ID attribute.
	 * @return string
	 */
	public static function sanitize_id_attribute( $attribute ) {
		if ( ! is_string( $attribute ) || ! preg_match( '/\bid=["\']([^"\']+)["\']/', $attribute, $matches ) ) {
			return '';
		}

		return ' id="' . esc_attr( $matches[1] ) . '"';
	}
}
