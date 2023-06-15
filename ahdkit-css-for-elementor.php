<?php
/*
 * Plugin Name: AHDKit CSS for Elementor
 * Plugin URI: https://ahd-creative.agency/plugins/ahdkit-css-for-elementor/
 * Description: A lightweight plugin that open an option to add custom CSS code for each device (desktop, tablets, mobiles) by elementor widgets.
 * Version: 1.3.1
 * Author: AHDCreative Web Solutions
 * Author URI: https://ahd-creative.agency/
 * Text Domain: ahdkit-css-for-elementor
 * Domain Path: /languages/
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Elementor tested up to: 3.13.4
 * Elementor Pro tested up to: 3.13.2
 */

// if this file it's called directly, abort.
defined('ABSPATH') || exit;

// include the autoloader from composer
require 'vendor/autoload.php';

// Initialize the Plugin
require 'plugin.php';

/**
 * load plugin after initialized the WordPress core
 */
add_action('plugin_loaded', function () {
	AHDKitCSS\Plugin::instance()->init();
});
