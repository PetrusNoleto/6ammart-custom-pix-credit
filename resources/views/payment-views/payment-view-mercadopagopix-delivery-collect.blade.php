<!DOCTYPE html>
<html  lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
    <title>
        @yield('title')
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/resources/css/mercadopagopix.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
   
</head>
<body >
    <main>
      
    </main><!DOCTYPE html>
<html  lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
    <title>
        @yield('title')
    </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/resources/css/mercadopagopix.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
   
</head>
<body onload = "getPix()">
<main class="mercadopagopixpage">
   

    <div class="bussinessImageBox">
        <img id="businessBrandImg" class="businessBrandImg" src = "" alt=""/>
    </div>    
    <div class="paymentPixBox">
        <h1 class="paymentPixTitle">Page com Pix</h1>     
         
        <div class="paymentPixInfoBox">
          
            <p class="paymentPixDescription">Abra seu aplicativo de pagamentos preferido <br/>e escaneie o código abaixo</p>
        </div>
        <div id = "paymentPixQrCodeBox" class="paymentPixQrCodeBox hidden">
            <img id = "paymentPixQrCode" class="paymentPixQrCode" src = "" alt =""/>
        </div>        
        <div id ="paymentPixQrCodeBoxLoading" class="paymentPixQrCodeBoxLoading">
            <span class="loader"></span>
        </div>     
        <div id="paymentPixOrderInfoBox" class="paymentPixOrderInfoBox hidden">
            <span id="paymentPixOrderInfoBussinessName" class="paymentPixOrderInfoBussinessName"><strong></strong></span>
            <span class="paymentPixOrderInfoNumbers">valor: <strong >{{$currencySimbol}} {{$paymentValue}}</strong></span>
        </div>
        <div class="paymentPixCopyBox">
            <button id="paymentPixCopyButton" class="paymentPixCopyButton hidden" onclick="MercadoPagoPixCopyQrCode()">copiar codigo de pagamento</button> 
        </div>
        <div class="paymentPixReloadBox">
            <h2 class="paymentPixReloadTitle">Se tiver algum problema com a leitura <br/> do <strong>QR code</strong> recarregue a pagina</h2>
            <button class="paymentPixReloadButton" onclick="reloadPage()">recarregar pagina</button> 
        </div> 
    </div>
    
</main>

<script>
   function reloadPage(){
     location.reload()
   }
   async function getPix(){
        const additionalData = "{{$additional_data}}"
        let replaceAdditionalData = additionalData.replace(/&quot;/g, '\"');
        let parseAdditionalData = JSON.parse(replaceAdditionalData);
        const businessName = parseAdditionalData.business_name
        const paymentid = "{{$paymentId}}"
        const paymentValue = "{{$paymentValue}}"
        const paymentDescription = `pagamento pix no valor de {{$paymentValue}} na plataforma ${businessName}`;
        const paymentAccessToken = "{{$accessToken}}"
        const pixPaymentInfoName = document.getElementById('paymentPixOrderInfoBussinessName')
        pixPaymentInfoName.textContent = businessName
        const paymentEmail = "{{$payerEmail}}"
        let getBusinessDataImg = document.getElementById("businessBrandImg");
        getBusinessDataImg.setAttribute("src", parseAdditionalData.business_logo);
        getBusinessDataImg.setAttribute("alt", "logo seu churrasco");
        try{
        const createNewPayment = await createNewPixPayment(paymentid,paymentValue,paymentDescription,paymentAccessToken,paymentEmail)
        if(createNewPayment.message === "payment created" && createNewPayment.data !== null){
            const getQRcodeComponent = document.getElementById('paymentPixQrCode')
            const getPaymentData = createNewPayment.data
            const pixPaymentInfoComponent = document.getElementById('paymentPixOrderInfoBox') 
            const qrcodepixLoadingbox = document.getElementById('paymentPixQrCodeBoxLoading')  
            const qrcodepixbox = document.getElementById('paymentPixQrCodeBox')
            const qrcodeUrlCopyElement = document.getElementById("paymentPixCopyButton");
            getQRcodeComponent.setAttribute("src", `data:image/jpeg;base64,${getPaymentData.qrcodeImage}`);
            getQRcodeComponent.setAttribute("alt", `pagamento pix,${getPaymentData.qrcode}`);
            pixPaymentInfoComponent.classList.remove("hidden")
            qrcodepixLoadingbox.classList.add("hidden")
            qrcodepixbox.classList.remove("hidden")
            qrcodeUrlCopyElement.value = getPaymentData.qrcode
            qrcodeUrlCopyElement.classList.remove("hidden")
            const checkpix = async()=>{
                const mercadopagopixid = getPaymentData.id
                const check = await checkMercadoPagoPixPayment(mercadopagopixid,paymentAccessToken)
                if(check.message === "payment checked"&& check.data !== null){
                    switch(check.data.status){
                        case  "approved":
                            location.replace(`/payment/mercadopagopix/success?payment_id=${paymentid}&transaction_id=${mercadopagopixid}`)
                            break;
                        case  "rejected":
                            location.replace(`/payment/mercadopagopix/failed?payment_id=${paymentid}`)
                            break;
                        case  "in_process":
                            location.replace(`/payment/mercadopagopix/failed?payment_id=${paymentid}`)
                            break;      
                        case  "in_mediation":
                            location.replace(`/payment/mercadopagopix/failed?payment_id=${paymentid}`)
                            break;
                        case  "cancelled":
                            location.replace(`/payment/mercadopagopix/failed?payment_id=${paymentid}`)
                            break;
                        case  "refunded":
                            location.replace(`/payment/mercadopagopix/failed?payment_id=${paymentid}`)
                            break;                          
                        case  "charged_back":
                            location.replace(`/payment/mercadopagopix/failed?payment_id=${paymentid}`)
                            break               
                        case  "charged_back":
                            location.replace(`/payment/mercadopagopix/failed?payment_id=${paymentid}`)
                            break     
                    }
                }else{
                    reloadPage()
                }
                return check
            }
            setInterval(() => checkpix(), 3000); 
        }
        }catch(error){
            location.replace(`/payment/mercadopagopix/failed?payment_id=${paymentid}`)
        }
       
    }
    </script>
    <script src = "/resources/js/mercadopagopix.js"></script>
    <script src = "/resources/js/checkmercadopagopixpayment.js"></script>
    <script src = "/resources/js/mercadopagopixcopy.js"></script>
</body>
</html>
</body>
</html>