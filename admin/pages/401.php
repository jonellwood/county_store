<?php
session_start();
?>

<h1>You requested a page that you do not have permissions for.</h1>
<p>If you feel you need access to the page please contact the <a href="mailto:store@berkeleycountysc.gov">Store Support
        Team. <button class="btn btn-outline-dark" type="button"><a href="<?php echo $_SERVER["HTTP_REFERER"] ?>"> Back
            </a></button></p>
<div class='centered'>
    <img src="../assets/img/error_401.jpg" alt="Hold Up!" />
</div>


<!-- <button type="button"><a href="../index.php"> Home </a></button> -->
<style>
    body {
        margin: 20px;
        padding: 20px;
        background-color: antiquewhite;
    }

    h1,
    p {
        text-align: center;
    }

    .centered {
        display: flex;
        justify-content: center;
    }

    img {
        margin-top: 5%;
        width: 50vw;
        box-shadow: 10px 10px 5px 0px rgba(87, 80, 87, 1);
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    .btn {
        margin-left: 20px;
        padding: 5px;
        border: 1px solid #f09d09;
        border-radius: 5px;
        text-decoration: none;
    }

    .btn:hover {
        background-color: black;
        color: white;
        text-decoration: none;
        box-shadow: 0px 0px 25px -5px rgba(87, 80, 87, 1);
    }
</style>