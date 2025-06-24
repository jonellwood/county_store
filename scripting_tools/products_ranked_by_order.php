<?php
include_once __DIR__ . '/../admin/config.php';

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$sql = "SELECT
    pn.code AS product_number,
    pn.name AS product_name,
    COALESCE(SUM(od.quantity), 0) AS total_ordered,
    COALESCE(SUM(CASE WHEN od.bill_to_fy = '2223' THEN od.quantity ELSE 0 END), 0) AS ordered_2223,
    COALESCE(SUM(CASE WHEN od.bill_to_fy = '2324' THEN od.quantity ELSE 0 END), 0) AS ordered_2324
FROM
    products_new pn
LEFT JOIN order_details od ON pn.product_id = od.product_id
GROUP BY
    pn.product_id, pn.code, pn.name
ORDER BY
    total_ordered DESC, pn.name ASC;";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Ranked by Orders</title>
    <style>
    body {
        font-family: Roboto, Arial, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1100px;
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
    </style>
</head>

<body>
    <div class="container">
        <h1>Products Ranked by Orders</h1>
        <table>
            <thead>
                <tr>
                    <th>Product Number</th>
                    <th>Product Name</th>
                    <th>Total Ordered</th>
                    <th>Ordered FY 2223</th>
                    <th>Ordered FY 2324</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_number']) ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['total_ordered']) ?></td>
                    <td><?= htmlspecialchars($row['ordered_2223']) ?></td>
                    <td><?= htmlspecialchars($row['ordered_2324']) ?></td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5">No data found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<?php $conn->close(); ?>