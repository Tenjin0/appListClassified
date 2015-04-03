<?php

 $_SERVER['SERVER_NAME'] = 'localhost';
 $_SERVER['SERVER_PORT'] = '9000';
 $_SERVER['REQUEST_URI'] = '/';

require __DIR__.'/../lib/main.php';
require __DIR__.'/../conf/config.php';
require __DIR__.'/../lib/autoload.php';

$currentUrlFolder = application\Path::getCurrentUrlFolder();
$rootFolder = $config['appFolder'];

// A OPTI PLUS TARD
$iosApps = new application\AppList('ipa');
$iosApps->find($rootFolder);
$iosApps = $iosApps->getApps(); // a virer ou a remettre ligne 96
foreach ($iosApps as $app){
	foreach(array_slice($app->getVersions(),1,9) as $key => $value){
		echo "itms-services://?action=download-manifest&amp;url= ".urlencode($currentUrlFolder.'plist.php?path='.$value)." \n";
	}
}
$androidApps = new application\AppList('apk');
$androidApps->find($rootFolder);
$androidApps = $androidApps->getApps();

// if (!empty($iosApps)){
// 	print_r($iosApps);
// }
// if (!empty($androidApps)){
// 	print_r($androidApps);
// }

