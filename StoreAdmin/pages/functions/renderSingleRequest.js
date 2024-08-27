function formatColorValueForUrl(str) {
    var noSpaces = str.replace(/[\s/]/g, '');
    var lowercaseString = noSpaces.toLowerCase();
    return lowercaseString;
}
function formatValueForUrl(str) {
    return str.toLowerCase();
}
function firstChar(str) {
    return str[0];
}

function renderSingleRequest(target, data) { 
    console.log(data);
    var html = `
     <div class='main-order-info-holder w-100'>
        <table class='table table-striped'>
                <thead>
                    <tr>
                        <th class='thead-dark text-end' colspan=2>Qty</th>
                        <th class='thead-dark' colspan=3>Product </th>
                        <th class='thead-dark'>Size</th>
                        <th class='thead-dark'>Color</th>
                        <th class='thead-dark'>Total</th>
                        <th class='thead-dark'>Logo</th>
                        <th class='thead-dark'>Dept Placement</th>
                        <th class='thead-dark'>Status</th>
                        <th class='thead-dark'>Image</th>
                    </tr>
                </thead>
                <tbody>
                <tr class='${data[0].status}'>
                            <td class="${data[0].status}"> 
                                <p class="status-pills">${firstChar(data[0].status)} </p>
                            </td>
                            <td id='currentQuantity'>${data[0].quantity ? data[0].quantity : ''}</td>
                            <td id='currentProductCode' colspan=3>${data[0].product_code ? data[0].product_code : ''}</td>
                            <td id='currentSize'>${data[0].size_name ? data[0].size_name : ''}</td>
                            <td id='currentColor'>${data[0].color_name ? data[0].color_name : ''}</td>
                            <td id='currentTotal'>${data[0].line_item_total ? money_format(data[0].line_item_total) : '' }</td>
                            <td id='currentLogo' data-value=${data[0].logo_id ? data[0].logo_id : ''} data-url=${data[0].logo ? data[0].logo : ''}><img src=../../${data[0].logo ? data[0].logo : ''} alt=${data[0].product_code ? data[0].product_code : ''} class='small-logo-img'/></td>
                            <td id='currentDeptPlacement'>${data[0].dept_patch_place ? data[0].dept_patch_place : 'N/A'}</td>
                            <td>${data[0].status ? data[0].status : ''}</td>
                            <td><img class='img prod_img' src=../../product-images/${data[0].color_name ? formatColorValueForUrl(data[0].color_name) : ''}_${data[0].product_code ? formatValueForUrl(data[0].product_code) : ''}.jpg alt='..' width='50px'/></td></tr>
                            </tbody>
            </table>
            <p class='hidden' id='currentBillTo' data-billto='${data[0].bill_to_dept ? data[0].bill_to_dept : ''}'></p>
            <p class='hidden' id='currentLogoFee' data-logofee='${data[0].logo_fee ? data[0].logo_fee : ''}'></p>
            <p class='hidden' id='currentTax' data-tax='${data[0].tax ? data[0].tax : ''}'></p>
            <p class='hidden' id='currentItemPrice' data-itemprice='${data[0].item_price ? data[0].item_price : ''}'></p>
            <p class='hidden' id='productType' data-type='${data[0].product_type ? data[0].product_type : ''}'></p>
            </div>
    `
    document.getElementById(target).innerHTML = html;
}
//  <tr class='${data[0].status}' onclick="setLineItemSession(${data[0].order_details_id}, '${data[0].status}')"'>