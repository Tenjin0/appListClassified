<?php

	require __DIR__.'/../conf/config.php';
	require __DIR__.'/../lib/main.php';
	require __DIR__.'/../lib/autoload.php';
	$currentUrlFolder = Path::getCurrentUrlFolder();
	$rootFolder = $config['appFolder'];

	// A OPTI PLUS TARD
	$iosApps = new AppList('ipa');
	$iosApps->find($rootFolder);
	$androidApps = new AppList('apk');
	$androidApps->find($rootFolder);
	$numberDisplayedVersions = 9; //how many old versions to display
	if (!$iosApps || !$androidApps) {
		echo "Could not list files in folder: ".$rootFolder;
		die();
	}

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Applications Coqs en Pâte</title>

	<!--iOS-->
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
		<meta name="apple-mobile-web-app-title" content="AppList">

		<link rel="apple-touch-startup-image" href="images/icon-152.png">
		<link rel="apple-touch-icon" href="images/icon-60.png">
		<link rel="apple-touch-icon" sizes="76x76" href="images/icon-76.png">
		<link rel="apple-touch-icon" sizes="120x120" href="images/icon-120.png">
		<link rel="apple-touch-icon" sizes="152x152" href="images/icon-152.png">

	<!--android-->
		<meta name="mobile-web-app-capable" content="yes">

	<!--General-->
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/fontAwesome/css/font-awesome.min.css" rel="stylesheet">
		<link href="css/main.css" rel="stylesheet">

		<script>

			function hide(el) {
				$(el).hide();
			}

		</script>

	</head>

	<body>
	<!-- Barre de navigation -->

		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container">
				<span class="navbar-brand">Coqs en Pâte</span>
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>

				</div> <!--class="navbar-header"-->


				<div id="navbar" class="navbar-collapse collapse" role="tabpanel">
					<ul id="myTab" class="nav navbar-nav" role="tablist">
						<li role="presentation" class="active">
							<a href="#"  data-target="#iOS" role="tab" data-toggle="tab">iOS</a>
						</li>
						<li role="presentation">
							<a href="#"  data-target="#android" role="tab" data-toggle="tab">Android</a>
						</li>
						</ul>
						<ul class="nav navbar-nav navbar-right">
							<li>
								<a class="fa fa-refresh refreshButton" onclick="window.location.reload()"></a>
							</li>
					</ul>
				</div><!--class="navbar-collapse collapse"-->
			</div><!--class="container"-->
		</nav>

		<!-- Contenu de la page -->

		<div class="tab-content">
			<div id="iOS" class="tab-pane active" role="tabpanel" >
				<?php if (!empty($iosApps->getApps())): ?>
					<ul class="list-group">

<!-- iOS - Derniere version disponible -->

						<?php foreach ($iosApps->getApps() as $app): ?>
						<?php $friendlyId = 'ipa_'.preg_replace('/\./', '_', $app['id']) ?>
							<li class="list-group-item app-entry">
								<div class="container">
									<span class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#<?= $friendlyId ?>"></span>

									<!--icone de l appli-->

									<div class="wrapper">
										<img  class='appIcon' src="<?= $currentUrlFolder.'display.php?path='.$app['name'] ?>" onerror="hide(this)"></img>
									</div>

									<!--nom de l appli-->
									<span class="name"><?= $app['name'] ?></span> <span class="versionTxt">v<?= array_keys($app['versions'])[0] ?></span>
									<a class="pull-right" href="itms-services://?action=download-manifest&amp;url=<?= urlencode($currentUrlFolder.'plist.php?path='.$app['versions'][array_keys($app['versions'])[0]]) ?>"><span class="fa fa-download"></span></a>
								</div>
							</li>


<!-- iOS - Versions disponible dans Archive-->

							<div id="<?= $friendlyId ?>" class="panel-collapse collapse">
							      <div class="panel-body">
							        	<ul>
								        	<?php

								        	if(count($app['versions']) < 2 )
								        	{
						        			?>
												<div class="container">
							        				<li class=" default oldVersions app-entry">Il n'y a pas d'autres versions</li>
												</div>
						        				<?php
								        	}
								        	else{
								        	  ?>
								        		<?php foreach(array_slice($app['versions'],1,$numberDisplayedVersions) as $key => $value){?>

													<div class="container">
														<li class="oldVersions app-entry">
										        			<span class="name"><?= $app['name'] ?> </span> <span class="versionTxtOld">v<?= $key ?></span>
										        			<a class="pull-right" href="itms-services://?action=download-manifest&amp;url=<?= urlencode($currentUrlFolder().'plist.php?path='.$value) ?>">
										        			<span class="oldDownloads fa fa-download"></span></a>
										        		</li>
													</div>
												<?php } }?>
							        	</ul>
							      </div>
						    </div>
						<?php endforeach ?>
					</ul>
				<?php endif ?>
			</div> <!--iOS-->

			<div id="android" class="tab-pane"  role="tabpanel" >
				<?php if (!empty($androidApps)): ?>
					<ul class="list-group">
						<?php foreach ($androidApps as $app): ?>
						<?php $friendlyId = 'apk_'.preg_replace('/\./', '_', $app['id']) ?>

<!-- Android - Derniere version disponible -->

							<li class="list-group-item app-entry">
								<div class="container">
									<span class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#<?= $friendlyId ?>"></span>

									<!--icone de l appli-->
									<div class="wrapper">
										<img class='appIcon' src="<?= $currentUrlFolder().'display.php?path='.$app['name'] ?>" onerror="hide(this)"></img>
									</div>

								<!--nom de l appli-->
									<span class="name"><?= $app['name'] ?></span> <span class="versionTxt">v<?= array_keys($app['versions'])[0] ?></span>
									<a class="pull-right" href="download.php?path=<?=$app['versions'][array_keys($app['versions'])[0]] ?>"><span class="fa fa-download"></span></a>

								</div>
							</li>

<!-- Android - Versions disponible dans Archive-->

							<div id="<?= $friendlyId ?>" class="panel-collapse collapse">
							      <div class="panel-body">
							        	<ul>
								        	<?php $arrayKeys = array_keys($app['versions']);


								        	if(count($arrayKeys) < 2 )
								        	{
						        			?>
												<div class="container">
							        				<li class=" default oldVersions app-entry">Il n'y a pas d'autres versions</li>
												</div>
						        				<?php
								        	}
								        	else
								        	{
								        		foreach(array_slice($app['versions'],1, $numberDisplayedVersions) as $key => $value)?>
													<div class="container">
														<li class="oldVersions app-entry">
										        			<span class=" name"><?= $app['name'] ?> </span> <span class="versionTxtOld">v<?= $key ?></span>
										        			<a class="pull-right" href="download.php?path=<?=$value?>"><span class="oldDownloads fa fa-download"></span></a>
										        		</li>
													</div>
												<?php }  ?>
							        	</ul>
							      </div>
						    </div>
						<?php endforeach ?>
					</ul>
				<?php endif ?>
			</div> <!--android-->

		</div>


	<!-- Scripts -->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.min.js"></script>
		<script src='js/fastclick.js'></script>
		<script >
			$(function() {
			    FastClick.attach(document.body);
			});
		</script>
	</body>
</html>
