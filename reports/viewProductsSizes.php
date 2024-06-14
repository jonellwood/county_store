<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products and Colors</title>

    <script>
    function removeBracketsAndQuotes(str) {
        str = str.slice(1, -1);
        str = str.replace(/"/g, '');
        return str;
    }


    async function getProducts() {
        await fetch('getProductsSizes.php')
            .then((response) => response.json())
            .then((data) => {
                console.log(data[0].json_data);
                html = '<table class="styled-table">';
                html += '<thead><tr>';
                html +=
                    '<th>Product Code</th><th>Name</th><th>Sizes</th>';
                html += '</tr></thead>';
                html += '<tbody>';
                for (var i = 0; i < data.length; i++) {
                    html += '<tr>';
                    html += '<td>' + data[i].code + '</td>';
                    html += '<td>' + data[i].name + '</td>';
                    html += '<td class="not-centered">' + removeBracketsAndQuotes(data[i].sizes) + '</td>';
                    html += '</tr>';
                }
                html += '</tbody>';
                html += '<table>';
            })
        document.getElementById('table-data').innerHTML = html;
    }
    </script>
</head>
<?php include "Header.php" ?>

<body onload="getProducts()">

    <div id="table-data"></div>
</body>

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

img {
    width: 50px !important;
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
    text-align: center;
}

.styled-table tbody tr {
    border-bottom: 1px solid #74E193;

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

.endPrice {
    border-right: 2px solid darkgrey;
}

.secondary-head {
    background-color: lightgrey !important;
    color: black !important;
}

.start-sizes {
    border-left: 2px solid darkgreen;
}

.white-text {
    /* color: #f3f3f3; */
    visibility: hidden;
}

.note {
    /* text-align: center; */
    font-size: medium;
    margin-left: 5%;
    margin-right: 30%;
}

ul {
    list-style: none;
}

.tab {
    overflow: hidden;
    border: 1px solid #ccc;
    background-color: #f1f1f1;
}

/* Style the buttons that are used to open the tab content */
.tab button {
    background-color: inherit;
    float: left;
    border: none;
    outline: none;
    cursor: pointer;
    padding: 14px 16px;
    transition: 0.3s;
}

/* Change background color of buttons on hover */
.tab button:hover {
    background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
    background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
    display: none;
    padding: 6px 12px;
    border: 1px solid #ccc;
    border-top: none;
}

.tabcontent {
    animation: fadeEffect 1s;
    /* Fading effect takes 1 second */
}

.not-centered {
    text-align: left !important;
}

/* Go from zero to full opacity */
@keyframes fadeEffect {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}
</style>