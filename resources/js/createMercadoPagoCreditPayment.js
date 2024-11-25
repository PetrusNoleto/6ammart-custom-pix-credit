
async function createNewCreditPayment(paymentId,paymentAccessToken,paymentEmail,paymentValue,paymentDescription,paymentToken,paymentIssuerId,paymentMethodId,paymentInstallments,paymentIdentificationType,paymentIdentificationNumber){
    try{
        const createNewPayment = await fetch("/payment/mercadopagocredit/create/", {
            method: "POST",
            body: JSON.stringify({
                paymentId,
                paymentAccessToken,
                paymentEmail,
                paymentValue,
                paymentDescription,
                paymentToken,
                paymentIssuerId,
                paymentMethodId,
                paymentInstallments,
                paymentIdentificationType,
                paymentIdentificationNumber
            }),
            headers: {"Content-type": "application/json; charset=UTF-8"}  
        })    
        console.log(createNewPayment)        
        const getPixData = await createNewPayment.json()
        console.log(getPixData)
        return getPixData 
    }catch(error){
        if (error instanceof SyntaxError) {
            console.log(error)
            // location.replace(`/payment/mercadopagopix/failed?payment_id=${paymentID}`)
        } else {
            return {code:4513,message:"payment not created",data:null}
        }     
    }
}
