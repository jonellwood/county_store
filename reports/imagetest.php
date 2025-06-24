<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <a href="https://store.berkeleycountysc.gov/product-details-onefee.php?product_id=14" alt="Mythrill" target="_blank">
        <div class="card">
            <div class="wrapper">
                <img src="shirt.png" class="cover-image" />
            </div>
            <img src="name.png" class="title" />
            <img src="model.png" class="character" />
        </div>
    </a>

    <!-- <a href="https://www.mythrillfiction.com/force-mage" alt="Mythrill" target="_blank">
        <div class="card">
            <div class="wrapper">
                <img src="https://ggayane.github.io/css-experiments/cards/force_mage-cover.jpg" class="cover-image" />
            </div>
            <img src="https://ggayane.github.io/css-experiments/cards/force_mage-title.png" class="title" />
            <img src="https://ggayane.github.io/css-experiments/cards/force_mage-character.webp" class="character" />
        </div>
    </a> -->
    <!-- Explanation in JS tab -->

    <!-- The two texts -->
    <div id="container">
        <span id="text1"></span>
        <span id="text2"></span>
    </div>

    <!-- The SVG filter used to create the merging effect -->
    <svg id="filters">
        <defs>
            <filter id="threshold">
                <!-- Basically just a threshold effect - pixels with a high enough opacity are set to full opacity, and all other pixels are set to completely transparent. -->
                <feColorMatrix in="SourceGraphic" type="matrix" values="1 0 0 0 0
									0 1 0 0 0
									0 0 1 0 0
									0 0 0 255 -140" />
            </filter>
        </defs>
    </svg>

</body>
<script>
    const elts = {
        text1: document.getElementById("text1"),
        text2: document.getElementById("text2")
    };

    // The strings to morph between. You can change these to anything you want!
    const texts = [
        "Hover",
        "over",
        "the",
        "shirt",
        "and",
        "prepare",
        "to",
        "be",
        "amazed"
    ];

    // Controls the speed of morphing.
    const morphTime = 1;
    const cooldownTime = 0.25;

    let textIndex = texts.length - 1;
    let time = new Date();
    let morph = 0;
    let cooldown = cooldownTime;

    elts.text1.textContent = texts[textIndex % texts.length];
    elts.text2.textContent = texts[(textIndex + 1) % texts.length];

    function doMorph() {
        morph -= cooldown;
        cooldown = 0;

        let fraction = morph / morphTime;

        if (fraction > 1) {
            cooldown = cooldownTime;
            fraction = 1;
        }

        setMorph(fraction);
    }

    // A lot of the magic happens here, this is what applies the blur filter to the text.
    function setMorph(fraction) {
        // fraction = Math.cos(fraction * Math.PI) / -2 + .5;

        elts.text2.style.filter = `blur(${Math.min(8 / fraction - 8, 100)}px)`;
        elts.text2.style.opacity = `${Math.pow(fraction, 0.4) * 100}%`;

        fraction = 1 - fraction;
        elts.text1.style.filter = `blur(${Math.min(8 / fraction - 8, 100)}px)`;
        elts.text1.style.opacity = `${Math.pow(fraction, 0.4) * 100}%`;

        elts.text1.textContent = texts[textIndex % texts.length];
        elts.text2.textContent = texts[(textIndex + 1) % texts.length];
    }

    function doCooldown() {
        morph = 0;

        elts.text2.style.filter = "";
        elts.text2.style.opacity = "100%";

        elts.text1.style.filter = "";
        elts.text1.style.opacity = "0%";
    }

    // Animation loop, which is called every frame.
    function animate() {
        requestAnimationFrame(animate);

        let newTime = new Date();
        let shouldIncrementIndex = cooldown > 0;
        let dt = (newTime - time) / 1000;
        time = newTime;

        cooldown -= dt;

        if (cooldown <= 0) {
            if (shouldIncrementIndex) {
                textIndex++;
            }

            doMorph();
        } else {
            doCooldown();
        }
    }

    // Start the animation.
    animate();
</script>

</html>
<style>
    @import url('https://fonts.googleapis.com/css?family=Raleway:900&display=swap');

    :root {
        --card-height: 300px;
        --card-width: calc(var(--card-height) / 1.5);
    }

    * {
        box-sizing: border-box;
    }

    body {
        width: 100vw;
        height: 100vh;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #191c29;
    }

    .card {
        width: var(--card-width);
        height: var(--card-height);
        position: relative;
        display: flex;
        justify-content: center;
        align-items: flex-end;
        padding: 0 36px;
        perspective: 2500px;
        margin: 0 50px;
    }

    .cover-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .wrapper {
        transition: all 0.5s;
        position: absolute;
        width: 100%;
        z-index: -1;
    }

    .card:hover .wrapper {
        transform: perspective(900px) translateY(-5%) rotateX(25deg) translateZ(0);
        box-shadow: 2px 35px 32px -8px rgba(0, 0, 0, 0.75);
        -webkit-box-shadow: 2px 35px 32px -8px rgba(0, 0, 0, 0.75);
        -moz-box-shadow: 2px 35px 32px -8px rgba(0, 0, 0, 0.75);
    }

    .wrapper::before,
    .wrapper::after {
        content: "";
        opacity: 0;
        width: 100%;
        height: 80px;
        transition: all 0.5s;
        position: absolute;
        left: 0;
    }

    .wrapper::before {
        top: 0;
        height: 100%;
        background-image: linear-gradient(to top,
                transparent 46%,
                rgba(12, 13, 19, 0.5) 68%,
                rgba(12, 13, 19) 97%);
    }

    .wrapper::after {
        bottom: 0;
        opacity: 1;
        background-image: linear-gradient(to bottom,
                transparent 46%,
                rgba(12, 13, 19, 0.5) 68%,
                rgba(12, 13, 19) 97%);
    }

    .card:hover .wrapper::before,
    .wrapper::after {
        opacity: 1;
    }

    .card:hover .wrapper::after {
        height: 120px;
    }

    .title {
        width: 100%;
        transition: transform 0.5s;
    }

    .card:hover .title {
        transform: translate3d(0%, -50px, 100px);
    }

    .character {
        width: 100%;
        opacity: 0;
        transition: all 0.5s;
        position: absolute;
        z-index: -1;
    }

    .card:hover .character {
        opacity: 1;
        transform: translate3d(0%, -30%, 100px);
    }

    #container {
        /* Center the text in the viewport. */
        position: relative;
        color: cornflowerblue;
        /* margin: auto; */
        /* width: 100vw; */
        height: 50pt;
        top: 1;
        bottom: 0;

        /* This filter is a lot of the magic, try commenting it out to see how the morphing works! */
        filter: url(#threshold) blur(0.6px);
    }

    /* Your average text styling */
    #text1,
    #text2 {
        position: absolute;
        width: 100%;
        display: inline-block;

        font-family: 'Raleway', sans-serif;
        font-size: 80pt;

        text-align: center;

        user-select: none;
    }
</style>