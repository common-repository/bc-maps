<?php

/**
 * load google maps api lib
 * 
 */
function bc_maps_load_maps_lib(){
	
	do_action('bc_maps_before_load_libs');
	
	//google maps api
	wp_register_script( 'bc-maps-google-maps-api', apply_filters('bc-maps-google-maps-api-src', 'http://maps.google.com/maps/api/js?sensor=true') );
    wp_enqueue_script( 'bc-maps-google-maps-api' );
    
    //google gears
	wp_register_script( 'bc-maps-google-gears', apply_filters('bc-maps-google-gears-src', 'http://code.google.com/apis/gears/gears_init.js') );
    wp_enqueue_script( 'bc-maps-google-gears' );
    
    // markerclusterer
    wp_register_script( 'bc-maps-markerclusterer', apply_filters('bc-maps-markerclusterer-src', 'http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerclusterer/1.0/src/markerclusterer.js') );
    wp_enqueue_script( 'bc-maps-markerclusterer' );

   	// bc maps data
    wp_register_script( 'bc-maps-json-data', site_url() . "/wp-load.php?action=" . apply_filters('bc_maps_load_json_data', 'bc_maps_load_json_data'));
    wp_enqueue_script( 'bc-maps-json-data' );
    
   	// bc maps js
    wp_register_script( 'bc-maps-general-js', BC_MAPS_PLUGIN_URL . "/js/bc-maps.js");
    wp_enqueue_script( 'bc-maps-general-js' );
    
    // load dynamic js code
    wp_register_script( 'bc-maps-dynamic-js', site_url() . "/wp-load.php?action=" . apply_filters('bc_maps_dynamic_js_action', 'bc_maps_dynamic_js'));
    wp_enqueue_script( 'bc-maps-dynamic-js' );
    
    do_action('bc_maps_after_load_libs');
    
}
add_action('bc_maps_load_lib', 'bc_maps_load_maps_lib');


/**
 * Show json encoded geo data
 * Build a list ob all markers in json format
 */
function bc_maps_show_json_ajax_data( $geo_data = array() ){
	
	do_action('bc_maps_before_show_json_geo_data');
	
	$geo_data = apply_filters('bc_maps_json_geo_data', $geo_data);
	
	if( !empty($geo_data) )
		echo apply_filters('bc_maps_js_markers_name', 'var bc_maps_markers = ') . json_encode( apply_filters('bc_maps_show_json_geo_data', $geo_data) );
	
	do_action('bc_maps_after_show_json_geo_data');
}
add_action('wp_ajax_bc_maps_load_json_data', 'bc_maps_show_json_ajax_data');


/**
 * Show profile marker
 * Show the marker on displayed profile
 * @param array() $geo_data
 */
function bc_maps_show_profile_marker($geo_data){
	if( !bp_displayed_user_id() )
		return $geo_data;
	
	if( bp_current_component() == 'profile' && bp_current_action() == 'edit' && in_array('group', bp_action_variables() ) && in_array('1', bp_action_variables() ) )
		return $geo_data;

	$userData = get_user_meta(bp_displayed_user_id(), 'bc_maps_location');
	
	if($userData[0])
		$userData = Maps::format_geo_data($userData[0]);	

	if(is_array($userData) && !empty($geo_data) )	
		return array_merge( $geo_data, apply_filters('bc_maps_displayed_user_marker_json', array( bp_displayed_user_id() => $userData ) ) );
	
	if(is_array($userData) )	
		return apply_filters('bc_maps_displayed_user_marker_json', array( bp_displayed_user_id() => $userData ) );
	
	return $geo_data;
}
add_filter('bc_maps_json_geo_data', 'bc_maps_show_profile_marker');


/**
 * Show dynamic js code
 * Show js code which is generated dynamically
 */
function bc_maps_load_dynamic_js(){
	do_action('bc_maps_before_dynamic_js');
	echo "function bc_maps_init_dynamic_js() {";
	do_action('bc_maps_show_dynamic_js');
	echo "}";
	do_action('bc_maps_after_dynamic_js');
}
add_action('wp_ajax_bc_maps_dynamic_js', 'bc_maps_load_dynamic_js');


/**
 * Display map
 * Show the map on element
 */
function bc_maps_show_map(){
	echo apply_filters('bc_maps_show_map_div', '<div id="bc-maps-map" style="width:100%; height:400px;"><span class="ajax-loader" style="display:inline; text-align:center;"></span></div>');
	do_action('bc_maps_init_js_to_element');
}

// Show map on users profile page
add_action('bp_after_profile_field_content', 'bc_maps_show_map');
// Show map on users list page
add_action('bp_after_directory_members_list', 'bc_maps_show_map');
// Show map on group page
add_action('bp_inside_after_sidebar', 'bc_maps_show_map');
add_action('bp_after_group_member_widget', 'bc_maps_show_map');
// Show on projects page, this requires the free bettercodes plugin from http://bettercodes.org
add_action('bp_after_project_member_widget', 'bc_maps_show_map');
//show friends map on profile page
add_action('bp_after_sidebar_friends_widget', 'bc_maps_show_map');


/**
 * init the js code
 * init the js functions to load map markers etc.
 */
function bc_maps_init_js(){
?>
<script type="text/javascript">
<!--
	<?php 
		$position = get_user_meta(bp_loggedin_user_id(), 'bc_maps_location');  
		if($position[0])
			$position = Maps::format_geo_data($position[0]);
	?>
	
	<?php if( isset($position['lat']) && isset($position['lng']) ):?>
	var initialLocation = new google.maps.LatLng(<?php echo $position['lat'] ?>,<?php echo $position['lng'] ?>);
	<?php else: ?>
	var initialLocation = newyork;
	var zoom = 2;
	<?php endif;?>
	
	<?php if( bp_loggedin_user_id() == bp_displayed_user_id() && !isset($position['lat']) ): $find_geolocation = 'true'; else: $find_geolocation = 'false'; endif; ?>

	jQuery(window).load(function() {
  		bc_maps_initialize('<?php echo apply_filters('bc_maps_init_map_element', 'bc-maps-map') ?>',<?php echo apply_filters('bc_maps_init_map_get_user_location', $find_geolocation) ?>,<?php echo apply_filters('bc_maps_init_map_zoom', zoom)?>,'<?php echo apply_filters('bc_maps_init_map_type', 'ROADMAP') // This doesent't work yet'?>');
	});
//-->
</script>
<?php
}
add_action('bc_maps_init_js_to_element', 'bc_maps_init_js');


/**
 * Edit location JS
 * Enable user profile settings to edit location
 */
function bc_maps_edit_location(){
	global $bp;
	
	if( !is_user_logged_in() )
		return false;
	
	if( bp_loggedin_user_id() != bp_displayed_user_id() )
		return false;
		
	if( bp_current_component() != 'profile' && bp_current_action() != 'edit' )
		return false;

	if( !in_array('group', bp_action_variables() ) || !in_array('1', bp_action_variables() ) )
		return false; 	
		
	$marker = get_user_meta(bp_loggedin_user_id(), 'bc_maps_location');
	
	if($marker[0]){
		$marker = Maps::format_geo_data($marker[0]);
		$marker = "new google.maps.LatLng({$marker["lat"]},{$marker["lng"]}); var show_initial_hint = false;";
	} else {
		$marker = "initialLocation; var show_initial_hint = true;";
	}
		
	$edit_location_js = <<<EOF
	
		try {
			
			var markerLatlng = {$marker};
			
			google.maps.event.addListener(map, "tilesloaded", function() {
			
				if( window.edit_mark == false ){
						
					map.setCenter(initialLocation);
					
					var marker = new google.maps.Marker({
				        position: markerLatlng, 
				        draggable: true,
				        map: map,
				        title:"Please move to your position."
					});
					
					window.edit_mark = true;
						
					var infowindow = new google.maps.InfoWindow({
							content: '',
							maxWidth: 200
					});	
		
					if( show_initial_hint ){
						infowindow.setContent("You haven't selected your location yet. Please drag this mark to your location.");
						infowindow.open(map,marker);
					}
				
					google.maps.event.addListener(marker, "dragstart", function() {
					  	infowindow.close();
					});
					
					google.maps.event.addListener(marker, "dragend", function() {
					
						infowindow.setContent('Saving your position, please wait...');
						infowindow.open(map,marker);				
						
						position = marker.getPosition();
						lat = position.lat();
						lng = position.lng();
				 		var data = {
							action: 'bc_maps_ajax_save_user_location',
							lat: lat,
							lng: lng
						};
						jQuery.post(ajaxurl, data, function(response) {
							infowindow.setContent(response);
						});					
					});
			
				}
			});
				
		} catch (err){
			alert(err);
		}

EOF;

	echo $edit_location_js;
	 	
	
}
add_action('bc_maps_show_dynamic_js', 'bc_maps_edit_location');


/**
 * Save user position
 * Save the users location if draged around
 */
function bc_maps_save_user_location(){
	
	if( !is_user_logged_in() )
		return false;
		
	if( bp_loggedin_user_id() != bp_displayed_user_id() )
		return false;
		
	if(isset($_POST['lat']) && is_numeric($_POST['lat']) && isset($_POST['lng']) && is_numeric($_POST['lng'])){
		
		if(update_user_meta(bp_loggedin_user_id(), 'bc_maps_location', "{$_POST['lat']},{$_POST['lng']}")){
			echo "Updated your location successfully.";
		} else {
			echo "Sorry, it has been an error while saving your location. Please try again.";
		}
	}
	
}
add_action('wp_ajax_bc_maps_ajax_save_user_location', 'bc_maps_save_user_location');



/**
 * Members data json
 * Build the json data for members directoy
 * @param array $geo_data
 */
function bc_map_members_directory_map_json_data($geo_data) {

	if( bp_current_component() != 'members' || bp_current_action() || bp_action_variables() )
		return $geo_data;
	
	$users = new Maps();
	$users->get_all_members_locations('members_directory');
	
	if(is_array($users->multi_user_locations) && !empty($geo_data) )	
		return array_merge( $geo_data, apply_filters('bc_maps_members_directory_marker_json', $users->multi_user_locations ) );
	
	if(is_array($users->multi_user_locations) )	
		return apply_filters('bc_maps_members_directory_marker_json',$users->multi_user_locations );
	
	
	return $geo_data;
	
}
add_filter('bc_maps_json_geo_data', 'bc_map_members_directory_map_json_data');


/**
 * Groups maps
 * Show member map on groups page
 * @param array $geo_data
 */
function bc_maps_group_member_map($geo_data) {
	global  $bp;

	if( !bp_get_current_group_name() || !$bp->groups->current_group->id )
		return $geo_data;
		
	
	$users = new Maps();
	$users->get_all_members_locations('group', $bp->groups->current_group->id );
	
	if(is_array($users->multi_user_locations) && !empty($geo_data) )	
		return array_merge( $geo_data, apply_filters('bc_maps_group_marker_json', $users->multi_user_locations ) );
	
	if(is_array($users->multi_user_locations) )	
		return apply_filters('bc_maps_group_marker_json',$users->multi_user_locations );
		
	
		
	return $geo_data;
	
}
add_filter('bc_maps_json_geo_data', 'bc_maps_group_member_map');


/**
 * Projects maps
 * Show member map on projects page, this requieres the bettercodes plugins from http://bettercodes.org
 * @param array $geo_data
 */
function bc_maps_project_member_map($geo_data) {
	global  $bp;
	if(function_exists('bc_core_install')){
	
		if( ! $bp->projects->current_project->id)
			return $geo_data;
		
		$users = new Maps();
		$users->get_all_members_locations('project', $bp->projects->current_project->id );
		
		if(is_array($users->multi_user_locations) && !empty($geo_data) )	
			return array_merge( $geo_data, apply_filters('bc_maps_project_marker_json', $users->multi_user_locations ) );
		
		if(is_array($users->multi_user_locations) )	
			return apply_filters('bc_maps_project_marker_json',$users->multi_user_locations );
			
	}	
			
	return $geo_data;
}
add_filter('bc_maps_json_geo_data', 'bc_maps_project_member_map');

/**
 * Profile map
 * Show map on users profile
 * @param array $geo_data
 */
function bc_maps_profile_member_map($geo_data) {
	global  $bp;
	
	
	if( ! bp_displayed_user_id() )
		return $geo_data;
	
	if( ! $marker = get_user_meta(bp_displayed_user_id(), 'bc_maps_location') )
		return $geo_data;

	$marker = Maps::format_geo_data($marker[0]);	
	
	if(!is_array($marker))
		return false;
		
	$user[0]['name'] = bp_get_displayed_user_fullname();
	$user[0]['lat'] = $marker['lat'];
	$user[0]['lng'] = $marker['lng'];
	$user[0]['url'] = bp_get_displayed_user_link();

	if(is_array($user) && !empty($geo_data) )	
		return array_merge( $geo_data, apply_filters('bc_maps_profile_marker_json', $user) );
	
	if(is_array($user) )	
		return apply_filters('bc_maps_profile_marker_json', $user );

			
	return $geo_data;
}
add_filter('bc_maps_json_geo_data', 'bc_maps_profile_member_map');


/**
 * Friends Map
 * Show friends on a map
 * @param unknown_type $geo_data
 */
function bc_maps_friends_member_map($geo_data) {
	global  $bp;

	if( ! bp_displayed_user_id() )
		return $geo_data;
		
	if(bp_current_component() == 'profile' || bp_current_action() == 'edit')
		return $geo_data;
	
	$users = new Maps();
	$users->get_all_members_locations('friends', bp_displayed_user_id() );
	
	if(is_array($users->multi_user_locations) && !empty($geo_data) )	
		return array_merge( $geo_data, apply_filters('bc_maps_friends_marker_json', $users->multi_user_locations ) );
	
	if(is_array($users->multi_user_locations) )	
		return apply_filters('bc_maps_friends_marker_json',$users->multi_user_locations );
		
	
		
	return $geo_data;
}
add_filter('bc_maps_json_geo_data', 'bc_maps_friends_member_map');

/**
 * Google Maps ip locate
 * Function to detect users position with google maps ip-based api
 */
function bc_maps_google_ip_based_location() {
	
	if( !is_user_logged_in() )
		return false;

	// ckeck if user location is alredy set
	if( get_user_meta(bp_loggedin_user_id(), 'bc_maps_location') )
		return false;
	
	if( !isset($_POST['action']) || $_POST['action'] != 'bc_maps_auto_locate_ip' ){
		wp_register_script( 'bc-maps-google-jsapi-location', apply_filters('bc-maps-google-jsapi-location-src', 'http://www.google.com/jsapi') );
	    wp_enqueue_script( 'bc-maps-google-jsapi-location' );
	}
    
    if( isset($_POST['action']) && $_POST['action'] == 'bc_maps_auto_locate_ip'  && isset($_POST['_nonce']) &&  wp_verify_nonce( $_POST['_nonce'], 'bc_maps_save_user_auto_ip_located_position') ){
    	$position = new Maps();
    	$position->set_user_location($_POST['lat'] . ',' . $_POST['lng']);
    }
    	
		
}
add_action('wp_enqueue_scripts', 'bc_maps_google_ip_based_location');
add_action('wp_ajax_bc_maps_auto_locate_ip', 'bc_maps_google_ip_based_location');


function bc_maps_save_auto_ip_base_location(){

	if( !is_user_logged_in() )
		return false;
	
	if( get_user_meta(bp_loggedin_user_id(), 'bc_maps_location') )
		return false;

	$nonce= wp_create_nonce('bc_maps_save_user_auto_ip_located_position');
	
	$save_location = <<<EOF
<script type="text/javascript">	
	function bc_maps_start_ip_location(){
		
		if (google.loader.ClientLocation) {
			
			var data = {
				action: 'bc_maps_auto_locate_ip',
				_nonce: '$nonce',
				lat: google.loader.ClientLocation.latitude,
				lng: google.loader.ClientLocation.longitude
			};
		
			jQuery.post(ajaxurl, data, function(response) {
				//alert('Got this from the server: ' + response);
			});
		
	    }
	
	
	}
	
	jQuery(document).ready(function() {
		bc_maps_start_ip_location();
		//google.load("", "3",  {callback: bc_maps_start_ip_location, other_params:"sensor=false"});
	});

</script>
EOF;

	echo $save_location;
	
}
add_action('wp_head', 'bc_maps_save_auto_ip_base_location');



?>