<?php

namespace app\responses;

use yii\helpers\ArrayHelper;
use app\models\User;

/**
 * Class UserResponse
 *
 * @package app\responses
 */
class UserResponse
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