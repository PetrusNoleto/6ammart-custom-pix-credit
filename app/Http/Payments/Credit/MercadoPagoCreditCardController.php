<?php

namespace App\Http\Payments\Credit;
use App\Traits\Processor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Models\DeliveryMan;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentRequest;
use App\Services\CreatePaymentService;
use App\Services\CheckPaymentService;
class MercadoPagoCreditCardController extends Controller
{
    use Processor;
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
    private PaymentRequest $paymentRequest;
    private $config;
    private $currencies;
    private $order;
    private $stores;
    private $user;
    private $deliveryMan;
    public function __construct(
        PaymentRequest $paymentRequest, 
        User $user,
        DeliveryMan $deliveryMan,
        Order $order,
        Currency $currencies, 
        Store $stores,
        $defaultPaymentId = "", 
        $defaultPaymentAccessToken = "", 
        $defaultPaymentEmail = "",
        $defaultPaymentValue = "",
        $defaultPaymentDescription = "",
        $defaultPaymentToken = "",
        $defaultMethodId = "",
        $defaultPaymentIssuerId = "",
        $defaultPaymentInstallments = 1,
        $defaultPaymentIdentificationType = "",
        $defaultPaymentIdentificationNumber = ""
        )
    {
        $config = $this->payment_config('mercadopago', 'payment_config');
        if (!is_null($config) && $config->mode == 'live') {
            $this->config = json_decode($config->live_values);
        } elseif (!is_null($config) && $config->mode == 'test') {
            $this->config = json_decode($config->test_values);
        }
        $this->currencies = $currencies;
        $this->order = $order;
        $this->paymentRequest = $paymentRequest;
        $this->stores = $stores;
        $this->user = $user;
        $this->paymentAccessToken = $defaultPaymentAccessToken;
        $this->paymentEmail = $defaultPaymentEmail;
        $this->paymentValue = $defaultPaymentValue;
        $this->paymentDescription = $defaultPaymentDescription;
        $this->deliveryMan = $deliveryMan;
        $this->paymentToken= $defaultPaymentToken;
        $this->paymentIssuerId= $defaultPaymentIssuerId;
        $this->paymentMethodId= $defaultMethodId ;
        $this->paymentInstallments=$defaultPaymentInstallments;
        $this->paymentPayerIdentificationType=$defaultPaymentIdentificationType;
        $this->paymentPayerIdentificationNumber=$defaultPaymentIdentificationNumber;
    }
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }
        $data = $this->paymentRequest::where(['id' => $request['payment_id']])->where(['is_paid' => 0])->first();
        if (!isset($data)) {
            $this->payment_failed($request['payment_id']);
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_204), 200);
        }
        
        if($data['attribute'] === "order"){
            $additional_data =  $data->additional_data;
            $config = $this->config;
            $accessToken = $config->access_token;
            $paymentId = $request['payment_id'];
            $paymentOrderId = $data->attribute_id;
            $paymentCurrency = $data->currency_code;
            $payerId = $data->payer_id;
            $payerData = $this->user::where(['id' => $payerId])->first();
            $payerEmail = $payerData->email;
            $paymentValue = $data->payment_amount;
            $currencyData = $this->currencies::where(['currency_code' => $paymentCurrency])->first();
            $currencySimbol = $currencyData->currency_symbol;
            $paymentOrderData = $this->order::where(['id' => $paymentOrderId])->first();
            $paymentStoreId = $paymentOrderData->store_id;
            $storeData = $this->stores::where(['id' => $paymentStoreId])->first();
            $storeName =$storeData->name;
            $storeImage =$storeData->logo;
            return view('payment-views.payment-view-mercado-pago-credit', compact('paymentId','config','data','payerEmail','paymentValue'));
        } 
        if($data['attribute'] === "deliveryman_collect_cash_payments"){
            $additional_data =  $data->additional_data;
            $config = $this->config;
            $accessToken = $config->access_token;
            $paymentId = $request['payment_id'];
            $paymentOrderId = $data->attribute_id;
            $paymentCurrency = $data->currency_code;
            $payerId = $data->payer_id;
            $payerData = $this->deliveryMan::where(['id' => $payerId])->first();
            $payerEmail = $payerData->email;
            $paymentValue = $data->payment_amount;
            $currencyData = $this->currencies::where(['currency_code' => $paymentCurrency])->first();
            $currencySimbol = $currencyData->currency_symbol;
            $paymentOrderData = $this->order::where(['id' => $paymentOrderId])->first();
            return view('payment-views.payment-view-mercado-pago-credit', compact('paymentId','config','data','payerEmail','paymentValue'));
        } 
        if($data['attribute'] === "wallet_payments"){
            $additional_data =  $data->additional_data;
            $config = $this->config;
            $accessToken = $config->access_token;
            $paymentId = $request['payment_id'];
            $paymentOrderId = $data->attribute_id;
            $paymentCurrency = $data->currency_code;
            $payerId = $data->payer_id;
            $payerData = $this->user::where(['id' => $payerId])->first();
            $payerEmail = $payerData->email;
            $paymentValue = $data->payment_amount;
            $currencyData = $this->currencies::where(['currency_code' => $paymentCurrency])->first();
            $currencySimbol = $currencyData->currency_symbol;
            $paymentOrderData = $this->order::where(['id' => $paymentOrderId])->first();
            return view('payment-views.payment-view-mercado-pago-credit', compact('paymentId','config','data','payerEmail','paymentValue'));
        } 
    }
    public function create(Request $request)
    {
        $this->paymentId = $request['paymentId'];
        $this->paymentAccessToken = $request['paymentAccessToken'];
        $this->paymentEmail = $request['paymentEmail'];
        $this->paymentValue = $request['paymentValue'];
        $this->paymentDescription = $request['paymentDescription'];
        $this->paymentToken= $request['paymentToken'];
        $this->paymentIssuerId= $request['paymentIssuerId'];
        $this->paymentMethodId= $request['paymentMethodId'] ;
        $this->paymentInstallments=$request['paymentInstallments'];
        $this->paymentPayerIdentificationType=$request['paymentIdentificationType'];
        $this->paymentPayerIdentificationNumber=$request['paymentIdentificationNumber'];
        if(
                !$this->paymentId || 
                !$this->paymentAccessToken || 
                !$this->paymentEmail || 
                !$this->paymentValue || 
                !$this->paymentDescription ||
                !$this->paymentToken ||
                !$this->paymentIssuerId ||
                !$this->paymentMethodId ||
                !$this->paymentInstallments ||
                !$this->paymentPayerIdentificationType ||
                !$this->paymentPayerIdentificationNumber
            ){
            return response()->json([
                'code'=>4402,
                'data' => $request,
                'message' => "invalid request data"
            ]);
        }else{
            $paymentController = new CreatePaymentService();
            $newValue = floatval($this->paymentValue);
            $createNewPayment = $paymentController->MercadoPagoCredit(
                $this->paymentId,
                $this->paymentAccessToken,
                $this->paymentEmail,$newValue,
                $this->paymentDescription, 
                $this->paymentToken,
                $this->paymentIssuerId,
                $this->paymentMethodId,
                $this->paymentInstallments,
                $this->paymentPayerIdentificationType,
                $this->paymentPayerIdentificationNumber,
            );
            $paymentData = [
                "id" => $createNewPayment->id,
                "status" => $createNewPayment->status
            ];
            return response()->json([
                'code' => 2201,
                'message' => 'payment created',
                'data' =>  $paymentData,
            ]);
        }
    }
    public function payment_approved(Request $request)
    {
        $paymentData = $this->paymentRequest::where(['id' => $request['payment_id']])->first();
        if($paymentData->id != null){
            $this->paymentRequest::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'mercadopago',
                'is_paid' => 1,
                'transaction_id' => $request['transaction_id'],
            ]);
            $data = $this->paymentRequest::where(['id' => $request['payment_id']])->first();
            if (isset($data) && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }
            return $this->payment_response($data, 'success');
        }else{
            $paymentData = $this->paymentRequest::where(['id' => $request['payment_id']])->first();
            if (isset($paymentData) && function_exists($paymentData->failure_hook)) {
                call_user_func($paymentData->failure_hook, $paymentData);
            }
            return $this->payment_response($paymentData, 'fail');
        }
    }
    public function payment_failed(Request $request)
    {
        $paymentData = $this->paymentRequest::where(['id' => $request['payment_id']])->first();
        if (isset($paymentData) && function_exists($paymentData->failure_hook)) {
            call_user_func($paymentData->failure_hook, $paymentData);
        }
        return $this->payment_response($paymentData, 'fail');
    }
}