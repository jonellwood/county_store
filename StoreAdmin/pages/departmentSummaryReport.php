<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: sign-in.php");

    exit;
}
if (!isset($_SESSION["role_id"]) && $_SESSION["role_id"] !== 1) {
    header("Location: 401.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="../../build/style.max.css" rel="stylesheet" />
    <link rel="stylesheet" href="prod-admin-style.css">


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
                    let dhtml = '<select id="department" name="department" onchange="departmentSummary(this.value)">';
                    for (let i = 0; i < data.length; i++) {
                        dhtml += '<option value="' + data[i].department + '">' + data[i].dep_name + '- (' + data[i].department + ')</option>';
                    }
                    dhtml += '</select>';
                    dhtml += '<p>Select a Department to generate report</p>';
                    document.getElementById('department-select').innerHTML = dhtml;
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
                html += `<span class='table-title'>FY ${years.current_fy_start_year} - ${years.current_fy_end_year} Summary</span>`;
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

</head>

<body>
    <div class="parent">
        <div class="div1">
            <?php include('hideNav.php'); ?>
        </div>
        <div class="div2">
            <div class="report-data-holder">
                <div class="table-container" id="table-container"></div>
                <div class="other-table-container" id="other-table-container"></div>
            </div>
        </div>
        <div class="div4">
            <!-- Select mene will render here -->
            <div id="department-select" class="department-select">
            </div>
        </div>

</body>

</html>
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

    .div2 {
        grid-area: 2/2/2/6 !important;
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

        p {
            padding-left: 15px;
            padding-right: 15px;
        }
    }

    .parent {
        background-color: #00000070;
    }
</style>