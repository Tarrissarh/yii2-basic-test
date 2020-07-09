<?php

namespace app\forms;

use Yii;
use app\models\User;
use app\models\UserRepository;

/**
 * Class AuthForm
 *
 * @package app\forms
 */
class AuthForm extends BaseForm
{
	/** @var string Email */
	public $email;
	
	/** @var string Имя пользователя */
	public $username;
	
	/** @var string Пароль */
	public $password;
	
	/** @var User|null */
	protected $_user;
	
	/** @inheritDoc */
	public function rules()
	{
		return [
			['password', 'required'],
			[
				'email',
				'required',
				'when' => function (AuthForm $form) {
					return empty($form->username);
				},
			],
			[
				'username',
				'required',
				'when' => function (AuthForm $form) {
					return empty($form->email);
				},
			],
			[['email', 'username', 'password'], 'string'],
			['email', 'email'],
			['username', 'string', 'length' => [2, 64]],
			['username', 'match', 'pattern' => '/^[A-z0-9_-]*$/i'],
			['password', 'validatePassword'],
		];
	}
	
	/**
	 * Validates the password.
	 * This method serves as the inline validation for password.
	 *
	 * @param  string  $attribute  the attribute currently being validated
	 * @param  array   $params     the additional name-value pairs given in the rule
	 */
	public function validatePassword($attribute, $params)
	{
		if (!$this->hasErrors()) {
			$user = $this->getUser();
			
			if (!$user || !$user->validatePassword($this->password)) {
				$this->addError($attribute, 'Incorrect username or password.');
			}
		}
	}
	
	/**
	 * @return User|null
	 */
	public function getUser(): ?User
	{
		if (!empty($this->_user)) {
			return $this->_user;
		}
		
		if (!empty($this->email)) {
			$this->_user = UserRepository::getByUsername($this->email);
			
			if ($this->_user === null) {
				$this->addError('email', 'Неверно указан email');
			}
			
			return $this->_user ?? null;
		}
		
		if (!empty($this->username)) {
			$this->_user = UserRepository::getByUsername($this->username);
			
			if ($this->_user === null) {
				$this->addError('username', 'Неверно указан username');
			}
			
			return $this->_user ?? null;
		}
		
		return null;
	}
	
	/**
	 * @return string
	 */
	public function getAuthKey(): string
	{
		return $this->_user->auth_key;
	}
	
	/**
	 * @return bool
	 */
	public function auth(): bool
	{
		if (!$this->validate()) {
			return false;
		}
		
		$user = $this->getUser();
		
		if ($user === null) {
			return false;
		}
		
		return Yii::$app->user->login($user);
	}
}