<?php

namespace bongrun\sms;

use bongrun\sms\instances\SmsAccessInstance;

class SmsSimple
{
    /** @var Sms */
    private $sms;
    private $status = false;
    private $code;

    /**
     * SmsApi constructor.
     *
     * @param SmsAccessInstance[] $services
     * @param int         $site
     */
    public function __construct($services, $site = null)
    {
        $servicesData = [];
        foreach ($services as $service) {
            $servicesData[] = [
                'class'  => $service->getClass(),
                'apiKey' => $service->getKey(),
            ];
        }
        $this->sms = new Sms($servicesData, $site);
    }

    public function getNumber()
    {
        $number = $this->sms->getNumber();
        $this->sms->setStatus(Sms::STATUS_READY);
        return $number;
    }

    public function code()
    {
        list($this->status, $this->code) = $this->sms->getCode();
        return $this;
    }

    public function isResult()
    {
        return !!$this->status;
    }

    public function isError()
    {
        return !$this->status;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function invalid()
    {
        $this->sms->setStatus(Sms::STATUS_INVALID);
        return $this;
    }

    public function used()
    {
        $this->sms->setStatus(Sms::STATUS_USED);
        return $this;
    }

    public function getBalance()
    {
        return $this->sms->getBalance();
    }
}