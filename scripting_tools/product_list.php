<?php
include_once __DIR__ . '/../admin/config.php';

$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$sql = "SELECT
    p.product_id,
    p.code,
    p.name,
    p.description,
    p.image,
    GROUP_CONCAT(DISTINCT s.size_name ORDER BY s.size_name) AS sizes,
    GROUP_CONCAT(DISTINCT c.color ORDER BY c.color) AS colors
FROM
    products_new p
LEFT JOIN products_sizes_new ps ON p.product_id = ps.product_id
LEFT JOIN sizes_new s ON ps.size_id = s.size_id
LEFT JOIN products_colors pc ON p.product_id = pc.product_id
LEFT JOIN colors c ON pc.color_id = c.color_id
WHERE
    p.keep = 1
GROUP BY
    p.product_id, p.code, p.name, p.description, p.image
ORDER BY
    p.product_id;";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
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

    .img-thumb {
        max-width: 80px;
        max-height: 80px;
        border-radius: 4px;
        border: 1px solid #eee;
    }
    </style>
</head>

<body>
    <div class="container">
        <h1>Product List (keep = 1)</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <!-- <th>Description</th> -->
                    <th>Image</th>
                    <th>Sizes</th>
                    <th>Colors</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_id']) ?></td>
                    <td><?= htmlspecialchars($row['code']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <!-- <td><?= htmlspecialchars($row['description']) ?></td> -->
                    <td>
                        <?php if (!empty($row['image'])): ?>
                        <img src="/../<?= htmlspecialchars($row['image']) ?>" alt="Image" class="img-thumb" />
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['sizes']) ?></td>
                    <td><?= htmlspecialchars($row['colors']) ?></td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7">No products found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
<?php $conn->close(); ?>