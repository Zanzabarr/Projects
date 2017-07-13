<?php
$query = logged_query_assoc_array("SELECT * FROM markers",null,0,array());

?>
<script src="//maps.googleapis.com/maps/api/js?v=3.exp"></script>


<script type="text/javascript">
	var geocoder;
	var map;
	var panorama;
	var stores = [<?php foreach($query as $marker) { echo "['" . $marker['name'] . "', '" . $marker['lat'] . "', '" . $marker['lng'] . "', '" . $marker['address'] . "', '" . $marker['opt1'] . "'],\n"; }?>];

	function map_initialize(){
		geocoder = new google.maps.Geocoder();
		var latlng = new google.maps.LatLng(51.495065, -119.553223);
		var myOptions = {
			zoom: 6,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		}
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		setMarkers(map, stores);
	}

	function codeAddress(){
		var address = document.getElementById("address").value;
		geocoder.geocode({'address': address}, function(results, status){
			if (status == google.maps.GeocoderStatus.OK){
				map.setCenter(results[0].geometry.location);
				marker = new google.maps.Marker({
					map: map,
					position: results[0].geometry.location,
					title: address
				});
				map.setZoom(11);
				panorama = map.getStreetView();
				panorama.setPosition(results[0].geometry.location);
				panorama.setPov({
					heading: 265,
					zoom:1,
					pitch:0
				});
				//setMarkers(map, stores);
			} else {
				alert("Geocode was not successful for the following reason: " + status);
			}
		});
	}

	function toggleStreetView(){
		var toggle = panorama.getVisible();
		if (toggle == false){
			panorama.setVisible(true);
		} else {
			panorama.setVisible(false);
		}
	}

	function setMarkers(map, stores){
		var image = "//waytogrow.ca/images/icon-store.png";
		for (var i = 0; i < stores.length; i++){
			var store = stores[i];
			var myLatLng = new google.maps.LatLng(store[1], store[2]);
			var marker = new google.maps.Marker({
				position: myLatLng,
				map: map,
				icon: image,
				title: store[0]
			});
			var content = '<strong>' + store[0] + '</strong><br>' + store[3] + '<br><a href="' + store[4] + '" target="_blank">' + store[4] + '</a>';
			informationWindow(marker, content);
		}
	}

	function informationWindow(marker, content){
		var infowindow = new google.maps.InfoWindow({
			content: content
		});
		google.maps.event.addListener(marker, 'click', function(){
			infowindow.open(map,marker);
		});
	}

	$(document).ready(function(){
		map_initialize();
	});
</script>
