<?php
// Created: 2025/04/09 11:27:29
// Last Modified: 2025/04/09 13:05:32
// include_once '../rootConfig.php';
class Logger
{

    public function __construct() {}
    public static function logAPI($message): void
    {
        // $logDir = __DIR__ . '/../logs';
        $logDir = APP_ROOT . '/tmp';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/API_log.txt';
        $currentDate = date('Y-m-d H:i:s');
        $endIndicator = '###END###';
        $logMessage = "[$currentDate] $message $endIndicator" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public static function logAuth($message): void
    {
        // $logDir = __DIR__ . '/../logs';
        $logDir = APP_ROOT . '/tmp';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/auth_log.txt';
        $currentDate = date('Y-m-d H:i:s');
        $endIndicator = '###END###';
        $logMessage = "[$currentDate] $message $endIndicator" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public static function logError($message): void
    {
        // $logDir = __DIR__ . '/../logs';
        $logDir = APP_ROOT . '/tmp';
        // $logDir = '/tmp';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/error_log.txt';
        $currentDate = date('Y-m-d H:i:s');
        $endIndicator = '###END###';
        $logMessage = "[$currentDate] $message $endIndicator" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    public static function logInfo($message): void
    {
        // $logDir = __DIR__ . '/../logs';
        $logDir = APP_ROOT . '/tmp';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/info_log.txt';
        $currentDate = date('Y-m-d H:i:s');
        $endIndicator = '###END###';
        $logMessage = "[$currentDate] $message $endIndicator" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    public static function logLocal($message): void
    {
        // $logDir = __DIR__ . '/../logs';
        $logDir = APP_ROOT . '/tmp';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/local_log.txt';
        $currentDate = date('Y-m-d H:i:s');
        $endIndicator = '###END###';
        $logMessage = "[$currentDate] $message $endIndicator" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
