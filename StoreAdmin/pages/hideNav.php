<div class="mini-side-nav">
    <div class="offcanvas-body" data-title="Welcome" data-intro="This is your nav. You've used a nav before right? " data-step="1">
        <div>
            <ul>
                <li class="nav-item">
                    <a class="nav-link " href="./employeeRequests.php">
                        <span class="nav-link-text ms-1">&#127968; Home </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../../inventory/index.php">
                        <span class="nav-link-text ms-1">&#128104;&#8205;&#128187; Inventory</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="../../index.php">
                        <span class="nav-link-text ms-1">&#127980; Store</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="../../support.php">
                        <span class="nav-link-text ms-1">
                            &#128129; Help
                        </span>
                    </a>
                </li>
                <?php
                if ($_SESSION['role_name'] == 'Administrator') {
                    echo "
                    <li class='nav-item'>
                        <a class='nav-link' href='edit-request-ui.php'>
                            <span class='nav-link-text ms-1'>
                            &#129689;
                            Edit Requests
                            
                            </span>
                        </a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='orders-by-dept-for-admin.php'>
                            <span class='nav-link-text ms-1'>
                            &#8986;
                            Awaiting Order
                            </span>
                        </a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='orders-to-be-received.php'>
                            <span class='nav-link-text ms-1'>
                            &#128666;
                            Receiving
                            </span>
                        </a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='edit-users.php'>
                            <span class='nav-link-text ms-1'>
                            &#129318;
                            Edit Users
                            </span>
                        </a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='dept-admin.php'>
                            <span class='nav-link-text ms-1'>
                            &#129377;
                            Edit Dept
                            </span>
                        </a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='add-product-ui.php'>
                            <span class='nav-link-text ms-1'>
                            &#129518;
                            Add Product
                            </span>
                        </a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='edit-product-ui.php'>
                            <span class='nav-link-text ms-1'>
                            &#129650;
                            Edit Product
                            </span>
                        </a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='./reports.php'>
                            <span class='nav-link-text ms-1'>
                            &#129531;
                            Reports
                            </span>
                        </a>
                    </li>
                        <li class='nav-item'>
                        <a class='nav-link' href='overview.php'>
                            <span class='nav-link-text ms-1'>
                            &#128117;&#127996;
                            Old Dashboard
                            </span>
                        </a>
                    </li>
                    ";
                }
                // var_dump($_SESSION);

                ?>
                <li class="nav-item">
                    <a class="nav-link " href="../../logout.php">
                        <span class="nav-link-text ms-1 red">
                            &#128473;
                            Logout
                        </span>
                    </a>
                </li>
                <?php
                if ($_SESSION['role_name'] == 'Administrator') {
                    echo "
                <li id='alert-on-nav' class='nav-item'>
                </li>
                ";
                } ?>
            </ul>
            <img src="../assets/img/bcg12.png" alt="bgc logo" />
            <!-- <p class='cool-tip'>
                Looking for orders already received? <br>
                Use the <mark> <a href="https://localhost/county-store/inventory/login-ldap.php"> inventory management tool</a>
                </mark>
            </p> -->
        </div>
    </div>
</div>

<script>
    async function checkForWaiting() {
        // console.log('checking for waiting....')
        await fetch('check-for-waiting-get.php')
            .then((response) => response.json())
            .then((data) => {
                // console.log(data);
                if (data[0].status == null) {

                } else {
                    if (data.length > 0) {
                        var whtml = "";
                        // whtml += "<div id='waiting-alerts-notify'>";
                        whtml += "<a href='view-request.php'><mark class='red-alert'>ALERT (" + data.length +
                            ")</mark>";
                        whtml += "<p>&#128276;</p></a>"
                        // whtml += "<button popovertarget='see-alerts' popovertargetaction='show'>See Alerts</button>";
                        // whtml += "</div>";
                        whtml += "<div id='see-alerts' popover=manual>";
                        whtml +=
                            "<button class='close-btn' popovertarget='see-alerts' popovertargetaction='hide' onclick='hideOffCanvas()'>";
                        whtml += "<span aria-hidden='true'> ❌ </span>";
                        whtml += "<span class='sr-only'> Close </span>";
                        whtml += "</button>";
                        whtml += "<table><thead>";
                        whtml += "<tr><th>Requested for</th> <th>Req Submitted</th> <th>Last Contacted</th> </tr>"
                        whtml += "</thead><tbody>"
                        for (var i = 0; i < data.length; i++) {
                            whtml += "<tr><td>" + data[i].requested_by_name + " " + data[i].requested_by_last +
                                "</td><td>" + data[i].created + "</td><td>" + data[0].last_contact + "</tr>";
                        }
                        whtml += "</tbody></table>";
                        whtml += "</div>";
                        // document.getElementById('alert-holder').innerHTML = whtml;
                        document.getElementById('alert-on-nav').innerHTML = whtml;
                    }
                }
            })
    }
    <?php if ($_SESSION['role_name'] == 'Administrator') {
        echo 'checkForWaiting()';
    } ?>
    // checkForWaiting();
</script>
<!-- </?php
if ($_SESSION['role_name'] == 'Administrator') {
    echo 'checkForWaiting()';
}
?> -->
<style>
    .mini-side-nav {
        /* padding: 10px; */
        box-shadow: 10px 0px 38px -17px rgba(59, 54, 59, 1);
        background-color: #00000099;
        color: #FFFFFF;
        height: 99dvh;

        img {
            position: fixed;
            bottom: 0;
            width: 9%;
            padding-bottom: 12px;
        }
    }

    .offcanvas-body {
        padding-top: 10px !important;
    }

    ul {
        list-style-type: none;
        padding: 10px;
    }

    .nav-item {
        a {
            color: #ffffff !important;
            padding-left: 5px;
            margin-top: 5%;
        }
    }

    /* a {
        display: flex;
        justify-content: space-between;
        padding-bottom: 10px;
        font-size: large !important;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
    } */


    /* .nav-link a p {
        margin-right: 10px;
    } */

    .red {
        color: red !important;
    }

    /* img {
        margin-bottom: 10%;
        width: 100px;
        margin-left: auto;
        margin-right: auto;
    } */

    .cool-tip {
        font-family: monospace;
        margin-top: 10px;
        margin-left: 4px;
        max-width: min-content;
        text-align: center;
        padding: 10px;
        border-top: 1px double darkslategray;
    }

    .cool-tip a {
        font-size: medium !important;
        font-weight: bold;
    }
</style>