<?php

namespace app\models;

/**
 * Class UserRepository
 *
 * @package app\models
 */
class UserRepository
{
	/**
	 * @param  int       $id
	 * @param  int|null  $status_id
	 *
	 * @return \app\models\User|array|\yii\db\ActiveRecord|null
	 */
	public static function getById(int $id, ?int $status_id = null)
	{
		return User::find()->where(['id' => $id])->andFilterWhere(['status_id' => $status_id])->one();
	}
	
	/**
	 * @param  string  $authKey
	 *
	 * @return \app\models\User|null
	 */
	public static function getByAuthKey(string $authKey): ?User
	{
		return User::findOne(['auth_key' => $authKey]);
	}
	
	/**
	 * @param  string  $username
	 *
	 * @return \app\models\User|null
	 */
	public static function getByUsername(string $username): ?User
	{
		return User::findOne(['username' => $username]);
	}
}