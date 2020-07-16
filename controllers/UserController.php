<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\rest\ActiveController;
use app\models\User;
use app\actions\ErrorAction;
use app\forms\AuthForm;

/**
 * Class RestController
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
				'authenticator' => [
					'class'    => HttpBearerAuth::class,
					'optional' => ['*'],
					'except'   => ['auth', 'error'],
				],
				'access'        => [
					'class' => AccessControl::class,
					'rules' => [
						[
							'allow'   => true,
							'actions' => ['auth', 'error'],
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
			'error'  => ['class' => ErrorAction::class],
			'view'   => [
				'class'       => 'app\actions\ViewAction',
				'modelClass'  => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
			],
			'create' => [
				'class'       => 'app\actions\CreateAction',
				'modelClass'  => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
				'scenario'    => $this->createScenario,
			],
			'update' => [
				'class'       => 'app\actions\UpdateAction',
				'modelClass'  => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
				'scenario'    => $this->updateScenario,
			],
			'delete' => [
				'class'       => 'app\actions\DeleteAction',
				'modelClass'  => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
			],
		];
	}
	
	/**
	 * Авторизация
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
}