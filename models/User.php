<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;

/**
 * Class User
 *
 * @property int    $id
 * @property string $username
 * @property string $email
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property int    $created_at
 * @property int    $updated_at
 *
 * @package app\models
 */
class User extends ActiveRecord implements IdentityInterface
{
	/** @var int Активный */
	public const STATUS_ACTIVE = 10;
	
	/** @var int Не активный */
	public const STATUS_INACTIVE = 0;
	
	/** @var int Заблокированный */
	public const STATUS_BLOCK = 12;
	
	/** @inheritDoc */
	public function behaviors()
	{
		return [
			'timestamp' => [
				'class'      => TimestampBehavior::class,
				'attributes' => [
					ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
					ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
				],
			],
		];
	}
	
	public function rules()
	{
		return [
			[['username', 'email', 'password_hash', 'auth_key'], 'string'],
			['status_id', 'integer'],
			['status_id', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_BLOCK]],
		];
	}
	
	/**
	 * {@inheritdoc}
	 */
	public static function findIdentity($id)
	{
		return UserRepository::getById($id);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public static function findIdentityByAccessToken($token, $type = null)
	{
		$user = UserRepository::getByAuthKey($token);
		
		return $user ?? null;
	}
	
	/**
	 * Finds user by username
	 *
	 * @param  string  $username
	 *
	 * @return static|null
	 */
	public static function findByUsername($username)
	{
		$user = UserRepository::getByUsername($username);
		
		return $user ?? null;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getAuthKey()
	{
		return $this->auth_key;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function validateAuthKey($authKey)
	{
		return $this->auth_key === $authKey;
	}
	
	/**
	 * Validates password
	 *
	 * @param  string  $password  password to validate
	 *
	 * @return bool if password provided is valid for current user
	 */
	public function validatePassword($password)
	{
		return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
	}
}
