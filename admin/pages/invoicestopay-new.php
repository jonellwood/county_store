<?php
include "../../components/header.php";
?>
<link href="invoicestopay.css" rel="stylesheet" />

<div class="admin-dashboard-container">
    <!-- Alert Banner -->
    <div class="alert-banner" id="alert-banner">
        <i class="fas fa-file-invoice-dollar"></i>
        <span>Invoices To Pay - Review and process outstanding invoices</span>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="main">
        <!-- Table will be rendered by JS -->
    </div>

    <!-- Details Panel -->
    <div class="details-panel" id="details">
        <!-- Details will be rendered by JS -->
    </div>
</div>

<script>
    function formatMoney(amt) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amt);
    }

    function formatDate(str) {
        const date = new Date(str);
        return `${date.getMonth() + 1}-${date.getDate()}-${date.getFullYear()}`;
    }

    async function getInvoices() {
        await fetch('./API/getInvoicesToPay.php')
            .then((response) => response.json())
            .then((data) => {
                let ohtml = "";
                ohtml += `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Order Date</th>
                            <th>PO Number</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                `;
                for (let i = 0; i < data.length; i++) {
                    if (data[i].order_inst_id != null) {
                        ohtml += `
                        <tr id="${data[i].order_inst_id}">
                            <td><span class="dept-badge">${data[i].dep_name}</span> <span class="dept-num">${data[i].order_for_dept ? data[i].order_for_dept : "N/A"}</span></td>
                            <td>${formatDate(data[i].order_inst_created)}</td>
                            <td>${data[i].po_number ? data[i].po_number : "N/A"}</td>
                            <td class="amount">${formatMoney(data[i].total) ? formatMoney(data[i].total) : "N/A"}</td>
                            <td><button class="btn btn-primary" onclick="getOrderDetails('${data[i].order_inst_id}')"><i class="fas fa-eye"></i> View</button></td>
                        </tr>
                        `;
                    }
                }
                ohtml += `</tbody></table>`;
                document.getElementById("main").innerHTML = ohtml;
            })
    }

    function displayAlert() {
        var html = '';
        html +=
            `<div class="info-banner">
        Everything passes if you learn to hold things lightly.
        </div>`
        document.getElementById('alert-banner').innerHTML = html
    }

    function setActive(id) {
        var activeRows = document.querySelectorAll('tr.active');
        activeRows.forEach(function(row) {
            row.classList.remove('active');
        });
        document.getElementById(id).classList.add('active');
    }

    function getOrderDetails(id) {
        setActive(id);
        fetch(`./API/getOrderDetailsByInstId.php?id=${id}`)
            .then((response) => response.json())
            .then((data) => {
                let ohtml = "";
                ohtml += `
                <table class="table">
                    <thead>
                        <tr>
                            <th>For</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Size</th>
                            <th>Color</th>
                            <th>Item Price</th>
                            <th>Logo Fee</th>
                            <th>Tax</th>
                            <th>Line Item Total</th>
                        </tr>
                    </thead>
                    <tbody>
                `;
                for (let i = 0; i < data.length; i++) {
                    ohtml += `
                        <tr>
                            <td>${data[i].customer_name}</td>
                            <td>${data[i].name}</td>
                            <td>${data[i].quantity}</td>
                            <td>${data[i].size_name}</td>
                            <td>${data[i].color}</td>
                            <td class="amount">${formatMoney(data[i].item_price)}</td>
                            <td class="amount">${formatMoney(data[i].logo_fee)}</td>
                            <td class="amount">${formatMoney(data[i].tax)}</td>
                            <td class="amount">${formatMoney(data[i].line_item_total)}</td>
                        </tr>
                        `;
                }
                ohtml += `</tbody></table>`;
                document.getElementById("details").innerHTML = ohtml;
            })
    }

    function doStuff() {
        displayAlert();
        getInvoices();
    }

    doStuff();
</script>