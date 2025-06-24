<?php 

session_start();
if (!isset($_SESSION["pa_loggedin"]) || $_SESSION["pa_loggedin"] !== true) {
    header("location: login-ldap.php");
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
    <link rel="stylesheet" href="prod-admin-style.css">
    <title>Edit Request</title>
</head>

<script>
function makeSkeleton() {
    const tableBody = document.getElementById('table-body');
    const rowTemplate = document.getElementById("row-template")
    for (let i = 0; i < 20; i++) {
        tableBody.append(rowTemplate.content.cloneNode(true));
    }
    loadRequests();
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
            // console.log(data);
            const tableBody = document.getElementById('table-body');
            const rowTemplate = document.getElementById("row-template")
            tableBody.innerHTML = "";
            data.forEach((order) => {
                const tr = rowTemplate.content.cloneNode(true);
                // console.log(tr);
                tr.getElementById("order_details_id").textContent = order.order_details_id;
                tr.getElementById("order_id").textContent = order.order_id;
                tr.getElementById("product_name").textContent = order.name;
                tr.getElementById("price").textContent = order.item_price;
                tr.getElementById("vendor").textContent = order.vendor_id;
                tr.getElementById("dept_name").textContent = order.deptName;
                tr.getElementById("submitted_by").textContent = order.submitted_by_name;
                tr.getElementById("status").textContent = order.status;
                var editButton = tr.getElementById("edit_button");
                // console.log(editButton);
                // tr.getElementById("edit_button").onclick = "editRequest(" + order.order_details_id + ")";
                // tr.getElementById("blank").textContent = "";
                tableBody.append(tr);
                removeSkeleton();
            })
            // var html = ""
            // html += `
            // <table class="styled-table">
            //         <thead>
            //             <tr> 
            //                 <th>Order Details ID</th>
            //                 <th>Order ID</th>
            //                 <th>Product Name</th>
            //                 <th>Price</th>
            //                 <th>Vendor</th>
            //                 <th>Dept Name</th>
            //                 <th>Submitted By</th>
            //                 <th>Status</th>
            //                 <th></th>
            //             </tr>
            //         </thead>
            //         <tbody>
            // `
            // for (var i = 0; i < data.length; i++) {
            //     html += `
            //     <tr>
            //         <td>${data[i].order_details_id}</td>
            //         <td>${data[i].order_id}</td>
            //         <td>${data[i].name}</td>
            //         <td>${data[i].item_price}</td>
            //         <td>${data[i].vendor_id}</td>
            //         <td>${data[i].deptName}</td>
            //         <td>${data[i].created}</td>
            //         <td>${data[i].status}</td>
            //         <td><button popovertarget='requestEdit' onclick="editRequest(${data[i].order_details_id})">Edit</button></td>
            //     </tr>
            //     </tbody>
            //     </table>
            // `
            // }
            // document.getElementById('requests-table').innerHTML = html;
        })
}

async function editRequest(id) {
    // alert('Editing ' + id);
    await fetch('load-single-request.php?id=' + id)
        .then((response) => response.json())
        .then((data) => {
            // console.log(data);
            var html = "";
            html += `
            <div class='edit-container'>
                <table class='styled-table'>
                    <h2>Existing Request for <mark>${data[0].submitted_by_name}</mark> </h2>
                    <thead>
                        <tr>
                            <th></th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="bold">Product Code</td><td>${data[0].code}</td>
                        </tr>
                        <tr>
                            <td class="bold">Name</td><td>${data[0].name}</td>
                        </tr>
                        <tr>
                            <td class="bold">Vendor</td><td>${data[0].vendor_id}</td>
                        </tr>
                        <tr>
                            <td class="bold"></td><td>${data[0].code}</td>
                        </tr>
                        <tr>
                            <td class="bold">Product Code</td><td>${data[0].code}</td>
                        </tr>
                        <tr>
                            <td class="bold">Product Code</td><td>${data[0].code}</td>
                        </tr>
                        <tr>
                            <td class="bold">Product Code</td><td>${data[0].code}</td>
                        </tr>

                    </tbody>
            </div>
            `
            document.getElementById('edit-table').innerHTML = html;
        })
}
</script>

<!-- <body onload="loadRequests()"> -->

<body onload="makeSkeleton()">

    <!-- <body> -->
    <?php include "nav.php" ?>
    <h1>Request Edit Page </h1>
    <div id="requests-table">
        <table class="styled-table">
            <thead class="skeleton">
                <tr>
                    <th class="skeleton">Order Details ID</th>
                    <th class="skeleton">Order ID</th>
                    <th class="skeleton">Product Name</th>
                    <th class="skeleton">Price</th>
                    <th class="skeleton">Vendor</th>
                    <th class="skeleton">Dept Name</th>
                    <th class="skeleton">Submitted By</th>
                    <th class="skeleton">Status</th>
                    <th class="skeleton">&#128280;</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <template id="row-template">
                    <tr>
                        <td id="order_details_id" class="skeleton" width="10%">XXXX</td>
                        <td id="order_id" class="skeleton" width="10%">XXXX</td>
                        <td id="product_name" class="skeleton" width="25%">XXX X XXXXXXX XXXXX</td>
                        <td id="price" class="skeleton" width="5%">XX.XX</td>
                        <td id="vendor" class="skeleton" width="5%">X</td>
                        <td id="dept_name" class="skeleton" width="15%">XXXXXXXXXX</td>
                        <td id="submitted_by" class="skeleton" width="20%">XXXXXXX XXXXXX</td>
                        <td id="status" class="skeleton" width="10%">XXXXXXX</td>
                        <!-- <td id="blank" class="skeleton">-</td> -->
                        <!-- <td class="skeleton skeleton-text">
                        <button popovertarget='requestEdit' onclick="editRequest(${data[i].order_details_id})">Edit</button>
                    </td> -->
                        <td id="edit-button" class="skeleton">
                            <button class="skeleton" id="edit_button" popovertarget="requestEdit">Edit</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <div id="requestEdit" popover=manual>
        <button class="close-btn" popovertarget="requestEdit" popovertargetaction="hide">
            <span aria-hidden=”true”>❌</span>
            <span class="sr-only">Close</span>
        </button>
        <!-- <button onclick="setValues()">Set Vals</button> -->
        <div id="edit-table"></div>
        <div id=edit-form></div>
    </div>

    <script>
    function removeSkeleton() {
        // console.log("Removing the bones");
        const skeletonItems = document.querySelectorAll('.skeleton')
        skeletonItems.forEach((item) => {
            item.classList.remove('skeleton');
        })
    }

    // Function to generate a random string
    function generateRandomString(min, max) {
        const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+';
        const minLength = min; // Minimum length of each segment
        const maxLength = max; // Maximum length of each segment
        const numSegments = Math.floor(Math.random() * (6 - 4 + 1)) + 4; // Random number of segments
        let randomString = '';

        for (let i = 0; i < numSegments; i++) {
            const segmentLength = Math.floor(Math.random() * (maxLength - minLength + 1)) + minLength;
            let segment = '';

            for (let j = 0; j < segmentLength; j++) {
                const randomIndex = Math.floor(Math.random() * alphabet.length);
                segment += alphabet[randomIndex];
            }

            randomString += segment;

            // Add 3 or 4 spaces except for the last segment
            if (i !== numSegments - 1) {
                const numSpaces = Math.random() < 0.5 ? 3 : 4;
                for (let k = 0; k < numSpaces; k++) {
                    randomString += ' ';
                }
            }
        }

        return randomString;
    }

    function generateRandomNums(min, max, seg) {
        const nums = '0123456789!@#$%^&*()_+';
        const minLength = min;
        const maxLength = max;
        const numSegments = seg;
        let randomNums = "";

        for (let i = 0; i < numSegments; i++) {
            const segmentLength = Math.floor(Math.random() * (maxLength - minLength + 1)) + minLength;
            let segment = '';

            for (let j = 0; j < segmentLength; j++) {
                const randomIndex = Math.floor(Math.random() * nums.length);
                segment += nums[randomIndex];
            }

            randomNums += segment;

            if (i !== numSegments - 1) {
                // const numSpaces = Math.random() < 0.5 ? 3 : 4;
                const numSpaces = 1;
                for (let k = 0; k < numSpaces; k++) {
                    randomNums += '.';
                }
            }
        }
        return randomNums;
    }

    function generateRandomNames(min, max, seg) {
        const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+';
        const minLength = min; // Minimum length of each segment
        const maxLength = max; // Maximum length of each segment
        const numSegments = seg; // Adjusted to generate only two segments
        let randomName = '';

        for (let i = 0; i < numSegments; i++) {
            const segmentLength = Math.floor(Math.random() * (maxLength - minLength + 1)) + minLength;
            let segment = '';

            for (let j = 0; j < segmentLength; j++) {
                const randomIndex = Math.floor(Math.random() * alphabet.length);
                segment += alphabet[randomIndex];
            }

            randomName += segment;

            // Add space between segments
            if (i !== numSegments - 1) {
                const numSpaces = Math.random() < 0.5 ? 3 : 4;
                for (let k = 0; k < numSpaces; k++) {
                    randomName += ' ';
                }
            }
        }

        return randomName;
    }



    // Select all <td> elements with class "skeleton"
    function makeSilly() {
        const skeletonItems = document.querySelectorAll('#product_name');
        // console.log(skeletonItems);
        // Loop through each <td> element and set contents
        skeletonItems.forEach(function(td) {
            // Generate a random string
            const randomString = generateRandomString(1, 6);
            td.textContent = randomString;
        });

        const skeletonPrices = document.querySelectorAll('#price');
        // console.log(skeletonPrices);
        skeletonPrices.forEach(function(rp) {
            const randomNums = generateRandomNums(2, 3, 2);
            // console.log(randomNums);
            rp.textContent = randomNums;
        });

        const skeletonNames = document.querySelectorAll('#submitted_by');
        skeletonNames.forEach(function(rn) {
            const randomName = generateRandomNames(4, 9, 2);
            rn.textContent = randomName;
        });

        const skeletonDepts = document.querySelectorAll('#dept_name');
        skeletonDepts.forEach(function(dn) {
            const randomName = generateRandomNames(5, 6, 2);
            dn.textContent = randomName;
        });

        const skeletonOrders = document.querySelectorAll("#order_id");
        skeletonOrders.forEach(function(ro) {
            const randomOrderNum = generateRandomNums(2, 3, 1);
            ro.textContent = randomOrderNum;
        });

        const skeletonOrderIds = document.querySelectorAll("#order_details_id");
        skeletonOrderIds.forEach(function(so) {
            const randomOrderIdNum = generateRandomNums(2, 3, 1);
            so.textContent = randomOrderIdNum;
        });

        const skeltonStatus = document.querySelectorAll("#status");
        skeltonStatus.forEach(function(ss) {
            const randomStatus = generateRandomNames(4, 6, 1);
            ss.textContent = randomStatus;
        })

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

.close-btn {
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

.close-btn:hover {
    transform: scale(.95);
    border: 2px solid green;
    box-shadow: 0px 0px, 0px 0px, 0px 0px, 1px 1px;
}

#requests-table {
    margin-left: 15%;
    margin-right: auto;
}


#requestEdit {
    margin: auto;
    /* position: fixed; */
    width: 90%;
    height: 90%;
    border: 5px solid tomato;
    /* overflow: hidden; */
    box-shadow: 1px 1px, 2px 2px, 3px 3px, 4px 4px;
}

#requestEdit::backdrop {
    backdrop-filter: blur(5px);
}

#edit-table {
    display: grid;
    /* grid-template-rows: 1fr 1fr; */
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

#edit-form {
    border-top: 5px solid tomato;
    padding: 10px;
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

/* .skeleton-text__body {
    width: 75%;
}

.skeleton-footer {
    width: 30%;
} */
</style>