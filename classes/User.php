<?php
// Created: 2025/02/28 09:12:40
// Last modified: 2025/02/28 10:30:00

class User
{
    private $db;

    public function __construct()
    {
        //include_once(dirname(__FILE__) . '/config.php');
        //$this->db = new dbConfig();
    }

    public static function getUsers()
    {
        $host = "10.50.10.94";
        $port = 3306;
        $socket = "";
        $user = "EmpOrderForm";
        $password = "FwpIXaIf1jGCpjS5Banp";
        $dbname = "uniform_orders";

        try {
            $conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
                or die('Could not connect to the database server' . mysqli_connect_error());

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "SELECT u.emp_num, u.empName, u.email, u.role_name, 
                    d1.dep_num as dep_num_1, d1.dep_name as dep_name_1, 
                    d2.dep_num as dep_num_2, d2.dep_name as dep_name_2, 
                    d3.dep_num as dep_num_3, d3.dep_name as dep_name_3
                    FROM user_ref u
                    LEFT JOIN dep_ref d1 ON u.emp_num = d1.dep_head
                    LEFT JOIN dep_ref d2 on u.emp_num = d2.dep_assist
                    LEFT JOIN dep_ref d3 on u.emp_num = d3.dep_asset_mgr";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $employeeData = [];

                while ($row = $result->fetch_assoc()) {
                    $empNum = $row['emp_num'];

                    if (!isset($employeeData[$empNum])) {
                        $employeeData[$empNum] = array(
                            'emp_num' => $empNum,
                            'empName' => $row['empName'],
                            'role_name' => $row['role_name'],
                            'departments' => array(),
                        );
                    }

                    $employeeData[$empNum]['departments'][] = array(
                        'dep_num_1' => $row['dep_num_1'],
                        'dep_name_1' => $row['dep_name_1'],
                        'dep_num_2' => $row['dep_num_2'],
                        'dep_name_2' => $row['dep_name_2'],
                        'dep_num_3' => $row['dep_num_3'],
                        'dep_name_3' => $row['dep_name_3'],
                    );
                }
            }

            return $employeeData;
        } catch (PDOException $e) {
            return json_encode("Error: " . $e->getMessage());
        }
    }
}
$users = User::getUsers();
echo "<pre>";
print_r($users);
echo "</pre>";

// echo json_encode($users);