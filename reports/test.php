<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

</head>

<body>
    <div class="container">
        <div>
            <img id="img-0" class="active"
                src="https://fastly.4sqi.net/img/general/width960/120712835_4VwqFzE_sJiy_jTheJXq8_w0kvnSsu8dnv3nEee5Eh0.jpg" />
            <!-- <img id="img-1" src="https://berkeleymeansbusiness.com/wp-content/uploads/Berkeley-County-Economic-Department-Home-Banner_1@2x.png" /> -->
            <img id="img-1"
                src="https://d7e3m5n2.stackpathcdn.com/wp-content/uploads/administration-building-blue-sky.jpg" />
        </div>
        <button id="next">NEXT</button>
    </div>
</body>
<script>
const button = document.getElementById("next");
var totalElements = 2;
var index = 0;

button.onclick = function(e) {
    console.log("click");
    var currentImage = document.getElementById(`img-${index}`);
    index++;
    if (index >= totalElements) {
        index = 0;
    }
    var nextImage = document.getElementById(`img-${index}`);
    currentImage.classList.add("transition-start");
    currentImage.classList.add("right");
    nextImage.classList.add("transition-end");
    nextImage.classList.add("right");

    currentImage.onanimationend = function(e) {
        currentImage.classList.remove("active");
        currentImage.classList.remove("transition-start");
        currentImage.classList.remove("right");
    };
    nextImage.onanimationend = function(e) {
        nextImage.classList.add("active");
        nextImage.classList.remove("transition-end");
        nextImage.classList.remove("right");
    }
};
</script>

</html>
<style>
*,
*:after,
*:before {
    margin: 0;
    padding: 0;
    box-sizing: content-box;
}

html,
body {
    width: 100%;
    height: 100%;
}

.container {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.active+img {
    transform: translatex(100%);
}

img:has(* + .active) {
    transform: translatex(-150%);
}

img {
    position: absolute;
    left: 50%;
    transform: translatex(-50%);
    bottom: 0;
    width: 100vw;
    /* height: calc(100vw * 2 / 3); */
    height: 100%;
    filter: blur(0px);
    /* 
  object-fit: cover; 
  object-position: bottom; 
  */
}

img.transition-start.right {
    animation: transition-right-start 0.5s linear forwards;
}

button {
    padding: 10px;
    background-color: #bada55;
    border: 1px solid greenyellow;
    border-radius: 10%;
    box-shadow: 0 0 15px 7px #fff,
        0 0 35px 15px #36d916,
        0 0 45px 22px #1a4f10;
}

@keyframes transition-right-start {
    0% {
        width: 100vw;
        filter: blur(0px);
        /* height: calc(100vw * 2 / 3); */
        height: 100%;
        transform: translatex(-50%);
    }

    3% {
        width: 300vw;
        filter: blur(3px);
        /* height: calc(100vw * 2 / 3); */
        height: 100%;
        transform: translatex(-50%);
    }

    5% {
        width: 600vw;
        filter: blur(3px);
        /* height: calc(100vw * 2 / 3); */
        height: 100%;
        transform: translatex(-50%);
    }

    95% {
        width: 6000vw;
        filter: blur(3px);
        /* height: calc(100vw * 2 / 3); */
        height: 100%;
        transform: translatex(-100%);
    }

    100% {
        width: 100vw;
        filter: blur(0px);
        /* height: calc(100vw * 2 / 3); */
        height: 100%;
        transform: translatex(-150%);
    }
}

img.transition-end.right {
    animation: transition-right-end 0.5s linear forwards;
}

@keyframes transition-right-end {
    0% {
        width: 100vw;
        filter: blur(0px);
        /* height: calc(100vw * 2 / 3); */
        height: 100%;
        transform: translatex(100%);
    }

    3% {
        width: 300vw;
        filter: blur(3px);
        /* height: calc(100vw * 2 / 3); */
        height: 100%;
        transform: translatex(100%);
    }

    5% {
        width: 600vw;
        filter: blur(3px);
        /* height: calc(100vw * 2 / 3); */
        height: 100%;
        transform: translatex(100%);
    }

    95% {
        width: 6000vw;
        filter: blur(3px);
        /* height: calc(100vw * 2 / 3); */
        height: 100%;
        transform: translatex(-50%);
    }

    100% {
        width: 100vw;
        filter: blur(0px);
        /* height: calc(100vw * 2 / 3); */
        height: 100%;
        transform: translatex(-50%);
    }
}

button {
    position: absolute;
    z-index: 999;
    left: 50%;
    transform: translatex(-50%);
}
</style>