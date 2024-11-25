
async function createNewPixPayment(paymentID,PaymentAmmount,paymentDescription,paymentAccessToken,paymentEmail){
    try{
        const createNewPix = await fetch("/payment/mercadopagopix/create", {
            method: "POST",
            body: JSON.stringify({
                paymentId:paymentID,
                paymentAccessToken:paymentAccessToken,
                paymentEmail:paymentEmail,
                paymentValue:PaymentAmmount, 
                paymentDescription:paymentDescription 
            }),
            headers: {"Content-type": "application/json; charset=UTF-8"}  
        })            
        const getPixData = await createNewPix.json()
        return getPixData 
    }catch(error){
        if (error instanceof SyntaxError) {
            location.replace(`/payment/mercadopagopix/failed?payment_id=${paymentID}`)
        } else {
            return {code:4513,message:"payment not created",data:null}
        }     
    }
}
