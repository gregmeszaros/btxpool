<?php

// Add autoload
require __DIR__ . '/../vendor/autoload.php';

// Connect mysql
$conn = include_once(__DIR__ . '/../config.php');
$redis = include_once(__DIR__ . '/../config-redis.php');

/**
 * Helper class
 * @var minerHelper.php
 */
include_once('minerHelper.php');

// What page/algo we are on
$coin_id = 1425;

// Check if $_GET['wallet'] is set or we have cookie value with a wallet
$wallet = minerHelper::checkWallet();

$data = [];
$data['miner_address'] = $wallet;
$data['coin_id'] = $coin_id;

// Get the total pool hashrate
$total_pool_hashrate = minerHelper::getPoolHashrateStats($conn, minerHelper::miner_getAlgos()[$data['coin_id']], 600, $redis);

// Get the current page
$page = $_GET['page'] ?? "index";

// Prepare twig
$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader, [
  'cache' => __DIR__ . '/../twig_cache',
  'auto_reload' => false // Should be turned off on production
]);

// Create some custom functions to twig
$addFriendlyHash = new Twig_SimpleFunction('showFriendlyHash', function ($hashrate) {
  return minerHelper::Itoa2($hashrate) . 'h/s';
});

$addDateTime = new Twig_SimpleFunction('showDateTime', function ($timestamp) {
  return minerHelper::getDateTime($timestamp);
});

$roundSimple = new Twig_SimpleFunction('roundSimple', function ($value) {
  return minerHelper::roundSimple($value);
});

$formatConfirmations = new Twig_SimpleFunction('formatConfirmations', function ($value) {
  return minerHelper::formatConfirmations($value);
});



// Add the functions
$twig->addFunction($addFriendlyHash);
$twig->addFunction($addDateTime);
$twig->addFunction($roundSimple);
$twig->addFunction($formatConfirmations);

// Load available routes
$load_routes = minerHelper::getRoutes();

// If template found, load otherwise 404
if (!empty($load_routes[$page]['template'])) {
  // Return TWIG template
  $default_variables = [
    'routes' => $load_routes,
    'current_route' => $page,
    'wallet' => $wallet,
    'total_pool_hashrate' => $total_pool_hashrate
  ];

  print $twig->render($load_routes[$page]['template'], array_merge($default_variables, minerHelper::_templateVariables($conn, $page, $data, $redis)));
}
else {
  header("HTTP/1.0 404 Not Found");
}
