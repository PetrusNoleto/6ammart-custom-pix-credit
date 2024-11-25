<!DOCTYPE html>
<html  lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>
        @yield('title')
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <script src="https://sdk.mercadopago.com/js/v2"></script>
   
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link rel="stylesheet"  href="/resources/css/mercadopagocredit.css">
</head>
<body>

    <input type="hidden" id="mercado-pago-public-key" value="{{$config->public_key}}">
       <div class="container">
            <!-- <div class="payment-container">
              <div class="payment-container-info">
                <h1>pague com <br/>cartão de credito</h1>

              </div> -->
              <div id="cardPaymentBrick_container"></div> 
            </div>    
        </div>
        
   
<script>
    const paymentid = "{{$paymentId}}";
    const publicKey = document.getElementById("mercado-pago-public-key").value;
        const mp = new MercadoPago(publicKey, {
          locale: 'pt-BR'
        });
        const bricksBuilder = mp.bricks();
        const renderCardPaymentBrick = async (bricksBuilder) => {
          const settings = {
            initialization: {
              amount:"{{$paymentValue}}",
              payer:{
                email:"{{$payerEmail}}"
              }
            },
            customization: {
                visual: {
                hideFormTitle: true, 
                style: {
                  customVariables: {
                    theme: 'default', 
                  }
                }
              },
              paymentMethods: {
                  types: {
                    excluded: ['debit_card']
                  }, 
                  maxInstallments: 1,
                }
            },
            callbacks: {
              onReady: () => {
                
              },
               onSubmit: async(cardFormData) => {
                const requestData = {
                  paymentId:"{{$paymentId}}",
                  paymentAccessToken:"{{$config->access_token}}",
                  paymentEmail:cardFormData.payer.email,
                  paymentValue:cardFormData.transaction_amount,
                  paymentDescription:`pagamento de feito com cartão de credito`,
                  paymentToken:cardFormData.token,
                  paymentIssuerId:cardFormData.issuer_id,
                  paymentMethodId:cardFormData.payment_method_id,
                  paymentInstallments:cardFormData.installments,
                  paymentIdentificationType:cardFormData.payer.identification.type,
                  paymentIdentificationNumber:cardFormData.payer.identification.number,
                }
                try{
                  const createNewPayment = await fetch("/payment/mercadopagocredit/create", {
                    method: "POST",
                    body: JSON.stringify({
                        paymentId:requestData.paymentId,
                        paymentAccessToken:requestData.paymentAccessToken,
                        paymentEmail:requestData.paymentEmail,
                        paymentValue:requestData.paymentValue,
                        paymentDescription:requestData.paymentDescription,
                        paymentToken:requestData.paymentToken,
                        paymentIssuerId:requestData.paymentIssuerId,
                        paymentMethodId:requestData.paymentMethodId,
                        paymentInstallments:requestData.paymentInstallments,
                        paymentIdentificationType:requestData.paymentIdentificationType,
                        paymentIdentificationNumber:requestData.paymentIdentificationNumber
                    }),
                    headers: {"Content-type": "application/json; charset=UTF-8"}  
                })        
                const getPixData = await createNewPayment.json()
                console.log(getPixData.data.status)
                if(getPixData.message === "payment checked"&& check.data !== null){
                  const mercadopagopixid = getPixData.data.id  
                  switch(getPixData.data.status){
                        case  "approved":
                            location.replace(`/payment/mercadopagocredit/success?payment_id=${paymentid}&transaction_id=${mercadopagopixid}`)
                            break;
                        case  "rejected":
                            location.replace(`/payment/mercadopagocredit/failed?payment_id=${paymentid}`)
                            break;
                        case  "in_process":
                            location.replace(`/payment/mercadopagocredit/failed?payment_id=${paymentid}`)
                            break;      
                        case  "in_mediation":
                            location.replace(`/payment/mercadopagocredit/failed?payment_id=${paymentid}`)
                            break;
                        case  "cancelled":
                            location.replace(`/payment/mercadopagocredit/failed?payment_id=${paymentid}`)
                            break;
                        case  "refunded":
                            location.replace(`/payment/mercadopagocredit/failed?payment_id=${paymentid}`)
                            break;                          
                        case  "charged_back":
                            location.replace(`/payment/mercadopagocredit/failed?payment_id=${paymentid}`)
                            break               
                        case  "charged_back":
                            location.replace(`/payment/mercadopagocredit/failed?payment_id=${paymentid}`)
                            break     
                    }
                }else{
                    reloadPage()
                }
                }catch(error){
                  location.replace(`/payment/mercadopagocredit/failed?payment_id=${paymentid}`)
                } 
              },
              onError: (error) => {
                location.replace(`/payment/mercadopagocredit/failed?payment_id=${paymentid}`)
              },
            },
          };
          window.cardPaymentBrickController = await bricksBuilder.create('cardPayment', 'cardPaymentBrick_container', settings);
        };
        renderCardPaymentBrick(bricksBuilder);  
</script>
<script src = "/resources/js/createMercadoPagoCreditPayment.js"></script>
</body>
</html>
