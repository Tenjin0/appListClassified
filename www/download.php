<?php
	require __DIR__.'/../conf/config.php';
	require __DIR__.'/../lib/main.php';

	$rootFolder = $config['appFolder'];

	define('PATH_PARM', 'path');

	if (!array_key_exists(PATH_PARM, $_GET) || !$_GET[PATH_PARM])
		sendNotFound();

	$appPath = $_GET[PATH_PARM];
	$appFullPath = "{$rootFolder}/{$appPath}";

	if (!file_exists($appFullPath))
		sendNotFound();


// ne fonctionne pas depuis un navigateur sur PC/mac... fonctionne uniquement depuis un device android/iOS

	setContentType('application/octet-stream');
	header('Content-Disposition: attachment; filename='.basename($appPath));
	readfile($appFullPath);
