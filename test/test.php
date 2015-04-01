<?php

require __DIR__.'/../lib/main.php';
require __DIR__.'/../conf/config.php';

Path::join();
$currentUrlFolder = Path::getCurrentUrlFolder();
$rootFolder = $config['appFolder'];

// A OPTI PLUS TARD
$iosApps = new AppList('ipa');
$iosApps->find($rootFolder);
$androidApps = new AppList('apk');
$androidApps->find($rootFolder);
