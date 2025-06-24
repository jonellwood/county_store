<?php
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

<!-- <div class="parent"> -->
<!-- <div class="div1">
            </?php include('hideNav.php'); ?>
        </div> -->
<div class="div2">
    <div id="department-select" class="department-select"></div>

    <!-- <div class="report-data-holder">
            <div class="table-container" id="table-container"></div>
            <div class="other-table-container" id="other-table-container"></div>
        </div> -->

    <center>
        <button class="hide-from-printer" onclick="printPage()" type="submit" value="Print" role="button" id="btn">Click
            Here to Print Report</button>
    </center>
    <script>
        function printPage() {
            // var sideMenu = document.querySelector(".sb-sidenav").classList.add('hide-from-printer');
            // var deptSelect = document.querySelector(".department-select").classList.add('hide-from-printer');
            // var reportHolder = document.querySelector(".report-data-holder").classList.add('single-column');

            window.print();
        }
    </script>

    </body>

    </html>
    <script>
        function currencyFormat(number) {
            const currency = number.toLocaleString('en-US', {
                style: 'currency',
                currency: 'USD'
            });
            return currency;
        }

        function getDepartments() {
            fetch('fetch-departments.php')
                .then(response => response.json())
                .then(data => {
                    let dhtml =
                        '<div class="department-select"><select id="department" name="department" onchange="departmentSummary(this.value)">';
                    for (let i = 0; i < data.length; i++) {
                        dhtml += '<option value="' + data[i].department + '">' + data[i].dep_name + '- (' + data[i]
                            .department + ')</option>';
                    }
                    dhtml += '</select></div>';
                    dhtml += '<p>Select a Department to generate report</p>';
                    dhtml += `<div class="report-data-holder"> 
                            <div class="table-container"
                                id="table-container"> 
                            </div> 
                            <div class="other-table-container"
                                id="other-table-container"> 
                                </div> 
                            </div>`
                    var target = document.getElementById('main')
                    // main.classList.add('department-select');
                    main.innerHTML = dhtml;
                })
        }
        getDepartments();

        async function departmentSummary(dep) {
            try {
                const response = await fetch('fetchDepartmentSummaryReportData.php?dept=' + dep);
                const data = await response.json();

                const years = data[2];
                let html = '';

                // Build FY table header
                html +=
                    `<span class='table-title'>FY ${years.current_fy_start_year} - ${years.current_fy_end_year} Summary</span>`;
                html += `<table><tr class='table-head-row'><th>Employee Name</th><th>Employee ID</th></tr>`;

                // Process current fiscal year data
                for (const empId in data[0]) {
                    const employee = data[0][empId];
                    const numOrders = employee.totals.length;

                    // Build employee row (combined name and ID based on order count)
                    html += `
                        <tr class='name-row'>
                            <td>${employee.name}</td>
                            <td>${employee.emp_id}</td>
                        </tr>
                    `;

                    // Loop through employee's requests
                    for (let i = 0; i < numOrders; i++) {
                        const order = employee.totals[i];
                        html += `
                        <tr class='data=row''>
                            <td>${order.status}</td>
                            <td>${currencyFormat(order.total_line_item_total)}</td>
                        </tr>
                    `;
                    }
                }

                html += `</table>`; // Close FY table

                // Similar logic for previous fiscal year data (replace table container ID)
                const previousFyHtml = buildPreviousFyTable(data[1], years);

                document.getElementById('table-container').innerHTML = html;
                document.getElementById('other-table-container').innerHTML = previousFyHtml;
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        }

        // Helper function to build the previous FY table 
        function buildPreviousFyTable(previousFyData, years) {
            let html = '';
            html += `<span class='table-title'>FY ${years.prev_fy_start_year} - ${years.prev_fy_end_year} Summary</span>`;
            html += `<table><tr class='table-head-row'><th>Employee Name</th><th>Employee ID</th></tr>`;

            for (const empId in previousFyData) {
                const employee = previousFyData[empId];
                const numOrders = employee.totals.length;

                html += `
                    <tr class='name-row'>
                        <td>${employee.name}</td>
                        <td>${employee.emp_id}</td>
                    </tr>
                `;

                for (let i = 0; i < numOrders; i++) {
                    const order = employee.totals[i];
                    html += `
                    <tr class='data-row'>
                        <td>${order.status}</td>
                        <td>${currencyFormat(order.total_line_item_total)}</td>
                    </tr>
                `;
                }
            }

            html += `</table>`;
            return html;
        }


        // departmentSummary('41515');
    </script>
    <script>
        function displayAlert() {
            var html = '';
            html +=
                `<div class="info-banner">
            <p>What have I become? My sweetest friend.</p>
            <p>Everyone I know goes away in the end.</p>
        </div>`
            document.getElementById('alert-banner').innerHTML = html
        }
        displayAlert();
    </script>
    <style>
        .report-data-holder {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
            justify-content: space-evenly;
            align-content: flex-start;
            text-wrap: balance;
            font-size: max(1.25vw, 14px);
            text-align: center;
            padding: 10px;
            padding-top: 20px;
            width: 100%;
            background-color: #FFFFFF90;

            table {
                width: 100%;
            }

            table tr {
                width: 100%;
                background-color: #FFFFFF;
            }
        }

        .table-head-row {
            /* font-size: larger; */
            width: 100%;
            text-align: start;
        }

        .table-title {
            display: flex;
            justify-content: space-evenly;
            align-content: flex-start;
            text-wrap: balance;
            font-size: max(1.25vw, 14px);
            text-align: center;
            padding-top: 20px;
            width: auto;
            background-color: #FFFFFF;
            font-size: larger;
        }

        .tbody {
            border-bottom: 5px solid hotpink;
        }

        .name-row {
            background-color: #005700 !important;
            color: #FFFFFF;
            text-align: start;

            td {
                padding-left: 15px;
            }
        }



        .data-row {
            text-align: end;

            td {
                padding-right: 15px;

            }
        }

        /* .div2 {
        grid-area: 2/2/2/6 !important;
    } */
        .info-banner {
            padding-left: 20px;
            padding-right: 20px;
        }

        .parent {
            display: grid;
            grid-template-columns: 10% 90%;
            grid-template-rows: 75px 1fr 1fr;
            height: 100vh;
            /* overflow: hidden; */
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
            /* grid-area: 2/ 4 / 2 /4; */
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

        .department-select {
            display: flex;
            justify-content: space-evenly;
            align-content: baseline;
            text-wrap: balance;
            font-size: max(1.25vw, 14px);
            text-align: center;
            padding-top: 20px;
            width: auto;
            background-color: #FFFFFF;
            font-size: larger;
            height: fit-content;
            padding-bottom: 20px;

            p {
                padding-left: 15px;
                padding-right: 15px;
            }
        }

        .parent {
            background-color: #00000070;
        }

        @media print {

            /* hide the print button when printing */
            .hide-from-printer {
                display: none;
            }

            body {
                width: 2500px;
                font-size: 12px;
            }

            img {
                display: block;
                z-index: 2;
                margin-left: 20px;
                height: 100px;

            }

            .report td {
                margin-top: 10px;
            }

            .report-data-holder {
                grid-template-columns: 1fr;
            }

            .department-select {
                display: none;
            }

            .sb-sidenav {
                display: none;
            }
        }
    </style>