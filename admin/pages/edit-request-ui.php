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

require_once 'config.php';
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Edit products in the store database" />
    <link rel="icon" href="./favicons/favicon.ico">
    <link href="../../build/style.max.css" rel="stylesheet" />
    <link href="../../index23.css" rel="stylesheet" />
    <link rel="stylesheet" href="prod-admin-style.css">
    <title>Edit Request</title>
</head>

<script>
    let productOptions;

    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    })

    function loadOptions(id) {
        return fetch('load-single-product-multiiq.php?id=' + id)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error fetching product options');
                }
                return response.json();
            })
            .then((data) => {
                console.log('Product Options')
                // console.log(data);
                productOptions = data;
                // console.log(productOptions);
            })
            .catch(error => {
                console.error('There was a fetch problem. Fetch me a doctor')
            });
    }
    // loadOptions(155);

    function makeSkeleton() {
        const tableBody = document.getElementById('table-body');
        const rowTemplate = document.getElementById("row-template")
        for (let i = 0; i < 20; i++) {
            tableBody.append(rowTemplate.content.cloneNode(true));
        }
        loadRequests();
    }

    function extractDate(inputString) {
        const parts = inputString.split(' ');
        return parts[0];
    }


    function loadRequests() {
        const tableBody = document.getElementById('table-body');
        const rowTemplate = document.getElementById("row-template")
        for (let i = 0; i < 20; i++) {
            tableBody.append(rowTemplate.content.cloneNode(true));
        }
        makeSilly();
        fetch('load-active-requests.php')
            .then((response) => response.json())
            .then((data) => {
                console.log(data);
                const tableBody = document.getElementById('table-body');
                const rowTemplate = document.getElementById("row-template")
                tableBody.innerHTML = "";
                data.forEach((order) => {
                    const tr = rowTemplate.content.cloneNode(true);
                    // console.log(tr);
                    tr.getElementById("order_details_id").textContent = order.order_details_id;
                    tr.getElementById("order_id").textContent = order.order_id;
                    tr.getElementById("product_code").textContent = order.code;
                    tr.getElementById("product_name").textContent = order.name;
                    tr.getElementById("price").textContent = formatter.format(order.item_price);
                    tr.getElementById("vendor").textContent = order.vendor_name;
                    tr.getElementById("dept_name").textContent = order.deptName;
                    tr.getElementById("submitted_by").textContent = order.submitted_by_name;
                    tr.getElementById("submitted_on").textContent = extractDate(order.created);
                    tr.getElementById("status").textContent = order.status;
                    tr.getElementById("edit_button").addEventListener("click", function() {
                        editRequest(order.order_details_id, order.product_id);
                    })

                    tableBody.append(tr);
                    removeSkeleton();
                })
            })
    }

    async function editRequest(id, p_id) {
        var stupidOptions = await loadOptions(p_id);

        // alert('Editing ' + id);
        await fetch('load-single-request.php?id=' + id + '&p_id=' + p_id)
            .then((response) => response.json())
            .then((data) => {
                console.log(data);
                var request = data[0].request_data;
                var sim_prod = data[2].similar_products;
                var logos = data[3].logos;

                var html = "";
                html += `
            <div class='edit-container'>
                <table class='styled-table'>
                    <h2>Existing Request for <mark>${request[0].req_for_name}</mark> </h2>
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="bold">Product Code</td><td>${request[0].product_code} - ${request[0].product_id}</td>
                        </tr>
                        <tr>
                            <td class="bold">Name</td><td>${request[0].product_name}</td>
                        </tr>
                        
                        <tr>
                            <td class="bold">Quantity</td><td>${request[0].quantity}</td>
                        </tr>
                        <tr>
                            <td class="bold">Price</td><td>${request[0].price}</td>
                        </tr>
                        <tr>
                            <td class="bold">Logo Fee</td><td>${formatter.format(request[0].logo_fee)}</td>
                        </tr>
                        <tr>
                            <td class="bold">Tax</td><td>${formatter.format(request[0].tax)}</td>
                        </tr>
                        <tr>
                            <td class="bold">Total with Fees</td><td>${formatter.format(request[0].line_item_total)}</td>
                        </tr>
                        <tr>
                            <td class="bold">Size</td><td>${request[0].size}</td>
                        </tr>
                        <tr>
                            <td class="bold">Color</td><td>${request[0].color}</td>
                        </tr>
                        <tr>
                        <td class="bold">Dept Name Placement</td><td>${request[0].dept_patch_place !== null ? request[0].dept_patch_place : "None"}</td>
                        </tr>
                        <tr>
                        <td class="bold">Order Status</td><td>${request[0].status !== null ? request[0].status : "None"}</td>
                        </tr>
                        <tr>
                        <td class="bold">Bill to Dept</td><td>${request[0].bill_to_dept !== null ? request[0].bill_to_dept : "None"}</td>
                        </tr>
                        <tr>
                        <td class="bold">Logo</td><td><img class='logo-img' src=../../${request[0].logo} alt=${request[0].product_name} />
                        </tr>
                        
                    </tbody>
            </div>
            `
                document.getElementById('edit-table').innerHTML = html;
                var fhtml = "";
                var currentProduct = request[0].product_id;
                var currentSize = request[0].size_id;
                var currentColor = request[0].color_id;
                var basePrice = request[0].price;

                fhtml += `
            <form action="edit-request-db.php" method="POST" id="edit" name="edit" onsubmit="return validateForm()"> 
            <p id="updated_price" class="hidden"><input name="new_price" value="${request[0].price}"/></p>
            <input type="hidden" name="order_details_id" value=${id} />
            <input type="hidden" name="vendor" value=${request[0].vendor_id} />
            <input type="hidden" name="status" id="currentStatus" value=${request[0].status} />
            <h2>Select the values you want to change</h2>
                <table class="styled-table">
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td><label for="productCodeSelect">Product</label></td>
                    <td><select data-price=${basePrice} name="productCodeSelect" id="productCodeSelect" onchange="checkOptions(this.value, ${currentProduct}, ${currentSize}, ${currentColor}, ${basePrice})"/>
                        `

                for (var i = 0; i < sim_prod.length; i++) {
                    fhtml +=
                        `<option value=${sim_prod[i].product_id}>${sim_prod[i].code} - ${sim_prod[i].product_id} - ${sim_prod[i].name}</option> `
                }
                fhtml += `
                    </select></td></tr>
                    <tr>
                        <td>Name:</td><td id="displayName">${request[0].product_name}</td>
                    </tr>
                    <tr>
                        <td><label for="quantity">Quantity</label></td>
                        <td><input type="number" name="quantity" value=${request[0].quantity} /></td>
                    </tr>
                    <tr>
                        <td>Price:</td><td id="display-price">${formatter.format(request[0].price)}</td>
                    </tr>
                    <tr>
                        <td><label for="logoFee">Logo Fee: </label></td><td id="logoFee"><input name="logoFee" type="number" value='${request[0].logo_fee}'</td>
                    </tr>
                    <tr>
                    <td>YOU 🫵<td>
                    </tr>
                    <tr>
                    <td>Are</td><td>Enough</td>
                    </tr>
                    <tr>
                        <td><label for="sizeSelect">Size</label></td>
                        <td><select id="sizeSelect" name="sizeSelect">
                    
                    `

                var sizeOptions = productOptions.current_sizes;

                for (var i = 0; i < sizeOptions.length; i++) {
                    fhtml +=
                        `<option value=${sizeOptions[i].size_id}>${sizeOptions[i].size}</option>`
                }
                fhtml += `
                    <option value='s-404'>Not Selected</option>
                    </select></td>
                    <tr>
                    
                    <td><label for="colorSelect">Color</label></td>
                    <td><select id="colorSelect" name="colorSelect">
                    
            `
                var colorOptions = productOptions.current_colors;
                for (var i = 0; i < colorOptions.length; i++) {
                    fhtml +=
                        `<option value=${colorOptions[i].color_id}>${colorOptions[i].color}</option>`
                }
                fhtml += `
                    <option value='c-404'>Not Selected</option>
                    </select></td>
                    </tr>
                    <tr>
                        <td>❤️ </td><td>Yourself</td>
                    </tr>
                    <tr>
                        <td>Enjoy</td><td>Today</td>
                    </tr>
                    <tr>
                        <td>
                            <label for="billToDept">Bill to Dept<label>
                        </td>
                        <td>
                            <input type=text name="billToDept" id="billToDept" />
                        </td>
                    </tr>
                    <tr>
                    <td>
                        <label for="logoSelect">Logo</label>
                    </td>
                    <td>
                        <select name="logoSelect">`
                for (var l = 0; l < logos.length; l++) {
                    fhtml += `<option value='${logos[l].id}'>${logos[l].logo_name}</option>`
                }
                fhtml += `
                    </select>
                    </td>
                    </tr>
                    </form>   
            `
                document.getElementById("edit-form").innerHTML = fhtml;
                // if (stupidOptions) {
                setCurrentProductCode(request[0].product_id);
                setCurrentColor(request[0].color_id);
                setCurrentSize(request[0].size_id);
                // }
            })
    }
</script>

<body onload="makeSkeleton()">
    <div class="parent">
        <div class="div1">
            <?php include('hideNav.php'); ?>
        </div>
        <div class="div2">
            <div id="requests-table">
                <table class="styled-table">
                    <thead class="skeleton">
                        <tr>
                            <th class="skeleton" width="10%">Reference Num</th>
                            <th class="skeleton" width="10%">Order ID</th>
                            <th class="skeleton" width="10%">Product Code</th>
                            <th class="skeleton" width="25%">Product Name</th>
                            <th class="skeleton" width="5%">Price</th>
                            <th class="skeleton" width="10%">Vendor</th>
                            <th class="skeleton" width="15%">Dept Name</th>
                            <th class="skeleton" width="20%">Submitted By</th>
                            <th class="skeleton" width="20%">Submitted On</th>
                            <th class="skeleton" width="10%">Status</th>
                            <th class="skeleton">&#128280;</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <template id="row-template">
                            <tr>
                                <td id="order_details_id" class="skeleton" width="5%">XXXX</td>
                                <td id="order_id" class="skeleton" width="5%">XXXX</td>
                                <td id="product_code" class="skeleton" width="10%">XXXXX</td>
                                <td id="product_name" class="skeleton" width="25%">XXXXX </td>
                                <td id="price" class="skeleton" width="5%">XX.XX</td>
                                <td id="vendor" class="skeleton" width="10%">X</td>
                                <td id="dept_name" class="skeleton" width="15%">XXXXXXXXXX</td>
                                <td id="submitted_by" class="skeleton" width="20%">XXXXXXX XXXXXX</td>
                                <td id="submitted_on" class="skeleton" width="20%">XXXXXXX XXXXXX</td>
                                <td id="status" class="skeleton" width="10%">XXXXXXX</td>
                                <td id="edit-button" class="skeleton">
                                    <button class="skeleton button" id="edit_button"
                                        popovertarget="requestEdit">Edit</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div id="requestEdit" popover=manual>
                <button class="button close-btn" popovertarget="requestEdit" popovertargetaction="hide">
                    <span aria-hidden=”true”>❌</span>
                    <span class="sr-only">Cancel</span>
                </button>
                <div class="edit-holder">
                    <div id="edit-table"></div>
                    <div id=edit-form>
                    </div>
                    <button form="edit" type="submit" class="button submit-btn">Update</button>
                </div>
                <div class="mismatch-alert" id="mismatch-alert" popover>
                    <div id="alert-message"></div>
                </div>
            </div>
        </div>
        <div class="div4">
            <?php include('hideTopNav.php'); ?>
        </div>
    </div>

    <script>
        function removeSkeleton() {
            const skeletonItems = document.querySelectorAll('.skeleton')
            skeletonItems.forEach((item) => {
                item.classList.remove('skeleton');
            })
        }


        function generateRandomData(minLength, maxLength, numSegments, characters) {
            let randomData = '';

            for (let i = 0; i < numSegments; i++) {
                const segmentLength = Math.floor(Math.random() * (maxLength - minLength + 1)) + minLength;
                let segment = '';

                for (let j = 0; j < segmentLength; j++) {
                    const randomIndex = Math.floor(Math.random() * characters.length);
                    segment += characters[randomIndex];
                }

                randomData += segment;

                // Add space between segments
                if (i !== numSegments - 1) {
                    const numSpaces = Math.random() < 0.5 ? 3 : 4;
                    for (let k = 0; k < numSpaces; k++) {
                        randomData += ' ';
                    }
                }
            }

            return randomData;
        }

        function generateRandomCodes(minLength, maxLength, numSegments, characters) {
            let randomData = '';

            for (let i = 0; i < numSegments; i++) {
                let segment = '';

                // Generate the first segment (1 or 2 characters)
                const firstSegmentLength = Math.floor(Math.random() * 2) + 1;
                for (let j = 0; j < firstSegmentLength; j++) {
                    const randomIndex = Math.floor(Math.random() * characters.length);
                    segment += characters[randomIndex];
                }

                // Add a hyphen between segments
                segment += '-';

                // Generate the second segment (3 or 5 digits)
                const secondSegmentLength = Math.random() < 0.5 ? 3 : 5;
                for (let k = 0; k < secondSegmentLength; k++) {
                    segment += Math.floor(Math.random() * 10);
                }

                randomData += segment;

                // Add space between segments if not the last segment
                if (i !== numSegments - 1) {
                    const numSpaces = Math.random() < 0.5 ? 3 : 4;
                    for (let l = 0; l < numSpaces; l++) {
                        randomData += ' ';
                    }
                }
            }

            return randomData;
        }


        // Select all <td> elements with class "skeleton"
        function makeSilly() {
            const skeletonItems = document.querySelectorAll('#product_name');
            skeletonItems.forEach(function(td) {
                const randomString = generateRandomData(6, 15, 5,
                    'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
                td.textContent = randomString;
            });

            const skeletonPrices = document.querySelectorAll('#price');
            skeletonPrices.forEach(function(rp) {
                const randomNums = generateRandomData(2, 3, 1, 'ABCDEFGHIJ');
                rp.textContent = randomNums;
            });

            const skeletonVendors = document.querySelectorAll('#vendor');
            skeletonVendors.forEach(function(sv) {
                const randomVendor = generateRandomData(2, 5, 3,
                    '!@#$%^&*()_+=-');
                sv.textContent = randomVendor;
            })

            const skeletonNames = document.querySelectorAll('#submitted_by');
            skeletonNames.forEach(function(rn) {
                const randomName = generateRandomData(4, 9, 2,
                    'ABCDEFGHQRSTUVWXYZabcdefghijklmpqrsuvwxyz');
                rn.textContent = randomName;
            });

            const skeletonDepts = document.querySelectorAll('#dept_name');
            skeletonDepts.forEach(function(dn) {
                const randomName = generateRandomData(5, 6, 2,
                    'ACDEFGHIMOPUVWXabcfhjlmnoptuvwxyz');
                dn.textContent = randomName;
            });

            const skeletonOrders = document.querySelectorAll("#order_id");
            skeletonOrders.forEach(function(ro) {
                const randomOrderNum = generateRandomData(3, 4, 1, 'KLMNOPQRS');
                ro.textContent = randomOrderNum;
            });

            const skeletonOrderIds = document.querySelectorAll("#order_details_id");
            skeletonOrderIds.forEach(function(so) {
                const randomOrderIdNum = generateRandomData(3, 4, 1, 'ABCDEFGHIJ');
                so.textContent = randomOrderIdNum;
            });

            const skeltonStatus = document.querySelectorAll("#status");
            skeltonStatus.forEach(function(ss) {
                const randomStatus = generateRandomData(4, 6, 1,
                    'BDFGIJLNOPRTVXZbdfhjmoqsxz');
                ss.textContent = randomStatus;
            })
            const skeletonCode = document.querySelectorAll("#product_code");
            skeletonCode.forEach(function(sc) {
                const randomCodes = generateRandomCodes(4, 6, 1,
                    'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
                sc.textContent = randomCodes;
            })
        }

        function openAlert() {
            var alert = document.getElementById('mismatch-alert')
            alert.showPopover();
        }

        function closeAlert() {
            var alert = document.getElementById('mismatch-alert')
            alert.hidePopover();
        }

        function checkOptions(selected, current, size, color, price) {
            fetch('check-size-and-color.php?c_id=' + current + '&s_id=' + selected + '&c_size=' + size + '&c_color=' +
                    color)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log(data);
                    if (data.status == 200) {
                        if (!comparePrices(price, data.selected_price)) {
                            openAlert();
                            var html = '';

                            html +=
                                `<div class='price-warning'><p class='price-warning-text'>⚠️ Current Product Price is $ ${price.toFixed(2)} and Selected Product price is $ ${data.selected_price.toFixed(2)}</p>
                            <p class='generic-warning'>Selecting "Change Anyway" will change the status of the request back to pending and require another approval</p></div>
                            `
                        }
                        html += `<div class='button-holder'>`
                        html +=
                            `<button onclick="setPriceDisplayInForm('${data.selected_price}')" popovertarget="mismatch-alert" popovertargetaction="hide" class="button">Change Anyway</button>`
                        html +=
                            `<button popovertarget="mismatch-alert" popovertargetaction="hide" class="button">Do Not Change</button>`
                        html += `</div>`

                        document.getElementById('alert-message').innerHTML = html;

                    } else {
                        if (data.status == 404) {
                            openAlert();
                            var productSelect = document.getElementById('productCodeSelect')
                            var html = "";
                            html += `<div class='data-message'><h1 class='warning'>${data.message}<h1></div>`;
                            if (!comparePrices(price, data.selected_price)) {
                                html +=
                                    `<div class='price-warning'><p class='price-warning-text'> ⚠️ Current Product Price is $ ${price.toFixed(2)} and Selected Product price is $ ${data.selected_price.toFixed(2)}</p>
                                <p class='generic-warning'>Selecting "Change Anyway" will change the status of the request back to pending and require another approval</p></div>
                                `
                            }
                            html +=
                                `<p class='generic-warning'>Selecting "Change Anyway" will reset both the color and size selectors in the form</p>`
                            html += `<div class='button-holder'>`;
                            html +=
                                `<button onclick="resetValues('${data.selected_price}', ${selected}, '${removeSingleQuotes(data.s_name)}')" popovertarget="mismatch-alert" popovertargetaction="hide" class="button">Change Anyway</button>`
                            html +=
                                `<button onclick="setCurrentProductCode(${current})" popovertarget="mismatch-alert" popovertargetaction="hide" class="button">Do Not Change</button>`
                            html += `</div>`
                            document.getElementById('alert-message').innerHTML = html;

                        }
                    }
                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                });

        }

        function setCurrentProductCode(id) {
            var selected = document.getElementById("productCodeSelect");
            for (var i = 0; i < selected.options.length; i++) {
                var option = selected.options[i];
                if (option.value == id) {
                    option.selected = true;
                    break;
                }
            }
        }

        function setCurrentColor(id) {
            var selected = document.getElementById("colorSelect");
            for (var i = 0; i < selected.options.length; i++) {
                var option = selected.options[i];
                if (option.value == id) {
                    option.selected = true;
                    break;
                }
            }
        }

        function setCurrentSize(id) {
            var selected = document.getElementById("sizeSelect");
            for (var i = 0; i < selected.options.length; i++) {
                var option = selected.options[i];
                if (option.value == id) {
                    option.selected = true;
                    break;
                }
            }
        }

        function setStatusToPending() {
            // alert("Setting status to pending")
            var statusHolder = document.getElementById('currentStatus')
            statusHolder.value = 'Pending'
        }

        function resetValues(price, id, name) {
            setStatusToPending();
            setPriceDisplayInForm(price);
            updateColorsAndSizeInForm(id);
            setCurrentSize('s-404');
            setCurrentColor('c-404');
            updateProductNameDisplayInForm(name);
        }

        function comparePrices(c_p, s_p) {
            if (s_p > c_p) {
                return false;
            } else {
                return true;
            }
        }

        function setPriceDisplayInForm(price) {
            let displayPrice = document.getElementById('display-price');
            displayPrice.textContent = price;
            let formPrice = document.getElementById('updated_price');
            console.log(formPrice);
            let html = ''
            html += "<input type='hidden' name='new_price' id='new_price' value='" + price + "' />"
            //  console.log(html);
            formPrice.innerHTML = html;
            setStatusToPending();
        }

        function updateProductNameDisplayInForm(name) {
            let nameDisplay = document.getElementById('displayName');
            nameDisplay.textContent = name;
        }

        function updateColorsAndSizeInForm(id) {
            var sizeSelect = document.getElementById('sizeSelect');
            var colorSelect = document.getElementById('colorSelect');
            fetch('load-new-colors-and-sizes.php?id=' + id)
                .then((response) => response.json())
                .then((data) => {
                    let sizeOptions = data[0].size_options;
                    let colorOptions = data[1].color_options;
                    // console.log('Updated Color and Size Options')
                    // console.log(data[1].color_options);
                    var shtml = ""
                    shtml += `<select id='sizeSelect' name='sizeSelect'>`
                    for (var i = 0; i < sizeOptions.length; i++) {
                        shtml += `<option value=${sizeOptions[i].size_id}>${sizeOptions[i].size}</option>`
                    }
                    shtml += `
                    <option value='s-404' selected>Not Selected</option>
                    </select>
                `
                    var chtml = "";
                    chtml += `<select id='colorSelect' name='colorSelect'>`
                    for (var j = 0; j < colorOptions.length; j++) {
                        chtml += `<option value=${colorOptions[j].color_id}>${colorOptions[j].color}</option>`
                    }
                    chtml += `<option value='c-404' selected>Not Selected</option>
                        </select>
                `
                    sizeSelect.innerHTML = shtml;
                    colorSelect.innerHTML = chtml;
                })

        }

        function removeSingleQuotes(str) {
            return str.replace(/'/g, '');
        }

        function validateForm() {
            // console.log('validating');
            const colorValue = document.getElementById('colorSelect').value;
            const sizeValue = document.getElementById('sizeSelect').value;
            const billToValue = document.getElementById('billToDept').value;

            if (colorValue == 'c-404') {
                alert('Please select a valid color option');
                return false;
            } else if (sizeValue == 's-404') {
                alert('Please select a valid size option');
                return false;
            } else if (billToValue.length != 5) {
                alert('Please enter a valid billing department number');
                return false;
            }
            return true;

        }
    </script>
</body>


</html>
<style>
    body {
        position: relative;
        max-width: 90vw;
        /* padding-right: calc(var(--bs-gutter-x) * 0.5); */
        /* padding-left: calc(var(--bs-gutter-x) * 0.5); */
        margin-right: 20px;
        margin-left: 20px;
        /* overflow: hidden; */
    }

    .div2 {
        background-color: #00000050;
        grid-area: 2/2/2/6 !important;

    }

    .close-btn,
    .alert-close-btn {
        position: fixed;
        top: 0;
        margin-top: 20px;
        left: 0;
        margin-left: 60px;
        border: 3px solid tomato;
        width: 100px;
        height: 50px;
        border-radius: 5px;
        box-shadow: 1px 1px, 2px 2px, 3px 3px, 4px 4px;
    }

    .submit-btn {
        position: fixed;
        bottom: 0;
        margin-bottom: 2rem;
        right: 0;
        margin-right: 60px;
        border: 3px solid green;
        width: 100px;
        height: 50px;
        border-radius: 5px;
        box-shadow: 1px 1px, 2px 2px, 3px 3px, 4px 4px;
    }

    .close-btn:hover {
        transform: scale(.95);
        border: 2px solid red;
        box-shadow: 0px 0px, 0px 0px, 0px 0px, 1px 1px;
    }

    .submit-btn:hover {
        transform: scale(.95);
        border: 2px solid darkgreen;
        box-shadow: 0px 0px, 0px 0px, 0px 0px, 1px 1px;
    }

    .alert-close-btn:hover {
        transform: scale(.95);
        border: 2px solid green;
        box-shadow: 0px 0px, 0px 0px, 0px 0px, 1px 1px;
    }

    #requests-table {
        margin-left: 10px;
        margin-right: auto;
    }

    .edit-holder {
        display: grid;
        grid-template-columns: 1fr 1fr;
        justify-content: center;
    }

    #requestEdit {
        margin: auto;
        /* position: fixed; */
        width: 90%;
        height: 90%;
        border: 5px solid tomato;
        /* overflow: hidden; */
        box-shadow: 1px 1px, 2px 2px, 3px 3px, 4px 4px;
        padding-top: 30px;
    }

    #requestEdit::backdrop {
        backdrop-filter: blur(5px);
    }

    #mismatch-alert {
        margin: auto;
        /* position: fixed; */
        width: 60%;
        /* height: 30%; */
        border: 10px solid red;
        /* overflow: hidden; */
        box-shadow: 1px 1px, 2px 2px, 3px 3px, 4px 4px;
        padding: 15px;
    }

    #mismatch-alert::backdrop {
        backdrop-filter: blur(5px);
    }

    #edit-table,
    #edit-form {
        display: flex;
        justify-content: center;
        /* grid-template-rows: 1fr 1fr; */
    }

    #edit-form {
        gap: 20px;
    }

    .styled-table tr td {
        text-align: center;
    }

    /* .styled-table th {
    width: 100px;
} */

    .description-entry {
        width: 90%;
    }

    /* .yes:before {
        content: "Yes";
    }

    .no:before {
        content: "No";
    } */

    td[data-int="0"]:before {
        content: "No";
    }

    td[data-int="1"]:before {
        content: "Yes";
    }

    .cs-current {
        display: grid;
        grid-template-columns: 1fr auto;
        padding: 10px;
        justify-content: center;
    }

    .cs-current span {
        width: 100%;
        text-align: center;
        font-weight: bold;
    }

    #colors-table,
    #sizes-table {
        text-align: center;
    }

    .color-list {
        /* max-width: 70%; */
        list-style: none;
        gap: 10px;
        display: flex;
        flex-wrap: wrap;
        /* justify-content: flex-start; */
        justify-content: center;

    }

    .color-list li img {
        border: 1px solid black;
        /* box-shadow: 0px 0px 10px 3px rgba(128, 128, 128, 1); */
        width: 15px;
    }

    #color-pick-form {
        margin-top: 20px;
    }

    .size-list {
        /* max-width: 20%; */
        list-style: none;
        gap: 5px;
        display: flex;
        flex-wrap: wrap;
        /* justify-content: flex-start; */
        justify-content: center;
    }

    .color-list li,
    .size-list li {
        padding: 3px;
        border: 1px solid black;
        display: flex;
        align-content: center;
    }

    .color-list li label {
        align-content: center;
    }



    #color_options,
    #size_options {
        /* width: 100%; */
        margin: 10px;
        padding: 15px;
    }

    #product-options {

        margin: 10px;
        padding: 15px;
        /* display: flex; */
        /* gap: 10px; */
    }

    #product-options input {
        margin-right: 20px;
    }

    #product-options select {
        margin-right: 20px;
    }

    #product-options h2 p {
        font-size: small;
        color: darkslategray;
        font-weight: bold;
    }

    #product-options label {
        font-weight: bold;
    }

    input[type="checkbox"]~label {
        background-color: whitesmoke;
        padding: 5px;
    }

    input[type="checkbox"]:checked~label {
        background-color: limegreen !important;
        font-weight: bold;
        /* margin: 5px;
        padding: 5px; */
    }

    h2 {
        text-align: center;
        box-shadow: 1px 1px 5px 10px rgba(0, 0, 0, .04);
    }

    mark {
        font-family: monospace;
        background-color: lightgrey;
        font-size: larger;
        font-weight: bold;
        padding: 1px;
    }

    .legend {
        display: grid;
        /* flex-direction: row; */
        /* flex-wrap: wrap; */
        /* flex-basis: 100px; */
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
        justify-content: center;
        align-content: center;
        margin-top: 1em;
        border: 1px solid rebeccapurple;
        padding: 10px;
    }

    #alert-message p,
    .legend p {
        font-size: smaller;
        color: darkslategray;
        /* border: 1px solid hotpink; */
        margin: 0;
        padding: 5px;
    }

    .options-holder {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    details:hover {
        /* cursor: ns-resize; */
        cursor: cell;
    }

    .logo-img {
        background-color: darkgray;
    }

    /* Skeleton Styling  */
    .skeleton {
        animation: skeleton-loading 1s linear infinite alternate;
    }

    @keyframes skeleton-loading {
        0% {
            background-color: hsl(200, 20%, 80%);
        }

        100% {
            background-color: hsl(200, 20%, 95%);
        }
    }

    .skeleton-text {
        /* width: 100%; */
        height: 0.7rem;
        margin-bottom: 0.5rem;
        border-radius: 0.25rem;
    }

    .data-message {
        font-size: x-large;
    }

    .warning {
        font-size: larger;
        display: flex;
        flex-direction: column;
        padding-top: 10px;
        padding-bottom: 10px;
        text-align: center;
        /* border: 1px solid hotpink; */
    }

    .price-warning {
        display: flex;
        flex-direction: column;
        padding-top: 10px;
        padding-bottom: 10px;
        text-align: center;
        border: 1px solid darkred;
    }

    .price-warning-text {
        color: red !important;
        font-size: x-large !important;
        margin-left: 10px !important;
        margin-right: 10px !important;
        ;
    }

    .warning-text {
        color: red !important;
        background-color: pink !important;
        border: 2px solid darkred !important;
        margin-left: 10px !important;
        margin-right: 10px !important;
    }

    .generic-warning {
        font-size: 1rem !important;
        text-align: center;
    }

    .button-holder {
        display: flex;
        justify-content: center;
        gap: 20px;
        padding-top: 20px;
    }

    #requestEdit .styled-table {
        width: 40rem;
    }

    .hidden {
        /* visibility: hidden; */
        display: none;
    }

    /* .skeleton-text__body {
    width: 75%;
}

.skeleton-footer {
    width: 30%;
} */
</style>