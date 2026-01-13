<?php
define ( 'PUFFIN_VERSION', '0.0.4');

/**
 * BlueWren Puffin
 *
 * @package       Puffin
 * @author        BlueWren
 *
 * @wordpress-plugin
 * Plugin Name:       BlueWren Puffin
 * Plugin URI:        https://www.bluewren.co.uk
 * Version:           0.0.3
 * Author:            BlueWren
 * Author URI:        https://www.bluewren.co.uk
 * Update URI:        https://bluewren.uat-web.co.uk/wp-content/uploads/update-plugins/plugins-control.json
 * Text Domain:       bw
 */

use BWPuffin\Admin;
use BWPuffin\Controllers\PuffinController;
use BWPuffin\Puffin;

if ( ! defined( 'ABSPATH' ) ) {
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

// wp_update_plugins();

print_r('<pre style="padding-left:200px">');
print_r(get_option('_site_transient_update_plugins'));
print_r('</pre>');

var_dump($result);
print_r('yes');



// require('update-notifier.php');

if( ! function_exists( 'bwCheckForUpdates' ) ){
    function bwCheckForUpdates( $update, $plugin_data, $plugin_file ){

        exit();
        
        static $response = false;
        
        if( empty( $plugin_data['UpdateURI'] ) || ! empty( $update ) )
            return $update;
        
        if( $response === false )
            $response = wp_remote_get( $plugin_data['UpdateURI'] );
        
        if( empty( $response['body'] ) )
            return $update;
        
        $custom_plugins_data = json_decode( $response['body'], true );
        
        if( ! empty( $custom_plugins_data[ $plugin_file ] ) )
            return $custom_plugins_data[ $plugin_file ];
        else
            return $update;
    }
    
    add_filter('update_plugins_bluewren.uat-web.co.uk', 'bwCheckForUpdates', 10, 3);
}

// add_filter( 'update_plugins_github.com', 'self_update', 10, 4 );

/**
 * Check for updates to this plugin
 *
 * @param array  $update   Array of update data.
 * @param array  $plugin_data Array of plugin data.
 * @param string $plugin_file Path to plugin file.
 * @param string $locales    Locale code.
 *
 * @return array|bool Array of update data or false if no update available.
 */
function self_update( $update, array $plugin_data, string $plugin_file, $locales ) {
    // wp_mail('mark.southall@bluewren.co.uk', 'function check', 'bw-01');
    // print_r('yes11');
    // exit();

	// only check this plugin
	if ( 'your-custom-plugin/your-custom-plugin.php' !== $plugin_file ) {
		return $update;
	}

	// already completed update check elsewhere
	if ( ! empty( $update ) ) {
		return $update;
	}

	// let's go get the latest version number from GitHub
	$response = wp_remote_get(
		'https://api.github.com/repos/your-org/your-custom-plugin/releases/latest',
		array(
			'user-agent' => 'YOUR_GITHUB_USERNAME',
		)
	);

	if ( is_wp_error( $response ) ) {
		return;
	} else {
		$output = json_decode( wp_remote_retrieve_body( $response ), true );
	}

	$new_version_number  = $output['tag_name'];
	$is_update_available = version_compare( $plugin_data['Version'], $new_version_number, '<' );

	if ( ! $is_update_available ) {
		return false;
	}

	$new_url     = $output['html_url'];
	$new_package = $output['assets'][0]['browser_download_url'];

	error_log('$plugin_data: ' . print_r( $plugin_data, true ));
	error_log('$new_version_number: ' . $new_version_number );
	error_log('$new_url: ' . $new_url );
	error_log('$new_package: ' . $new_package );

	return array(
		'slug'    => $plugin_data['TextDomain'],
		'version' => $new_version_number,
		'url'     => $new_url,
		'package' => $new_package,
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