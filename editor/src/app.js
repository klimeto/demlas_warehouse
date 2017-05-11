function generateUUID() {
	var d = new Date().getTime();
	var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
			var r = (d + Math.random() * 16) % 16 | 0;
			d = Math.floor(d / 16);
			return (c == 'x' ? r : (r & 0x3 | 0x8)).toString(16);
		});
	return uuid;
};


var metadataFileName,
	metadataURI;
	
var fileExists = true;
function checkAndCreate (url){
	//var def = $.Deferred();
	$.ajax({
        url:url,
        error: function()
        {
           console.log('file does not exists');
		   //fileExists = false;
		   
		   alert("METADATA FILE DOES NOT EXIST YET, CREATING A NEW ONE ...");
		   var urlPHP = '/warehouse/api/upload.php';
		   $.ajax({
			type: "POST",
			contentType:"application/json; charset=utf-8",
			dataType: "json",
			url: urlPHP,
			data: '{"metadataURI" : "'+url+'", "sendData":""}',
			success: function (data){
					console.log(data);
				},
			error: function (e){
					console.log(e);
				}
			});
			
			fileExists = false;
			//console.log(def);
			//return def;
        },
        success: function()
        {
            console.log('file exists');
			fileExists = true;
			//return def;
        }
    });
	console.log("END OF THE CREATE XML FUNCTION")
	//return def;
}

var localStorageApp = angular.module('localStorageApp', ['LocalStorageModule'])
	.config(function (localStorageServiceProvider) {
		localStorageServiceProvider.setPrefix('demoPrefix');
		// localStorageServiceProvider.setStorageCookieDomain('example.com');
		// localStorageServiceProvider.setStorageType('sessionStorage');
	})
	.controller('localStorageCtrl',
		function ($scope, localStorageService) {
		$scope.metadata.title = localStorageService.get('metadata.title');
		$scope.metadata.abstract = localStorageService.get('metadata.abstract');
		$scope.metadata.theme = localStorageService.get('metadata.theme');
		$scope.metadata.keywords = localStorageService.get('metadata.keywords');
		$scope.metadata.contact = localStorageService.get('metadata.contact');
		$scope.metadata.email = localStorageService.get('metadata.email');
		$scope.metadata.url = localStorageService.get('metadata.url');
		$scope.metadata.lineage = localStorageService.get('metadata.lineage');
		//$scope.metadata.date = localStorageService.get('metadata.date');

		
		$scope.$watch('metadata.title', function (value) {
			localStorageService.set('metadata.title', value);
			$scope.localStorageTitleValue = localStorageService.get('metadata.title');
		});
		
		$scope.$watch('metadata.abstract', function (value) {
			localStorageService.set('metadata.abstract', value);
			$scope.localStorageAbstractValue = localStorageService.get('metadata.abstract');
		});
		$scope.$watch('metadata.theme', function (value) {
			localStorageService.set('metadata.theme', value);
			$scope.localStorageThemeValue = localStorageService.get('metadata.theme');
		});
		$scope.$watch('metadata.keywords', function (value) {
			localStorageService.set('metadata.keywords', value);
			$scope.localStorageKeywordsValue = localStorageService.get('metadata.keywords');
		});
		$scope.$watch('metadata.contact', function (value) {
			localStorageService.set('metadata.contact', value);
			$scope.localStorageContactValue = localStorageService.get('metadata.contact');
		});

		$scope.$watch('metadata.email', function (value) {
			localStorageService.set('metadata.email', value);
			$scope.localStorageEmailValue = localStorageService.get('metadata.email');
		});
		$scope.$watch('metadata.url', function (value) {
			localStorageService.set('metadata.url', value);
			$scope.localStorageUrlValue = localStorageService.get('metadata.url');
		});
		$scope.$watch('metadata.lineage', function (value) {
			localStorageService.set('metadata.lineage', value);
			$scope.localStorageLineageValue = localStorageService.get('metadata.lineage');
		});
		$scope.$watch('metadata.date', function (value) {
			localStorageService.set('metadata.date', value);
			$scope.localStorageDateValue = localStorageService.get('metadata.date');
		});

		$scope.storageType = 'Local storage';

		if (localStorageService.getStorageType().indexOf('session') >= 0) {
			$scope.storageType = 'Session storage';
		}

		if (!localStorageService.isSupported) {
			$scope.storageType = 'Cookie';
		}

		$scope.$watch(function () {
			return localStorageService.get('metadata.title');
		}, function (value) {
			$scope.metadata.title = value;
		});

		$scope.clearAll = localStorageService.clearAll;
	});


var app = angular.module('demoapp', ['openlayers-directive', 'localStorageApp']);
app.config = function (localStorageServiceProvider) {
	localStorageServiceProvider.setPrefix('demoPrefix');
	// localStorageServiceProvider.setStorageCookieDomain('example.com');
	// localStorageServiceProvider.setStorageType('sessionStorage');
};



app.controller('DemoController', ['$scope', '$location', '$log', '$http', '$timeout', '$window', '$filter',
function ($scope, $location, $log, $http, $timeout, $window, $filter) {
			angular.extend($scope, {
				geolocation : {
					offset : 0,
					lat : 45.81097266892925,
					lon : 16.04465957031252,
					zoom : 6,
					//autodiscover: true,
					bounds : [],
					centerUrlHash : true
				},
				metadata : {
					//title: localStorageService.get('localTitle'),
					identifier : generateUUID(),
					date : new Date($filter('date')(Date.now(), 'yyyy-MM-dd')),
					pubdate : new Date($filter('date')(Date.now(), 'yyyy-MM-dd')),
					//language : {name:"Hrvatski",code:"hrv"}
					//language : 'Hrvatski'
					//languages : [{name:"Hrvatski",code:"hrv"},{name:"Hrvatski",code:"hrv"}]
					//languages : ["LANG1","LANG2","LANG3"]

				}
			});
			//$scope.metadata.language = ["Emil", "Tobias", "Linus"];
			$scope.$watch("offset", function (offset) {
				$scope.geolocation.bounds[0] += parseFloat(offset, 10);
				$scope.geolocation.bounds[1] += parseFloat(offset, 10);
				$scope.geolocation.bounds[2] -= parseFloat(offset, 10);
				$scope.geolocation.bounds[3] -= parseFloat(offset, 10);
			});
			$scope.languages = [{
					name : "Bulgarian",
					code : "bul"
				}, {
					name : "Irish",
					code : "gle"
				}, {
					name : "Hrvatski",
					code : "hrv"
				}, {
					name : "Italian",
					code : "ita"
				}, {
					name : "Czech",
					code : "cze"
				}, {
					name : "Latvian",
					code : "lav"
				}, {
					name : "Danish",
					code : "dan"
				}, {
					name : "Lithuanian",
					code : "lit"
				}, {
					name : "Dutch",
					code : "dut"
				}, {
					name : "Maltese",
					code : "mlt"
				}, {
					name : "English",
					code : "eng"
				}, {
					name : "Polish",
					code : "pol"
				}, {
					name : "Estonian",
					code : "est"
				}, {
					name : "Portuguese",
					code : "por"
				}, {
					name : "Finnish",
					code : "fin"
				}, {
					name : "Romanian",
					code : "rum"
				}, {
					name : "French",
					code : "fre"
				}, {
					name : "Slovak",
					code : "slo"
				}, {
					name : "German",
					code : "ger"
				}, {
					name : "Slovenian",
					code : "slv"
				}, {
					name : "Greek",
					code : "gre"
				}, {
					name : "Spanish",
					code : "spa"
				}, {
					name : "Hungarian",
					code : "hun"
				}, {
					name : "Swedish",
					code : "swe"
				}
			];
			$scope.themes = [{
					name : "Cadastral Parcel data",
					code : "demlas_cp"
				}, {
					name : "Elevation coverage data",
					code : "demlas_el_cov"
				}, {
					name : "Elevation TIN data",
					code : "demlas_el_tin"
				}, {
					name : "Elevation vector data",
					code : "demlas_el_vec"
				}, {
					name : "Existing Land Use data",
					code : "demlas_elu"
				}, {
					name : "Land Cover data",
					code : "demlas_lc"
				}, {
					name : "Observation data",
					code : "demlas_om"
				}, {
					name : "Orthoimagery data",
					code : "demlas_oi"
				}
			]
			var promise;
			$scope.$on("centerUrlHash", function (event, centerHash) {
				$location.search({
					c : centerHash
				});
			});
		
			$scope.cancel = function () {
				$window.close();
			};
			
			// FUNCTION TO GENERATE MD XML
			function generateXML(){
				console.log("###### FUNCTION generateXML START");
				var xml2Send = '';
				xml2Send += '<?xml version="1.0" encoding="UTF-8"?><simpledc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dct="http://purl.org/dc/terms/" xmlns:geonet="http://www.fao.org/geonetwork" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://purl.org/dc/elements/1.1/ http://dublincore.org/schemas/xmls/qdc/2006/01/06/simpledc.xsd http://purl.org/dc/terms/ http://dublincore.org/schemas/xmls/qdc/2006/01/06/dcterms.xsd">';
				xml2Send += '<dc:identifier>' + $scope.metadata.identifier + '</dc:identifier>';
				xml2Send += '<dc:title>' + $scope.metadata.title + '</dc:title>';
				if($scope.metadata.alttitle){
					xml2Send += '<dct:alternative>' + $scope.metadata.alttitle + '</dct:alternative>';
				}
				else{
					xml2Send += '<dct:alternative></dct:alternative>';
				}
				
				$scope.metadata.pubdate = $filter('date')($scope.metadata.pubdate, "yyyy-MM-dd");
				if($scope.metadata.pubdate){
					xml2Send += '<dct:dateSubmitted>' + $scope.metadata.pubdate + '</dct:dateSubmitted>';
				}
				else{
					xml2Send += '<dct:dateSubmitted></dct:dateSubmitted>';
				}
				
				$scope.metadata.date = $filter('date')($scope.metadata.date, "yyyy-MM-dd");
				xml2Send += '<dc:created>' + $scope.metadata.date + '</dc:created>';
				if($scope.metadata.dataidentifier){
					xml2Send += '<dc:identifier>' + $scope.metadata.dataidentifier + '</dc:identifier>';
				}
				else{
					xml2Send += '<dc:identifier></dc:identifier>';
				}
				
				xml2Send += '<dc:description>' + $scope.metadata.abstract + '</dc:description>';
				xml2Send += '<dc:creator>' + $scope.metadata.contact + ';' + $scope.metadata.email + '</dc:creator>';
				if ($scope.metadata.keywords) {
					var keywordsString = $scope.metadata.keywords.toString();
					var keywords = keywordsString.split(",");
					$.each(keywords, function (i) {
						xml2Send += '<dc:subject>' + keywords[i] + '</dc:subject>';
					});
				} else {
					xml2Send += '<dc:subject>' + $scope.metadata.keywords + '</dc:subject>';
				}
				xml2Send += '<dc:type>' + $scope.metadata.theme + '</dc:type>';
				if($scope.metadata.accessuse){
					xml2Send += '<dc:rights>' + $scope.metadata.accessuse + '</dc:rights>';
				}
				else{
					xml2Send += '<dc:rights></dc:rights>';
				}
				if($scope.metadata.publicaccess){
					xml2Send += '<dct:accessRights>' + $scope.metadata.publicaccess + '</dct:accessRights>';
				}
				else{
					xml2Send += '<dct:accessRights></dct:accessRights>';
				}
				xml2Send += '<dc:language>' + $scope.metadata.language.name + '</dc:language>';
				xml2Send += '<dc:coverage>North ' + $scope.geolocation.bounds[3] + ',South ' + $scope.geolocation.bounds[1] + ',East ' + $scope.geolocation.bounds[2] + ',West ' + $scope.geolocation.bounds[0] + '. (Global)</dc:coverage>';
				xml2Send += '<dc:format>' + $scope.metadata.format + '</dc:format>';
				if ($scope.metadata.url) {
					var urlsString = $scope.metadata.url.toString();
					var urls = urlsString.split(",");
					$.each(urls, function (i) {
						xml2Send += '<dct:references>' + urls[i] + '</dct:references>';
					});
				} else {
					xml2Send += '<dct:references>' + $scope.metadata.url + '</dct:references>';
				}
				xml2Send += '<dc:source>' + $scope.metadata.lineage + '</dc:source>';
				xml2Send += '</simpledc>';
				console.log("###### FUNCTION generateXML END");
				console.log("###### FUNCTION generateXML RESULT: " + xml2Send);
				return xml2Send;
			}
			var harvTaskId;
			function metadataPATH(){
				console.log("###### FUNCTION metadataPATH START");
				var metadataPATH = '';
				if ($scope.metadata.theme === 'demlas_cp'){
					metadataPATH = '/opt/demlas/cp/metadata/metadata_' + $scope.metadata.identifier + '.xml';
					harvTaskId = 724;
				}
				if ($scope.metadata.theme === 'demlas_oi'){
					metadataPATH = '/opt/demlas/oi/metadata/metadata_' + $scope.metadata.identifier + '.xml';
					harvTaskId = 676;
				}
				if ($scope.metadata.theme === 'demlas_lc'){
					metadataPATH = '/opt/demlas/lc/metadata/metadata_' + $scope.metadata.identifier + '.xml';
					harvTaskId = 819;
				}
				if ($scope.metadata.theme === 'demlas_om'){
					metadataPATH = '/opt/demlas/om/metadata/metadata_' + $scope.metadata.identifier + '.xml';
					harvTaskId = 1025;
				}
				if ($scope.metadata.theme === 'demlas_el_cov'){
					metadataPATH = '/opt/demlas/el-cov/metadata/metadata_' + $scope.metadata.identifier + '.xml';
					harvTaskId = 8729;
				}
				if ($scope.metadata.theme === 'demlas_elu'){
					metadataPATH = '/opt/demlas/elu/metadata/metadata_' + $scope.metadata.identifier + '.xml';
					harvTaskId = 9233;
				}
				if ($scope.metadata.theme === 'demlas_el_tin'){
					metadataPATH = '/opt/demlas/el-tin/metadata/metadata_' + $scope.metadata.identifier + '.xml';
					harvTaskId = 9035;
				}
				if ($scope.metadata.theme === 'demlas_el_vec'){
					metadataPATH = '/opt/demlas/el-vec/metadata/metadata_' + $scope.metadata.identifier + '.xml';
					harvTaskId = 9134;
				}
				
				console.log("###### FUNCTION metadataPATH END");
				console.log("###### FUNCTION metadataPATH RESULT: " + metadataPATH);
				return metadataPATH;
				
				
			}
			// TOTO NEFUNGOVALO TREBA TO CHECKNUT!!!!
			$scope.submitForm = function () {
				if ($scope.form.metadataForm.$valid) {
					console.log('###### METADATA IS VALID: STARTING ACTION SAVE TO SERVER ######');
					// SEND METADATA TO WAREHOUSE
					var url = '/warehouse/api/upload.php';
					$http.post(url, {metadataPATH : metadataPATH(), sendData: generateXML()})
					.success(function (data){
						console.log("####### METADATA CREATION SCRIPT SUCCESS: ", data);
					})
					.error(function(e){
						console.log("####### METADATA CREATION SCRIPT ERROR: ", e);
					});
				} else {
					console.log('metadataform is not in scope');
				}
				};
			// SAVING METADATA XML LOCALLY
			$scope.saveXML = function () {
				$scope.toJSON = '';
				$scope.toJSON = angular.toJson($scope.data);
				
				var blob = new Blob([generateXML()], {
						type : "text/xml;charset=utf-8;"
					});
				var downloadLink = angular.element('<a></a>');
				downloadLink.attr('href', window.URL.createObjectURL(blob));
				downloadLink.attr('download', 'metadata.xml');
				downloadLink[0].click();
			};
			// SAVING METADATA XML IN WAREHOUSE
			$scope.sendXML = function () {
				console.log('###### METADATA IS VALID: STARTING ACTION SAVE TO SERVER ######');
				// SEND METADATA TO WAREHOUSE
				var url = '/warehouse/api/upload.php';
				$http.post(url, {metadataPATH : metadataPATH(), sendData: generateXML()})
				.success(function (data){
					console.log(data);
					//alert(data);
					alert("*** INFO: Metadata record was saved in the warehouse ***");
				})
				.error(function(e){
					console.log("####### METADATA CREATION SCRIPT ERROR: ", e);
				});
			};
			
			$scope.sendHarvest = function () {
				console.log('###### STARTING FUNCTION SEND AND HARVEST ######');
				// SEND METADATA TO WAREHOUSE
				var transform = function(data){
					return $.param(data);
				}
				//$http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
				var url = '/warehouse/api/upload.php';
				var loginURL = 'https://demlas.geof.unizg.hr/geonetwork/j_spring_security_check';
				var harvestingURL;
				var logoutURL = 'https://demlas.geof.unizg.hr/geonetwork/j_spring_security_logout';
				var error = false;
				$http.post(url,{metadataPATH: metadataPATH(),sendData: generateXML()})
				.catch(function(e){
					//alert("*** ERROR: Could not load metadata to warehouse. Contact system admin ***");
					console.log("###### UPLOAD ERROR: ", e);
					error = true;
				})
				.then(function(data){
					harvestingURL = 'https://demlas.geof.unizg.hr/geonetwork/srv/eng/admin.harvester.run?_content_type=json&id=' + harvTaskId;
					console.log("###### LOGIN REQUEST ######");
					return $http.post(loginURL, {username: 'harvester', password: 'Only4Harvesting', redirectUrl: ''},
						{headers: { 'Content-Type': 'application/x-www-form-urlencoded'},
						transformRequest: transform});
					
				})
				.catch(function(e){
					//alert("*** ERROR: Could not login into catalogue. Contact system admin ***");
					console.log("###### LOGIN ERROR: ", e);
					error = true;
				})
				.then(function(data){
					console.log("###### HARVESTING REQUEST ######");
					if (error == false){
					return $http.get(harvestingURL);
					}
					
					
				})
				.catch(function(e){
					//alert("*** ERROR: Could not harvest metadata due previous error. Contact system admin ***");
					console.log("###### HARVESTING ERROR: ", e);
					error = true;
				})
				.then(function(data){
					console.log("###### LOGOUT REQUEST ######");
					if (error == false){
						return $http.get(logoutURL);
					}
				})
				.catch(function(e){
					//alert("*** ERROR: Could not logout due previous error. Contact system admin ***");
					console.log("###### LOGOUT ERROR: ", e);
					return;
				})
				.then(function(data){
					if (error == false){
					alert("*** INFO: Metadata record was saved in the warehouse and published in catalogue ***");
					}
					else {
						alert("*** ERROR: Process failed due previous errors. Contact system admin. ***");
					}
				})
			};
		}
	]);
