<?php

namespace app\actions;

use Yii;
use yii\helpers\Json;
use yii\web\ErrorAction as YiiErrorAction;

/**
 * Class ErrorAction
 *
 * @package actions
 */
class ErrorAction extends YiiErrorAction
{
	/**
	 * Runs the action.
	 *
	 * @return string result content
	 */
	public function run()
	{
		Yii::$app->getResponse()->setStatusCodeByException($this->exception);
		
		return Json::encode(
			[
				'errors' => [$this->exception->getMessage()],
			]
		);
	}
}