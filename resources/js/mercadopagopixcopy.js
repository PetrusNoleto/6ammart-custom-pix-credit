const MercadoPagoPixCopyQrCode  = async()=>{
    let qrcodeUrlCopy = document.getElementById("paymentPixCopyButton");
    try {
    await navigator.clipboard.writeText(qrcodeUrlCopy.value);
    console.log('Content copied to clipboard');
    qrcodeUrlCopy.textContent = "codigo copiado";
    if(qrcodeUrlCopy.textContent === "codigo copiado"){
        setTimeout(()=>{
            qrcodeUrlCopy.textContent = "copiar codigo de pagamento"
        },3000)
    }
    } catch (err) {
    console.error('Failed to copy: ', err);
    }
}