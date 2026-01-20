<?php
include "../../components/header.php";
?>
<link href="orders-to-be-received.css" rel="stylesheet" />

<div class="admin-dashboard-container">
    <!-- Alert Banner -->
    <div class="alert-banner" id="alert-banner">
        <i class="fas fa-truck"></i>
        <span>Orders To Be Received - Manage incoming department orders</span>
    </div>

    <!-- Main Content Area -->
    <div class="main-content main-list-holder">
        <div id="main">Loading orders...</div>
    </div>

    <!-- Details Panel (optional, for future expansion) -->
    <div class="details-panel" id="details" style="display:none;"></div>
</div>

<!-- Popovers -->
<div id="whole-order-confirm" popover=manual>
    <button class="close-btn" popovertarget="whole-order-confirm" popovertargetaction="hide">
        <span aria-hidden="true">❌</span>
        <span class="sr-only">Close</span>
    </button>
    <span id="confirm-details-holder">
        <br>
        <p id="whole-order-popover-values"></p>
    </span>
</div>
<div id="single-item-confirm" popover=manual>
    <button popovertarget="single-item-confirm" popovertargetaction="hide">
        <span aria-hidden="true">❌</span>
        <span class="sr-only">Cancel</span>
    </button>
    <span id="confirm-details-holder">
        <br>
        <p id="single-item-popover-values"></p>
    </span>
</div>

<script>
    function displayAlert() {
        var html = '';
        html +=
            `<div class="info-banner">
            It's gonna be a lovely day.
        </div>`
        document.getElementById('alert-banner').innerHTML = html
    }
    displayAlert();

    async function getOrders() {
        await fetch('./orders-to-be-received-get.php')
            .then((response) => response.json())
            .then((data) => {
                const groupedByDepartment = data.reduce((acc, entry) => {
                    const departmentNumber = entry.dep_name;
                    if (!acc[departmentNumber]) acc[departmentNumber] = [];
                    acc[departmentNumber].push(entry);
                    return acc;
                }, {});

                var tableHolder = document.getElementById('main');
                tableHolder.innerHTML = '';

                Object.keys(groupedByDepartment).forEach(departmentNumber => {
                    var table = document.createElement('table');
                    table.classList.add('styled-table');
                    table.innerHTML = `
                        <caption><i class='fas fa-building'></i> ${departmentNumber}</caption>
                    `;
                    tableHolder.appendChild(table);

                    // Group entries by order_id within each department
                    const groupedByOrderId = groupedByDepartment[departmentNumber].reduce((acc, entry) => {
                        const orderId = entry.order_id;
                        if (!acc[orderId]) acc[orderId] = [];
                        acc[orderId].push(entry);
                        return acc;
                    }, {});

                    Object.keys(groupedByOrderId).forEach(orderId => {
                        var orderTable = document.createElement('table');
                        orderTable.classList.add('styled-table');
                        const firstEntry = groupedByOrderId[orderId][0];
                        if (firstEntry) {
                            const tr = document.createElement('tr');
                            tr.classList.add('orderIDCaption');
                            tr.innerHTML = `
                                <td colspan='4'>
                                    <i class='fas fa-file-invoice-dollar'></i> Order #: ${firstEntry.order_id} for ${firstEntry.rf_first_name} ${firstEntry.rf_last_name} for total of <span class='amount'>${new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(firstEntry.grand_total)}</span>
                                </td>
                                <td></td>
                                <td></td>
                                <td colspan='2'>
                                    <button class="btn btn-primary" popovertarget="whole-order-confirm" popovertargetaction="show" onclick="createWholeOrderPopoverContent(${orderId})"><i class="fas fa-box-open"></i> Receive Entire Order</button>
                                <td>
                            `;
                            orderTable.appendChild(tr);
                        }
                        tableHolder.appendChild(orderTable);

                        const groupedByOrderDetailsId = groupedByOrderId[orderId].reduce((acc, entry) => {
                            const orderDetailsId = entry.order_details_id;
                            if (!acc[orderDetailsId]) acc[orderDetailsId] = [];
                            acc[orderDetailsId].push(entry);
                            return acc;
                        }, {});

                        Object.keys(groupedByOrderDetailsId).forEach(orderDetailsId => {
                            var orderLineTable = document.createElement('tr');
                            orderLineTable.innerHTML = `
                                ${groupedByOrderDetailsId[orderDetailsId].map(entry => `
                                    <tr> 
                                        <td width=10%>${entry.quantity}</td>
                                        <td width=30%>${entry.product_name}</td>
                                        <td width=10%>${entry.size_name}</td>
                                        <td width=10%>${entry.color_id}</td>
                                        <td width=10%><img src='../../${entry.logo}' alt='logo' style='width:32px;height:32px;border-radius:6px;background:#eee;'/></td>
                                        <td width=10%>${entry.dept_patch_place}</td>
                                        <td>
                                            <button class="btn btn-primary" popovertarget="single-item-confirm" popovertargetaction="show" onclick="createPopoverContent(${entry.order_details_id})">
                                                <i class="fas fa-box"></i> Receive Item (#${entry.order_details_id})
                                            </button>
                                        </td>
                                    </tr>
                                `).join('')}
                            `;
                            orderTable.appendChild(orderLineTable);
                        });
                    });
                });
            });
    }
    getOrders();

    async function receiveItem(orderDetailsId) {
        await fetch('./API/receiveSingleItemAdmin.php?id=' + orderDetailsId)
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    window.location.replace('orders-to-be-received.php');
                }
            })
    }
    async function receiveWholeOrder(orderId) {
        await fetch('./API/receiveWholeOrderAdmin.php?id=' + orderId)
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    window.location.replace('orders-to-be-received.php');
                }
            })
    }

    function createPopoverContent(id) {
        var html = '';
        html += '<hr><div><p>Are you sure you want to receive this item?</p>';
        html += '<button class="btn btn-primary" onclick="receiveItem(\'' + id + '\')" popovertarget="single-item-confirm" popovertargetaction="hide"><i class="fas fa-check"></i> Yes</button></div>';
        document.getElementById('single-item-popover-values').innerHTML = html;
    }

    function createWholeOrderPopoverContent(id) {
        var html = '';
        html += '<hr><div><p>Are you sure you want to receive all items in this order?</p>';
        html += '<button class="btn btn-primary" onclick="receiveWholeOrder(\'' + id + '\')" popovertarget="whole-order-confirm" popovertargetaction="hide"><i class="fas fa-check-double"></i> Yes</button></div>';
        document.getElementById('whole-order-popover-values').innerHTML = html;
    }
</script>
