$(document).ready(function(){
	h = $(document).height();
	$('.r-main').height(h);
});


is_hide = 0;

$('.r-projects__hide').click(function(){
	console.log('hide');
	
	if(is_hide == 0) {
		$('.r-menu').hide();
		$('.r-language').hide();
		$('.r-content').hide();

		is_hide = 1;
	} else {
		$('.r-menu').show();
		$('.r-language').show();
		$('.r-content').show();

		is_hide = 0;
	}
});

cnt_hide = 0;

$('.r-content__close').click(function(){
	if (cnt_hide == 0) {
		$('.r-content').addClass('r-content_hide');
		$('.r-content__text').hide();

		cnt_hide = 1;
	} else {
		$('.r-content').removeClass('r-content_hide');
		$('.r-content__text').show();

		cnt_hide = 0;
	}
	
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

