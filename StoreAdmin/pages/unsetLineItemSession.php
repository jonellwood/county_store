<?php
session_start();

unset($_SESSION['order_details_id']);

echo json_encode(["success" => true, "message" => "Session variable unset"]);
