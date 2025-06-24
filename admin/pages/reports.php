<?php
// Created: 2024/08/28 14:29:00
// Last Modified: 2024/09/26 10:15:27
include('DBConn.php');
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: pages/sign-in.php");
    exit;
}

if (!isset($_SESSION["role_id"]) && $_SESSION["role_id"] !== 1) {
    header("Location: 401.php");
    exit;
}

include "components/commonHead.php";
?>
<script src="functions/helpers.js"></script>
<script>
    function formatDate(inputDate) {
        const dateObject = new Date(inputDate);
        const month = String(dateObject.getMonth() + 1).padStart(2, "0");
        const day = String(dateObject.getDate()).padStart(2, "0");
        const year = dateObject.getFullYear();

        return `${month}-${day}-${year}`;
    }
</script>
<!-- <script src="functions/renderDepartmentOrdersList.js"></script> -->
<script>
    async function getPastReports() {
        await fetch('./past-reports-get.php')
            .then((response) => response.json())
            .then((data) => {
                console.log(data);
                let reportTable =
                    `<table class='table table-striped' id='reportsTable'>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Dept</th>
                            <th>Amount</th>
                            <th width='2em'></th>
                        </tr>
                    </thead><tbody>`;
                for (var i = 0; i < data.length; i++) {
                    reportTable += `
                                        <tr>
                                            <td>${formatDate(data[i].order_placed)}</td> 
                                            <td>${data[i].dep_name}</td>
                                            <td>${money_format(data[i].total)}</td>
                                            <td class="shift-right"><a href='vendorReport.php?uid=${data[i].order_inst_id}' target='_blank'>&#129531;</a></td>
                                        </tr>
                                    `;
                }
                reportTable += '</tbody></table>'
                var reportContainer = document.getElementById("main");
                reportContainer.innerHTML = reportTable;
            })
    }
    getPastReports();
</script>


<div id="div2">
    <div id="main"></div>
</div>

</body>

</html>
<script>
    function displayAlert() {
        var fyData = fiscalYear();
        var html = '';
        html +=
            `<div class="info-banner">
            🚨 WARNING 🚨
            You've stumbled upon a placeholder banner! 🚀
            We’re currently out of witty remarks and high-quality content. Please stand by while we recharge our creativity. In the meantime, feel free to practice your interpretive dance moves or take a nap. We’ll be back with something fantastic soon! 🌟</p>
        </div>`
        document.getElementById('alert-banner').innerHTML = html
    }
    displayAlert();
</script>
<style>
    .info-banner {
        padding-left: 20px;
        padding-right: 20px;
    }

    .parent {
        display: grid;
        grid-template-columns: 10% 90%;
        grid-template-rows: 75px 1fr 1fr;
        height: 100vh;
    }

    .div1 {
        display: flex;
        grid-area: 1 / 1 / 4 / 1;

    }

    .div2 {
        display: grid;
        grid-template-columns: 1fr;
        grid-area: 2 / 2 / 5 / 5;
        scrollbar-gutter: stable;
        padding-left: 20px;
        padding-right: 20px;
        background-image:
            conic-gradient(from 127deg at 0% 100%,
                #00d5ff 47% 47%, #aa92ff 101% 101%);
    }

    .div3 {
        display: none;
        grid-area: 2 / 3 / 2 / 3;
        height: 100vh;
        scrollbar-gutter: stable;
        padding-left: 20px;
        overflow-y: auto;
        border-top: 3px solid #80808050;
        border-left: 3px solid #80808050;
        border-bottom: 3px solid #80808050;

    }

    .div4 {
        display: flex;
        grid-area: 1 / 2 / 1 / 5;
    }

    .div5 {
        display: flex;
        overflow-y: auto;
        scrollbar-gutter: stable;
    }

    .div6 {
        display: none;
        flex-direction: column;
        grid-area: 2 / 4 / 3 / 4;
        border-top: 3px solid #80808050;
        border-right: 3px solid #80808050;
        border-bottom: 3px solid #80808050;
    }

    .shift-right {
        padding-right: 40px !important;
    }

    #past-reports {
        width: 90% !important;
    }
</style>