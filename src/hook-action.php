<?php

namespace AHDKitCSS\src;

// if this file it's called directly, abort,
defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Core\DynamicTags\Base_Tag;
use Elementor\Core\DynamicTags\Dynamic_CSS;
use Elementor\Plugin as Elementor;
use Wikimedia\CSS\Parser\Parser;
use Wikimedia\CSS\Sanitizer\StylesheetSanitizer;
use Wikimedia\CSS\Util;

class Hook_Action {
	public static $instance;

	/**
	 * Class Initialization
	 * @return void
	 */
	public function init() {
		add_action( 'elementor/element/common/_section_responsive/after_section_end', [
			$this,
			'register_controls'
		], 10, 2 );
		add_action( 'elementor/element/section/_section_responsive/after_section_end', [
			$this,
			'register_controls'
		], 10, 2 );
		add_action( 'elementor/element/common/_section_responsive/after_section_end', [
			$this,
			'register_controls'
		], 10, 2 );
		add_action( 'elementor/element/container/_section_responsive/after_section_end', [
			$this,
			'register_controls'
		], 10, 2 );

		add_action( 'elementor/element/parse_css', [ $this, 'add_post_css' ], 10, 2 );

		add_action( 'elementor/css-file/post/parse', [ $this, 'add_page_settings_css' ] );
	}

	/**
	 * register controls to elementor widget function
	 *
	 * @param Controls_Stack $element
	 * @param $section_id
	 *
	 * @return void
	 */
	public function register_controls( Controls_Stack $element, $section_id ) {
		if ( ! current_user_can( 'edit_pages' ) && ! current_user_can( 'unfiltered_html' ) ) {
			return;
		}
		$element->start_controls_section(
			'_ahdkit_css_f_ele',
			[
				'label' => esc_html__( 'AHDKit CSS for Elementor', 'ahdkit-css-for-elementor' ),
				'tab'   => Controls_Manager::TAB_ADVANCED
			]
		);
		$element->start_controls_tabs( 'style_tabs' );

		$element->start_controls_tab(
			'_custom_css_desktop',
			[
				'label' => '<span class="eicon-device-desktop" title="Desktop"></span>'
			]
		);
		$element->add_control(
			'_ahdkit_css_title_desktop',
			[
				'label' => esc_html__( 'AHDKit Custom CSS', 'ahdkit-css-for-elementor' ),
				'type'  => Controls_Manager::HEADING
			]
		);

		$element->add_control(
			'_ahdkit_css_desktop',
			[
				'label'       => esc_html__( 'AHDKit Custom CSS', 'ahdkit-css-for-elementor' ),
				'type'        => Controls_Manager::CODE,
				'language'    => 'css',
				'render_type' => 'ui',
				'show_label'  => false,
				'separator'   => 'none'
			]
		);

		$element->end_controls_tab();

		$element->start_controls_tab(
			'_custom_css_tablet',
			[
				'label' => '<span class="eicon-device-tablet" title="Tablet"></span>'
			]
		);
		$element->start_controls_tab(
			'_ahdkit_css_title_tablet',
			[
				'label' => esc_html__( 'AHDKit Custom CSS (Tablet)', 'ahdkit-css-for-elementor' ),
				'type'  => Controls_Manager::HEADING
			]
		);

		$element->add_control(
			'_ahdkit_css_tablet',
			[
				'type'        => Controls_Manager::CODE,
				'label'       => esc_html__( 'AHDKit Custom CSS (Tablet)', 'ahdkit-css-for-elementor' ),
				'language'    => 'css',
				'render_type' => 'ui',
				'show_label'  => false,
				'separator'   => 'none'
			]
		);

		$element->end_controls_tab();

		$element->start_controls_tab(
			'_custom_css_mobile',
			[
				'label' => '<span class="eicon-device-mobile" title="Mobile"></span>'
			]
		);

		$element->add_control(
			'_ahdkit_css_title_mobile',
			[
				'label' => esc_html__( 'AHDKit Custom CSS (Mobile)', 'ahdkit-css-for-elementor' ),
				'type'  => Controls_Manager::HEADING
			]
		);

		$element->add_control(
			'_ahdkit_css_mobile',
			[
				'type'        => Controls_Manager::CODE,
				'label'       => esc_html__( 'AHDKit Custom CSS (Mobile)', 'ahdkit-css-for-elementor' ),
				'language'    => 'css',
				'render_type' => 'ui',
				'show_label'  => false,
				'separator'   => 'none'
			]
		);

		$element->end_controls_tab();
		$element->end_controls_tabs();

		$element->add_control(
			'ahdkit_css_description',
			[
				'raw'             => esc_html__( 'Use "selector" to target wrapper element. Examples:<br>selector {color: red;} // For main element<br>selector .child-element {margin: 10px;} // For child element<br>.my-class {text-align: center;} // Or use any custom selector', 'ahdkit-css-for-elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content:classes' => 'elementor-descriptor'
			]
		);

		$element->add_control(
			'_ahdkit_css_notice',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'CSS will not reflect in editor panel. You have to save and open preview panel to get output.', 'ahdkit-css-for-elementor' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Add custom css function to post
	 *
	 * @param [type] $post_css
	 * @param [type] $element
	 *
	 * @return void
	 */
	public function add_post_css( $post_css, $element ) {
		if ( $post_css instanceof Dynamic_CSS ) {
			return;
		}

		$element_settings = $element->get_settings();
		$sanitize         = $this->parse_css_to_remove_injecting_code( $element_settings, $post_css->get_element_unique_selector( $element ) );
		$post_css->get_stylesheet()->add_raw_css( $sanitize );
	}

	/**
	 * Add custom CSS to pages
	 *
	 * @param [type] $post_css
	 *
	 * @return void
	 */
	public function add_page_settings_css( $post_css ) {
		$doc              = Elementor::instance()->documents->get( $post_css->get_post_id() );
		$element_settings = $doc->get_settings();
		$sanitize         = $this->parse_css_to_remove_injecting_code( $element_settings, $doc->get_css_wrapper_selector() );
		$post_css->get_stylesheet()->add_raw_css( $sanitize );
	}

	/**
	 * Validate and Sanitize css to avoid malicious code injection
	 *
	 * @param [type] $element_settings
	 * @param [type] $unique_selector
	 *
	 * @return string|void
	 */
	public function parse_css_to_remove_injecting_code( $element_settings, $unique_selector ) {
		$ahd_kit = '';

		if ( empty( $element_settings['_ahdkit_css_desktop'] ) && empty( $element_settings['_ahdkit_css_tablet'] ) && empty( $element_settings['_ahdkit_css_mobile'] ) ) {
			return;
		}

		$ahdkit_css_desktop = trim( $element_settings['_ahdkit_css_desktop'] );
		$ahdkit_css_tablet  = trim( $element_settings['_ahdkit_css_tablet'] );
		$ahdkit_css_mobile  = trim( $element_settings['_ahdkit_css_mobile'] );

		if ( empty( $ahdkit_css_desktop ) && empty( $ahdkit_css_tablet ) && empty( $ahdkit_css_mobile ) ) {
			return;
		}

		$ahd_kit .= ( ( ! empty( $ahdkit_css_desktop ) ) ? $ahdkit_css_desktop : "" );
		$ahd_kit .= ( ( ! empty( $ahdkit_css_tablet ) ) ? " @media (max-width: 768px) { " . $ahdkit_css_tablet . "}" : "" );
		$ahd_kit .= ( ( ! empty( $ahdkit_css_mobile ) ) ? " @media (max-width: 425px) { " . $ahdkit_css_mobile . "}" : "" );

		if ( empty( $ahd_kit ) ) {
			return;
		}

		$ahd_kit         = str_replace( 'selector', $unique_selector, $ahd_kit );
		$remove_tags_css = wp_kses( $ahd_kit, [] );
		$parser          = Parser::newFromString( $remove_tags_css );
		$parsed_css      = $parser->parseStylesheet();
		$sanitizer       = StylesheetSanitizer::newDefault();
		$sanitized_css   = $sanitizer->sanitize( $parsed_css );

		return Util::stringify( $sanitized_css, [ 'minify' => true ] );
	}

	/**
	 * Singleton instance of this class
	 * @return self
	 */
	public static function instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
