<?php

namespace app\actions;

use app\models\User;
use Yii;
use yii\db\ActiveRecordInterface;
use yii\helpers\Json;
use yii\rest\DeleteAction as YiiRestDeleteAction;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

/**
 * Class DeleteAction
 *
 * @package app\actions
 */
class DeleteAction extends YiiRestDeleteAction
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
		
		$model->setAttribute('status_id', User::STATUS_INACTIVE);
		
		if (!$model->save()) {
			throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
		}
		
		Yii::$app->getResponse()->setStatusCode(202);
		
		return [];
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