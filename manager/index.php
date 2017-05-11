<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
//require ("config.ini");
class DirectoryListing {
	
	private $__config;

	// The top level directory where this script is located, or alternatively one of it's sub-directories
	public $startDirectory = 'data';

	// An optional title to show in the address bar and at the top of your page (set to null to leave blank)
	public $pageTitle = 'Demlas Data Manager App';

	// The URL of this script. Optionally set if your server is unable to detect the paths of files
	public $includeUrl = true;

	// If you've enabled the includeUrl parameter above, enter the full url to the directory the index.php file
	// is located in here, followed by a forward slash.
	public $directoryUrl = 'https://demlas.geof.unizg.hr/warehouse/manager/';

	// Set to true to list all sub-directories and allow them to be browsed
	public $showSubDirectories = true;

	// Set to true to open all file links in a new browser tab
	public $openLinksInNewTab = true;

	// Set to true to show thumbnail previews of any images
	public $showThumbnails = true;

	// Set to true to allow new directories to be created.
	public $enableDirectoryCreation = true;

	// Set to true to allow file uploads (NOTE: you should set a password if you enable this!)
	public $enableUploads = true;

	// Enable multi-file uploads (NOTE: This makes use of javascript libraries hosted by Google so an internet connection is required.)
	public $enableMultiFileUploads = false;

	// Set to true to overwrite files on the server if they have the same name as a file being uploaded
	public $overwriteOnUpload = false;

	// Set to true to enable file deletion options
	public $enableFileDeletion = true;

	// Set to true to enable directory deletion options (only available when the directory is empty)
	public $enableDirectoryDeletion = false;

	// List of all mime types that can be uploaded. Full list of mime types: http://www.iana.org/assignments/media-types/media-types.xhtml
	public $allowedUploadMimeTypes = array(
		'image/jpeg',
		'image/gif',
		'image/tiff',
		'image/png',
		'image/bmp',
		'audio/mpeg',
		'audio/mp3',
		'audio/mp4',
		'audio/x-aac',
		'audio/x-aiff',
		'audio/x-ms-wma',
		'audio/midi',
		'audio/ogg',
		'video/ogg',
		'video/webm',
		'video/quicktime',
		'video/x-msvideo',
		'video/x-flv',
		'video/h261',
		'video/h263',
		'video/h264',
		'video/jpeg',
		'text/plain',
		'text/html',
		'text/xml',
		'text/css',
		'text/csv',
		'text/calendar',
		'application/pdf',
		'application/x-pdf',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // MS Word (modern)
		'application/msword',
		'application/vnd.ms-excel',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // MS Excel (modern)
		'application/zip',
		'application/x-zip-compressed',
		'application/x-tar',
		'application/acad',
		'application/x-acad',
		'application/autocad_dwg',
		'image/x-dwg',
		'application/dwg',
		'application/x-dwg',
		'application/x-autocad',
		'image/vnd.dwg',
		'drawing/dwg',
		'application/octet-stream'
	);

	// Set to true to unzip any zip files that are uploaded (note - will overwrite files of the same name!)
	public $enableUnzipping = true;

	// If you've enabled unzipping, you can optionally delete the original zip file after its uploaded by setting this to true.
	public $deleteZipAfterUploading = false;

	// The Evoluted Directory Listing Script uses Bootstrap. By setting this value to true, a nicer theme will be loaded remotely.
	// Setting this to false will make the directory listing script use the default bootstrap style, loaded locally.
	public $enableTheme = true;

	// Set to true to require a password be entered before being able to use the script
	public $passwordProtect = true;

	// The password to require to use this script (only used if $passwordProtect is set to true)
	public $password = 'password';
	
	// Optional. Allow restricted access only to whitelisted IP addresses
	public $enableIpWhitelist = false;

	// List of IP's to allow access to the script (only used if $enableIpWhitelist is true)
	public $ipWhitelist = array(
		'127.0.0.1'
	);

	// File extensions to block from showing in the directory listing
	public $ignoredFileExtensions = array(
		'php',
		'ini',
	);

	// File names to block from showing in the directory listing
	public $ignoredFileNames = array(
		'.htaccess',
		'.DS_Store',
		'Thumbs.db',
	);

	// Directories to block from showing in the directory listing
	public $ignoredDirectories = array(
	'src',
	);

	// Files that begin with a dot are usually hidden files. Set this to false if you wish to show these hiden files.
	public $ignoreDotFiles = true;

	// Works the same way as $ignoreDotFiles but with directories.
	public $ignoreDotDirectories = true;
	
	/***
		KLIMETO.COM
	***/
	// Metadata creation
	public $enableMetadataCreation = true;
	
	// Publishing to geoserver
	public $enableGeoserver = true;
	public $geoserverSupport = array(
		'tif',
		'TIF'
	);
	public $editableDir = array(
		'om',
		'lc',
		'oi',
		'om',
		'el-vec'
	);

	public function strposa($haystack, $needles=array(), $offset=0) {
			$chr = array();
			foreach($needles as $needle) {
					$res = strpos($haystack, $needle, $offset);
					if ($res !== false) $chr[$needle] = $res;
			}
			if(empty($chr)) return false;
			return min($chr);
	}
	
	
	/*
	====================================================================================================
	You shouldn't need to edit anything below this line unless you wish to add functionality to the
	script. You should only edit this area if you know what you are doing!
	====================================================================================================
	*/
	private $__previewMimeTypes = array(
		'image/gif',
		'image/jpeg',
		'image/png',
		'image/bmp',
		'image/tiff'
	);

	private $__currentDirectory = null;

	private $__fileList = array();

	private $__directoryList = array();

	private $__debug = true;
	

	public $sortBy = 'name';
	
	public $loggedUser = '';
	
	public $publishedLayer = null;

	public $sortableFields = array(
		'name',
		'size',
		'modified'
	);
	
	public function __construct() {
		define('DS', '/');
	}

	private $__sortOrder = 'asc';
	
	public function run() {
		if ($this->enableIpWhitelist) {
			$this->__ipWhitelistCheck();
		}

		$this->__currentDirectory = $this->startDirectory;
		

		// Sorting
		if (isset($_GET['order']) && in_array($_GET['order'], $this->sortableFields)) {
			$this->sortBy = $_GET['order'];
		}

		if (isset($_GET['sort']) && ($_GET['sort'] == 'asc' || $_GET['sort'] == 'desc')) {
			$this->__sortOrder = $_GET['sort'];
		}

		if (isset($_GET['dir'])) {
			if (isset($_GET['delete']) && $this->enableDirectoryDeletion) {
				$this->deleteDirectory();
			}

			$this->__currentDirectory = $_GET['dir'];
			return $this->__display();
		} elseif (isset($_GET['preview'])) {
			$this->__generatePreview($_GET['preview']);
		} else {
			return $this->__display();
		}
	}
	
	/***
		KLIMETO.COM
	***/
	public function login() {
		$password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
		$user = filter_var($_POST['user'], FILTER_SANITIZE_STRING);

		//if ($password === $this->password) {
		$config = parse_ini_file("config.ini", true);
		$users = $config['USERS']['name'];
		$passwords = $config['USERS']['password'];
		if (in_array($user, $users)){
			$key = array_search($user, $users);
			if ($passwords[$key]===$password){
				$_SESSION['evdir_loggedin'] = true;
				//$this->loggedUser = $user;
				$_SESSION['loggedUser'] = $user;
				unset($_SESSION['evdir_loginfail']);
			} else {
				$_SESSION['evdir_loginfail'] = true;
				unset($_SESSION['evdir_loggedin']);

			}
		}
		
		else {
				$_SESSION['evdir_loginfail'] = true;
				unset($_SESSION['evdir_loggedin']);

			}
		}
	/***
		KLIMETO.COM
	***/
	public function logout(){
		unset($_SESSION['evdir_loggedin']);
		header("Refresh:0; url=https://demlas.geof.unizg.hr/warehouse/manager/");
	}
	
	/***
		KLIMETO.COM
	***/
	public function geoserverCheck($fileName){
		$layerName = explode('.', $fileName);
		$passwordStr = "admin:hFAvuw6km";
		$context = stream_context_create(array (
			'http' => array ( 'header' => 'Authorization: Basic ' . base64_encode("$passwordStr")
			)
		));
		$layersJson = file_get_contents('https://demlas.geof.unizg.hr/geoserver/rest/layers.json', false, $context);
		$layersData = json_decode($layersJson);
		//print_r ($layersData->layers->layer[0]->name);
		$publishedLayerNames = $layersData->layers->layer;
		foreach ($publishedLayerNames as $layer){
			if($layerName[0] == $layer->name) {
				$item = $layer;
				break;
			}
		}
		if(isset($item)){
			return ('<span class="label label-success">Published</span>');
			$this->publishedLayer = true;
		}
		else{
			return ('<span class="label label-success">Unpublished</span>');
		}
	}
	
	public function upload() {
		$files = $this->__formatUploadArray($_FILES['upload']);

		if ($this->enableUploads) {
			if ($this->enableMultiFileUploads) {
				foreach ($files as $file) {
					$status = $this->__processUpload($file);
				}
			} else {
				$file = $files[0];
				print_r($file);
				$status = $this->__processUpload($file);
				//$statusMeta = $this->__processMetadata($file);
				
			}

			return $status;
		}
		return false;
	}

	private function __formatUploadArray($files) {
		$fileAry = array();
		$fileCount = count($files['name']);
		$fileKeys = array_keys($files);

		for ($i = 0; $i < $fileCount; $i++) {
			foreach ($fileKeys as $key) {
				$fileAry[$i][$key] = $files[$key][$i];
			}
		}

		return $fileAry;
	}
	private function __processUpload($file) {
		if (isset($_GET['dir'])) {
			$this->__currentDirectory = $_GET['dir'];
		}

		if (! $this->__currentDirectory) {
			$filePath = realpath($this->startDirectory);
		} else {
			$this->__currentDirectory = str_replace('..', '', $this->__currentDirectory);
			$this->__currentDirectory = ltrim($this->__currentDirectory, "/");
			$filePath = realpath($this->__currentDirectory);
		}

		$filePath = $filePath . DS . $file['name'];

		if (! empty($file)) {

			if (! $this->overwriteOnUpload) {
				if (file_exists($filePath)) {
					return 2;
				}
			}
			// TU MAS IN ARRAY
			if (! in_array($file['type'], $this->allowedUploadMimeTypes)) {
				//echo 'FILE TYPE IS: ' . $file['type'];
				var_dump($file['type']);
				return 3;
				
			}

			move_uploaded_file($file['tmp_name'], $filePath);
			
			$this->__processMetadata($file,'create');
			
			if (($file['type'] == 'application/zip' || $file['type'] == 'application/x-zip-compressed') && $this->enableUnzipping && class_exists('ZipArchive')) {

				$zip = new ZipArchive;
				$result = $zip->open($filePath);
				$zip->extractTo(realpath($this->__currentDirectory));
				$zip->close();

				if ($this->deleteZipAfterUploading) {
					// Delete the zip file
					unlink($filePath);
				}


			}

			return true;
		}
	}

	public function deleteFile() {
		if (isset($_GET['deleteFile'])) {
			
			$file = $_GET['deleteFile'];
			

			// Clean file path
			$file = str_replace('..', '', $file);
			$file = ltrim($file, "/");
			
			

			// Work out full file path
			$filePath = __DIR__ . $this->__currentDirectory . '/' . $file;
			$this->__processMetadata($filePath,'delete');

			if (file_exists($filePath) && is_file($filePath)) {
				return unlink($filePath);
				
				
			}
			return false;
		}
	}
	
	/***
		KLIMETO.COM
	***/
	private function __processMetadata($file,$action){
				if ($file){
					if($action == 'create'){
						$this->__currentDirectory = $_GET['dir'];
					}
					if($action == 'delete'){
						$this->__currentDirectory = $_GET['deleteFile'];
					}
					if (strpos($this->__currentDirectory, '/om') !== false){
						$metadataDir = realpath($file->startDirectory) . '/data/om/metadata/';
						$keyword = 'Observations and Measurements';
						$category = 'demlas_om';
					}
					if (strpos($this->__currentDirectory, '/oi') !== false){
						$metadataDir = realpath($file->startDirectory) . '/data/oi/metadata/';
						$keyword = 'Orthoimagery';
						$category = 'demlas_oi';
					}
					if (strpos($this->__currentDirectory, '/lc') !== false){
						$metadataDir = realpath($file->startDirectory) . '/data/lc/metadata/';
						$keyword = 'Land Cover';
						$category = 'demlas_lc';
					}
					if (strpos($this->__currentDirectory, '/cp') !== false){
						$metadataDir = realpath($file->startDirectory) . '/data/cp/metadata/';
						$keyword = 'Cadastral Parcels';
					}
					if (strpos($this->__currentDirectory, '/el-vec') !== false){
						$metadataDir = realpath($file->startDirectory) . '/data/el-vec/metadata/';
						$keyword = 'Elevation Vector';
						$category = 'demlas_el_vec';
					}
					if (strpos($this->__currentDirectory, '/el-tin') !== false){
						$metadataDir = realpath($file->startDirectory) . '/data/el-tin/metadata/';
						$keyword = 'Elevation TIN';
						$category = 'demlas_el_tin';
					}
					if (strpos($this->__currentDirectory, '/el-cov') !== false){
						$metadataDir = realpath($file->startDirectory) . '/data/el-cov/metadata/';
						$keyword = 'Elevation Coverage';
						$category = 'demlas_el_cov';
					}
					if (strpos($this->__currentDirectory, '/elu') !== false){
						$metadataDir = realpath($file->startDirectory) . '/data/elu/metadata/';
						$keyword = 'Existing Land Use';
						$category = 'demlas_elu';
					}
					if($action == 'create'){
						//$mdFileName = $metadataDir . 'metadata_' . str_replace('.','_',$file['name']) .'.xml';
						$mdFileName1 = explode('.',$file['name']);
						$mdFileName = $metadataDir . 'metadata_' . $mdFileName1[0] .'.xml';
						$filePath = $this->__currentDirectory . '/' . $file['name'];
						$fileModified = filemtime($filePath);
					}
					if($action == 'delete' && strpos($file, '/metadata') === false ){
						$DataFileString = explode('//',$file);
						$DataFileName = explode('.',$DataFileString[1]);
 						$mdFileName = $metadataDir . 'metadata_' . $DataFileName[0] .'.xml';
					}
					if($action == 'create'){
$mdFileContent='<?xml version="1.0" encoding="UTF-8"?>
<simpledc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dct="http://purl.org/dc/terms/" xmlns:geonet="http://www.fao.org/geonetwork" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://purl.org/dc/elements/1.1/ http://dublincore.org/schemas/xmls/qdc/2006/01/06/simpledc.xsd http://purl.org/dc/terms/ http://dublincore.org/schemas/xmls/qdc/2006/01/06/dcterms.xsd">
	<dc:identifier>a8ec20a0-fb33-402e-999e-e22699a68649</dc:identifier>
	<dc:title>'.$file['name'].'</dc:title>
	<dct:alternative></dct:alternative>
	<dct:dateSubmitted>'. date('Y-m-d', $fileModified) .'</dct:dateSubmitted>
	<dc:created>'. date('Y-m-d', $fileModified) .'</dc:created>
	<dc:identifier></dc:identifier>
	<dc:description></dc:description>
	<dc:creator>'. $_SESSION['loggedUser'] .','. $_SESSION['loggedUser'] . '@demlas.hr</dc:creator>
	<dc:subject>'.$keyword.'</dc:subject>
	<dc:type>'. $category .'</dc:type>
	<dc:rights></dc:rights>
	<dct:accessRights></dct:accessRights>
	<dc:language>Hrvatski</dc:language>
	<dc:coverage>North 48.79001416537477,South 42.66362868386096,East 29.404034570312522,West 2.685284570312518. (Global)</dc:coverage>
	<dc:format>'.$file['type'].'</dc:format>
	<dct:references>' . $this->__getUrl(). '/'. $file['name'] .'</dct:references>
	<dc:source></dc:source>
</simpledc>';
}
					if($action == 'create'){
						//echo "PROCESS: CREATE<br>";
						var_dump($mdFileName);
						var_dump($mdFileContent);
						file_put_contents ($mdFileName, $mdFileContent);
					}
					if($action == 'delete' && strpos($file, '/metadata') === false){
						//echo "PROCESS: DELETE<br>";
						var_dump($mdFileName);
						unlink($mdFileName);
						$vec = explode('//',$this->__currentDirectory);
						header("Refresh:0; url=https://demlas.geof.unizg.hr/warehouse/manager/?dir=" . $vec[0]);
						
					}
				}
	}
	public function deleteDirectory() {
		if (isset($_GET['dir'])) {
			$dir = $_GET['dir'];
			// Clean dir path
			$dir = str_replace('..', '', $dir);
			$dir = ltrim($dir, "/");

			// Work out full directory path
			$dirPath = __DIR__ . '/' . $dir;

			if (file_exists($dirPath) && is_dir($dirPath)) {

				$iterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
				$files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);

				foreach ($files as $file) {
					if ($file->isDir()) {
						rmdir($file->getRealPath());
					} else {
						unlink($file->getRealPath());
					}
				}
				return rmdir($dir);
			}
		}
		return false;
	}

	public function createDirectory() {
		if ($this->enableDirectoryCreation) {
			$directoryName = $_POST['directory'];

			// Convert spaces
			$directoryName = str_replace(' ', '_', $directoryName);

			// Clean up formatting
			$directoryName = preg_replace('/[^\w-_]/', '', $directoryName);

			if (isset($_GET['dir'])) {
				$this->__currentDirectory = $_GET['dir'];
			}

			if (! $this->__currentDirectory) {
				$filePath = realpath($this->startDirectory);
			} else {
				$this->__currentDirectory = str_replace('..', '', $this->__currentDirectory);
				$filePath = realpath($this->__currentDirectory);
			}

			$filePath = $filePath . DS . strtolower($directoryName);

			if (file_exists($filePath)) {
				return false;
			}

			return mkdir($filePath, 0755);

		}
		return false;
	}

	public function sortUrl($sort) {

		// Get current URL parts
		$urlParts = parse_url($_SERVER['REQUEST_URI']);

		$url = '';

		if (isset($urlParts['scheme'])) {
			$url = $urlParts['scheme'] . '://';
		}

		if (isset($urlParts['host'])) {
			$url .= $urlParts['host'];
		}

		if (isset($urlParts['path'])) {
			$url .= $urlParts['path'];
		}


		// Extract query string
		if (isset($urlParts['query'])) {
			$queryString = $urlParts['query'];

			parse_str($queryString, $queryParts);

			// work out if we're already sorting by the current heading
			if (isset($queryParts['order']) && $queryParts['order'] == $sort) {
				// Yes we are, just switch the sort option!
				if (isset($queryParts['sort'])) {
					if ($queryParts['sort'] == 'asc') {
						$queryParts['sort'] = 'desc';
					} else {
						$queryParts['sort'] = 'asc';
					}
				}
			} else {
				$queryParts['order'] = $sort;
				$queryParts['sort'] = 'asc';
			}

			// Now convert back to a string
			$queryString = http_build_query($queryParts);

			$url .= '?' . $queryString;
		} else {
			$order = 'asc';
			if ($sort == $this->sortBy) {
				$order = 'desc';
			}
			$queryString = 'order=' . $sort . '&sort=' . $order;
			$url .= '?' . $queryString;
		}

		return $url;
	}

	public function sortClass($sort) {
		$class = $sort . '_';

		if ($this->sortBy == $sort) {
			if ($this->__sortOrder == 'desc') {
				$class .= 'desc sort_desc';
			} else {
				$class .= 'asc sort_asc';
			}
		} else {
			$class = '';
		}
		return $class;
	}

	private function __ipWhitelistCheck() {
		// Get the users ip
		$userIp = $_SERVER['REMOTE_ADDR'];

		if (! in_array($userIp, $this->ipWhitelist)) {
			header('HTTP/1.0 403 Forbidden');
			die('Your IP address (' . $userIp . ') is not authorized to access this file.');
		}
	}

	private function __display() {
		if ($this->__currentDirectory != '.' && !$this->__endsWith($this->__currentDirectory, DS)) {
			$this->__currentDirectory = $this->__currentDirectory . DS;
		}

		return $this->__loadDirectory($this->__currentDirectory);
	}

	private function __loadDirectory($path) {
		$files = $this->__scanDir($path);

		if (! empty($files)) {
			// Strip excludes files, directories and filetypes
			$files = $this->__cleanFileList($files);

			foreach ($files as $file) {
				$filePath = realpath($this->__currentDirectory . DS . $file);

				if ($this->__isDirectory($filePath)) {

					if (! $this->includeUrl) {
						$urlParts = parse_url($_SERVER['REQUEST_URI']);

						$dirUrl = '';

						if (isset($urlParts['scheme'])) {
							$dirUrl = $urlParts['scheme'] . '://';
						}

						if (isset($urlParts['host'])) {
							$dirUrl .= $urlParts['host'];
						}

						if (isset($urlParts['path'])) {
							$dirUrl .= $urlParts['path'];
						}
					} else {
						$dirUrl = $this->directoryUrl;
					}

					if ($this->__currentDirectory != '' && $this->__currentDirectory != '.') {
						$dirUrl .= '?dir=' . $this->__currentDirectory . $file;
					} else {
						$dirUrl .= '?dir=' . $file;
					}

					$this->__directoryList[$file] = array(
						'name' => $file,
						'path' => $filePath,
						'type' => 'dir',
						'url' => $dirUrl
					);
				} else {
					$this->__fileList[$file] = $this->__getFileType($filePath, $this->__currentDirectory . DS . $file);
				}
			}
		}

		if (! $this->showSubDirectories) {
			$this->__directoryList = null;
		}

		$data = array(
			'currentPath' => $this->__currentDirectory,
			'directoryTree' => $this->__getDirectoryTree(),
			'files' => $this->__setSorting($this->__fileList),
			'directories' => $this->__directoryList,
			'requirePassword' => $this->passwordProtect,
			'enableUploads' => $this->enableUploads
		);

		return $data;
	}

	private function __setSorting($data) {
		$sortOrder = '';
		$sortBy = '';

		// Sort the files
		if ($this->sortBy == 'name') {
			function compareByName($a, $b) {
				return strnatcasecmp($a['name'], $b['name']);
			}

			usort($data, 'compareByName');
			$this->soryBy = 'name';
		} elseif ($this->sortBy == 'size') {
			function compareBySize($a, $b) {
				return strnatcasecmp($a['size_bytes'], $b['size_bytes']);
			}

			usort($data, 'compareBySize');
			$this->soryBy = 'size';
		} elseif ($this->sortBy == 'modified') {
			function compareByModified($a, $b) {
				return strnatcasecmp($a['modified'], $b['modified']);
			}

			usort($data, 'compareByModified');
			$this->soryBy = 'modified';
		}

		if ($this->__sortOrder == 'desc') {
			$data = array_reverse($data);
		}
		return $data;
	}

	private function __scanDir($dir) {
		// Prevent browsing up the directory path.
		if (strstr($dir, '../')) {
			return false;
		}

		if ($dir == '/') {
			$dir = $this->startDirectory;
			$this->__currentDirectory = $dir;
		}

		$strippedDir = str_replace('/', '', $dir);

		$dir = ltrim($dir, "/");

		// Prevent listing blacklisted directories
		if (in_array($strippedDir, $this->ignoredDirectories)) {
			return false;
		}

		if (! file_exists($dir) || !is_dir($dir)) {
			return false;
		}

		return scandir($dir);
	}

	private function __cleanFileList($files) {
		$this->ignoredDirectories[] = '.';
		$this->ignoredDirectories[] = '..';

		foreach ($files as $key => $file) {

			// Remove unwanted directories
			if ($this->__isDirectory(realpath($file)) && in_array($file, $this->ignoredDirectories)) {
				unset($files[$key]);
			}

			// Remove dot directories (if enables)
			if ($this->ignoreDotDirectories && substr($file, 0, 1) === '.') {
				unset($files[$key]);
			}

			// Remove unwanted files
			if (! $this->__isDirectory(realpath($file)) && in_array($file, $this->ignoredFileNames)) {
				unset($files[$key]);
			}

			// Remove unwanted file extensions
			if (realpath($file) != '' && ! $this->__isDirectory(realpath($file))) {

				$info = pathinfo($file);
				$extension = $info['extension'];

				if (in_array($extension, $this->ignoredFileExtensions)) {
					unset($files[$key]);
				}

				// If dot files want ignoring, do that next
				if ($this->ignoreDotFiles) {

					if (substr($file, 0, 1) == '.') {
						unset($files[$key]);
					}
				}
			}
		}
		return $files;
	}

	private function __isDirectory($file) {
		if ($file == $this->__currentDirectory . DS . '.' || $file == $this->__currentDirectory . DS . '..') {
			return true;
		}
		if (filetype($file) == 'dir') {
			return true;
		}

		return false;
	}

	/**
	 * __getFileType
	 *
	 * Returns the formatted array of file data used for thre directory listing.
	 *
	 * @param  string $filePath Full path to the file
	 * @return array   Array of data for the file
	 */
	private function __getFileType($filePath, $relativePath = null) {
		$fi = new finfo(FILEINFO_MIME_TYPE);

		if (! file_exists($filePath)) {
			return false;
		}

		$type = $fi->file($filePath);

		$filePathInfo = pathinfo($filePath);

		$fileSize = filesize($filePath);

		$fileModified = filemtime($filePath);

		$filePreview = false;

		// Check if the file type supports previews
		if ($this->__supportsPreviews($type) && $this->showThumbnails) {
			$filePreview = true;
		}

		return array(
			'name' => $filePathInfo['basename'],
			'extension' => $filePathInfo['extension'],
			'dir' => $filePathInfo['dirname'],
			'path' => $filePath,
			'relativePath' => $relativePath,
			'size' => $this->__formatSize($fileSize),
			'size_bytes' => $fileSize,
			'modified' => $fileModified,
			'type' => 'file',
			'mime' => $type,
			'url' => $this->__getUrl($filePathInfo['basename']),
			'preview' => $filePreview,
			'target' => ($this->openLinksInNewTab ? '_blank' : '_parent')
		);
	}

	private function __supportsPreviews($type) {
		if (in_array($type, $this->__previewMimeTypes)) {
			return true;
		}
		return false;
	}

	/**
	 * __getUrl
	 *
	 * Returns the url to the file.
	 *
	 * @param  string $file filename
	 * @return string   url of the file
	 */
	private function __getUrl($file) {
		if (! $this->includeUrl) {
			$dirUrl = $_SERVER['REQUEST_URI'];

			$urlParts = parse_url($_SERVER['REQUEST_URI']);

			$dirUrl = '';

			if (isset($urlParts['scheme'])) {
				$dirUrl = $urlParts['scheme'] . '://';
			}

			if (isset($urlParts['host'])) {
				$dirUrl .= $urlParts['host'];
			}

			if (isset($urlParts['path'])) {
				$dirUrl .= $urlParts['path'];
			}
		} else {
			$dirUrl = $this->directoryUrl;
		}

		if ($this->__currentDirectory != '.') {
			$dirUrl = $dirUrl . $this->__currentDirectory;
		}
		return $dirUrl . $file;
	}

	private function __getDirectoryTree() {
		$dirString = $this->__currentDirectory;
		$directoryTree = array();

		$directoryTree['./'] = 'Index';

		if (substr_count($dirString, '/') >= 0) {
			$items = explode("/", $dirString);
			$items = array_filter($items);
			$path = '';
			foreach ($items as $item) {
				if ($item == '.' || $item == '..') {
					continue;
				}
				$path .= $item . '/';
				$directoryTree[$path] = $item;

			}
		}

		$directoryTree = array_filter($directoryTree);

		return $directoryTree;
	}

	private function __endsWith($haystack, $needle) {
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}

	private function __generatePreview($filePath) {
		$file = $this->__getFileType($filePath);
		if ($file['mime'] == 'image/jpeg') {
			$image = imagecreatefromjpeg($file['path']);
		} elseif ($file['mime'] == 'image/png') {
			$image = imagecreatefrompng($file['path']);
		} elseif ($file['mime'] == 'image/gif') {
			$image = imagecreatefromgif($file['path']);
		} 
		/***
		KLIMETO.COM
		***/
		elseif ($file['mime'] == 'image/tiff') {
			$image = new Imagick($file['path']);
			$image->setImageFormat( "png" );
			$image->resizeImage(100, 100, imagick::FILTER_LANCZOS, 1);  
			header( "Content-Type: image/png" );
			echo $image;
		}
		else {
			die();
		}

		$oldX = imageSX($image);
		$oldY = imageSY($image);

		$newW = 100;
		$newH = 100;

		if ($oldX > $oldY) {
			$thumbW = $newW;
			$thumbH = $oldY * ($newH / $oldX);
		}
		if ($oldX < $oldY) {
			$thumbW = $oldX * ($newW / $oldY);
			$thumbH = $newH;
		}
		if ($oldX == $oldY) {
			$thumbW = $newW;
			$thumbH = $newW;
		}
		header('Content-Type: ' . $file['mime']);

		$newImg = ImageCreateTrueColor($thumbW, $thumbH);

		imagecopyresampled($newImg, $image, 0, 0, 0, 0, $thumbW, $thumbH, $oldX, $oldY);

		if ($file['mime'] == 'image/jpeg') {
			imagejpeg($newImg);
		} elseif ($file['mime'] == 'image/png') {
			imagepng($newImg);
		} elseif ($file['mime'] == 'image/gif') {
			imagegif($newImg);
		}
		imagedestroy($newImg);
		die();
	}

	private function __formatSize($bytes) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= pow(1024, $pow);

		return round($bytes, 2) . ' ' . $units[$pow];
	}

}

$listing = new DirectoryListing();

$successMsg = null;
$errorMsg = null;

if (isset($_POST['password']) && isset ($_POST['user'])) {
	$listing->login();

	if (isset($_SESSION['evdir_loginfail'])) {
		$errorMsg = 'Login Failed! Please check you entered the correct user and corresponding password an try again.';
		unset($_SESSION['evdir_loginfail']);
	}

} elseif(isset($_GET['logout'])){
	$listing->logout();
}

 elseif (isset($_FILES['upload'])) {
	$uploadStatus = $listing->upload();
	if ($uploadStatus == 1) {
		$successMsg = 'Your file was successfully uploaded!';
		//print_r($file);
	} elseif ($uploadStatus == 2) {
		$errorMsg = 'Your file could not be uploaded. A file with that name already exists.';
	} elseif ($uploadStatus == 3) {
		$errorMsg = 'Your file could not be uploaded as the file type is blocked.';
	}
} elseif (isset($_POST['directory'])) {
	if ($listing->createDirectory()) {
		$successMsg = 'Directory Created!';
	} else {
		$errorMsg = 'There was a problem creating your directory.';
	}
} elseif (isset($_GET['deleteFile']) && $listing->enableFileDeletion) {
	if ($listing->deleteFile()) {
		$successMsg = 'The dataset file and corresponding metadata was successfully deleted!';
	} else {
		$errorMsg = 'The selected file could not be deleted. Please check your file permissions and try again.';
	}
} elseif (isset($_GET['dir']) && isset($_GET['delete']) && $listing->enableDirectoryDeletion) {
	if ($listing->deleteDirectory()) {
		$successMsg = 'The directory was successfully deleted!';
		unset($_GET['dir']);
	} else {
		$errorMsg = 'The selected directory could not be deleted. Please check your file permissions and try again.';
	}
}

$data = $listing->run();

function pr($data, $die = false) {
	echo '<pre>';
	print_r($data);
	echo '</pre>';

	if ($die) {
		die();
	}
}
?>
<!doctype html>
<html lang="en" ng-app="editorModalFormApp" >
<head>
	<title>Directory Listing of <?php echo $data['currentPath'] . (!empty($listing->pageTitle) ? ' (' . $listing->pageTitle . ')' : null); ?></title>
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; minimum-scale=1.0; user-scalable=no; target-densityDpi=device-dpi" />
	<link rel="shortcut icon" href="https://demlas.geof.unizg.hr/theme/image.php/aardvark/theme/1493373374/favicon">
	<link rel="stylesheet" href="/warehouse/manager/src/css/style.css"></link>
	
	
	<?php if($listing->enableTheme): ?>
		<link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.5/yeti/bootstrap.min.css" rel="stylesheet" integrity="sha256-gJ9rCvTS5xodBImuaUYf1WfbdDKq54HCPz9wk8spvGs= sha512-weqt+X3kGDDAW9V32W7bWc6aSNCMGNQsdOpfJJz/qD/Yhp+kNeR+YyvvWojJ+afETB31L0C4eO0pcygxfTgjgw==" crossorigin="anonymous">
	<?php endif; ?>
	
	 <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
	 <link rel="stylesheet" href="https://tombatossals.github.io/angular-openlayers-directive/bower_components/openlayers3/build/ol.css" />
	 <link rel="stylesheet" href="https://rawgithub.com/sthomp/angular-blurred-modal/master/st-blurred-dialog.css">
	 <link rel="stylesheet" href="/warehouse/manager/src/css/angular-resizable.min.css"></link>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/angular-loading-bar/0.9.0/loading-bar.min.css" type="text/css" media="all" />
	<!-- JS ===================== -->
	<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>

	
    
	
	<script src="https://cdn.rawgit.com/abdmob/x2js/master/xml2json.js"></script>
    <script src="https://tombatossals.github.io/angular-openlayers-directive/bower_components/angular/angular.min.js"></script>
	<script src="https://tombatossals.github.io/angular-openlayers-directive/bower_components/openlayers3/build/ol.js"></script>
	 
	 <script src="https://tombatossals.github.io/angular-openlayers-directive/bower_components/angular-sanitize/angular-sanitize.min.js"></script>
	<script src="https://tombatossals.github.io/angular-openlayers-directive/dist/angular-openlayers-directive.min.js"></script>
	
	<script src="//angular-ui.github.io/bootstrap/ui-bootstrap-tpls-2.5.0.js"></script>
    <script src="https://angular-ui.github.io/bootstrap/ui-bootstrap-tpls-0.9.0.js"></script>
	<script src="/warehouse/manager/src/js/angular-resizable.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/angular-loading-bar/0.9.0/loading-bar.min.js"></script>
	<script src="/warehouse/manager/src/js/angular-base64.min.js"></script>
	
	
	<script src="/warehouse/editor/src/angular-local-storage.min.js"></script>
	<script src="/warehouse/manager/src/js/app.js"></script>
		<!--<script src="/warehouse/editor/src/app.js"></script>-->
</head>
<body>
	<div class="container-fluid">
		<?php if (! empty($listing->pageTitle)): ?>
			<div class="row">
				<div class="col-xs-12">
					<h1 class="text-center"><?php echo $listing->pageTitle; ?></h1>
				</div>
			</div>
		<?php endif; ?>
		<?php if (! empty($successMsg)): ?>
			<div class="alert alert-success"><?php echo $successMsg; ?></div>
		<?php endif; ?>
		<?php if (! empty($errorMsg)): ?>
			<div class="alert alert-danger"><?php echo $errorMsg; ?></div>
		<?php endif; ?>
		<?php if ($data['requirePassword'] && !isset($_SESSION['evdir_loggedin'])): ?>
			<div class="row">
				<div class="col-xs-12">
				<hr>
					<form action="" method="post" class="text-center form-inline">
						<div class="form-group">
							<label for="user">User:</label>
							<input type="user" name="user" class="form-control">
							<label for="password">Password:</label>
							<input type="password" name="password" class="form-control">
							<button type="submit" class="btn btn-primary">Login</button>
						</div>
					</form>
				</div>
			</div>
		<?php else: ?>
			<span class="pull-right pull-top">
				<a href="?logout=true" class="btn btn-danger btn-xs" onclick="return confirm('Do you wanna log out from manager?')">Logout (<?php echo $_SESSION['loggedUser'] ?>)</a>
			</span>
			<?php if(! empty($data['directoryTree'])): ?>
				<div class="row">
					<div class="col-xs-12">
						<ul class="breadcrumb">
						<?php foreach ($data['directoryTree'] as $url => $name): ?>
							<li>
								<?php
								$lastItem = end($data['directoryTree']);
								if($name === $lastItem):
									echo $name;
								else:
								?>
									<a href="?dir=<?php echo $url; ?>">
										<?php echo $name; ?>
									</a>
								<?php
								endif;
								?>
							</li>
						<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php endif; ?>
				<div class="row">
					<div class="col-xs-12">
						<div class="table-container">
							<table class="table table-striped table-bordered">
								<?php if (! empty($data['directories'])): ?>
									<thead>
										<th>Directory</th>
									</thead>
									<tbody>
										<?php foreach ($data['directories'] as $directory): ?>
											<tr>
												<td>
													<a href="<?php echo $directory['url']; ?>" class="item dir">
														<?php echo $directory['name']; ?>
													</a>
													<?php if ($listing->enableDirectoryDeletion): ?>
														<span class="pull-right">
															<a href="<?php echo $directory['url']; ?>&delete=true" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure? The file will be deleted permanently!')">Delete</a>
														</span>
													<?php endif; ?>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								<?php endif; ?>
								<?php if(($listing->enableDirectoryCreation) && (strpos($_GET['dir'], '/om') !== false ||strpos($_GET['dir'], '/oi') !== false || strpos($_GET['dir'], '/lc') !== false || strpos($_GET['dir'], '/cp') !== false || strpos($_GET['dir'], '/el-vec') !== false || strpos($_GET['dir'], '/el-tin') !== false || strpos($_GET['dir'], '/el-cov') !== false || strpos($_GET['dir'], '/elu') !== false)) : ?>
								<tfoot>
									<tr>
										<td>
											<form action="" method="post" class="text-center form-inline">
												<div class="form-group">
													<label for="directory">Directory Name:</label>
													<input type="text" name="directory" id="directory" class="form-control">
													<button type="submit" class="btn btn-primary" name="submit">Create Directory</button>
												</div>
											</form>
										</td>
									</tr>
								</tfoot>
								<?php endif; ?>
							</table>
						</div>
					</div>
				</div>
			<?php if (($data['enableUploads']) && (strpos($_GET['dir'], '/om') !== false ||strpos($_GET['dir'], '/oi') !== false || strpos($_GET['dir'], '/lc') !== false || strpos($_GET['dir'], '/cp') !== false || strpos($_GET['dir'], '/el-vec') !== false|| strpos($_GET['dir'], '/el-tin') !== false || strpos($_GET['dir'], '/el-cov') !== false || strpos($_GET['dir'], '/elu') !== false)): ?>
				<div class="row">
					<div class="col-xs-12">
						<form action="" method="post" enctype="multipart/form-data" class="text-center upload-form form-vertical">
							<h4>Upload A File</h4>
							<div class="row upload-field">
								<div class="col-xs-12">
									<div class="form-group">
										<div class="row">
											<div class="col-sm-2 col-md-2 col-md-offset-3 text-right">
												<label for="upload">File:</label>
											</div>
											<div class="col-sm-10 col-md-4">
												<input type="file" name="upload[]" id="upload" class="form-control">
											</div>
										</div>
									</div>
								</div>
							</div>
							<hr>
							<?php if ($listing->enableMultiFileUploads): ?>
								<div class="row">
									<div class="col-xs-12 col-sm-6 col-md-4 col-md-offset-2 col-lg-3 col-lg-offset-2">
										<button type="button" class="btn btn-success btn-block" name="add_file">Add Another File</button>
									</div>
									<div class="col-xs-12 col-sm-6 col-md-4 col-md-offset-1 col-lg-3 col-lg-offset-2">
										<button type="submit" class="btn btn-primary btn-block" name="submit">Upload File(s)</button>
									</div>
								</div>
							<?php else: ?>
								<div class="row">
									<div class="col-xs-12 col-sm-6 col-sm-offset-3">
										<button type="submit" class="btn btn-primary btn-block" name="submit">Upload File</button>
									</div>
								</div>
							<?php endif; ?>
						</form>
					</div>
				</div>
			<?php endif; ?>
			<?php if (! empty($data['files'])): ?>
				<div class="row">
					<div class="col-xs-12">
						<div class="table-container">
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>
											<a href="<?php echo $listing->sortUrl('name'); ?>">File Name <span class="<?php echo $listing->sortClass('name'); ?>"></span></a>
										</th>
										<th class="text-right xs-hidden">
											<a href="<?php echo $listing->sortUrl('size'); ?>">Size <span class="<?php echo $listing->sortClass('size'); ?>"></span></a>
										</th>
										<th class="text-right sm-hidden">
											<a href="<?php echo $listing->sortUrl('modified'); ?>">Last Modified <span class="<?php echo $listing->sortClass('modified'); ?>"></span></a>
										</th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($data['files'] as $file): ?>
									<tr>
										<td>
											<a href="<?php echo $file['url']; ?>" target="<?php echo $file['target']; ?>" class="item _blank <?php echo $file['extension']; ?>" >
												<?php echo $file['name']; ?>
											</a>
											<?php if (isset($file['preview']) && $file['preview']): ?>
												<span class="preview"><img src="?preview=<?php echo $file['relativePath']; ?>"><i class="preview_icon"></i></span>
												<!--<img src="?preview=<?php //echo $file['relativePath']; ?>" class="img-thumbnail"/>-->
											<?php endif; ?>
											<?php if ($listing->enableFileDeletion == true): ?>
												<a href="?deleteFile=<?php echo urlencode($file['relativePath']); ?>" class="pull-right btn btn-danger btn-xs" onclick="return confirm('WARNING: This will delete the file permanently!')">Delete</a>
											<?php endif; ?>
											<?php if ($listing->enableMetadataCreation == true): 
												if (strpos($file["url"], '/om') !== false){
													$metadataURI = substr($file['url'], 0, strpos($file['url'], '/data/')) . '/data/om/metadata/';
												}
												if (strpos($file["url"], '/oi') !== false){
													$metadataURI = substr($file['url'], 0, strpos($file['url'], '/data/')) . '/data/oi/metadata/';
												}
												if (strpos($file["url"], '/lc') !== false){
													$metadataURI = substr($file['url'], 0, strpos($file['url'], '/data/')) . '/data/lc/metadata/';
												}
												if (strpos($file["url"], '/cp') !== false){
													$metadataURI = substr($file['url'], 0, strpos($file['url'], '/data/')) . '/data/cp/metadata/';
												}
												if (strpos($file["url"], '/el-vec') !== false){
													$metadataURI = substr($file['url'], 0, strpos($file['url'], '/data/')) . '/data/el-vec/metadata/';
												}
												if (strpos($file["url"], '/el-tin') !== false){
													$metadataURI = substr($file['url'], 0, strpos($file['url'], '/data/')) . '/data/el-tin/metadata/';
												}
												if (strpos($file["url"], '/el-cov') !== false){
													$metadataURI = substr($file['url'], 0, strpos($file['url'], '/data/')) . '/data/el-cov/metadata/';
												}
												if (strpos($file["url"], '/elu') !== false){
													$metadataURI = substr($file['url'], 0, strpos($file['url'], '/data/')) . '/data/elu/metadata/';
												}
												if (strpos($file["url"], '/metadata') !== false){
													$mdFileName = $file['name'];
													$mdFileURI = $metadataURI . $file['name'];
													//echo "SOM V METADATA FOLDERI!!!<br>";
													var_dump ($mdFileURI);
												}
												else{
													//$mdFileURI = $metadataURI . 'metadata_' . str_replace('.','_',$file['name']) .'.xml';
													//$mdFileName = 'metadata_' . str_replace('.','_',$file['name']) .'.xml';
													$mdFileName1 = explode('.',$file['name']);
													$mdFileName = 'metadata_' . $mdFileName1[0] .'.xml';
													//$mdFileURI = $metadataURI . 'metadata_'. $mdFileName .'.xml';
													$mdFileURI = $metadataURI . $mdFileName;
													//echo "SOM V DATA FOLDERI!!!<br>";
													var_dump ($mdFileURI);
												}
											?>
											<button ng-controller="editorModalFormController" class="pull-right btn btn-primary btn-xs" ng-click="openModal('<?php echo $mdFileURI;?>','<?php echo $mdFileName; ?>')">Metadata</button>
											<?php endif; ?>
											
											<a href="<?php echo $file['url']; ?>" class="pull-right btn btn-success btn-xs" download>Download</a>
											<?php if($listing->enableGeoserver == true && ($file['extension'] === 'tif' || $file['extension'] === 'TIF')): ?>
												<?php echo $listing->geoserverCheck($file['name']); ?>
												<!--
												<button class="pull-right btn btn-info btn-xs" onclick="alert('LAYER NAME IS: <?php //echo $listing->geoserverCheck($file['name']); ?>')">Geoserver</button>
												-->
											<?php endif; ?>
											
											
										</td>
										<td class="text-right xs-hidden"><?php echo $file['size']; ?></td>
										<td class="text-right sm-hidden"><?php echo date('M jS Y \a\t g:ia', $file['modified']); ?></td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			<?php elseif (strpos($_GET['dir'], '/om') !== false ||strpos($_GET['dir'], '/oi') !== false || strpos($_GET['dir'], '/lc') !== false || strpos($_GET['dir'], '/cp') !== false) : ?>
				<div class="row">
					<div class="col-xs-12">
						<p class="alert alert-info text-center">This directory does not contain any files.</p>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>
		<div class="row">
			<div class="col-xs-12 text-center"><hr>Powered by Directory Listing Script &copy; <?php echo date('Y'); ?> Evoluted, <a href="http://www.evoluted.net">Web Design Sheffield</a><hr>Developed by <a href="http://klimeto.com/drupal8" target="_new"><span class="label label-info">klimeto</span></a> Hosted by <a href="http://www.srce.unizg.hr/ target="_new"><span class="label label-warning">srce</span></a></div>
		</div>
		<div id="loaderGIF" ng-controller="loaderGIF">
		</div>
	</div>
	<link rel="stylesheet" href="/warehouse/manager/src/css/preview.css"></link>
	<?php if ($listing->enableMultiFileUploads): ?>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script>
			$('button[name=add_file]').on('click', function(e) {
				e.preventDefault();
				$('.upload-field:last').clone().insertAfter('.upload-field:last').find('input').val('');
			});
		</script>
	<?php endif; ?>
	<script type="text/javascript" src="https://rawgithub.com/sthomp/angular-blurred-modal/master/st-blurred-dialog.js"></script>
</body>
</html>
