async function checkMercadoPagoPixPayment(paymentId,paymentAccessToken){
    try{
        const checkPix = await fetch("/payment/mercadopagopix/check", {
            method: "POST",
            body: JSON.stringify({
                paymentId:paymentId,
                paymentAccessToken:paymentAccessToken
            }),
            headers: {"Content-type": "application/json; charset=UTF-8"}  
        })            
        const getPixData = await checkPix.json()
        return getPixData 
    }catch(error){
        if (error instanceof SyntaxError) {
            location.replace(`/payment/mercadopagopix/failed?payment_id=${paymentId}`)
        } else {
            return {code:4513,message:"payment not checked",data:null}
        }     
    }
}