<?php

 $_SERVER['SERVER_NAME'] = 'localhost';
 $_SERVER['SERVER_PORT'] = '9000';
 $_SERVER['REQUEST_URI'] = '/';

require __DIR__.'/../lib/main.php';
require __DIR__.'/../conf/config.php';

toto\Path::join();
$currentUrlFolder = toto\Path::getCurrentUrlFolder();
$rootFolder = $config['appFolder'];

// A OPTI PLUS TARD
$iosApps = new toto\AppList('ipa');
$iosApps->find($rootFolder);
$androidApps = new toto\AppList('apk');
$androidApps->find($rootFolder);

print_r($iosApps);
