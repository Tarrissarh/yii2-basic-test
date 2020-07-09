<?php

namespace app\actions;

use yii\helpers\Json;
use yii\rest\ViewAction as YiiRestViewAction;
use app\views\UserView;

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
		
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id, $model);
		}
		
		return Json::encode(UserView::render($model));
	}
}