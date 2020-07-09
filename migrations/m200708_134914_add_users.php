<?php

use yii\db\Migration;

/**
 * Class m200708_134914_add_users
 */
class m200708_134914_add_users extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$data      = [];
		$status_id = [10, 11, 12];
		
		for ($i = 1; $i <= 20; $i++) {
			$user = "test{$i}";
			
			$data[] = [
				$user,
				Yii::$app->security->generateRandomString(),
				Yii::$app->security->generatePasswordHash($user),
				Yii::$app->security->generateRandomString(),
				"{$user}@example.com",
				$status_id[random_int(0, 2)],
				time(),
				time(),
			];
		}
		
		$this->batchInsert(
			'{{%user}}',
			[
				'username',
				'auth_key',
				'password_hash',
				'password_reset_token',
				'email',
				'status_id',
				'created_at',
				'updated_at',
			],
			$data
		);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->truncateTable('{{%user}}');
	}
}
