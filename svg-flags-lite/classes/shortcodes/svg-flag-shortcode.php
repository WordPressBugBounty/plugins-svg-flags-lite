<?php

namespace WPGO_Plugins\SVG_Flags;

/*
 *    Class for the [svg-flag] shortcode
 */
class SVG_Flag_Shortcode
{

    protected static $instance;
    protected $module_roots;
    protected $custom_plugin_data;
    protected $country_codes;

    /* Main class constructor. */
    protected function __construct($module_roots, $custom_plugin_data)
    {
        $this->module_roots = $module_roots;
        $this->custom_plugin_data = $custom_plugin_data;
        $this->country_codes = $this->custom_plugin_data->country_codes;

        // shortcodes
        add_shortcode('svg-flags', array(&$this, 'render_svg_flag_shortcode'));
        add_shortcode('svg-flag', array(&$this, 'render_svg_flag_shortcode'));
        // we can use static callbacks too if needed
        //add_shortcode('svg-flags', array(__NAMESPACE__ . '\\SVG_Flag_Shortcode', 'render_svg_flag'));
        //add_shortcode('svg-flag', array(__NAMESPACE__ . '\\SVG_Flag_Shortcode', 'render_svg_flag'));
    }

    public static function create_instance($module_roots, $custom_plugin_data)
    {
        if (!self::$instance) {
            self::$instance = new SVG_Flag_Shortcode($module_roots, $custom_plugin_data);
        }
        return self::$instance;
    }

    public static function get_instance()
    {
        if (!self::$instance) {
            die('Error: Class instance hasn\'t been created yet.');
        }
        return self::$instance;
    }

    public function render_svg_flag_block($attributes)
    {
      // manually set this to true as we're rendering a block
      $attributes['gutenberg_block'] = true;
      return $this->render_svg_flag($attributes);
    }

    public function render_svg_flag_shortcode($attributes)
    {
      // if any shortcode attributes specified then manually set 'gutenberg_block' this to false in case it has been set to true
      if( is_array($attributes) ) {
        $attributes['gutenberg_block'] = false;
      }

      return $this->render_svg_flag($attributes);
    }

    public function render_svg_flag($attributes)
    {
        // if $attributes are coming from a shortcode parse here
        if (!(isset($attributes['gutenberg_block']) && $attributes['gutenberg_block'] === true)) {
            // get attributes from the shortcode
            $atts = shortcode_atts(array(
                'flag' => 'gb',
                'size' => '5',
                'size_unit' => 'em',
                'square' => false,
                'caption' => false,
                'inline' => false,
                'inline_valign' => 'middle',
                'random' => false
            ), $attributes, 'svg-flag');
            // @todo do we need this anymore?
            // might be empty string if no shortcode attributes specified
            if (is_array($attributes)) {
                $atts = array_merge($atts, $attributes);
            }
            $flag = esc_attr($atts['flag']);

            // if user has set 'width' instead of 'size' then manually correct and set 'size' equal to the width
            if (isset($attributes['width']) && $attributes['width'] !== '') {
                $atts['size'] = $attributes['width'];
                $atts['size_unit'] = ''; // in this case size unit will be included with size attribute.
            }

            // echo "SHORTCODE";
            // echo "<pre>";
            // echo "A:";
            // print_r($attributes);
            // echo "B:";
            // print_r($atts);
            // echo "</pre>";
        } else {
            // attributes come from an editor block
            $atts = $attributes;
            $flag = $atts['flag'];
        }

        // extract shortcode attributes
        $size = Utility::sanitize_css_size($atts['size'], $atts['size_unit']);
        $square = $atts['square'];

        // initialise shortcode element attribute arrays
				$class_attribute = array();
        $style_attribute = array();
        $title_attribute = array();

        // display random flag?
        if (Utility::is_truthy($atts['random'])) {
            $flag = array_rand($this->country_codes);
        }

        // filter flag and force to lower incase if it has been set to uppercase by user or via country array
        $flag = Utility::normalize_flag(
            apply_filters('svg_flag_shortcode_custom_flag', $flag, $atts),
            $this->country_codes
        );

        // filter tag used for flag element - defaults to span
        // tag filter - if inline flag use 'span', otherwise use 'div'
        $inline = Utility::is_truthy($atts['inline']);
        if ($inline) {
            $tag = 'span';
        } else {
            $tag = 'div';
        }
        $tag = apply_filters('svg_flag_shortcode_tag', $tag, $atts);
        $tag = in_array($tag, array('div', 'span'), true) ? $tag : 'div';

        // filter flag element id - defaults to none
        $id = Utility::sanitize_id_attribute(apply_filters('svg_flag_shortcode_id', '', $atts));

        // add another entry to the style attribute array
        $inline_valign = sanitize_key($atts['inline_valign']);
        $allowed_valign = array('baseline', 'bottom', 'middle', 'sub', 'super', 'text-bottom', 'text-top', 'top');
        if ($inline && in_array($inline_valign, $allowed_valign, true)) {
            $sp = count($style_attribute) > 0 ? ' ' : '';
            array_push($style_attribute, $sp . 'vertical-align:' . $inline_valign . ';');
        }

        // class attribute - setup flag class via inline class attribute
        //echo $inline . '<br>';
        //echo $flag_class . '<br>';
        $flag_class = '';
        if ($inline) {
            $flag_class = 'svg-flag fi fi-' . $flag . ' flag-icon flag-icon-' . $flag;
        } else {
            $flag_class = 'svg-flag fib fi-' . $flag . ' flag-icon-background flag-icon-' . $flag;
        }

        $flag_class = apply_filters('svg_flag_shortcode_flag_class', $flag_class, $flag, $atts);
        //$flag_class = 'svg-flag flag-icon-background flag-icon-' . $flag;
        $sp = count($class_attribute) > 0 ? ' ' : '';
        array_push($class_attribute, $sp . $flag_class);

        // class attribute - square
        $sp = count($class_attribute) > 0 ? ' ' : '';
        if (Utility::is_truthy($square)) {
            array_push($class_attribute, $sp . 'fis flag-icon-squared');
        }

        // style attribute - size
        $sp = count($style_attribute) > 0 ? ' ' : '';
        if (!empty($size)) {
            array_push($style_attribute, $sp . 'width:' . $size . ';');
            array_push($style_attribute, ' height:' . $size . ';');
        }
        // style attribute - width
        // $sp = count($style_attribute) > 0 ? ' ' : '';
        // if (!empty($width)) {
        //     array_push($style_attribute, $sp . 'width:' . $width . ';');
        // }
        // style attribute - height
        // $sp = count($style_attribute) > 0 ? ' ' : '';
        // if (!empty($height)) {
        //     array_push($style_attribute, $sp . 'height:' . $height . ';');
        // }

        // caption
        $caption = Utility::is_truthy($atts['caption']);
        //echo "TOOLTIP: " . $tooltip . '<br>';
        //echo "CUSTOM TOOLTIP: " . $custom_tooltip . '<br>';
        // if ($caption === true || $caption === 'true') {
        // The true(bool/string) value of caption is typecast to 1(string).    
        if ($caption) {
            $flag_lookup_code = strtoupper($flag);
            $caption_text_wrapper_open = '<div class="svg-flags-caption">';
            $caption_text_heading_open = '<h3 class="svg-flags-caption-heading">';
            $caption_text = isset($this->country_codes[$flag_lookup_code]) ? $this->country_codes[$flag_lookup_code] : '';
            $caption_text_heading_close = '</h3>';
            $caption_text_wrapper_close = '</div>';
            $caption_text = apply_filters('svg_flag_caption_text', $caption_text, $atts);
        } else {
            $caption_text_wrapper_open = '';
            $caption_text_heading_open = '';
            $caption_text = '';
            $caption_text_heading_close = '';
            $caption_text_wrapper_close = '';
            //echo "CT: [" . $caption_text . ']<br>';
				}
				// don't show caption if flag is inline
        if ($inline) {
            $caption_text_wrapper_open = '';
            $caption_text_heading_open = '';
            $caption_text = '';
            $caption_text_heading_close = '';
            $caption_text_wrapper_close = '';
        }

        // filter shortcode element attribute arrays
        $class_attribute = apply_filters('svg_flag_shortcode_class_attribute', $class_attribute, $atts);
        $style_attribute = apply_filters('svg_flag_shortcode_style_attribute', $style_attribute, $atts);
        $title_attribute = apply_filters('svg_flag_shortcode_title_attribute', '', $atts, $flag);

        // build element attributes
        $el_attributes = Utility::build_el_attributes($class_attribute, $style_attribute, $title_attribute);

        // start output buffering
        // $output = $caption_text_wrapper_open;
        // $output .= '<' . $tag . $id . $el_attributes . '></' . $tag . '>';
        // $output .= $caption_text;
        // $output .= $caption_text_wrapper_close;

        return $caption_text_wrapper_open
            . '<' . $tag . $id . $el_attributes . '></' . $tag . '>'
            . $caption_text_heading_open
            . esc_html($caption_text)
            . $caption_text_heading_close
            . $caption_text_wrapper_close;
    }

} /* End class definition */
