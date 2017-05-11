var app = angular.module("editorModalFormApp", ['ui.bootstrap', 'openlayers-directive','stBlurredDialog','angularResizable','base64']);

var metadataFileName,
	metadataURI;
	
var fileExists = true;
var rendered = false;
var showMoreMetadataDiv = false;




function generateUUID() {
	var d = new Date().getTime();
	var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
			var r = (d + Math.random() * 16) % 16 | 0;
			d = Math.floor(d / 16);
			return (c == 'x' ? r : (r & 0x3 | 0x8)).toString(16);
		});
	return uuid;
};
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
app.controller("editorModalFormController", ['$scope', '$modal', '$log', '$http', '$window', '$cacheFactory', '$timeout', 

function ($scope, $modal, $log, $http, $window, $cacheFactory, $timeout) {
	console.log ("editorModalFormController controller is LOADED!");
			$scope.openModal = function (mdFile,fileName) {
				
				metadataFileName = fileName;
				metadataURI = mdFile;
				$scope.message = "Show Form Button Clicked";
				console.log($scope.message);
				console.log(mdFile);
				checkAndCreate(mdFile);
				$timeout(function(){
					console.log("LOADING STARTED!!!");
					$http.get(mdFile).success(function (data) {
					console.log("SUCCESS!!!");
					var x2js = new X2JS();
					var jsonData = x2js.xml_str2json(data);
					console.log(jsonData);
					var dcIdentifiers = jsonData.simpledc.identifier;
					if(dcIdentifiers[0].__text){
						var metadataIdentifier = dcIdentifiers[0];
					}
					else{
						var metadataIdentifier = generateUUID();
					}
					if(dcIdentifiers[1].__text){
						var dataIdentifier = dcIdentifiers[1];
					}
					else{
						var dataIdentifier = '';
					}
					var dcAlternative = jsonData.simpledc.alternative;
					if (dcAlternative && dcAlternative.__text){
						dcAlternative = dcAlternative;
					}
					else{
						dcAlternative = '';
					}
					var dcSubmitted = new Date(jsonData.simpledc.dateSubmitted);
					var dcTitle = jsonData.simpledc.title;
					var dcAbstract = jsonData.simpledc.description;
					var dcSubject = jsonData.simpledc.subject;
					var dcRights = jsonData.simpledc.rights;
					if (dcRights.__text){
						dcRights = dcRights;
					}
					else{
						dcRights = '';
					}
					var dcAccess = jsonData.simpledc.accessRights;
					if (dcAccess.length === 2){
						dcAccess = dcAccess[0]+ ','+ dcAccess[1];
					}
					if (dcAccess && dcAccess.__text){
						dcAccess = dcAccess.__text;
					}
					else{
						dcAccess = '';
					}
					var dcLanguage = jsonData.simpledc.language;
					var dcFormat = jsonData.simpledc.format;
					var dcSource = jsonData.simpledc.references;
					if (dcAbstract.__text){
						dcAbstract = dcAbstract;
					}
					else{
						dcAbstract = '';
					}
					var dcContact = jsonData.simpledc.creator;
					if (dcContact.__text){
						var contactSplit = dcContact.__text.split(",");
						var contactName = contactSplit[0];
						var contactEmail = contactSplit[1];
					}
					else{
						var contactName = '';
						var contactEmail = '';
					}
					
					
					var dcRelation = jsonData.simpledc.relation;
					var dctProvenance = jsonData.simpledc.source;
					if (dctProvenance && dctProvenance.__text){
						dctProvenance = dctProvenance;
					}
					else{
						dctProvenance = '';
					}
					
					
					
					var dcCreated = new Date(jsonData.simpledc.created);
					var dcCoverage = jsonData.simpledc.coverage;
					if (dcCoverage && dcCoverage.__text){
						var sliced = dcCoverage.__text.slice(0,-10);
						console.log(sliced);
						var bounds = sliced.split(",");
						var xmlMaxY = Number(bounds[0].slice(5));
						console.log(xmlMaxY);
						var xmlMinY = Number(bounds[1].slice(5));
						console.log(xmlMinY);
						var xmlMaxX = Number(bounds[2].slice(4));
						console.log(xmlMaxX);
						var xmlMinX = Number(bounds[3].slice(5));
						console.log(xmlMinX);
						var centerX = (xmlMinX + xmlMaxX) / 2;
						var centerY = (xmlMinY + xmlMaxY) / 2;
					}
					else {
							var centerX = 15.983333;
							var centerY = 45.816667;
						}
					
					angular.extend($scope, {
						metadata : {
							identifier : metadataIdentifier,
							dataidentifier : dataIdentifier,
							title : dcTitle,
							alttitle: dcAlternative,
							pubdate: dcSubmitted,
							abstract : dcAbstract,
							contact : contactName,
							email : contactEmail,
							date : dcCreated,
							url : dcRelation,
							lineage : dctProvenance,
							keywords : dcSubject,
							url : dcSource,
							accessuse : dcRights,
							publicaccess : dcAccess,
							language : dcLanguage,
							format : dcFormat
						},
						geolocation : {
							lat : centerY,
							lon : centerX,
							zoom : 10,
							bounds : []
						},
						
					})
					
					var modalInstance = $modal.open({
						templateUrl : '/warehouse/manager/src/templates/modal-form.html',
						backdrop : true,
						controller : ModalInstanceCtrl,
						scope : $scope,
						resolve : {
							metadataForm : function () {
								return $scope.metadataForm;
								
							}
						}
					});
					
					modalInstance.result.then(function (selectedItem) {
						
						$scope.selected = selectedItem;
					}, function () {
							$log.info('Modal dismissed at: ' + new Date());
						});
					console.log ("LOADING FINISHED!!!");
					
					
				})
				
				
				.error(function(e){
					//alert("Something happenned while loading metadata from server: " + e);
					console.log("ERROR!!!! " +e);
					
				})
				},3000);
				

			};

			angular.extend($scope, {
				offset : 0,
				geolocation : {
					lat : 45.816667,
					lon : 15.983333,
					zoom : 6,
					bounds : []
				}
			});

			$scope.$watch("offset", function (offset) {
				$scope.geolocation.bounds[0] += parseFloat(offset, 10);
				$scope.geolocation.bounds[1] += parseFloat(offset, 10);
				$scope.geolocation.bounds[2] -= parseFloat(offset, 10);
				$scope.geolocation.bounds[3] -= parseFloat(offset, 10);
			});
			

		}
		//$templateCache.removeAll();
		
	]);

var ModalInstanceCtrl = function ($scope, $modalInstance, $http, metadataForm, $cacheFactory, $filter, $timeout,$base64) {
	console.log ("ModalInstanceCtrl controller is LOADED!");
	$scope.form = {};
	//$scope.dialogData = stBlurredDialog.getDialogData();
	function generateXML(){
		var xml2Send = '';
			xml2Send += '<?xml version="1.0" encoding="UTF-8"?><simpledc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dct="http://purl.org/dc/terms/" xmlns:geonet="http://www.fao.org/geonetwork" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://purl.org/dc/elements/1.1/ http://dublincore.org/schemas/xmls/qdc/2006/01/06/simpledc.xsd http://purl.org/dc/terms/ http://dublincore.org/schemas/xmls/qdc/2006/01/06/dcterms.xsd">';
			xml2Send += '<dc:identifier>'+$scope.metadata.identifier+'</dc:identifier>';
			xml2Send += '<dc:title>'+$scope.metadata.title+'</dc:title>';
			xml2Send += '<dct:alternative>'+$scope.metadata.alttitle+'</dct:alternative>';
			$scope.metadata.pubdate = $filter('date')($scope.metadata.pubdate, "yyyy-MM-dd");
			xml2Send += '<dct:dateSubmitted>'+$scope.metadata.pubdate+'</dct:dateSubmitted>';
			$scope.metadata.date = $filter('date')($scope.metadata.date, "yyyy-MM-dd");
			xml2Send += '<dct:created>'+$scope.metadata.date+'</dct:created>';
			xml2Send += '<dc:identifier>'+$scope.metadata.dataidentifier+'</dc:identifier>';
			xml2Send += '<dc:description>'+$scope.metadata.abstract+'</dc:description>';
			xml2Send += '<dc:creator>'+$scope.metadata.contact+','+$scope.metadata.email+'</dc:creator>';
			var keywordsString = $scope.metadata.keywords.toString();
			if (keywordsString.indexOf('Prekrivenost tla') || keywordsString.indexOf('Land cover')){
				
			};
			var keywords = keywordsString.split(",");
			$.each(keywords,function(i){
			   xml2Send += '<dc:subject>'+keywords[i]+'</dc:subject>';
			});
			xml2Send += '<dc:rights>'+$scope.metadata.accessuse+'</dc:rights>';
			xml2Send += '<dct:accessRights>'+$scope.metadata.publicaccess+'</dct:accessRights>';
			xml2Send += '<dc:language>'+$scope.metadata.language.name+'</dc:language>';
			xml2Send += '<dc:coverage>North ' + $scope.geolocation.bounds[3] + ',South ' + $scope.geolocation.bounds[1] + ',East ' + $scope.geolocation.bounds[2] + ',West ' + $scope.geolocation.bounds[0] + '. (Global)</dc:coverage>';
			xml2Send += '<dc:format>'+$scope.metadata.format+'</dc:format>';
			var urlsString = $scope.metadata.url.toString();
			var urls = urlsString.split(",");
			$.each(urls,function(i){
			   xml2Send += '<dct:references>'+urls[i].replace('&',';amp&')+'</dct:references>';
			});
			xml2Send +='<dc:source>'+$scope.metadata.lineage+'</dc:source>';
			xml2Send += '</simpledc>';
			//console.log('LANGUAGE:', $scope.metadata.language);
			console.log(xml2Send);
			return xml2Send;
			
	}
	$scope.submitForm = function () {
		if ($scope.form.metadataForm.$valid) {
			console.log('user form is in scope');
			// SEND METADATA TO WAREHOUSE
			var auth = $base64.encode("apiadmin:Only4Apiadmin"), 
			headers = {"Authorization": "Basic " + auth};
			//,{headers: headers}
			var url = '/warehouse/api/upload.php';
			$http.post(url, {metadataURI : metadataURI, sendData: generateXML()},{headers: headers})
			.success(function (data){
				//alert(data);
				alert("*** INFO: Metadata record was saved in the warehouse ***");
				$modalInstance.close('closed');
			})
			.error(function(e){
				//alert(e);
				alert("*** ERROR: Process failed due previous errors. Contact system admin. ***");
			});
		} else {
			console.log('metadataform is not in scope');
		}
	};

	$scope.cancel = function () {
		$modalInstance.dismiss('cancel');
	};
	
	
	
	$scope.title = metadataFileName;
	$cacheFactory.get('$http').removeAll();
	//$templateCache.removeAll();
	$modalInstance.opened.then(
		function() {
			rendered = true;
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

};
app.controller("loaderGIF", ['$scope', function ($scope) {
	console.log ("loaderGIF controller is LOADED!");
}
]);

app.controller("publishToCsw", ['$scope', function ($scope) {
	console.log ("publishToCsw controller is LOADED!");
	$scope.publish = function () {
		alert("PUBLISH BUTTON PRESSED!");
	};
}
]);
