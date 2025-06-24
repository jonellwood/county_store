<?php
/*
Author: Jon Ellwood
Organization: Berkeley County IT Department
Last Updated: 07/05/2024
Purpose: Footer element across application. setAppVersion gets the current app version from the changelog and displays it in the footer if known. If unknown it displays the word "Documentation" to make it look like it was on purpose.
Includes:     setAppVersion.php
*/
include_once("setAppVersion.php");

echo "<footer>";

echo "<div class='footer-holder'>";

echo "<p>&#169; " . date("Y") . "</p>";

echo "<p> Developed by <a href='https://berkeleycountysc.gov/dept/it/' class='font-weight-bold' target='_blank'>Berkeley County Information Technology</a></p>";

// echo "<p><a href='https://store.berkeleycountysc.gov/changelogView.php' target='_blank'> App Version " . $_SESSION['appVersion'] . "</a></p>";
// echo "<p> <a href='https://store.berkeleycountysc.gov/changelogView.php' target='_blank'><i class='fa fa-code-branch'></i> Change Log</a></p>";
echo "<p><a href='https://store.berkeleycountysc.gov/changelogView.php' target='_blank'> App Version " . ($_SESSION['appVersion'] == 'Unknown' ? 'Documentation' : $_SESSION['appVersion']) . "</a></p>";
// echo "<p><a href='products-by-communications.php'><i class='fa fa-phone'></i> 911</a></p>";
echo "</div>";

echo "</footer>";

?>

<style>
    .footer-holder {
        /* position: relative; */
        position: fixed;
        margin-top: 10px;
        bottom: 0;
        display: flex;
        justify-content: space-between;
        padding-top: 20px;
        padding-left: 5%;
        padding-right: 5%;
        background-color: #ffffff;
        color: #000000;
        z-index: 0;
        width: 100vw;
    }


    a {
        text-decoration: none;
        color: inherit;
        /* font-weight: bolder; */
    }

    .far-right {
        margin-left: 100px;
    }
</style>