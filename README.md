# Deploy
### Применение миграций
#### создание таблицы пользователей и добавление тестовых данных:
``` 
php yii migrate
```

### Настройка конфигурации:
Создать файлы component-local.php, котором указываем название хост, БД, логин и пароль для доступа к БД:
```php
<?php

return [
	'db' => [
		'dsn'      => (YII_ENV_DEV || YII_ENV_PROD) ? 'mysql:host=localhost;dbname=yii2basic' : 'mysql:host=localhost;dbname=yii2_basic_tests',
		'username' => '',
		'password' => '',
	]
];
```
и params-local.php в app\config:
```php
<?php

return [];
```

В component.php можно использовать два варианта:
1. Кастомный вариант
2. Через Rest ActiveController Yii2

Для этого нужно в urlManager в rules закомментировать одну из частей (rest закомментирована по умолчанию)