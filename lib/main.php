<?php

namespace application;
require __DIR__.'/../conf/config.php';

$appFolder = $config['appFolder'];
$imgFolder = $config['imageFolder'];
$iconFolder = $imgFolder.'icons/';

abstract class Application
{
	abstract public function findInfos($ipaPath, $rootPath);
	protected static $extension; // string with ipa ou apk

	protected $id;
	protected $name;
	protected $description;
	protected $versions;

	public function Application($id='', $name='', $description='', $versions='')
	{
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->versions = $versions;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}
	public function getVersions(){
		return $this->versions;
	}

	public function setVersions($argVersions){
		$this->versions = $argVersions;
	}

	public function getId(){
		return $this->id;
	}

	public function setId($argId){
		$this->id = $argId;

	}
}


class IosApp extends Application
{
	protected static $extension = 'ipa';

	public function findInfos($ipaPath, $rootPath=null) // old getApplicationInfo
	{
		$za = new \ZipArchive();
		$za->open($ipaPath);

		for ($i=0; $i<$za->numFiles;$i++) {
			$entry = $za->statIndex($i);
			$entryName = $entry['name'];

			if (preg_match('/^Payload\/(.*?)\.app\/config.xml$/i', $entryName)) {
				$xmlPath = "zip://{$ipaPath}#{$entryName}";

				$config = new \SimpleXMLElement(file_get_contents($xmlPath));

				$path = $ipaPath;

				if ($rootPath){
					$path = Path::getRelativePath($rootPath, $path);
				}

				$this->id = (string)$config->attributes()['id'];
				$this->name = (string)$config->name;
				$this->description = (string)$config->description;
				$this->versions = [(string)$config->attributes()['version'] => $path];
				// print_r($this);
				return $this;
			}
		}
		return null;
	}

}

class AndroidApp extends Application
{
	protected static $extension = 'apk';

	public function findInfos($apkPath, $rootPath=null) // old getApkinfo
		{
			$apk = new \ApkParser\Parser($apkPath);
			$manifest = $apk->getManifest();

			$path = $apkPath;

			if ($rootPath)
				$path = Path::getRelativePath($rootPath, $path);

			$this->id = $manifest->getPackageName();
			$this->name = $manifest->getApplication()->getActivityNameList()[0];
			$this->description = "";
			$this->versions = [$manifest->getVersionName() => $path ];
			return $this;
		}

}



class Path // DONE
{
	static  function join() // old joinPath, as many arguments as needed
	{
		$args = func_get_args();

		$result = false;

		if (!empty($args))
			$result = preg_replace('/\/$/', '', array_shift($args)); # Remove trailing slash

		if (!empty($args)) {
			$result .= '/';
			$result .= preg_replace('/^\//', '', array_shift($args)); # Remove trailing slash
		}

		// http://stackoverflow.com/questions/11780551/php-call-user-func-with-class-and-arguments
		if (!empty($args))
			$result = call_user_func_array(array('\application\Path','join'), array_merge([$result],$args)); # recurse with remaining args.

		return $result;
	}

	static function getRelativePath($parent, $child)
	{
		$result = substr($child, strlen($parent));
		$result = preg_replace('/^\//', '', $result);
		return $result;
	}


	static function getCurrentUrlFolder()
	{
		return preg_replace('/[^\/]*(\?.*)?$/', '', Path::getCurrentUrl());
	}


	private static function getCurrentUrl()
	{
		return Path::getCurrentServerAddress().$_SERVER['REQUEST_URI'];
	}


	private static function getCurrentServerAddress()
	{
		$protocol = "http";

		if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'Off')
			$protocol .= 's';

		return "{$protocol}://{$_SERVER['SERVER_NAME']}:{$_SERVER['SERVER_PORT']}";
	}
}


class AppList
{
	private $extension;
	private $apps = array();

	public function __construct($ext){

		$this->extension = $ext;
	}

	public function check($appList, $appToTest) // old checkAppAlreadyInList ... check if App is Already In the List
	{
		// print_r($appList);
		// print_r($appToTest);
		foreach ($appList as $key=>$app){
			if (strcmp($app->getId(),$appToTest->getId()) == 0){
				return $key;
			}
		}
		return -1;
	}

	public function getApps(){
		return $this->apps;
	}

	public function getExtension(){

		return $this->extension;
	}

	public function findPaths($dir) // old findIosAppPath et findAndroidAppPath
	{


		// $dir = joinPath($dir); # remove trailing slash, if any
		$dir = Path::join($dir); # remove trailing slash, if any
		$files = scandir($dir);
		$files = array_diff(scandir($dir), array('..', '.')); #remove  . .. directory in the linux environment
		$appPathList = [];

		foreach ($files as $file) {

			if (preg_match('/\.'.$this->extension.'$/i', $file))
			{
				$appPathList [] = "{$dir}/{$file}";
			}
			else if (is_dir("{$dir}/{$file}"))
			{
				$appPathList2  = $this->findPaths("{$dir}/{$file}"); // a remplacer
				$appPathList = array_merge($appPathList, $appPathList2);
			}
		}
		return $appPathList;
	}


	public function createListFromPaths($dir, $listPath){

		foreach ($listPath as $appPath){

			if ($this->extension == 'ipa'){
				$app = new IosApp();

			}
			else if ($this->extension == 'apk'){
				$app = new AndroidApp();
			}


			$app->findInfos($appPath, $dir);
			$temp = $app;

			$indice = $this->check($this->apps, $temp);

			if ($indice == -1){
				array_push($this->apps,$temp);

				} else {
					$apptemp = $this->apps[$indice];
					$versions = array_merge($apptemp->getVersions(), $temp->getVersions());
					$apptemp->setVersions($versions);
				}
		}

	}

	public function findAllApps($dir) // A FINIR fusion de findAndroidApps et findIosApps
	{
		$dirResult = Path::join($dir); # remove trailing slash, if any

		$files = scandir($dirResult);
		// $result = array();


		$appPathList = $this->findPaths($dir);

		$this->createListFromPaths($dir,$appPathList);


		AppList::sortByNameAndVersion($this->apps);

			// Trouver l icone dans le fichier

		$this->findIconsInLastVersion();

	}

	public function findIconsInLastVersion(){

		global $appFolder;
		global $imgFolder;
		global $iconFolder;

		foreach ($this->apps as $app) {
					$appName = $app->getName();
					if ($this->extension == 'ipa'){
						$iconPath = 'Payload/'.$appName.'.app/icon-72.png';
					}
					else if ($this->extension == 'apk'){
						$iconPath = 'res/drawable/icon.png';
					}

					$za = new \ZipArchive();
					$za->open($appFolder.$app->getVersions()[array_keys($app->getVersions())[0]]);
					$za->extractTo(Path::join($imgFolder,'tmp'), $iconPath);
					$za->close();

					if (!file_exists(Path::join($iconFolder,$appName))) {
						mkdir(Path::join($iconFolder,$appName), 0755, true);
					}

					if (file_exists(Path::join($imgFolder,'tmp/',$iconPath))) {
						copy(Path::join($imgFolder,'tmp/',$iconPath), Path::join($iconFolder,$appName,'/icon.png'));
					}

					if (file_exists($iconFolder.$appName)) {
						$this->rrmdir(Path::join($imgFolder,'tmp/res/'));
					}
				}
	}
	public function rrmdir($dir)
	{
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
						}
				}
				reset($objects);
				rmdir($dir);
		}
	}

	static function sortByName($array)
	{
		usort($array, function ($a, $b)
		{
			$a->setName(strtolower($a->getName())) ;
			$b->setName(strtolower($b->getName())) ;

			if ($a->getName() == $b->getName())
			{
					return 0;
			}

			return ($a->getName() < $b->getName()) ? -1 : 1;
		});
	}

	static function sortByVersions($array){

		for( $i= 0 ; $i <sizeof($array)  ; $i++ ){
			$appsTemp =$array[$i]->getVersions();
			uksort($appsTemp, function ($a, $b)
			{
				return  -1 * version_compare($a, $b); // multiply by -1 to reverse sort order
			});
			$array[$i]->setversions($appsTemp) ;
		}
	}

	static function sortByNameAndVersion($array){

		AppList::sortByName($array);
		AppList::sortByVersions($array);

	}

}

class Web {

	public static function sendNotFound() {
		header('HTTP/1.0 404 Not Found');
		echo "Not Found";
		exit();
	}


	public static function setContentType($contentType) {
		header("Content-type: {$contentType}");
	}

	// NOT USED YET //
	public static function getBrowser(){ // fonction avancée

		$u_agent = $_SERVER['HTTP_USER_AGENT'];
			$bname = 'Unknown';
			$platform = 'Unknown';
			$version= "";

			//First get the platform?
			if (preg_match('/linux/i', $u_agent)) {
					$platform = 'linux';
			}
			elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
					$platform = 'mac';
			}
			elseif (preg_match('/windows|win32/i', $u_agent)) {
					$platform = 'windows';
			}

			// Next get the name of the useragent yes seperately and for good reason
			if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
			{
					$bname = 'Internet Explorer';
					$ub = "MSIE";
			}
			elseif(preg_match('/Firefox/i',$u_agent))
			{
					$bname = 'Mozilla Firefox';
					$ub = "Firefox";
			}
			elseif(preg_match('/Chrome/i',$u_agent))
			{
					$bname = 'Google Chrome';
					$ub = "Chrome";
			}
			elseif(preg_match('/Safari/i',$u_agent))
			{
					$bname = 'Apple Safari';
					$ub = "Safari";
			}
			elseif(preg_match('/Opera/i',$u_agent))
			{
					$bname = 'Opera';
					$ub = "Opera";
			}
			elseif(preg_match('/Netscape/i',$u_agent))
			{
					$bname = 'Netscape';
					$ub = "Netscape";
			}

			// finally get the correct version number
			$known = array('Version', $ub, 'other');
			$pattern = '#(?<browser>' . join('|', $known) .
			')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
			if (!preg_match_all($pattern, $u_agent, $matches)) {
					// we have no matching number just continue
			}

			// see how many we have
			$i = count($matches['browser']);
			if ($i != 1) {
					//we will have two since we are not using 'other' argument yet
					//see if version is before or after the name
					if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
							$version= $matches['version'][0];
					}
					else {
							$version= $matches['version'][1];
					}
			}
			else {
					$version= $matches['version'][0];
			}

			// check if we have a number
			if ($version==null || $version=="") {$version="?";}

			return array(
					'userAgent' => $u_agent,
					'name'      => $bname,
					'version'   => $version,
					'platform'  => $platform,
					'pattern'    => $pattern
			);
	}
}
