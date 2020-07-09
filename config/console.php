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

unset($components['response']);
unset($components['request']);
unset($components['cache']);
unset($components['errorHandler']);
unset($components['urlManager']);

$config = [
	'id'                  => 'basic-console',
	'basePath'            => dirname(__DIR__),
	'bootstrap'           => ['log'],
	'controllerNamespace' => 'app\commands',
	'aliases'             => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
		'@tests' => '@app/tests',
	],
	'components'          => $components,
	'params'              => $params,
];

if (YII_ENV_DEV) {
	// configuration adjustments for 'dev' environment
	$config['bootstrap'][]    = 'gii';
	$config['modules']['gii'] = [
		'class' => 'yii\gii\Module',
	];
}

return $config;
