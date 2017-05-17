<?php

namespace bongrun\sms;

use bongrun\sms\error\SmsException;
use bongrun\sms\service\SmsServiceBase;
use bongrun\sms\service\SmsSites;

/**
 * Class Sms
 * @package bongrun\sms
 */
class Sms
{
    /** @var SmsServiceBase */
    private $service;

    /**
     * @var string Сайт
     */
    private $site = SmsSites::OTHER;

    /**
     * @status отменить активацию
     */
    const STATUS_CANCEL = 'cancel';
    /**
     * @status сообщить о готовности номера (смс на номер отправлено)
     */
    const STATUS_READY = 'ready';
    /**
     * @status сообщить о неверном коде
     */
    const STATUS_INVALID = 'invalid';
    /**
     * @status завершить активацию(если был статус "код получен" - помечает успешно и завершает, если был "подготовка" - удаляет и помечает ошибка, если был статус "ожидает повтора" - переводит активацию в ожидание смс)
     */
    const STATUS_COMPLETE = 'complete';
    /**
     * @status сообщить о том, что номер использован и отменить активацию
     */
    const STATUS_USED = 'used';

    /**
     * Сервисы по приёму смс
     * @var SmsServiceBase[]
     */
    public $services = [];

    /**
     * Sms constructor.
     * @param $services
     * @param null $site
     */
    public function __construct($services, $site = null)
    {
        $this->services = $services;
        $services = [];
        foreach ($this->services as $key => $service) {
            try {
                if (!is_object($service)) {
                    /** @var SmsServiceBase $service */
                    $service = new $service['class']($service['apiKey']);
                    $balance = $service->getBalance();
                    if (is_null($balance) || $balance > 0) {
                        $services[$key] = $service;
                    }
                }
            } catch (SmsException $e) {
            }
        }
        $this->services = $services;
        $this->setSite($site);
    }

    /**
     * Выбираем с какого сайта будут приходить смс сообщения
     * @param null|array $site
     */
    public function setSite($site = null)
    {
        $this->site = $site;
        if (!is_null($site)) {
            $prices = [];
            foreach ($this->services as $key => $service) {
                $service->setSite($site);
                $prices[$key] = $service->getPrice();
            }
            asort($prices);
            $services = [];
            foreach ($prices as $key => $price) {
                $services[$key] = $this->services[$key];
            }
            $this->services = $services;
        }
    }

    /**
     * Доступное количество номеров
     * @param null|array $site
     * @return integer
     * @throws \Exception
     */
    public function getNumbersStatus($site = null)
    {
        $this->setSite($site);
        $count = 0;
        foreach ($this->services as $key => $service) {
            try {
                $count += $service->getNumbersStatus();
            } catch (SmsException $e) {
                unset($this->services[$key]);
            }
        }
        return $count;
    }

    /**
     * Баланс
     * @return integer
     * @throws \Exception
     */
    public function getBalance()
    {
        $balance = 0;
        foreach ($this->services as $service) {
            try {
                $balance += $service->getBalance();
            } catch (SmsException $e) {
            }
        }
        return $balance;
    }

    /**
     * Получить номер
     * @param null|array $site
     * @return string
     * @throws SmsException
     */
    public function getNumber($site = null)
    {
        $this->setSite($site);
        foreach ($this->services as $service) {
            try {
                $number = $service->getNumber();
                $this->service = $service;
                return $number;
            } catch (SmsException $e) {
            }
        }
        throw new SmsException('Не нашло номер');
    }

    /**
     * Задаём статус
     * @param int $status
     * @throws SmsException
     */
    public function setStatus($status = self::STATUS_READY)
    {
        if (is_object($this->service)) {
            /** @var SmsServiceBase $service */
            $service = $this->service;
            switch ($status) {
                case self::STATUS_CANCEL:
                    $this->service->setStatus($service::$METHOD_CANCEL);
                    break;
                case self::STATUS_COMPLETE:
                    $this->service->setStatus($service::$METHOD_COMPLETE);
                    break;
                case self::STATUS_READY:
                    $this->service->setStatus($service::$METHOD_READY);
                    break;
                case self::STATUS_INVALID:
                    $this->service->setStatus($service::$METHOD_INVALID);
                    break;
                case self::STATUS_USED:
                    $this->service->setStatus($service::$METHOD_USED);
                    break;
                default:
                    throw new SmsException('Нет такого статуса');
            }
        }
    }

    /**
     * Получаем код
     * @return array
     * @throws SmsException
     */
    public function getCode()
    {
        //todo не сделан reset
        return $this->service->getCode();
    }
}