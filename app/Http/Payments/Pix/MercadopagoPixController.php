<?php

namespace App\Http\Payments\Pix;
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
class MercadopagoPixController extends Controller
{
    use Processor;
    private  $paymentId;
    private  $paymentAccessToken;
    private  $paymentEmail;
    private  $paymentValue;
    private  $paymentDescription;
    private PaymentRequest $paymentRequest;
    private $config;
    private $currencies;
    private $order;
    private $stores;
    private $user;
    private $deliveryMan;
    public function __construct(PaymentRequest $paymentRequest, User $user,DeliveryMan $deliveryMan,Order $order,Currency $currencies, Store $stores,$defaultPaymentId = "", $defaultPaymentAccessToken = "", $defaultPaymentEmail = "", $defaultPaymentValue = "", $defaultPaymentDescription = "")
    {
        $config = $this->payment_config('mercadopagopix', 'payment_config');
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
        $this->deliveryMan = $deliveryMan;
    }
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json($this->response_formatter(GATEWAYS_DEFAULT_400, null, $this->error_processor($validator)), 400);
        }
        $data = $this->paymentRequest::where(['id' => $request['payment_id']])->first();
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
            return view('payment-views.payment-view-mercado-pago-pix', compact('paymentId','accessToken','additional_data','paymentOrderId','storeImage','storeName','currencySimbol','paymentValue','payerEmail'));
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
            return view('payment-views.payment-view-mercadopagopix-delivery-collect',compact('paymentId','accessToken','additional_data','paymentOrderId','currencySimbol','paymentValue','payerEmail'));
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
            return view('payment-views.payment-view-mercadopagopix-user-wallet',compact('paymentId','accessToken','additional_data','paymentOrderId','currencySimbol','paymentValue','payerEmail'));
        } 

     
        
      
    }
    public function create(Request $request)
    {
        $this->paymentId = $request['paymentId'];
        $this->paymentAccessToken = $request['paymentAccessToken'];
        $this->paymentEmail = $request['paymentEmail'];
        $this->paymentValue = $request['paymentValue'];
        $this->paymentDescription = $request['paymentDescription'];
        if(!$request['paymentId'] && !$request['paymentAccessToken'] && !$request['paymentEmail'] && !$request['paymentValue'] && !$request['paymentDescription']){
            return response()->json([
                'code'=>4402,
                'data' => null,
                'message' => "invalid request data"
            ]);
        }else{
            $paymentController = new CreatePaymentService();
            $newValue = floatval($this->paymentValue);
            $createNewPayment = $paymentController->MercadoPagoPix($this->paymentId,$this->paymentAccessToken,$this->paymentEmail,$newValue,$this->paymentDescription);
            $paymentData = [
                "id" => $createNewPayment->id,
                "status" => $createNewPayment->status,
                "qrcode" => $createNewPayment->point_of_interaction->transaction_data->qr_code,
                "qrcodeImage" => $createNewPayment->point_of_interaction->transaction_data->qr_code_base64
            ];
            return response()->json([
                'code' => 2201,
                'message' => 'payment created',
                'data' =>  $paymentData,
            ]);
        }
    }
    public function check(Request $request)
    {
        $this->paymentId = $request['paymentId'];
        $this->paymentAccessToken = $request['paymentAccessToken'];
        $newIdValue = intval($this->paymentId);
        if(!$request['paymentId'] && !$request['paymentAccessToken']){
            return response()->json([
                'code'=>4402,
                'data' => null,
                'message' => 'payment not checked'
            ]);
        }else{
            $checkController = new CheckPaymentService();
            $checkPayment = $checkController->mercadopago($newIdValue,$this->paymentAccessToken);
            $paymentData = [
                "id" => $checkPayment->id,
                "status" => $checkPayment->status,
                "qrcode" => $checkPayment->point_of_interaction->transaction_data->qr_code,
                "qrcodeImage" => $checkPayment->point_of_interaction->transaction_data->qr_code_base64
            ];
            return response()->json([
                'code' => 2200,
                'message' => 'payment checked',
                'data' =>  $paymentData,  
            ]);
        }
    }
    public function payment_approved(Request $request)
    {
        $paymentData = $this->paymentRequest::where(['id' => $request['payment_id']])->first();
        if($paymentData->id != null){
            $this->paymentRequest::where(['id' => $request['payment_id']])->update([
                'payment_method' => 'mercadopagopix',
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