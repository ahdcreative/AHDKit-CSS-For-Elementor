<?php

namespace AHDKitCSS;

// if this file it's called directly, abort.
defined( 'ABSPATH' ) || exit;

final class Plugin {
	public static $instance;

	/**
	 * Elementor Version Requirement
	 */
	const MIN_ELE_VER = '3.9.0';

	/**
	 * Minimum PHP Version Requirement
	 */
	const MIN_PHP_VER = '5.6';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_constants();
	}

	/**
	 * Plugin Constants
	 *
	 * @return void
	 */
	public function plugin_constants(): void {
		define( 'AHDKIT_CSS_VERSION', '1.3.1' );
		define( 'AHDKIT_CSS_PACKAGE', 'free' );
		define( 'AHDKIT_CSS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		define( 'AHDKIT_CSS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Initialize plugin
	 *
	 * @return void
	 */
	public function init(): void {
		// Check if Elementor it's installed and activated.
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );

			return;
		}

		// Check the Elementor Version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MIN_ELE_VER, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_elementor_version' ] );

			return;
		}

		// Check the PHP Version
		if ( version_compare( PHP_VERSION, self::MIN_PHP_VER, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_php_version' ] );

			return;
		}

		// Check for Hook Action and include it
		if ( ! class_exists( 'AHDKitCSS\src\Hook_Action' ) ) {
			include_once AHDKIT_CSS_PLUGIN_DIR . 'src/hook-action.php';
		}
		src\Hook_Action::instance()->init();
	}

	/**
	 * Admin notice for missing Elementor
	 *
	 * @return void
	 */
	public function admin_notice_missing_main_plugin(): void {
		if ( file_exists( WP_PLUGIN_DIR . '/elementor/elementor.php' ) ) {
			$notice_title = esc_html__( 'Activate Elementor', 'ahdkit-css-for-elementor' );
			$notice_url   = wp_nonce_url( 'plugins.php?action=activate&plugin=elementor/elementor.php&plugin_status=all&paged=1', 'activate-plugin_elementor/elementor.php' );
		} else {
			$notice_title = esc_html__( 'Install Elementor', 'ahdkit-css-for-elementor' );
			$notice_url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
		}

		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated. %3$s', 'ahdkit-css-for-elementor' ),
			'<strong>' . esc_html__( 'AHDKit CSS for Elementor', 'ahdkit-css-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'ahdkit-css-for-elementor' ) . '</strong>',
			'<a href="' . esc_url( $notice_url ) . '">' . $notice_title . '</a>'
		);

		printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message );
	}


	/**
	 * Admin notice for incorrect Elementor version.
	 *
	 * @return void
	 */
	public function admin_notice_elementor_version(): void {
		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'ahdkit-css-for-elementor' ),
			'<strong>' . esc_html__( 'AHDKit CSS for Elementor', 'ahdkit-css-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'ahdkit-css-for-elementor' ) . '</strong>',
			self::MIN_ELE_VER
		);

		printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Admin notice for incorrect PHP version.
	 *
	 * @return void
	 */
	public function admin_notice_php_version(): void {
		$message = sprintf(
		/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'ahdkit-css-for-elementor' ),
			'<strong>' . esc_html__( 'AHDKit CSS for Elementor', 'ahdkit-css-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'ahdkit-css-for-elementor' ) . '</strong>',
			self::MIN_PHP_VER
		);

		printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message );
	}

	/**
	 * Singleton Instance
	 *
	 * @return self
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
