<?php
	require __DIR__.'/../conf/config.php';
	require __DIR__.'/../lib/main.php';

	$imageFolder = $config['imageFolder'].'icons/';

	define('PATH_PARM', 'path');

	if (!array_key_exists(PATH_PARM, $_GET) || !$_GET[PATH_PARM])
		sendNotFound();

	$appName = $_GET[PATH_PARM];
	$imgFullPath = "{$imageFolder}{$appName}/icon.png";

	if (!file_exists($imgFullPath))
		sendNotFound();

	setContentType('image/png');
	readfile($imgFullPath);
