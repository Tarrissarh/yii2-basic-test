<?php

use yii\helpers\ArrayHelper;

$localParamsPath = __DIR__ . '/params-local.php';
$params          = [];

if (file_exists($localParamsPath)) {
	$params = require $localParamsPath;
}

$params = ArrayHelper::merge(
	require __DIR__ . '/params.php',
	$params
);

$localComponentsPath = __DIR__ . '/components-local.php';
$components          = [];

if (file_exists($localComponentsPath)) {
	$components = require $localComponentsPath;
}

$components = ArrayHelper::merge(
	require __DIR__ . '/components.php',
	$components
);

$config = [
	'id'         => 'basic',
	'basePath'   => dirname(__DIR__),
	'bootstrap'  => ['log'],
	'aliases'    => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
	],
	'components' => $components,
	'params'     => $params,
];

if (YII_ENV_DEV) {
	// configuration adjustments for 'dev' environment
	$config['bootstrap'][]      = 'debug';
	$config['modules']['debug'] = [
		'class' => 'yii\debug\Module',
		// uncomment the following to add your IP if you are not connecting from localhost.
		//'allowedIPs' => ['127.0.0.1', '::1'],
	];
	
	$config['bootstrap'][]    = 'gii';
	$config['modules']['gii'] = [
		'class' => 'yii\gii\Module',
		// uncomment the following to add your IP if you are not connecting from localhost.
		//'allowedIPs' => ['127.0.0.1', '::1'],
	];
}

return $config;
