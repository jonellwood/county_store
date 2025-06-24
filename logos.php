<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>See Logos</title>
</head>

<script>
async function getLogos() {
    await fetch('./logosfetch.php')
        .then((response => response.json()))
        .then(data => {
            // console.table(data);
            // console.log(JSON.stringify(data));
            var html = "<table width='100%' class='paleBlueRows'>";
            html +=
                "<tr><th width='25%'>Logo Name</th><th width='40%'>Logo Image</th><th width='30%'>Logo Description</th></tr>";
            for (var i = 0; i < data.length; i++) {
                html += "<tr>";


                html += "<td>" + data[i].logo_name + "</td>"
                html +=
                    "<td> <img src='" + data[i].image +
                    "' width='200px' class='littleLogo' alt='logo image' onclick=\"chromeSucks('" + data[i]
                    .image + "')\"></td>";

                html += "<td>" + data[i].description + "</td>"
            }

            document.getElementById('data-container').innerHTML = html;
        });
}

function chromeSucks(imageSrc) {
    console.log('chromeSucks');
    var popover = document.getElementById('imagePopover');
    var popoverImage = document.getElementById('popoverImage');
    console.log(popoverImage);
    var scrollY = window.scrollY || window.pageYOffset;

    popoverImage.src = imageSrc; // Set the src

    // calculate the position of the popover
    var rect = event.target.getBoundingClientRect();
    // console.log(rect);
    // console.log(rect.bottom);
    // console.log(scrollY);
    popover.style.top = rect.bottom - scrollY + 'px';
    popover.style.left = (rect.left + rect.right) / 2 + 'px';
    popover.style.marginTop = (scrollY + 50) + 'px';
    popover.style.position = 'absolute';
    popover.style.top = 100 + 'px';
    popover.style.right = 100 + 'px';
    popover.style.bottom = 100 + 'px';
    popover.style.left = 100 + 'px';

    // Toggle popover
    popover.style.display = (popover.style.display === 'none' || popover.style.display === '') ? 'block' : 'none';
}

function closePopover() {
    var popover = document.getElementById('imagePopover');
    popover.style.display = 'none';
}
</script>

<body onload="getLogos()">
    <div class="row col-lg-12 products-container" id="products-container">
        <div id="data-container">

        </div>
        <p>If the "No Department Name" is selected from the order page the logo will look exactly like the one pictured
            only without the department name</p>
    </div>
    <div id="imagePopover" class="popover">
        <button class="close-btn">
            <span class="close-text" aria-hidden=”true” onclick="closePopover()">❌</span>
            <span class="sr-only close-text" onclick="closePopover()">Close</span>
        </button>
        <img id="popoverImage" alt="Large Image" class="popoverImage">
    </div>

</body>
<style>
body {
    background-color: lightgray;
    ;
}

#data-container {
    margin: 0 auto;
    width: 65%;
    padding: 10px;
    /* background-color: white; */
    border-radius: 10px;
    box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.1);
    margin-top: 10px;
    margin-bottom: 10px;
}

table {
    border-collapse: collapse;
    width: 100%;
}

table tr {
    border: 1px solid darkgrey;

}

table tr td {
    padding: 20px;

}

.popover {
    display: none;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    margin-left: 20%;
    margin-right: 20%;
    margin-top: 10%;
    margin-bottom: 10%;
    background-color: #fff;
    border: 1px solid #ccc;
    padding: 10px;
    /* z-index: 1000; */
    background-color: lightgray;
    width: fit-content;
}

.littleLogo {
    /* margin-left: 35%; */
    box-shadow: 0px 15px 15px 3px rgba(0, 0, 0, 0.75);
}

.popoverImage {
    padding: 10px;
    border: 5px solid white;
    width: 550px;
    height: auto;
    background-color: grey;
}


.close-btn {
    border: none;
    background: none;
    color: tomato;
    position: absolute;
    right: 0.25rem;
    top: 0.5rem;
    filter: grayscale() brightness(20);
    cursor: pointer;
}

.close-text {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.5;
    transition: opacity 0.25s ease-in-out;
}

p {
    text-align: center;
    font-size: medium;
    color: darkgray
}
</style>