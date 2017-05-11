<?php

/*
** PHP SCRIPT DEVELOPED TO GENERATE ISO GMD OR DUBLIN CORE METADATA FROM GDRIVE SHEET
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('date.timezone','Europe/Belgrade');

$config = parse_ini_file("config.ini", true);
$metadataSchema = 'simpledc';
$geoserver = 'https://demlas.geof.unizg.hr/geoserver/';
$geoserverPass = "admin:hFAvuw6km";

//header('Content-Type: text/xml');

// Parsing this spreadsheet: https://spreadsheets.google.com/pub?key=0Ah0xU81penP1dFNLWk5YMW41dkcwa1JNQXk3YUJoOXc&hl=en&output=html
$url = $config['GOOSHEETURL']['json'];
$file= file_get_contents($url);
$json = json_decode($file);
//echo $json;
$rows = $json->{'feed'}->{'entry'};
$jsontext = "[";
$status = "<h1>DEMLAS METADATA CREATION WORKFLOW</h1>";
$status .= "<h3>List of created metadata: </h3>";
$i=0;
// MAIN LOOP START
foreach($rows as $row) {
$i++;
$topCate = $row->{'gsx$topiccategory'}->{'$t'};
$resId = $row->{'gsx$uniqueresourceidentifier'}->{'$t'};
$resIden = str_replace(array( '(' ), '_', str_replace(array(')'),'',$resId));
if($topCate === 'pokrov zemljišta'){
		$workspace = 'demlas_lc';
		$dataDir = '/opt/demlas/lc';
	}
else if($topCate === 'planski katastar'){
		$workspace = 'demlas_cp';
		$dataDir = '/opt/demlas/cp';
	}
else{
	$dataDir = '/opt/demlas';
}

$di = new RecursiveDirectoryIterator($dataDir,RecursiveDirectoryIterator::SKIP_DOTS);
$it = new RecursiveIteratorIterator($di);

foreach($it as $file) {
	$files = basename($file);
	$fileName = substr($files, 0, strpos($files, '.'));
	
	if(($fileName == $resIden || $fileName == $resId) && (pathinfo($file, PATHINFO_EXTENSION) == "tif" || pathinfo($file, PATHINFO_EXTENSION) == "TIF" || pathinfo($file, PATHINFO_EXTENSION) == "dwg" || pathinfo($file, PATHINFO_EXTENSION) == "txt" || pathinfo($file, PATHINFO_EXTENSION) == "pdf")){
		$fileFullPath = dirname($file);
		$fileFullName = basename($file);
		$fileExtension = pathinfo($file, PATHINFO_EXTENSION);
		//echo $fileExtension;
	}
	
}
  $dcXML = '<simpledc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dct="http://purl.org/dc/terms/" xmlns:geonet="http://www.fao.org/geonetwork" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://purl.org/dc/elements/1.1/ http://dublincore.org/schemas/xmls/qdc/2006/01/06/simpledc.xsd  http://purl.org/dc/terms/ http://dublincore.org/schemas/xmls/qdc/2006/01/06/dcterms.xsd">';
  

  /**
	METADATA ON METADATA SECTION
**/
  // METADATA RECORD IDENTIFIER
  $id = explode("/public/values/", $row->{'id'}->{'$t'});
  $uuid = $id[1];
  $dcXML .= '<dc:identifier>http://metadata.demlas.geof.unizg.hr/'. $i .'_'. $uuid . '</dc:identifier>';
 
// C.2.1 Resource title
	$datasetTitle = $row->{'gsx$resourcetitle'}->{'$t'};
	
	
	if (strpos($resIden, 'scan_') !== false) {
		$datasetTitle = "Skenirani list - ". $datasetTitle;
	}
	if (strpos($resIden, 'georef_') !== false) {
		$datasetTitle = "Georeferencirani skenirani list - ". $datasetTitle;
	}
	
	if (strpos($datasetTitle, 'TK') !== false) {
		$datasetTitle = str_replace('TK','topografska karta TK',$datasetTitle);
	}
	if (strpos($datasetTitle, 'VDKP')) {
		$datasetTitle = str_replace('VDKP','Vektorizirani digitalni katastarski plan ',$datasetTitle);
	}
	$alternateTitle = $row->{'gsx$alternativenameofsources'}->{'$t'};
	
	$dcXML .= '<dc:title>'. strtoupper(str_replace('_',' ',$datasetTitle)).'</dc:title>';
   
	$alternateTitle = $row->{'gsx$alternativenameofsources'}->{'$t'};
	if ($alternateTitle){
	$dcXML .= '<dct:alternative>'. $alternateTitle .'</dct:alternative>';
	}
	
// 5 Temporal reference
	$dateRevi = $row->{'gsx$dateoflastrevision'}->{'$t'};
	$datePubl = $row->{'gsx$dateofpublication'}->{'$t'};
	$dateCrea = $row->{'gsx$dateofcreation'}->{'$t'};
// C.2.15 Date of last revision
	if($dateRevi){
		$dcXML .= '<dct:modified>'. $dateRevi .'</dct:modified>';
	}
// C.2.14 Date of publication
	if($datePubl){
		$dcXML .= '<dct:dateSubmitted>'. $datePubl .'</dct:dateSubmitted>';
	}
// C.2.16 Date of creation
	if($dateCrea){
		$dcXML .= '<dc:created>'. $dateCrea .'</dc:created>';
	}
// C.2.5 Unique resource identifier
	if ($resIden){
		$dcXML .= '<dc:identifier>'. $resIden .'</dc:identifier>';
	}
	else {
		$dcXML .= '<dc:identifier>'. $uuid .'</dc:identifier>';
	}
  
// C.2.2 Resource abstract
$resAbst = $row->{'gsx$resourceabstract'}->{'$t'};
$dcXML .= '<dc:description>'. $resAbst .'</dc:description>';

	// C.2.23 Responsible party
	$resContactFull = $row->{'gsx$responsibleparty'}->{'$t'};
	$resContactOrg = explode("\nKontakt: ", $resContactFull);
	$resEmail = explode("\nOsoba: ", $resContactOrg[1]);
	$posName = $row->{'gsx$responsiblepartyrole'}->{'$t'};
	$dcXML .= '<dc:creator>'. $resContactOrg[0]. ';'. $resEmail[0] .'</dc:creator>';
  
	// C.7.1 Maintenance information
	$resMaint = $row->{'gsx$updatefrequency'}->{'$t'};
	$maiNote = $row->{'gsx$updatenote'}->{'$t'};
	
	//C.2.10 Keyword value
	$resKeyw = $row->{'gsx$keywordvalue'}->{'$t'};
	$keyThes = $row->{'gsx$originatingcontrolledvocabulary'}->{'$t'};
	if ($resKeyw){
		$keywords = explode(',', $resKeyw);
		foreach ($keywords as $keyword){
			$dcXML .= '<dc:subject>'. $keyword .'</dc:subject>';
		}
	}
	// INSPIRE KEYWORDS
	if ($topCate === 'planski katastar'){
		$dcXML .= '<dc:subject>Katastarske čestice</dc:subject>';
	}
	if ($topCate === 'pokrov zemljišta'){
		$dcXML .= '<dc:subject>Prekrivenost tla</dc:subject>';
	}
	
	// C.2.21 Conditions applying to access and use
	$accUse = $row->{'gsx$conditionsapplayingtoaccessanduse'}->{'$t'};
	if ($accUse){
	$dcXML.= '<dc:rights>'. $accUse .'</dc:rights>';
	}

	// C.2.22 Limitations on public access
	$pubAccValue = $row->{'gsx$limitationsonpublicaccessaccessconstrains'}->{'$t'};
	$pubAccText = $row->{'gsx$limitationsonpublicaccessotherconstrains'}->{'$t'};
	
	if($pubAccValue){
		$dcXML .= '<dct:accessRights>'. $pubAccValue .'</dct:accessRights>';
		$dcXML .= '<dct:accessRights>'. $pubAccText .'</dct:accessRights>';
	}
	
	// C.2.18 Spatial resolution
	$resScal = $row->{'gsx$spatialresolutionscale'}->{'$t'};
	$resScalExpl = explode(':',$resScal);
	$resDist = $row->{'gsx$spatialresolutiondistance'}->{'$t'};
	if ($resScal){
		
		$dcXML .= '<dct:spatial>'. $resScal .'</dct:spatial>';
		
	}
	// C.2.7 Resource language
	$resLang = $row->{'gsx$resourcelanguage'}->{'$t'};
	$resLanguages = explode(',',$resLang);
	
	$euLanguages = array('Bulgarian' => 'bul',
						'Irish' => 'gle',
						'Croatian' => 'hrv',
						'Italian' => 'ita',
						'Czech' => 'cze',
						'Latvian' => 'lav',
						'Danish' => 'dan',
						'Lithuanian' => 'lit',
						'Dutch' => 'dut',
						'Maltese' => 'mlt',
						'English' => 'eng',
						'Polish' => 'pol',
						'Estonian' => 'est',
						'Portuguese' => 'por',
						'Finnish' => 'fin',
						'Romanian' => 'rum',
						'French' => 'fre',
						'Slovak' => 'slo',
						'German' => 'ger',
						'Slovenian' => 'slv',
						'Greek' => 'gre',
						'Spanish' => 'spa',
						'Hungarian' => 'hun',
						'Swedish' => 'swe');
	foreach($resLanguages as $language){
		foreach($euLanguages as $key => $value)
		{
		  if ($value === $language) {
				$dcXML .= '<dc:language>'. $key .'</dc:language>';
			}
		}
	}
	
	// C.2.12 Geographic bounding box
	if (strpos($resIden, 'scan_') !== false && ($fileExtension == "tif" || $fileExtension == "TIF")) {
		$geoserverURL = $geoserver .'rest/workspaces/'. $workspace .'/coveragestores/'. str_replace('scan_','georef_',$resIden) .'.'.$fileExtension.'/coverages/'. str_replace('scan_','georef_',$resIden) .'.json';
	}
	
	if (strpos($resIden, 'georef_') !== false && ($fileExtension == "tif" || $fileExtension == "TIF")) {
		$geoserverURL = $geoserver .'rest/workspaces/'. $workspace .'/coveragestores/'. $resIden .'.'.$fileExtension.'/coverages/'. $resIden .'.json';
	}
	
	//$geoserverURL = 'http://www.pg.geof.unizg.hr:8080/geoserver/rest/workspaces/'. $workspace .'/coveragestores/'. $resIden .'.tif/coverages/'. $resIden .'.json';
	// EXTRACTING BOUNDS FROM GEOSERVER COVERAGE STORE USING REST API
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$geoserverURL);
	curl_setopt($ch, CURLOPT_USERPWD,  $geoserverPass);
	$result=curl_exec($ch);
	curl_close($ch);
	// Will dump a beauty json :3
	$obj = json_decode($result, true);
	$bbox = $obj['coverage']['latLonBoundingBox'];
	$bboxNative = $obj['coverage']['nativeBoundingBox'];
	if($bboxNative && strpos($resIden, 'georef_') !== false){
		$wmsGetMap130 = $geoserver . $workspace .'/wms?SERVICE=WMS&amp;VERSION=1.3.0&amp;REQUEST=GetMap&amp;FORMAT=image%2Fjpeg&amp;TRANSPARENT=true&amp;tiled=true&amp;LAYERS='. $workspace .'%3A'. $resIden .'&amp;STYLES&amp;WIDTH=256&amp;HEIGHT=256&amp;CRS=EPSG%3A3908&amp;BBOX='.$bboxNative['miny'].'%2C'.$bboxNative['minx'].'%2C'.$bboxNative['maxy'].'%2C'.$bboxNative['maxx'];
	} 
	if($bbox){
		$dcXML .= '<dc:coverage>North ' . $bbox['maxy'] .',South '. $bbox['miny'] .',East '. $bbox['maxx'] .',West '. $bbox['minx'] .'. (Global)</dc:coverage>';
	}

/**
	DISTRIBUTION METADATA SECTION
**/

// 3.2.3.1 Data encoding
$resFormat = $row->{'gsx$dataformat'}->{'$t'};

if(strpos($resFormat, 'tiff') !== false){
	$dcXML .= '<dc:format>'. $resFormat .'</dc:format>';
}
if(strpos($resFormat, 'png') !== false){
	$dcXML .= '<dc:format>'. $resFormat .'</dc:format>';
}
if(strpos($resFormat, 'jp2') !== false){
	$dcXML .= '<dc:format>'. $resFormat .'</dc:format>';
}
if(strpos($resFormat, 'dwg') !== false){
	$dcXML .= '<dc:format>'. $resFormat .'</dc:format>';
}
if(strpos($resFormat, 'pdf') !== false){
	$dcXML .= '<dc:format>'. $resFormat .'</dc:format>';
}
if(strpos($resFormat, 'txt') !== false){
	$dcXML .= '<dc:format>'. $resFormat .'</dc:format>';
}
if(strpos($resFormat, 'xls') !== false){
	$dcXML .= '<dc:format>'. $resFormat .'</dc:format>';
}

// C.4.2 Resource locator
// URL TO HTTP GET DATASET FROM THEMATIC FOLDER
//if (($fileExtension == "tif" )||($fileExtension == "TIF" )||($fileExtension == "dwg" )||($fileExtension == "pdf" ) ){
if($fileExtension){
		$dcXML .= '<dct:references>'. str_replace('/opt/demlas/','https://data.demlas.unizg.hr/',$fileFullPath) .'/'. $fileFullName .'</dct:references>';
}
// URL TO OGC SERVICE ENDPOINT

if (strpos($resIden, 'georef_') !== false && $bbox) {
		$dcXML .= '<dct:references>'. $geoserver . $workspace .'/ows</dct:references>';
		$dcXML .= '<dct:references>'. $wmsGetMap130 .'</dct:references>';
}

/**
	QUALITY METADATA SECTION
**/  
// C.2.19 Specification + C.2.20 Degree



$resSpecFull = $row->{'gsx$conformityspecification'}->{'$t'};
$resSpecExpl = explode("\nDatum: ", $resSpecFull[1]); //$resSpecDate[1]
$speExpl = $row->{'gsx$conformityexplenation'}->{'$t'};
$specPass = $row->{'gsx$conformitydegree'}->{'$t'};


//C.2.17 Lineage
$resLine = $row->{'gsx$lineage'}->{'$t'};

//$dcXML .= '<dct:provenance>'. $resLine  .'</dct:provenance>';
// FOR GEONETWORK
$dcXML .= '<dc:source>'. $resLine  .'</dc:source>';

  
$dcXML .= '</simpledc>';
//SAVING METADATA AS ISO GMD FILE
//$fileName = $dataDir ."/metadata/". time() ."_". $uuid .".xml";

$fileName = $dataDir .'/metadata/metadata_' . $resIden . '.xml';
file_put_contents ($fileName, $dcXML);


$status .= '<p>'. $i .') '. $fileName . ' created.</p>';

// LOOP END
}
$status .= '<h3>FINISHED</h3>';
echo $status;
$jsontextFinal = substr_replace($jsontext,'', -1); 
$jsontextFinal .= "]";
$jsonData = trim(preg_replace('/\s\s+/', ' ', $jsontextFinal));
//echo $jsonData;
// See this here: http://imagine-it.org/google/spreadsheets/showspreadsheet.php
$xml = new SimpleXMLElement($dcXML);
//$content = file_get_contents ($dcXML);

//echo $xml->asXML();
?>