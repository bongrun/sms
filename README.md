sms
========
[![PHP version](https://badge.fury.io/ph/bongrun%2Fsms.svg)](https://badge.fury.io/ph/bongrun%2Fsms)

Приём смс сообщений

Компонент позволяет объединить несколько сервисов по приёму смс сообщений.

Сервисы
-----------
На данные момент разработано api для сервисов
* [Sim Sms](http://simsms.org)
* [Sms Activate](http://sms-activate.ru)
* [Sms-Area](http://sms-area.org/signup.php?referrer=NjE4Mjk=)
* [Sms-Reg](http://sms-reg.com)

Особенности
------------
* Сразу несколько сервисов по приёму смс сообщений
* Лёгкая возможность добавить пользовательский сервис
* Анализ на каком из сервисов есть доступные номера
* Выбор самого выгодного сервиса для определённого сайта

Установка
------------
Предпочтительный способ установить это расширение через [composer](http://getcomposer.org/download/).

Либо запустить

```
composer require --prefer-dist bongrun/sms "*"
```

или добавить

```
"bongrun/sms": "*"
```

в файл `composer.json`.

Конфигурация
------------
Указать ключи от своих аккаунтов и от куда по умолчанию будут приходить смс сообщения.

```php
'components' => [
    'sms' => [
        'class' => \bongrun\sms\Sms::className(),
        'site' => \bongrun\sms\service\SmsSites::OTHER,
        'services' => [
            [
                'class' => \bongrun\sms\service\SmsActivateService::className(),
                'apiKey' => 'apiKey1234567890',
            ],
            [
                'class' => \bongrun\sms\service\SmsAreaService::className(),
                'apiKey' => 'apiKey1234567890',
            ],
            [
                'class' => \bongrun\sms\service\SmsSimService::className(),
                'apiKey' => 'apiKey1234567890',
            ],
            [
                'class' => \bongrun\sms\service\SmsRegService::className(),
                'apiKey' => 'apiKey1234567890',
            ],
        ],
    ],
],
```

Методы
------------
```php
/** @var Sms $sms */
$sms = \Yii::$app->sms;
```

#### Запрос на получение общего баланса
```php
$balance = $sms->getBalance(); 
if (!$balance) {
    throw new Exception('Нет денег на смс');
}
```

#### Изменяем сайт с которого необходимо получить смс
```php
$sms->site = \bongrun\sms\service\SmsSites::VKONTAKTE;
```

#### Количество доступных номеров
```php
$count = $sms->getNumbersStatus();
```

#### Получение номера
```php
$number = $sms->getNumber();
```

#### Изменяем статус
```php
// Отменить активацию
$sms->setStatus($sms::STATUS_CANCEL);
// Сообщить о готовности номера (смс на номер отправлено)
$sms->setStatus($sms::STATUS_READY);
// Сообщить о неверном коде
$sms->setStatus($sms::STATUS_INVALID);
// Завершить активацию(если был статус "код получен" - помечает успешно и завершает, если был "подготовка" - удаляет и помечает ошибка, если был статус "ожидает повтора" - переводит активацию в ожидание смс)
$sms->setStatus($sms::STATUS_COMPLETE);
// Сообщить о том, что номер использован и отменить активацию
$sms->setStatus($sms::STATUS_USED);
```

#### Получение кода
```php
$code = $sms->getCode();
```

Пример использования
------------
```php
$sms = new Sms();
try {
    $number = $sms->getNumber();
    ...
    $sms->setStatus($sms::STATUS_READY);
    list($status, $code) = $sms->getCode();
    if ($status) {
        ...
        $sms->setStatus($sms::STATUS_COMPLETE);
    } else {
        ...
    }
} catch (Exception $e) {
    $sms->setStatus($sms::STATUS_CANCEL);
    throw $e;
}
```
