<?php

namespace App\Entity;

use PHPUnit\Util\Exception;

class WebService
{
    public $id;
    public $name;
    public $code;
    private $wsdl;
    private $login;
    private $password;
    private $soapClient;

    public function __construct($webserviceRow, $params = [])
    {
        $this->wsdl = $webserviceRow['WSDL'];
        $this->login = $webserviceRow['LOGIN'];
        $this->password = $webserviceRow['PASSWORD'];
        $this->getSoapClient($this->wsdl);
    }

    public function call($methodName, $params)
    {
        $message = 'Ошибка на стороне сервера';
        $showThrowableMessage = true;

        $soapClient = $this->getSoapClient($this->wsdl);
        if (is_null($soapClient)) {
            $showThrowableMessage = false;
            $message = 'Ошибка подключения к вебсервису';
        }

        try {
            $response = $soapClient->$methodName($params);
            if (!$response->return) {
                throw new \Exception('Не удалось получить данные из внешнего ресурса');
            }
        } catch (\Throwable $error) {
            throw new Exception($showThrowableMessage ? $error->getMessage() : $message);
        }
        return $response;
    }

    public function getTypes() {
        if (!is_null($this->soapClient)) {
            return $this->soapClient[$this->wsdl]->__getTypes();
        }
        return [];
    }

    /**
     * @throws \Exception
     */
    private function getSoapClient($wsdl)
    {
        if (empty($wsdl)) {
            return null;
        }
        if (!$this->soapClient) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            $defaultParams = [
                'location' => $wsdl,
                'keep_alive' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'login' => $this->login,
                'password' => $this->password,
                'stream_context' => $context,
                'trace' => true,
            ];
            try {
                $this->soapClient[$wsdl] = new \SoapClient($wsdl. '?wsdl', $defaultParams);
            } catch (\Exception $exception) {
                throw new Exception($exception->getMessage());
            }
        }
        return $this->soapClient[$wsdl];
    }
}