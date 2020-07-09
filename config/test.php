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

/**
 * Application configuration shared by all test types
 */
return [
	'id'         => 'basic-tests',
	'basePath'   => dirname(__DIR__),
	'aliases'    => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
	],
	'language'   => 'en-US',
	'components' => $components,
	'params'     => $params,
];
