<?php

//error_reporting(E_ALL);
//ini_set('display_errors','1');

//$cookie;
$_POST = json_decode(file_get_contents('php://input'), true);
$serverURL = 'https://demlas.geof.unizg.hr';
$mdWebURI = $_POST['metadataURI'];
$mdServerURI = $_POST['metadataPATH'];
$serverDocFolder = '/var/www/moodle';
$mdServerPath = explode($serverURL,$mdWebURI);
$mdContent = $_POST['sendData'];
$mdContentTemplate = 
'<?xml version="1.0" encoding="UTF-8"?>
<simpledc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dct="http://purl.org/dc/terms/" xmlns:geonet="http://www.fao.org/geonetwork" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://purl.org/dc/elements/1.1/ http://dublincore.org/schemas/xmls/qdc/2006/01/06/simpledc.xsd http://purl.org/dc/terms/ http://dublincore.org/schemas/xmls/qdc/2006/01/06/dcterms.xsd">
	<dc:identifier></dc:identifier>
	<dc:title></dc:title>
	<dct:alternative></dct:alternative>
	<dct:dateSubmitted></dct:dateSubmitted>
	<dc:created></dc:created>
	<dc:identifier></dc:identifier>
	<dc:description></dc:description>
	<dc:creator></dc:creator>
	<dc:subject></dc:subject>
	<dc:type></dc:type>
	<dc:rights></dc:rights>
	<dct:accessRights></dct:accessRights>
	<dc:language>Hrvatski</dc:language>
	<dc:coverage>North 48.79001416537477,South 42.66362868386096,East 29.404034570312522,West 2.685284570312518. (Global)</dc:coverage>
	<dc:format></dc:format>
	<dct:references></dct:references>
	<dc:source></dc:source>
</simpledc>';
$mdFileFullPath = $serverDocFolder . $mdServerPath[1];

if ($mdContent != '' && $mdWebURI){
	file_put_contents ($mdFileFullPath, $mdContent);
	var_dump($mdFileFullPath);
	var_dump($mdContent);
	echo "*** The metadata has been stored in warehouse ***";
}

else if ($mdWebURI && $mdContent === ''){
	file_put_contents ($mdFileFullPath, $mdContentTemplate);
	echo "*** The empty metadata record was created and stored in warehouse ***";
}
else if($mdServerURI && $mdContent != ''){
	file_put_contents ($mdServerURI, $mdContent);
	//echo "STANDALONE METADATA RECORD HAS BEEN SUCESSFULLY CREATED AND STORED IN WAREHOUSE!";
}
else{
	echo "*** Error in metadata creation / upload process ***";
}

?>