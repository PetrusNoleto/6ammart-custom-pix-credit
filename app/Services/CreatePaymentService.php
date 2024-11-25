<?php
namespace App\Services;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
class CreatePaymentService {
    private $paymentId;
    private $paymentAccessToken;
    private $paymentEmail;
    private $paymentValue;
    private $paymentDescription;
    private $paymentToken;
    private $paymentIssuerId;
    private $paymentMethodId;
    private $paymentInstallments;
    private $paymentPayerIdentificationType;
    private $paymentPayerIdentificationNumber;
    function  __construct(
        $defaultPaymentId = "", 
        $defaultPaymentAccessToken = "", 
        $defaultPaymentEmail = "", 
        $defaultPaymentValue = 0.0, 
        $defaultPaymentDescription = "",
        $defaultPaymentToken = "",
        $defaultMethodId = "",
        $defaultPaymentIssuerId = "",
        $defaultPaymentInstallments = 1,
        $defaultPaymentIdentificationType = "",
        $defaultPaymentIdentificationNumber = ""
        )
    {
        $this->paymentId = $defaultPaymentId;
        $this->paymentAccessToken = $defaultPaymentAccessToken;
        $this->paymentEmail = $defaultPaymentEmail;
        $this->paymentValue = $defaultPaymentValue;
        $this->paymentDescription = $defaultPaymentDescription;
        $this->paymentToken= $defaultPaymentToken;
        $this->paymentIssuerId= $defaultPaymentIssuerId;
        $this->paymentMethodId= $defaultMethodId ;
        $this->paymentInstallments=$defaultPaymentInstallments;
        $this->paymentPayerIdentificationType=$defaultPaymentIdentificationType;
        $this->paymentPayerIdentificationNumber=$defaultPaymentIdentificationNumber;
        
    }
    public function MercadoPagoPix($paymentId,$paymentAccessToken,$paymentEmail,$paymentValue,$paymentDescription){
        $this->paymentId = $paymentId;
        $this->paymentAccessToken = $paymentAccessToken;
        $this->paymentEmail = $paymentEmail;
        $this->paymentValue = $paymentValue;
        $this->paymentDescription = $paymentDescription;
        MercadoPagoConfig::setAccessToken($this->paymentAccessToken);
        $client = new PaymentClient();
        try{
        $request = [
            "transaction_amount" => $this->paymentValue,
            "description" => $this->paymentDescription,
            "payment_method_id" => "pix",
            "payer" => [
                "email" => $this->paymentEmail,
            ]
        ];
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(["X-Idempotency-Key: $this->paymentId"]);
        $createPayment = $client->create($request, $request_options);
        return $createPayment;
       }catch(MPApiException $e){
            $e->getApiResponse()->getStatusCode();    
            var_dump($e->getApiResponse()->getContent());
       }
    }
    public function MercadoPagoCredit(
        $paymentId,
        $paymentAccessToken,
        $paymentEmail,
        $paymentValue,
        $paymentDescription,
        $paymentToken,
        $paymentIssuerId,
        $paymentMethodId,
        $paymentInstallments,
        $paymentIdentificationType,
        $paymentIdentificationNumber
        ){
        $this->paymentId = $paymentId;
        $this->paymentAccessToken = $paymentAccessToken;
        $this->paymentEmail = $paymentEmail;
        $this->paymentValue = $paymentValue;
        $this->paymentDescription = $paymentDescription;
        $this->paymentToken = $paymentToken;
        $this->paymentIssuerId = $paymentIssuerId;
        $this->paymentMethodId = $paymentMethodId;
        $this->paymentInstallments = $paymentInstallments;
        $this->paymentPayerIdentificationType = $paymentIdentificationType;
        $this->paymentPayerIdentificationNumber = $paymentIdentificationNumber;
        MercadoPagoConfig::setAccessToken($this->paymentAccessToken);
        $client = new PaymentClient();
        try{
            $request = [
                "token" => $this->paymentToken,
                "issuer_id" => $this->paymentIssuerId,
                "transaction_amount" => $this->paymentValue,
                "description" => $this->paymentDescription,
                "payment_method_id" => $this->paymentMethodId,
                "installments"=> $this->paymentInstallments,
                "payer" => [
                    "email" => $this->paymentEmail,
                    "identification" => [
                        "type" => $this->paymentPayerIdentificationType,
                        "number" => $this->paymentPayerIdentificationNumber,
                    ]
                ]
            ];
            $request_options = new RequestOptions();
            $request_options->setCustomHeaders(["X-Idempotency-Key: $this->paymentId"]);
            $createPayment = $client->create($request, $request_options);
            return $createPayment;
       }catch(MPApiException $e){
            $e->getApiResponse()->getStatusCode();    
            var_dump($e->getApiResponse()->getContent());
       }
    }
}