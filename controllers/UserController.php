<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\rest\ActiveController;
use Swagger\Annotations as SWG;
use yii2mod\swagger\OpenAPIRenderer;
use yii2mod\swagger\SwaggerUIRenderer;
use app\models\User;
use app\actions\ErrorAction;
use app\forms\AuthForm;
use app\actions\ViewAction;
use app\actions\CreateAction;
use app\actions\UpdateAction;
use app\actions\DeleteAction;

/**
 * Class RestController
 *
 * @SWG\Swagger(
 *     basePath="/v1/",
 *     produces={"application/json"},
 *     consumes={"application/x-www-form-urlencoded"},
 *     @SWG\Info(version="1.0", title="Simple API"),
 * )
 *
 * @SWG\SecurityScheme(
 *      securityDefinition="Authorization",
 *      type="apiKey",
 *      description="Bear token for authorization",
 *      name="Authorization",
 *      in="header",
 * )
 *
 * @package app\controllers
 */
class UserController extends ActiveController
{
	public $modelClass = User::class;
	
	/** @inheritDoc */
	public function behaviors()
	{
		return ArrayHelper::merge(
			parent::behaviors(),
			[
				'verbFilter'    => [
					'class'   => VerbFilter::class,
					'actions' => [
						'view'   => ['GET'],
						'create' => ['POST'],
						'update' => ['PUT'],
						'delete' => ['DELETE'],
					],
				],
				'authenticator' => [
					'class'    => HttpBearerAuth::class,
					'optional' => ['*'],
					'except'   => ['auth', 'error', 'swg-api', 'swg-config'],
				],
				'access'        => [
					'class' => AccessControl::class,
					'rules' => [
						[
							'allow'   => true,
							'actions' => ['auth', 'error', 'swg-api', 'swg-config'],
							'roles'   => ['?'],
						],
						[
							'allow'   => true,
							'actions' => ['view', 'create', 'update', 'delete'],
							'roles'   => ['@'],
						],
					],
				],
				'verbs'         => [
					'class'   => VerbFilter::class,
					'actions' => [
						'auth' => ['POST'],
					],
				],
			]
		);
	}
	
	/**
	 * @inheritdoc
	 */
	public function actions()
	{
		return [
			'error'      => ['class' => ErrorAction::class],
			'swg-api'    => [
				'class'   => SwaggerUIRenderer::class,
				'restUrl' => Url::to(['user/swg-config']),
			],
			'swg-config' => [
				'class'   => OpenAPIRenderer::class,
				'cache'   => null,
				'scanDir' => [
					Yii::getAlias('@app/controllers') . '/UserController.php',
					Yii::getAlias('@app/responses'),
				],
			],
			'view'       => [
				'class'       => ViewAction::class,
				'modelClass'  => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
			],
			'create'     => [
				'class'       => CreateAction::class,
				'modelClass'  => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
				'scenario'    => $this->createScenario,
			],
			'update'     => [
				'class'       => UpdateAction::class,
				'modelClass'  => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
				'scenario'    => $this->updateScenario,
			],
			'delete'     => [
				'class'       => DeleteAction::class,
				'modelClass'  => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
			],
		];
	}
	
	/**
	 * @SWG\Post(
	 *     path="/auth/",
	 *     tags={"User"},
	 *     description="Авторизация пользователя",
	 *     produces={"application/json"},
	 *     consumes={"application/x-www-form-urlencoded"},
	 *     @SWG\Parameter(
	 *          name="email",
	 *          in="formData",
	 *          description="Email",
	 *          type="string",
	 *     ),
	 *     @SWG\Parameter(
	 *          name="username",
	 *          in="formData",
	 *          description="Имя пользователя",
	 *          type="string",
	 *     ),
	 *     @SWG\Parameter(
	 *          name="password",
	 *          in="formData",
	 *          description="Пароль",
	 *          type="string",
	 *     ),
	 *     @SWG\Response(
	 *          response=200,
	 *          description="Успешная операция",
	 *          @SWG\Schema(
	 *              @SWG\Property(property="authKey", type="string", description="Ключ авторизации",
	 *                                                example="sU1dPy5X_bAp2adxn5FTiV3POty0XQ1Z"),
	 *          ),
	 *     ),
	 *     @SWG\Response(
	 *          response=422,
	 *          description="Операция с ошибкой",
	 *          @SWG\Schema(
	 *              @SWG\Property(
	 *                  property="errors",
	 *                  type="array",
	 *                  description="Массив ошибок",
	 *                  @SWG\Items(type="string")
	 *              ),
	 *          ),
	 *      ),
	 * )
	 */
	public function actionAuth()
	{
		$data = Yii::$app->request->post();
		$form = new AuthForm();
		
		$form->load($data);
		
		if (!$form->validate()) {
			Yii::$app->getResponse()->setStatusCode(422);
			
			return Json::encode($form->getErrors());
		}
		
		return ['authKey' => $form->getAuthKey()];
	}
	
	/**
	 * @SWG\Get(
	 *     path="/users/{id}/",
	 *     tags={"User"},
	 *     description="Информация о пользователе",
	 *     produces={"application/json"},
	 *     consumes={"application/x-www-form-urlencoded"},
	 *     security={
	 *          {"Authorization":{}}
	 *     },
	 *     @SWG\Parameter(
	 *          name="id",
	 *          description="ID пользователя",
	 *          required=true,
	 *          type="string",
	 *          in="path"
	 *     ),
	 *     @SWG\Response(
	 *          response=200,
	 *          description="Успешная операция",
	 *          @SWG\Schema(
	 *              @SWG\Property(property="id", type="integer", description="ID пользователя", example=1),
	 *              @SWG\Property(property="username", type="string", description="Имя пользователя", example="test1"),
	 *              @SWG\Property(property="email", type="string", description="Email", example="test1@example.com"),
	 *          ),
	 *     ),
	 *     @SWG\Response(
	 *          response=404,
	 *          description="Операция с ошибкой",
	 *          @SWG\Schema(
	 *              @SWG\Property(
	 *                  property="errors",
	 *                  type="array",
	 *                  description="Массив ошибок",
	 *                  @SWG\Items(type="string")
	 *              ),
	 *          ),
	 *      ),
	 * )
	 */
	
	/**
	 * @SWG\Post(
	 *     path="/users/",
	 *     tags={"User"},
	 *     description="Создание нового пользователя",
	 *     produces={"application/json"},
	 *     consumes={"application/x-www-form-urlencoded"},
	 *     security={
	 *          {"Authorization":{}}
	 *     },
	 *     @SWG\Parameter(
	 *          name="email",
	 *          in="formData",
	 *          description="Email",
	 *          type="string",
	 *     ),
	 *     @SWG\Parameter(
	 *          name="username",
	 *          in="formData",
	 *          description="Имя пользователя",
	 *          type="string",
	 *     ),
	 *     @SWG\Parameter(
	 *          name="password",
	 *          in="formData",
	 *          description="Пароль",
	 *          type="string",
	 *     ),
	 *     @SWG\Response(
	 *          response=201,
	 *          description="Успешная операция",
	 *          @SWG\Schema(
	 *              @SWG\Schema(
	 *              @SWG\Property(property="id", type="integer", description="ID пользователя", example=1),
	 *              @SWG\Property(property="username", type="string", description="Имя пользователя", example="test1"),
	 *              @SWG\Property(property="email", type="string", description="Email", example="test1@example.com"),
	 *          ),
	 *          ),
	 *     ),
	 *     @SWG\Response(
	 *          response=422,
	 *          description="Операция с ошибкой",
	 *          @SWG\Schema(
	 *              @SWG\Property(
	 *                  property="errors",
	 *                  type="array",
	 *                  description="Массив ошибок",
	 *                  @SWG\Items(type="string")
	 *              ),
	 *          ),
	 *      ),
	 * )
	 */
	
	/**
	 * @SWG\Put(
	 *     path="/users/{id}/",
	 *     tags={"User"},
	 *     description="Обновление данных пользователя",
	 *     produces={"application/json"},
	 *     consumes={"application/x-www-form-urlencoded"},
	 *     security={
	 *          {"Authorization":{}}
	 *     },
	 *     @SWG\Parameter(
	 *          name="id",
	 *          description="ID пользователя",
	 *          required=true,
	 *          type="string",
	 *          in="path"
	 *     ),
	 *     @SWG\Parameter(
	 *          name="email",
	 *          in="formData",
	 *          description="Email",
	 *          type="string",
	 *     ),
	 *     @SWG\Parameter(
	 *          name="username",
	 *          in="formData",
	 *          description="Имя пользователя",
	 *          type="string",
	 *     ),
	 *     @SWG\Parameter(
	 *          name="password",
	 *          in="formData",
	 *          description="Пароль",
	 *          type="string",
	 *     ),
	 *     @SWG\Response(
	 *          response=201,
	 *          description="Успешная операция",
	 *          @SWG\Schema(
	 *              @SWG\Property(property="id", type="integer", description="ID пользователя", example=1),
	 *              @SWG\Property(property="username", type="string", description="Имя пользователя", example="test1"),
	 *              @SWG\Property(property="email", type="string", description="Email", example="test1@example.com"),
	 *          ),
	 *     ),
	 *     @SWG\Response(
	 *          response=422,
	 *          description="Операция с ошибкой",
	 *          @SWG\Schema(
	 *              @SWG\Property(
	 *                  property="errors",
	 *                  type="array",
	 *                  description="Массив ошибок",
	 *                  @SWG\Items(type="string")
	 *              ),
	 *          ),
	 *      ),
	 * )
	 */
	
	/**
	 * @SWG\Delete(
	 *     path="/users/{id}/",
	 *     tags={"User"},
	 *     description="Удаление (отключение) пользователя",
	 *     produces={"application/json"},
	 *     consumes={"application/x-www-form-urlencoded"},
	 *     security={
	 *          {"Authorization":{}}
	 *     },
	 *     @SWG\Parameter(
	 *          name="id",
	 *          description="ID пользователя",
	 *          required=true,
	 *          type="string",
	 *          in="path"
	 *     ),
	 *     @SWG\Response(
	 *          response=202,
	 *          description="Успешная операция",
	 *     ),
	 *     @SWG\Response(
	 *          response=404,
	 *          description="Операция с ошибкой",
	 *          @SWG\Schema(
	 *              @SWG\Property(
	 *                  property="errors",
	 *                  type="array",
	 *                  description="Массив ошибок",
	 *                  @SWG\Items(type="string")
	 *              ),
	 *          ),
	 *      ),
	 * )
	 */
}