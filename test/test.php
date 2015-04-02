<?php

require __DIR__.'/../lib/main.php';
require __DIR__.'/../conf/config.php';
//require __DIR__.'/apkparser/ApkParser/Parser.php';
Path::join();
$currentUrlFolder = Path::getCurrentUrlFolder();
$rootFolder = $config['appFolder'];
//$apk = new \ApkParser\Parser($apkPath);
// A OPTI PLUS TARD
$iosApps = new AppList('ipa');
$iosApps->find($rootFolder);
$androidApps = new AppList('apk');
$androidApps->find($rootFolder);
