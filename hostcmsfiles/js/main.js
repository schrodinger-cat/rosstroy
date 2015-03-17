$(document).ready(function(){
	h = $(document).height();
	$('.r-main').height(h);
});

function initialize() {
	var myLatlng = new google.maps.LatLng(48.7017566,44.4978932);
	var markerLat = new google.maps.LatLng(48.7017566,44.4978932);

	var mapOptions = {
		zoom: 16,
		center: myLatlng,
		disableDefaultUI: true,
		scrollwheel: false,
		scaleControl: false
	}

	var map = new google.maps.Map(document.getElementById('r-map'), mapOptions);

	var marker = new google.maps.Marker({
		position: markerLat,
		map: map
	});
}

google.maps.event.addDomListener(window, 'load', initialize);
