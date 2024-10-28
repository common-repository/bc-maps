<?php


class Maps {
	
	public $loggedin_user_location;
	public $displayed_user_location;
	public $multi_user_locations;
	
	public function __construct(){
		if( is_user_logged_in() )
			$this->loggedin_user_location = get_user_meta( bp_loggedin_user_id() , 'bc_maps_location');

		if( bp_displayed_user_id() )
			$this->displayed_user_location = get_user_meta(bp_displayed_user_id(), 'bc_maps_location');
	}
	
	public function get_user_location($user_id = false ){
		
		if(!$user_id)
			$user_id = bp_loggedin_user_id();
			
		return get_user_meta($user_id, 'bc_maps_location');
	}
	
	public function set_user_location($location){
		update_user_meta(bp_loggedin_user_id(), 'bc_maps_location', $location);
	}
	
	/**
	 * Get members locations
	 * Get all members location from component
	 * @param string $component
	 */
	public function get_all_members_locations($component, $id = false){
		global $bp, $wpdb;

		if(! $table_user_meta = _get_meta_table('user') )
			return false;
		
		// get all mebers @TODO This may be requieres solr to prevent server crash
		if( $component == 'members_directory'){
			$users = $wpdb->get_results( $wpdb->prepare( "
				SELECT {$table_user_meta}.user_id, {$table_user_meta}.meta_value, {$wpdb->users}.display_name, {$wpdb->users}.user_url
				FROM {$table_user_meta}, {$wpdb->users}
				WHERE {$table_user_meta}.user_id = {$wpdb->users}.ID AND {$table_user_meta}.meta_key = 'bc_maps_location';		
			"));
		}
		// get all group mebers
		if($component == 'group' && $id){
			$users = $wpdb->get_results( $wpdb->prepare( "
				SELECT {$table_user_meta}.user_id, {$table_user_meta}.meta_value, {$wpdb->users}.display_name, {$wpdb->users}.user_url
				FROM {$table_user_meta}, {$wpdb->users}, {$bp->groups->table_name_members}
				WHERE {$table_user_meta}.user_id = {$wpdb->users}.ID 
					AND {$table_user_meta}.meta_key = 'bc_maps_location' 
					AND {$bp->groups->table_name_members}.user_id = {$table_user_meta}.user_id 
					AND {$bp->groups->table_name_members}.group_id = {$id}
					AND {$bp->groups->table_name_members}.is_confirmed = 1
					AND {$bp->groups->table_name_members}.is_banned = 0;
			"));
		}
		
		// get all project members mebers this requieres the bettercodes plugin
		if($component == 'project' && $id){
			$users = $wpdb->get_results( $wpdb->prepare( "
				SELECT {$table_user_meta}.user_id, {$table_user_meta}.meta_value, {$wpdb->users}.display_name, {$wpdb->users}.user_url
				FROM {$table_user_meta}, {$wpdb->users}, {$bp->projects->table_name_members}
				WHERE {$table_user_meta}.user_id = {$wpdb->users}.ID 
					AND {$table_user_meta}.meta_key = 'bc_maps_location' 
					AND {$bp->projects->table_name_members}.user_id = {$table_user_meta}.user_id 
					AND {$bp->projects->table_name_members}.project_id = {$id}
					AND {$bp->projects->table_name_members}.is_confirmed = 1
					AND {$bp->projects->table_name_members}.is_banned = 0;
			"));
		}
		
		// get all friends
		if($component == 'friends' && $id){
			
			$friends = bp_get_friend_ids();
			
			if(!empty($friends)){
				$users = $wpdb->get_results( $wpdb->prepare( "
					SELECT {$table_user_meta}.user_id, {$table_user_meta}.meta_value, {$wpdb->users}.display_name, {$wpdb->users}.user_url
					FROM {$table_user_meta}, {$wpdb->users}
					WHERE {$table_user_meta}.user_id = {$wpdb->users}.ID AND {$table_user_meta}.meta_key = 'bc_maps_location' AND {$table_user_meta}.user_id IN($friends);		
				"));				
			}

		}
		
			
		if($users){
			
			$this->prepare_json_location_array($users);
				
			if($this->multi_user_locations )	
				return true;
		}

		return false;
			
	}
	
	public function get_friends_locations(){
		global $bp,$wpdb;
		
	}
	
	public function get_group_members_location(){
		global $bp,$wpdb;
		
	}
	
	/**
	 * Format geo date
	 * Format a string to return an array of geodata
	 * @param unknown_type $location
	 */
	public function format_geo_data($location = '' ){
		
		if($location == '')
			return false;
		
		if( stristr($location, ',') ){
			$location = split(',', $location);
			$location['lat'] = $location[0];
			$location['lng'] = $location[1];
			unset($location[0]);
			unset($location[1]);
			return $location;
		}
		
		return false;
	}
	
	/**
	 * Prepare json data
	 * Prepare the array to be displayed as json data
	 */
	public function prepare_json_location_array($users){
		
		if(!is_array($users))
			return false;
		
		$jsondata = array();	
			
		foreach ($users as $key => $value) {
			
			$latlng = $this->format_geo_data($value->meta_value);
			
			$jsondata[$key]['lat'] = $latlng['lat'];
			$jsondata[$key]['lng'] = $latlng['lng'];
			$jsondata[$key]['name'] = $value->display_name;
			$jsondata[$key]['url'] = $value->user_url;
			
			unset($latlng);
		}
		
		if($jsondata){
			$this->multi_user_locations = $jsondata;
			return true;
		}
		
		return false;
	} 
}

?>