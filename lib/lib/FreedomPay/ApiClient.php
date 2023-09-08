<?php

namespace FreedomPay;

use CSalePaySystemAction;
use JsonException;
use RuntimeException;
use SimpleXMLElement;

class ApiClient
{
    public const PARSE_XML_ERROR = 'Parse XML response error';

    private string $createPaymentsUrl;

    public function __construct()
    {
        $apiUrl = CSalePaySystemAction::GetParamValue('API_URL');

        if (empty($apiUrl)) {
            $apiUrl = explode(',', 'api.freedompay.money,api.freedompay.uz')[0] ?? 'api.freedompay.money';
        }

        $this->createPaymentsUrl = "https://$apiUrl/init_payment.php";
    }

    /**
     * @throws JsonException
     */
    public function createPayment(array $paymentData): string
    {
        $response = $this->post($this->createPaymentsUrl, $paymentData);

        return $this->getRedirectUrlFromXmlResponse($response);
    }

    /**
     * @throws JsonException
     */
    private function post(string $url, array $postData): bool|string
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            json_encode($postData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        curl_close($ch);

        return curl_exec($ch);
    }

    /**
     * @throws RuntimeException
     */
    private function getRedirectUrlFromXmlResponse(string $responseBody): string
    {
        $responseXml = simplexml_load_string($responseBody);

        if (!$responseXml instanceof SimpleXMLElement || empty($responseXml->pg_redirect_url)) {
            throw new RuntimeException(self::PARSE_XML_ERROR);
        }

        return (string)$responseXml->pg_redirect_url;
    }
}
