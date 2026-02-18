<?php
// Created: 2025/04/09 11:24:51
// Last modified: 2026/02/18 10:29:19

include_once 'Logger.php';

class databaseConfig
{
    public $serverName = "10.50.10.94";
    public $port = 3306;
    public $socket = "";
    public $uid = "EmpOrderForm";
    public $pwd = "FwpIXaIf1jGCpjS5Banp";
    public $database = "uniform_orders";
}

class Product
{

    private $db;

    public function __construct()
    {
        // include_once(dirname(__DIR__) . '/data/dbconfig.php');
        $this->db = new databaseConfig();
    }

    public function getProducts()
    {
        Logger::logError("Fetching products from database.");
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;
        // Logger::logError("Database credentials: Server - {$this->db->serverName}, Database - {$this->db->database}");

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT products_new.product_id, products_new.code, products_new.description, products_new.name, 
                producttypes.productType as product_type 
                FROM products_new 
                JOIN producttypes on producttypes.productType_id = products_new.product_type
                order by product_type, code, name";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $resultArray = $result->fetch_all(MYSQLI_ASSOC);
            return $resultArray;
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function getProductById($productID)
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;
        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT * FROM products_new WHERE product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $productID);
            $stmt->execute();
            $result = $stmt->get_result();
            $resultArray = $result->fetch_assoc();
            return $resultArray;
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function getProductByCode($productCode)
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;
        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT * FROM products_new WHERE code = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $productCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $resultArray = $result->fetch_assoc();
            return $resultArray;
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function getProductColorsByProductID($productID)
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT pc.id, pc.product_id, pc.color_id, c.color, p_hex
                    FROM products_colors pc
                    JOIN colors c on c.color_id = pc.color_id
                    WHERE pc.product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $productID);
            $stmt->execute();
            $result = $stmt->get_result();
            $resultArray = $result->fetch_all(MYSQLI_ASSOC);
            return $resultArray;
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function getProductSizesByProductID($productID)
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT psn.id, psn.product_id, psn.size_id, sn.size_name
                    FROM products_sizes_new psn
                    JOIN sizes_new sn on sn.size_id = psn.size_id 
                    WHERE psn.product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $productID);
            $stmt->execute();
            $result = $stmt->get_result();
            $resultArray = $result->fetch_all(MYSQLI_ASSOC);
            return $resultArray;
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function getProductPricesByProductID($productID)
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT prices.price_id, prices.product_id, prices.vendor_id, 
                    prices.size_id, prices.price, prices.isActive, sizes_new.size_name
                    FROM prices
                    JOIN sizes_new on sizes_new.size_id = prices.size_id 
                    WHERE prices.product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $productID);
            $stmt->execute();
            $result = $stmt->get_result();
            $resultArray = $result->fetch_all(MYSQLI_ASSOC);
            return $resultArray;
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function getActiveProductTypes()
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT productType_id, productType 
                    FROM producttypes 
                    WHERE isactive = 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $resultArray = $result->fetch_all(MYSQLI_ASSOC);
            return $resultArray;
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function getColors()
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT color_id, color, p_hex, s_hex, t_hex FROM colors";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $resultArray = $result->fetch_all(MYSQLI_ASSOC);
            return $resultArray;
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }
    public function getColorUsage()
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT 
                color_id,
                COUNT(DISTINCT product_id) AS product_count
                    FROM 
                products_colors
                    GROUP BY 
                color_id
                    ORDER BY 
                product_count DESC;";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $resultArray = $result->fetch_all(MYSQLI_ASSOC);
            return $resultArray;
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }


    public function addColor($colorData)
    {
        $colorName = isset($colorData['color']) ? trim($colorData['color']) : '';

        if ($colorName === '') {
            return [
                'success' => false,
                'message' => 'Color name is required'
            ];
        }

        try {
            $primaryHex = $this->normalizeHexValue($colorData['p_hex'] ?? '', false, 'Primary hex');
            $secondaryHex = $this->normalizeHexValue($colorData['s_hex'] ?? '', true, 'Secondary hex');
            $tertiaryHex = $this->normalizeHexValue($colorData['t_hex'] ?? '', true, 'Tertiary hex');
        } catch (\InvalidArgumentException $hexException) {
            return [
                'success' => false,
                'message' => $hexException->getMessage()
            ];
        }

        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);

            $duplicateSql = "SELECT color_id FROM colors WHERE LOWER(color) = LOWER(?) LIMIT 1";
            $duplicateStmt = $conn->prepare($duplicateSql);
            $duplicateStmt->bind_param("s", $colorName);
            $duplicateStmt->execute();
            $duplicateResult = $duplicateStmt->get_result();

            if ($duplicateResult && $duplicateResult->num_rows > 0) {
                return [
                    'success' => false,
                    'message' => 'Color name already exists'
                ];
            }

            $insertSql = "INSERT INTO colors (color, p_hex, s_hex, t_hex) VALUES (?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ssss", $colorName, $primaryHex, $secondaryHex, $tertiaryHex);
            $insertStmt->execute();

            $colorId = $conn->insert_id;
            Logger::logError("Added color '{$colorName}' with ID: " . $colorId);

            return [
                'success' => true,
                'colorId' => $colorId,
                'message' => 'Color added successfully'
            ];
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Error adding color: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function getSizes()
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT size_id, size_name FROM sizes_new";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $resultArray = $result->fetch_all(MYSQLI_ASSOC);
            return $resultArray;
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function getGenderFilters()
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT id, filter FROM filters_gender ORDER BY filter";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function getSizeFilters()
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT id, filter FROM filters_size ORDER BY filter";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function getSleeveFilters()
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT id, filter FROM filters_sleeve ORDER BY filter";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function getTypeFilters()
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $sql = "SELECT id, filter FROM filters_type ORDER BY filter";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e) {
            Logger::logError("Connection failed: " . $e->getMessage());
        }
    }

    public function addProduct($productData, $colors, $sizes, $filters = [])
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $conn->autocommit(FALSE); // Start transaction

            // Insert product
            $sql = "INSERT INTO products_new (code, name, image, description, keep, product_type) VALUES (?, ?, ?, ?, 1, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssi",
                $productData['code'],
                $productData['name'],
                $productData['image'],
                $productData['description'],
                $productData['productType']
            );
            $stmt->execute();

            $productId = $conn->insert_id;
            Logger::logError("Added product with ID: " . $productId);

            // Insert product colors
            if (!empty($colors)) {
                $colorSql = "INSERT INTO products_colors (product_id, color_id) VALUES (?, ?)";
                $colorStmt = $conn->prepare($colorSql);

                foreach ($colors as $colorId) {
                    $colorStmt->bind_param("ii", $productId, $colorId);
                    $colorStmt->execute();
                }
                Logger::logError("Added " . count($colors) . " colors for product ID: " . $productId);
            }

            // Insert product sizes
            if (!empty($sizes)) {
                $sizeSql = "INSERT INTO products_sizes_new (product_id, size_id) VALUES (?, ?)";
                $sizeStmt = $conn->prepare($sizeSql);

                foreach ($sizes as $size) {
                    $sizeStmt->bind_param("ii", $productId, $size['sizeId']);
                    $sizeStmt->execute();
                }
                Logger::logError("Added " . count($sizes) . " sizes for product ID: " . $productId);
            }

            // Insert prices
            if (!empty($sizes)) {
                $priceSql = "INSERT INTO prices (product_id, vendor_id, size_id, price, isActive) VALUES (?, ?, ?, ?, 1)";
                $priceStmt = $conn->prepare($priceSql);

                foreach ($sizes as $size) {
                    $priceStmt->bind_param("iiid", $productId, $productData['vendorId'], $size['sizeId'], $size['price']);
                    $priceStmt->execute();
                }
                Logger::logError("Added " . count($sizes) . " prices for product ID: " . $productId);
            }

            $genderFilter = isset($filters['gender']) && $filters['gender'] !== '' ? (int)$filters['gender'] : null;
            $typeFilter = isset($filters['type']) && $filters['type'] !== '' ? (int)$filters['type'] : null;
            $sizeFilter = isset($filters['size']) && $filters['size'] !== '' ? (int)$filters['size'] : null;
            $sleeveFilter = isset($filters['sleeve']) && $filters['sleeve'] !== '' ? (int)$filters['sleeve'] : null;

            if ($genderFilter !== null || $typeFilter !== null || $sizeFilter !== null || $sleeveFilter !== null) {
                $filterSql = "INSERT INTO products_filters (product, gender_filter, type_filter, size_filter, sleeve_filter) VALUES (?, ?, ?, ?, ?)";
                $filterStmt = $conn->prepare($filterSql);
                $filterStmt->bind_param("iiiii", $productId, $genderFilter, $typeFilter, $sizeFilter, $sleeveFilter);
                $filterStmt->execute();
                Logger::logError("Added filters for product ID: " . $productId);
            }

            $conn->commit(); // Commit transaction
            $conn->autocommit(TRUE);

            return [
                'success' => true,
                'productId' => $productId,
                'message' => 'Product added successfully'
            ];
        } catch (mysqli_sql_exception $e) {
            if (isset($conn)) {
                $conn->rollback(); // Rollback transaction on error
                $conn->autocommit(TRUE);
            }
            Logger::logError("Error adding product: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function updateColor($colorData)
    {
        if (empty($colorData['id'])) {
            return [
                'success' => false,
                'message' => 'Color ID is required'
            ];
        }

        $colorName = isset($colorData['color']) ? trim($colorData['color']) : '';
        if ($colorName === '') {
            return [
                'success' => false,
                'message' => 'Color name is required'
            ];
        }

        try {
            $primaryHex = $this->normalizeHexValue($colorData['p_hex'] ?? '', false, 'Primary hex');
            $secondaryHex = $this->normalizeHexValue($colorData['s_hex'] ?? '', true, 'Secondary hex');
            $tertiaryHex = $this->normalizeHexValue($colorData['t_hex'] ?? '', true, 'Tertiary hex');
        } catch (\InvalidArgumentException $hexError) {
            return [
                'success' => false,
                'message' => $hexError->getMessage()
            ];
        }

        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);

            $duplicateSql = "SELECT color_id FROM colors WHERE LOWER(color) = LOWER(?) AND color_id != ? LIMIT 1";
            $duplicateStmt = $conn->prepare($duplicateSql);
            $duplicateStmt->bind_param("si", $colorName, $colorData['id']);
            $duplicateStmt->execute();
            $duplicateResult = $duplicateStmt->get_result();

            if ($duplicateResult && $duplicateResult->num_rows > 0) {
                return [
                    'success' => false,
                    'message' => 'Another color already uses this name'
                ];
            }

            $updateSql = "UPDATE colors SET color = ?, p_hex = ?, s_hex = ?, t_hex = ? WHERE color_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssssi", $colorName, $primaryHex, $secondaryHex, $tertiaryHex, $colorData['id']);
            $updateStmt->execute();

            return [
                'success' => true,
                'message' => 'Color updated'
            ];
        } catch (mysqli_sql_exception $e) {
            Logger::logError('Error updating color: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function deleteColors(array $colorIds)
    {
        if (empty($colorIds)) {
            return [
                'success' => false,
                'message' => 'No colors supplied'
            ];
        }

        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);

            $placeholders = implode(',', array_fill(0, count($colorIds), '?'));
            $types = str_repeat('i', count($colorIds));

            $conn->begin_transaction();

            $deleteColorsSql = "DELETE FROM colors WHERE color_id IN ($placeholders)";
            $deleteColorsStmt = $conn->prepare($deleteColorsSql);
            $deleteColorsStmt->bind_param($types, ...$colorIds);
            $deleteColorsStmt->execute();

            $conn->commit();

            return [
                'success' => true,
                'deleted' => count($colorIds)
            ];
        } catch (mysqli_sql_exception $e) {
            if (isset($conn)) {
                $conn->rollback();
            }
            Logger::logError('Error deleting colors: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function updateProduct($productId, $colors, $sizes)
    {
        $serverName = $this->db->serverName;
        $database = $this->db->database;
        $uid = $this->db->uid;
        $pwd = $this->db->pwd;

        try {
            $conn = new mysqli($serverName, $uid, $pwd, $database);
            $conn->autocommit(FALSE); // Start transaction

            Logger::logError("Starting product update for ID: " . $productId);

            // Update colors - remove existing and add new ones
            if (!empty($colors)) {
                // Remove existing color associations
                $deleteColorSql = "DELETE FROM products_colors WHERE product_id = ?";
                $deleteColorStmt = $conn->prepare($deleteColorSql);
                $deleteColorStmt->bind_param("i", $productId);
                $deleteColorStmt->execute();

                // Add new color associations
                $colorSql = "INSERT INTO products_colors (product_id, color_id) VALUES (?, ?)";
                $colorStmt = $conn->prepare($colorSql);

                foreach ($colors as $color) {
                    $colorStmt->bind_param("ii", $productId, $color['color_id']);
                    $colorStmt->execute();
                }
                Logger::logError("Updated " . count($colors) . " colors for product ID: " . $productId);
            }

            // Update sizes and prices
            if (!empty($sizes)) {
                // First, collect all existing price_ids for this product
                $existingPricesQuery = "SELECT price_id FROM prices WHERE product_id = ? AND isActive = 1";
                $existingPricesStmt = $conn->prepare($existingPricesQuery);
                $existingPricesStmt->bind_param("i", $productId);
                $existingPricesStmt->execute();
                $existingResult = $existingPricesStmt->get_result();
                $existingPriceIds = [];
                while ($row = $existingResult->fetch_assoc()) {
                    $existingPriceIds[] = $row['price_id'];
                }

                // Collect price_ids that are being kept/updated
                $keptPriceIds = [];

                foreach ($sizes as $size) {
                    if (isset($size['price_id'])) {
                        // Update existing price
                        $keptPriceIds[] = $size['price_id'];
                        $updatePriceSql = "UPDATE prices SET price = ? WHERE price_id = ? AND product_id = ?";
                        $updatePriceStmt = $conn->prepare($updatePriceSql);
                        $updatePriceStmt->bind_param("dii", $size['price'], $size['price_id'], $productId);
                        $updatePriceStmt->execute();
                        Logger::logError("Updated price_id: " . $size['price_id'] . " to price: " . $size['price']);
                    } else if (isset($size['size_id'])) {
                        // Add new size/price combination
                        // First add to products_sizes_new if not exists
                        $sizeSql = "INSERT IGNORE INTO products_sizes_new (product_id, size_id) VALUES (?, ?)";
                        $sizeStmt = $conn->prepare($sizeSql);
                        $sizeStmt->bind_param("ii", $productId, $size['size_id']);
                        $sizeStmt->execute();

                        // Then add price record
                        $priceSql = "INSERT INTO prices (product_id, size_id, price, vendor_id, isActive) VALUES (?, ?, ?, 1, 1)";
                        $priceStmt = $conn->prepare($priceSql);
                        $priceStmt->bind_param("iid", $productId, $size['size_id'], $size['price']);
                        $priceStmt->execute();
                        Logger::logError("Added new size_id: " . $size['size_id'] . " with price: " . $size['price']);
                    }
                }

                // Delete prices that were removed (exist in DB but not in kept list)
                $pricesToDelete = array_diff($existingPriceIds, $keptPriceIds);
                if (!empty($pricesToDelete)) {
                    // First get the size_ids for the prices we're about to delete
                    $getSizeIdsQuery = "SELECT DISTINCT size_id FROM prices WHERE price_id IN (" . str_repeat('?,', count($pricesToDelete) - 1) . "?) AND product_id = ?";
                    $getSizeIdsStmt = $conn->prepare($getSizeIdsQuery);
                    $params = array_merge($pricesToDelete, [$productId]);
                    $types = str_repeat('i', count($params));
                    $getSizeIdsStmt->bind_param($types, ...$params);
                    $getSizeIdsStmt->execute();
                    $sizeIdsResult = $getSizeIdsStmt->get_result();
                    $sizeIdsToRemove = [];
                    while ($row = $sizeIdsResult->fetch_assoc()) {
                        $sizeIdsToRemove[] = $row['size_id'];
                    }

                    // Deactivate the prices (soft delete)
                    $deletePriceSql = "UPDATE prices SET isActive = 0 WHERE price_id = ? AND product_id = ?";
                    $deletePriceStmt = $conn->prepare($deletePriceSql);

                    foreach ($pricesToDelete as $priceIdToDelete) {
                        $deletePriceStmt->bind_param("ii", $priceIdToDelete, $productId);
                        $deletePriceStmt->execute();
                        Logger::logError("Deactivated (soft delete) price_id: " . $priceIdToDelete);
                    }

                    // Remove size associations from products_sizes_new for sizes that no longer have active prices
                    if (!empty($sizeIdsToRemove)) {
                        foreach ($sizeIdsToRemove as $sizeIdToRemove) {
                            // Check if this size still has any active prices for this product
                            $checkActivePricesQuery = "SELECT COUNT(*) as count FROM prices WHERE product_id = ? AND size_id = ? AND isActive = 1";
                            $checkActivePricesStmt = $conn->prepare($checkActivePricesQuery);
                            $checkActivePricesStmt->bind_param("ii", $productId, $sizeIdToRemove);
                            $checkActivePricesStmt->execute();
                            $activePricesResult = $checkActivePricesStmt->get_result();
                            $activePricesCount = $activePricesResult->fetch_assoc()['count'];

                            // If no active prices remain for this size, remove it from products_sizes_new
                            if ($activePricesCount == 0) {
                                $deleteSizeSql = "DELETE FROM products_sizes_new WHERE product_id = ? AND size_id = ?";
                                $deleteSizeStmt = $conn->prepare($deleteSizeSql);
                                $deleteSizeStmt->bind_param("ii", $productId, $sizeIdToRemove);
                                $deleteSizeStmt->execute();
                                Logger::logError("Removed size_id: " . $sizeIdToRemove . " from products_sizes_new for product_id: " . $productId);
                            }
                        }
                    }
                }
                Logger::logError("Updated " . count($sizes) . " sizes/prices for product ID: " . $productId);
            }

            $conn->commit(); // Commit transaction
            $conn->autocommit(TRUE);

            Logger::logError("Product update completed successfully for ID: " . $productId);

            return [
                'success' => true,
                'productId' => $productId,
                'message' => 'Product updated successfully'
            ];
        } catch (mysqli_sql_exception $e) {
            if (isset($conn)) {
                $conn->rollback(); // Rollback transaction on error
                $conn->autocommit(TRUE);
            }
            Logger::logError("Error updating product: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    private function normalizeHexValue($value, $allowEmpty = true, $label = 'Hex value')
    {
        if ($value === null) {
            if ($allowEmpty) {
                return null;
            }
            throw new \InvalidArgumentException($label . ' is required');
        }

        $value = trim($value);

        if ($value === '') {
            if ($allowEmpty) {
                return null;
            }
            throw new \InvalidArgumentException($label . ' is required');
        }

        if ($value[0] !== '#') {
            $value = '#' . $value;
        }

        $value = strtoupper($value);

        if (!preg_match('/^#([0-9A-F]{3}|[0-9A-F]{6})$/', $value)) {
            throw new \InvalidArgumentException($label . ' must be a valid hex code (e.g., #AABBCC)');
        }

        if (strlen($value) === 4) {
            $value = '#' . $value[1] . $value[1] . $value[2] . $value[2] . $value[3] . $value[3];
        }

        return $value;
    }
}
