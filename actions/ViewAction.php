<?php

namespace app\actions;

use Yii;
use yii\db\ActiveRecordInterface;
use yii\rest\ViewAction as YiiRestViewAction;
use app\responses\UserResponse;

/**
 * Class ViewAction
 *
 * @package app\actions
 */
class ViewAction extends YiiRestViewAction
{
	/** @inheritDoc */
	public function run($id)
	{
		$model = $this->findModel($id);
		
		if ($model === null) {
			Yii::$app->getResponse()->setStatusCode(404);
			
			return ['errors' => ['id' => 'В БД нет такого ID']];
		}
		
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id, $model);
		}
		
		return UserResponse::render($model);
	}
	
	/** @inheritDoc */
	public function findModel($id)
	{
		if ($this->findModel !== null) {
			return call_user_func($this->findModel, $id, $this);
		}
		
		/* @var $modelClass ActiveRecordInterface */
		$modelClass = $this->modelClass;
		$keys       = $modelClass::primaryKey();
		
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