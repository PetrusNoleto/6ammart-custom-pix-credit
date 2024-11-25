function setPixPageImage(image,name){
    let getBusinessDataImg = document.getElementById("businessBrandImg");
    const restaurantImageAddress = `/storage/app/public/store/${image}`;
    const restaurantAlt = `image do fornecedor/${name}`;
    getBusinessDataImg.setAttribute("src", restaurantImageAddress);
    getBusinessDataImg.setAttribute("alt", restaurantAlt);
}