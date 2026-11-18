<?php
/**
 * Database connection. Credentials come from .env (see .env.example).
 * No connection details are ever printed to the browser.
 */

$dbHost = (string) env('DB_HOST', 'localhost');
$dbPort = (int) env('DB_PORT', 3306);
$dbName = (string) env('DB_NAME', 'neukai');
$dbUser = (string) env('DB_USER', 'root');
$dbPass = (string) env('DB_PASS', '');

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

if ($conn->connect_error) {
    error_log('NeuKai database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    exit('The application is temporarily unavailable. Please try again later.');
}

$conn->set_charset('utf8mb4');
