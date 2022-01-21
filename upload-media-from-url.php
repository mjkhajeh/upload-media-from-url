<?php
/*
Plugin Name: Upload media from URL
Plugin URI: https://wordpress.org/plugins/upload-media-from-url
Description: Download files from URL and upload them in WordPress media library.
Version: 1.0.0.0
Author: MohammadJafar Khajeh
Author URI: https://mjkhajeh.ir
Text Domain: mjupurl
Domain Path: /languages
*/
namespace MJUPURL;

if( !defined( 'ABSPATH' ) ) exit;

class Init {
	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return	A single instance of this class.
	 */
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}
	
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'constants' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'includes' ), 5 );
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
	}
	
	public function constants() {
		if( ! defined( 'MJUPURL_DIR' ) )
			define( 'MJUPURL_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

		if( ! defined( 'MJUPURL_URI' ) )
			define( 'MJUPURL_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );

		if( ! defined( 'MJUPURL_VERSION' ) )
			define( 'MJUPURL_VERSION', '1.0.0.0' );
	}

	public function i18n() {
		// Load languages
		load_plugin_textdomain( 'mjupurl', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function includes() {
		include_once( MJUPURL_DIR . "Utils.php" );
		if( is_admin() ) {
			include_once( MJUPURL_DIR . "Backend/Page.php" );
			include_once( MJUPURL_DIR . "Backend/AttachmentField.php" );
		}
	}

	public function admin_enqueue() {
		wp_register_script( 'mjupurl-upload-page', MJUPURL_URI . "assets/js/backend.upload_page.min.js", array( 'jquery' ), MJUPURL_VERSION, true );
		wp_register_style( 'mjupurl-upload-page', MJUPURL_URI . "assets/css/backend.upload_page.min.css", array(), MJUPURL_VERSION );
	}
}
Init::get_instance();

/* TO DO */
// Add in REST API