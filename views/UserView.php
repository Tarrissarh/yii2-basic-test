<?php

namespace app\views;

use yii\helpers\ArrayHelper;
use app\models\User;

/**
 * Class UserView
 *
 * @package app\views
 */
class UserView
{
	/**
	 * @param  User|null|array|\yii\db\ActiveRecord|\yii\db\ActiveRecordInterface  $object
	 *
	 * @return array
	 */
	public static function render($object)
	{
		if (empty($object)) {
			return $object;
		}
		
		$properties = [
			User::class => [
				'id',
				'username',
				'email',
			],
		];
		
		return ArrayHelper::toArray($object, $properties);
	}
}