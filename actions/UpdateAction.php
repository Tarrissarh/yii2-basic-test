<?php

namespace app\actions;

use app\models\UserRepository;
use Yii;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\rest\UpdateAction as YiiRestUpdateAction;
use yii\web\ServerErrorHttpException;
use app\responses\UserResponse;

/**
 * Class UpdateAction
 *
 * @package app\actions
 */
class UpdateAction extends YiiRestUpdateAction
{
	/** @inheritDoc */
	public function run($id)
	{
		/* @var $model ActiveRecord */
		$model = $this->findModel($id);
		
		if ($model === null) {
			Yii::$app->getResponse()->setStatusCode(404);
			
			return ['errors' => ['id' => 'В БД нет такого ID']];
		}
		
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id, $model);
		}
		
		$model->scenario = $this->scenario;
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
		
		unset($params['auth_key'], $params['password_reset_token']);
		
		$model->setAttributes($params);
		
		if (!$model->save()) {
			throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
		}
		
		return UserResponse::render($model);
	}
	
	public function findModel($id)
	{
		if ($this->findModel !== null) {
			return call_user_func($this->findModel, $id, $this);
		}
		
		/* @var $modelClass ActiveRecordInterface */
		$modelClass = $this->modelClass;
		$keys = $modelClass::primaryKey();
		
		if (count($keys) > 1) {
			$values = explode(',', $id);
			
			if (count($keys) === count($values)) {
				$model = $modelClass::findOne(array_combine($keys, $values));
			}
		} elseif ($id !== null) {
			$model = $modelClass::findOne($id);
		}
		
		if (isset($model)) {
			return $model;
		}
		
		return null;
	}
}