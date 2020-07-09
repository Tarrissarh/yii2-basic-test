# Задача
# ТЕСТОВОЕ ЗАДАНИЕ ОТ BCS

## Задача: Реализовать REST API сервис.

### Технологический стек: Yii2, MySQL, PHP, Composer, GitHub

###### Ожидаемый результат: Код рабочего сервиса залитый в github с инструкцией по развертыванию в файле README.md

###### Структура БД - стандартная Yii2 миграция для создания таблицы пользователей
https://github.com/yiisoft/yii2-app-advanced/blob/master/console/migrations/m130524_201442_init.php
https://github.com/yiisoft/yii2-app-advanced/blob/master/console/migrations/m190124_110200_add_verification_token_column_to_user_table.php

плюс написать свою миграцию с инсертом тестовых пользователей.

API Authorize - **bearer**

Токен в БД - **user.auth_key**

Все ответы в формате - **Json**

Все body request - **x-www-form-urlencoded**

### API endpoints:

#### GET /v1/user/{id} 
Выводить данные из таблицы user в формате JSON. Выводить только: id, username, email. И только с статусом 10

`SQL: SELECT * FROM user WHERE status = 10 AND id = {id}`

Варианты ответа

Успешное выполнение

Статус: **200**
```javascript
{
  id:1,
  username:'Test1',
  email:'test@test.bcs'
}
```
Ошибка - в БД нет такого id с статусом 10

Статус: **404**

(ошибка в формате json)

#### POST /v1/user
Вставлять в таблицу user новую строку с данными.

Пароль проверить на длину минимум 6 символов.

Пароль шифровать стандартными методами Yii2.

auth_key - генерировать 32 символа

email - стандартная валидация

username - от 2 до 64 символов, без пробелов и спецсимволов. [A-z0-9_-]

created_at, updated_at - стандартные Yii2 behaviors

Body request example:
```javascript
username:Test1
password:123456
email:test@test.bcs
```

Успешное выполнение

Статус: **201**

Response data (JSON):

```javascript
{
  id:1,
  username:'Test1',
  email:'test@test.bcs'
}
```
Ошибка валидации

Статус: **422**

(ошибка в формате json)


#### DELETE /v1/user/{id}
Удаляет пользователя (Устанавливает статус 0)

`SQL: UPDATE user SET status = 0 WHERE id = {id}`

Успешное выполнение
Статус: **202**

Ошибка - в БД нет такого id

Статус: **404**

(ошибка в формате json)

#### PUT /v1/user/{id}
Обновляет данные пользователя.

Если передан пароль - заменять пароль.

Если пароль не передан - оставлять старый

Данные которые можно изменять в этом методе:

username, email, password.

created_at, updated_at - стандартные Yii2 behaviors

Body request example:
```javascript
username:Test2
email:test3@test.bcs
```
Успешное выполнение

Статус: **200**

```javascript
Response data (JSON):
{
  id:1,
  username:'Test1',
  email:'test@test.bcs'
}
```
Ошибка валидации

Статус: **422**

(ошибка в формате json)


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