<?php
include('DBConn.php');
?>
<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {

    header("location: sign-in.php");

    exit;
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Invoices</title>
</head>

<body>
    <center>
        <?php
        session_start();
        $empNumber = $_SESSION["empNumber"];

        if (isset($_FILES['files'])) {
            $errors = array();
            foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
                $file_name = $key . $_FILES['files']['name'][$key];
                $file_tmp = $_FILES['files']['tmp_name'][$key];
                //FORIMAGE
                // Taking all values from the form data(input)
                $invoice =  $_REQUEST['invoice'];
                $amount = $_REQUEST['amount'];
                $dept = $_REQUEST['dept'];
                // $uid = dechex(microtime(true) * 1000) . bin2hex(random_bytes(8));
                // $uid = $_POST['uid']; 

                // $query = "INSERT into case_notes (filename) VALUES('$file_name'); ";
                $sql = "INSERT INTO pdf (invoice_id, file_name, uploaded_on, uploaded_by, invoice_amount, department) VALUES ('$invoice','$file_name',now(),'$empNumber','$amount','$dept');";
                //FORIMAGE
                $desired_dir = "assets/pdf/";
                if (empty($errors) == true) {
                    if (is_dir($desired_dir) == false) {
                        mkdir("$desired_dir", 0700);        // Create directory if it does not exist
                    }
                    if (is_dir("$desired_dir/" . $file_name) == false) {
                        move_uploaded_file($file_tmp, "../assets/pdf/" . $file_name);
                    } else {
                        //rename the file if another one exist
                        $new_dir = "user_data/" . $file_name . time();
                        rename($file_tmp, $new_dir);
                    }
                    mysqli_query($conn, $sql);
                } else {
                    print_r($errors);
                }
            }
            if (empty($error)) {
                echo "Success";
            }
        }





        // Close connection
        mysqli_close($conn);
        echo "<script>
            alert('You have updated the case successfully')
            // window.location.reload()
        </script>";
        // echo "<a href='browse.php?uid=$uid'></a>";
        header("Location: invoices.php");
        // header("Refresh: 0");
        ?>
    </center>
</body>

</html>