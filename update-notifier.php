<?php
/**************************************************************
 *                                                            *
 *   Provides a notification to the user everytime            *
 *   your WordPress plugin is updated                         *
 *															  *
 *	 Based on the script by Unisphere:						  *
 *   https://github.com/unisphere/unisphere_notifier          *
 *                                                            *
 *   Author: Pippin Williamson                                *
 *   Profile: http://codecanyon.net/user/mordauk              *
 *   Follow me: http://twitter.com/pippinsplugins             *
 *                                                            *
 **************************************************************/
 
/*
	Replace BW and bw by your plugin prefix to prevent conflicts between plugins using this script.
*/

function debug()
{
	if( is_user_logged_in() ) {
		$user = wp_get_current_user();
		$roles = ( array ) $user->roles;
		// Use this to return a single value
	} else {
		return array();
	}
}
// add_action('admin_init', 'debug');

// ini_set('display_errors', 1);

// Constants for the plugin name, folder and remote XML url
define( 'BW_NOTIFIER_PLUGIN_NAME', 'BWPuffin' ); // The plugin name
define( 'BW_NOTIFIER_PLUGIN_SHORT_NAME', 'Puffin' ); // The plugin short name, only if needed to make the menu item fit. Remove this if not needed
define( 'BW_NOTIFIER_PLUGIN_FOLDER_NAME', 'bw-puffin-email' ); // The plugin folder name
define( 'BW_NOTIFIER_PLUGIN_FILE_NAME', 'BWPuffin.php' ); // The plugin file name
define( 'BW_NOTIFIER_PLUGIN_XML_FILE', 'https://bluewren.uat-web.co.uk/wp-content/uploads/update-plugins/notifier.xml' ); // The remote notifier XML file containing the latest version of the plugin and changelog
define( 'BW_PLUGIN_NOTIFIER_CACHE_INTERVAL', 1 ); // The time interval for the remote XML cache in the database (21600 seconds = 6 hours)

// Adds an update notification to the WordPress Dashboard menu
function bw_update_plugin_notifier_menu() {  
	if ( function_exists( 'simplexml_load_string' ) ) { // Stop if simplexml_load_string funtion isn't available
	    $xml 			= bw_get_latest_plugin_version( BW_PLUGIN_NOTIFIER_CACHE_INTERVAL ); // Get the latest remote XML file on our server
		$plugin_data 	= get_plugin_data( WP_PLUGIN_DIR . '/' . BW_NOTIFIER_PLUGIN_FOLDER_NAME . '/' . BW_NOTIFIER_PLUGIN_FILE_NAME ); // Read plugin current version from the style.css

		if ( (string) $xml->latest > (string) $plugin_data['Version'] ) { // Compare current plugin version with the remote XML version
			if ( defined( 'BW_NOTIFIER_PLUGIN_SHORT_NAME' ) ) {
				$menu_name = BW_NOTIFIER_PLUGIN_SHORT_NAME;
			} else {
				$menu_name = BW_NOTIFIER_PLUGIN_NAME;
			}
			add_dashboard_page( BW_NOTIFIER_PLUGIN_NAME . ' Plugin Updates', $menu_name . ' <span class="update-plugins count-1"><span class="update-count">New Updates</span></span>', 'administrator', 'bw-plugin-update-notifier', 'bw_update_notifier');
		}
	}	
}
add_action('admin_menu', 'bw_update_plugin_notifier_menu');  
 
// Adds an update notification to the WordPress 3.1+ Admin Bar
function bw_update_notifier_bar_menu() {
	if ( function_exists( 'simplexml_load_string' ) ) { // Stop if simplexml_load_string funtion isn't available
		global $wp_admin_bar, $wpdb;

		if ( ! is_super_admin() || ! is_admin_bar_showing() ) // Don't display notification in admin bar if it's disabled or the current user isn't an administrator
		return;

		$xml 		= bw_get_latest_plugin_version( BW_PLUGIN_NOTIFIER_CACHE_INTERVAL ); // Get the latest remote XML file on our server
		$plugin_data 	= get_plugin_data( WP_PLUGIN_DIR . '/' . BW_NOTIFIER_PLUGIN_FOLDER_NAME . '/' .BW_NOTIFIER_PLUGIN_FILE_NAME ); // Read plugin current version from the main plugin file

		if( (string) $xml->latest > (string) $plugin_data['Version'] ) { // Compare current plugin version with the remote XML version
			$wp_admin_bar->add_menu( array( 'id' => 'plugin_update_notifier', 'title' => '<span>' . BW_NOTIFIER_PLUGIN_NAME . ' <span id="ab-updates">New Updates</span></span>', 'href' => get_admin_url() . 'index.php?page=bw-plugin-update-notifier' ) );
		}
	}
}
add_action( 'admin_bar_menu', 'bw_update_notifier_bar_menu', 1000 );

// The notifier page
function bw_update_notifier() { 
	$xml 			= bw_get_latest_plugin_version( BW_PLUGIN_NOTIFIER_CACHE_INTERVAL ); // Get the latest remote XML file on our server
	$plugin_data 	= get_plugin_data( WP_PLUGIN_DIR . '/' . BW_NOTIFIER_PLUGIN_FOLDER_NAME . '/' .BW_NOTIFIER_PLUGIN_FILE_NAME ); // Read plugin current version from the main plugin file ?>

	<style>
		.update-nag { display: none; }
		#instructions {max-width: 670px;}
		h3.title {margin: 30px 0 0 0; padding: 30px 0 0 0; border-top: 1px solid #ddd;}
	</style>

	<div class="wrap">

		<div id="icon-tools" class="icon32"></div>
		<h2><?php echo BW_NOTIFIER_PLUGIN_NAME ?> Plugin Updates</h2>
	    <div id="message" class="updated below-h2"><p><strong>There is a new version of the <?php echo BW_NOTIFIER_PLUGIN_NAME; ?> plugin available.</strong> You have version <?php echo $plugin_data['Version']; ?> installed. Update to version <?php echo $xml->latest; ?>.</p></div>
		
		<div id="instructions">
		    <h3>Update</h3>
			<?php
			print_r(print_r('<pre>'));
			print_r(get_plugins());
			print_r(print_r('</pre>'));
	
			$plugin_name = 'go-live-update-urls';
			$install_link = '<a href="' . esc_url( get_admin_url().'plugin-install.php?tab=plugin-information&plugin=' . $plugin_name . '&TB_iframe=true&width=600&height=550'  ) . '" class="thickbox" title="More info about ' . $plugin_name . '">Install ' . $plugin_name . '</a>';
			echo $install_link;
			?>
			<a class="button" href="<?php echo get_admin_url().'?index.php&page=bw-plugin-update-notifier&pluginaction=updatepuffin'; ?>">Install Version <?php echo $xml->latest; ?></a>
		</div>
	    
	    <h3 class="title">Changelog</h3>
	    <?php echo $xml->changelog; ?>

		<?php if($_GET['pluginaction'] ?? false){
		if( $_GET['pluginaction']== 'updatepuffin'){
			


			
			// $upgrader = new \Plugin_Notifier();
			// $upgraded = $upgrader->upgrade( 'bw-puffin-email' );
		}
	}?>

	</div>
    
<?php } 

// Get the remote XML file contents and return its data (Version and Changelog)
// Uses the cached version if available and inside the time interval defined
function bw_get_latest_plugin_version( $interval ) {
	$notifier_file_url = BW_NOTIFIER_PLUGIN_XML_FILE;	
	$db_cache_field = 'notifier-cache';
	$db_cache_field_last_updated = 'notifier-cache-last-updated';
	$last = get_option( $db_cache_field_last_updated );
	$now = time();

	// check the cache
	if ( ! $last || ( ( $now - $last ) > $interval ) ) {
		// cache doesn't exist, or is old, so refresh it
		if( function_exists( 'curl_init' ) ) { // if cURL is available, use it...
			$ch = curl_init( $notifier_file_url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
			$cache = curl_exec( $ch );
			curl_close( $ch );
		} else {
			$cache = file_get_contents( $notifier_file_url ); // ...if not, use the common file_get_contents()
		}

		if ( $cache ) {			
			// we got good results	
			update_option( $db_cache_field, $cache );
			update_option( $db_cache_field_last_updated, time() );
		} 
		// read from the cache file
		$notifier_data = get_option( $db_cache_field );
	}
	else {
		// cache file is fresh enough, so read from it
		$notifier_data = get_option( $db_cache_field );
	}

	// Let's see if the $xml data was returned as we expected it to.
	// If it didn't, use the default 1.0 as the latest version so that we don't have problems when the remote server hosting the XML file is down
	if( strpos( (string) $notifier_data, '<notifier>' ) === false ) {
		$notifier_data = '<?xml version="1.0" encoding="UTF-8"?><notifier><latest>1.0</latest><changelog></changelog></notifier>';
	}

	// Load the remote XML data into a variable and return it
	$xml = simplexml_load_string( $notifier_data ); 

	return $xml;
}

function bw_check_updating()
{
	
}
add_action('admin_menu', 'bw_check_updating');