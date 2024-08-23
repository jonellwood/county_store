<?php

include_once "../../../config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

function bindNullableParam($stmt, $param, $value)
{
    if ($value === null) {
        $stmt->bind_param($param, $value, PDO::PARAM_NULL);
    } else {
        $stmt->bind_param($param, $value);
    }
}

$data = json_decode(file_get_contents('php://input'), true);

$event_desc = $data['event_desc'] ?? null;
$init_id = $data['init_id'] ?? null;
$assoc_order_details_id = $data['assoc_order_details_id'] ?? null;
$assoc_order_id = $data['assoc_order_id'] ?? null;
$event_type_id = $data['event_type_id'] ?? null;

$sql = "INSERT INTO event_log (event_desc, initiating_id, associated_order_details_id, associated_order_id, event_type_id) VALUES (?,?,?,?,?)";
$stmt = $conn->prepare($sql);

bindNullableParam($stmt, "s", $event_desc);
bindNullableParam($stmt, "s", $init_id);
bindNullableParam($stmt, "s", $assoc_order_details_id);
bindNullableParam($stmt, "s", $assoc_order_id);
bindNullableParam($stmt, "i", $event_type_id);

$stmt->execute();
$stmt->close();
$conn->close();