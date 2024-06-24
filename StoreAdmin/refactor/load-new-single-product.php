<?php
session_start();
header('Content-type: application/json');

require_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket);

$q = $_GET['id'];
$id = intval($q);

// echo "q: " . htmlspecialchars($q) . "<br>";
// echo "id: " . $id . "<br>";
// echo "gettype(id): " . gettype($id) . "<br>";

$data = [];
$product = [];
$current_vendors = [];
$current_prices_and_sizes = [];
$current_colors = [];
$current_sizes = [];
$current_filters = [];
$current_prices = [];
$all_vendors = [];
$all_colors = [];
$all_sizes = [];
$all_price_mods = [];
$all_gender_filters = [];
$all_type_filters = [];
$all_size_filters = [];
$all_sleeve_filters = [];
$all_filters = [];
$all_products = [];

$sql = "SELECT p.product_id, p.code, p.name, p.name, p.description, ppt.producttype_id,  pt.producttype as producttype from products_new p
JOIN prices pr on pr.product_id = p.product_id
JOIN vendors v on pr.vendor_id = v.id 
JOIN products_producttype ppt on ppt.product_id = p.product_id
JOIN producttypes pt on pt.productType_id = ppt.producttype_id
WHERE p.product_id = $id
group by p.product_id";


$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}


$stmt->execute();
if ($stmt->execute() === false) {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
};
$productDetails = $stmt->get_result();
if ($productDetails === false) {
    die('Get result failed: ' . htmlspecialchars($stmt->error));
}
if ($productDetails->num_rows > 0) {
    while ($row = $productDetails->fetch_assoc()) {
        array_push($product, $row);
    }
    array_push($data, [
        "product" => $product
    ]);
}

// select vendors id and name for the product and push to array
$stmt = $conn->prepare("SELECT p.vendor_id, v.name as vendor_name, v.vendor_number_finance as vfn FROM uniform_orders.prices p join vendors v on v.id = p.vendor_id where p.product_id = $id group by p.vendor_id");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        array_push($current_vendors, [
            'vendor_id' => $row['vendor_id'],
            'vendor_name' => $row['vendor_name'],
            'vendor_fin_num' => $row['vfn']
        ]);
    }
    array_push($data, [
        'current_vendor' => $current_vendors
    ]);
} else {
    array_push($data, [
        'current_vendor' => null
    ]);
}
// set size id and price for all vendors

// foreach ($vendors as $vendor) {

$stmt = $conn->prepare("SELECT p.price_id, p.size_id, p.price, p.vendor_id, s.size_name, v.name as vendor_name 
from prices p 
join sizes_new s on s.size_id = p.size_id
JOIN vendors v on v.id = p.vendor_id
where p.product_id = " . $id . ";");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        array_push($current_prices_and_sizes, [
            'price_id' => $row['price_id'],
            'price' => $row['price'],
            'size_id' => $row['size_id'],
            'size_name' => $row['size_name'],
            'vendor_id' => $row['vendor_id'],
            'vendor_name' => $row['vendor_name']
        ]);
    }
    array_push($data, [
        'current_prices_and_sizes' => $current_prices_and_sizes
    ]);
} else {
    array_push($data, [
        'current_prices_and_sizes' => null
    ]);
}


// Select color_ids and names
// $stmt = $conn->prepare("SELECT c.color, c.color_id 
// from products_colors pc
// LEFT JOIN colors c on c.color_id = pc.color_id WHERE product_id = $id ORDER BY color ASC");
// $stmt->execute();
// $colorIds = $stmt->get_result();
// if ($colorIds->num_rows > 0) {
//     while ($row = $colorIds->fetch_assoc()) {
//         array_push($current_colors, $row);
//     }
//     array_push($data, [
//         "current_colors" => $current_colors
//     ]);
// } else {
//     array_push($data, [
//         "current_colors" => null
//     ]);
// }


// Select size_ids and names
// $stmt = $conn->prepare("SELECT s.size, s.size_id 
// from products_sizes ps
// LEFT JOIN sizes s on s.size_id = ps.size_id WHERE product_id = $id");
// $stmt->execute();
// $sizeIds = $stmt->get_result();
// if ($sizeIds->num_rows > 0) {
//     while ($row = $sizeIds->fetch_assoc()) {
//         array_push($current_sizes, $row);
//     }
//     array_push($data, [
//         "current_sizes" => $current_sizes
//     ]);
// } else {
//     array_push($data, [
//         "current_sizes" => null
//     ]);
// }

// Select current product filters
// $stmt = $conn->prepare("SELECT gender_filter, type_filter, size_filter, sleeve_filter from products_filters WHERE product = $id");
// $stmt->execute();
// $filterIds = $stmt->get_result();
// if ($sizeIds->num_rows > 0) {
//     while ($row = $filterIds->fetch_assoc()) {
//         array_push($current_filters, $row);
//     }
//     array_push($data, [
//         "current_filters" => $current_filters
//     ]);
// } else {
//     array_push($data, [
//         "current_filters" => null
//     ]);
// }

//get all vendors and id's
$stmt = $conn->prepare("SELECT p.vendor_id, v.name as vendor_name, v.vendor_number_finance as vfn 
FROM uniform_orders.prices p join vendors v on v.id = p.vendor_id 
group by p.vendor_id");
$stmt->execute();
$allVendors = $stmt->get_result();
if ($allVendors->num_rows > 0) {
    while ($row = $allVendors->fetch_assoc()) {
        array_push($all_vendors, $row);
    }
    array_push($data, [
        "all_vendors" => $all_vendors
    ]);
}

// Get all colors and id's
$stmt = $conn->prepare("SELECT color, color_id, p_hex, s_hex, t_hex FROM colors ORDER BY color ASC");
$stmt->execute();
$allColors = $stmt->get_result();
if ($allColors->num_rows > 0) {
    while ($row = $allColors->fetch_assoc()) {
        array_push($all_colors, $row);
    }
    array_push($data, [
        "all_colors" => $all_colors
    ]);
}

// Get all sizes and id's
$stmt = $conn->prepare("SELECT size_name, size_id FROM sizes_new");
$stmt->execute();
$allSizes = $stmt->get_result();
if ($allSizes->num_rows > 0) {
    while ($row = $allSizes->fetch_assoc()) {
        array_push($all_sizes, $row);
    }
    array_push($data, [
        "all_sizes" => $all_sizes
    ]);
}

// get all gender filters
// $stmt = $conn->prepare("SELECT id, filter from filters_gender");
// $stmt->execute();
// $allGenderFilters = $stmt->get_result();
// if ($allGenderFilters->num_rows > 0) {
//     while ($row = $allGenderFilters->fetch_assoc()) {
//         array_push($all_gender_filters, $row);
//     }
//     array_push($all_filters, [
//         "gender_filters" => $all_gender_filters
//     ]);
// }

// Get all type filters 
$stmt = $conn->prepare("SELECT id, filter from filters_type");
$stmt->execute();
$allTypeFilters = $stmt->get_result();
if ($allTypeFilters->num_rows > 0) {
    while ($row = $allTypeFilters->fetch_assoc()) {
        array_push($all_type_filters, $row);
    }
    array_push($all_filters, [
        "type_filters" => $all_type_filters
    ]);
    array_push($data, [
        "all_filters" => $all_filters
    ]);
}

// Get all size filters 
// $stmt = $conn->prepare("SELECT id, filter from filters_size");
// $stmt->execute();
// $allSizeFilters = $stmt->get_result();
// if ($allSizeFilters->num_rows > 0) {
//     while ($row = $allSizeFilters->fetch_assoc()) {
//         array_push($all_size_filters, $row);
//     }
//     array_push($all_filters, [
//         "size_filters" => $all_size_filters
//     ]);
// }

// Get all sleeve filters 
// $stmt = $conn->prepare("SELECT id, filter from filters_sleeve");
// $stmt->execute();
// $allSleeveFilters = $stmt->get_result();
// if ($allSleeveFilters->num_rows > 0) {
//     while ($row = $allSleeveFilters->fetch_assoc()) {
//         array_push($all_sleeve_filters, $row);
//     }
//     array_push($all_filters, [
//         "sleeve_filters" => $all_sleeve_filters
//     ]);
// }
// array_push($data, [
//     "all_filters" => $all_filters
// ]);

// $stmt = $conn->prepare("SELECT id, price_mod FROM price_mods");
// $stmt->execute();
// $allMods = $stmt->get_result();
// if ($allMods->num_rows > 0) {
//     while ($row = $allMods->fetch_assoc()) {
//         array_push($all_price_mods, $row);
//     }
//     array_push($data, [
//         "all_mods" => $all_price_mods
//     ]);
// }

// $stmt = $conn->prepare("SELECT product_id, code, name FROM products WHERE isactive = 1");
// $stmt->execute();
// $allProducts = $stmt->get_result();
// if ($allProducts->num_rows > 0) {
//     while ($row = $allProducts->fetch_assoc()) {
//         array_push($all_products, $row);
//     }
//     array_push($data, [
//         "all_products" => $all_products
//     ]);
// }


echo json_encode($data);
