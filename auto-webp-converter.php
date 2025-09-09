<?php
/**
 * Plugin Name:       Auto WebP Converter
 * Description:       Automatically converts new JPG/PNG uploads and existing library images to WebP.
 * Version:           1.1.1
 * Author:            Dental Funnels The Platform
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Network:           false
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'AWC_VERSION', '1.1.1' );
define( 'AWC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AWC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AWC_WEBP_QUALITY', 82 );
define( 'AWC_DB_VERSION', '1.0' );

// Load required classes
require_once AWC_PLUGIN_DIR . 'includes/class-awc-converter.php';
require_once AWC_PLUGIN_DIR . 'includes/class-awc-admin.php';

// Activation hook
register_activation_hook( __FILE__, [ 'AWC_Converter', 'activate' ] );

// Initialize plugin
add_action( 'plugins_loaded', 'awc_initialize_plugin' );
function awc_initialize_plugin() {
	new AWC_Converter();
	
	if ( is_admin() ) {
		new AWC_Admin();
	}
}

// Helper function to check if PNG is palette-indexed
function awc_is_problematic_palette_png_with_gd( string $image_path ): bool {
	if ( ! file_exists( $image_path ) || ! is_readable( $image_path ) ) return false;
	$image_type = @exif_imagetype( $image_path );
	if ( false === $image_type || image_type_to_mime_type( $image_type ) !== 'image/png' ) return false;
	if ( ! function_exists( 'imagecreatefrompng' ) || ! function_exists( 'imageistruecolor' ) ) return false;
	$img = @imagecreatefrompng( $image_path );
	if ( ! $img ) return false;
	$is_palette = ! imageistruecolor( $img );
	imagedestroy( $img );
	return $is_palette;
}