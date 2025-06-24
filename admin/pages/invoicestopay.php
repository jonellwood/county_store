<?php
include "components/commonHead.php";
?>
<script>
    function formatMoney(amt) {
        const dollars = new Intl.NumberFormat('us-EN', {
            style: 'currency',
            currency: 'USD'
        }).format(amt, )
        return dollars
    }

    function formatDate(str) {
        const date = new Date(str);
        const formattedDate = `${date.getMonth() + 1}-${date.getDate()}-${date.getFullYear()}`;
        return formattedDate;
    }

    async function getInvoices() {
        await fetch('./API/getInvoicesToPay.php')
            .then((response) => response.json())
            .then((data) => {
                console.log(data);
                var ohtml = "";
                ohtml += `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="thead-dark">Department</th>
                            <th class="thead-dark">Order Date</th>
                            
                            <th class="thead-dark">PO Number</th>
                            <th class="thead-dark">Total</th>
                            <th class="thead-dark"></th>
                        </tr>
                    </thead>
                    <tbody>
                `;
                for (var i = 0; i < data.length; i++) {
                    if (data[i].order_inst_id != null) {
                        ohtml += `
                        <tr id="${data[i].order_inst_id}">
                            <td>${data[i].dep_name} - ${data[i].order_for_dept ? data[i].order_for_dept : "N/A"}</td>
                            <td>${formatDate(data[i].order_inst_created)}</td>
                            <td>${data[i].po_number ? data[i].po_number : "N/A"}</td>
                            <td>${formatMoney(data[i].total) ? formatMoney(data[i].total) : "N/A"}</td>
                            <td><button class="btn btn-primary" onclick="getOrderDetails('${data[i].order_inst_id}')">View</button></td>
                        </tr>
                        `;
                    } else {
                        console.log('You suck at coding');
                    }
                }
                ohtml += `</tbody></table>`;
                console.log(ohtml);
                document.getElementById("main").innerHTML = ohtml;
            })
    }

    function displayAlert() {
        // var fyData = fiscalYear();
        var html = '';
        html +=
            `<div class="info-banner">
        Everything passes if you learn to hold things lightly.</p>
        </div>`
        document.getElementById('alert-banner').innerHTML = html
    }

    function fillThree() {
        var html = '<h1>hello</h1>';
        document.getElementById('details').innerHTML = html
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
                console.log(data);
                var ohtml = "";
                ohtml += `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="thead-dark">For</th>
                            <th class="thead-dark">Product</th>
                            <th class="thead-dark">Quantity</th>
                            <th class="thead-dark">Size</th>
                            <th class="thead-dark">Color</th>
                            <th class="thead-dark">Item Price</th>
                            <th class="thead-dark">Logo Fee</th>
                            <th class="thead-dark">Tax</th>
                            <th class="thead-dark">Line Item Total</th>
                        </tr>
                    </thead>
                    <tbody>
                `;
                for (var i = 0; i < data.length; i++) {
                    ohtml += `
                        <tr>
                            <td>${data[i].customer_name}</td>
                            <td>${data[i].name}</td>
                            <td>${data[i].quantity}</td>
                            <td>${data[i].size_name}</td>
                            <td>${data[i].color}</td>
                            <td>${formatMoney(data[i].item_price)}</td>
                            <td>${formatMoney(data[i].logo_fee)}</td>
                            <td>${formatMoney(data[i].tax)}</td>
                            <td>${formatMoney(data[i].line_item_total)}</td>
                        </tr>
                        `;
                }
                ohtml += `</tbody></table>`;
                // console.log(ohtml);
                document.getElementById("details").innerHTML = ohtml;
            })
    }


    function doStuff() {
        displayAlert();
        getInvoices();
        // fillThree();
    }

    doStuff();
</script>




<style>
    .parent {
        grid-template-columns: 10% 40% 50%;
        grid-template-rows: none;
    }

    .div2 {
        display: grid;
        grid-template-columns: 1fr;
        /* width: 70%; */
        grid-area: 2/2/5/2;
        scrollbar-gutter: stable;
        background-image: conic-gradient(from 127deg at 0% 100%,
                #00d5ff 47% 47%, #aa92ff 101% 101%);
    }

    .div3 {
        /* background-color: #aa92ff; */
        /* grid-area: 2/2/3/2; */
    }

    .info-banner {
        padding-left: 20px;
        padding-right: 20px;
    }

    .active td {
        background-color: dodgerblue;
    }
</style>