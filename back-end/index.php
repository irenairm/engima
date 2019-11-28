<?php
require 'config/database.php';
require 'routes/router.php';

use Config\Database;
use Routes\Router;

/*
 *  CORS setup
 */

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

// Getting request.
$request = $_SERVER['REQUEST_URI'];
$url_components = parse_url($request);
parse_str($url_components['query'], $params);

$explode_url = explode('/', $request);

// Assign controller and action, passed through routing.
$controller = $explode_url[1];
$query_arr = explode('?', $explode_url[2]);
$action = $query_arr[0];

/*
 *  Database connection.
 */
$database = new Database();
$error = '';
$connection = $database->connect();

/*
 * Route every single request: call router.php
 */
if ($controller != 'back-end') {
    $router = new Router();
    $router->route($controller, $action, $params, $database->connection);
}
