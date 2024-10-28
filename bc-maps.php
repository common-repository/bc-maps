<?php
/*
Plugin Name: BC Maps
Plugin URI: http://bettercodes.org
Description: This Plugins gives Buddypress and bettercodes a user, groups and Projects geo localisation feature
Version: 0.1
Author: Niklas Guenther http://bettercodes.org/members/guenther/
Author URI: http://bettercodes.org
License: GPL2
Revision Date: January 06 2010
Requires at least: WP 3.0.4
Tested up to: WP 3.0.4
*/

define( 'BC_MAPS_VERSION', '0.0.1' );
//define( 'BC_MAPS_DB_VERSION', '1' );
define( 'BC_MAPS_PLUGIN_DIR', WP_PLUGIN_DIR . '/bc-maps' );
define( 'BC_MAPS_PLUGIN_URL', plugins_url( $path = '/bc-maps' ) );

include_once 'Classes/Maps.php';

include_once 'bc-maps-templatetags.php';

/**
 * Load scripts to head
 * 
 */
function bc_maps_load_head(){
	global $bp;
	
	if(!$bp)
		return false;
			
	if( in_array($bp->current_component, apply_filters('bc-maps-load-component-header', array('groups', 'members', 'projects', 'profile', 'activity', 'friends' )) ) )
		do_action('bc_maps_load_lib');

	// remove sidebar widget on profiles page
	if(bp_current_component() == 'profile' || bp_current_component() == 'friends')
		remove_action('bp_after_sidebar_friends_widget', 'bc_maps_show_map');
	

}
add_action('wp_enqueue_scripts', 'bc_maps_load_head');


?>
