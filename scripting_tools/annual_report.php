<?php
include_once __DIR__ . '/../admin/config.php';

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

function fetch_report($conn, $fy)
{
    $sql = "SELECT 
        pt.productType,
        d.dep_name,
        SUM(od.line_item_total) AS total_amount
    FROM 
        order_details od
        JOIN products_new pn ON od.product_id = pn.product_id
        LEFT JOIN producttypes pt ON pt.productType_id = pn.product_type
        LEFT JOIN departments d ON d.dep_num = od.emp_dept
    WHERE 
        od.status IN ('ordered', 'received')
        AND od.bill_to_fy = '" . $conn->real_escape_string($fy) . "'
    GROUP BY 
        pn.product_type, od.emp_dept WITH ROLLUP;";
    return $conn->query($sql);
}

$result2324 = fetch_report($conn, '2324');
$result2223 = fetch_report($conn, '2223');

function render_table($result, $fy_label)
{
?>
<h1>Annual Order Report (FY <?= htmlspecialchars($fy_label) ?>, Ordered/Received)</h1>
<table>
    <thead>
        <tr>
            <th>Product Type</th>
            <th>Department</th>
            <th>Total Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <?php
                    $isGrandTotal = is_null($row['productType']) && is_null($row['dep_name']);
                    $isProductSubtotal = !is_null($row['productType']) && is_null($row['dep_name']);
                    $isDeptSubtotal = is_null($row['productType']) && !is_null($row['dep_name']);
                    $rowClass = '';
                    if ($isGrandTotal) {
                        $rowClass = 'grandtotal';
                    } elseif ($isProductSubtotal || $isDeptSubtotal) {
                        $rowClass = 'subtotal';
                    }
                    ?>
        <tr class="<?= $rowClass ?>">
            <td>
                <?php
                            if ($isGrandTotal) {
                                echo '<strong>Grand Total</strong>';
                            } elseif ($isProductSubtotal) {
                                echo '<strong>Subtotal for ' . htmlspecialchars($row['productType']) . '</strong>';
                            } elseif ($isDeptSubtotal) {
                                echo '<strong>Subtotal for ' . htmlspecialchars($row['dep_name']) . '</strong>';
                            } else {
                                echo htmlspecialchars($row['productType']);
                            }
                            ?>
            </td>
            <td>
                <?php
                            if ($isGrandTotal) {
                                echo '';
                            } elseif ($isProductSubtotal) {
                                echo '';
                            } elseif ($isDeptSubtotal) {
                                echo '<strong>' . htmlspecialchars($row['dep_name']) . '</strong>';
                            } else {
                                echo htmlspecialchars($row['dep_name']);
                            }
                            ?>
            </td>
            <td><strong>$<?= number_format($row['total_amount'], 2) ?></strong></td>
        </tr>
        <?php endwhile; ?>
        <?php else: ?>
        <tr>
            <td colspan="3">No data found.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
<?php
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annual Order Report</title>
    <style>
    body {
        font-family: Roboto, Arial, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1200px;
        margin: 40px auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 32px;
    }

    h1 {
        font-size: 2rem;
        margin-bottom: 24px;
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 24px;
    }

    th,
    td {
        padding: 12px 16px;
        text-align: left;
    }

    th {
        background: #1976d2;
        color: #fff;
        font-weight: 500;
    }

    tr:nth-child(even) {
        background: #f0f7fa;
    }

    tr:hover {
        background: #e3f2fd;
    }

    .subtotal {
        background: #ffe082 !important;
        font-weight: bold;
    }

    .grandtotal {
        background: #ffd54f !important;
        font-weight: bold;
    }
    </style>
</head>

<body>
    <div class="container">
        <?php render_table($result2324, '2324'); ?>
        <?php render_table($result2223, '2223'); ?>
    </div>
</body>

</html>
<?php $conn->close(); ?>