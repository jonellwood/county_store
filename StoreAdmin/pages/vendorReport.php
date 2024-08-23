<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: ../signin/signin.php");

    exit;
}
$uid = $_GET['uid'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Report</title>
</head>
<script>
function formatMoney(amt) {
    const dollars = new Intl.NumberFormat('us-EN', {
        style: 'currency',
        currency: 'USD'
    }).format(amt, )
}
async function getOrder(uid) {
    console.log(uid);
    await fetch('vendorReportGet.php?uid=' + uid)
        .then((response) => response.json())
        .then((data) => {
            console.log(data);
            const splitObjs = splitByVendorID(data);
            console.log(splitObjs);
            var container = document.getElementById('tables-container');
            container.innerHTML = ''; // Clear the container before appending new tables
            var html = '';
            const date = new Date();
            let day = date.getDate();
            let month = date.getMonth() + 1;
            let year = date.getFullYear();

            for (var i = 0; i < splitObjs.length; i++) {
                var html = '';
                html += `
                    <div id="container">
                        <div id="header">
                            <img src="../assets/img/bcg-hz (6).png" width="25%" alt="bcg logo">
                            <div class="deets">
                                <span>Department: ${splitObjs[i][0].dep_name}</span><br>
                                <span class='ponumber'> PO#: ${splitObjs[i][0].po_number} </span><br>
                                <span>Report Date: ${month}/${day}/${year} </span><br>
                                <span>Vendor: ${splitObjs[i][0].vendor}</span><br>
                                <span>Item Count: ${splitObjs[i].length} </span>
                            </div>
                        </div>
                    </div>            
                    <table>
                        <tr>
                            <th width=10%>Style #</th>
                            <th width=10%>Color</th>
                            <th width=10%>Size</th>
                            <th width=5%>Qty</th>
                            <th width=5%>Unit Price</th>
                            <th width=5%>Logo Fee</th>
                            <th width=5%>Tax</th>
                            <th width=10%>Total x/Tax</th>
                            <th width=20%>Logo</th>
                            <th>Employee Name</th>
                        </tr>`;

                for (var j = 0; j < splitObjs[i].length; j++) {
                    html += `<tr>    
                                <td>${splitObjs[i][j].product_code}</td>
                                <td>${splitObjs[i][j].color_id}</td>
                                <td>${splitObjs[i][j].size_name}</td>
                                <td>${splitObjs[i][j].quantity}</td>
                                <td>${new Intl.NumberFormat('us-EN', {style: 'currency', currency: 'USD'}).format(splitObjs[i][j].pre_tax_price)}</td>
                                <td>${new Intl.NumberFormat('us-EN', {style: 'currency', currency: 'USD'}).format(splitObjs[i][j].logo_fee)}</td>
                                <td>${new Intl.NumberFormat('us-EN', {style: 'currency', currency: 'USD'}).format(splitObjs[i][j].tax)}</td>
                                <td>${new Intl.NumberFormat('us-EN', {style: 'currency', currency: 'USD'}).format(splitObjs[i][j].line_item_total)}</td>
                                <td class='logo-img'><img src="../../${splitObjs[i][j].logo}" alt='logo' id='logo-img'/></td>
                                
                                <td>${splitObjs[i][j].rf_first_name} ${splitObjs[i][j].rf_last_name} </td>
                                </tr>
                                <tr>
                                <td colspan='3'>${splitObjs[i][j].product_name}</td>
                                <td class="${splitObjs[i][j].status}"></td>
                                <td colspan='2'>Order ID: ${splitObjs[i][j].order_id}</td>
                                
                                <td colspan='2'>Order Details ID: ${splitObjs[i][j].order_details_id}</td>
                                
                                <td class="deptNamePatch" data-value="${splitObjs[i][j].dept_patch_place}">Department Name Placement: ${splitObjs[i][j].dept_patch_place}</td>
                                <td>${splitObjs[i][j].comment}</td>
                            </tr>
                            <tr class='buttercream'>
                            <td colspan='10'>
                            </td>
                            </tr>
                            `;
                }

                html += `</table>`;
                html += `<div class="pageBreak"> </div>`
                container.innerHTML += html; // Append the HTML for each table
                handleDeptNamePatch();
            }
        });
}
getOrder('<?php echo $uid ?>');

function splitByVendorID(data) {
    const result = {};
    data.forEach(item => {
        const vendorID = item.vendor_id;

        if (!result[vendorID]) {
            // make an object IF we dont have one already
            result[vendorID] = [];
        }
        // push if we do
        result[vendorID].push(item)
    });
    return Object.values(result);
}

function createTable(tableData) {
    const table = document.createElement("table");
    const headers = Object.keys(tableData[0]);
    // Create a table header
    const headerRow = table.insertRow();
    headers.forEach(header => {
        const th = document.createElement('th');
        th.textContent = header;
        headerRow.appendChild(th);
    });

    // Create the table rows
    tableData.forEach(dataItem => {
        const row = table.insertRow();
        headers.forEach(header => {
            const cell = row.insertCell();
            cell.textContent = dataItem[header];
        });
    });
    console.log(table);
    return table;
}
</script>

<body>
    <!-- <div id="container"> -->
    <!-- <div id="header"> -->
    <!-- <img src="../assets/img/bcg-hz (6).png" width="50%" alt="bcg logo"> -->
    </br>

    <div id="tables-container"></div>
    <!-- </div> -->
</body>

<script>
function addNoToFileName(filename) {
    if (filename.includes('_NO')) {
        return filename; // "_NO" is already present, no need to modify ... except when there is.... hmmmm
    } else {
        const dotIndex = filename.lastIndexOf('.');
        const baseName = filename.substring(0, dotIndex); // Get base name without extension
        const extension = filename.substring(dotIndex); // Grab the extension
        return baseName + '_NO' + extension;
    }
}

function removeNoFromFileName(filename) {
    return filename.replace(/_NO(\.[^.]+)$/, '$1');
}

function handleDeptNamePatch() {
    const selectElements = document.querySelectorAll('.deptNamePatch')

    selectElements.forEach(selectElement => {
        const selectedValue = selectElement.dataset.value;
        console.log(selectedValue)
        const parentElement = selectElement.parentElement
        // console.log(parentElement)

        // find the corrosponding image... hmmm thats gonna be tough...
        // const imageElement = selectElement.previousElementSibling.querySelector('.logo-img')
        const imageElement = parentElement.previousElementSibling.querySelector('#logo-img')
        // console.log('Image Element is :')
        console.log('old Image Source', imageElement.src);


        if (imageElement && imageElement.complete && imageElement.src) {
            const oldSrc = imageElement.src;
            const fileName = oldSrc.split('/').pop();

            let newSrc;
            switch (selectedValue) {
                case 'No Dept Name':
                case 'Back of Hat':
                case 'Left Sleeve':
                    const newFileName = addNoToFileName(fileName);
                    newSrc = oldSrc.replace(fileName, newFileName);
                    break;

                case 'Below Logo':
                    const fileNameWithoutNo = removeNoFromFileName(fileName);
                    newSrc = oldSrc.replace(fileName, fileNameWithoutNo);
                    break;

                default:
                    console.log('Invalid selection');
                    return; // Skip invalid selections
            }
            if (newSrc) {
                imageElement.src = newSrc;
                console.log('New Image Source', newSrc)
                console.log('🥬🍃 . . . . . . 🐞')
            }
        }
    })

}
</script>

</html>


<style>
table {
    margin-top: 30px;
    margin-bottom: 30px;
    margin-left: 20px;
    margin-right: 20px;
    border-collapse: collapse;
}

table,
th,
td {
    border: 1px solid black;
}

th,
td {
    padding: 10px;
}

th {
    background-color: #FDDF9530;
    /* background-color: #808080; */
}

colgroup {

    width: 225px;
}

#header {
    text-align: center;
}

.logo-img {
    background-color: #808080;
}

.logo-img img {
    display: flex;
    max-width: 150px;
    margin-left: auto;
    margin-right: auto;
}

.ponumber {
    font-family: monospace;

}

.pageBreak {
    clear: both;
    page-break-after: right;

}

caption {
    font-size: x-large;
    font-weight: bold;
    padding: 20px;
}

.test-hr {
    /* color: hotpink; */
    /* border: 10px solid blue; */
    height: 20px;

    --dot-bg: lightslategrey;
    --dot-color: white;
    --dot-size: 2px;
    --dot-space: 11px;
    background:
        linear-gradient(90deg, var(--dot-bg) calc(var(--dot-space) - var(--dot-size)), transparent 1%) center / var(--dot-space) var(--dot-space),
        linear-gradient(var(--dot-bg) calc(var(--dot-space) - var(--dot-size)), transparent 1%) center / var(--dot-space) var(--dot-space),
        var(--dot-color);

}

h2 {
    text-align: center;
}

#header {
    background-color: lightblue;
    padding: 10px;
    display: flex;
    justify-content: space-evenly;
    font-size: larger;
    font-weight: bold;
    border: 1px solid darkslateblue;
    margin-left: 20px;
    margin-right: 20px;
}

.deets {
    text-align: left;
}

.Ordered {
    visibility: hidden;
}

.Received {
    color: inherit;
}

.Received:after {
    content: "RECEIVED";
    position: absolute;
    /* top: 70px; */
    /* left: 10px; */
    z-index: 1;
    margin-left: -130px;
    margin-top: -20px;
    font-family: Arial, sans-serif;
    -webkit-transform: rotate(-10deg);
    /* Safari */
    -moz-transform: rotate(-10deg);
    /* Firefox */
    -ms-transform: rotate(-10deg);
    /* IE */
    -o-transform: rotate(-10deg);
    /* Opera */
    transform: rotate(-10deg);
    font-size: 16px;
    color: #c00;
    background: #fff;
    border: solid 4px #c00;
    padding: 5px;
    border-radius: 5px;
    zoom: 1;
    filter: alpha(opacity=20);
    opacity: 0.2;
    text-shadow: 0 0 2px #c00;
    box-shadow: 0 0 2px #c00;
}

.buttercream,
.whitecarbon {
    background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHhtbG5zOnhsaW5rPSdodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rJyB3aWR0aD0nNicgaGVpZ2h0PSc2Jz4KICA8cmVjdCB3aWR0aD0nNicgaGVpZ2h0PSc2JyBmaWxsPScjZWVlZWVlJy8+CiAgPGcgaWQ9J2MnPgogICAgPHJlY3Qgd2lkdGg9JzMnIGhlaWdodD0nMycgZmlsbD0nI2U2ZTZlNicvPgogICAgPHJlY3QgeT0nMScgd2lkdGg9JzMnIGhlaWdodD0nMicgZmlsbD0nI2Q4ZDhkOCcvPgogIDwvZz4KICA8dXNlIHhsaW5rOmhyZWY9JyNjJyB4PSczJyB5PSczJy8+Cjwvc3ZnPg==");
    background-repeat: repeat;
}

/* .my-stripes {
        background-image: url("data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHdpZHRoPScxMCcgaGVpZ2h0PScxMCc+CiAgPHJlY3Qgd2lkdGg9JzEwJyBoZWlnaHQ9JzEwJyBmaWxsPScjODA4MDgwJy8+CiAgPHBhdGggZD0nTS0xLDEgbDIsLTIKICAgICAgICAgICBNMCwxMCBsMTAsLTEwCiAgICAgICAgICAgTTksMTEgbDIsLTInIHN0cm9rZT0nd2hpdGUnIHN0cm9rZS13aWR0aD0nMycvPgo8L3N2Zz4=");
        background-repeat: repeat;
    } */
</style>