<?php

namespace app\actions;

use app\models\User;
use Yii;
use yii\helpers\Json;
use yii\rest\DeleteAction as YiiRestDeleteAction;
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
		
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id, $model);
		}
		
		$model->setAttribute('status_id', User::STATUS_INACTIVE);
		
		if (!$model->save()) {
			throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
		}
		
		Yii::$app->getResponse()->setStatusCode(204);
		
		return Json::encode([]);
	}
}