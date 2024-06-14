<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pick List</title>
</head>
<script>
async function getList() {
    await fetch('http://localhost/county-store/county-store/pick-list-get.php')
        .then((response => response.json()))
        .then(data => {
            console.table(data);
            // console.log(JSON.stringify(data));
            var html = "<table width='100%' class='paleBlueRows'>";
            html +=
                "<tr><th width='20%'>Qty</th><th width='25%'>Product Code</th><th width='40%'>Product Name</th><th width='30%'>Product Color</th><th width='30%'>Product Size</th></tr>";
            for (var i = 0; i < data.length; i++) {
                html += "<tr>";
                // html += "<tr><td>" + data[i].dep_name + "</td>";
                html += "<td>" + data[i].quantity + "</td>";
                html += "<td>" + data[i].product_code + "</td>"
                html += "<td>" + data[i].product_name + "</td>"
                html += "<td>" + data[i].color_id + "</td>"
                html += "<td>" + data[i].size_name + "</td>"

            }

            document.getElementById('data-container').innerHTML = html;
        });
}
</script>




<body onload='getList()'>
    <div class="row col-lg-12 products-container" id="products-container">
        <div id="data-container">

        </div>
    </div>
</body>

</html>
<style defer>
@font-face {
    font-family: bcFont;
    src: url(./fonts/Gotham-Medium.otf);
}

@font-face {
    font-family: Rye;
    src: url(./fonts/Rye-Regular.ttf)
}

body {
    position: relative;
    background-color: slategray;
    font-family: bcFont !important;
}

table.paleBlueRows {
    border: 1px solid #FFFFFF;
    /* width: 350px; */
    height: 200px;
    text-align: center;
    border-collapse: collapse;
}

table.paleBlueRows td,
table.paleBlueRows th {
    border: 1px solid #FFFFFF;
    padding: 3px 2px;
}

table.paleBlueRows tbody td {
    font-size: 13px;
}

table.paleBlueRows tr:nth-child(even) {
    background: #D0E4F5;
}

table.paleBlueRows thead {
    background: #0B6FA4;
    border-bottom: 5px solid #FFFFFF;
}

table.paleBlueRows thead th {
    font-size: 17px;
    font-weight: bold;
    color: #FFFFFF;
    text-align: center;
    border-left: 2px solid #FFFFFF;
}

table.paleBlueRows thead th:first-child {
    border-left: none;
}

table.paleBlueRows tfoot {
    font-size: 14px;
    font-weight: bold;
    color: #333333;
    background: #D0E4F5;
    border-top: 3px solid #444444;
}

table.paleBlueRows tfoot td {
    font-size: 14px;
}

table.darkTable {
    font-family: "Arial Black", Gadget, sans-serif;
    border: 2px solid #000000;
    background-color: #4A4A4A;
    width: 100%;
    height: 200px;
    text-align: center;
    border-collapse: collapse;
}

table.darkTable td,
table.darkTable th {
    border: 1px solid #4A4A4A;
    padding: 3px 2px;
}

table.darkTable tbody td {
    font-size: 13px;
    color: #E6E6E6;
}

table.darkTable tr:nth-child(even) {
    background: #888888;
}

table.darkTable thead {
    background: #000000;
    border-bottom: 3px solid #000000;
}

table.darkTable thead th {
    font-size: 15px;
    font-weight: bold;
    color: #E6E6E6;
    text-align: center;
    border-left: 2px solid #4A4A4A;
}

table.darkTable thead th:first-child {
    border-left: none;
}

table.darkTable tfoot {
    font-size: 12px;
    font-weight: bold;
    color: #E6E6E6;
    background: #000000;
    background: -moz-linear-gradient(top, #404040 0%, #191919 66%, #000000 100%);
    background: -webkit-linear-gradient(top, #404040 0%, #191919 66%, #000000 100%);
    background: linear-gradient(to bottom, #404040 0%, #191919 66%, #000000 100%);
    border-top: 1px solid #4A4A4A;
}

table.darkTable tfoot td {
    font-size: 12px;
}
</style>