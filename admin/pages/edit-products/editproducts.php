<?php
// Created: 2025/04/09 11:34:08
// Last modified: 2025/11/06 11:54:54

echo "<div class='page-header-section'>";
echo "<h1>Product Edit</h1>";
echo "<div class='header-actions'>";
echo "<div class='search-container'>";
echo "<i class='fas fa-search search-icon'></i>";
echo "<input type='text' id='productSearch' class='product-search-input' placeholder='Search products...' />";
echo "</div>";
echo "<a href='../add-product/' class='add-new-product-btn'>";
echo "<i class='fas fa-plus'></i> Add New Product";
echo "</a>";
echo "</div>";
echo "</div>";

echo "<table class='table' id='productsTable'>";
echo "<thead>";
echo "<tr>
        <th><i class='fas fa-image'></i></th>
        <th>Product Information</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>";

echo "</thead>";
echo "<tbody>";
$icon = 'fas fa-basket-shopping';
foreach ($products as $product) {
    if ($product['product_type'] == 'Shirts') {
        $icon = 'fas fa-shirt';
    } else if ($product['product_type'] == 'Accessories') {
        $icon = 'fas fa-bag-shopping';
    } else if ($product['product_type'] == 'Boots') {
        $icon = 'fa-solid fa-shoe-prints';
    } else if ($product['product_type'] == 'Pants') {
        $icon = 'fa-solid fa-spaghetti-monster-flying';
    } else if ($product['product_type'] == 'Hats') {
        $icon = 'fas fa-hat-cowboy';
    } else if ($product['product_type'] == 'Sweatshirts') {
        $icon = 'fa-solid fa-cookie-bite';
    } else if ($product['product_type'] == 'Outwear') {
        $icon = 'fa-solid fa-fire';
    } else {
        $icon = 'fa-solid fa-location-dot';
    }
    echo "<tr class='product-row' data-product-code='" . strtolower($product['code']) . "' data-product-name='" . strtolower($product['name']) . "' data-product-desc='" . strtolower($product['description']) . "'>";
    echo "<td><i class='" . $icon . "'></i> ";
    echo "</td>";
    echo "<td>";
    echo "<div class='d-flex align-items-start flex-column'>";
    echo "<p class='fw-bold mb-1'>" . $product['code'] . "</p>";
    echo "<div class='ms-3 d-flex align-items-start'>
            <p class='mb-1'>" . $product['name'] . "</p>
          </div>";
    echo "</div>";
    echo "</td>";

    echo "<td class='text-muted mb-0'>" . $product['description'] . "</td>";
    echo "<td>";
    echo "<button type='button' class='btn btn-outline-secondary btn-sm ' data-mdb-ripple-color='dark' value =  '" . $product['product_id'] . "' class='btn btn-outline-info' onclick='editProduct(this.value)'>Edit</button>";
    echo "</td>";
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";

?>

<div class='edit-product-popover' id='editProductPopover' name='editProductPopover' popover="manual"></div>