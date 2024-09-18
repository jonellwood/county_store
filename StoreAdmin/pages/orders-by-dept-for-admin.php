<?php
include('DBConn.php');
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

    exit;
}
if (!isset($_SESSION["role_id"]) && $_SESSION["role_id"] !== 1) {
    header("Location: 401.php");
    exit;
}
include "components/commonHead.php";

?>
<script src="functions/helpers.js"></script>
<script src="functions/renderDepartmentOrdersList.js"></script>
<script>
    async function getApprovedList() {
        await fetch('./orders-by-dept-for-admin-get.php')
            .then((response) => response.json())
            .then((data) => {
                // console.log(data);
                renderDepartmentOrdersList(data, 'main')
            })
    }
    getApprovedList();
    async function getPastReports() {
        await fetch('./past-reports-get.php')
            .then((response) => response.json())
            .then((data) => {
                // console.log(data);
                let reportTable =
                    `<table class='styled-table' id='reportsTable'>
                        <caption>Vendor reports</caption>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Dept</th>
                                <th width='2em'></th>
                            </tr>
                        </thead>`;
                for (var i = 0; i < data.length; i++) {
                    reportTable += `<tbody>
                                            <tr>
                                                <td>${formatDate(data[i].order_inst_created)}</td>
                                                <td>${data[i].dep_name}</td>
                                                <td><a href='vendorReport.php?uid=${data[i].order_inst_id}'>&#129531;</a></td>
                                            </tr>
                                        </tbody>`;
                }
                reportTable += '</table>'
                var reportContainer = document.getElementById("past-reports");
                reportContainer.innerHTML = reportTable;
            })
    }
    // getPastReports();
</script>


<div class="div2">
    <div id="main"></div>
    <!-- <pre>
        </?php
        print_r($_SESSION);
        ?>
    </pre> -->

</div>



</body>

</html>
<script>
    function displayAlert() {
        var fyData = fiscalYear();
        var html = '';
        html +=
            `<div class="info-banner">
            Whereas, it is both fitting and proper to acknowledge and celebrate individuals of such extraordinary caliber, Now, Therefore, be it proclaimed that Sherry is hereby recognized as the epitome of excellence and the best ever in her pursuits. Her remarkable attributes and accomplishments shall serve as a standard of distinction for all to aspire to.</p>
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



    #main {
        overflow-y: auto;
    }



    .order-button {
        position: relative;
        z-index: 3;
    }

    .order-button:hover {
        transform: scale(.95);
        box-shadow: 0px 0px 0px 0px rgba(0, 0, 0, 0.75), inset 0px 0px 5px 1px rgba(201, 189, 201, 1);
        cursor: pointer;
    }

    .confirm-approval-button {
        margin-top: 10px;
        border-color: #009879;
    }



    #pobutton {
        background-color: hotpink;
        height: 100px;
        width: 100px;

    }



    .po-enter-popover {

        width: 20vw;
        height: 20vw;
        background-color: #ffd592;

    }

    .place-order-button {
        margin-top: 10%;
    }

    .popover-top-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        align-content: end;
        justify-content: space-between;
        padding-left: 10px;
        padding-right: 10px;
        background-color: #ffc566;
        height: 3vw;
        font-family: cursive;
        font-size: x-large;

    }

    .po-enter-popover form {
        padding: 20px;
    }

    @media print {

        .parent,
        button {
            display: none;
        }

        #po-req {
            margin: 10px;
            border: none;
            width: fit-content;
        }

        .hide-from-printer {
            display: none;
        }
    }

    .dept-orders-table {
        box-shadow: 0 0 20px 4px #00000050;
        margin: 15px;
        margin-bottom: 20px;
    }

    .dept-table-list-header {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        padding-right: 40px;
        width: 100%;
        background-color: #1e242b;
        color: #fff;
    }

    .dept-table-list-title {
        font-size: 1.5rem !important;
    }

    .table-footer-row {
        /* display: flex;
        justify-content: flex-end; */
        background-color: #1e242b;

        /* td {
            background-color: #1e242b;
        } */
    }

    .table-footer-td {
        text-align: right;
        /* background-color: #f300ff !important; */
        background-color: #1e242b !important;

        button {
            float: right;
            margin-right: 40px;
        }
    }

    .table {
        margin-bottom: 0 !important;
    }
</style>