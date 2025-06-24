<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary Page</title>

    <script>
        function formatDate(inputDate) {
            const dateObject = new Date(inputDate);
            const month = String(dateObject.getMonth() + 1).padStart(2, '0');
            const day = String(dateObject.getDate()).padStart(2, '0');
            const year = dateObject.getFullYear();

            return `${month}-${day}-${year}`;
        }

        function formatAsCurrency(value) {
            return value.toFixed(2);
        }
        async function getOrderData() {
            await fetch('orderSummaryGet.php')
                .then((response) => response.json())
                .then((data) => {
                    console.log(data);

                    const results = [];

                    for (const key in data) {
                        const {
                            order_inst_id,
                            line_item_total,
                            dep_name,
                            item_price,
                            order_inst_created
                        } = data[key];

                        const existingIndex = results.findIndex(item => item.order_inst_id === order_inst_id);
                        if (existingIndex !== -1) {
                            results[existingIndex].total += line_item_total;
                            results[existingIndex].preTax += item_price;
                        } else {
                            results.push({
                                order_inst_id,
                                total: line_item_total,
                                dep_name,
                                preTax: item_price,
                                order_inst_created,
                            });
                        }
                    }

                    console.log(results[0]);
                    // console.log('Item Price');
                    // console.log(results[0].preTax);
                    // console.log('Here comes the dataHolder');
                    // console.log(dataHolder);
                    var html =
                        "<table class='styled-table' id='invoice-table'><thead><tr><th onclick='sortTable(0)'>Department &#9660;</th><th onclick='sortTable(1)'>Date &#9660;</th><th onclick='sortTable(2)'>Total Invoice Amount &#9660;</th><th onclick='sortTable(3)'>Pre Tax Amount &#9660;</th></tr>";
                    html += "<tbody>";
                    for (var i = 0; i < results.length; i++) {
                        html += "<tr><td>" + results[i]
                            .dep_name + "</td><td>" + formatDate(results[i].order_inst_created) + "</td><td>$" +
                            formatAsCurrency(results[i].total) + "</td><td>$" + formatAsCurrency(results[i]
                                .preTax) + "</td></tr>"
                    }
                    html += "</tbody></table>"
                    document.getElementById('sumsTable').innerHTML = html;
                })
        }
        getOrderData();
    </script>




</head>

<body>
    <?php include "topNav.php" ?>
    <div id="sumsTable"></div>
</body>

<script>
    function sortTable(column) {
        var table, rows, switching, i, x, y, shouldSwitch;
        table = document.getElementById('invoice-table');
        switching = true;

        while (switching) {
            switching = false;
            rows = table.rows;

            for (i = 1; i < rows.length - 1; i++) {
                shouldSwitch = false;
                x = rows[i].getElementsByTagName('td')[column];
                y = rows[i + 1].getElementsByTagName('td')[column];

                // Check if the two rows should switch place based on the selected column's content
                if (isNaN(x.innerHTML)) {
                    if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                        shouldSwitch = true;
                        break;
                    }
                } else {
                    if (parseInt(x.innerHTML) > parseInt(y.innerHTML)) {
                        shouldSwitch = true;
                        break;
                    }
                }
            }

            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
            }
        }
    }
</script>

</html>

<style>
    html {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        /* background-color: #ffe53b; */
        background-color: #dcd9d4;
        background-image: linear-gradient(to bottom,
                rgba(255, 255, 255, 0.5) 0%,
                rgba(0, 0, 0, 0.5) 100%),
            radial-gradient(at 50% 0%,
                rgba(255, 255, 255, 0.1) 0%,
                rgba(0, 0, 0, 0.5) 50%);
        background-blend-mode: soft-light, screen;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
            Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
    }

    .body {
        margin: 20px;
        display: flex;
        justify-content: center;
    }

    h3 {
        text-align: center;
    }

    .body {
        display: flex;
        justify-content: center;
    }

    .header-holder {
        display: flex;
        justify-content: space-around;
    }

    .styled-table {
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 0.9em;
        font-family: sans-serif;
        min-width: 400px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    .styled-table thead tr {
        background-color: #1aa260;
        color: #ffffff;
        text-align: center;
    }

    .styled-table th,
    .styled-table td {
        padding: 12px 15px;
    }

    .styled-table tbody tr {
        border-bottom: 1px solid #dddddd;
    }

    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }

    .styled-table tbody tr:last-of-type {
        border-bottom: 2px solid #009879;
    }

    .styled-table tbody tr.active-row {
        font-weight: bold;
        color: #009879;
    }

    a:link {
        text-decoration: none;
        color: black;
    }

    a:link:hover {
        background-color: #ffe53b;
    }

    th {
        cursor: pointer;
    }
</style>