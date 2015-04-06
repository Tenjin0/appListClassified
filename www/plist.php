<?php
	require __DIR__.'/../conf/config.php';
	require __DIR__.'/../lib/main.php';

	$rootFolder = $config['appFolder'];

	define('PATH_PARM', 'path');

	if (!array_key_exists(PATH_PARM, $_GET) || !$_GET[PATH_PARM])
		Web::sendNotFound();

	$appPath = $_GET[PATH_PARM];
	$appFullPath = "{$rootFolder}/{$appPath}";

	if (!file_exists($appFullPath))
		Web::sendNotFound();

	$app = getApplicationInfo($appFullPath, $rootFolder);

	$currentUrlFolder = Path::getCurrentUrlFolder();

	$id = $app->getId();

	$version = array_keys($app->getVersions())[0];

	$description = $app->getDescription();
	$name = $app->getName();

	setContentType('text/xml');

?><?= '<?xml version="1.0" encoding="UTF-8"?>' ?><?php // Put xml declaration in a string to avoid short open tag conflict. ?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>items</key>
	<array>
		<dict>
			<key>assets</key>
			<array>
				<dict>
					<key>kind</key>
					<string>software-package</string>
					<key>url</key>
					<string><?= $currentUrlFolder ?>download.php?path=<?= $appPath ?></string>
				</dict>
				<dict>
					<key>kind</key>
					<string>full-size-image</string>
					<key>needs-shine</key>
					<false/>
					<key>url</key>
					<string><?= $currentUrlFolder ?>images/download512.png</string>
				</dict>
				<dict>
					<key>kind</key>
					<string>display-image</string>
					<key>needs-shine</key>
					<false/>
					<key>url</key>
					<string><?= $currentUrlFolder ?>images/download57.png</string>
				</dict>
			</array>
			<key>metadata</key>
			<dict>
				<key>bundle-identifier</key>
				<string><?= $id ?>.<?= time() ?></string><!-- Adding timestamp as a workaround for ios8 deployment bug. -->
				<key>bundle-version</key>
				<string><?= $version ?></string>
				<key>kind</key>
				<string>software</string>
				<key>subtitle</key>
				<string><?= $description ?></string>
				<key>title</key>
				<string><?= $name ?></string>
			</dict>
		</dict>
	</array>
</dict>
</plist>
