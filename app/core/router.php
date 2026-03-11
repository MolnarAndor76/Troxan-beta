<?php

// ======================================================
// BASIC REQUEST PARSING
// ======================================================

// Read the "path" parameter from the URL
// Example request: index.php?path=api/recipes/5
// If it does not exist, default to empty string
$path = $_GET['path'] ?? '';

// Remove leading and trailing slashes from the path
// "/api/recipes/" becomes "api/recipes"
$path = trim($path, '/');

// Convert path into array segments separated by "/"
// Example: "api/recipes/5" → ['api','recipes','5']
// If path empty → return empty array
$segments = ($path === '') ? [] : explode('/', $path);

// Detect HTTP request method (GET, POST, PUT, DELETE...)
$method = $_SERVER['REQUEST_METHOD'];

// Map segments into readable route variables
$route = [
    'segment1' => $segments[0] ?? null, // first part of URL
    'segment2' => $segments[1] ?? null, // second part
    'segment3' => $segments[2] ?? null, // third part (often ID)
];

// ======================================================
// HELPER FUNCTIONS (CLEAN CODE PRACTICE)
// ======================================================

// Function to safely load a controller file
// Keeps router clean and avoids repeating code
function load_controller(array $data, string $file, int $statusCode = 200): void
{
    // Set HTTP response code (200, 404, etc.)
    http_response_code($statusCode);

    // Check if controller file exists
    if (file_exists($file)) {
        require $file; // load controller
    } else {
        // If controller file missing → server error
        http_response_code(500);
        echo "Controller not found.";
    }
}


// Function to send JSON response (for API)
function json_response($data, int $statusCode = 200): void
{
    // Set HTTP response code
    http_response_code($statusCode);

    // Tell browser response is JSON
    header('Content-Type: application/json');

    // Convert array/object into JSON and outputs
    echo json_encode($data, JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_QUOT);

    // Stop script after response
    exit;
}


// Helper function for invalid HTTP methods
function method_not_allowed(): void
{
    json_response(['error' => 'Method Not Allowed'], 405);
}


//Get API routes and logic
require 'router/api.php';
//Get NORMAL routes and logic
//require 'router/web.php';

?>