<?php
include_once "config.php";

function html_escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
}


$empNum = $_GET['empNum'];

$sql = "SELECT order_items.color,  order_items.size, order_items.quantity, order_items.price, 
order_items.description, order_items.style, order_items.employeeNumber, order_items.createdOn, emp_ref.deptNumber,
order_items.id
FROM order_items
JOIN emp_ref on emp_ref.empNumber=order_items.employeeNumber
WHERE order_items.employeeNumber = $empNum
";

$result = mysqli_query($conn, $sql);

$data = array();

while ($row = mysqli_fetch_assoc($result))
    array_push($data, $row);

echo json_encode($data);