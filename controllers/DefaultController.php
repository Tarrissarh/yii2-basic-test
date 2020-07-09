<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\auth\HttpBearerAuth;
use Swagger\Annotations as SWG;
use yii2mod\swagger\OpenAPIRenderer;
use yii2mod\swagger\SwaggerUIRenderer;
use app\actions\ErrorAction;
use app\forms\AuthForm;
use app\forms\UserForm;
use app\views\UserView;

/**
 * Class DefaultController
 *
 * @SWG\Swagger(
 *     basePath="/",
 *     produces={"application/json"},
 *     consumes={"application/x-www-form-urlencoded"},
 *     @SWG\Info(version="1.0", title="Simple API"),
 * )
 *
 * @package app\controllers
 */
class DefaultController extends Controller
{
	/** @inheritDoc */
	public function behaviors()
	{
		return [
			'corsFilter'    => [
				'class' => Cors::class,
				'cors'  => [
					'Origin'                         => ['*'],
					'Access-Control-Request-Method'  => ['POST', 'GET', 'PUT', 'DELETE'],
					'Access-Control-Request-Headers' => ['*'],
				],
			],
			'authenticator' => [
				'class'    => HttpBearerAuth::class,
				'optional' => ['*'],
				'except'   => ['swg-api', 'swg-config'],
			],
			'access'        => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow'   => true,
						'actions' => ['view', 'create', 'delete', 'update'],
						'roles'   => ['@'],
					],
					[
						'allow'   => true,
						'actions' => ['auth', 'swg-api', 'swg-config', 'error'],
						'roles'   => ['?'],
					],
				],
			],
			'verbs'         => [
				'class'   => VerbFilter::class,
				'actions' => [
					'view'   => ['GET'],
					'create' => ['POST'],
					'auth'   => ['POST'],
					'delete' => ['DELETE'],
					'update' => ['PUT'],
				],
			],
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function actions(): array
	{
		return [
			'swg-api'    => [
				'class'   => SwaggerUIRenderer::class,
				'restUrl' => Url::to(['default/swg-config']),
			],
			'swg-config' => [
				'class'   => OpenAPIRenderer::class,
				'cache'   => null,
				'scanDir' => [
					Yii::getAlias('@app/controllers') . '/DefaultController.php',
					Yii::getAlias('@app/models'),
				],
			],
			'error'      => [
				'class' => ErrorAction::class,
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
			Yii::$app->response->setStatusCode(422);
			
			return Json::encode($form->getErrors());
		}
		
		return Json::encode(['authKey' => $form->getAuthKey()]);
	}
	
	/**
	 * @SWG\Get(
	 *     path="/view/{id}",
	 *     tags={"User"},
	 *     description="Информация о пользователе",
	 *     produces={"application/json"},
	 *     consumes={"application/x-www-form-urlencoded"},
	 *     security={
	 *          {"bearAuth":{}}
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
	public function actionView()
	{
		$data = Yii::$app->request->get();
		$form = new UserForm();
		
		$form->load($data);
		
		$form->setAttributes(['action' => 'view']);
		
		if (!$form->validate()) {
			Yii::$app->response->setStatusCode(404);
			
			return Json::encode($form->getErrors());
		}
		
		return Json::encode(UserView::render($form->getUser()));
	}
	
	/**
	 * @SWG\Post(
	 *     path="/create/",
	 *     tags={"User"},
	 *     description="Создание пользователя",
	 *     produces={"application/json"},
	 *     consumes={"application/x-www-form-urlencoded"},
	 *     security={
	 *          {"bearAuth":{}}
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
	 * @throws \yii\base\Exception
	 */
	public function actionCreate()
	{
		$data = Yii::$app->request->post();
		$form = new UserForm();
		
		$form->load($data);
		
		$form->setAttributes(['action' => 'create']);
		
		if (!$form->create()) {
			Yii::$app->response->setStatusCode(422);
			
			return Json::encode($form->getErrors());
		}
		
		Yii::$app->response->setStatusCode(201);
		
		return Json::encode(UserView::render($form->getUser()));
	}
	
	/**
	 * @SWG\Put(
	 *     path="/update/{id}/",
	 *     tags={"User"},
	 *     description="Создание пользователя",
	 *     produces={"application/json"},
	 *     consumes={"application/x-www-form-urlencoded"},
	 *     security={
	 *          {"bearAuth":{}}
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
	 * @throws \yii\base\Exception
	 */
	public function actionUpdate()
	{
		$data       = Yii::$app->request->post();
		$data['id'] = Yii::$app->request->get('id');
		$form       = new UserForm();
		
		$form->load($data);
		
		$form->setAttributes(['action' => 'update']);
		
		if (!$form->update()) {
			Yii::$app->response->setStatusCode(422);
			
			return Json::encode($form->getErrors());
		}
		
		Yii::$app->response->setStatusCode(201);
		
		return Json::encode(UserView::render($form->getUser()));
	}
	
	/**
	 * @SWG\Delete(
	 *     path="/delete/{id}/",
	 *     tags={"User"},
	 *     description="Удаление пользователя",
	 *     produces={"application/json"},
	 *     consumes={"application/x-www-form-urlencoded"},
	 *     security={
	 *          {"bearAuth":{}}
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
	public function actionDelete()
	{
		$data = Yii::$app->request->get();
		$form = new UserForm();
		
		$form->load($data);
		
		$form->setAttributes(['action' => 'delete']);
		
		if (!$form->delete()) {
			Yii::$app->response->setStatusCode(404);
			
			return Json::encode($form->getErrors());
		}
		
		Yii::$app->response->setStatusCode(202);
		
		return Json::encode([]);
	}
}