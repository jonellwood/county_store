<?php
// Created: 2025/04/09 11:24:51
// Last modified: 2025/04/10 13:41:31

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
}
