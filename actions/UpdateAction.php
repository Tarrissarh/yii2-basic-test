<?php

namespace app\actions;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\rest\UpdateAction as YiiRestUpdateAction;
use yii\web\ServerErrorHttpException;
use app\views\UserView;

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
		
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id, $model);
		}
		
		$model->scenario = $this->scenario;
		$params = Yii::$app->getRequest()->getBodyParams();
		
		if (!empty($params['password'])) {
			$params['password_hash'] = Yii::$app->getSecurity()->generatePasswordHash($params['password']);
		}
		
		$model->setAttributes($params);
		
		if (!$model->save()) {
			throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
		}
		
		return Json::encode(UserView::render($model));
	}
}