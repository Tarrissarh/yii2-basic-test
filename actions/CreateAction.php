<?php

namespace app\actions;

use Yii;
use yii\rest\CreateAction as YiiRestCreateAction;
use yii\web\ServerErrorHttpException;
use app\responses\UserResponse;
use app\models\UserRepository;

/**
 * Class CreateAction
 *
 * @package app\actions
 */
class CreateAction extends YiiRestCreateAction
{
	/** @inheritDoc */
	public function run()
	{
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id);
		}
		
		/* @var $model \yii\db\ActiveRecord */
		$model = new $this->modelClass(
			[
				'scenario' => $this->scenario,
			]
		);
		
		$params = Yii::$app->getRequest()->getBodyParams();
		
		if (!empty($params['email'])) {
			$user = UserRepository::getByEmail($params['email']);
			
			if ($user !== null) {
				Yii::$app->getResponse()->setStatusCode(422);
				
				return ['errors' => ['email' => 'Данный email занят']];
			}
		}
		
		if (!empty($this->username)) {
			$user = UserRepository::getByUsername($this->username);
			
			if ($user !== null) {
				Yii::$app->getResponse()->setStatusCode(422);
				
				return ['errors' => ['username' => 'Данный username занят']];
			}
		}
		
		if (!empty($params['password'])) {
			$params['password_hash'] = Yii::$app->getSecurity()->generatePasswordHash($params['password']);
		}
		
		$params['auth_key']             = Yii::$app->getSecurity()->generateRandomString();
		$params['password_reset_token'] = Yii::$app->getSecurity()->generateRandomString();
		
		$model->setAttributes($params);
		
		if ($model->save()) {
			$response = Yii::$app->getResponse();
			$response->setStatusCode(201);
		} elseif (!$model->hasErrors()) {
			throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
		}
		
		return UserResponse::render($model);
	}
}