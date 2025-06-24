<?php
// Created: 2025/02/28 09:36:24
// Last Modified:

function logError($message)
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/error_log.txt';
    $currentDate = date('Y-m-d H:i:s');
    $logMessage = "[$currentDate] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    $message = "Error [$errno] on line $errline in file $errfile: $errstr";
    logError($message);
    return false; // Let the default error handler handle the error as well
});

set_exception_handler(function ($exception) {
    $message = "Uncaught exception: " . $exception->getMessage();
    logError($message);
    return false; // Let the default exception handler handle the exception as well
});
