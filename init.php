<?php
/*
Plugin Name: WooCommerce Telehooks SMS
Plugin URI: http://telehooks.com/
Description: Telehooks SMS Notification Panel
Version: 2.0
Author: Onjection
Author URI: http://telehooks.com/
License: GPL
@author Onjection
@package WooCommerce Telehooks
@version 2.0
*/

/*  Copyright 2015  Onjection Solutions  (email : contact@Onjection.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! defined( 'telehooks_DIR' ) ) {
	define( 'telehooks_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

function telehooks_install_woocommerce_admin_notice() {
	?>
	<div class="error">
		<p><?php _e( 'WooCommerce_Telehooks is enabled but not effective. It requires Woocommerce in order to work.', 'yit' ); ?></p>
	</div>
<?php
}

function telehooks_install_free_admin_notice() {
	?>
	
<?php
}

if ( ! function_exists( 'telehooks_plugin_registration_hook' ) ) {
	//do nothing for now
}
register_activation_hook( __FILE__, 'telehooks_plugin_registration_hook' );



if ( ! defined( 'TELEHOOKS_REQUEST_URL' ) ) {
	define( 'TELEHOOKS_REQUEST_URL', 'http://telehooks.com/api/admin/save/modulesetting' );
	define( 'TELEHOOKS_REQUEST_URL_ADDSTATUS', 'http://telehooks.com/api/addstatuses' );
}


//endregion

function telehooks_init() {
	// Load required classes and functions
	require_once( telehooks_DIR . 'telehooks.php' );

	global $Instance;
	$Instance = new Telehooks();
}

add_action( 'telehooks_init', 'telehooks_init' );




global $tele_db_version;
$tele_db_version = '1.0';





function telehooks_install() {

	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'telehooks_install_woocommerce_admin_notice' );
	} elseif ( defined( 'telehooks_PREMIUM' ) ) {
		add_action( 'admin_notices', 'telehooks_install_free_admin_notice' );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	} else {
		do_action( 'telehooks_init' );
	}
}
	
	global $wpdb;
	global $tele_db_version;

	add_action( 'plugins_loaded', 'telehooks_install', 11 );           