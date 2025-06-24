<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION["role_id"]) && $_SESSION["role_id"] !== 1) {
    header("Location: 401.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="./favicons/favicon.ico">
    <link href="../../build/style.max.css" rel="stylesheet" />
    <link href="prod-admin-style.css" rel="stylesheet" />
    <title>Edit Product Filters</title>

    <script>
    function updateFilter(obj) {
        //console.log(obj);
        var p = parseInt(obj.getAttribute('data-id'));
        var f = parseInt(obj.getAttribute('data-filter'));
        var n = parseInt(obj.value);
        // console.log('prod id: ', typeof(p));
        // console.log('filter id: ', typeof(f));
        // console.log('new value: ', typeof(n));

        fetch('./API/updateProductsAndFilters.php?p=' + p + '&f=' + f + '&n=' + n)
            .then((response) => response.json())
            .then((data) => {
                console.log(data);
                showToast('Filter Updated');

            })
    }

    function getFilterData() {
        fetch('./API/fetchProductsAndFilters.php')
            .then((response) => response.json())
            .then((data) => {
                //console.log(data);
                var html = '';
                html += `
                        <div class='filters-holder'>
                        <table class='styled-table'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th class='start'>Gender</th>
                                <th class='end'>Change Filter</th>
                                <th class='start'>Size</th>
                                <th class='end'>Change Filter</th>
                                <th class='start'>Type</th>
                                <th class='end'>Change Filter</th>
                                <th class='start'>Sleeve</th>
                                <th class='end'>Change Filter</th>
                            </tr>
                            </thead>
                            <tbody>
                            `
                for (var i = 0; i < data[0].product.length; i++) {
                    html += `
                            <tr>    
                                <td>${data[0].product[i].product_id}</td>
                                <td>${data[0].product[i].code}</td>
                                <td>${data[0].product[i].name}</td>
                                <td class='start '>${data[0].product[i].gender}</td>
                                <td class='end'>
                                    <select data-id="${data[0].product[i].product_id}" data-filter="1" onchange="updateFilter(this)">
                                    <option value="">Select</option>
                                    `
                    for (var j = 0; j < data[1].gender_filters.length; j++) {
                        const currentFilter = data[1].gender_filters[j];
                        const isSelected = currentFilter.filter === data[0].product[i].gender;

                        html += `<option value="${currentFilter.id}" ${isSelected ? 'selected' : ''}>
                                        ${currentFilter.filter}
                                    </option>`;
                    }
                    html += `</select>
                                </td>
                                <td class='start'>${data[0].product[i].size}</td>
                                <td class='end'>
                                    <select data-id="${data[0].product[i].product_id}" data-filter="2" onchange="updateFilter(this)">
                                    <option value="">Select</option>
                                    `
                    for (var k = 0; k < data[2].size_filters.length; k++) {
                        const currentFilter = data[2].size_filters[k];
                        const isSelected = currentFilter.filter === data[0].product[i].size;
                        html += `<option value="${currentFilter.id}" ${isSelected ? 'selected' : ''}>
                                        ${currentFilter.filter}
                                    </option>`;
                    }
                    html += `</select>       
                                </td>
                                <td class='start'>${data[0].product[i].type}</td>
                                <td class='end'>
                                    <select data-id="${data[0].product[i].product_id}" data-filter="3" onchange="updateFilter(this)">
                                    <option value="">Select</option>
                                   `
                    for (var m = 0; m < data[3].type_filters.length; m++) {
                        const currentFilter = data[3].type_filters[m];
                        const isSelected = currentFilter.filter === data[0].product[i].type;
                        html += `<option value="${currentFilter.id}" ${isSelected ? 'selected' : ''}>
                                        ${currentFilter.filter}
                                    </option>`;
                    }
                    html += `</select>
                                </td>
                        
                                <td class='start'>${data[0].product[i].sleeve}</td>
                                <td class='end'>
                                    <select data-id="${data[0].product[i].product_id}" data-filter="4" onchange="updateFilter(this)">
                                    <option value="">Select</option>
                                    `

                    for (var n = 0; n < data[4].sleeve_filters.length; n++) {
                        const currentFilter = data[4].sleeve_filters[n];
                        const isSelected = currentFilter.filter === data[0].product[i].sleeve;
                        html += `<option value="${currentFilter.id}" ${isSelected ? 'selected' : ''}>
                                        ${currentFilter.filter}
                                    </option>`;
                    }
                    html += `</select>
                                </td>
                            </tr>
                            `
                }
                html += `</tbody>
                            </table>
                            </div>`
                document.getElementById('filters').innerHTML = html;
            })
    }
    getFilterData();
    </script>
</head>

<body>
    <div class="parent">
        <div class="div1">
            <?php include('hideNav.php'); ?>
        </div>
        <div class="div2" id="filters"></div>
        <div id="alertToast">
            <div class="toast-header">

                <small>
                    <p id="price_toast_message"></p>
                </small>
                <div class="toast-body">
                    <button type="button" class="btn-close" data-bs-dismiss="toast" onclick="eatToast()"></button>
                    <!-- <p id="price_toast_message"></p> -->
                </div>
            </div>
        </div>
</body>
<script>
function showToast(msg) {
    console.log(msg);
    var toast = document.getElementById('alertToast');
    var msgBlock = document.getElementById('price_toast_message');
    console.log(msgBlock);
    msgBlock.innerText = msg;
    toast.className = "show";
    setTimeout(function() {
        toast.className = toast.className.replace("show", "hideToast");
    }, 1500);
}

function eatToast() {
    var toast = document.getElementById('alertToast').classList.replace('show', 'eatToast');
}
</script>

</html>
<style>
.div2 {
    grid-area: 2/2/2/6;
}

.styled-table {
    margin: 0;
}

.start {
    border-left: 1px solid black;

}

.end {
    border-right: 1px solid black;

}

.toast-header {
    /* background-color: #f57f43; */
    background-color: #00000050;
    color: #ffffff;
    display: flex;
    justify-content: space-between;
    padding: 10px;
    font-size: large;
    border-radius: 5px;
    align-items: baseline;
}

.toast-body {
    padding-top: 5px;
    text-align: center;
}

#alertToast {
    width: 15%;
    visibility: hidden;
    background-color: slategray;
    color: aliceblue;
    text-align: center;
    border-radius: 5px;
    padding: 16px;
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 5;
    border: 2px solid #005677;
    border-radius: 10px;
    box-shadow: 0px 0px 15px 0px rgba(120, 155, 72);
}

.show {
    visibility: visible !important;
    opacity: 1;
    transition: opacity 2s linear;
}

.hideToast {
    visibility: hidden;
    opacity: 0;
    transition: visibility 0s 2s, opacity 2s linear;
}

.eatToast {
    visibility: hidden;
}
</style>