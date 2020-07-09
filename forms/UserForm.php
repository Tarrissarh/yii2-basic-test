<?php

namespace app\forms;

use app\models\User;
use app\models\UserRepository;
use Yii;

/**
 * Class UserForm
 *
 * @package app\forms
 */
class UserForm extends BaseForm
{
	/** @var int ID пользователя */
	public $id;
	
	/** @var string Имя пользователя */
	public $username;
	
	/** @var string Email */
	public $email;
	
	/** @var string Пароль */
	public $password;
	
	/** @var string Производимое действие */
	public $action;
	
	/** @var User */
	protected $_user;
	
	/** @inheritDoc */
	public function rules()
	{
		return [
			['action', 'required'],
			['action', 'string'],
			[
				'action',
				'in',
				'range' => [
					'view',
					'create',
					'update',
					'delete',
				],
			],
			[
				'id',
				'required',
				'when' => static function (UserForm $form) {
					return in_array(
						$form->action,
						[
							'view',
							'update',
							'delete',
						],
						false
					);
				},
			],
			['id', 'integer'],
			[
				['email', 'username', 'password'],
				'required',
				'when' => static function (UserForm $form) {
					return empty($form->id);
				},
			],
			[['username', 'email', 'password'], 'string'],
			['email', 'email'],
			['username', 'string', 'length' => [2, 64]],
			['username', 'match', 'pattern' => '/^[A-z0-9_-]*$/i'],
		];
	}
	
	/**
	 * @return User|null
	 */
	public function getUser(): ?User
	{
		if (!empty($this->_user)) {
			return $this->_user;
		}
		
		if (!empty($this->id)) {
			$this->_user = UserRepository::getById($this->id, 10);
			
			return $this->_user ?? null;
		}
		
		if (!empty($this->email)) {
			$this->_user = UserRepository::getByUsername($this->email);
			
			if ($this->_user !== null) {
				$this->addError('email', 'Пользователь с таким email уже существует');
			}
			
			return $this->_user ?? null;
		}
		
		if (!empty($this->username)) {
			$this->_user = UserRepository::getByUsername($this->username);
			
			if ($this->_user !== null) {
				$this->addError('username', 'Пользователь с таким username уже существует');
			}
			
			return $this->_user ?? null;
		}
		
		return null;
	}
	
	/**
	 * Создание пользователя
	 *
	 * @return bool
	 * @throws \yii\base\Exception
	 */
	public function create(): bool
	{
		if (!$this->validate()) {
			return false;
		}
		
		if ($this->getUser() !== null) {
			$this->clearErrors();
			$this->addError('common', 'Пользователь уже существует');
			
			return false;
		}
		
		$user = new User();
		
		$user->setAttributes(
			[
				'username'             => $this->username,
				'email'                => $this->email,
				'password_hash'        => Yii::$app->security->generatePasswordHash($this->password),
				'status_id'            => User::STATUS_ACTIVE,
				'auth_key'             => Yii::$app->security->generateRandomString(),
				'password_reset_token' => Yii::$app->security->generateRandomString(),
			]
		);
		
		if (!$user->save()) {
			$this->addErrors($user->getErrors());
			
			return false;
		}
		
		$this->_user = $user;
		
		return true;
	}
	
	/**
	 * Обновление данных пользователя
	 *
	 * @return bool
	 * @throws \yii\base\Exception
	 */
	public function update(): bool
	{
		if (!$this->validate()) {
			return false;
		}
		
		$user = $this->getUser();
		
		if ($user === null) {
			$this->clearErrors();
			$this->addError('common', 'Пользователь не найден');
			
			return false;
		}
		
		$params = [];
		
		if (!empty($this->username)) {
			$params['username'] = $this->username;
		}
		
		if (!empty($this->email)) {
			$params['email'] = $this->email;
		}
		
		if (!empty($this->password)) {
			$params['password_hash'] = Yii::$app->security->generatePasswordHash($this->password);
		}
		
		if (empty($params)) {
			return true;
		}
		
		$user->setAttributes($params);
		
		if (!$user->save()) {
			$this->addErrors($user->getErrors());
			
			return false;
		}
		
		$this->_user = $user;
		
		return true;
	}
	
	/**
	 * Удаление пользователя
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		if (!$this->validate()) {
			return false;
		}
		
		if ($this->getUser() === null) {
			$this->clearErrors();
			$this->addError('common', 'Пользователь не найден');
			
			return true;
		}
		
		$this->_user->setAttribute('status_id', User::STATUS_INACTIVE);
		
		if (!$this->_user->save()) {
			$this->addErrors($this->_user->getErrors());
			
			return false;
		}
		
		return true;
	}
}