<?php

namespace app\forms;

use yii\base\Model;

/**
 * Class BaseForm
 *
 * @package app\forms
 */
abstract class BaseForm extends Model
{
	/** @inheritDoc */
	public function formName()
	{
		return '';
	}
}