<?php

use yii\web\JsonResponseFormatter;
use yii\rest\UrlRule;
use yii\web\UrlManager;
use yii\db\Connection;
use yii\caching\FileCache;
use yii\log\FileTarget;
use yii\swiftmailer\Mailer;
use yii\web\Response;
use app\models\User;

return [
	'response'     => [
		'class'         => Response::class,
		'on beforeSend' => static function($event) {
			/** @var Response $response */
			$response = $event->sender;
			
			$request = Yii::$app->getRequest();
			
			// Для ajax запроса или методов дебага устанавливаем ответ json
			if ($request->isAjax && strpos($request->getUrl(), 'debug') === false) {
				$response->format     = Response::FORMAT_JSON;
				$response->formatters = [
					Response::FORMAT_JSON => [
						'class'         => JsonResponseFormatter::class,
						'prettyPrint'   => YII_DEBUG, // используем "pretty" в режиме отладки
						'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
					],
				];
			} else {
				// Для swagger выводим данные
				if (in_array($request->getUrl(), ['/', '/swg-config/'], false)) {
					echo $response->data;
					exit;
				}
				
				// В остальных случаях возвращаем данные
				return $response->data;
			}
		},
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
			''           => 'user/swg-api',
			'swg-config' => 'user/swg-config',
			
			// Правила для rest варианта
			'/v1/auth/'  => '/user/auth',
			
			[
				'class'      => UrlRule::class,
				'prefix'     => 'v1',
				'controller' => 'user',
			],
		],
	],
	'errorHandler' => [
		'errorAction' => 'user/error',
	],
];