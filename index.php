<?php

// Add autoload
require __DIR__ . '/vendor/autoload.php';

// Connect mysql
$conn = include_once('config.php');

/**
 * Helper class
 * @var minerHelper.php
 */
include_once('minerHelper.php');

// What page/algo we are on
$algo = 'bitcore';

// Gets data for specific miner address
$miner_address = $_GET['address'] ?? "";

// Get the current page
$page = $_GET['page'] ?? "index";

// Prepare twig
$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader, [
  'cache' => __DIR__ . '/twig_cache',
  'auto_reload' => true // Should be turned off on production
]);

// Load available routes
$load_routes = minerHelper::getRoutes();

// Get workers for miner address
$workers = minerHelper::getWorkers($conn, $miner_address);

// Check if $_GET['wallet'] is set or we have cookie value with a wallet
$wallet = minerHelper::checkWallet();

foreach ($workers as $key => $worker) {
  $hashrate = minerHelper::getHashrate($conn, $algo, $worker['version'], $worker['name']);
  $workers[$key]['hashrate'] = minerHelper::Itoa2($hashrate['hashrate']) . 'h/s';
}

// If template found, load otherwise 404
if (!empty($load_routes[$page]['template'])) {
  // Return TWIG template
  print $twig->render($load_routes[$page]['template'], [
    'name' => 'Greg',
    'workers' => $workers,
    'routes' => $load_routes,
    'current_route' => $page,
    'load_charts' => $load_routes[$page]['load_charts'] ?? FALSE,
    'wallet' => $wallet
  ]);
}
else {
  header("HTTP/1.0 404 Not Found");
}
