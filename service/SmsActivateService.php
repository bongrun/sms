<?php

namespace bongrun\sms\service;

use bongrun\sms\error\SmsException;

/**
 * http://sms-activate.ru/
 *
 * Class SmsActivateService
 * @package bongrun\sms\service
 */
class SmsActivateService extends SmsServiceBase
{
    protected $sites = [
        SmsSites::VKONTAKTE => [
            'name' => 'vk',
            'price' => 10,
        ],
        SmsSites::ODNOKLASSNIKI => [
            'name' => 'od',
            'price' => 5,
        ],
        SmsSites::WHATSAPP => [
            'name' => 'wa',
            'price' => 3,
        ],
        SmsSites::VIBER => [
            'name' => 'vi',
            'price' => 3,
        ],
        SmsSites::TELEGRAM => [
            'name' => 'tg',
            'price' => 3,
        ],
        SmsSites::GOOGLE => [
            'name' => 'go',
            'price' => 2,
        ],
        SmsSites::AVITO => [
            'name' => 'av',
            'price' => 4,
        ],
        SmsSites::FACEBOOK => [
            'name' => 'fb',
            'price' => 2,
        ],
        SmsSites::TWITTER => [
            'name' => 'tw',
            'price' => 1,
        ],
        SmsSites::UBER => [
            'name' => 'ub',
            'price' => 2,
        ],
        SmsSites::QIWI => [
            'name' => 'qw',
            'price' => 6,
        ],
        SmsSites::GETTAXI => [
            'name' => 'gt',
            'price' => 1,
        ],
        SmsSites::OLX => [
            'name' => 'sn',
            'price' => 2,
        ],
        SmsSites::INSTAGRAM => [
            'name' => 'ig',
            'price' => 5,
        ],
        SmsSites::SEOSPRINT => [
            'name' => 'ss',
            'price' => 2,
        ],
        SmsSites::YANDEX => [
            'name' => 'ya',
            'price' => 1,
        ],
        SmsSites::MAILRU => [
            'name' => 'ma',
            'price' => 2,
        ],
        SmsSites::MICROSOFT => [
            'name' => 'mm',
            'price' => 1,
        ],
        SmsSites::IMO => [
            'name' => 'uk',
            'price' => 1,
        ],
        SmsSites::LM => [
            'name' => 'me',
            'price' => 2,
        ],
        SmsSites::YAHOO => [
            'name' => 'mb',
            'price' => 1,
        ],
        SmsSites::AOL => [
            'name' => 'we',
            'price' => 1,
        ],
        SmsSites::OTHER => [
            'name' => 'ot',
            'price' => 2,
        ],
    ];

    protected $href = 'http://sms-activate.ru/stubs/handler_api.php?action={method}';

    const API_KEY = 'api_key';
    const ID = 'id';
    const SITE = 'service';
    const NUMBER = 'number';

    public static $METHOD_GET_NUMBERS_STATUS = 'getNumbersStatus';
    public static $METHOD_GET_BALANCE = 'getBalance';
    public static $METHOD_GET_NUMBER = [
        'method' => 'getNumber',
        'ref' => 'yii2sms',
    ];
    public static $METHOD_READY = [
        'method' => 'setStatus',
        'status' => 1,
    ];
    public static $METHOD_CANCEL = [
        'method' => 'setStatus',
        'status' => -1,
    ];
    public static $METHOD_INVALID = [
        'method' => 'setStatus',
        'status' => 3,
    ];
    public static $METHOD_COMPLETE = [
        'method' => 'setStatus',
        'status' => 6,
    ];
    public static $METHOD_USED = [
        'method' => 'setStatus',
        'status' => 8,
    ];
    public static $METHOD_GET_STATUS = 'getStatus';

    /** @inheritdoc */
    public function getNumbersStatus($site = null)
    {
        $result = parent::getNumbersStatus($site);
        return $result["{$this->site['name']}_0"] ?? 0;
    }

    /** @inheritdoc */
    public function getBalance()
    {
        if (is_null($this->balance)) {
            $result = parent::getBalance();
            $result = explode(':', $result);
            $result[] = null;
            $result[] = null;
            list($message, $value) = $result;
            switch ($message) {
                case 'ACCESS_BALANCE':
                    $this->balance = $value;
                    return $this->balance;
                default:
                    throw new SmsException($message);
            }
        }
        return $this->balance;
    }

    /** @inheritdoc */
    public function getNumber($site = null)
    {
        $result = parent::getNumber($site);
        $result = explode(':', $result);
        $result[] = null;
        $result[] = null;
        list($request, $id, $number) = $result;
        switch ($request) {
            case 'NO_NUMBERS':
                throw new SmsException($request, 404);
            case 'ACCESS_NUMBER':
                $this->sessionId = $id;
                $this->number = str_pad($number, 12, "+7", STR_PAD_LEFT);
                break;
            default:
                throw new SmsException($request);
        }
        return $this->number;
    }

    /** @inheritdoc */
    public function setStatus($status = null)
    {
        $result = parent::setStatus($status);
        switch ($result) {
            case 'ACCESS_READY':
            case 'ACCESS_RETRY_GET':
            case 'ACCESS_ACTIVATION':
            case 'ACCESS_CANCEL':
                break;
            default:
                throw new SmsException($result, 707);
        }
    }

    /** @inheritdoc */
    public function getCode()
    {
        $time = time();
        while (true) {
            if (time() - $time > 60 * 15) {
                return [false, null];
                //throw new SmsException('Превышенно время ожидания смс', 300);
            }
            $result = parent::getCode();
            $result = explode(':', $result);
            $result[] = null;
            $request = array_shift($result);
            $code = [];
            foreach ($result as $resultRow) {
                $code[] = $resultRow;
            }
            $code = implode(':', $code);
            switch ($request) {
                case 'STATUS_WAIT_RETRY':
                case 'STATUS_WAIT_CODE':
                    sleep(10);
                    break;
                case 'STATUS_WAIT_RESEND':
                    return [false, null];
                //$this->setStatus(self::$METHOD_COMPLETE);
                //return ['RETURN', null];
                case 'STATUS_OK':
                    return [true, $code];
                default:
                    return [false, null];
                //throw new SmsException($request);
            }
        }
    }
}
