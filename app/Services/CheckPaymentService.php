<?php
namespace App\Services;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
class CheckPaymentService {
    private  $paymentId;
    private  $paymentAccessToken;


    function  __construct($defaultPaymentId = "", $defaultPaymentAccessToken = "")
    {
        $this->paymentId = $defaultPaymentId;
        $this->paymentAccessToken = $defaultPaymentAccessToken;
    }
    public function mercadopago($paymentId,$paymentAccessToken){
        $this->paymentId = $paymentId;
        $this->paymentAccessToken = $paymentAccessToken;
        MercadoPagoConfig::setAccessToken($this->paymentAccessToken);
        $client = new PaymentClient();
        try{
        $request_options = new RequestOptions();
        $checkPayment = $client->get($this->paymentId);
        return $checkPayment;
       }catch(MPApiException $e){
            $e->getApiResponse()->getStatusCode();    
            var_dump($e->getApiResponse()->getContent());
       }
    }
}