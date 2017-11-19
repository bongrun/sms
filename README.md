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


Методы
------------
```php
/** @var SmsSimple $sms */
$sms = new SmsSimple($smsAccesses, SmsSites::VKONTAKTE);
```

#### Запрос на получение общего баланса
```php
$balance = $sms->getBalance(); 
if (!$balance) {
    throw new Exception('Нет денег на смс');
}
```

#### Получение номера
```php
$number = $sms->getNumber();
```

Пример использования
------------
```php
$sms = new SmsApi($user->getSmsAccesses(), SmsSites::VKONTAKTE);
$number = $sms->getNumber();

.....
.....

$sms->code();
if ($sms->isError()) {
    throw new \Exception('Смс не было получино');
}
$vkReg->setCode(preg_replace("/[^0-9]/", '', $sms->getCode()));
```
