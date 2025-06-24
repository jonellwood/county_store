<?php
/*
 * Shared configuration file (ssoConfig.php)
 * This file should be identical on both App-A and App-B
 */
include_once '../../rootConfig.php';
include_once APP_ROOT . '/classes/Logger.php';

// Shared super long string as secret key for signing tokens
define('SSO_SECRET_KEY', 'HmsNHJcjdNNBCI8DbTqmHT5kGiKcVSPNbOrT4PAAu6AUCRzWSYJBRHtvLOaM1o7WXi43Heef62jWexwyoo1XCKhKii28VMyk');

// Token expiration time in seconds (5 minutes)
define('TOKEN_EXPIRY', 300);

// Allowed application origins (for security)
define('ALLOWED_ORIGINS', [
    'app-a' => 'https://my.berkeleycountysc.gov',
    'cc3560dd-e429-f011-8108-000c29ab5143' => 'https://store.berkeleycountysc.gov/admin/signin',
    'cd3560dd-e429-f011-8108-000c29ab5143' => 'https://store.berkeleycountysc.gov/inventory',
]);

/**
 * SSO Helper Class
 */
class SSOHelper
{
    /**
     * Generate a secure SSO token
     * 
     * @param string $username The loggedin username
     * @param array $userData Additional user data to pass (optional)
     * @param string $targetApp The target application identifier
     * @return string The encoded token
     */
    public static function generateToken($username, $userData = [], $targetApp = '')
    {
        // Create token payload
        $payload = [
            'username' => $username,
            'userData' => $userData,
            'exp' => time() + TOKEN_EXPIRY,
            'iat' => time(),
            'source' => $_SERVER['HTTP_HOST'],
            'target' => $targetApp
        ];

        // Encode and sign the token
        $base64Payload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $base64Payload, SSO_SECRET_KEY);

        // Combine payload and signature
        return $base64Payload . '.' . $signature;
    }

    /**
     * Verify a token
     * 
     * @param string $token The token to verify
     * @return array|false Returns the payload if valid, false otherwise
     */
    public static function verifyToken($token)
    {
        // Split token into payload and signature
        $parts = explode('.', $token);
        if (count($parts) != 2) {
            return false;
        }

        list($base64Payload, $signature) = $parts;
        // Logger::logError("Parts in verify: " . json_encode($parts));
        // Verify signature
        $expectedSignature = hash_hmac('sha256', $base64Payload, SSO_SECRET_KEY);
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }

        // Decode payload
        $payload = json_decode(base64_decode($base64Payload), true);
        if (!$payload) {
            return false;
        }

        // Check if token has expired
        if (time() > $payload['exp']) {
            return false;
        }

        return $payload;
    }
}

/*
 * App-A: Link Generation (implemented in App-A)
 */

/**
 * Generate a link to App-B with SSO token
 * 
 * @param string $username The loggedin username
 * @param array $userData Additional user data to pass
 * @return string The URL to App-B
 */
// function generateSSOLink($username, $userData = [], $sAppId)
// {
//     // Make sure user is loggedin in App-A
//     if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//         return false;
//     }

//     // Generate token for App-B
//     $token = SSOHelper::generateToken($username, $userData, $sAppId);

//     // Return URL with token
//     return ALLOWED_ORIGINS['app-b'] . '/sso.php?token=' . urlencode($token);
// }
function generateSSOLink($username, $appData, $userData = [])
{
    Logger::logAPI("generateSSOLink function called");
    Logger::logAPI("App Data from genSSOLink func call: " . json_encode($appData));

    // Make sure user is loggedin
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        return false;
    }

    // Use app ID from the database record
    $appId = $appData['id'];

    Logger::logAPI("App ID from genSSOLink func call: " . $appId);

    // IMPORTANT CHANGE: Don't include app_id in userData - it needs to be in the target parameter
    // Instead of merging app_id into userData, we'll pass it directly as the target parameter

    // Generate token for the specific app
    // This passes app ID as the target parameter - this is crucial!
    $token = SSOHelper::generateToken($username, $userData, $appId);

    // Log token payload for debugging
    $tokenParts = explode('.', $token);
    if (count($tokenParts) >= 1) {
        $payloadJson = base64_decode($tokenParts[0]);
        Logger::logAPI("Token payload: " . $payloadJson);
    }

    // Get URL from database and construct the SSO endpoint URL
    $baseUrl = $appData['url'];

    // Parse the URL to find the proper location for the SSO endpoint
    $urlParts = parse_url($baseUrl);
    $path = $urlParts['path'] ?? '';

    // Add sso.php to the base path
    $ssoPath = rtrim(dirname($path), '/') . '/sso.php';
    if ($ssoPath === '/sso.php' && $path !== '/') {
        // If there's a specific page (not just domain), put sso.php at same level
        $ssoPath = dirname($path) . '/sso.php';
    }

    // Reconstruct URL with sso.php endpoint
    $ssoUrl = $urlParts['scheme'] . '://' . $urlParts['host'];
    if (isset($urlParts['port'])) {
        $ssoUrl .= ':' . $urlParts['port'];
    }
    $ssoUrl .= $ssoPath . '?token=' . urlencode($token);

    return $ssoUrl;
}
/*
 * App-B: SSO Handler (implemented in App-B as sso.php)
 */

/**
 * Sample SSO handler for App-B
 */
function handleSSO()
{
    // Check if token exists
    if (!isset($_GET['token'])) {
        header('Location: signin.php?error=no_token');
        exit;
    }
    Logger::logError("SSO token: " . $_GET['token']);
    // Verify token
    $payload = SSOHelper::verifyToken($_GET['token']);
    if (!$payload) {
        header('Location: signin.php?error=invalid_token');
        exit;
    }
    Logger::logError("SSO payload target: " . $payload['target']);
    // Check if token is intended for this app
    if ($payload['target'] !== 'CC3560DD-E429-F011-8108-000C29AB5143') {
        header('Location: signin.php?error=wrong_target');
        exit;
    }

    // Extract username and user data
    $username = $payload['username'];
    // $userData = $payload['userData'];

    // Check if user exists in App-B's database
    if (!userExistsInAppB($username)) {
        header('Location: signin.php?error=user_not_found');
        exit;
    }
    /** 
     * Create session in App-B
     * These will have to be configured per applications based on the variables
     * and requirements of each application
     */
    session_start();
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $username;

    // You can also copy over other needed session variables
    // from the userData array if needed

    // Redirect to App-B's dashboard or home page
    header('Location: ../pages/employeeRequests.php');
    exit;
}

/**
 * Check if user exists in App-B's database
 * There should be exiting logic in place to check if the user exists
 * in App-B's database. The idea is to to not alter the existing auth process 
 * since that would prevent (or at least complicate) access not coming from 
 * myBerkeley - but provide this as an alternate process altogether which 
 * myBerkeley points directly too.
 */
// function userExistsInAppB($username)
// {
//  
//     $db = new PDO('mysql:host=localhost;dbname=appb_db', 'username', 'password');
//     $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
//     $stmt->execute([$username]);
//     return $stmt->rowCount() > 0;
// }

function userExistsInAppB($username)
{
    include_once "dbConfig.php";
    $db = new dbConfig;
    $serverName = $db->serverName;
    $database = $db->database;
    $uid = $db->uid;
    $pwd = $db->pwd;

    $conn = mysqli_connect($serverName, $uid, $pwd, $database);

    $sql = "SELECT u.emp_num, u.role_id, u.user_name, er.deptNumber, er.email, er.empName, 
        r.role_name
        FROM users u
        JOIN emp_ref er on er.empNumber = u.emp_num
        JOIN roles r on r.role_id = u.role_id
        WHERE u.user_name = '$username'
        ";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $_SESSION["loggedin"] = true;
            $_SESSION["loggedinuser"] = $row['user_name'];
            $_SESSION["department"] = $row['deptNumber'];
            $_SESSION["userName"] = $row['empName'];
            $_SESSION["empNumber"] = $row["emp_num"];
            $_SESSION['role_id'] = $row["role_id"];
            $_SESSION["email"] = $row["email"];
            $_SESSION["role_name"] = $row["role_name"];
            $conn->close();
            return true;
        }
    } else {
        header("location: signin.php");
    }
}
