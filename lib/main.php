<!-- TODO :
			- verifier les instanciations
			- verifier les appels de functions (certaines on ete renommer)

 -->

<?php

	require __DIR__.'/../conf/config.php';

	$appFolder = $config['appFolder'];
	$imgFolder = $config['imageFolder'];
	$iconFolder = $imgFolder.'icons/';

abstract class Application
{
	abstract public function getInfos();
	private static $extension; // string with ipa ou apk

	private $id;
	private $name;
	private $description;
	private $versions;

	public function Application($id, $name, $description, $versions)
	{
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->versions = $versions;
	}

	public function findPaths($dir) // old findIosAppPath et findAndroidAppPath
	{
		$path = new Paths();
		$dir = joinPath($dir); # remove trailing slash, if any
		$files = scandir($dir);
		$files = array_diff(scandir($dir), array('..', '.')); #remove  . .. directory in the linux environment
		$appPathList = [];

		foreach ($files as $file) {
			if (preg_match('/\.'.$extension$.'/i', $file)) // $extension represente la variable static $extension
			{
				$appPathList [] = "{$dir}/{$file}";
			}
			else if (is_dir("{$dir}/{$file}"))
			{
				$appPathList2  = findIosAppPaths("{$dir}/{$file}");
				$appPathList = array_merge($appPathList, $appPathList2);
			}
		}
		return $appPathList;
	}


	public function findApps() // fusion de findAndroidApps et findIosApps
	{
		$dir = joinPath($dir); # remove trailing slash, if any
		$result = array();
		$files = scandir($dir);
		global $appFolder;
		global $imgFolder;
		global $iconFolder;

		$appPathList = findAndroidAppPaths($dir);

		foreach ($appPathList as $appPath){

			//echo $appPath;
			$temp = getApkinfo($appPath, $dir);
			$indice = checkAppAlreadyInList($result, $temp);
			if ($indice == -1){
				$result [] = $temp;

		    } else {
		    	$versions = array_merge($result[$indice]['versions'], $temp['versions']);
		    	$result[$indice]['versions'] = $versions;
		    }
		}

		usort($result, 'sortAppsByName');

		for( $i= 0 ; $i <sizeof($result)  ; $i++ ){
			$appTemp =$result[$i]['versions'];
			uksort($appTemp, 'sortVersions');
			$result[$i]['versions'] = $appTemp;
		}

			// Trouver l icone dans le fichier

		foreach ($result as $app) {

			$appName = $app['name'];
			$iconPath = 'res/drawable/icon.png';

			$za = new ZipArchive();
			$za->open($appFolder.$app['versions'][array_keys($app['versions'])[0]]);
			$za->extractTo(joinPath($imgFolder,'tmp'), $iconPath);
			$za->close();

			if (!file_exists(joinPath($iconFolder,$appName))) {
				mkdir(joinPath($iconFolder,$appName), 0755, true);
			}

			if (file_exists(joinPath($imgFolder,'tmp/',$iconPath))) {
				copy(joinPath($imgFolder,'tmp/',$iconPath), joinPath($iconFolder,$appName,'/icon.png'));
			}

			if (file_exists($iconFolder.$appName)) {
				rrmdir(joinPath($imgFolder,'tmp/res/'));
			}
		}

		return $result;
	}



	public function rrmdir($dir)
	{
	   if (is_dir($dir)) {
	     $objects = scandir($dir);
	     foreach ($objects as $object) {
	       if ($object != "." && $object != "..") {
	         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
	       }
	     }
	     reset($objects);
	     rmdir($dir);
	}


	public function setExtension($extension)
	{
		$this->extension = $extension;
	}
}


class Ios extends Application
{

	function getInfos($ipaPath, $rootPath=null) // old getApplicationInfo
	{
		$za = new ZipArchive();
		$za->open($ipaPath);

		for ($i=0; $i<$za->numFiles;$i++) {
			$entry = $za->statIndex($i);
			$entryName = $entry['name'];

			if (preg_match('/^Payload\/(.*?)\.app\/config.xml$/i', $entryName)) {
				$xmlPath = "zip://{$ipaPath}#{$entryName}";

				$config = new SimpleXMLElement(file_get_contents($xmlPath));

				$path = $ipaPath;

				if ($rootPath)
					$path = getRelativePath($rootPath, $path);

				return array(
					'id' => (string)$config->attributes()['id'],
					'name' => (string)$config->name,
					'description' => (string)$config->description,
					'versions' => [
						(string)$config->attributes()['version'] => $path
					],
				);
			}
		}

		return null;
	}

}

class Android extends Application
{

	function getInfos($apkPath, $rootPath=null) // old getApkinfo
		{
			$apk = new \ApkParser\Parser($apkPath);
			$manifest = $apk->getManifest();

			$path = $apkPath;

			if ($rootPath)
				$path = getRelativePath($rootPath, $path);

			return [
				'id' => $manifest->getPackageName(),
				'name' => $manifest->getApplication()->getActivityNameList()[0],
				'description' => "",
				'versions' => [
						$manifest->getVersionName() => $path ]
				];
		}

}


class Sort // DONE
{
	private $a;
	private $b;

	function Sort($a, $b)
	{
		$this->a = $a;
		$this->b = $b;
	}


	function byName($a, $b) // old sortAppsByName
	{
		$a['name'] = strtolower($a['name']);
		$b['name'] = strtolower($b['name']);

		if ($a['name'] == $b['name']) {
				return 0;
		}

		return ($a['name'] < $b['name']) ? -1 : 1;
	}


	function byVersions($a, $b) // sortVersions
	{
		return  -1 * version_compare($a, $b); // multiply by -1 to reverse sort order
	}

}


class Path // DONE
{

	function join() // old joinPath, as many arguments as needed
	{
		$args = func_get_args();

		$result = false;

		if (!empty($args))
			$result = preg_replace('/\/$/', '', array_shift($args)); # Remove trailing slash

		if (!empty($args)) {
			$result .= '/';
			$result .= preg_replace('/^\//', '', array_shift($args)); # Remove trailing slash
		}

		if (!empty($args))
			$result = call_user_func_array('joinPath', array_merge([$result],$args)); # recurse with remaining args.

		return $result;
	}

	function getRelativePath($parent, $child)
	{
		$result = substr($child, strlen($parent));
		$result = preg_replace('/^\//', '', $result);
		return $result;
	}


	function getCurrentUrlFolder()
	{
		return preg_replace('/[^\/]*(\?.*)?$/', '', getCurrentUrl());
	}


	private function getCurrentUrl()
	{
		return getCurrentServerAddress().$_SERVER['REQUEST_URI'];
	}


	private function getCurrentServerAddress()
	{
		$protocol = "http";

		if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'Off')
			$protocol .= 's';

		return "{$protocol}://{$_SERVER['SERVER_NAME']}:{$_SERVER['SERVER_PORT']}";
	}


}


class AppsList()
{
	function checkAppAlreadyInList ($appList, $appToTest) // check if App is Already In the List
	{
		foreach ($appList as $key=>$app){
			if (strcmp($app['id'],$appToTest['id']) == 0){
				return $key;
			}
		}
		return -1;
	}
}
