<?php

use yii\web\JsonResponseFormatter;
use yii\rest\UrlRule;
use yii\web\UrlManager;
use yii\db\Connection;
use app\models\User;
use yii\caching\FileCache;
use yii\log\FileTarget;
use yii\swiftmailer\Mailer;
use yii\web\Response;

return [
	'response'     => [
		'class'      => Response::class,
		'formatters' => [
			Response::FORMAT_JSON => [
				'class'         => JsonResponseFormatter::class,
				'prettyPrint'   => YII_DEBUG, // используем "pretty" в режиме отладки
				'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
			],
		],
	],
	'request'      => [
		'enableCookieValidation' => false,
		'enableCsrfValidation'   => false,
	],
	'cache'        => [
		'class' => FileCache::class,
	],
	'user'         => [
		'class'           => \yii\web\User::class,
		'identityClass'   => User::class,
		'enableAutoLogin' => false,
		'enableSession'   => false,
		'loginUrl'        => ['auth'],
	],
	'mailer'       => [
		'class'            => Mailer::class,
		'useFileTransport' => true,
	],
	'log'          => [
		'traceLevel' => YII_DEBUG ? 3 : 0,
		'targets'    => [
			[
				'class'  => FileTarget::class,
				'levels' => ['error', 'warning'],
			],
		],
	],
	'db'           => [
		'class'   => Connection::class,
		'charset' => 'utf8',
	],
	'urlManager'   => [
		'class'               => UrlManager::class,
		'enablePrettyUrl'     => true,
		'showScriptName'      => false,
		'enableStrictParsing' => true,
		'suffix'              => '/',
		'rules'               => [
			// Правила для rest варианта
			/*''           => 'rest/swg-api',
			'swg-config' => 'rest/swg-config',
			
			['class' => UrlRule::class, 'controller' => 'rest'],
			
			'/<action>'           => '/rest/<action>',
			'/<action>/'          => '/rest/<action>',
			'/<action>/<id:\d+>'  => '/rest/<action>',
			'/<action>/<id:\d+>/' => '/rest/<action>',*/
			
			// Правила для кастомного варианта
			''           => 'default/swg-api',
			'swg-config' => 'default/swg-config',
			
			'/default/error'             => '/default/error',
			'/default/<action>'          => '/default/<action>',
			'/default/<action>/<id:\d+>' => '/default/<action>',
			
			'/<action>'          => '/default/<action>',
			'/<action>/<id:\d+>' => '/default/<action>',
			
			// Общее для всех правило
			'/<controller>/<action>'          => '/<controller>/<action>',
			'/<controller>/<action>/<id:\d+>' => '/<controller>/<action>',
		],
	],
	'errorHandler' => [
		//'errorAction' => 'rest/error',
		'errorAction' => 'default/error',
	],
];