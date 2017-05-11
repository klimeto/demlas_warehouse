<?php

/*
** PHP SCRIPT DEVELOPED TO GENERATE ISO GMD OR DUBLIN CORE METADATA FROM GDRIVE SHEET
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('date.timezone','Europe/Belgrade');

//$metadataSchema = 'simpledc';
$metadataSchema = 'iso19139';
$geoserverURL = 'https://demlas.geof.unizg.hr/geoserver/';
$geoserverPass = "admin:hFAvuw6km";

//header('Content-Type: text/xml');

// Parsing this spreadsheet: https://spreadsheets.google.com/pub?key=0Ah0xU81penP1dFNLWk5YMW41dkcwa1JNQXk3YUJoOXc&hl=en&output=html
$url = 'https://spreadsheets.google.com/feeds/list/1o71n-81cCXYl_TB7bKhtIZnWSKXSG5pv-ttmyu677EI/1/public/values?alt=json';
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
  $gmdXML = '<?xml version="1.0" encoding="UTF-8"?><gmd:MD_Metadata xmlns:gmd="http://www.isotc211.org/2005/gmd" xmlns:gmx="http://www.isotc211.org/2005/gmx" xmlns:gco="http://www.isotc211.org/2005/gco" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:gts="http://www.isotc211.org/2005/gts" xmlns:gml="http://www.opengis.net/gml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.isotc211.org/2005/gmd http://inspire.ec.europa.eu/draft-schemas/inspire-md-schemas/apiso-inspire/apiso-inspire.xsd http://www.isotc211.org/2005/srv http://inspire.ec.europa.eu/draft-schemas/inspire-md-schemas/srv/1.0/srv.xsd">';
  
/**
	METADATA ON METADATA SECTION
**/
  // METADATA RECORD IDENTIFIER
  $id = explode("/public/values/", $row->{'id'}->{'$t'});
  $uuid = $id[1];
  $gmdXML .= '<gmd:fileIdentifier><gco:CharacterString>http://metadata.demlas.geof.unizg.hr/'. $i .'_'. $uuid . '</gco:CharacterString></gmd:fileIdentifier>';
  /**
  <gmd:fileIdentifier>
      <gmx:Anchor xlink:href="http://metadata.demlas.geof.unizg.hr/'. $uuid .'">'. $uuid .'</gmx:Anchor>
  </gmd:fileIdentifier>
  **/
  
// C.2.27 Metadata language
  $gmdXML .= '<gmd:language>
				  <gmd:LanguageCode codeList="http://www.loc.gov/standards/iso639-2/" codeListValue="hrv">Hrvatski</gmd:LanguageCode>
			  </gmd:language>
			  <gmd:characterSet>
				  <gmd:MD_CharacterSetCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/gmxCodelists.xml#MD_CharacterSetCode" codeListValue="utf8">utf8</gmd:MD_CharacterSetCode>
			  </gmd:characterSet>
			  <gmd:hierarchyLevel>
				  <gmd:MD_ScopeCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#MD_ScopeCode" codeListValue="dataset">dataset</gmd:MD_ScopeCode>
			  </gmd:hierarchyLevel>';

// C.2.25 Metadata point of contact
  $mdContactFull = $row->{'gsx$metadatapointofcontact'}->{'$t'};
  $mdContactOrg = explode("\nKontakt: ", $mdContactFull);
  $mdEmail = explode("\nOsoba: ", $mdContactOrg[1]);

  $gmdXML .= '<gmd:contact>
				  <gmd:CI_ResponsibleParty>
					 <gmd:organisationName>
						<gco:CharacterString>'.$mdContactOrg[0].'</gco:CharacterString>
					 </gmd:organisationName>
					 <gmd:contactInfo>
						<gmd:CI_Contact>
						   <gmd:address>
							  <gmd:CI_Address>
								 <gmd:electronicMailAddress>
									<gco:CharacterString>'.$mdEmail[0].'</gco:CharacterString>
								 </gmd:electronicMailAddress>
							  </gmd:CI_Address>
						   </gmd:address>
						</gmd:CI_Contact>
					 </gmd:contactInfo>
					 <gmd:role>
						<gmd:CI_RoleCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#CI_RoleCode" codeListValue="pointOfContact"></gmd:CI_RoleCode>
					 </gmd:role>
				  </gmd:CI_ResponsibleParty>
			   </gmd:contact>';
// C.2.26 Metadata date
  $mdDate = $row->{'gsx$metadatadate'}->{'$t'};
  if (!empty($mdDate))
  {
		$gmdXML .=  '<gmd:dateStamp>
					  <gco:Date>'.$mdDate.'</gco:Date>
				   </gmd:dateStamp>';  
  }
  else
  {
		$gmdXML .=  '<gmd:dateStamp>
					  <gco:Date>'. date("Y-m-d") . '</gco:Date>
				   </gmd:dateStamp>';
  }
/**
	IDENTIFICATION METADATA SECTION
**/
	
// C.3.1 Coordinate Reference System
//<gmx:Anchor xlink:href="http://www.opengis.net/def/crs/EPSG/0/3045">EPSG:3045</gmx:Anchor>
$refSystem = str_replace(' ','',($row->{'gsx$coordinatereferencesystem'}->{'$t'}));
	if ($refSystem){
	$gmdXML .= '<gmd:referenceSystemInfo>
				  <gmd:MD_ReferenceSystem>
					 <gmd:referenceSystemIdentifier>
						<gmd:RS_Identifier>
						   <gmd:code>
							   <gco:CharacterString>http://www.opengis.net/def/crs/EPSG/0/3045</gco:CharacterString>
						   </gmd:code>
						</gmd:RS_Identifier>
					 </gmd:referenceSystemIdentifier>
				  </gmd:MD_ReferenceSystem>
			   </gmd:referenceSystemInfo>
			   <gmd:referenceSystemInfo>
				  <gmd:MD_ReferenceSystem>
					 <gmd:referenceSystemIdentifier>
						<gmd:RS_Identifier>
						   <gmd:code>
							  <gco:CharacterString>http://www.opengis.net/def/crs/EPSG/0/3046</gco:CharacterString>
						   </gmd:code>
						</gmd:RS_Identifier>
					 </gmd:referenceSystemIdentifier>
				  </gmd:MD_ReferenceSystem>
			   </gmd:referenceSystemInfo>';
	if (strpos($refSystem, 'EPSG') !== false){
		//<gmx:Anchor xlink:href="http://www.opengis.net/def/crs/EPSG/0/'. substr($refSystem, -4) .'">'. $refSystem. '</gmx:Anchor>
		$gmdXML .= '<gmd:referenceSystemInfo>
					  <gmd:MD_ReferenceSystem>
						 <gmd:referenceSystemIdentifier>
							<gmd:RS_Identifier>
							   <gmd:code>
								  <gco:CharacterString>http://www.opengis.net/def/crs/EPSG/0/'. substr($refSystem, -4) .'</gco:CharacterString>
							   </gmd:code>
							</gmd:RS_Identifier>
						 </gmd:referenceSystemIdentifier>
					  </gmd:MD_ReferenceSystem>
				   </gmd:referenceSystemInfo>';
	}
	else {
		$gmdXML .= '<gmd:referenceSystemInfo>
					  <gmd:MD_ReferenceSystem>
						 <gmd:referenceSystemIdentifier>
							<gmd:RS_Identifier>
							   <gmd:code>
								  <gco:CharacterString>'. $refSystem .'</gco:CharacterString>
							   </gmd:code>
							</gmd:RS_Identifier>
						 </gmd:referenceSystemIdentifier>
					  </gmd:MD_ReferenceSystem>
				   </gmd:referenceSystemInfo>';
	}
}

$gmdXML .= '<gmd:identificationInfo><gmd:MD_DataIdentification><gmd:citation><gmd:CI_Citation>';
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
	
	$gmdXML .= '<gmd:title>
                  <gco:CharacterString>'. strtoupper(str_replace('_',' ',$datasetTitle)).'</gco:CharacterString>
               </gmd:title>';
   
	$alternateTitle = $row->{'gsx$alternativenameofsources'}->{'$t'};
	if ($alternateTitle){
	$gmdXML .= '<gmd:alternateTitle>
                  <gco:CharacterString>'. $alternateTitle .'</gco:CharacterString>
               </gmd:alternateTitle>';
	}
	
// 5 Temporal reference
	$dateRevi = $row->{'gsx$dateoflastrevision'}->{'$t'};
	$datePubl = $row->{'gsx$dateofpublication'}->{'$t'};
	$dateCrea = $row->{'gsx$dateofcreation'}->{'$t'};
// C.2.15 Date of last revision
	if($dateRevi){
		
		$gmdXML .= '<gmd:date>
					  <gmd:CI_Date>
						 <gmd:date>
							<gco:Date>'. $dateRevi .'</gco:Date>
						 </gmd:date>
						 <gmd:dateType>
							<gmd:CI_DateTypeCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/ML_gmxCodelists.xml#CI_DateTypeCode" codeListValue="revision">revizija</gmd:CI_DateTypeCode>
						 </gmd:dateType>
					  </gmd:CI_Date>
				   </gmd:date>';
	}
// C.2.14 Date of publication
	if($datePubl){
		
		$gmdXML .= '<gmd:date>
					  <gmd:CI_Date>
						 <gmd:date>
							<gco:Date>'. $datePubl .'</gco:Date>
						 </gmd:date>
						 <gmd:dateType>
							<gmd:CI_DateTypeCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/ML_gmxCodelists.xml#CI_DateTypeCode" codeListValue="publication">objavljeno</gmd:CI_DateTypeCode>
						 </gmd:dateType>
					  </gmd:CI_Date>
				   </gmd:date>';
	}
// C.2.16 Date of creation
	if($dateCrea){
		
		$gmdXML .= '<gmd:date>
					  <gmd:CI_Date>
						 <gmd:date>
							<gco:Date>'. $dateCrea .'</gco:Date>
						 </gmd:date>
						 <gmd:dateType>
							<gmd:CI_DateTypeCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/ML_gmxCodelists.xml#CI_DateTypeCode" codeListValue="creation">stvoren</gmd:CI_DateTypeCode>
						 </gmd:dateType>
					  </gmd:CI_Date>
				   </gmd:date>';
	}
// C.2.5 Unique resource identifier
	if ($resIden){
		//<gmx:Anchor xlink:href="http://data.demlas.geof.unizg.hr/'. $resIden .'">'. $resIden .'</gmx:Anchor>
		$gmdXML .= '<gmd:identifier>
					  <gmd:MD_Identifier>
						 <gmd:code>
							<gco:CharacterString>http://data.demlas.geof.unizg.hr/'. $resIden .'</gco:CharacterString>
						 </gmd:code>
					  </gmd:MD_Identifier>
				   </gmd:identifier>';
	}
	else {
		//<gmx:Anchor xlink:href="http://metadata.demlas.geof.unizg.hr/'. $uuid .'">'. $uuid .'</gmx:Anchor>
		$gmdXML .= '<gmd:identifier>
					  <gmd:MD_Identifier>
						 <gmd:code>
							<<gco:CharacterString>http://metadata.demlas.geof.unizg.hr/'. $uuid .'</gco:CharacterString>
						 </gmd:code>
					  </gmd:MD_Identifier>
				   </gmd:identifier>';
	}
$gmdXML .= '</gmd:CI_Citation></gmd:citation>';
  
// C.2.2 Resource abstract
$resAbst = $row->{'gsx$resourceabstract'}->{'$t'};
$gmdXML .= '<gmd:abstract><gco:CharacterString>'. $resAbst .'</gco:CharacterString></gmd:abstract>';

// C.2.23 Responsible party

	$resContactFull = $row->{'gsx$responsibleparty'}->{'$t'};
  $resContactOrg = explode("\nKontakt: ", $resContactFull);
  $resEmail = explode("\nOsoba: ", $resContactOrg[1]);
  $posName = $row->{'gsx$responsiblepartyrole'}->{'$t'};

  $gmdXML .= '<gmd:pointOfContact>
				  <gmd:CI_ResponsibleParty>
					 <gmd:organisationName>
						<gco:CharacterString>'.$resContactOrg[0].'</gco:CharacterString>
					 </gmd:organisationName>';
	if($posName){
		$gmdXML .= '<gmd:positionName>
						<gco:CharacterString>'. $posName .'</gco:CharacterString>
					 </gmd:positionName>';
	}
	$gmdXML .= '<gmd:contactInfo>
						<gmd:CI_Contact>
						   <gmd:address>
							  <gmd:CI_Address>
								 <gmd:electronicMailAddress>
									<gco:CharacterString>'.$resEmail[0].'</gco:CharacterString>
								 </gmd:electronicMailAddress>
							  </gmd:CI_Address>
						   </gmd:address>
						</gmd:CI_Contact>
					 </gmd:contactInfo>
					 <gmd:role>
						<gmd:CI_RoleCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#CI_RoleCode" codeListValue="pointOfContact"></gmd:CI_RoleCode>
					 </gmd:role>
				  </gmd:CI_ResponsibleParty>
			   </gmd:pointOfContact>';
// C.7.1 Maintenance information
	$resMaint = $row->{'gsx$updatefrequency'}->{'$t'};
	$maiNote = $row->{'gsx$updatenote'}->{'$t'};
	$gmdXML .= '<gmd:resourceMaintenance>
					<gmd:MD_MaintenanceInformation>
					   <gmd:maintenanceAndUpdateFrequency>
						  <gmd:MD_MaintenanceFrequencyCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/gmxCodelists.xml#MD_MaintenanceFrequencyCode" codeListValue="notPlanned">'. $resMaint .'</gmd:MD_MaintenanceFrequencyCode>
					   </gmd:maintenanceAndUpdateFrequency>';
	if($maiNote){
		
		$gmdXML .= '<gmd:maintenanceNote><gco:CharacterString>'. $maiNote .'</gco:CharacterString></gmd:maintenanceNote>';
		
	}
	$gmdXML .= '</gmd:MD_MaintenanceInformation>
			</gmd:resourceMaintenance>';
				 
//C.2.10 Keyword value

	$resKeyw = $row->{'gsx$keywordvalue'}->{'$t'};
	$keyThes = $row->{'gsx$originatingcontrolledvocabulary'}->{'$t'};
	if ($resKeyw){
		$gmdXML .= '<gmd:descriptiveKeywords>
					<gmd:MD_Keywords>';
		$keywords = explode(',', $resKeyw);
		foreach ($keywords as $keyword){
			$gmdXML .= '<gmd:keyword>
						  <gco:CharacterString>'. $keyword .'</gco:CharacterString>
					   </gmd:keyword>';
		}
		if ($keyThes){
			$gmdXML .= '<gmd:thesaurusName xlink:href="'. $keyThes .'"/>';
		}
		$gmdXML .= '</gmd:MD_Keywords>
				</gmd:descriptiveKeywords>';
	}
	
// INSPIRE KEYWORDS
	
	$gmdXML .= '<gmd:descriptiveKeywords>
				<gmd:MD_Keywords>';
	if ($topCate === 'planski katastar'){
		//<gmx:Anchor xlink:href="http://inspire.ec.europa.eu/theme/cp">Katastarske čestice</gmx:Anchor>
		$gmdXML .= '<gmd:keyword>
					 <gco:CharacterString>Katastarske čestice</gco:CharacterString>
					 </gmd:keyword>';
	}
	if ($topCate === 'pokrov zemljišta'){
		//<gmx:Anchor xlink:href="http://inspire.ec.europa.eu/theme/lc">Prekrivenost tla</gmx:Anchor>
		$gmdXML .= '<gmd:keyword>
					 <gco:CharacterString>Prekrivenost tla</gco:CharacterString>
					 </gmd:keyword>';
	}
	//<gmx:Anchor xlink:href="http://www.eionet.europa.eu/gemet/inspire_themes">GEMET - INSPIRE themes, version 1.0</gmx:Anchor>
	$gmdXML .= '<gmd:thesaurusName>
				 <gmd:CI_Citation>
					 <gmd:title>
					 <gco:CharacterString>GEMET - INSPIRE themes, version 1.0</gco:CharacterString>
					 </gmd:title>
				 <gmd:date>
					 <gmd:CI_Date>
					 <gmd:date>
					 <gco:Date>2008-06-01</gco:Date>
					 </gmd:date>
					 <gmd:dateType>
					 <gmd:CI_DateTypeCode
					codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_DateTypeCode"
					codeListValue="publication" />
					 </gmd:dateType>
					 </gmd:CI_Date>
				 </gmd:date>
				 </gmd:CI_Citation>
				 </gmd:thesaurusName>
				 </gmd:MD_Keywords>
				</gmd:descriptiveKeywords>';
				
// C.2.21 Conditions applying to access and use
	$accUse = $row->{'gsx$conditionsapplayingtoaccessanduse'}->{'$t'};
	if ($accUse){
	$gmdXML.= '<gmd:resourceConstraints>
				<gmd:MD_LegalConstraints>
				   <gmd:accessConstraints>
					  <gmd:MD_RestrictionCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/gmxCodelists.xml#MD_RestrictionCode" codeListValue="otherRestrictions">otherRestrictions</gmd:MD_RestrictionCode>
				   </gmd:accessConstraints>
				   <gmd:useConstraints>
					  <gmd:MD_RestrictionCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/gmxCodelists.xml#MD_RestrictionCode" codeListValue="otherRestrictions">otherRestrictions</gmd:MD_RestrictionCode>
				   </gmd:useConstraints>
				   <gmd:otherConstraints>
					  <gco:CharacterString>'. $accUse .'</gco:CharacterString>
				   </gmd:otherConstraints>
				</gmd:MD_LegalConstraints>
			 </gmd:resourceConstraints>';
	}

// C.2.22 Limitations on public access
	$pubAccValue = $row->{'gsx$limitationsonpublicaccessaccessconstrains'}->{'$t'};
	$pubAccText = $row->{'gsx$limitationsonpublicaccessotherconstrains'}->{'$t'};
	
	if($pubAccValue){
		$gmdXML .= '<gmd:resourceConstraints>
						<gmd:MD_LegalConstraints>
						   <gmd:accessConstraints>
							  <gmd:MD_RestrictionCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/gmxCodelists.xml#MD_RestrictionCode" codeListValue="restricted">'. $pubAccValue .'</gmd:MD_RestrictionCode>
						   </gmd:accessConstraints>
						   <gmd:otherConstraints>
							  <gco:CharacterString>'. $pubAccText .'</gco:CharacterString>
						   </gmd:otherConstraints>
						</gmd:MD_LegalConstraints>
					 </gmd:resourceConstraints>';
	}
	
	// C.2.18 Spatial resolution
	$resScal = $row->{'gsx$spatialresolutionscale'}->{'$t'};
	$resScalExpl = explode(':',$resScal);
	$resDist = $row->{'gsx$spatialresolutiondistance'}->{'$t'};
	if($resScal){
		$gmdXML .= '<gmd:spatialResolution>
						<gmd:MD_Resolution>
						<gmd:equivalentScale>
						  <gmd:MD_RepresentativeFraction>
							 <gmd:denominator>
								<gco:Integer>'. $resScalExpl[1] .'</gco:Integer>
							 </gmd:denominator>
						  </gmd:MD_RepresentativeFraction>
					   </gmd:equivalentScale>
					   </gmd:MD_Resolution>
					 </gmd:spatialResolution>';
	}else if ($resDist){
		$gmdXML .= ' <gmd:spatialResolution>
						<gmd:MD_Resolution>
						   <gmd:distance>
							  <gco:Distance uom="dd">'. $resDist .'</gco:Distance>
						   </gmd:distance>
						</gmd:MD_Resolution>
					 </gmd:spatialResolution>';
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
				
				$gmdXML .= '<gmd:language>
							 <gmd:LanguageCode
							 codeList="http://www.loc.gov/standards/iso639-2/" codeListValue="'. $value .'">'. $key .'</gmd:LanguageCode>
							</gmd:language>';
			}
	  
		}
	}
	
	//C.2.8 Topic category
	$topCategories = explode(',',$topCate);
	foreach($topCategories as $category){
		
		$gmdXML .= '<gmd:topicCategory>
					 <gmd:MD_TopicCategoryCode>'. $category .'</gmd:MD_TopicCategoryCode>
					</gmd:topicCategory>';
	}
	
	// C.2.12 Geographic bounding box
	
	
	if (strpos($resIden, 'scan_') !== false && ($fileExtension == "tif" || $fileExtension == "TIF")) {
		$geoserverURL = $geoserverURL . 'rest/workspaces/'. $workspace .'/coveragestores/'. str_replace('scan_','georef_',$resIden) .'.'.$fileExtension.'/coverages/'. str_replace('scan_','georef_',$resIden) .'.json';
	}
	
	if (strpos($resIden, 'georef_') !== false && ($fileExtension == "tif" || $fileExtension == "TIF")) {
		$geoserverURL = $geoserverURL . 'rest/workspaces/'. $workspace .'/coveragestores/'. $resIden .'.'.$fileExtension.'/coverages/'. $resIden .'.json';
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
		$wmsGetMap130 = $geoserverURL . $workspace .'/wms?SERVICE=WMS&amp;VERSION=1.3.0&amp;REQUEST=GetMap&amp;FORMAT=image%2Fjpeg&amp;TRANSPARENT=true&amp;tiled=true&amp;LAYERS='. $workspace .'%3A'. $resIden .'&amp;STYLES&amp;WIDTH=256&amp;HEIGHT=256&amp;CRS=EPSG%3A3908&amp;BBOX='.$bboxNative['miny'].'%2C'.$bboxNative['minx'].'%2C'.$bboxNative['maxy'].'%2C'.$bboxNative['maxx'];
	} 
	if($bbox){
		$gmdXML .= '<gmd:extent>
						<gmd:EX_Extent>
						   <gmd:geographicElement>
							  <gmd:EX_GeographicBoundingBox>
								 <gmd:westBoundLongitude>
									<gco:Decimal>'. $bbox['minx'] .'</gco:Decimal>
								 </gmd:westBoundLongitude>
								 <gmd:eastBoundLongitude>
									<gco:Decimal>'. $bbox['maxx'] .'</gco:Decimal>
								 </gmd:eastBoundLongitude>
								 <gmd:southBoundLatitude>
									<gco:Decimal>'. $bbox['miny'] .'</gco:Decimal>
								 </gmd:southBoundLatitude>
								 <gmd:northBoundLatitude>
									<gco:Decimal>'. $bbox['maxy'] .'</gco:Decimal>
								 </gmd:northBoundLatitude>
							  </gmd:EX_GeographicBoundingBox>
						   </gmd:geographicElement>
						</gmd:EX_Extent>
					 </gmd:extent>';
}
$gmdXML .= '</gmd:MD_DataIdentification></gmd:identificationInfo>';

/**
	DISTRIBUTION METADATA SECTION
**/
$gmdXML .= '<gmd:distributionInfo><gmd:MD_Distribution>';
// 3.2.3.1 Data encoding
$resFormat = $row->{'gsx$dataformat'}->{'$t'};
//<gmx:Anchor xlink:href="https://www.iana.org/assignments/media-types/image/tiff">'. $resFormat .'</gmx:Anchor>
if(strpos($resFormat, 'tiff') !== false){
	$gmdXML .= '<gmd:distributionFormat>
					 <gmd:MD_Format>
					 <gmd:name>
						<gco:CharacterString>'. $resFormat .'</gco:CharacterString>
					 </gmd:name>
					 <gmd:version gco:nilReason="unknown" />
					 </gmd:MD_Format>
					</gmd:distributionFormat>';
}
//<gmx:Anchor xlink:href="https://www.iana.org/assignments/media-types/image/png'. $resFormat .'</gmx:Anchor>
if(strpos($resFormat, 'png') !== false){
	$gmdXML .= '<gmd:distributionFormat>
					 <gmd:MD_Format>
					 <gmd:name>
						<gco:CharacterString>'. $resFormat .'</gco:CharacterString>
					 </gmd:name>
					 <gmd:version gco:nilReason="unknown" />
					 </gmd:MD_Format>
					</gmd:distributionFormat>';
}
//<gmx:Anchor xlink:href="https://www.iana.org/assignments/media-types/image/jp2'. $resFormat .'</gmx:Anchor>
if(strpos($resFormat, 'jp2') !== false){
	$gmdXML .= '<gmd:distributionFormat>
					 <gmd:MD_Format>
					 <gmd:name>
						<gco:CharacterString>'. $resFormat .'</gco:CharacterString>
					 </gmd:name>
					 <gmd:version gco:nilReason="unknown" />
					 </gmd:MD_Format>
					</gmd:distributionFormat>';
}
//<gmx:Anchor xlink:href="https://www.iana.org/assignments/media-types/image/vnd.dwg">'. $resFormat .'</gmx:Anchor>
if(strpos($resFormat, 'dwg') !== false){
	$gmdXML .= '<gmd:distributionFormat>
					 <gmd:MD_Format>
					 <gmd:name>
						<gco:CharacterString>'. $resFormat .'</gco:CharacterString>
					 </gmd:name>
					 <gmd:version gco:nilReason="unknown" />
					 </gmd:MD_Format>
					</gmd:distributionFormat>';
}
//<gmx:Anchor xlink:href="https://www.iana.org/assignments/media-types/application/pdf">'. $resFormat .'</gmx:Anchor>
if(strpos($resFormat, 'pdf') !== false){
	$gmdXML .= '<gmd:distributionFormat>
					 <gmd:MD_Format>
					 <gmd:name>
						<gco:CharacterString>'. $resFormat .'</gco:CharacterString>
					 </gmd:name>
					 <gmd:version gco:nilReason="unknown" />
					 </gmd:MD_Format>
					</gmd:distributionFormat>';
}
//<gmx:Anchor xlink:href="https://www.iana.org/assignments/media-types/application/txt">'. $resFormat .'</gmx:Anchor>
if(strpos($resFormat, 'txt') !== false){
	$gmdXML .= '<gmd:distributionFormat>
					 <gmd:MD_Format>
					 <gmd:name>
						<gco:CharacterString>'. $resFormat .'</gco:CharacterString>
					 </gmd:name>
					 <gmd:version gco:nilReason="unknown" />
					 </gmd:MD_Format>
					</gmd:distributionFormat>';
}
// <gmx:Anchor xlink:href="https://www.iana.org/assignments/media-types/application/xls">'. $resFormat .'</gmx:Anchor>
if(strpos($resFormat, 'xls') !== false){
	$gmdXML .= '<gmd:distributionFormat>
					 <gmd:MD_Format>
					 <gmd:name>
						<gco:CharacterString>'. $resFormat .'</gco:CharacterString>
					 </gmd:name>
					 <gmd:version gco:nilReason="unknown" />
					 </gmd:MD_Format>
					</gmd:distributionFormat>';
}

// C.4.2 Resource locator

$gmdXML .= '<gmd:transferOptions>
            <gmd:MD_DigitalTransferOptions>';
// URL TO HTTP GET DATASET FROM THEMATIC FOLDER
//if (($fileExtension == "tif" )||($fileExtension == "TIF" )||($fileExtension == "dwg" )||($fileExtension == "pdf" ) ){
if($fileExtension){
		$gmdXML .= '<gmd:onLine>
					 <gmd:CI_OnlineResource>
					 <gmd:linkage>
						<gmd:URL>'. str_replace('/opt/demlas/','https://data.demlas.unizg.hr/',$fileFullPath) .'/'. $fileFullName .'</gmd:URL>
					 </gmd:linkage>
					 <gmd:name>
						<gco:CharacterString>Direct download (Dataset: '. $fileFullName .')</gco:CharacterString>
					 </gmd:name>
					 <gmd:function>
					 <gmd:CI_OnLineFunctionCode codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_OnLineFunctionCode" codeListValue="download" />
					 </gmd:function>
					 </gmd:CI_OnlineResource>
				 </gmd:onLine>';
}
// URL TO OGC SERVICE ENDPOINT

if (strpos($resIden, 'georef_') !== false && $bbox) {
		$gmdXML .= '<gmd:onLine>
						 <gmd:CI_OnlineResource>
						 <gmd:linkage>
							<gmd:URL>' . $geoserverURL . $workspace .'/ows</gmd:URL>
						 </gmd:linkage>
						 <gmd:name>
							<gco:CharacterString>Web Map Service (Layer: '. $resIden .')</gco:CharacterString>
						 </gmd:name>
						 <gmd:function>
						 <gmd:CI_OnLineFunctionCode codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_OnLineFunctionCode" codeListValue="download" />
						 </gmd:function>
						 </gmd:CI_OnlineResource>
					 </gmd:onLine>';
		$gmdXML .= '<gmd:onLine>
						 <gmd:CI_OnlineResource>
						 <gmd:linkage>
							<gmd:URL>'. $wmsGetMap130. '</gmd:URL>
						 </gmd:linkage>
						 <gmd:name>
							<gco:CharacterString>Thumbnail ('. $resIden .'.jpg)</gco:CharacterString>
						 </gmd:name>
						 <gmd:function>
						 <gmd:CI_OnLineFunctionCode codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_OnLineFunctionCode" codeListValue="information" />
						 </gmd:function>
						 </gmd:CI_OnlineResource>
					 </gmd:onLine>';
	
	
}

$gmdXML .= '</gmd:MD_DigitalTransferOptions></gmd:transferOptions></gmd:MD_Distribution></gmd:distributionInfo>';
/**
	QUALITY METADATA SECTION
**/  
// C.2.19 Specification + C.2.20 Degree

$gmdXML .= '<gmd:dataQualityInfo><gmd:DQ_DataQuality>
			 <gmd:scope>
				<gmd:DQ_Scope>
				   <gmd:level>
					  <gmd:MD_ScopeCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#MD_ScopeCode" codeListValue="dataset">dataset</gmd:MD_ScopeCode>
				   </gmd:level>
				</gmd:DQ_Scope>
			 </gmd:scope>';

$resSpecFull = $row->{'gsx$conformityspecification'}->{'$t'};
$resSpecExpl = explode("\nDatum: ", $resSpecFull[1]); //$resSpecDate[1]
$speExpl = $row->{'gsx$conformityexplenation'}->{'$t'};
$specPass = $row->{'gsx$conformitydegree'}->{'$t'};

// INSPIRE DEAFAULT SPECIFICATION
// <gmx:Anchor xlink:href="http://data.europa.eu/eli/reg/2010/1089">COMMISSION REGULATION (EU) No 1089/2010 of 23 November 2010 implementing Directive 2007/2/EC of the European Parliament and of the Council as regards interoperability of spatial data sets and services</gmx:Anchor>
$gmdXML .= '<gmd:report><gmd:DQ_DomainConsistency><gmd:result>
			 <gmd:DQ_ConformanceResult>
				 <gmd:specification xlink:href="http://inspire.ec.europa.eu/id/citation/ir/reg-1089-2010">
					 <gmd:CI_Citation>
						 <gmd:title>
						 <gco:CharacterString>COMMISSION REGULATION (EU) No 1089/2010 of 23 November 2010 implementing Directive 2007/2/EC of the European Parliament and of the Council as regards interoperability of spatial data sets and services</gco:CharacterString>
						 </gmd:title>
						 <gmd:date>
							 <gmd:CI_Date>
								 <gmd:date>
									<gco:Date>2010-12-08</gco:Date>
								 </gmd:date>
								 <gmd:dateType>
									<gmd:CI_DateTypeCode codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_DateTypeCode" codeListValue="publication">publication</gmd:CI_DateTypeCode>
								 </gmd:dateType>
							 </gmd:CI_Date>
						 </gmd:date>
					 </gmd:CI_Citation>
				 </gmd:specification>
				 <gmd:explanation>
					<gco:CharacterString>This data set is conformant with the INSPIRE Implementing Rules for the interoperability of spatial data sets and services</gco:CharacterString>
				 </gmd:explanation>
				 <gmd:pass>
					<gco:Boolean>false</gco:Boolean>
				 </gmd:pass>
			 </gmd:DQ_ConformanceResult>
			</gmd:result></gmd:DQ_DomainConsistency></gmd:report>';

//C.2.17 Lineage
$resLine = $row->{'gsx$lineage'}->{'$t'};
$gmdXML .= '<gmd:lineage>
            <gmd:LI_Lineage>
               <gmd:statement>
                  <gco:CharacterString>'. $resLine  .'</gco:CharacterString>
               </gmd:statement>
            </gmd:LI_Lineage>
         </gmd:lineage>';

$gmdXML .= '</gmd:DQ_DataQuality>
   </gmd:dataQualityInfo>';  
$gmdXML .= '</gmd:MD_Metadata>';
//SAVING METADATA AS ISO GMD FILE
//$fileName = $dataDir ."/metadata/". time() ."_". $uuid .".xml";

$fileName = $dataDir ."/metadata/". $i .'_iso_'. $uuid .".xml";
file_put_contents ($fileName, $gmdXML);


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
$xml = new SimpleXMLElement($gmdXML);
//$content = file_get_contents ($gmdXML);

//echo $xml->asXML();
?>