//(function (win, doc) {

	window.app = {};
	var app = window.app;
	var zoomLevel = 10;
	var center = [16.7583, 43.14370];
	var map = null;
	var geo = navigator.geolocation;
	var gnk = "/geonetwork/srv/eng/catalog.search#/search";
	var myProjectionName = "EPSG:3857";
	var poi_enabled = $('#geoinput-poi').parent().hasClass('toggle btn btn-primary');
	var geoloc_enabled = $('#geoinput-geoloc').parent().hasClass('toggle btn btn-primary');

	if (window.location.hash !== '') {
		var hash = window.location.hash.replace('#c=', '');
		var parts = hash.split(':');
		if (parts.length === 3) {
			zoomLevel = parseInt(parts[2], 10);
			center = [parseFloat(parts[0]), parseFloat(parts[1])];
		}
	}
	function getLocation() {
		if (geo) {
			alert("Geolocation API is supported");
			var timeoutVal = 10 * 1000 * 1000;
			geo.getCurrentPosition(displayLocation(position), displayError, {
				enableHighAccuracy : true,
				timeout : timeoutVal,
				maximumAge : 0
			});
		} else {
			alert("Oops, Geolocation API is not supported");
		}
		return x;

	}
	var category = '';
	var searchText = '';
	var category = '';

	function getCategory() {
		category = '';
		var searchConcept = $("#search_concept").text();
		if (searchConcept === "Land Cover") {
			category += "&_cat=demlas_lc";
			searchText += "<li>Theme: Land Cover</li>";
		}
		if (searchConcept === "Cadastra Parcels") {
			category += "&_cat=demlas_cp";
			searchText += "<li>Theme: Cadastral parcels</li>";
		}
		if (searchConcept === "Orthoimagery") {
			category += "&_cat=demlas_oi";
			searchText += "<li>Theme: Orthoimagery data</li>";
		}
		if (searchConcept === "Observation Data") {
			category += "&_cat=demlas_obs";
			searchText += "<li>Theme: Observation data</li>";
		}
		if (searchConcept === "All data") {
			category += "";
			searchText += "<li>All data</li>";
		}
	}

	function displayError(error) {
		var errors = {
			1 : 'Permission denied',
			2 : 'Position unavailable',
			3 : 'Request timeout'
		};
		alert("Error: " + errors[error.code]);
	}

	/**
	 ** GEOLOCATION
	 **/
	var locationCircle = new ol.Feature();
	var locationSource = new ol.source.Vector({
			projection : 'EPSG:4326',
		});

	var geolocation = new ol.Geolocation({
			projection : myProjectionName,
			tracking : true,
			trackingOptions : {
				enableHighAccuracy : true
			}
		});
	var locationLayer = new ol.layer.Vector({
			source : locationSource,
			style : [
				new ol.style.Style({
					stroke : new ol.style.Stroke({
						color : 'blue',
						width : 3
					}),
					text : new ol.style.Text({
						textAlign : "Start",
						textBaseline : "Middle",
						font : 'Normal 12px Arial',
						text : 'You might be approximatelly here',
						fill : new ol.style.Fill({
							color : '#ffa500'
						}),
						stroke : new ol.style.Stroke({
							color : '#000000',
							width : 3
						}),
						offsetX : -85,
						offsetY : 0,
						rotation : 0
					}),
					fill : new ol.style.Fill({
						color : 'rgba(0, 0, 255, 0.33)'
					})
				})]
		});
	/*
	if(window.confirm("Do you wanna be geolocated by the app?")){

	geolocation.once('change', function() {
	var position = this.getPosition();
	var speed = this.getSpeed();
	var altitude = this.getAltitude();
	var heading = this.getHeading();
	var accuracy = this.getAccuracy();
	locationCircle = new ol.Feature();
	locationSource = new ol.source.Vector({
	projection: 'EPSG:4326',
	});

	//map.getView().setCenter(position);
	locationCircle.setGeometry(new ol.geom.Circle(position, accuracy)
	);
	locationSource.addFeature(locationCircle);
	map.addLayer(locationLayer);
	var extent = locationLayer.getSource().getExtent();
	map.getView().fit(extent, map.getSize());
	map.getView().setZoom(18);

	});


	}
	 */
	/**
	 * OPENLAYERS MAP DEFINITION
	 */
	var googleLayer = new olgm.layer.Google();
	var vectorSource = new ol.source.Vector();
	var vectorLayer = new ol.layer.Vector({
			source : vectorSource
		});

	var osmLayer = new ol.layer.Tile({
			title : "Open StreetMap",
			visible : true,
			opacity : 0.5,
			source : new ol.source.OSM()
		});

	var demlas_base = new ol.layer.Tile({
			source : new ol.source.XYZ({
				crossOrigin : null,
				url : 'https://demlas.geof.unizg.hr/geoserver/gwc/service/tms/1.0.0/demlas_oi:demlas_base/EPSG:3857@png/{z}/{x}/{-y}.png'
			}),
			title : "DEMLAS Base Map",
			visible : true,
			opacity : 1
		});

	var dof = new ol.layer.Tile({
			source : new ol.source.XYZ({
				crossOrigin : null,
				url : 'https://demlas.geof.unizg.hr/geoserver/gwc/service/tms/1.0.0/demlas_ext:DOF/EPSG:3857@png/{z}/{x}/{-y}.png'
			}),
			title : "Ortophoto- DOF5 (DGU geoportal)",
			visible : false,
			opacity : 0.75
		});

	var dof_toponimi = new ol.layer.Tile({
			source : new ol.source.XYZ({
				crossOrigin : null,
				url : 'https://demlas.geof.unizg.hr/geoserver/gwc/service/tms/1.0.0/demlas_ext:DOF_TOPONIMI/EPSG:3857@png/{z}/{x}/{-y}.png'
			}),
			title : "Ortophoto with toponyms - DOF5 (DGU geoportal)",
			visible : false,
			opacity : 0.75
		});

	var hok = new ol.layer.Tile({
			source : new ol.source.XYZ({
				crossOrigin : null,
				url : 'https://demlas.geof.unizg.hr/geoserver/gwc/service/tms/1.0.0/demlas_ext:HOK/EPSG:3857@png/{z}/{x}/{-y}.png'
			}),
			title : "Craotian base map 1:5000 (DGU geoportal)",
			visible : false,
			opacity : 0.75
		});

	var tk25 = new ol.layer.Tile({
			source : new ol.source.XYZ({
				crossOrigin : null,
				url : 'https://demlas.geof.unizg.hr/geoserver/gwc/service/tms/1.0.0/demlas_ext:TK25/EPSG:3857@png/{z}/{x}/{-y}.png'
			}),
			title : "Topographic map 1:25000 (DGU geoportal)",
			visible : false,
			opacity : 0.75
		});
	var map = new ol.Map({
			layers : [
				new ol.layer.Group({
					title : 'Base Maps',
					layers : [dof, dof_toponimi, hok, tk25, osmLayer, demlas_base]
				})/*,

				new ol.layer.Group({
				title : 'Overlays Data',
				layers : [vectorLayer]
				}),
				new ol.layer.Group({
				title : 'Cadastral Parcels',
				layers : []
				}),
				new ol.layer.Group({
				title : 'Orthoimagery',
				layers : []
				}),
				new ol.layer.Group({
				title : 'Land Cover',
				layers : []
				}),
				new ol.layer.Group({
				title : 'Elevation',
				layers : []
				}),
				new ol.layer.Group({
				title : 'Observations',
				layers : []
				})
				 */

			],
			//interactions: olgm.interaction.defaults(),
			//overlays : [overlay],
			target : 'map',
			view : new ol.View({
				center : ol.proj.fromLonLat(center),
				//center: centerPosition,
				zoom : zoomLevel
			})
		});

	/*
	var olGM = new olgm.OLGoogleMaps({map: map,
	mapIconOptions: {
	useCanvas: true
	}});
	 */
	// map is the ol.Map instance
	//olGM.activate();

	var layerSwitcher = new ol.control.LayerSwitcher({
			tipLabel : 'Layer list'
		});
	map.addControl(layerSwitcher);

	map.on('moveend', function () {
		var view = map.getView();
		var center = ol.proj.transform(view.getCenter(), 'EPSG:3857', 'EPSG:4326');
		window.location.hash =
			//c=45.7078:17.8258:7
			'c=' + center[0] + ':' + center[1] + ':' + view.getZoom();
	});

	$("#languages").on('click', (function openLocation() {
			var view = map.getView();
			var center = ol.proj.transform(view.getCenter(), 'EPSG:3857', 'EPSG:4326');
			window.location = '/warehouse/portal/#c=' + center[0] + ':' + center[1] + ':' + view.getZoom();
		}));

	map.once('postrender', function () {
		//var buttonLocate = $('<button id="locateButton" type="button" title="Locate" style="background: rgba(0,60,136,.5) url(src/img/locate.png); background-size: 30px 30px; background-repeat: no-repeat; "></button>');
		var buttonLocate = $('<button id="locateButton" type="button" title="Locate" class="btn btn-default btn-lg" style="position:fixed;bottom: 3em;left: 0.4em; z-index: 10; "><img src="../src/img/locate.png" height="30"/></button>');

		buttonLocate.click(function () {
			//alert("LOCATE FIRED");
			map.removeLayer(locationLayer);
			locationSource.clear();

			var position = geolocation.getPosition();
			var position4326 = ol.coordinate.toStringXY(ol.proj.transform(position, 'EPSG:3857', 'EPSG:4326'), 6);
			var projection3765 = new ol.proj.Projection({
					code : 'EPSG:3765',
					extent : [250515.0793, 4698849.3024, 747014.5638, 5163391.4419]
				});
			var position3765 = ol.coordinate.toStringXY(ol.proj.transform(position, 'EPSG:3857', projection3765), 6);
			//var latitude = geolocation.getLatitude();
			//var longitude = geolocation.getLongitude();
			var accuracy = geolocation.getAccuracy();
			var altitude = geolocation.getAltitude();
			// REETURNED IF AVAILABLE
			var alitudeAccuracy = geolocation.getAltitudeAccuracy();
			// REETURNED IF AVAILABLE
			var speed = geolocation.getSpeed();
			// REETURNED IF AVAILABLE
			var heading = geolocation.getHeading();
			// REETURNED IF AVAILABLE
			var projection = geolocation.getProjection();
			map.getView().setCenter(position);
			map.getView().setZoom(18);
			locationCircle.setGeometry(new ol.geom.Circle(position, accuracy));
			locationSource.addFeature(locationCircle);
			map.addLayer(locationLayer);
			$('#geolocation-info').modal('show');
			$('#geolocation-info-results').html('<ul><li>POSITION<ul><li>WGS 84: ' + position4326 + '</li><li>HTRS96 / Croatia TM: ' + position3765 + '</li><li>WGS84 Web Mercator (Auxiliary Sphere): ' + position + '</li></ul></li><li>ACCURACY: ' + accuracy + ' meters</li><li>ALTITUDE: ' + altitude + '</li><li>ALTITUDE ACCURACY: ' + alitudeAccuracy + '</li><li>HEADING: ' + heading + '  radians clockwise from North</li><li>SPEED: ' + speed + ' meters per second</li><li>TIME: ' + projection + '</li></ul>');
		})
		$('body').append(buttonLocate);
	});

	/**
	 * Add a click handler to the map to render the popup.
	 */

	map.on('singleclick', function (evt) {
		getCategory();
		evt.preventDefault();
		var coordinate = evt.coordinate;
		var XY = ol.coordinate.toStringXY(ol.proj.transform(
					coordinate, 'EPSG:3857', 'EPSG:4326'), 6);
		var LAT = XY.substring(XY.indexOf(" ") + 1);
		var LON = XY.substring(0, XY.indexOf(","));
		var HDMS = ol.coordinate.toStringHDMS(ol.proj.transform(
					coordinate, 'EPSG:3857', 'EPSG:4326'), 1);
		var XYCor = XY.replace(',', '');
		var poi_enabled = $('#geoinput-poi').parent().hasClass('toggle btn btn-mini btn-primary');
		if (poi_enabled) {
			//var url = '/geonetwork/apps/search/?west_collapsed=true&s_search=&s_E_geometry=POLYGON((' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + '))';
			var url = gnk + '?resultType=details&sortBy=relevance&from=1&to=20&fast=index&_content_type=json&geometry=POLYGON((' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + '))' + category;
			//console.log("#################################" + url + "#################################");
			map.getView().setCenter(coordinate);
			map.getView().setZoom(15);
			$('#md-info').modal('show');
			//$('.modal-title').append("<p>Results covering location with Latitude:" + LAT +" and Longiture" + LON +"</p>");
			$("#md-info-body").html("<p>Results covering location: " + HDMS + "<iframe id='iframe_poi' src='" + url + "' width='100%' height='600px'></iframe></p>");
			//content.innerHTML = '<p><iframe seamless src="' + url + '" height="280px" width="100%"></iframe></p>';
			//content.innerHTML = '<pre>'. $fn:escapeXml(url) .'</pre>';
			//overlay.setPosition(coordinate);
		} else {
			$('#click-info').modal('show');
			$('#click-info-results').html('<p>You clicked here: ' + LAT + ',' + LON + '</p><p>If you wanna use this location as query enable OVERLAP POI option</p>');
		}

	});

	$('#btn-search').click(function (e) {
		getCategory();
		var bbox_enabled = $('#geoinput-bbox').parent().hasClass('toggle btn btn-mini btn-primary');
		var searchTerm = $("#srch-term").val();
		var geoloc_enabled = $('#geoinput-geoloc').parent().hasClass('toggle btn btn-mini btn-primary');
		if (bbox_enabled && searchTerm === '') {
			var bounds = ol.proj.transformExtent(map.getView().calculateExtent(map.getSize()), 'EPSG:3857', 'EPSG:4326');
			console.log(bounds);
			var url = gnk + '?resultType=details&sortBy=relevance&from=1&to=20&fast=index&_content_type=json&geometry=POLYGON((' + bounds[0] + '%20' + bounds[1] + ',' + bounds[0] + '%20' + bounds[3] + ',' + bounds[2] + '%20' + bounds[1] + ',' + bounds[2] + '%20' + bounds[3] + ',' + bounds[0] + '%20' + bounds[1] + '))' + category;
			console.log(url);
			$('#md-info').modal('show');
			$("#md-info-body").html("<p>Results within the BBOX: " + bounds[0] + ", " + bounds[1] + ", " + bounds[2] + ", " + bounds[3] + "<iframe id='iframe_click' src='" + url + "' width='100%' height='600px' ></iframe></p>");
		}
		if (bbox_enabled && searchTerm != '') {
			var bounds = ol.proj.transformExtent(map.getView().calculateExtent(map.getSize()), 'EPSG:3857', 'EPSG:4326');
			console.log(bounds);
			var url = gnk + '?resultType=details&sortBy=relevance&from=1&to=20&any=' + searchTerm + '&fast=index&_content_type=json&geometry=POLYGON((' + bounds[0] + '%20' + bounds[1] + ',' + bounds[0] + '%20' + bounds[3] + ',' + bounds[2] + '%20' + bounds[1] + ',' + bounds[2] + '%20' + bounds[3] + ',' + bounds[0] + '%20' + bounds[1] + '))' + category;
			console.log(url);
			$('#md-info').modal('show');
			$("#md-info-body").html("<p>Geospatial search applied with bounds: <br>" + bounds[0] + ", " + bounds[1] + ", " + bounds[2] + ", " + bounds[3] + "<br>Search term: " + searchTerm + "<iframe id='iframe_click' onload='setTimeout(addToMap, 5000);'src='" + url + "' width='100%' height='600px' ></iframe></p>");
		}
		if (searchTerm != '' && (!bbox_enabled && !geoloc_enabled)) {
			var url = gnk + '?resultType=details&fast=index&from=1&to=20&sortBy=relevance&any=' + searchTerm + category;
			$('#md-info').modal('show');
			$("#md-info-body").html("<p>Results for: '" + searchTerm + "'<iframe id='iframe_click' onload='setTimeout(addToMap, 5000);' src='" + url + "' width='100%' height='600px' ></iframe></p>");
			//doSearch(searchTerm);
			$("#srch-term").val('');

		}
		if (searchTerm === '' && (!bbox_enabled && !geoloc_enabled)) {
			var url = gnk + '?any=' + category;
			$('#md-info').modal('show');
			$("#md-info-body").html("<p>Entire catalogue search applied<iframe id='iframe_click' src='" + url + "' width='100%' height='600px' ></iframe></p>");
			//doSearch(searchTerm);
			$("#srch-term").val('');
		}

		if (geoloc_enabled && searchTerm === '') {
			var html5geolocation = geo.getCurrentPosition(function (p, e) {
					var longitude = p.coords.longitude;
					console.log(longitude);
				});
			var position = geolocation.getPosition();
			var position4326 = ol.coordinate.toStringXY(ol.proj.transform(position, 'EPSG:3857', 'EPSG:4326'), 6);
			var XYCor = position4326.replace(',', '');
			var xy = position4326.split(", ");
			var x = xy[0];
			var y = xy[1];
			var url = gnk + '?resultType=details&sortBy=relevance&from=1&to=20&fast=index&_content_type=json&geometry=POLYGON((' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + '))' + category;
			map.getView().setCenter(position);
			map.getView().setZoom(15);
			$('#md-info').modal('show');
			//$('.modal-title').append("<p>Results covering location with Latitude:" + LAT +" and Longiture" + LON +"</p>");
			$("#md-info-body").html("<p>Results covering current geographic location:<br><b>Longitude: " + x + "<br>Latitude: " + y + "<br><iframe id='iframe_click' src='" + url + "' width='100%' height='600px' ></iframe></p>");
		}

	});

	$('#srch-term').keypress(function (e) {
		getCategory();
		if (e.which == '13') {
			e.preventDefault();
			var bbox_enabled = $('#geoinput-bbox').parent().hasClass('toggle btn btn-mini btn-primary');
			var searchTerm = $("#srch-term").val();
			var geoloc_enabled = $('#geoinput-geoloc').parent().hasClass('toggle btn btn-mini btn-primary');
			if (bbox_enabled && searchTerm === '') {
				var bounds = ol.proj.transformExtent(map.getView().calculateExtent(map.getSize()), 'EPSG:3857', 'EPSG:4326');
				console.log(bounds);
				var url = gnk + '?resultType=details&sortBy=relevance&from=1&to=20&fast=index&_content_type=json&geometry=POLYGON((' + bounds[0] + '%20' + bounds[1] + ',' + bounds[0] + '%20' + bounds[3] + ',' + bounds[2] + '%20' + bounds[1] + ',' + bounds[2] + '%20' + bounds[3] + ',' + bounds[0] + '%20' + bounds[1] + '))' + category;
				console.log(url);
				$('#md-info').modal('show');
				$("#md-info-body").html("<p>Results within the BBOX: " + bounds[0] + ", " + bounds[1] + ", " + bounds[2] + ", " + bounds[3] + "<iframe id='iframe_enter' src='" + url + "' width='100%' height='600px' ></iframe></p>");
			}
			if (bbox_enabled && searchTerm != '') {
				var bounds = ol.proj.transformExtent(map.getView().calculateExtent(map.getSize()), 'EPSG:3857', 'EPSG:4326');
				console.log(bounds);
				var url = gnk + '?resultType=details&sortBy=relevance&from=1&to=20&any=' + searchTerm + '&fast=index&_content_type=json&geometry=POLYGON((' + bounds[0] + '%20' + bounds[1] + ',' + bounds[0] + '%20' + bounds[3] + ',' + bounds[2] + '%20' + bounds[1] + ',' + bounds[2] + '%20' + bounds[3] + ',' + bounds[0] + '%20' + bounds[1] + '))' + category;
				console.log(url);
				$('#md-info').modal('show');
				$("#md-info-body").html("<p>Geospatial search applied with bounds: <br>" + bounds[0] + ", " + bounds[1] + ", " + bounds[2] + ", " + bounds[3] + "<br>Search term: " + searchTerm + "<iframe id='iframe_enter' onload='setTimeout(addToMap, 5000);' src='" + url + "' width='100%' height='600px' ></iframe></p>");
			}
			if (searchTerm != '' && (!bbox_enabled && !geoloc_enabled)) {
				var url = gnk + '?resultType=details&fast=index&from=1&to=20&sortBy=relevance&any=' + searchTerm + category;
				$('#md-info').modal('show');
				$("#md-info-body").html("<p>Results for: '" + searchTerm + "'<iframe id='iframe_enter' onload='setTimeout(addToMap, 5000);' src='" + url + "' width='100%' height='600px' ></iframe></p>");
				//doSearch(searchTerm);
				$("#srch-term").val('');

			}
			if (searchTerm === '' && (!bbox_enabled && !geoloc_enabled)) {
				var url = gnk + '?any=' + category;
				$('#md-info').modal('show');
				$("#md-info-body").html("<p>Entire catalogue search applied<iframe id='iframe_enter' src='" + url + "' width='100%' height='600px' ></iframe></p>");
				//doSearch(searchTerm);
				$("#srch-term").val('');
			}

			if (geoloc_enabled && searchTerm === '') {
				var html5geolocation = geo.getCurrentPosition(function (p, e) {
						var longitude = p.coords.longitude;
						console.log(longitude);
					});
				var position = geolocation.getPosition();
				var position4326 = ol.coordinate.toStringXY(ol.proj.transform(position, 'EPSG:3857', 'EPSG:4326'), 6);
				var XYCor = position4326.replace(',', '');
				var xy = position4326.split(", ");
				var x = xy[0];
				var y = xy[1];
				var url = gnk + '?resultType=details&sortBy=relevance&from=1&to=20&fast=index&_content_type=json&geometry=POLYGON((' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + ',' + XYCor + '))' + category;
				map.getView().setCenter(position);
				map.getView().setZoom(15);
				$('#md-info').modal('show');
				//$('.modal-title').append("<p>Results covering location with Latitude:" + LAT +" and Longiture" + LON +"</p>");
				$("#md-info-body").html("<p>Results covering current geographic location:<br><b>Longitude: " + x + "<br>Latitude: " + y + "<br><iframe id='iframe_enter' src='" + url + "' width='100%' height='600px' ></iframe></p>");
			}
		}
	});

	function removeMarker() {
		map.getOverlays().getArray().slice(0).forEach(function (overlay) {
			// if markerDB tak remove iny nie
			// ja nevim jak sa to napise ale nejak takto:
			var el = overlay.getElement();
			if ($(el).hasClass('markerGPS')) {
				map.removeOverlay(overlay);
			}
			//hento nevim ci je spravne ale treba class toho elementu do if() treba test
		});
	}

	// FILTER SWITCHER IN SEARCH BOX

	$(document).ready(function (e) {
		$('.search-panel .dropdown-menu').find('a').click(function (e) {
			e.preventDefault();
			var param = $(this).attr("href").replace("#", "");
			var concept = $(this).text();
			$('.search-panel span#search_concept').text(concept);
			$('.input-group #search_param').val(param);
		});
	});

	// AUTOCOMPLETE FROM GEONETWORK
	var suggestions;
	var xhr;
	var data;
	$('input[name="srch-term"]').autoComplete({
		source : function (term, response) {
			try {
				xhr.abort();
			} catch (e) {}
			xhr = $.getJSON('/geonetwork/srv/eng/suggest?field=anylight&sortBy=STARTSWITHFIRST', {
					q : term
				}, function (data) {
					response(data);
					//console.log(data);
					//console.log(data[1].length);
					//console.log(data[1][4]);
					var suggestions = [];
					for (i = 0; i < data[1].length; i++)
						suggestions.push(data[1][i]);
					response(suggestions);
				})
		},
		renderItem : function (item, search) {
			return '<div class="dropdown"><div class="autocomplete-suggestion" style="top:25%" data-selected="' + item + '" data-val="' + search + '">' + item + '</div></div>';
			//$("#srch-term").append('<div class="autocomplete-suggestion" style="top:25%" data-selected="' + item + '" data-val="' + search + '">' + item + '</div>');
		},
		onSelect : function (e, term, item) {
			//console.log('Item "'+item.data('langname')+' ('+item.data('lang')+')" selected by '+(e.type == 'keydown' ? 'pressing enter or tab' : 'mouse click')+'.');
			console.log(item);
			$('input[name="srch-term"]').val(item.data('selected'));
		}

	});

	function displayLocation(position) {
		var latitude = position.coords.latitude;
		var longitude = position.coords.longitude;
		var accuracy = position.coords.accuracy;
		var altitude = position.coords.altitude;
		var altitudeAccuracy = position.coords.altitudeAccuracy;
		var heading = position.coords.heading;
		var speed = position.coords.speed;
		var timestamp = position.coords.timestamp;
		var div = document.getElementById('geolocation');
		//div.innerHTML = "You are at Latitude: " + latitude + ", Longitude: " + longitude;
		if (!latitude || !longitude) {
			alert("Still waiting for position data ...")
		} else {
			div.innerHTML = ("<p>Latitude: " + latitude + "</p>" +
				"<p>Longitude: " + longitude + "</p>" +
				"<p>Accuracy: " + accuracy + "</p>" +
				"<p>Altitude: " + altitude + "</p>" +
				"<p>Altitude accuracy: " + altitudeAccuracy + "</p>" +
				"<p>Heading: " + heading + "</p>" +
				"<p>Speed: " + speed + "</p>" +
				"<p>Timestamp: " + timestamp + "</p>");
			//if(zoomLevel === undefined) { zoomLevel=20; }
			//map.removeOverlay(overlay);
			removeMarker();
			try {
				map.getView().setCenter(ol.proj.transform([longitude, latitude], 'EPSG:4326', 'EPSG:3857'));
			} catch (e) {}
			zoomLevel = 18;

			if (map.getView().getZoom() < zoomLevel) {
				map.getView().setZoom(zoomLevel);
			}

			map.addOverlay(new ol.Overlay({
					//insertFirst: true,
					position : ol.proj.transform(
						[longitude, latitude],
						'EPSG:4326',
						'EPSG:3857'),
					element : $('<img class=markerGPS src="../src/img/locationIcon.png">')
				}));
			var circle = new ol.style.Style({
					image : new ol.style.Circle({
						radius : accuracy,
						fill : null,
						stroke : new ol.style.Stroke({
							color : 'rgba(255,0,0,0.9)',
							width : 3
						})
					})
				});

			var feature = new ol.Feature(
					new ol.geom.Point(ol.proj.transform(
							[longitude, latitude],
							'EPSG:4326',
							'EPSG:3857')));
			feature.setStyle(circle);
			vectorSource.addFeature(feature);
		}
	}

	// ONLY ONE TOGGLE OPTION ON
	$('[id^="geoinput-"]').change(function () {
		if ($(this).parent().hasClass('toggle btn btn-mini btn-primary')) {
			$('[id^="geoinput-"]').not(this).each(function () {
				//$(this).parent().hasClass('toggle btn btn-mini btn-default off');
				$(this).bootstrapToggle('off');
			});
		}
	});

	$('[id^="geoinput-"]').change(function () {
		if ($(this).parent().hasClass('toggle btn btn-mini btn-primary')) {
			$("#spatialQueryType").show();
			var typeEnabled = $(this).parent().prev().text();
			$("#spatialQueryType").find('span').text(typeEnabled + ' Filter Enabled');
		} else {
			$("#spatialQueryType").hide();
		}
	});
	
	function addToMap(layerId){
		var clickedLayer = layerId;
		//alert("SUCCESS: FUNCTION ADD TO MAP WAS LOADED");
		alert("CLICKED ON: " + clickedLayer);
		};



//})(window, document);
