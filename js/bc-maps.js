// friendly token from http://code.google.com/intl/de-DE/apis/maps/documentation/javascript/examples/map-geolocation.html

/**
 * Set some general vars
 */
var initialLocation;
var newyork = new google.maps.LatLng(40.69847032728747, -73.9514422416687);
var browserSupportFlag =  new Boolean();
var map;
var infowindow = new google.maps.InfoWindow();
var zoom = 2;
var edit_mark = false;	

/**
 * Initialize Maps
 * @param element_id
 */
function bc_maps_initialize(element_id, getgeolocation, zoom) {
	var myOptions = {
		zoom: zoom,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	map = new google.maps.Map(document.getElementById(element_id), myOptions);
	
	if(getgeolocation == false){
		map.setCenter(initialLocation);
	}
  	
	if(getgeolocation == true){
	  // Try W3C Geolocation method (Preferred)
	  if(navigator.geolocation) {
	    browserSupportFlag = true;
	    navigator.geolocation.getCurrentPosition(function(position) {
	      initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
	      contentString = "Location found using W3C standard";
	      map.setCenter(initialLocation);
	      infowindow.setContent(contentString);
	      infowindow.setPosition(initialLocation);
	      //infowindow.open(map);
	    }, function() {
	      handleNoGeolocation(browserSupportFlag);
	    });
	  } else if (google.gears) {
	    // Try Google Gears Geolocation
	    browserSupportFlag = true;
	    var geo = google.gears.factory.create('beta.geolocation');
	    geo.getCurrentPosition(function(position) {
	      initialLocation = new google.maps.LatLng(position.latitude,position.longitude);
	      contentString = "Location found using Google Gears";
	      map.setCenter(initialLocation);
	      infowindow.setContent(contentString);
	      infowindow.setPosition(initialLocation);
	      //infowindow.open(map);
	    }, function() {
	      handleNoGeolocation(browserSupportFlag);
	    });
	  } else {
	    // Browser doesn't support Geolocation
	    browserSupportFlag = false;
	    handleNoGeolocation(browserSupportFlag);
	  }
	}
  
  //check if markers available
	try {
	  if( typeof(bc_maps_markers) !== "undefined"){
		  
		  var markers = [];

		  var data = bc_maps_markers;
		  // build markers from json object
		  for (var i in data){
	          var latLng = new google.maps.LatLng(data[i].lat, data[i].lng);
	          var marker = new google.maps.Marker({
	        	position: latLng,
	           	map: map,
	           	title: data[i].name           
	          });
	          markers.push(marker);
		  }
		  
		  // cluster multiple values
		  var markerCluster = new MarkerClusterer(map, marker);
	  }
	
	} catch (err){
		//alert(err);  
	}
  
  //check if function to edit location exists
  try {
	  if( typeof window.bc_maps_init_dynamic_js == 'function'){
		bc_maps_init_dynamic_js();
	  }
  } catch (err){
	  //handle err
  }

  
}

/**
 * Handle no browsersupport for geolocation
 * show info window
 * 
 */
function handleNoGeolocation(errorFlag) {
  if (errorFlag == true) {
    initialLocation = newyork;
    contentString = "Error: The Geolocation service failed.";
  } else {
    contentString = "Error: Your browser doesn't support geolocation.";
  }
  map.setCenter(initialLocation);
  infowindow.setContent(contentString);
  infowindow.setPosition(initialLocation);
  //infowindow.open(map);
}

/**
 * function to genrate marker
 * @param lat
 * @param lng
 * @param map
 * @param title
 */
function bc_maps_generate_marker(lat,lng, map, title){
	var newLatlng = new google.maps.LatLng(lat,lng);
    var marker = new google.maps.Marker({
        position: newLatlng, 
        map: map,
        title:title
    }); 
}


function bc_maps_ajax_save_user_location(map, marker, infowindow){
	
	var data = {
		action: 'bc_maps_ajax_save_user_location',
		position: marker.getPosition()
	};
		
	jQuery.post(ajaxurl, data, function(response) {
		/*
		 var infowindow = new google.maps.InfoWindow({
	        content: response,
	        maxWidth: 200
	    });
	    infowindow.open(map,marker);
	    */
	    data = response;
	    
	});			
	
	
	infowindow.setContent(data);
    infowindow.open(map, marker);

	
}

function bc_maps_loader(action){
	
	if(action == 'hide'){
		
		
	}
}