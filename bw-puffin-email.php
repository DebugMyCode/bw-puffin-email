<?php
/**
 * @wordpress-plugin
 * Plugin Name:       BlueWren Puffin
 * Plugin URI:        https://www.bluewren.co.uk
 * Version:           0.0.1
 * Author:            BlueWren
 * Author URI:        https://www.bluewren.co.uk
 * Update URI:        https://github.com/DebugMyCode/bw-puffin-email/
 * Description: 	  Used by 11
 * License: 			GPLv2 or later
 * Text Domain: 		bw-puffin
 */

use BWPuffin\Admin;
use BWPuffin\Controllers\PuffinController;
use BWPuffin\Puffin;

if ( ! defined( 'WPINC' ) ) {
	exit; // Exit if accessed directly.
}

define( 'PUFFIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once  PUFFIN_PLUGIN_DIR . 'BWPuffin/Controllers/PuffinController.php';
require_once  PUFFIN_PLUGIN_DIR . 'BWPuffin/Traits/Bridge.php';
require_once  PUFFIN_PLUGIN_DIR . 'BWPuffin/Puffin.php';
require_once  PUFFIN_PLUGIN_DIR . 'BWPuffin/Admin.php';

global $wpdb;
// $result = $wpdb->query( 
// 	$wpdb->prepare( 
// 		"DELETE FROM wp_options WHERE option_name = '_site_transient_update_plugins'"
// 	)
// );

// print_r('<pre style="padding-left:200px">');
// print_r(get_option('_site_transient_update_plugins'));
// print_r('</pre>');

// var_dump($result);
// print_r('yes');

// $response = wp_remote_get(
// 	'https://api.github.com/repos/DebugMyCode/bw-puffin-email/releases/latest',
// 	array(
// 		'user-agent' => 'DebugMyCode',
// 	)
// );

// print_r('<pre style="padding-left:200px">');
// print_r(json_decode( wp_remote_retrieve_body( $response ), true ));
// print_r('</pre>');

// if( ! function_exists( 'bwCheckForUpdates' ) ){
//     function bwCheckForUpdates( $update, $plugin_data, $plugin_file ){
        
//         static $response = false;
        
//         if( empty( $plugin_data['UpdateURI'] ) || ! empty( $update ) )
//             return $update;
        
//         if( $response === false )
//             $response = wp_remote_get( $plugin_data['UpdateURI'] );
        
//         if( empty( $response['body'] ) )
//             return $update;
        
//         $custom_plugins_data = json_decode( $response['body'], true );
        
//         if( ! empty( $custom_plugins_data[ $plugin_file ] ) )
//             return $custom_plugins_data[ $plugin_file ];
//         else
//             return $update;
//     }
    
//     add_filter('update_plugins_github.com', 'bwCheckForUpdates', 10, 3);
// }

add_filter( 'update_plugins_github.com', 'selfUpdate', 10, 4 );

function selfUpdate( $update, array $plugin_data, string $plugin_file, $locales ) {

	// only check this plugin
	if ( 'bw-puffin-email/bw-puffin-email.php' !== $plugin_file ) {
		return $update;
	}

	// already completed update check elsewhere
	if ( ! empty( $update ) ) {
		return $update;
	}

	// let's go get the latest version number from GitHub
	$response = wp_remote_get(
		'https://api.github.com/repos/DebugMyCode/bw-puffin-email/releases/latest',
		array(
			'user-agent' => 'DebugMyCode',
		)
	);

	if ( is_wp_error( $response ) ) {
		return;
	} else {
		$output = json_decode( wp_remote_retrieve_body( $response ), true );
	}

	$new_version_number  = $output['tag_name'];
	$is_update_available = version_compare( 'v'.$plugin_data['Version'], $new_version_number, '<' );



	if ( ! $is_update_available ) {
		return false;
	}

	$new_url     = $output['html_url'];
	$new_package = $output['assets'][0]['browser_download_url'];
	// $new_package = $output['assets'][0]['zipball_url'];

	error_log('$plugin_data: ' . print_r( $plugin_data, true ));
	error_log('$new_version_number: ' . $new_version_number );
	error_log('$new_url: ' . $new_url );
	error_log('$new_package: ' . $new_package );



	// print_r(array(
	// 	'slug'    => $plugin_data['TextDomain'],
	// 	'version' => $new_version_number,
	// 	'url'     => $new_url,
	// 	'package' => $new_package,
	// ));
	// print_r('YES');
	// die();


	return array(
		'slug'    		=> $plugin_data['TextDomain'],
		'version' 		=> $plugin_data['Version'],
		'new_version' 	=> str_replace('v', '', $new_version_number),
		'url'     		=> $new_url,
		'package' 		=> $new_package,
	);
}

add_action('admin_menu', 'bw_puffin');

function bw_puffin()
{
    $admin = new Admin();
}    

add_action('init', 'bw_test_email');

function bw_test_email()
{
    $puffin = new Puffin();
    $admin = new PuffinController($puffin);
}

function testBlank()
{
    // silence
}

add_filter( 'plugins_api', 'bbloomer_plugin_view_version_details', 9999, 3 );
 
function bbloomer_plugin_view_version_details( $res, $action, $args ) {
   if ( 'plugin_information' !== $action ) return $res;
   if ( $args->slug !== 'bw-puffin-email' ) return $res;
   $res = new stdClass();
   $res->name = 'Whatever Plugin For WooCommerce';
   $res->slug = 'bw-puffin-email';
   $res->path = 'bw-puffin-email/bw-puffin-email.php';
   $res->sections = array(
      'description' => 'The plugin description',
   );
   $changelog = bbloomer_whatever_plugin_request();
   $res->version = $changelog->latest_version;
   $res->download_link = $changelog->download_url; 
   return $res;
}
 