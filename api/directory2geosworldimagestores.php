<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DEFINE PATH TO PROJECTION FILES
$prj3906 = '/opt/demlas/prj/EPSG3906.prj';
$prj3907 = '/opt/demlas/prj/EPSG3907.prj';
$prj3908 = '/opt/demlas/prj/EPSG3908.prj';

// OPEN LOG FILE
$logfh = fopen("../log/directory2geosworldimagestores.log", 'w') or die("can't open log file");

// OPTIONAL SETTINGS FOR DEBUGGING 
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //option to return string
//curl_setopt($ch, CURLOPT_VERBOSE, true);
//curl_setopt($ch, CURLOPT_STDERR, $logfh); // logs curl messages

// DEFINE ROOT FOLDER OF TIF DATA TO CREATE PROJECTION FILES

if ($_GET['rootWS']){
	$rootWS = $_GET['rootWS'];
}
else{
	$rootWS = 'demlas_cp';
}
if($_GET['rootDir']){
	$rootDir =$_GET['rootDir'];
}
else{
	$rootDir = '/opt/demlas/cp/georef/';
}

$di = new RecursiveDirectoryIterator($rootDir,RecursiveDirectoryIterator::SKIP_DOTS);
$it = new RecursiveIteratorIterator($di);
$a = 1;

// LOOP FOLDER FOR TIF FILES AND CREATE PRJ FOR EACH
echo "<h1>DEMLAS DATA PUBLISHING FLOW";
echo "<h2>LIST OF PUBLISHED FILES IN ROOT DIR: ". $rootDir ."</h2>";
foreach($it as $file) {
	$fileNew = str_replace(array( '(' ), '_', str_replace(array(')'),'',$file));
	rename($file, $fileNew);
    if ((pathinfo($file, PATHINFO_EXTENSION) == "tif" ) || (pathinfo($file, PATHINFO_EXTENSION) == "TIF" )){
        //echo $file. "<br>", PHP_EOL;
		
		// TIF FILE NAME
		$TIFs = basename($file);
		//echo $TIFs."<br>";
		
		// TIF FILE WITHOUT EXTENSION
		$fileName = substr($TIFs, 0, strpos($TIFs, '.'));
		//echo $fileName;
		if (!file_exists($rootDir . $fileName. ".prj")){
			$epsg = substr($fileName, -4);
			//echo $epsg;
			// CREATING THE PRJ FILE FOR EACH TIF FILE WITH THE SAME NAME
			$newPrjFile = dirname($file). '/' . $fileName .'.prj';
			$prj = '/opt/demlas/prj/EPSG'.$epsg.'.prj';
			if (!copy($prj, $newPrjFile)) {
				echo "failed to copy";
			}	
		}
		
		//CREATE WORKSPACE FOR EACH TIFF FILE
		// INITIATE CURL SESSION
		$service = "https://demlas.geof.unizg.hr/geoserver";
		$reqWor = "/rest/workspaces/"; // to add a new workspace
		$reqCov = "/rest/workspaces/".$rootWS."/coveragestores/". $TIFs ."/external.worldimage";
		$urlCov = $service . $reqCov;
		$ch = curl_init($urlCov);
		//echo $ch;
		
		// REQUIRED POST REQUEST SETTINGS
		//curl_setopt($ch, CURLOPT_POST, True);
		$passwordStr = "admin:hFAvuw6km"; // replace with your username:password
		//curl_setopt($ch, CURLOPT_USERPWD, $passwordStr);
		
		//PUT TO CREATE COVERAGES AND PUBLISH LAYERS FOR TIF FILES
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_USERPWD,  $passwordStr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
		array("Content-type: text/plain"));
		$textStr = 	"file:".$file;
		//echo $textStr;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $textStr);
		
		//POST return code
		$successCode = 201;
		
		$buffer = curl_exec($ch); // Execute the curl request
		
		// Check for errors and process results
		$info = curl_getinfo($ch);
		if ($info['http_code'] != $successCode) {
		$msgStr = "# Unsuccessful cURL request to ";
		$msgStr .= $urlCov." [". $info['http_code']. "]\n";
		fwrite($logfh, $msgStr);
		} else {
		$msgStr = "# Successful cURL request to ".$urlCov."\n";
		fwrite($logfh, $msgStr);
		}
		fwrite($logfh, $buffer."\n");
		
		echo "<br>" . $a++.": " . $file . "<br>", PHP_EOL;
	}
	/*
	else {
	echo "*** ERROR: There are no suppported data files in ROOT FOLDER.";
	return;
	}
	*/
	
	
	
}

curl_close($ch); // free resources if curl handle will not be reused
fclose($logfh);  // close logfile


// LIST OF STORES IN ROOT WORKSPACE

echo '<h2>LIST OF DATA STORES IN GEOSERVER WORKSPACE NAME: '.$rootWS.'</h2>';
echo '<div><iframe seamless src="' . $service . $reqWor . $rootWS . '"/></div>';

// RUN GEONETWORK HARVESTING TASK
/*
$url = "http://31.147.204.167:8080/geonetwork/srv/eng/xml.harvesting.run";
$ch = curl_init($url);
$passwordStr = "admin:admin";
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_USERPWD,  $passwordStr);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: text/xml"));
$textStr = 	"<request><id>959</id></request>";
curl_setopt($ch, CURLOPT_POSTFIELDS, $textStr);
//POST return code
$successCode = 201;
		
$buffer = curl_exec($ch); // Execute the curl request
curl_close($ch);

// CHECK IF TASK IS RUNNING

echo "NAME OF THE RUNNING HARVESTING TASK: ";
echo "ENDPOINT ON WHICH THE TASK RUNS: ";
echo "LAST RUN: "
*/
?>