<?php

namespace app\actions;

use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\rest\CreateAction as YiiRestCreateAction;
use yii\web\ServerErrorHttpException;
use app\views\UserView;

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
		
		if (!empty($params['password'])) {
			$params['password_hash'] = Yii::$app->getSecurity()->generatePasswordHash($params['password']);
		}
		
		$params['auth_key']             = Yii::$app->getSecurity()->generateRandomString();
		$params['password_reset_token'] = Yii::$app->getSecurity()->generateRandomString();
		
		$model->setAttributes($params);
		
		if ($model->save()) {
			$response = Yii::$app->getResponse();
			$response->setStatusCode(201);
			$id = implode(',', array_values($model->getPrimaryKey(true)));
			$response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
		} elseif (!$model->hasErrors()) {
			throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
		}
		
		return Json::encode(UserView::render($model));
	}
}