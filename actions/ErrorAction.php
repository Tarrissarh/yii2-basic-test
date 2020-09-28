<?php

namespace app\actions;

use Yii;
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
	 * @return array result content
	 */
	public function run()
	{
		Yii::$app->getResponse()->setStatusCodeByException($this->exception);
		
		return [
			'errors' => [$this->exception->getMessage()],
		];
	}
}