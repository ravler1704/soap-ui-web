<?php

namespace App\Controller;

use App\Entity\SoapFunction;
use App\Entity\WebService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SoapController extends AbstractController
{
    private array $wsdlLoginData = [
        'wsdl' => '',
        'login' => '',
        'password' => '',
    ];
    private string $methodName;
    private array $queryParamList;

    #[Route('/soap/data')]
    public function number(Request $request): Response
    {
        $wsdlLoginData = $request->request->all();
        if (!empty($wsdlLoginData)) {
            $this->wsdlLoginData = $wsdlLoginData;
        }

        $webService = $this->getWebService();

        $soapFunctions = [];
        foreach ($webService->getTypes() as $soapTypeStr) {
            $soapFunction = new SoapFunction($soapTypeStr);
            $soapFunctions[] = $soapFunction->__toArray();
        }

        return $this->render('soap/number.html.twig', [
            'soapFunctions' => $soapFunctions,
            'soapResponse' => '',
            'sendRequestRoute' => '',
            'wsdl' => $this->wsdlLoginData['wsdl'],
            'login' => $this->wsdlLoginData['login'],
            'password' => $this->wsdlLoginData['password'],
        ]);
    }

    #[Route('/send_request', methods: ['POST'])]
    public function sendRequest(Request $request): JsonResponse
    {
        $allData = $request->request->all();
        if (array_key_exists('soapFunction', $allData)) {
            $soapFunction = $allData['soapFunction'];
        }
        if (array_key_exists('loginData', $allData)) {
            $loginData = $allData['loginData'];
        }

        $this->methodName = $soapFunction['methodName'];

        foreach ($soapFunction['paramList'] as $param) {
            $this->queryParamList[$param['name']] = $param['value'];
        }

        $this->wsdlLoginData = [
            'wsdl' => $loginData['wsdl'],
            'login' => $loginData['login'],
            'password' => $loginData['password'],
        ];
        $soapResponse = $this->getSoapResponse();

        $jsonData = [
            'successMessage' => 'ok!',
            'soapResponse' => $soapResponse
        ];
        return new JsonResponse($jsonData);
    }


    /**
     * @param array $queryParamList
     * @param WebService $webService
     * @return mixed|null
     */
    private function getSoapResponse(): mixed
    {
        $soapResponse = null;
        if (!empty($this->queryParamList)) {
            $webService = $this->getWebService();
            $soapResponse = $webService->call($this->methodName, $this->queryParamList);
            $soapResponse = json_decode(json_encode($soapResponse), true);
            $soapResponse = json_decode($soapResponse['return'], true);
        }
        return $soapResponse;
    }

    /**
     * @return WebService
     */
    private function getWebService(): WebService
    {
        $webServiceRow = [
            'WSDL' => $this->wsdlLoginData['wsdl'] ?: null,
            'LOGIN' => $this->wsdlLoginData['login'] ?: null,
            'PASSWORD' => $this->wsdlLoginData['password'] ?: null,
        ];
        return new WebService($webServiceRow);
    }

}