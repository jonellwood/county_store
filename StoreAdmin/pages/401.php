<?php

?>

<h1>You requested a page that you do not have permissions for. If you feel you need access to the page please
    contact
    the help desk.</h1>
<div class='centered'>
    <img src="../assets/img/error_401.jpg" alt="Hold Up!" />
</div>
<!-- <button type="button"><a href="</?php echo $_SERVER["HTTP_REFERER"] ?>"> Back </a></button> -->
<!-- <button type="button"><a href="../index.php"> Home </a></button> -->
<style>
    body {
        margin: 20px;
        padding: 20px;
        background-color: antiquewhite;
    }

    h1 {
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
</style>