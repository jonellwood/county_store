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

function renderOrderDetails(data) {
    // console.log("Data in render order details")
    // console.log(data);
    const nonOrderedStatuses = data.filter(item => item.status !== 'Ordered');
    const allOrdered = nonOrderedStatuses.length === 0;
    const allPendingStatuses = data.filter(item => item.status !== 'Pending');
    const allPending = allPendingStatuses.length === 0;
    
    // console.log('All Ordered: ', allOrdered);
    // console.log('All Pending: ', allPending);
    var hasVendorID3 = data.some(item => item.vendor_id === 3);

    var department = data[0].department;
    // console.log('department is :' + department);
    var empID = data[0].emp_id;
    var totalCost = 0
    var totalCount = 0
    var html = '';
    html += `
        <div class='main-order-info-holder w-100'>
            <div class='stats-key-holder'>   
                <span class='w-20 me-2 Pending'><p class='status-pills'>P</p> = Pending</span>
                <span class='w-20 me-2 Approved'><p class='status-pills'>A</p> = Approved</span>
                <span class='w-20 me-2 Denied'><p class='status-pills'>D</p> = Denied</span>
                <span class='w-20 me-2 Ordered'><p class='status-pills'>O</p> = Ordered</span>
                <span class='w-20 me-2 Received'><p class='status-pills'>R</p> = Received</span>
                <span class='w-20' id='totals-button'></span>
            </div>
        `

            if (allOrdered) {
                html +=
                    `<span class='table-title'>Order Details for ${data[0].rf_first_name} ${data[0].rf_last_name} 
                    <button class='btn btn-receive' value='${data[0].order_id}' popovertarget='receive-whole-order-confirm' popoveraction='show'> Receive Whole Order </button>`
            } else if (allPending) {
                html +=
                    `<span class='table-title'>Order Details for ${data[0].rf_first_name} ${data[0].rf_last_name}<button class='btn btn-approve' value='${data[0].order_id}' popovertarget='action-jackson' onclick="createWholeOrderActionPopover(${data[0].order_id})"> Approve or Deny Order </button>`
                if (hasVendorID3) {
                    html += `<button class='btn btn-secondary gen-po-button' onclick='genPOreq(${data[0].order_id})'>Gen PO Request</button>`
                }
            } else {
                html += `<span class='table-title'>Order Details for ${data[0].rf_first_name} ${data[0].rf_last_name}`
                if (hasVendorID3) {
                    html += `<button class='btn btn-secondary gen-po-button' onclick='genPOreq(${data[0].order_id})'>Gen PO Request</button>`
                }
            }
    
            html += `</span>
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
                        <th class='thead-dark'>PO required</th>
                        <th class='thead-dark'>Image</th>
                    </tr>
                </thead>
                <tbody>`
            for (var j = 0; j < data.length; j++) {
                totalCost += data[j].line_item_total;
                totalCount += parseInt(data[j].quantity);
                
                html += `<tr class='${data[j].status}' onclick="setLineItemSession(${data[j].order_details_id}, '${data[j].status}', ${data[j].department})"'>
                            <td class="${data[j].status}"> 
                                <p class="status-pills">${firstChar(data[j].status)} </p>
                            </td>
                            <td>${data[j].quantity ? data[j].quantity : ''}</td>
                            <td colspan=3>${data[j].product_code ? data[j].product_code : ''}</td>
                            <td>${data[j].size_name ? data[j].size_name : ''}</td>
                            <td>${data[j].color_name ? data[j].color_name : ''}</td>
                            <td>${data[j].line_item_total ? money_format(data[j].line_item_total) : '' }</td>
                            <td><img src=../../${data[j].logo ? data[j].logo : ''} alt=${data[j].product_code ? data[j].product_code : ''} class='small-logo-img'/></td>
                            <td>${data[j].dept_patch_place ? data[j].dept_patch_place : 'N/A'}</td>
                            <td>${data[j].status ? data[j].status : ''}</td>`
                if (data[j].vendor_id == '3') {
                    html += `<td class='center-text'>Yes</td>`
                } else {
                    html += `<td class='center-text'> No </td>`
                }
                html += `<td id='prod_img_${j}'><img class='img prod_img' src=../../product-images/${ data[j].color_name ? formatColorValueForUrl(data[j].color_name) : ''}_${data[j].product_code ? formatValueForUrl(data[j].product_code) : ''}.jpg alt='..' width='50px'/></td></tr>`;                                                 
                
            }
    html += `</tbody>
            </table>
            <p class='tiny-text'>&#128161; Interact with a specific line by selecting it</p>
            <p class='tiny-text'>+  Indicates request was submitted in the previous fiscal year.</p>
            <div class='orderSummary'>
            <div class='total' id='orderTotal'></div>
            <div class='itemTotal' id='itemTotal'></div>
            </div>
        </div>
    <div id='spend-summary'></div>`

    var orderTotal = `<p class='receipt'>Request Total: ${money_format(totalCost)} </p>`
    var itemTotal = `<p class='receipt'>Item Count: ${parseInt(totalCount)}</p>`

    const renderOrderSummary = (orderTotal, itemTotal) => {
        const summaryContainer = document.getElementById('orderTotal');
        summaryContainer.innerHTML = `<p class='receipt'>Request Total: ${money_format(orderTotal)} </p>`;

        const itemTotalContainer = document.getElementById('itemTotal');
        itemTotalContainer.innerHTML = `<p class='receipt'>Item Count: ${itemTotal}</p>`;
    }

    const renderPopover = (orderTotal, itemTotal) => {
        const popoverValues = document.getElementById('popover-values');
        popoverValues.innerHTML = `
            <p>Confirm your decision for these <b>${itemTotal}</b> items - for a cost of  <b>$${money_format(orderTotal)}</b></p>
            <div class='buttons-in-approval-popover-holder'>
                <button class='confirm-approval-button' value='${order_id}' onclick='approveWholeOrder(this.value)'>Confirm Approval</button>
                <button class='huge-mistake-button' value='${order_id}' onclick='denyWholeOrder(this.value)'>Deny Order</button>
            </div>
        `;
    }

    function renderReceiveButtons() {
        const buttonsHtml = `
            <div class="buttons-in-approval-popover-holder">
                <button class="approve" value="${order_id}" onclick="receiveWholeOrder(this.value)">Receive All</button>
                <button class="deny" popovertarget="receive-whole-order-confirm" popovertargetaction="hide">Nope, I'm out of here</button>
            </div>
        `;
        document.getElementById('receive-popover-btns').innerHTML = buttonsHtml;
    }
            // renderPopover();
    if (data[0].status == 'Ordered') {
        // makeReceiveButtons();
    }
    document.getElementById('details').innerHTML = html;
    document.getElementById('orderTotal').innerHTML = orderTotal;
    document.getElementById('itemTotal').innerHTML = itemTotal;
    
    }
    

